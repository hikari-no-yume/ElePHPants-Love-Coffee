<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

use ajf\ElePHPants_Love_Coffee\DataStructures\Stack as Stack;

class Compiler
{
    private $functions;
    private $entryPoint;

    private $output;
    private $indentLevel;
    private $requiredZendFunctions;
    private $fcallInfoStack;

    public function __construct(array $functions, string $entryPoint) {
        $this->functions = $functions;
        $this->entryPoint = $entryPoint;
    }

    public function compile() {
        $this->output = '';
        $this->indentLevel = 0;
        // "set" (really, an array with ignored values)
        // contains names of opcode implementation functions we need in output
        $this->requiredZendFunctions = [];

        $this->emitPrelude();

        foreach ($this->functions as $key => $function) {
            if ($key === $this->entryPoint) {
                continue;
            }

            $this->compileFunction($key);
        }

        $this->compileFunction($this->entryPoint, '__main');

        $this->emitZendFunctions();

        $this->emitLine('__main();');

        $this->emitPostlude();

        return $this->output;
    }

    private function emitLine(string $line) {
        $this->output .= str_repeat(' ', $this->indentLevel);
        $this->output .= $line;
        $this->output .= PHP_EOL;
    }

    private function emitLineBegin(string $line = '') {
        $this->output .= str_repeat(' ', $this->indentLevel);
        $this->output .= $line;
    }

    private function emitLineEnd(string $line = '') {
        $this->output .= $line;
        $this->output .= PHP_EOL;
    }

    private function emit(string $string) {
        $this->output .= $string;
    }

    private function indent() {
        $this->indentLevel += 4;
    }

    private function dedent() {
        $this->indentLevel -= 4;
        if ($this->indentLevel < 0) {
            throw new \Exception("Indentation level fell below zero");
        }
    }

    private function emitPrelude() {
        $this->emitLine('(function () {');
        $this->indent();
        $this->emitLine('"use strict";');
    }

    private function emitPostlude() {
        $this->dedent();
        $this->emitLine('}());');
    }

    private function compileFunction(string $name, string $rename = NULL) {
        $oparray = $this->functions[$name];

        if ($rename !== NULL) {
            $this->emitLine('function ' . $rename . '() {');
        } else {
            $this->emitLine('function fun_' . strtolower($name) . '() {');
        }
        $this->indent();

        // "set" (really, an array with unused values)
        // contains opcodes which are actually jumped to
        // this is so we can avoid emitting unused labels
        $jumpTargets = array_flip($this->findJumpTargets($oparray));

        $usedVariables = $this->findUsedVariables($oparray);

        foreach ($usedVariables as $operand) {
            $this->compileOperandAsDeclaration($operand);
        }

        $this->fcallInfoStack = new Stack;

        // switch() for goto emulation
        if (!empty($jumpTargets)) {
            $this->emitLine("var jump = 0;");
            $this->emitLine("goto_emulation:");
            $this->emitLine("while (true) {");
            $this->indent();
            $this->emitLine("switch (jump) {");
            $this->indent();
            
            // ensure there's a "case 0:" for the first opcode
            $jumpTargets[0] = TRUE;
        }

        foreach ($oparray as $i => $opline) {
            if (isset($jumpTargets[$i])) {
                $this->emitLine("case " . $i . ":");
            }
            $this->compileOpline($opline);
        }

        if (!empty($jumpTargets)) {
            // end switch()
            $this->dedent();
            $this->emitLine("}");
            // end while()
            $this->dedent();
            $this->emitLine("}");
        }

        // end function
        $this->dedent();
        $this->emitLine('}');
    }

    private function findJumpTargets(OplineArray $oparray): array {
        $jumpTargets = [];
        
        foreach ($oparray as $i => $opline) {
            if (substr(OPCODE_NAMES[$opline->getType()], 0, 8) === 'ZEND_JMP') {
                $op1 = $opline->getOperand1();
                if ($op1 instanceof JumpTargetOperand) {
                    $jumpTargets[] = $op1->getOplineIndex();
                }
                $op2 = $opline->getOperand2();
                if ($op2 instanceof JumpTargetOperand) {
                    $jumpTargets[] = $op2->getOplineIndex();
                }
            }
        }

        return $jumpTargets;
    }

    private function findUsedVariables(OplineArray $oparray): array {
        $usedVariables = [];

        foreach ($oparray as $opline) {
            $result = $opline->getResult();
            if ($result instanceof CompiledVariableOperand) {
                $usedVariables['cv_' . $result->getName()] = $result;
            } else if ($result instanceof VariableOperand) {
                $usedVariables['var_' . $result->getNumber()] = $result;
            }
        }

        return array_values($usedVariables);
    }

    private function compileOpline(Opline $opline) {
        $type = $opline->getType();
        $op1 = $opline->getOperand1();
        $op2 = $opline->getOperand2();
        $result = $opline->getResult();

        switch ($type) {
            case ZEND_NOP:
                //$this->emitLine("void(0);");
                break;
            case ZEND_ECHO:
                if ($op2 !== NULL || $result !== NULL) {
                    throw new \Exception("Can't handle non-NULL op2 and result for ZEND_ECHO");
                }
                // TODO: echo properly!
                $this->emitLineBegin("console.dir(");
                $this->compileOperandAsRvalue($op1);
                $this->emitLineEnd(");");
                break;
            case ZEND_INIT_FCALL:
            case ZEND_INIT_FCALL_BY_NAME:
                if ($op1 !== NULL || $result !== NULL) {
                    throw new \Exception("Can't handle non-NULL op1 and result for ZEND_INIT_FCALL");
                }
                if (!$op2 instanceof LiteralOperand) {
                    throw new \Exception("Can't handle non-literal op2 for ZEND_INIT_FCALL");
                }
                $functionName = $op2->getValue();
                if (!is_string($functionName)) {
                    throw new \Exception("Can't handle non-string op2 for ZEND_INIT_FCALL");
                }

                if (isset($this->functions[$functionName])) {
                    $jsFunctionName = 'fun_' . $functionName;
                } else if (isset(PHP_FUNCTIONS[$functionName])) {
                    $jsFunctionName = PHP_FUNCTIONS[$functionName];

                    $this->requireZendFunction($jsFunctionName);
                } else {
                    throw new \Exception("Can't find any function by the name '$functionName'");
                }

                $fcallNumber = $this->fcallInfoStack->height();
                $this->emitLine('var fcall' . $fcallNumber . 'Target = ' . $jsFunctionName . ';');
                $this->fcallInfoStack->push([
                    'number' => $fcallNumber,
                    'argumentCount' => 0
                ]);
                $this->isFirst = TRUE;
                break;
            case ZEND_SEND_VAL:
            case ZEND_SEND_VAL_EX:
            case ZEND_SEND_VAR:
                if ($op2 !== NULL || $result !== NULL) {
                    throw new \Exception("Can't handle non-NULL op2 and result for ZEND_SEND_VAL");
                }
                $fcallInfo = $this->fcallInfoStack->pop();
                $this->emitLineBegin('var fcall' . $fcallInfo['number'] . 'Argument' . $fcallInfo['argumentCount'] . ' = ');
                $this->compileOperandAsRvalue($op1);
                $this->emitLineEnd(';');
                $fcallInfo['argumentCount']++;
                $this->fcallInfoStack->push($fcallInfo);
                break;
            case ZEND_DO_FCALL:
            case ZEND_DO_ICALL:
            case ZEND_DO_UCALL:
                if ($op1 !== NULL || $op2 !== NULL) {
                    throw new \Exception("Can't handle non-NULL op1 and op2 for ZEND_DO_FCALL");
                }
                $fcallInfo = $this->fcallInfoStack->pop();
                $this->emitLineBegin();
                $this->compileOperandAsLvalue($result);
                $this->emit(' = fcall' . $fcallInfo['number'] . 'Target(');
                for ($i = 0; $i < $fcallInfo['argumentCount']; $i++) {
                    if ($i !== 0) {
                        $this->emit(', ');
                    }
                    $this->emit('fcall' . $fcallInfo['number'] . 'Argument' . $i);
                }
                $this->emitLineEnd(');');
                break;
            case ZEND_RECV:
                if ($op1 !== NULL || $op2 !== NULL) {
                    throw new \Exception("Can't handle non-NULL op1 and op2 for ZEND_RECV");
                }
                $this->emitLineBegin();
                $this->compileOperandAsLvalue($result);
                $this->emitLineEnd(' = arguments[arguments.i = arguments.i || 0];');
                $this->emitLine('arguments.i++;');
                break;
            case ZEND_IS_SMALLER:
                $this->requireZendFunction('zend_compare_function');

                $this->emitLineBegin('zend_compare_function(');
                $this->compileOperandAsRvalue($result);
                $this->emit(', ');
                $this->compileOperandAsRvalue($op1);
                $this->emit(', ');
                $this->compileOperandAsRvalue($op2);
                $this->emitLineEnd(');');

                $this->emitLineBegin();
                $this->compileOperandAsLvalue($result);
                $this->emit(' = { type: (');
                $this->compileOperandAsRvalue($result);
                $this->emitLineEnd('.lval < 0) ? ' . IS_TRUE . ' : ' . IS_FALSE . ' };');
                break;
            case ZEND_SUB:
                $this->requireZendFunction('zend_sub_function');

                $this->emitLineBegin('zend_sub_function(');
                $this->compileOperandAsRvalue($result);
                $this->emit(', ');
                $this->compileOperandAsRvalue($op1);
                $this->emit(', ');
                $this->compileOperandAsRvalue($op2);
                $this->emitLineEnd(');');
                break;
            case ZEND_MUL:
                $this->requireZendFunction('zend_mul_function');

                $this->emitLineBegin('zend_mul_function(');
                $this->compileOperandAsRvalue($result);
                $this->emit(', ');
                $this->compileOperandAsRvalue($op1);
                $this->emit(', ');
                $this->compileOperandAsRvalue($op2);
                $this->emitLineEnd(');');
                break;
            case ZEND_JMP:
                if ($op2 !== NULL && $result !== NULL) {
                    throw new \Exception("Can't handle non-NULL op2 and result for ZEND_JMPZ");
                }
                $this->compileJump($op1);
                break;
            case ZEND_JMPZ:
                if ($result !== NULL) {
                    throw new \Exception("Can't handle non-NULL result for ZEND_JMPZ");
                }
                $this->requireZendFunction('zend_is_true');

                $this->emitLineBegin('if (!zend_is_true(');
                $this->compileOperandAsRvalue($op1);
                $this->emitLineEnd(')) {');
                $this->indent();
                
                $this->compileJump($op2);

                // end if
                $this->dedent();
                $this->emitLine('}');
                break;
            case ZEND_QM_ASSIGN:
                if ($op2 !== NULL) {
                    throw new \Exception("Can't handle non-NULL result for ZEND_QM_ASSIGN");
                }
                $this->emitLineBegin();
                $this->compileOperandAsLvalue($result);
                $this->emit(' = ');
                $this->compileOperandAsRvalue($op1);
                $this->emitLineEnd(';');
                break;
            case ZEND_RETURN:
                if ($op2 !== NULL && $result !== NULL) {
                    throw new \Exception("Can't handle non-NULL op2 and result for ZEND_RETURN");
                }
                $this->emitLineBegin('return ');
                $this->compileOperandAsRvalue($op1);
                $this->emitLineEnd(';');
                break;
            default:
                throw new \Exception("Can't handle opcode " . OPCODE_NAMES[$opline->getType()]);
                break;
        }
    }

    private function compileJump(JumpTargetOperand $op) {
        $this->emitLine('jump = ' . $op->getOplineIndex() . ';');
        $this->emitLine('continue goto_emulation;');
    }

    private function compileOperandAsDeclaration(Operand $op) {
        $this->emitLineBegin('var ');
        switch (TRUE) {
            case $op instanceof CompiledVariableOperand:
                $this->emit('cv_' . $op->getName());
                break;
            case $op instanceof VariableOperand:
                $this->emit('var_' . $op->getNumber());
                break;
            default:
                throw new \Exception("Can't handle variable declaration of operand of type " . get_class($op));
                break;
        }
        $this->emitLineEnd(' = { type: ' . IS_UNDEF . ' };');
    }

    private function compileOperandAsLvalue(Operand $op) {
        switch (TRUE) {
            case $op instanceof CompiledVariableOperand:
                $this->emit('cv_' . $op->getName());
                break;
            case $op instanceof VariableOperand:
                $this->emit('var_' . $op->getNumber());
                break;
            default:
                throw new \Exception("Can't handle lvalue operand of type " . get_class($op));
                break;
        }
    }

    private function compileOperandAsRvalue(Operand $op) {
        switch (TRUE) {
            case $op instanceof LiteralOperand:
                $this->compileZval($op->getValue());
                break;
            case $op instanceof CompiledVariableOperand:
                $this->emit('cv_' . $op->getName());
                break;
            case $op instanceof VariableOperand:
                $this->emit('var_' . $op->getNumber());
                break;
            default:
                throw new \Exception("Can't handle rvalue operand of type " . get_class($op));
                break;
        }
    }

    private function compileZval($value) {
        $this->emit("{ type: ");
        switch (gettype($value)) {
            case "NULL":
                $this->emit((string)IS_NULL);
                break;
            case "boolean":
                if ($value) {
                    $this->emit((string)IS_TRUE);
                } else {
                    $this->emit((string)IS_FALSE);
                }
                break;
            case "integer":
                $this->emit((string)IS_LONG . ', ');
                $this->emit('lval: ' . (string)$value);
                break;
            case "double":
                $this->emit((string)IS_DOUBLE . ', ');
                $this->emit('dval: ');
                if ($value === INF) {
                    $this->emit('Infinity');
                } else if ($value === -INF) {
                    $this->emit('-Infinity');
                } else if (is_nan($value)) {
                    $this->emit('NaN');
                } else {
                    $this->emit(json_encode($value));
                }
                break;
            default:
                throw new \Exception("Can't handle literals of type " . gettype($value));
                break;
        }
        $this->emit(" }");
    }

    private function requireZendFunction(string $name) {
        if (!isset(ZEND_FUNCTIONS[$name])) {
            throw new \Exception("No such Zend function: $name");
        }
        $functionDependencies = ZEND_FUNCTIONS[$name]['require'] ?? NULL;
        $this->requiredZendFunctions[$name] = NULL;
        if (!empty($functionDependencies)) {
            foreach ($functionDependencies as $function) {
                $this->requireZendFunction($function);
            }
        }
    }

    private function emitZendFunctions() {
        foreach ($this->requiredZendFunctions as $key => $value) {
            $lines = explode("\n", ZEND_FUNCTIONS[$key]['source']);
            foreach ($lines as $line) {
                $this->emitLine($line);
            }
        }
    }
}
