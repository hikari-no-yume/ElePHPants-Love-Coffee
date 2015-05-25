<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

require_once __DIR__ . '/../vendor/autoload.php';

if ($argc !== 3) {
    echo "Usage:", PHP_EOL;
    echo "    main.php <path-to-phpdbg> <infile>", PHP_EOL;
    die();
}

$phpdbgPath = $argv[1];
$infile = $argv[2];

$phpdbg = new PHPDbg($phpdbgPath);
$spider = new Spider($phpdbg, $infile);

$functions = $spider->spiderFile();

$compiler = new Compiler($functions, "(null)");

echo $compiler->compile();
