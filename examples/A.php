<?php

class A {
    use Puc\TableTracer\TableTracerTrait;
    
    private $a;
    private $b;
    
    public function getA() {
        return $this->a;
    }

    public function getB() {
        return $this->b;
    }

    public function setA($a) {
        $this->a = $a;
        return $this;
    }

    public function setB($b) {
        $this->b = $b;
        return $this;
    }


}
