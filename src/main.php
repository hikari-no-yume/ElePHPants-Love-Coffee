<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

require_once __DIR__ . '/../vendor/autoload.php';

if ($argc !== 2) {
    echo "Usage:", PHP_EOL;
    echo "    main.php <infile>", PHP_EOL;
    die();
}

$infile = $argv[1];

$grabber = new InspectorOplineGrabber;

$spider = new Spider($grabber, $infile);

$functions = $spider->spiderFile();

$compiler = new Compiler($functions, "(null)");

echo $compiler->compile();
