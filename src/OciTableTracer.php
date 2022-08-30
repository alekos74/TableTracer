<?php

namespace Puc\TableTracer;
use Puc\TableTracer\TableTracer;

class OciTableTracer extends TableTracer
{
    private $dbConn;
    private $tbN;
    
    public function __construct($dbConn,$tableName) {
        $this->dbConn=$dbConn;
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
                $stid = oci_parse($this->dbConn, $sql);
                oci_bind_by_name($stid, ':datalog', $data);
                oci_bind_by_name($stid, ':extradata', $extraData);
                if(@oci_execute($stid)){
                    return true;
                }else{
                    throw new \Exception(\json_encode(oci_error($stid)));
                }
            }
        } catch (\Exception $ex) {
            throw $ex;
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
        $stid = oci_parse($this->dbConn, 'SELECT 1 FROM '. \strtoupper($this->tbN). " where rownum<1");
        if(@oci_execute($stid)){
            if(!@\touch(dirname(__FILE__)."/tracedTabs/". \strtoupper($this->tbN))){
                $this::createTracedTabDir();
                \touch(dirname(__FILE__)."/tracedTabs/". \strtoupper($this->tbN));
            }
        }else{
            $stid = oci_parse($this->dbConn, "CREATE TABLE ".\strtoupper($this->tbN)." (
                                ID NUMBER GENERATED ALWAYS AS IDENTITY,
                                DTOP TIMESTAMP NOT NULL,
                                DATALOG CLOB NULL,
                                EXTRADATA CLOB NULL)");
            
            if(@oci_execute($stid)){
                if(!@\touch(dirname(__FILE__)."/tracedTabs/". \strtoupper($this->tbN))){
                    $this::createTracedTabDir();
                    \touch(dirname(__FILE__)."/tracedTabs/". \strtoupper($this->tbN));
                }
            }else{
                throw new \Exception("Impossibile creare tabella ".\strtoupper($this->tbN));
            }
        }
    }
}

