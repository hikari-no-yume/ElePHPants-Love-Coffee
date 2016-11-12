<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

// These are library functions for our generated JS code
// zend_ ones roughly map to functions used in the Zend Engine III's source code
// They are used to implement operators, for example.
// php_ ones are implementations of PHP standard library functions
// These are PHP functions like var_dump() or strlen().
// Each entry has two keys:
// 'require' - an array listing the names of functions this function depends on
// 'source' - the JavaScript source code of the function itself
const ZEND_FUNCTIONS = [
    'zend_compare_function' => [
        'require' => [],
        // TODO: handle strings/arrays/objects
        'source' => <<<'JS'
function zend_compare_function(op1, op2) {
    var diff = op1 - op2;
    return (diff > 0) ? 1 : (diff < 0) ? -1 : 0;
}
JS
    ],
    'zend_sub_function' => [
        'require' => [],
        // TODO: handle strings/arrays/objects
        'source' => <<<'JS'
function zend_sub_function(op1, op2) {
    return op1 - op2;
}
JS
    ],
    'zend_mul_function' => [
        'require' => [],
        // TODO: handle strings/arrays/objects
        'source' => <<<'JS'
function zend_mul_function(op1, op2) {
    return op1 * op2;
}
JS
    ],
    'zend_is_true' => [
        'require' => [],
        // TODO: handle strings/arrays/objects
        'source' => <<<'JS'
function zend_is_true(op) {
    return !!op;
}
JS
    ],
    'php_var_dump' => [
        'require' => [
            'php_var_dump_inner'
        ],
        'source' => <<<'JS'
function php_var_dump() {
    for (var i = 0; i < arguments.length; i++) {
        php_var_dump_inner(arguments[i]);
    }
}
JS
    ],
    'php_var_dump_inner' => [
        'require' => [],
        // TODO: handle strings/arrays/objects
        'source' => <<<'JS'
function php_var_dump_inner(value) {
    if (typeof value === "boolean") {
        console.log("bool(" + value + ")");
    } else if (value === null) {
        console.log("NULL");
    } else if (typeof value === "number") {
        if ((value | 0) === value) {
            console.log("int(" + value + ")");
        } else if (!Number.isFinite(value)) {
            console.log("float(" + (value < 0 ? "-" : "") + "INF)");
        } else if (Number.isNaN(value)) {
            console.log("float(NAN)");
        } else {
            console.log("float(" + value + ")");
        }
    }
}
JS
    ],
];

// PHP standard library functions
// This maps them by their PHP name ('htmlspecialchars') to their name in
// the ZEND_FUNCTIONS array/their name in JavaScript
const PHP_FUNCTIONS = [
    'var_dump' => 'php_var_dump'
];
