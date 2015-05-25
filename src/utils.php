<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

function super_dump($thing, bool $newline = TRUE, int $indent = 0, bool $leadIndent = TRUE) {
    if ($leadIndent) {
        echo str_repeat(' ', $indent);
    }
    switch (gettype($thing)) {
        case 'NULL':
            echo "NULL";
            break;
        case 'boolean':
            echo $thing ? "TRUE" : "FALSE";
            break;
        case 'integer':
            echo $thing;
            break;
        case 'double':
            if (floor($thing) === $thing) {
                echo $thing, '.0';
            } else {
                echo $thing;
            }
            break;
        case 'string':
            var_export($thing);
            break;
        case 'array':
            if (empty($thing)) {
                echo "[]";
            } else {
                echo "[", PHP_EOL;
                foreach ($thing as $key => $value) {
                    super_dump($key, FALSE, $indent + 4);
                    echo " => ";
                    super_dump($value, FALSE, $indent + 4, FALSE);
                    echo ",", PHP_EOL;
                }
                echo str_repeat(' ', $indent), "]";
            }
            break;
        case 'object':
            echo get_class($thing), " {", PHP_EOL;
            // use evil magic to see ALL properties!
            $evilMagic = function () {
                return get_object_vars($this);
            };
            $keys = $evilMagic->call($thing);
            foreach ($keys as $key => $value) {
                echo str_repeat(' ', $indent + 4);
                echo '$', $key, " = ";
                super_dump($value, FALSE, $indent + 4, FALSE);
                echo ";", PHP_EOL;
            }
            echo str_repeat(' ', $indent), "}";
            break;
        default:
            throw new \Exception("super_dump() can't handle " . gettype($thing) . "!");
            break;
    }

    if ($newline) {
        echo PHP_EOL;
    }
}
