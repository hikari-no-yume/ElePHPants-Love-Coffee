<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

class OplineArray implements \IteratorAggregate
{
    private $name;
    private $filename;
    private $startLineNumber;
    private $endLineNumber;
    private $oplines = [];

    public function __construct(
        string $name,
        string $filename,
        int $startLineNumber,
        int $endLineNumber,
        array $oplines = []
    ) {
        $this->name = $name;
        $this->filename = $filename;
        $this->startLineNumber = $startLineNumber;
        $this->endLineNumber = $endLineNumber;

        foreach ($oplines as $opline) {
            $this->addOpline($opline);
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

    public function addOpline(Opline $opline) {
        $this->oplines[] = $opline;
    }

    public function getOplines(): array {
        return $this->oplines;
    }

    public function getIterator(): \ArrayIterator {
        return new \ArrayIterator($this->oplines);
    }

    public function __toString(): string {
        $str = $this->name . '() - ' . $this->filename . ':' . $this->startLineNumber . '-' . $this->endLineNumber;
        foreach ($this->oplines as $opline) {
            $str .= "\n" . $opline->__toString();
        }
        return $str;
    }
}
