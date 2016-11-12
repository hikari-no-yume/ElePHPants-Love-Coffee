<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee\PseudoJS;

class Argument extends Expression
{
    private $index;

    public function __construct(int $index) {
        $this->index = $index;
    }

    public function getIndex(): int {
        return $this->index;
    }

    public function __toString(): string {
        return 'arguments[' . $this->index . ']';
    }
}
