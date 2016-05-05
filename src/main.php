<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

require_once __DIR__ . '/../vendor/autoload.php';

switch ([$argc, $argv[1] ?? '']) {
    case [4, '-phpdbg']:
        $phpdbgPath = $argv[2];
        $infile = $argv[3];

        $grabber = new PHPDbgOplineGrabber($phpdbgPath);
        break;
    case [3, '-inspector']:
        $infile = $argv[2];

        $grabber = new InspectorOplineGrabber;
        break;
    default:
        echo "Usage:", PHP_EOL;
        echo "    Using Inspector extension (https://github.com/krakjoe/inspector):", PHP_EOL;
        echo "        main.php -inspector <infile>", PHP_EOL;
        echo "    Using PHPDbg (hacky):", PHP_EOL;
        echo "        main.php -phpdbg <path-to-phpdbg> <infile>", PHP_EOL;
        die();
}

$spider = new Spider($grabber, $infile);

$functions = $spider->spiderFile();

$compiler = new Compiler($functions, "(null)");

echo $compiler->compile();
