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


Utilizzo:

tratto dall'esempio in cartella "examples"


file A.php:


class A {
    use Puc\TableTracer\TableTracerTrait;
    
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

$a->trace($dbh,'TT_PUC2',[]);

oci_commit($dbh);

