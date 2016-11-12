<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee\PseudoJS;

abstract class Node
{
    public function walk(callable /* (Node) */ $visitor) {
        $visitor($this);
    }
}
