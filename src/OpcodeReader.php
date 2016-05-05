<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

interface OpcodeReader
{
    public function compileFile(string $filePath): OpcodeArray;

    public function compileFunctionInFile(string $filePath, string $functionName): OpcodeArray;
}
