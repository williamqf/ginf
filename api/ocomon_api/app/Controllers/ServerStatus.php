<?php

namespace OcomonApi\Controllers;

use OcomonApi\Core\OcomonApi;
use OcomonApi\Models\ViewVariables;

class ServerStatus extends OcomonApi
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * List ServerStatus data
     */
    public function read(): void
    {
        
        $threads = new ViewVariables();
        $connections = $threads->numberOfConnections()->fetch();

        $response = [
            'status' => 'success',
            'message' => 'ServerStatus read successfully.',
            'connections' => $connections->variable_value,
        ];


        $this->back($response);
        return;
    }

}