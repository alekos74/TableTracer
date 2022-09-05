<?php

namespace Puc\TableTracer\db;

use Puc\TableTracer\db\DbExecutor;

class DoctrineExecutor  implements DbExecutor
{
    private $em;
    
    public function __construct($conn) {
        $this->em=$conn;
    }
    
    private function checkTracedTab($tbN)
    {
        if(\file_exists(dirname(__FILE__)."/tracedTabs/". \strtoupper($tbN))){
            return true;
        }else{
            try{
                $this->checkTableExists($tbN);
                return true;
            } catch (\Exception $ex) {
                throw $ex;
            }
        }
    }
    
    private function checkTableExists($tbN)
    {
        $sql='SELECT 1 FROM '. \strtoupper($tbN). " where rownum<1";
        try{
            $stmt = $this->em->getConnection()->prepare($sql);
            $stmt->execute();
            if(!@\touch(dirname(__FILE__)."/../tracedTabs/". \strtoupper($tbN))){
                //$this::createTracedTabDir();
                \touch(dirname(__FILE__)."/../tracedTabs/". \strtoupper($tbN));
            }
        }catch(\Exception $e){
            
            $sql="CREATE TABLE ".\strtoupper($tbN)." (
                                ID NUMBER GENERATED ALWAYS AS IDENTITY,
                                DTOP TIMESTAMP NOT NULL,
                                DATALOG CLOB NULL,
                                EXTRADATA CLOB NULL)";
            
            try{
                $stmt = $this->em->getConnection()->prepare($sql);
                $stmt->execute();
                if(!@\touch(dirname(__FILE__)."/../tracedTabs/". \strtoupper($tbN))){
                    //$this::createTracedTabDir();
                    \touch(dirname(__FILE__)."/../tracedTabs/". \strtoupper($tbN));
                }
            }catch(\Exception $e){
                
                throw $e;
            }
        }
    }
    
    public function insertTrace($tbN,$data,$extraData){
        try{
            if($this->checkTracedTab($tbN)===true){
                $sql='insert into '. strtoupper($tbN) . " (dtop,datalog,extradata) values ("
                        ."sysdate,"
                        .":datalog,"
                        .":extradata"
                        .")";
                $stmt = $this->em->getConnection()->prepare($sql);
                $stmt->execute( array('datalog'=> $data,'extradata'=> $extraData));
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
}