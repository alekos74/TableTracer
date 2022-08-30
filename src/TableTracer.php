<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Puc\TableTracer;

/**
 * Description of TableTracer
 *
 * @author ale
 */
class TableTracer {
    //put your code here
    
    public function createTracedTabDir(){
        if(!file_exists(dirname(__FILE__)."/../tracedTabs")){
            mkdir(dirname(__FILE__)."/tracedTabs",0777);
        }
    }
}
