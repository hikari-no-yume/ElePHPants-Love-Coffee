<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee\PseudoJS;

class IfStatement extends Statement
{
    private $condition;
    private $statement;

    public function __construct(Expression $condition, Statement $statement) {
        $this->condition = $condition;
        $this->statement = $statement;
    }

    public function getCondition(): Expression {
        return $this->condition;
    }

    public function getStatement(): Statement {
        return $this->statement;
    }

    public function walk(callable /*(Node)*/ $visitor) {
        Node::walk($visitor);
        $this->condition->walk($visitor);
        $this->statement->walk($visitor);
    }

    public function __toString(): string {
        return 'if (' . $this->condition->__toString() . ') ' . $this->statement->__toString();
    }
}
