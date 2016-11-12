<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

use ajf\ElePHPants_Love_Coffee\DataStructures\StringSet;
use ajf\ElePHPants_Love_Coffee\PseudoJS\Argument;
use ajf\ElePHPants_Love_Coffee\PseudoJS\AssignmentStatement;
use ajf\ElePHPants_Love_Coffee\PseudoJS\BinaryOperation;
use ajf\ElePHPants_Love_Coffee\PseudoJS\BooleanValue;
use ajf\ElePHPants_Love_Coffee\PseudoJS\ConstructorCall;
use ajf\ElePHPants_Love_Coffee\PseudoJS\Expression;
use ajf\ElePHPants_Love_Coffee\PseudoJS\ExpressionStatement;
use ajf\ElePHPants_Love_Coffee\PseudoJS\Func;
use ajf\ElePHPants_Love_Coffee\PseudoJS\FunctionCall;
use ajf\ElePHPants_Love_Coffee\PseudoJS\GlobalVariable;
use ajf\ElePHPants_Love_Coffee\PseudoJS\GotoLabelStatement;
use ajf\ElePHPants_Love_Coffee\PseudoJS\GotoStatement;
use ajf\ElePHPants_Love_Coffee\PseudoJS\IfStatement;
use ajf\ElePHPants_Love_Coffee\PseudoJS\LocalVariable;
use ajf\ElePHPants_Love_Coffee\PseudoJS\Lvalue;
use ajf\ElePHPants_Love_Coffee\PseudoJS\NullValue;
use ajf\ElePHPants_Love_Coffee\PseudoJS\NumberValue;
use ajf\ElePHPants_Love_Coffee\PseudoJS\ObjectValue;
use ajf\ElePHPants_Love_Coffee\PseudoJS\PropertyDereference;
use ajf\ElePHPants_Love_Coffee\PseudoJS\PseudoFunc;
use ajf\ElePHPants_Love_Coffee\PseudoJS\ReturnStatement;
use ajf\ElePHPants_Love_Coffee\PseudoJS\SourceFunc;
use ajf\ElePHPants_Love_Coffee\PseudoJS\Statement;
use ajf\ElePHPants_Love_Coffee\PseudoJS\TernaryOperation;
use ajf\ElePHPants_Love_Coffee\PseudoJS\UnaryOperation;

class PseudoJSToJSCompiler
{
    private $functions;

    private $output;
    private $indentLevel;
    private $declaredVariables;

    public function __construct(array $functions) {
        $this->functions = $functions;
    }

    public function compile(): string {
        $this->output = '';
        $this->indentLevel = 0;

        $this->emitPrelude();

        foreach ($this->functions as $function) {
            $this->compileFunction($function);
        }

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

    private function compileFunction(Func $func) {
        if ($func instanceof SourceFunc) {
            $source = $func->getSource();
            foreach (\explode("\n", $source) as $line) {
                $this->emitLine($line);
            }
            return;
        } else if (!$func instanceof PseudoFunc) {
            throw new \Exception("Unsupported Func type: " . get_class($func));
        }

        $this->emitLine('function ' . $func->getName() . '() {');
        $this->indent();

        $hasGotoLabels = $this->hasGotoLabels($func);

        $this->declaredVariables = new StringSet;

        // switch() for goto emulation
        if ($hasGotoLabels) {
            $this->emitLine("var jump = -1;");
            $this->emitLine("goto_emulation:");
            $this->emitLine("while (true) {");
            $this->indent();
            $this->emitLine("switch (jump) {");
            $this->indent();
            /* We have to have a first case that will be initially jumped to */
            $this->emitLine("case -1:");
        }

        foreach ($func as $i => $statement) {
            $this->compileStatement($statement);
        }

        if ($hasGotoLabels) {
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

    private function hasGotoLabels(PseudoFunc $func): bool {
        foreach ($func as $statement) {
            if ($statement instanceof GotoLabelStatement) {
                return true;
            }
        }

        return false;
    }

    private function compileStatement(Statement $statement) {
        if ($statement instanceof GotoLabelStatement) {
            $this->emitLine('case ' . $statement->getLabel() . ':');
        } elseif ($statement instanceof GotoStatement) {
            $this->emitLine('jump = ' . $statement->getLabel() . ';');
            $this->emitLine('continue goto_emulation;');
        } elseif ($statement instanceof AssignmentStatement) {
            $target = $statement->getTarget();
            if (!$target instanceof LocalVariable) {
                throw new \Exception("Can't handle assignment target of type " . \get_class($target));
            }
            $name = $target->getName();
            if (!$this->declaredVariables->has($name)) {
                $this->emitLineBegin('var ');
                $this->declaredVariables->add($name);
            } else {
                $this->emitLineBegin();
            }
            $this->compileLvalue($target);
            $this->emit(' = ');
            $this->compileExpression($statement->getValue());
            $this->emitLineEnd(';');
        } elseif ($statement instanceof IfStatement) {
            $this->emitLineBegin('if (');
            $this->compileExpression($statement->getCondition());
            $this->emitLineEnd(') {');
            $this->indent();
            $this->compileStatement($statement->getStatement());
            $this->dedent();
            $this->emitLine('}');
        } elseif ($statement instanceof ExpressionStatement) {
            $this->emitLineBegin();
            $this->compileExpression($statement->getExpression());
            $this->emitLineEnd(';');
        } elseif ($statement instanceof ReturnStatement) {
            $this->emitLineBegin('return ');
            $this->compileExpression($statement->getValue());
            $this->emitLineEnd(';');
        } else {
            throw new \Exception("Can't handle statement of type " . \get_class($statement));
        }
    }

    private function compileLvalue(Lvalue $lvalue) {
        switch (TRUE) {
            case $lvalue instanceof LocalVariable:
                $name = $lvalue->getName();
                if (!$this->declaredVariables->has($name)) {
                    throw new \Exception("Use of undeclared local variable $name");
                }
                $this->emit($lvalue->getName());
                break;
            case $lvalue instanceof GlobalVariable:
                $name = $lvalue->getName();
                if (!isset($this->functions[$name])) {
                    throw new \Exception("Use of undeclared global variable $name");
                }
                $this->emit($lvalue->getName());
                break;
            case $lvalue instanceof PropertyDereference:
                $this->emit('(');
                $this->compileExpression($lvalue->getObject());
                $this->emit(').' . $lvalue->getProperty());
                break;
            default:
                throw new \Exception("Can't handle lvalue of type " . \get_class($lvalue));
                break;
        }
    }

    private function compileExpression(Expression $expression) {
        switch (TRUE) {
            case $expression instanceof Lvalue:
                $this->compileLvalue($expression);
                break;
            case $expression instanceof Argument:
                $this->emit('arguments[' . $expression->getIndex() . ']');
                break;
            case $expression instanceof FunctionCall:
                $this->compileFunctionCall($expression->getFunction(), $expression->getArguments());
                break;
            case $expression instanceof ConstructorCall:
                $this->emit('new ');
                $this->compileFunctionCall($expression->getConstructor(), $expression->getArguments());
                break;
            case $expression instanceof UnaryOperation:
                $this->emit($expression->getOperator());
                $this->emit('(');
                $this->compileExpression($expression->getOperand());
                $this->emit(')');
                break;
            case $expression instanceof BinaryOperation:
                $this->emit('(');
                $this->compileExpression($expression->getLeftOperand());
                $this->emit(')');
                $this->emit($expression->getOperator());
                $this->emit('(');
                $this->compileExpression($expression->getRightOperand());
                $this->emit(')');
                break;
            case $expression instanceof TernaryOperation:
                $this->emit('(');
                $this->compileExpression($expression->getCondition());
                $this->emit(') ? (');
                $this->compileExpression($expression->getLeftExpression());
                $this->emit(') : (');
                $this->compileExpression($expression->getRightExpression());
                $this->emit(')');
                break;
            case $expression instanceof NullValue:
                $this->emit('null');
                break;
            case $expression instanceof BooleanValue:
                $this->emit($expression->getValue() ? 'true' : 'false');
                break;
            case $expression instanceof NumberValue:
                $value = $expression->getValue();
                if (\is_nan($value)) {
                    $this->emit('NaN');
                } else if (!\is_finite($value)) {
                    $this->emit(($value < 0 ? '-' : '') . 'Infinity');
                } else {
                    $this->emit((string)$value);
                }
                break;
            case $expression instanceof ObjectValue:
                $this->emit('{ ');
                $first = TRUE;
                foreach ($expression as $name => $value) {
                    if ($first) {
                        $first = FALSE;
                    } else {
                        $this->emit(', ');
                    }
                    $this->emit($name . ': ');
                    $this->compileExpression($value);
                }
                $this->emit(' }');
                break;
            default:
                throw new \Exception("Can't handle expression of type " . \get_class($expression));
                break;
        }
    }

    private function compileFunctionCall(Expression $function, array /*<Expression>*/ $arguments) {
        $this->emit('(');
        $this->compileExpression($function);
        $this->emit(')(');
        $first = TRUE;
        foreach ($arguments as $i => $argument) {
            if ($i !== 0) {
                $this->emit(', ');
            }
            $this->compileExpression($argument);
        }
        $this->emit(')');

    }
}
