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

require __DIR__ . "/" . "../../ocomon/open_tickets_by_email/php-imap/vendor/autoload.php";
require __DIR__ . "/" . "../../ocomon/open_tickets_by_email/config/config.php";

use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\ClientManager;


$post = $_POST;
$exception = "";
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['cod'] = (isset($post['cod']) ? intval($post['cod']) : "");
$data['action'] = $post['action'];
$data['field_id'] = "";

$data['imap_provider_azure'] = (isset($post['imap_provider_azure']) ? ($post['imap_provider_azure'] == "yes" ? 1 : 0) : 0);
$data['mail_account'] = (isset($post['mail_account']) ? noHtml($post['mail_account']) : "");
$data['imap_address'] = (isset($post['imap_address']) ? noHtml($post['imap_address']) : "");
$data['mail_port'] = (isset($post['mail_port']) ? noHtml($post['mail_port']) : "");
$data['ssl_cert'] = (isset($post['ssl_cert']) ? ($post['ssl_cert'] == "yes" ? true : false) : false);



if (!$data['imap_provider_azure']) {
    $data['success'] = false; 
    $data['message'] = message('warning', '', TRANS('ERROR_TEST_ONLY_FOR_OFFICE_365'), '');
    echo json_encode($data);
    return false;
}




if ($data['mail_account'] == "") {
    $data['success'] = false; 
    $data['field_id'] = "mail_account";
} elseif ($data['imap_address'] == "") {
    $data['success'] = false; 
    $data['field_id'] = "imap_address";
} elseif ($data['mail_port'] == "") {
    $data['success'] = false; 
    $data['field_id'] = "mail_port";
}

if ($data['success'] == false) {
    $data['message'] = message('warning', '', TRANS('MSG_EMPTY_DATA'), '');
    echo json_encode($data);
    return false;
}


if (!filter_var($data['mail_account'], FILTER_VALIDATE_EMAIL)) {
    /* FILTER_VALIDATE_DOMAIN */
    $data['success'] = false; 
    $data['field_id'] = "mail_account";
    $data['message'] = message('warning', '', TRANS('WRONG_FORMATTED_URL'), '');
    echo json_encode($data);
    return false;
}

if (!filter_var($data['imap_address'], FILTER_VALIDATE_DOMAIN)) {
    /* FILTER_VALIDATE_DOMAIN */
    $data['success'] = false; 
    $data['field_id'] = "imap_address";
    $data['message'] = message('warning', '', TRANS('WRONG_FORMATTED_URL'), '');
    echo json_encode($data);
    return false;
}

if (!filter_var($data['mail_port'], FILTER_VALIDATE_INT)) {
    /* FILTER_VALIDATE_DOMAIN */
    $data['success'] = false; 
    $data['field_id'] = "mail_port";
    $data['message'] = message('warning', '', TRANS('MSG_ERROR_WRONG_FORMATTED'), '');
    echo json_encode($data);
    return false;
}



$url = "https://login.microsoftonline.com/".IMAP_OAUTH_TENANT_ID."/oauth2/v2.0/token";

$param_post_curl = [
    'client_id' => IMAP_OAUTH_CLIENT_ID,
    'client_secret' => IMAP_OAUTH_CLIENT_SECRET,
    'refresh_token' => IMAP_OAUTH_REFRESH_TOKEN,
    'grant_type' => 'refresh_token'
];

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param_post_curl));
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//ONLY USE CURLOPT_SSL_VERIFYPEER AT FALSE IF YOU ARE IN LOCALHOST !!!
if (isLocalhost()) {
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // NOT IN LOCALHOST ? ERASE IT !
}

$oResult = curl_exec($ch);

if (!empty($oResult)) {

    //The token is a JSON object
    $array_php_resul = json_decode($oResult, true);

    if (isset($array_php_resul["access_token"])) {

        $access_token = $array_php_resul["access_token"];

        //$cm = new ClientManager($options = ["options" => ["debug" => true]]);                     
        $cm = new ClientManager();
        $client = $cm->make([
            'host'          => $data['imap_address'], //outlook.office365.com
            'port'          => $data['mail_port'], //993
            'encryption'    => 'ssl',
            'validate_cert' => $data['ssl_cert'],
            'username'      => $data['mail_account'],
            'password'      => $access_token,
            'protocol'      => 'imap',
            'authentication' => "oauth"
        ]);

        try {
            //Connect to the IMAP Server
            $client->connect();

            $data['success'] = true;
            $data['message'] = message('success', 'Yeaap!', TRANS('CONNECTION_SUCCESS') . $exception, '');
            echo json_encode($data);
            return true;

            // $status = $client->isConnected();
            // if (!$status) {
            //     echo "Not connected";
            //     return;
            // }
        } catch (Exception $e) {

            $exception .= "<hr>" . $e->getMessage();
            $data['success'] = false;
            $data['message'] = message('danger', '', TRANS('CONNECTION_ERROR') . '<hr>' . TRANS('ERROR_CONNECTION_OFFICE_365')  . $exception, '');
            echo json_encode($data);
            return false;
        }

    } else {
        $exception .= "<hr>" . $array_php_resul["error_description"];
        // $exception .= "<hr>" . $e->getMessage();
        $data['success'] = false;
        $data['message'] = message('danger', '', TRANS('CONNECTION_ERROR') . $exception, '');
        echo json_encode($data);
        return false;
    }

} else {
    $data['success'] = false;
    $data['message'] = message('danger', '', TRANS('EMPTY_RESULT_TRY_AGAIN') . $exception, '');
    echo json_encode($data);
    return false;
}