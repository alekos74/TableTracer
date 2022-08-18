<?php 
ini_set('display_errors', true);
error_reporting(E_ALL);
require_once '../vendor/autoload.php';

require_once './A.php';

$a=new A();$a->setA("a")->setB("b");


$dbh= oci_connect('pers_ricerca', 'itai%h3v','(DESCRIPTION =
  (ADDRESS_LIST = (LOAD_BALANCE = ON) (FAILOVER = ON)
   (ADDRESS=(PROTOCOL=tcp)(HOST=cman01-ext.dbc.cineca.it)(PORT=5555))
   (ADDRESS=(PROTOCOL=tcp)(HOST=cman02-ext.dbc.cineca.it)(PORT=5555))
  )
  (CONNECT_DATA = (SERVICE_NAME = miur01_dev.pdv.cineca.it))
)','AL32UTF8');

$a->trace($dbh,'TT_PUC2',[]);

oci_commit($dbh);


