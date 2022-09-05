<?php

namespace Puc\TableTracer\db;

interface DbExecutor {
    //function checkTracedTab();
    //function checkTableExists();
    function insertTrace($tbN,$data,$extraData);
}
