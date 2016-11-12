<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee\PseudoJS;

class AssignmentStatement extends Statement
{
    private $target;
    private $value;

    public function __construct(Lvalue $target, Expression $value) {
        $this->target = $target;
        $this->value = $value;
    }

    public function getTarget(): Lvalue {
        return $this->target;
    }

    public function getValue(): Expression {
        return $this->value;
    }

    public function __toString(): string {
        return $this->target->__toString() . ' = ' . $this->value->__toString() . ';';
    }
}
