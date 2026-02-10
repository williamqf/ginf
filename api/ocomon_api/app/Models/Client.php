<?php

namespace OcomonApi\Models;

use CoffeeCode\DataLayer\DataLayer;

/**
 * OcoMon Api | Class Client Active Record Pattern
 *
 * @author Flávio Ribeiro <flaviorib@gmail.com>
 * @package OcomonApi\Models
 */
class Client extends DataLayer
{
    /**
     * Client constructor.
     */
    public function __construct()
    {
        parent::__construct("clients", ["fullname", "is_active"], "id", false);
    }



}