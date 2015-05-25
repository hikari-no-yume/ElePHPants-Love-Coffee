<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

class LiteralOperand extends Operand
{
    private $value;

    public function __construct($value) {
        $this->value = $value;
    }

    public function getValue() {
        return $this->value;
    }
}
