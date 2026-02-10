<?php

namespace OcomonApi\Models;

use CoffeeCode\DataLayer\DataLayer;

/**
 * OcoMon Api | Class UsersTicketNotice Active Record Pattern
 *
 * @author Flávio Ribeiro <flaviorib@gmail.com>
 * @package OcomonApi\Models
 */
class UsersTicketNotice extends DataLayer
{
    /**
     * UsersTicketNotice constructor.
     */
    public function __construct()
    {
        parent::__construct("users_tickets_notices", ["source_table", "notice_id"], "id", false);
    }


    public function findByNoticeId(int $notice_id, string $columns = "*"): ?UsersTicketNotice
    {
        return (new UsersTicketNotice())->find("notice_id = :notice_id" ,"notice_id={$notice_id}", $columns)->fetch();
    }




}