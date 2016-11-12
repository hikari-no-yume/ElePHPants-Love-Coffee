<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee\PseudoJS;

class ReturnStatement extends Statement
{
    private $value;

    public function __construct(Expression $value) {
        $this->value = $value;
    }

    public function getValue(): Expression {
        return $this->value;
    }

    public function __toString(): string {
        return 'return ' . $this->value->__toString() . ';';
    }
}
