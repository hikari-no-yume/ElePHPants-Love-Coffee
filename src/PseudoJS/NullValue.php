<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee\PseudoJS;

class NullValue extends Expression
{
    public function __toString(): string {
        return 'null';
    }
}
