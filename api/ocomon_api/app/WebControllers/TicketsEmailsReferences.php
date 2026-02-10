<?php

namespace OcomonApi\WebControllers;

use OcomonApi\Core\OcomonWeb;
use OcomonApi\Models\TicketsEmailReference;

class TicketsEmailsReferences extends OcomonWeb
{

    public function __construct()
    {
        parent::__construct();
        
    }


    private function list(): Object
    {
        $ticketsEmailsReferences = (new TicketsEmailReference())->find()->fetch(true);

        $return = [];

        /** @var TicketsEmailReference $emailReference */
        foreach ($ticketsEmailsReferences as $emailReference) {

            $return[] = $emailReference->data();
        }

        $return = (Object)$return;
        return $return;
    }


    public function save(array $data): bool
    {
        /* ticket | references_to */
        $data = filter_var_array($data, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $ticket = (isset($data['ticket']) ? $data['ticket'] : "");
        $references_to = (isset($data['references_to']) ? $data['references_to'] : "");


        if (empty($ticket) || empty($references_to)) {
            echo "Dados incompletos";
            return false;
        }

        $find = (new TicketsEmailReference())->findByTicket($ticket);

        if ($find) {
            return false;
        }
            
        $ticketEmailReference = new TicketsEmailReference();
        
        $ticketEmailReference->ticket = $ticket;
        $ticketEmailReference->references_to = $references_to;

        if (!$ticketEmailReference->save()) {
            echo "Problema na tentativa de gravar o registro";
            return false;
        }

        return true;
    }




    // public function create(array $data): bool
    // {
    //     $data = filter_var_array($data, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    //     $now = date("Y-m-d H:i:s");

    //     if (empty($data["fullname"])) {
    //         $this->message = "Nao informou o nome do usuário";
    //         return false;
    //     }

    //     if (empty($data["login"])) {
    //         $this->message = "Nao informou o nome de acesso do usuário";
    //         return false;
    //     }

    //     if (empty($data["email"])) {
    //         $this->message = "Nao informou o email do usuário";
    //         return false;
    //     }

    //     if (empty($data["password"])) {
    //         $this->message = "Nao informou a senha";
    //         return false;
    //     }

    //     if (empty($data["level"])) {
    //         $this->message = "Nao informou o nível";
    //         return false;
    //     }

    //     // if (empty($data["phone"])) {
    //     //     $this->message = "Nao informou o telefone";
    //     //     return false;
    //     // }

    //     if (empty($data["primary_area"])) {
    //         $this->message = "Nao informou a área primária";
    //         return false;
    //     }


    //     $user = new User();
    //     $user->login = $data['login'];
    //     $user->nome = $data['fullname'];
    //     $user->email = $data['email'];
    //     $user->password = pass_hash(md5($data['password']));
    //     $user->data_inc = $now;
    //     $user->fone = $data['phone'] ?? null;
    //     $user->nivel = $data['level'];
    //     $user->AREA = $data['primary_area'];

    //     var_dump($user);
    //     return false;


    // }

}