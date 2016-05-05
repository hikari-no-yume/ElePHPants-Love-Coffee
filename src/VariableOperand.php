<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

class VariableOperand extends Operand
{
    private $number;

    public function __construct(int $number) {
        $this->number = $number;
    }

    public function getNumber(): int {
        return $this->number;
    }

    public function __toString(): string {
        return 'r' . $this->number;
    }
}
