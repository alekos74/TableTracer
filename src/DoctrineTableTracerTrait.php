<?php

namespace Puc\TableTracer;

use Puc\TableTracer\DoctrineTableTracer;

trait DoctrineTableTracerTrait
{   
    private $maxTableTracerNestingLevel=2;
    
    public function trace($dbConn,$tbN,$extraData){
        $tr=new DoctrineTableTracer($dbConn,$tbN);
        $tr->trace($this->getVars(), $extraData);
    }

    public function getVars($maxNestingLevel=2){
        $this->maxTableTracerNestingLevel=$maxNestingLevel;
        return $this->getObjectVars(0);
    }
    
    public function getObjectVars($level){
        $vars= \get_object_vars($this);
        $out=[];
        foreach($vars as $k=>$v){
            if($k!=='maxTableTracerNestingLevel'){
                if(\is_object($v) 
                    && $level<$this->maxTableTracerNestingLevel 
                    && \in_array("Puc\TableTracer\DoctrineTableTracerTrait",class_uses($v) )
                    )
                {
                    $out[$k]=$v->getObjectVars($level+1);
                }else{
                    $out[$k]=$v;
                }
            }
        }
        return $out; 
    }
}

