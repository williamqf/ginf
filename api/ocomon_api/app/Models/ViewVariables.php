<?php

namespace OcomonApi\Models;

use CoffeeCode\DataLayer\DataLayer;


/**
 * OcoMon Api | Class ViewVariables | Active Record Pattern
 *
 * @author Flávio Ribeiro <flaviorib@gmail.com>
 * @package OcomonApi\Models
 */
class ViewVariables extends DataLayer
{
    /**
     * ViewVariables constructor.
     */
    public function __construct()
    {
        parent::__construct("vw_variables", [], "variable_name", false);
    }

    
    public function numberOfConnections(): ViewVariables
    {
        $key = "THREADS_CONNECTED";
        return (new ViewVariables())->find("variable_name = :key", "key={$key}");
    }


    
    
    
}
