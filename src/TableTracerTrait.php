<?php

namespace Puc\TableTracer;

use Puc\TableTracer\TableTracer;

trait TableTracerTrait
{
    public function trace($dbConn,$tbN,$extraData){
        $tr=new TableTracer($dbConn,$tbN);
        $tr->trace(get_object_vars($this), $extraData);
    }
}

