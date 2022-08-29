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

Utilizzo:


file A.php:


class A {

    use Puc\TableTracer\TableTracerTrait; // trait da usare con connessioni OCI8 standard
    
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

/* ATTENZIONE */

Se un oggetto ha una proprietà con riferimento ad un altro oggetto, 
per loggare i dati dell'oggetto secondario referenziato,
è necessario che anche l'oggetto secondario utilizzi il TRAIT TableTracer.
I dati verranno comunque loggati nella stessa tabella indicata per l'oggetto primario, in formato json innestato
Questo vale anche quando si usa Doctrine 

****************************************************************************


ESEMPIO DOCTRINE:

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