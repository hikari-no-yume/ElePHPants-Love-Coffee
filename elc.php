<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

require_once __DIR__ . '/vendor/autoload.php';

if ($argc === 2) {
    $infile = $argv[1];
    $dump = false;
} else if ($argc === 3 && $argv[1] === "-dump") {
    $infile = $argv[2];
    $dump = true;
} else {
    echo "Usage:", PHP_EOL;
    echo "    Compile to JS:", PHP_EOL;
    echo "        main.php <infile>", PHP_EOL;
    echo "    Dump opcodes:", PHP_EOL;
    echo "        main.php -dump <infile>", PHP_EOL;
    die();
}

$grabber = new InspectorOplineGrabber;

$spider = new Spider($grabber, $infile);

$functions = $spider->spiderFile();

if ($dump) {
    foreach ($functions as $function) {
        echo $function, PHP_EOL;
    }
} else {
    $pseudoJSCompiler = new ZendToPseudoJSCompiler($functions, "(null)");

    $pseudoJSFunctions = $pseudoJSCompiler->compile();

    $jsCompiler = new PseudoJSToJSCompiler($pseudoJSFunctions);

    echo $jsCompiler->compile();
}
