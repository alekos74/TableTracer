<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Puc\TableTracer;

/**
 * Description of TableTracer
 *
 * @author ale
 */
class TableTracer {
    //put your code here
    
    public static function createTracedTabDir(){
        if(!file_exists(dirname(__FILE__)."/../tracedTabs")){
            mkdir(dirname(__FILE__)."/tracedTabs");
        }
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
            foreach(get_object_vars($data) as $kk=>$vv){
                $out[$kk]=$this->getObjectVars($vv,$level+1,$maxLevel);
            }
        }elseif(is_array ($data)){
            foreach($data as $kk=>$vv){
                $out[$kk]=$this->getObjectVars($vv,$level+1,$maxLevel);
            }
        }else{
            $out=$data;
        }
        return $out; 
    }
}
