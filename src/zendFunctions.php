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
    'zend_long' => [
        'require' => [],
        'source' => <<<'JS'
function zend_long(lval) {
    this.val = lval;
}
JS
    ],
    'zend_double' => [
        'require' => [],
        'source' => <<<'JS'
function zend_double(dval) {
    this.val = dval;
}
JS
    ],
    'zend_compare_function' => [
        'require' => ['zend_long', 'zend_double'],
        'source' => <<<'JS'
function zend_compare_function(op1, op2) {
    if (!((op1 instanceof zend_long || op1 instanceof zend_double)
        && (op2 instanceof zend_long || op2 instanceof zend_double))) {
        throw new Error("Can\'t handle non-IS_LONG/IS_DOUBLE op1 and op2");
    }

    var diff = op1.val - op2.val;
    return new zend_long((diff > 0) ? 1 : (diff < 0) ? -1 : 0);
}
JS
    ],
    'zend_sub_function' => [
        'require' => ['zend_long', 'zend_double'],
        'source' => <<<'JS'
function zend_sub_function(op1, op2) {
    if (!((op1 instanceof zend_long || op1 instanceof zend_double)
        && (op2 instanceof zend_long || op2 instanceof zend_double))) {
        throw new Error("Can\'t handle non-IS_LONG/IS_DOUBLE op1 and op2");
    }

    var resval = op1.val - op2.val;
    if (op1 instanceof zend_long && op2 instanceof zend_long && (resval | 0) === resval) {
        return new zend_long(resval);
    } else {
        return new zend_double(resval);
    }
}
JS
    ],
    'zend_mul_function' => [
        'require' => ['zend_long', 'zend_double'],
        'source' => <<<'JS'
function zend_mul_function(op1, op2) {
    if (!((op1 instanceof zend_long || op1 instanceof zend_double)
        && (op2 instanceof zend_long || op2 instanceof zend_double))) {
        throw new Error("Can\'t handle non-IS_LONG/IS_DOUBLE op1 and op2");
    }

    var resval = op1.val * op2.val;
    if (op1 instanceof zend_long && op2 instanceof zend_long && (resval | 0) === resval) {
        return new zend_long(resval);
    } else {
        return new zend_double(resval);
    }
}
JS
    ],
    'zend_is_true' => [
        'require' => ['zend_long', 'zend_double'],
        'source' => <<<'JS'
function zend_is_true(op) {
    if (op === undefined || op === null || op === false || op === true) {
        return !!op;
    } else if (op instanceof zend_long || op instanceof zend_double) {
        return !!op.val;
    } else {
        throw new Error("Can\'t handle non-IS_UNDEF/IS_NULL/IS_FALSE/IS_TRUE/IS_LONG/IS_DOUBLE op");
    }
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
        'require' => ['zend_long', 'zend_double'],
        'source' => <<<'JS'
function php_var_dump_inner(value) {
    if (value === false) {
        console.log("bool(false)");
    } else if (value === true) {
        console.log("bool(true)");
    } else if (value === null) {
        console.log("NULL");
    } else if (value instanceof zend_long) {
        console.log("int(" + value.val + ")");
    } else if (value instanceof zend_double) {
        if (!Number.isFinite(value.val)) {
            console.log("float(" + (value.val < 0 ? "-" : "") + "INF)");
        } else if (Number.isNaN(value.val)) {
            console.log("float(NAN)");
        } else {
            console.log("float(" + value.val + ")");
        }
    } else {
        throw new Error("Can\'t handle non-IS_NULL/IS_FALSE/IS_TRUE/IS_LONG/IS_DOUBLE op");
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
