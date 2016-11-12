<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee\PseudoJS;

class FunctionCall extends Expression
{
    private $func;
    private $arguments;

    public function __construct(Expression $func, Expression ...$arguments) {
        $this->func = $func;
        $this->arguments = $arguments;
    }

    public function getFunction(): Expression {
        return $this->func;
    }

    public function getArguments(): array /* <Expression> */ {
        return $this->arguments;
    }

    public function walk(callable /*(Node)*/ $visitor) {
        Node::walk($visitor);
        $this->func->walk($visitor);
        foreach ($this->arguments as $argument) {
            $argument->walk($visitor);
        }
    }

    public function __toString(): string {
        return '(' . $this->func->__toString() . ')(' . implode(", ", array_map(function (Expression $argument): string {
            return $argument->__toString();
        }, $this->arguments)) . ')';
    }
}
