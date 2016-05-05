<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

class JumpTargetOperand extends Operand
{
    private $opcodeIndex;

    public function __construct(int $opcodeIndex) {
        $this->opcodeIndex = $opcodeIndex;
    }

    public function getOpcodeIndex(): int {
        return $this->opcodeIndex;
    }

    public function __toString(): string {
        return (string)$this->opcodeIndex;
    }
}
