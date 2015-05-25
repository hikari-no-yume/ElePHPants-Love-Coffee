<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

class Spider
{
    private $phpdbg;
    private $filename;
    private $functions;

    public function __construct(PHPDbg $phpdbg, string $filename) {
        $this->phpdbg = $phpdbg;
        $this->filename = $filename;
    }

    private function getFunction(string $function = NULL): OpcodeArray {
        if ($function === NULL) {
            $listing = $this->phpdbg->showFile($this->filename);
        } else {
            $listing = $this->phpdbg->showFileFunction($this->filename, $function);
        }

        if (preg_match('/^\tInternal .+\(\)\s*$/', $listing[1])) {
            throw new \Exception("ElePHPants Love Coffee does not currently support the internal/extension function $function()");
        }

        return parseOpcodes($listing);
    }

    private function spiderFunction(OpcodeArray $oparray) {
        foreach ($oparray as $opcode) {
            if ($opcode->getType() === ZEND_INIT_FCALL) {
                $functionName = $opcode->getOperand2()->getValue();
                if (!isset(PHP_FUNCTIONS[$functionName])) {
                    $function = $this->getFunction($functionName);
                    $this->spiderFunction($function);
                }
            }
        }
        $this->functions[$oparray->getName()] = $oparray;
    }

    public function spiderFile(): array {
        $main = $this->getFunction();

        $this->functions = [];
        $this->spiderFunction($main);

        return $this->functions;
    }
}
