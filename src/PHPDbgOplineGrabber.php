<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

use function theodorejb\polycast\to_int;

class PHPDbgOplineGrabber implements OplineGrabber
{
    private $binaryPath;

    public function __construct(string $binaryPath) {
        $this->binaryPath = $binaryPath;
    }

    public function compileFile(string $filePath): OplineArray {
        exec(
            escapeshellarg($this->binaryPath)
            . ' -p '
            . escapeshellarg($filePath),
            $lines,
            $exit_status
        );
        if ($exit_status) {
            throw new \Exception("Error when running phpdbg and fetching file: " . $filePath);
        }
        
        self::checkInternal($lines);

        $oplines = self::parseOplines($lines);

        return $oplines;
    }

    public function compileFunctionInFile(string $filePath, string $functionName): OplineArray {
        exec(
            escapeshellarg($this->binaryPath)
            . ' -p=' . escapeshellarg($functionName)
            . ' ' . escapeshellarg($filePath),
            $lines,
            $exit_status
        );
        if ($exit_status) {
            throw new \Exception("Error when running phpdbg and fetching function: " . $functionName);
        }
        
        self::checkInternal($lines);

        $oplines = self::parseOplines($lines);

        return $oplines;
    }

    private static function checkInternal(array $lines) /* : void */ {
        if (preg_match('/^\tInternal .+\(\)\s*$/', $lines[1])) {
            throw new \Exception("ElePHPants Love Coffee does not currently support the internal/extension function $function()");
        }
    }

    private static function parseOplines(array $lines): OplineArray {
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

        $oparray = new OplineArray(
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
                    return new VariableOperand(to_int(substr($operandString, 1)));
                } else if ($operandString[0] === "$") {
                    return new CompiledVariableOperand(substr($operandString, 1));
                } else if ($operandString[0] === "J") {
                    return new JumpTargetOperand(to_int(substr($operandString, 1)));
                } else {
                    // assume literal value
                    // dangerous!!!
                    return new LiteralOperand(eval("return ($operandString);"));
                }
            }, $operandStrings);
            
            $oparray->addOpline(new Opline(
                to_int($lineNumber),
                constant('ajf\ElePHPants_Love_Coffee\ZEND_' . $opcodeName),
                ...$operands
            ));
        }

        return $oparray;
    }
}
