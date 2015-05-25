<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

class VariableOperand extends Operand
{
    private $name;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function getName(): string {
        return $this->name;
    }
}
