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
        'source' => 'function zend_compare_function(op1, op2) {
    if (!((op1.type === ' . IS_LONG . ' || op1.type === ' . IS_DOUBLE . ')
        && (op2.type === ' . IS_LONG . ' || op2.type === ' . IS_DOUBLE . '))) {
        throw new Error("Can\'t handle non-IS_LONG/IS_DOUBLE op1 and op2");
    }

    var diff = op1.val - op2.val;
    return {type: ' . IS_LONG . ', val: (diff > 0) ? 1 : (diff < 0) ? -1 : 0};
}'
    ],
    'zend_sub_function' => [
        'require' => [],
        'source' => 'function zend_sub_function(op1, op2) {
    if (!((op1.type === ' . IS_LONG . ' || op1.type === ' . IS_DOUBLE . ')
        && (op2.type === ' . IS_LONG . ' || op2.type === ' . IS_DOUBLE . '))) {
        throw new Error("Can\'t handle non-IS_LONG/IS_DOUBLE op1 and op2");
    }

    var resval = op1.val - op2.val;
    return {
        type: (op1.type === ' . IS_LONG . ' && op2.type === ' . IS_LONG . ' && (resval | 0) === resval) ? ' . IS_LONG . ' : ' . IS_DOUBLE . ',
        val: resval
    };
}'
    ],
    'zend_mul_function' => [
        'require' => [],
        'source' => 'function zend_mul_function(op1, op2) {
    if (!((op1.type === ' . IS_LONG . ' || op1.type === ' . IS_DOUBLE . ')
        && (op2.type === ' . IS_LONG . ' || op2.type === ' . IS_DOUBLE . '))) {
        throw new Error("Can\'t handle non-IS_LONG/IS_DOUBLE op1 and op2");
    }

    var resval = op1.val * op2.val;
    return {
        type: (op1.type === ' . IS_LONG . ' && op2.type === ' . IS_LONG . ' && (resval | 0) === resval) ? ' . IS_LONG . ' : ' . IS_DOUBLE . ',
        val: resval
    };
}'
    ],
    'zend_is_true' => [
        'require' => [],
        'source' => 'function zend_is_true(op) {
    switch(op.type) {
        case ' . IS_UNDEF . ':
        case ' . IS_NULL . ':
        case ' . IS_FALSE . ':
            return false;
        case ' . IS_TRUE . ':
            return true;
        case ' . IS_LONG . ':
        case ' . IS_DOUBLE . ':
            return !!op.val;
        default:
            throw new Error("Can\'t handle non-IS_UNDEF/IS_NULL/IS_FALSE/IS_TRUE/IS_LONG/IS_DOUBLE op");
    }
}'
    ],
    'php_var_dump' => [
        'require' => [
            'php_var_dump_inner'
        ],
        'source' => 'function php_var_dump() {
    for (var i = 0; i < arguments.length; i++) {
        php_var_dump_inner(arguments[i]);
    }
}'
    ],
    'php_var_dump_inner' => [
        'require' => [],
        'source' => 'function php_var_dump_inner(value) {
    switch(value.type) {
        case ' . IS_FALSE . ':
            console.log("bool(false)");
            break;
        case ' . IS_TRUE . ':
            console.log("bool(true)");
            break;
        case ' . IS_NULL . ':
            console.log("NULL");
            break;
        case ' . IS_LONG . ':
            console.log("int(" + value.val + ")");
            break;
        case ' . IS_DOUBLE . ':
            if (!Number.isFinite(value.val)) {
                console.log("float(" + (value.val < 0 ? "-" : "") + "INF)");
            } else if (Number.isNaN(value.val)) {
                console.log("float(NAN)");
            } else {
                console.log("float(" + value.val + ")");
            }
            break;
        default:
            throw new Error("Can\'t handle non-IS_NULL/IS_FALSE/IS_TRUE/IS_LONG/IS_DOUBLE op");
    }
}'
    ],
];

// PHP standard library functions
// This maps them by their PHP name ('htmlspecialchars') to their name in
// the ZEND_FUNCTIONS array/their name in JavaScript
const PHP_FUNCTIONS = [
    'var_dump' => 'php_var_dump'
];
