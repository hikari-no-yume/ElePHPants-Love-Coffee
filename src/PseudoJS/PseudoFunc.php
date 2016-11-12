<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee\PseudoJS;

// This would be called “Function”, but that's a reserved word. Ahh, PHP.
class PseudoFunc extends Func implements \IteratorAggregate
{
    private $statements = [];

    public function addInstruction(Statement $statement) {
        $this->statements[] = $statement;
    }

    public function getInstructions(): array {
        return $this->statements;
    }

    public function getIterator(): \ArrayIterator {
        return new \ArrayIterator($this->statements);
    }

    public function __toString(): string {
        $str = 'function ' . $this->name . '() {';
        foreach ($this->statements as $statement) {
            $str .= "\n    " . $statement->__toString();
        }
        $str .= "\n}";
        return $str;
    }
}
