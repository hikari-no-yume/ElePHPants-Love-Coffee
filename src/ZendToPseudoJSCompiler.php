<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

use ajf\ElePHPants_Love_Coffee\DataStructures\IntSet;
use ajf\ElePHPants_Love_Coffee\DataStructures\Stack;
use ajf\ElePHPants_Love_Coffee\DataStructures\StringSet;
use ajf\ElePHPants_Love_Coffee\PseudoJS\Argument;
use ajf\ElePHPants_Love_Coffee\PseudoJS\AssignmentStatement;
use ajf\ElePHPants_Love_Coffee\PseudoJS\BinaryOperation;
use ajf\ElePHPants_Love_Coffee\PseudoJS\BooleanValue;
use ajf\ElePHPants_Love_Coffee\PseudoJS\ConstructorCall;
use ajf\ElePHPants_Love_Coffee\PseudoJS\Expression;
use ajf\ElePHPants_Love_Coffee\PseudoJS\ExpressionStatement;
use ajf\ElePHPants_Love_Coffee\PseudoJS\PseudoFunc;
use ajf\ElePHPants_Love_Coffee\PseudoJS\FunctionCall;
use ajf\ElePHPants_Love_Coffee\PseudoJS\GlobalVariable;
use ajf\ElePHPants_Love_Coffee\PseudoJS\GotoLabelStatement;
use ajf\ElePHPants_Love_Coffee\PseudoJS\GotoStatement;
use ajf\ElePHPants_Love_Coffee\PseudoJS\IfStatement;
use ajf\ElePHPants_Love_Coffee\PseudoJS\LocalVariable;
use ajf\ElePHPants_Love_Coffee\PseudoJS\Lvalue;
use ajf\ElePHPants_Love_Coffee\PseudoJS\NullValue;
use ajf\ElePHPants_Love_Coffee\PseudoJS\NumberValue;
use ajf\ElePHPants_Love_Coffee\PseudoJS\PropertyDereference;
use ajf\ElePHPants_Love_Coffee\PseudoJS\ReturnStatement;
use ajf\ElePHPants_Love_Coffee\PseudoJS\SourceFunc;
use ajf\ElePHPants_Love_Coffee\PseudoJS\Statement;
use ajf\ElePHPants_Love_Coffee\PseudoJS\TernaryOperation;
use ajf\ElePHPants_Love_Coffee\PseudoJS\UnaryOperation;
use ajf\ElePHPants_Love_Coffee\ZendEngine\CompiledVariableOperand;
use ajf\ElePHPants_Love_Coffee\ZendEngine\JumpTargetOperand;
use ajf\ElePHPants_Love_Coffee\ZendEngine\LiteralOperand;
use ajf\ElePHPants_Love_Coffee\ZendEngine\Operand;
use ajf\ElePHPants_Love_Coffee\ZendEngine\Opline;
use ajf\ElePHPants_Love_Coffee\ZendEngine\OplineArray;
use ajf\ElePHPants_Love_Coffee\ZendEngine\VariableOperand;

class ZendToPseudoJSCompiler
{
    private $functions;
    private $entryPoint;

    private $currentJSFunc;
    private $requiredZendFunctions;
    private $fcallInfoStack;
    private $argumentCount;

    public function __construct(array $functions, string $entryPoint) {
        $this->functions = $functions;
        $this->entryPoint = $entryPoint;
    }

    public function compile(): array /* <Func> */ {
        // contains names of opcode implementation functions we depend on
        $this->requiredZendFunctions = new StringSet;

        $jsFunctions = [];

        foreach ($this->functions as $name => $function) {
            if ($name === $this->entryPoint) {
                continue;
            }

            $jsFunctions["fun_$name"] = $this->compileFunction($function, "fun_$name");
        }

        $jsFunctions['__main'] = $this->compileFunction($this->functions[$this->entryPoint], '__main');

        foreach ($this->requiredZendFunctions as $function) {
            $jsFunctions[$function] = new SourceFunc($function);
            $jsFunctions[$function]->setSource(ZEND_FUNCTIONS[$function]['source']);
        }

        return $jsFunctions;
    }

    private function emitStatement(Statement $statement) /* : void */ {
        $this->currentJSFunc->addInstruction($statement);
    }

    private function compileFunction(OplineArray $oparray, string $name): PseudoFunc {
        $this->currentJSFunc = new PseudoFunc($name);

        // contains opcodes which are actually jumped to
        // this is so we can avoid emitting unused labels
        $jumpTargets = $this->findJumpTargets($oparray);

        $this->fcallInfoStack = new Stack;
        $this->argumentCount = 0;

        foreach ($oparray as $i => $opline) {
            if ($jumpTargets->has($i)) {
                $this->emitStatement(new GotoLabelStatement($i));
            }
            $this->compileOpline($opline);
        }

        return $this->currentJSFunc;
    }

    private function findJumpTargets(OplineArray $oparray): IntSet {
        $jumpTargets = new IntSet;
        
        foreach ($oparray as $i => $opline) {
            if (substr(OPCODE_NAMES[$opline->getType()], 0, 8) === 'ZEND_JMP') {
                $op1 = $opline->getOperand1();
                if ($op1 instanceof JumpTargetOperand) {
                    $jumpTargets->add($op1->getOplineIndex());
                }
                $op2 = $opline->getOperand2();
                if ($op2 instanceof JumpTargetOperand) {
                    $jumpTargets->add($op2->getOplineIndex());
                }
            }
        }

        return $jumpTargets;
    }

    private function compileOpline(Opline $opline) {
        $type = $opline->getType();
        $op1 = $opline->getOperand1();
        $op2 = $opline->getOperand2();
        $result = $opline->getResult();

        $assert = function (string $what, string $as) use (&$type, &$op1, &$op2, &$result) /* : void */ {
            switch ($as) {
                case "NULL":
                    $condition = ($$what === NULL);
                    break;
                case "literal":
                    $condition = ($$what instanceof LiteralOperand);
                    break;
                case "jump":
                    $condition = ($$what instanceof JumpTargetOperand);
                    break;
                case "cv":
                    $condition = ($$what instanceof CompiledVariableOperand);
                    break;
                case "var":
                    $condition = ($$what instanceof VariableOperand);
                    break;
                default:
                    throw new \Exception("Incorrect \$type-type, \$type=\"$type\"");
            }
            if (!$condition) {
                throw new \Exception("Can't handle non-$as $what for " . OPCODE_NAMES[$type]);
            }
        };

        switch ($type) {
            case ZEND_NOP:
                //$this->emitStatement(new ExpressionStatement(new FunctionCall(new GlobalVariable('void'), new NumberValue(0))));
                break;
            case ZEND_ECHO:
                $assert("op2", "NULL");
                $assert("result", "NULL");

                // TODO: echo properly!
                $this->emitStatement(new ExpressionStatement(
                    new FunctionCall(
                        new PropertyDereference(new GlobalVariable('console'), 'dir'),
                        $this->compileOperandAsRvalue($op1)
                    )
                ));
                break;
            case ZEND_INIT_FCALL:
            case ZEND_INIT_FCALL_BY_NAME:
                $assert("op1", "NULL");
                $assert("op2", "literal");
                $assert("result", "NULL");
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
                $this->emitStatement(new AssignmentStatement(
                    new LocalVariable('fcall' . $fcallNumber . 'Target'),
                    new GlobalVariable($jsFunctionName)
                ));
                $this->fcallInfoStack->push([
                    'number' => $fcallNumber,
                    'argumentCount' => 0
                ]);
                $this->isFirst = TRUE;
                break;
            case ZEND_SEND_VAL:
            case ZEND_SEND_VAL_EX:
            case ZEND_SEND_VAR:
                $assert("op2", "NULL");
                $assert("result", "NULL");

                $fcallInfo = $this->fcallInfoStack->pop();
                $this->emitStatement(new AssignmentStatement(
                    new LocalVariable('fcall' . $fcallInfo['number'] . 'Argument' . $fcallInfo['argumentCount']),
                    $this->compileOperandAsRvalue($op1)
                ));
                $fcallInfo['argumentCount']++;
                $this->fcallInfoStack->push($fcallInfo);
                break;
            case ZEND_DO_FCALL:
            case ZEND_DO_FCALL_BY_NAME:
            case ZEND_DO_ICALL:
            case ZEND_DO_UCALL:
                $assert("op1", "NULL");
                $assert("op2", "NULL");

                $fcallInfo = $this->fcallInfoStack->pop();

                $arguments = [];
                for ($i = 0; $i < $fcallInfo['argumentCount']; $i++) {
                    $arguments[] = new LocalVariable('fcall' . $fcallInfo['number'] . 'Argument' . $i);
                }

                $this->emitStatement(new AssignmentStatement(
                    $this->compileOperandAsLvalue($result),
                    new FunctionCall(
                        new LocalVariable('fcall' . $fcallInfo['number'] . 'Target'),
                        ...$arguments
                    )
                ));
                break;
            case ZEND_RECV:
                $assert("op1", "NULL");
                $assert("op2", "NULL");

                $this->emitStatement(new AssignmentStatement(
                    $this->compileOperandAsLvalue($result),
                    new Argument($this->argumentCount)
                ));
                $this->argumentCount++;
                break;
            case ZEND_IS_SMALLER:
                $this->requireZendFunction('zend_compare_function');

                $this->emitStatement(new AssignmentStatement(
                    $this->compileOperandAsLvalue($result),
                    new FunctionCall(
                        new GlobalVariable('zend_compare_function'),
                        $this->compileOperandAsRvalue($op1),
                        $this->compileOperandAsRvalue($op2)
                    )
                ));

                $this->emitStatement(new AssignmentStatement(
                    $this->compileOperandAsLvalue($result),
                    new TernaryOperation(
                        new BinaryOperation(
                            '<',
                            new PropertyDereference(
                                $this->compileOperandAsRvalue($result),
                                'val'
                            ),
                            new NumberValue(0)
                        ),
                        new BooleanValue(true),
                        new BooleanValue(false)
                    )
                ));
                break;
            case ZEND_SUB:
                $this->requireZendFunction('zend_sub_function');

                $this->emitStatement(new AssignmentStatement(
                    $this->compileOperandAsLvalue($result),
                    new FunctionCall(
                        new GlobalVariable('zend_sub_function'),
                        $this->compileOperandAsRvalue($op1),
                        $this->compileOperandAsRvalue($op2)
                    )
                ));
                break;
            case ZEND_MUL:
                $this->requireZendFunction('zend_mul_function');

                $this->emitStatement(new AssignmentStatement(
                    $this->compileOperandAsLvalue($result),
                    new FunctionCall(
                        new GlobalVariable('zend_mul_function'),
                        $this->compileOperandAsRvalue($op1),
                        $this->compileOperandAsRvalue($op2)
                    )
                ));
                break;
            case ZEND_JMP:
                $assert("op2", "NULL");
                $assert("result", "NULL");

                $this->emitStatement($this->compileJump($op1));
                break;
            case ZEND_JMPZ:
                $assert("result", "NULL");
                $this->requireZendFunction('zend_is_true');

                $this->emitStatement(new IfStatement(
                    new UnaryOperation(
                        '!',
                        new FunctionCall(
                            new GlobalVariable('zend_is_true'),
                            $this->compileOperandAsRvalue($op1)
                        )
                    ),
                    $this->compileJump($op2)
                ));
                break;
            case ZEND_QM_ASSIGN:
                $assert("op2", "NULL");

                $this->emitStatement(new AssignmentStatement(
                    $this->compileOperandAsLvalue($result),
                    $this->compileOperandAsRvalue($op1)
                ));
                break;
            case ZEND_RETURN:
                $assert("op2", "NULL");
                $assert("result", "NULL");

                $this->emitStatement(new ReturnStatement(
                    $this->compileOperandAsRvalue($op1)
                ));
                break;
            default:
                throw new \Exception("Can't handle opcode " . OPCODE_NAMES[$opline->getType()]);
                break;
        }
    }

    private function compileJump(JumpTargetOperand $op): GotoStatement {
        return new GotoStatement($op->getOplineIndex());
    }

    private function compileOperandAsLvalue(Operand $op): Lvalue {
        switch (TRUE) {
            case $op instanceof CompiledVariableOperand:
                return new LocalVariable('cv_' . $op->getName());
                break;
            case $op instanceof VariableOperand:
                return new LocalVariable('var_' . $op->getNumber());
                break;
            default:
                throw new \Exception("Can't handle lvalue operand of type " . get_class($op));
                break;
        }
    }

    private function compileOperandAsRvalue(Operand $op): Expression {
        switch (TRUE) {
            case $op instanceof LiteralOperand:
                return $this->compileZval($op->getValue());
                break;
            case $op instanceof CompiledVariableOperand:
                return new LocalVariable('cv_' . $op->getName());
                break;
            case $op instanceof VariableOperand:
                return new LocalVariable('var_' . $op->getNumber());
                break;
            default:
                throw new \Exception("Can't handle rvalue operand of type " . get_class($op));
                break;
        }
    }

    private function compileZval($value): Expression {
        switch (gettype($value)) {
            case "NULL":
                return new NullValue;
            case "boolean":
                if ($value) {
                    return new BooleanValue(true);
                } else {
                    return new BooleanValue(false);
                }
            case "integer":
                $this->requireZendFunction('zend_long');

                return new ConstructorCall(
                    new GlobalVariable('zend_long'),
                    new NumberValue((float)$value)
                );
            case "double":
                $this->requireZendFunction('zend_double');

                return new ConstructorCall(
                    new GlobalVariable('zend_double'),
                    new NumberValue($value)
                );
            default:
                throw new \Exception("Can't handle literals of type " . gettype($value));
                break;
        }
    }

    private function requireZendFunction(string $name) {
        if (!isset(ZEND_FUNCTIONS[$name])) {
            throw new \Exception("No such Zend function: $name");
        }
        $functionDependencies = ZEND_FUNCTIONS[$name]['require'] ?? NULL;
        $this->requiredZendFunctions->add($name);
        if (!empty($functionDependencies)) {
            foreach ($functionDependencies as $function) {
                $this->requireZendFunction($function);
            }
        }
    }
}
