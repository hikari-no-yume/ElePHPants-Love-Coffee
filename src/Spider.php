<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

class Spider
{
    private $reader;
    private $filename;
    private $functions;

    public function __construct(OpcodeReader $reader, string $filename) {
        $this->reader = $reader;
        $this->filename = $filename;
    }

    private function getFunction(string $function = NULL): OplineArray {
        if ($function === NULL) {
            return $this->reader->compileFile($this->filename);
        } else {
            return $this->reader->compileFunctionInFile($this->filename, $function);
        }
    }

    private function spiderFunction(OplineArray $oparray) {
        $this->functions[$oparray->getName()] = $oparray;
        foreach ($oparray as $opline) {
            if ($opline->getType() === ZEND_INIT_FCALL) {
                $functionName = $opline->getOperand2()->getValue();
                if (!isset(PHP_FUNCTIONS[$functionName]) && !isset($this->functions[$functionName])) {
                    $function = $this->getFunction($functionName);
                    $this->spiderFunction($function);
                }
            }
        }
    }

    public function spiderFile(): array {
        $main = $this->getFunction();

        $this->functions = [];
        $this->spiderFunction($main);

        return $this->functions;
    }
}
