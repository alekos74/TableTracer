<?php

namespace Puc\TableTracer\db;

use Puc\TableTracer\db\DbExecutor;

class DoctrineExecutor  implements DbExecutor
{
    private $em;
    
    public function __construct($conn) {
        $this->em=$conn;
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
