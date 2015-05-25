<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

// These are library functions for our generated JS code
// zend_ ones roughly map to functions used in the Zend Engine III's source code
// php_ ones are implementations of PHP standard library functions
// Each entry has two keys:
// 'require' - an array listing the names of functions this function depends on
// 'source' - the JavaScript source code of the function itself
const ZEND_FUNCTIONS = [
    'zend_compare_function' => [
        'require' => [],
        'source' => 'function zend_compare_function(result, op1, op2) {
    if (!((op1.type === ' . IS_LONG . ' || op1.type === ' . IS_DOUBLE . ')
        && (op2.type === ' . IS_LONG . ' || op2.type === ' . IS_DOUBLE . '))) {
        throw new Error("Can\'t handle non-IS_LONG/IS_DOUBLE op1 and op2");
    }

    var diff = ((op1.type === ' . IS_LONG . ') ? op1.lval : op1.dval)
               - ((op2.type === ' . IS_LONG . ') ? op2.lval : op2.dval);
    result.type = ' . IS_LONG . ';
    result.lval = (diff > 0) ? 1 : (diff < 0) ? -1 : 0;
}'
    ],
    'zend_sub_function' => [
        'require' => [],
        'source' => 'function zend_sub_function(result, op1, op2) {
    if (!((op1.type === ' . IS_LONG . ' || op1.type === ' . IS_DOUBLE . ')
        && (op2.type === ' . IS_LONG . ' || op2.type === ' . IS_DOUBLE . '))) {
        throw new Error("Can\'t handle non-IS_LONG/IS_DOUBLE op1 and op2");
    }

    var resval = ((op1.type === ' . IS_LONG . ') ? op1.lval : op1.dval)
               - ((op2.type === ' . IS_LONG . ') ? op2.lval : op2.dval);
    if (op1.type === ' . IS_LONG . ' && op2.type === ' . IS_LONG . ' && (resval | 0) === resval) {
        result.type = ' . IS_LONG . ';
        result.lval = resval;
    } else {
        result.type = ' . IS_DOUBLE . ';
        result.dval = resval;
    }
}'
    ],
    'zend_mul_function' => [
        'require' => [],
        'source' => 'function zend_mul_function(result, op1, op2) {
    if (!((op1.type === ' . IS_LONG . ' || op1.type === ' . IS_DOUBLE . ')
        && (op2.type === ' . IS_LONG . ' || op2.type === ' . IS_DOUBLE . '))) {
        throw new Error("Can\'t handle non-IS_LONG/IS_DOUBLE op1 and op2");
    }

    var resval = ((op1.type === ' . IS_LONG . ') ? op1.lval : op1.dval)
               * ((op2.type === ' . IS_LONG . ') ? op2.lval : op2.dval);
    if (op1.type === ' . IS_LONG . ' && op2.type === ' . IS_LONG . ' && (resval | 0) === resval) {
        result.type = ' . IS_LONG . ';
        result.lval = resval;
    } else {
        result.type = ' . IS_DOUBLE . ';
        result.dval = resval;
    }
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
            return !!op.lval;
        case ' . IS_DOUBLE . ':
            return !!op.dval;
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
            console.log("int(" + value.lval + ")");
            break;
        case ' . IS_DOUBLE . ':
            if (!Number.isFinite(value.dval)) {
                console.log("float(" + (value.dval < 0 ? "-" : "") + "INF)");
            } else if (Number.isNaN(value.dval)) {
                console.log("float(NAN)");
            } else {
                console.log("float(" + value.dval + ")");
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
