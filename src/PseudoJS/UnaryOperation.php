<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee\PseudoJS;

class UnaryOperation extends Expression
{
    const VALID_OPERATORS = [
        "+",
        "-",
        "~",
        "!",
    ];

    private $operator;
    private $operand;

    public function __construct(
        string $operator,
        Expression $operand
    ) {
        if (!\in_array($operator, self::VALID_OPERATORS)) {
            throw new \RuntimeException("$operator is not a valid JavaScript unary operator");
        }

        $this->operator = $operator;
        $this->operand = $operand;
    }

    public function getOperator(): string {
        return $this->operator;
    }

    public function getOperand(): Expression {
        return $this->operand;
    }

    public function __toString(): string {
        return $this->operator . '(' . $this->operand->__toString() . ')';
    }
}
