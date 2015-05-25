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
}
