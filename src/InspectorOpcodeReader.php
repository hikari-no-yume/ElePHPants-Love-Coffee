<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

use Inspector;

class InspectorOpcodeReader implements OpcodeReader
{
    private $seenFiles = [];

    /* FIXME: TODO: Compile in isolated environment.
     * This currently clobbers the function/class table the compiler is
     * running within.
     */
    public function compileFile(string $filePath): OplineArray {
        if (!isset($this->seenFiles[$filePath])) {
            $file = new Inspector\File($filePath);

            $this->seenFiles[$filePath] = self::transmogrifyOpcodes($file, "(null)", $filePath);
        }
        return $this->seenFiles[$filePath];
    }

    public function compileFunctionInFile(string $filePath, string $functionName): OplineArray {
        // This really shouldn't work: see FIXME/TODO above.
        self::compileFile($filePath);

        $ohMyGodJoeReally = "Inspector\Global";
        $function = new $ohMyGodJoeReally($functionName);

        return self::transmogrifyOpcodes($function, $functionName, $filePath);
    }

    private static function transmogrifyOpcodes(Inspector\Scope $scope, string $name, string $filePath): OplineArray {
        $oparray = new OplineArray($name, $filePath, $scope->getLineStart(), $scope->getLineEnd());

        foreach ($scope as $opline) {
            $operands = array_map([$opline, 'getOperand'], [Inspector\Opline::OP1, Inspector\Opline::OP2, Inspector\Opline::RESULT]);
            $operands = array_map(function (Inspector\Operand $operand) {
                // Note that jump target operands are marked as IS_UNUSED
                // So this check MUST precede the ->isUnused() check
                if ($operand->isJumpTarget()) {
                    return new JumpTargetOperand($operand->getNumber());
                } else if ($operand->isUnused()) {
                    return null;
                } else if ($operand->isCompiledVariable()) {
                    return new CompiledVariableOperand($operand->getName());
                /* TODO: Distinguish TMP and VAR for optimisation purposes */
                } else if ($operand->isVariable() || $operand->isTemporaryVariable()) {
                    return new VariableOperand($operand->getNumber());
                } else if ($operand->isConstant()) {
                    return new LiteralOperand($operand->getValue());
                } else {
                    throw new \Exception("Can't handle this kind of operand.");
                }
            }, $operands);
            
            $oparray->addOpline(new Opline(
                $opline->getLine(),
                constant('ajf\ElePHPants_Love_Coffee\\' . $opline->getType()),
                ...$operands
            ));
        }

        return $oparray;
    }
}
