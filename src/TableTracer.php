<?php

namespace Puc\TableTracer;

class TableTracer
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
                $sql='insert into '. strtoupper($this->tbN) . " (id,dtop,ipaddr,datalog,extradata) values ("
                        .substr(strtoupper($this->tbN),0,13)."_TT.nextval, "
                        ."sysdate,"
                        .":ipaddr,"
                        .":datalog,"
                        .":extradata"
                        .")";
                $stid = oci_parse($this->dbConn, $sql);
                oci_bind_by_name($stid, ':datalog', $data);
                oci_bind_by_name($stid, ':extradata', $extraData);
                oci_bind_by_name($stid, ':ipaddr', $_SERVER['REMOTE_ADDR']);
                if(@oci_execute($stid)){
                    return true;
                }else{
                    throw new \Exception(\json_encode(oci_error($stid)));
                }
            }
        } catch (Exception $ex) {
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
            } catch (Exception $ex) {
                throw $ex;
            }
        }
    }
    
    private function checkTableExists()
    {
        $stid = oci_parse($this->dbConn, 'SELECT 1 FROM '. \strtoupper($this->tbN). " where rownum<1");
        if(@oci_execute($stid)){
            \touch(dirname(__FILE__)."/tracedTabs/". \strtoupper($this->tbN));
        }else{
            $stid = oci_parse($this->dbConn, "CREATE TABLE ".\strtoupper($this->tbN)." (
                                ID NUMBER NOT NULL,
                                DTOP TIMESTAMP NOT NULL,
                                IPADDR VARCHAR2(30) NULL,
                                DATALOG CLOB NULL,
                                EXTRADATA CLOB NULL)");
            
            if(@oci_execute($stid)){
                $stid = oci_parse($this->dbConn, "CREATE SEQUENCE ".\substr(\strtoupper($this->tbN),0,13)."_TT INCREMENT BY 1 MINVALUE 0 NOCYCLE NOCACHE NOORDER"); 
                if(!@oci_execute($stid)){
                    throw new \Exception("Impossibile creare sequence ".\substr(\strtoupper($this->tbN),0,13)."_TT");
                }else{
                    \touch(\dirname(__FILE__)."/tracedTabs/". \strtoupper($this->tbN));
                }
            }else{
                throw new \Exception("Impossibile creare tabella ".\strtoupper($this->tbN));
            }
        }
    }
}

