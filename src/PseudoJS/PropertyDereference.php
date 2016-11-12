<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee\PseudoJS;

class PropertyDereference extends Lvalue
{
    private $object;
    private $property;

    public function __construct(
        Expression $object,
        string $property
    ) {
        $this->object = $object;
        $this->property = $property;
    }

    public function getObject(): Expression {
        return $this->object;
    }

    public function getProperty(): string {
        return $this->property;
    }

    public function __toString(): string {
        return '(' . $this->object->__toString() . ').' . $this->property;
    }
}
