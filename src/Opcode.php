<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

class Opcode
{
    private $lineNumber;
    private $type;
    private $operand1;
    private $operand2;
    private $result;

    public function __construct(
        int $lineNumber,
        int $type,
        Operand $operand1 = NULL,
        Operand $operand2 = NULL,
        Operand $result = NULL
    ) {
        $this->lineNumber = $lineNumber;
        $this->type = $type;
        $this->operand1 = $operand1;
        $this->operand2 = $operand2;
        $this->result = $result;
    }

    public function getLineNumber(): int {
        return $this->lineNumber;
    }

    public function getType(): int {
        return $this->type;
    }

    public function getOperand1()/* : ?Operand */ {
        return $this->operand1;
    }

    public function getOperand2()/* : ?Operand */ {
        return $this->operand2;
    }

    public function getResult()/* : ?Operand */ {
        return $this->result;
    }

    public function __toString(): string {
        $str = 'L' . str_pad((string)$this->lineNumber, 4, '0', STR_PAD_LEFT) . ': ';
        $str .= strtolower(substr(OPCODE_NAMES[$this->type], 5)) . ' ';
        $operands = [];
        if ($this->result)
            $operands[] = $this->result->__toString();
        if ($this->operand1)
            $operands[] = $this->operand1->__toString();
        if ($this->operand2)
            $operands[] = $this->operand2->__toString();
        $str .= implode(', ', $operands);
        return $str;
    }
}
