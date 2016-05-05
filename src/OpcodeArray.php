<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

class OpcodeArray implements \IteratorAggregate
{
    private $name;
    private $filename;
    private $startLineNumber;
    private $endLineNumber;
    private $opcodes = [];

    public function __construct(
        string $name,
        string $filename,
        int $startLineNumber,
        int $endLineNumber,
        array $opcodes = []
    ) {
        $this->name = $name;
        $this->filename = $filename;
        $this->startLineNumber = $startLineNumber;
        $this->endLineNumber = $endLineNumber;

        foreach ($opcodes as $opcode) {
            $this->addOpcode($opcode);
        }
    }

    public function getName(): string {
        return $this->name;
    }

    public function getFilename(): string {
        return $this->filename;
    }

    public function getStartLineNumber(): int {
        return $this->startLineNumber;
    }

    public function getEndLineNumber(): int {
        return $this->endLineNumber;
    }

    public function addOpcode(Opcode $opcode) {
        $this->opcodes[] = $opcode;
    }

    public function getOpcodes(): array {
        return $this->opcodes;
    }

    public function getIterator(): \ArrayIterator {
        return new \ArrayIterator($this->opcodes);
    }

    public function __toString(): string {
        $str = $this->name . '() - ' . $this->filename . ':' . $this->startLineNumber . '-' . $this->endLineNumber;
        foreach ($this->opcodes as $opcode) {
            $str .= "\n" . $opcode->__toString();
        }
        return $str;
    }
}
