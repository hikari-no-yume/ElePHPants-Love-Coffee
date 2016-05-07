<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee\DataStructures;

final class Stack
{
    private $array = [];

    public function push($value) /* : void */ {
        $this->array[] = $value;
    }

    public function pop() /* : mixed */ {
        if (count($this->array) < 1) {
            throw new \RuntimeException("Cannot pop from empty stack");
        }

        return \array_pop($this->array);
    }

    public function height(): int {
        return count($this->array);
    }
}
