<?php

namespace Puc\TableTracer;

use Puc\TableTracer\TableTracer;

trait TableTracerTrait
{
    private $maxTableTracerNestingLevel=2;
    private $unexploredClasses=['Date','DateTime'];
    
    public function addUnexploredClass($className){
        $this->unexploredClasses[]=$className;
        return $this;
    }
    
    public function setMaxNestingLevel($level){
        $this->maxTableTracerNestingLevel=$level;
        return $this;
    }
    
    public function trace($dbConn,$tbN,$extraData){
        $this->em=$dbConn;
        $tr=new TableTracer($dbConn,$tbN);
        foreach($this->unexploredClasses as $c){
            if($c!='Date' && $c!='DateTime'){
                $tr->addUnexploredClass($c);
            }
        }
        $tr->setMaxNestingLevel($this->maxTableTracerNestingLevel)
                ->trace($dbConn,$tbN, $extraData,$this->getVars($this->maxTableTracerNestingLevel));
    }

    public function getVars($maxNestingLevel=2){
        $this->maxTableTracerNestingLevel=$maxNestingLevel;
        return $this->getObjectVars(0);
    }
    
    public function getObjectVars($level,$obj=null){
        if($obj===null){
            $vars= \get_object_vars($this);
        }
        elseif(is_iterable($obj)){
            $vars=$obj;
        }elseif(\is_object($obj) 
                    && \in_array("Puc\TableTracer\TableTracerTrait",class_uses($obj) ))
        {
            $vars= \get_object_vars($obj);
        }elseif(\is_object($obj))
        {
            $vars=[];
            $properties= \get_object_vars($obj);
            $class = new \ReflectionClass(get_class($obj));
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
                        @$vars[$pn]=$obj->$mn();
                    }catch(\Error $e){}
                }
            }
        }elseif(is_scalar($obj)){
            $vars= [$obj];
        }else{
            $vars=[$obj];
        }
            
        $out=[];
        foreach($vars as $k=>$v){
            if($k!=='maxTableTracerNestingLevel' && $k!=='em' && $k!='unexploredClasses'){
                if(\is_object($v)  
                    && preg_match('/^Proxies/',get_class($v)) 
                    )
                {
                    try{
                        $getter="get".strtoupper($k{0}).substr($k,1);
                        if(strlen($getter)>3){
                            @$t=$this->$getter();
                            //$v=$t;
                            //if(\is_object($t) && preg_match('/^Proxies/',get_class($t))){
                            #    $v = \Doctrine\Common\Util\ClassUtils::getClass($v);
                                try{
                                    //$this->em->refresh($v);//var_dump($v);
                                    //print_r($v);
                                    $t->__load();
                                    $t=$this->getRealEntity($t);//var_dump($v);exit;
                                }catch(\Exception $e){}
                            //}
                            $v=$t;
                        }
                    } catch (Error $ex) {}
                }
                if(\is_object($v) 
                    && $level<$this->maxTableTracerNestingLevel 
                    && \in_array("Puc\TableTracer\DoctrineTableTracerTrait",class_uses($v) )
                    )
                {
                    $out[$k]=$v->getObjectVars($level+1);
                }elseif(\is_object($v) 
                    && $level<$this->maxTableTracerNestingLevel 
                    && !in_array(get_class($v),['Date','DateTime'])
                    )
                {
                    $out[$k]=$this->getObjectVars($level+1,$v);
                }elseif(\is_iterable($v))
                {
                    $out[$k]=$this->getObjectVars($level+1,$v);
                }elseif(is_scalar($v)){
                    $out[$k]=$v;
                }else{
                    $out[$k]=$v;
                }
            }
        }
        return $out; 
    }
    
    public function getRealEntity($proxy) {
        //if ($proxy instanceof Doctrine\ORM\Proxy\Proxy) {print "proxy";
          $metadata  = $this->em->getMetadataFactory()->getMetadataFor(get_class($proxy));
          $class= $metadata->getName();
          $entity    = new $class();
          $reflectionSourceClass = new \ReflectionClass($proxy);
          $reflectionTargetClass = new \ReflectionClass($entity);
          foreach ($metadata->getFieldNames() as $fieldName) { 
            $reflectionPropertySource = $reflectionSourceClass->getProperty($fieldName);
            $reflectionPropertySource->setAccessible(true);
            $reflectionPropertyTarget = $reflectionTargetClass->getProperty($fieldName);
            $reflectionPropertyTarget->setAccessible(true);
            $reflectionPropertyTarget->setValue($entity, $reflectionPropertySource->getValue($proxy));
          }
          return $entity;
        //}
        //return $proxy;
    }

}



