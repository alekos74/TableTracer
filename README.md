Tracciamento automatizzato dei dati in tabella

- creazione automatica della tabella delle tracce
- tracciamento del dato nella tabella creata


Installazione:

- aggiungere nel file composer.json 

"repositories":[
        {
            "type": "vcs",
            "url": "https://github.com/alekos74/TableTracer.git"
        }
    ]

- da shell di comando:
composer require puc/table-tracer:dev-master
token: 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

IMPORTANTE: dopo l'installazione tramite composer è necessario eseguire uno script presente nel pacchetto 
dalla directory radice del progetto, scrivere il seguente comando:

bash vendor/puc/table-tracer/scripts/post-install.sh


/* ATTENZIONE */

Se un oggetto ha una proprietà con riferimento ad un altro oggetto, 
per loggare i dati dell'oggetto secondario referenziato,
è necessario che anche l'oggetto secondario utilizzi il TRAIT TableTracer.
I dati verranno comunque loggati nella stessa tabella indicata per l'oggetto primario, in formato json innestato
Questo vale anche quando si usa Doctrine 
E' possibile settare il livello di innestamento dei dati loggati. Il default è 2.

****************************************************************************

ESEMPI DI PROGRAMMAZIONE


1) CLASSE GENERICA


file A.php:


class A {

    use Puc\TableTracer\OciTableTracerTrait; // trait da usare con connessioni OCI8 standard
    
    private $a;
    private $b;
    
    public function getA() {
        return $this->a;
    }

    public function getB() {
        return $this->b;
    }

    public function setA($a) {
        $this->a = $a;
        return $this;
    }

    public function setB($b) {
        $this->b = $b;
        return $this;
    }


}


file prova.php:

 
require_once '../vendor/autoload.php';

require_once './A.php';

$a=new A();$a->setA("a")->setB("b");


$dbh= oci_connect('username', 'password','CONN_STRING','AL32UTF8');

$a->trace($dbh,'TRACE_TABLE_NAME',['ip'=>'xxxx','user_id'=>'utente_loggato']);

oci_commit($dbh);




2)CLASSE DOCTRINE:

file A.php:


class A {

    use Puc\TableTracer\DoctrineTableTracerTrait; // trait da usare con Doctrine
    
    private $a;
    private $b;
    
    public function getA() {
        return $this->a;
    }

    public function getB() {
        return $this->b;
    }

    public function setA($a) {
        $this->a = $a;
        return $this;
    }

    public function setB($b) {
        $this->b = $b;
        return $this;
    }


}


file MioController.php:

 

$a=new A();$a->setA("a")->setB("b");


$a->trace($this->getDoctrine()->getManager(),'TRACE_TABLE_NAME',['ip'=>'xxxx','user_id'=>'utente_loggato']);

oci_commit($dbh);


3) TRACCIAMENTO DATI DI VARIABILI CHE NON SONO CLASSI (OCI8)
In questo caso quindi non viene usato il "trait"

<?php 
ini_set('display_errors', true);
error_reporting(E_ALL);
require_once '../vendor/autoload.php';

require_once './A.php';

$a=new A();$a->setA("a")->setB("b");
$c=new stdClass();$c->prop1="prop1";$c->prop2="prop2";
$d=new stdClass();$d->prop1="prop1";$d->c=$c;
$b=['a'=>$a,'b'=>'b','c'=>[1,2,3,4],'d'=>$d];


$dbh= oci_connect('username', 'password','CONN_STRING','AL32UTF8');

$tracer=new Puc\TableTracer\OciTableTracer($dbh, 'TT_PUC1');

$tracer->trace($b,['ip'=>'aaa'],true); //NOTARE IL TERZO PARAMETRO CHE DEVE ESSERE SEMPRE "TRUE" PER IL CASO TRATTATO IN QUESTO ESEMPIO ($b NON È UNA CLASSE)

oci_commit($dbh);

### Questo esempio si riferisce all'uso dei driver Oci8. 
### Il caso doctrine è identico ma al posto della connessione oci viene passato l'oggetto EntityManager

4) TRACCIAMENTO DATI DI VARIABILI CHE NON SONO CLASSI (DOCTRINE)
In questo caso quindi non viene usato il "trait"

<?php 
ini_set('display_errors', true);
error_reporting(E_ALL);
require_once '../vendor/autoload.php';

require_once './A.php';

$a=new A();$a->setA("a")->setB("b");
$c=new stdClass();$c->prop1="prop1";$c->prop2="prop2";
$d=new stdClass();$d->prop1="prop1";$d->c=$c;
$b=['a'=>$a,'b'=>'b','c'=>[1,2,3,4],'d'=>$d];


$dbh= oci_connect('username', 'password','CONN_STRING','AL32UTF8');

$tracer=new Puc\TableTracer\DoctrineTableTracer($this->getDoctrine()->getManager(), 'TT_PUC1');

$tracer->trace($b,['ip'=>'aaa'],true); //NOTARE IL TERZO PARAMETRO CHE DEVE ESSERE SEMPRE "TRUE" PER IL CASO TRATTATO IN QUESTO ESEMPIO ($b NON È UNA CLASSE)

oci_commit($dbh);