<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

use ajf\ElePHPants_Love_Coffee\ZendEngine\OplineArray;

class Spider
{
    private $grabber;
    private $filename;
    private $functions;

    public function __construct(OplineGrabber $grabber, string $filename) {
        $this->grabber = $grabber;
        $this->filename = $filename;
    }

    private function getFunction(string $function = NULL): OplineArray {
        if ($function === NULL) {
            return $this->grabber->compileFile($this->filename);
        } else {
            return $this->grabber->compileFunctionInFile($this->filename, $function);
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
