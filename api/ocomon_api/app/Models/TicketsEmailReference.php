<?php

namespace OcomonApi\Models;

use CoffeeCode\DataLayer\DataLayer;

/**
 * OcoMon Api | Class TicketsEmailReference Active Record Pattern
 *
 * @author Flávio Ribeiro <flaviorib@gmail.com>
 * @package OcomonApi\Models
 */
class TicketsEmailReference extends DataLayer
{
    /**
     * TicketsEmailReference constructor.
     */
    public function __construct()
    {
        parent::__construct("tickets_email_references", ["ticket", "references_to"], "id", true);
    }



    public function findByTicket(int $ticket, string $columns = "*"): ?TicketsEmailReference
    {
        return (new TicketsEmailReference())->find("ticket = :ticket" ,"ticket={$ticket}", $columns)->fetch();
    }


}