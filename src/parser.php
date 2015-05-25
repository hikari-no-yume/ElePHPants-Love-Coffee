<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

use function theodorejb\polycast\to_int;

function parseOpcodes(array $lines): OpcodeArray {
    list($nameLine, $fileLine) = $lines;
    $lines = array_slice($lines, 2);

    if (!preg_match('/^function name: (.+)$/', $nameLine, $matches)) {
        throw new \Exception("First line of file does not begin with 'function name:'!");
    }
    $name = $matches[1];

    if (!preg_match('/^L(\d+)-(\d+) .+\(\) (.+) - .+ \+ \d+ ops$/', $fileLine, $matches)) {
        throw new \Exception('Second line of file does not match "L$-$ $() $ - $ + $ ops" pattern!');
    }
    list(, $startLineNumber, $endLineNumber, $filename) = $matches;

    $oparray = new OpcodeArray(
        $name, $filename, to_int($startLineNumber), to_int($endLineNumber)
    );

    foreach ($lines as $i => $line) {
        if (!preg_match('/^ L(\d+)\s+#\d+\s+([A-Z_]+)\s+(\S+)\s+(\S+)\s+(\S+)\s*$/', $line, $matches)) {
            // Some JMP opcodes have empty op2/result
            if (!preg_match('/^ L(\d+)\s+#\d+\s+([A-Z_]+)\s+(\S+)\s*$/', $line, $matches)) {
                throw new \Exception(($i + 3) . 'th line of file does not match " L$ #$ $ $ $ $" or " L$ #$ $" patterns!');
            }
            $matches[4] = $matches[5] = "<unused>";
        }
        list(, $lineNumber, $opcodeName) = $matches;
        $operandStrings = array_slice($matches, 3, 3);
        
        $operands = array_map(function (string $operandString) {
            if ($operandString === "<unused>") {
                return null;
            } else if ($operandString[0] === "@") {
                return new CompiledVariableOperand(to_int(substr($operandString, 1)));
            } else if ($operandString[0] === "$") {
                return new VariableOperand(substr($operandString, 1));
            } else if ($operandString[0] === "J") {
                return new JumpTargetOperand(to_int(substr($operandString, 1)));
            } else {
                // assume literal value
                // dangerous!!!
                return new LiteralOperand(eval("return ($operandString);"));
            }
        }, $operandStrings);
        
        $opcode = new Opcode(
            to_int($lineNumber),
            constant('ajf\ElePHPants_Love_Coffee\ZEND_' . $opcodeName),
            ...$operands
        );
        $opcode->name = $opcodeName;

        $oparray->addOpcode($opcode);
    }

    return $oparray;
}
