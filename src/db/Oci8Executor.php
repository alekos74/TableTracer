<?php

namespace Puc\TableTracer\db;

use Puc\TableTracer\db\DbExecutor;

class Oci8Executor implements DbExecutor
{
    private $dbConn;
    
    public function __construct($conn) {
        $this->dbConn=$conn;
        return $this;
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
        $stid = oci_parse($this->dbConn, 'SELECT 1 FROM '. \strtoupper($tbN). " where rownum<1");
        if(@oci_execute($stid)){
            if(!@\touch(dirname(__FILE__)."/../tracedTabs/". \strtoupper($tbN))){
                \touch(dirname(__FILE__)."/../tracedTabs/". \strtoupper($tbN));
            }
        }else{
            $stid = oci_parse($this->dbConn, "CREATE TABLE ".\strtoupper($tbN)." (
                                ID NUMBER GENERATED ALWAYS AS IDENTITY,
                                DTOP TIMESTAMP NOT NULL,
                                DATALOG CLOB NULL,
                                EXTRADATA CLOB NULL)");
            
            if(@oci_execute($stid)){
                if(!@\touch(dirname(__FILE__)."/../tracedTabs/". \strtoupper($tbN))){
                    $this::createTracedTabDir();
                    \touch(dirname(__FILE__)."/../tracedTabs/". \strtoupper($tbN));
                }
            }else{
                throw new \Exception("Impossibile creare tabella ".\strtoupper($tbN));
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
}
