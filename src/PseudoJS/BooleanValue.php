<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee\PseudoJS;

class BooleanValue extends Expression
{
    private $value;

    public function __construct(bool $value) {
        $this->value = $value;
    }

    public function getValue(): bool {
        return $this->value;
    }

    public function __toString(): string {
        return $this->value ? 'true' : 'false';
    }
}
