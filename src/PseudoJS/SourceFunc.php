<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee\PseudoJS;

class SourceFunc extends Func
{
    private $source;

    public function setSource(string $source) /* : void */ {
        $this->source = $source;
    }

    public function getSource(): string {
        return $this->source;
    }

    public function __toString(): string {
        return $this->source;
    }
}
