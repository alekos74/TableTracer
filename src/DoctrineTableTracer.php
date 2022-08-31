<?php

namespace Puc\TableTracer;
use Puc\TableTracer\TableTracer;

class DoctrineTableTracer extends TableTracer
{
    private $em;
    private $tbN;
    private $maxTableTracerNestingLevel=2;
    
    public function __construct($em,$tableName) {
        $this->em=$em;
        $this->tbN=$tableName;
    }
    
    public function setMaxTableTracerNestingLevel($level){
        $this->maxTableTracerNestingLevel=$level;
    }
    
    public function trace($data,$extraData,$forceDecoding=false)
    {
        if($forceDecoding){
            $data= parent::getObjectVars($data,0,$this->maxTableTracerNestingLevel);
        }
        $data= json_encode($data);
        $extraData= \json_encode($extraData);
        try{
            if($this->checkTracedTab()===true){
                $sql='insert into '. strtoupper($this->tbN) . " (dtop,datalog,extradata) values ("
                        ."sysdate,"
                        .":datalog,"
                        .":extradata"
                        .")";

                $stmt = $this->em->getConnection()->prepare($sql);
                $stmt->execute(['datalog'=>$data,'extradata'=>$extraData]);

                return true;
            }
        } catch(\Exception $e) {
            throw new  \Exception("Errore inserimento in ".strtoupper($this->tbN));
        }
    }
    
    private function checkTracedTab()
    {
        if(\file_exists(dirname(__FILE__)."/tracedTabs/". \strtoupper($this->tbN))){
            return true;
        }else{
            try{
                $this->checkTableExists();
                return true;
            } catch (\Exception $ex) {
                throw $ex;
            }
        }
    }
    
    private function checkTableExists()
    {
        $sql='SELECT 1 FROM '. \strtoupper($this->tbN). " where rownum<1";
        try{
            $stmt = $this->em->getConnection()->prepare($sql);
            $stmt->execute();
            if(!@\touch(dirname(__FILE__)."/tracedTabs/". \strtoupper($this->tbN))){
                $this::createTracedTabDir();
                \touch(dirname(__FILE__)."/tracedTabs/". \strtoupper($this->tbN));
            }
        }catch(\Exception $e){
            
            $sql="CREATE TABLE ".\strtoupper($this->tbN)." (
                                ID NUMBER GENERATED ALWAYS AS IDENTITY,
                                DTOP TIMESTAMP NOT NULL,
                                DATALOG CLOB NULL,
                                EXTRADATA CLOB NULL)";
            
            try{
                $stmt = $this->em->getConnection()->prepare($sql);
                $stmt->execute();
                if(!@\touch(dirname(__FILE__)."/tracedTabs/". \strtoupper($this->tbN))){
                    $this::createTracedTabDir();
                    \touch(dirname(__FILE__)."/tracedTabs/". \strtoupper($this->tbN));
                }
            }catch(\Exception $e){
                throw $e;
            }
        }
    }
}

