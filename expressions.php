<?php

//this will be the interface
abstract class expression {

    abstract protected function evaluate();
}

//represents an item (in our case a word)
class leaf extends expression {

    private $term;

    public function __construct($term) {
        $this->term = $term;
    }

    public function evaluate() {
        return $this->term["docs"];
    }

}

//represent the not expression
class notEx extends expression {

    protected $op;

    public function __construct(expression $op) {
        $this->op = $op;
    }

    public function evaluate() {
        global $total_docs;
        return array_diff($total_docs, $this->op->evaluate());
    }

}

//represent the and expression
class andEx extends expression {

    protected $left;
    protected $right;

    public function __construct(expression $left, expression $right) {
        $this->left = $left;
        $this->right = $right;
    }

    public function evaluate() {
        return array_intersect($this->left->evaluate(),$this->right->evaluate());
    }

}

//represent the or expression
class orEx extends expression {

    protected $left;
    protected $right;

    function __construct(expression $left, expression $right) {
        $this->left = $left;
        $this->right = $right;
    }

    public function evaluate() {
        return array_merge($this->left->evaluate(),$this->right->evaluate());
    }

}
