<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

use ajf\ElePHPants_Love_Coffee\ZendEngine\OplineArray;

interface OplineGrabber
{
    public function compileFile(string $filePath): OplineArray;

    public function compileFunctionInFile(string $filePath, string $functionName): OplineArray;
}
