<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

class JumpTargetOperand extends Operand
{
    private $oplineIndex;

    public function __construct(int $oplineIndex) {
        $this->oplineIndex = $oplineIndex;
    }

    public function getOplineIndex(): int {
        return $this->oplineIndex;
    }

    public function __toString(): string {
        return (string)$this->oplineIndex;
    }
}
