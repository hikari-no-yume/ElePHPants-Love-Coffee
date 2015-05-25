ElePHPants Love Coffee
======================

ElePHPants Love Coffee (ELC, pronounced like "elk") is a compiler, transforming Zend Engine III opcodes directly into JavaScript... in a sense generating a Zend Engine-esque pseudo-VM from a PHP file. It's a horrendous and yet wonderful hack.

Requirements
------------

You need a PHP 7 build, first off. The opcodes this deals with are the Zend Engine III's, not ZE2 or ZE1. Also, the compiler itself uses PHP 7-specific features.

You need both PHP 7's CLI sapi (your usual `php` command), and also `phpdbg`, which ElePHPants Love Coffee uses to read opcodes.

Install dependencies from npm with `npm install`.

Usage
-----

    $ php src/main.php path/to/phpdbg my_php_file.php

This uses `path/to/phpdbg` to dump the opcodes of `my_php_file.php`, attempts to compile them, and spits out JavaScript to standard output.

Want some code to try out? Here:

```php
<?php
declare(strict_types=1);

function factorial(int $n) {
    return ($n > 1
        ? $n * factorial($n - 1)
        : 1);
}

var_dump(factorial(7), factorial(15));
```

Actually, that's the only code that will work, at least currently, because ELC only implements the opcodes needed for that code snippet.

License
-------

ElePHPants Love Coffee is licensed under the PHP and Zend licenses except as otherwise noted.

This is to avoid copyright issues with code similarities to the Zend Engine and PHP.
