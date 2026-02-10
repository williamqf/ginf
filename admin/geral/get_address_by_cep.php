<?php session_start();
/*      Copyright 2023 Flávio Ribeiro

        This file is part of OCOMON.

        OCOMON is free software; you can redistribute it and/or modify
        it under the terms of the GNU General Public License as published by
        the Free Software Foundation; either version 3 of the License, or
        (at your option) any later version.
        OCOMON is distributed in the hope that it will be useful,
        but WITHOUT ANY WARRANTY; without even the implied warranty of
        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
        GNU General Public License for more details.

        You should have received a copy of the GNU General Public License
        along with Foobar; if not, write to the Free Software
        Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
    $_SESSION['session_expired'] = 1;
    echo "<script>top.window.location = '../../index.php'</script>";
    exit;
}

require_once __DIR__ . "/" . "../../includes/include_basics_only.php";
require __DIR__ . '/' . '../../includes/components/brasilapi-php/vendor/autoload.php';


use BrasilApi\Client;
use BrasilApi\Exceptions\BrasilApiException;


$brasilApi = new Client();

$post = $_POST;

$exception = "";
$screenNotification = "";
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['field_id'] = "";
$data['cep'] = (isset($post['cep']) && !empty($post['cep']) ? noHtml($post['cep']) : "");
$data['cep_mask'] = "^\d{5}-\d{3}$";


/* Validações */
if (empty($data['cep'])) {
    return json_encode([]);
}


if (!preg_match('/' . $data['cep_mask'] . '/i', (string)$data['cep'])) {
    $data['success'] = false; 
    $data['field_id'] = "unit_cep";

    $data['message'] = message('warning', 'Ooops!', TRANS('BAD_FIELD_FORMAT'),'');
    echo json_encode($data);
    return false;
}


if (!empty($data['cep'])) {
    try {
        /* cep | state | city | neighborhood | street | service */
        $data[] = $brasilApi->cep()->get($data['cep']);
    
    } catch (BrasilApiException $e) {
        
        $data['success'] = false;
        $data['field_id'] = "unit_cep";
        $data['message'] = message('warning', 'Ooops!', $e->getMessage() . '<hr />' . TRANS('CHECK_IF_CEP_IS_VALID'),'');
        
        // var_dump( $e->getMessage() ); // Retorna a mensagem de erro da API
        // var_dump( $e->getCode() ); // Retorna o código HTTP da API
        //var_dump( $e->getErrors() ); // Retorna os erros retornados pela API
        // var_dump( $e->getRawResponse() ); // Retorna a resposta bruta da API
    }
}



echo json_encode($data);