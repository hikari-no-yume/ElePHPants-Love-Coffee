ElePHPants Love Coffee
======================

ElePHPants Love Coffee (ELC, pronounced like "elk") is a compiler, transforming Zend Engine III opcodes directly into JavaScript... in a sense generating a Zend Engine-esque pseudo-VM from a PHP file. It's a horrendous and yet wonderful hack.

Requirements
------------

You need a PHP 7 build, first off. The opcodes this deals with are the Zend Engine III's, not ZE2 or ZE1. Also, the compiler itself uses PHP 7-specific features.

You'll also need either Joe Watkins's [Inspector extension](https://github.com/krakjoe/inspector), so that ElePHPants Love Coffee can get at the opcodes produced by the PHP compiler.

All other dependencies can be gotten from from Composer with `composer install`.

Usage
-----

    $ php src/main.php my_php_file.php

This uses the Inspector extension to dump the opcodes of `my_php_file.php`, attempts to compile them, and spits out JavaScript to standard output.

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

Implementation status
---------------------

### Tests

There aren't any. That's bad.

We should probably have some unit tests for parts of the compiler, and a bunch of integration tests.

We could also use some of the tests from the PHP language specification or the mainline PHP interpreter.

### Supported types

* null
* bool (false/true)
* integer
* float

### Unsupported types

* string
* array
* object
* resource

### Supported opcodes

* `NOP`
* `ECHO` (hacky, implicit newline, assumes `console.log`)
* `INIT_FCALL`, `INIT_FCALL_BY_NAME` (only for global functions known at compile-time)
* `SEND_VAL`, `SEND_VAL_EX`, `SEND_VAR` (no by-reference support)
* `DO_FCALL`, `DO_ICALL`, `DO_UCALL` (no optimisation for F/I/U cases, no by-reference support)
* `RECV` (no by-reference support, no type-checking)
* `IS_SMALLER` (integer/float operands only)
* `SUB`, `MUL` (integer/float operands only)
* `JMP`
* `JMPZ` (null/bool/integer/float operands only)
* `QM_ASSIGN` (probably doesn't implement assignment properly)
* `RETURN` (no by-reference support)

### Unsupported opcodes

Everything else.

Particularly notably, `ASSIGN` is unimplemented, and basic operations are incomplete with no `ASSIGN_`\* versions.

### Supported standard library functions

* `var_dump` (null/bool/integer/float only)

### Unsupported standard library functions

Everything else.

Particularly notably, all math and string functions.

### Other missing features

Pretty much everything, but particularly:

* Compiling multiple files
* PHP 7.1
  * Type-specialised opcodes
* JavaScript interoperability
  * Interacting with the DOM (whether through FFI or otherwise)
* Streams
  * `echo`
* References
* Copy-on-write/reference counting (if needed)
* Type coercion
* Strict typing
* Errors (the `trigger_error` kind)
* Nice error messages
* Namespaces (a function named `foo\bar` probably won't compile)
* Classes
* Reflection