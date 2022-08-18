<?php 
ini_set('display_errors', true);
error_reporting(E_ALL);
require_once '../vendor/autoload.php';

require_once './A.php';

$a=new A();$a->setA("a")->setB("b");


$dbh= oci_connect('user', 'password','CONN_STRING','AL32UTF8');

$a->trace($dbh,'TT_PUC2',[]);

oci_commit($dbh);


