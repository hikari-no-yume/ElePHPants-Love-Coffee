<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee\DataStructures;

final class StringSet implements \IteratorAggregate
{
    private $array = [];

    public function __construct(string ...$values) {
        foreach ($values as $value) {
            $this->add($value);
        }
    }

    public function add(string $value) /* : void */ {
        $this->array[$value] = TRUE;
    }

    public function has(string $value): bool {
        return isset($this->array[$value]);
    }

    public function isEmpty(): bool {
        return empty($this->array);
    }

    public function getIterator(): \Generator {
        foreach ($this->array as $key => $value) {
            yield (string)$key;
        }
    }
}
