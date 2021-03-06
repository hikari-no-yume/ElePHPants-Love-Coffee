<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee\ZendEngine;

class CompiledVariableOperand extends Operand
{
    private $name;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function getName(): string {
        return $this->name;
    }

    public function __toString(): string {
        return '$' . $this->name;
    }
}
