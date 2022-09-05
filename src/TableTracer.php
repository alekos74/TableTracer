<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Puc\TableTracer;
use Puc\TableTracer\db\DoctrineExecutor;
use Puc\TableTracer\db\Oci8Executor;

/**
 * Description of TableTracer
 *
 * @author ale
 */
class TableTracer {
    
    private $maxTableTracerNestingLevel=2;
    private $unexploredClasses=['Date','DateTime'];
    
    public function trace($dbConn,$tbN,$extraData,$data=null){
        
        if(is_object($dbConn)){
            if(get_class($dbConn)=="Doctrine\ORM\EntityManager"){
                if($data){
                    $vars=$this->getObjectVars($data, 0, $this->maxTableTracerNestingLevel);
                }else{
                    $vars=$this->getThisObjectVars(0);
                }
                $db=new DoctrineExecutor($dbConn);
                $db->insertTrace($tbN,json_encode($vars),json_encode($extraData));
            }            
        }else{
            if(is_resource($dbConn) && get_resource_type($dbConn)=="oci8 connection"){
                if($data){
                    $vars=$this->getObjectVars($data, 0, $this->maxTableTracerNestingLevel);
                }else{
                    $vars=$this->getThisObjectVars(0);
                }
                $db=new Oci8Executor($dbConn);
                $db->insertTrace($tbN,json_encode($vars),json_encode($extraData));
            }
        }
    }
    
    public function addUnexploredClass($className){
        $this->unexploredClasses[]=$className;
        return $this;
    }
    
    public function setMaxNestingLevel($level){
        $this->maxTableTracerNestingLevel=$level;
        return $this;
    }
    
    public function getObjectVars($data,$level,$maxLevel){
        $out=[];
        if(\is_object($data) 
            && $level<$maxLevel
            && (
                \in_array("Puc\TableTracer\OciTableTracerTrait",class_uses($data) )
                ||
                \in_array("Puc\TableTracer\DoctrineTableTracerTrait",class_uses($data) )
                )
            )
        {
            $out=$data->getObjectVars($level);
        }elseif(\is_object($data) 
            && $level<$maxLevel
            )
        {
            //foreach(get_object_vars($data) as $kk=>$vv){
            //    $out[$kk]=$this->getObjectVars($vv,$level+1,$maxLevel);
            //}
            $vars=[];
            $properties= \get_object_vars($data);
            $class = new \ReflectionClass(get_class($data));
            $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach($properties as $k=>$v){
                $vars[$k]=$v;
            }
            foreach($methods as $m){
                $mn=$m->name;
                if(strlen($mn)>3 && substr($mn, 0,3)=="get"){
                    $pn= substr($mn, 3);
                    $pn{0}= strtolower($pn{0});
                    try{
                        @$vars[$pn]=$data->$mn();
                    }catch(\Error $e){}
                }
            }
            foreach($vars as $kk=>$vv){
                $out[$kk]=$this->getObjectVars($vv,$level+1,$maxLevel);
            }
        }elseif(is_iterable ($data)){
            foreach($data as $kk=>$vv){
                $out[$kk]=$this->getObjectVars($vv,$level+1,$maxLevel);
            }
        }else{
            $out=$data;
        }
        return $out; 
    }
    
    public function getThisObjectVars($level){
        $vars= \get_object_vars($this);
        $out=[];
        foreach($vars as $k=>$v){
            if($k!=='maxTableTracerNestingLevel' && $k!='unexploredClasses'){
                if(\is_object($v) 
                    && $level<$this->maxTableTracerNestingLevel 
                    && \in_array("Puc\TableTracer\OciTableTracerTrait",class_uses($v) )
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
