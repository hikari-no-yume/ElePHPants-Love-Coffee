<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee\PseudoJS;

class ObjectValue extends Expression implements \IteratorAggregate
{
    private $properties = [];

    public function addProperty(string $name, Expression $value) /* : void */ {
        if (isset($this->properties[$name])) {
            throw new \RuntimeException("Property \"$name\" already set");
        }
        $this->properties[$name] = $value;
    }

    public function getIterator(): \Generator {
        foreach ($this->properties as $name => $value) {
            yield (string)$name => $value;
        }
    }

    public function walk(callable /*(Node)*/ $visitor) {
        Node::walk($visitor);
        foreach ($this->properties as $value) {
            $value->walk($visitor);
        }
    }

    public function __toString(): string {
        $str = '{ ';
        foreach ($this->properties as $name => $value) {
            $str .= $name . ': ' . $value->__toString() . ', ';
        }
        $str .= ' }';
        return $str;
    }
}
