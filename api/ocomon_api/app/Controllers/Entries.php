<?php

namespace OcomonApi\Controllers;

use OcomonApi\Core\OcomonApi;
use OcomonApi\Models\Entry;
use stdClass;

class Entries extends OcomonApi
{
    public function __construct()
    {
        parent::__construct();
    }

    
    public function create(array $data): ?Entries
    {

        /* Adicionar também a inserção para notificação dos usuários relacionados como em `insert_comment.php` */
        // $notice_id = $conn->lastInsertId();
        // setUserTicketNotice($conn, 'assentamentos', $notice_id);

        $data = filter_var_array($data, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $numero = (!empty([$data['ticket']]) ? (int)$data['ticket'] : "");
        $comment = (!empty([$data['comment']]) ? $data['comment'] : "");
        $author = $this->user->data()->user_id;
        $commentType = (!empty([$data['comment_type']]) ? $data['comment_type'] : "");
        
        if (empty($numero) || empty($comment) || empty($author) || empty($commentType)) {
            
            $this->call(
                400,
                "invalid_data",
                "Todos os campos precisam ser informados"
            )->back();

            return null;

        }
        
        // $entry = (new Solution())->findById($data['numero']);
        $entry = (new Entry());

        $entry->ocorrencia = $numero;
        $entry->assentamento = $comment;
        $entry->responsavel = $author;
        $entry->tipo_assentamento = $commentType;
        // $entry->{'data'} = date('Y-m-d H:i:s');
        $entry->created_at = date('Y-m-d H:i:s');

        if (!$entry->save()) {

            $this->call(
                400,
                "invalid_data",
                "Erro ao inserir o registro"
            )->back();
            
            return null;
            
        }

        $responseFull = $entry->data();

        $response['tipo_assentamento'] = $responseFull->tipo_assentamento;
        $response['assentamento'] = $responseFull->assentamento;
        $response['numero'] = $responseFull->numero;
        $response['comment'] = $responseFull->assentamento;
        return $this->back($response);
    }

}