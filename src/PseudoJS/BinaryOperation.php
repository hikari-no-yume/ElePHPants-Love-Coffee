<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee\PseudoJS;

class BinaryOperation extends Expression
{
    const VALID_OPERATORS = [
        "+",
        "-",
        "*",
        "/",
        "%",
        "**",
        "==",
        "!=",
        "<",
        "<=",
        ">",
        ">=",
        "===",
        "!==",
        "&",
        "|",
        "^",
        "<<",
        ">>",
        ">>>",
        "&&",
        "||",
        ","
    ];

    private $operator;
    private $leftOperand;
    private $rightOperand;

    public function __construct(
        string $operator,
        Expression $leftOperand,
        Expression $rightOperand
    ) {
        if (!\in_array($operator, self::VALID_OPERATORS)) {
            throw new \RuntimeException("$operator is not a valid JavaScript binary operator");
        }

        $this->operator = $operator;
        $this->leftOperand = $leftOperand;
        $this->rightOperand = $rightOperand;
    }

    public function getOperator(): string {
        return $this->operator;
    }

    public function getLeftOperand(): Expression {
        return $this->leftOperand;
    }

    public function getRightOperand(): Expression {
        return $this->rightOperand;
    }

    public function walk(callable /*(Node)*/ $visitor) {
        Node::walk($visitor);
        $this->leftOperand->walk($visitor);
        $this->rightOperand->walk($visitor);
    }

    public function __toString(): string {
        return '(' . $this->leftOperand->__toString() . ') ' . $this->operator . ' (' . $this->rightOperand->__toString() . ')';
    }
}
