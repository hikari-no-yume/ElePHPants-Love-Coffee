<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee\PseudoJS;

class ConstructorCall extends Expression
{
    private $constructor;
    private $arguments;

    public function __construct(Expression $constructor, Expression ...$arguments) {
        $this->constructor = $constructor;
        $this->arguments = $arguments;
    }

    public function getConstructor(): Expression {
        return $this->constructor;
    }

    public function getArguments(): array /* <Rvalue> */ {
        return $this->arguments;
    }

    public function __toString(): string {
        return 'new (' . $this->constructor->__toString() . ')(' . implode(", ", array_map(function (Expression $argument): string {
            return $argument->__toString();
        }, $this->arguments)) . ')';
    }
}
