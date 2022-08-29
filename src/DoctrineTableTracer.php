<?php

namespace Puc\TableTracer;

class DoctrineTableTracer
{
    private $em;
    private $tbN;
    
    public function __construct($em,$tableName) {
        $this->em=$em;
        $this->tbN=$tableName;
    }
    
    public function trace($data,$extraData)
    {
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
        } finally {
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
            } catch (Exception $ex) {
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
            \touch(dirname(__FILE__)."/tracedTabs/". \strtoupper($this->tbN));
        }finally{
            
            $sql="CREATE TABLE ".\strtoupper($this->tbN)." (
                                ID NUMBER GENERATED ALWAYS AS IDENTITY,
                                DTOP TIMESTAMP NOT NULL,
                                DATALOG CLOB NULL,
                                EXTRADATA CLOB NULL)";
            
            try{
                $stmt = $this->em->getConnection()->prepare($sql);
                $stmt->execute();
                \touch(\dirname(__FILE__)."/tracedTabs/". \strtoupper($this->tbN));
            }finally{
                throw new \Exception("Impossibile creare tabella ".\strtoupper($this->tbN));
            }
        }
    }
}

