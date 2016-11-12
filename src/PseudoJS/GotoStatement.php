<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee\PseudoJS;

class GotoStatement extends Statement
{
    private $label;

    public function __construct(int $label) {
        $this->label = $label;
    }

    public function getLabel(): int {
        return $this->label;
    }

    public function __toString(): string {
        return 'goto label' . $this->label . ';';
    }
}
