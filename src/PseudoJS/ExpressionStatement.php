<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee\PseudoJS;

class ExpressionStatement extends Statement
{
    private $expression;

    public function __construct(Expression $expression) {
        $this->expression = $expression;
    }

    public function getExpression(): Expression {
        return $this->expression;
    }

    public function walk(callable /*(Node)*/ $visitor) {
        Node::walk($visitor);
        $this->expression->walk($visitor);
    }

    public function __toString(): string {
        return $this->expression->__toString() . ';';
    }
}
