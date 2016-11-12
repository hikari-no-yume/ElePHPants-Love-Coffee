<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee\PseudoJS;

class NumberValue extends Expression
{
    private $value;

    public function __construct(float $value) {
        $this->value = $value;
    }

    public function getValue(): float {
        return $this->value;
    }

    public function __toString(): string {
        if (\is_nan($this->value)) {
            return 'NaN';
        } else if ($this->value === INF) {
            return 'Infinity';
        } else if ($this->value === -INF) {
            return '-Infinity';
        } else {
            return (string)$this->value;
        }
    }
}
