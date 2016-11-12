<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee\PseudoJS;

class TernaryOperation extends Expression
{
    private $condition;
    private $leftExpression;
    private $rightExpression;

    public function __construct(
        Expression $condition,
        Expression $leftExpression,
        Expression $rightExpression
    ) {
        $this->condition = $condition;
        $this->leftExpression = $leftExpression;
        $this->rightExpression = $rightExpression;
    }

    public function getCondition(): Expression {
        return $this->condition;
    }

    public function getLeftExpression(): Expression {
        return $this->leftExpression;
    }

    public function getRightExpression(): Expression {
        return $this->rightExpression;
    }

    public function __toString(): string {
        return '(' . $this->condition->__toString() . ') ? (' . $this->leftExpression->__toString() . ') : (' . $this->rightExpression->__toString() . ')';
    }
}
