<?php session_start();
/*  Copyright 2023 Flávio Ribeiro

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
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";
require_once __DIR__ . "/" . "../../includes/components/dompdf/vendor/autoload.php";


use OcomonApi\Support\Email;
use includes\classes\ConnectPDO;
use Dompdf\Dompdf;


$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2, 1);
$exception = "";
$data = [];
$data['success'] = true;
$mailConfig = getMailConfig($conn);
$data['sendEmailToAuthorizer'] = true;

$post = $_POST;

$info_id = "";
$data['csrf_session_key'] = (isset($post['csrf_session_key']) ? $post['csrf_session_key'] : "");

$data['main_asset_id'] = (isset($post['main_asset_id']) && !empty($post['main_asset_id']) ? (int)$post['main_asset_id'] : []);
$data['asset_tags'] = (isset($post['assetTag']) && !empty($post['assetTag']) ? array_map('noHtml', $post['assetTag']) : []);
$data['asset_units'] = (isset($post['asset_unit']) && !empty($post['asset_unit']) ? array_map('intval', $post['asset_unit']) : []);

$data['carrier'] = (isset($post['carrier']) && !empty($post['carrier']) ? noHtml($post['carrier']) : "");
$data['destination'] = (isset($post['destination']) && !empty($post['destination']) ? noHtml($post['destination']) : "");
$data['authorized_by'] = (isset($post['authorized_by']) && !empty($post['authorized_by']) ? (int)$post['authorized_by'] : "");
$data['responsible_area'] = (isset($post['responsible_area']) && !empty($post['responsible_area']) ? (int)$post['responsible_area'] : "");
$data['reason'] = (isset($post['reason']) && !empty($post['reason']) ? noHtml($post['reason']) : "");

$data['author'] = $_SESSION['s_uid'];
$data['term_id'] = (isset($post['term_id']) && !empty($post['term_id']) ? (int)$post['term_id'] : "");


if (count($data['asset_tags']) != count($data['asset_units'])) {
    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', TRANS('CHECK_FILLED_DATA'),'');

    echo json_encode($data);
    return false;
}

if (empty($data['main_asset_id']) && empty($data['asset_units'])) {
    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', TRANS('AT_LEAST_ONE_ASSET_IS_REQUIRED'),'');
    echo json_encode($data);
    return false;
}

function arrayHasDuplicates($array) {
    return count($array) !== count(array_unique($array));
}

/* Tratamento dos ativos */
$assets_new = [];
$i = 0;
foreach ($data['asset_tags'] as $key => $tag) {
    $assets_new[$i][] = $tag;
    $assets_new[$i][] = $data['asset_units'][$i];
    $i++;
}

$new_asset_ids = [];
foreach ($assets_new as $key => $asset) {
    $new_asset_ids[] = getAssetIdFromTag($conn, $asset[1], $asset[0]);
}

/* Remove valores nulos */
$new_asset_ids = array_filter($new_asset_ids, static function($var){return $var !== null;});
$new_asset_ids = array_merge($new_asset_ids, [$data['main_asset_id']]);

if (arrayHasDuplicates($new_asset_ids)) {
    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', TRANS('MSG_DUPLICATE_RECORD'),'');

    echo json_encode($data);
    return false;
}

$textNewAssetIds = implode(',', $new_asset_ids);


if (empty($data['carrier'])) {
    $data['success'] = false; 
    $data['field_id'] = 'carrier';
    $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
    echo json_encode($data);
    return false;
} elseif (empty($data['destination'])) {
    $data['success'] = false; 
    $data['field_id'] = 'destination';
    $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
    echo json_encode($data);
    return false;
} elseif (empty($data['authorized_by'])) {
    $data['success'] = false; 
    $data['field_id'] = 'authorized_by';
    $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
    echo json_encode($data);
    return false;
} elseif (empty($data['responsible_area'])) {
    $data['success'] = false; 
    $data['field_id'] = 'responsible_area';
    $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
    echo json_encode($data);
    return false;
} elseif (empty($data['reason'])) {
    $data['success'] = false; 
    $data['field_id'] = 'reason';
    $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
    echo json_encode($data);
    return false;
}


$authorInfo = getUserInfo($conn, $_SESSION['s_uid']);
if (empty($authorInfo)) {
    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', TRANS('SOME_ERROR_DONT_PROCEED') . $exception,'');
    echo json_encode($data);
    return false;
}

$authorizerInfo = getUserInfo($conn, $data['authorized_by']);
if (empty($authorizerInfo)) {
    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', TRANS('SOME_ERROR_DONT_PROCEED') . $exception,'');
    echo json_encode($data);
    return false;
}

$areaInfo = getAreaInfo($conn, $data['responsible_area']);
if (empty($areaInfo)) {
    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', TRANS('SOME_ERROR_DONT_PROCEED') . $exception,'');
    echo json_encode($data);
    return false;
}

// var_dump([
//     'post' => $post,
//     'data' => $data,
//     'new_asset_ids' => $new_asset_ids,
//     'textNewAssetIds' => $textNewAssetIds
// ]); exit;


if (!csrf_verify($post, $data['csrf_session_key'])) {
    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');

    echo json_encode($data);
    return false;
}


/**
 * Primeira parte: inserção das informações complementares em assets_traffic_info
 */
$sql = "INSERT 
        INTO
            assets_traffic_info
        (
            carrier,
            reason,
            destination,
            user_authorizer,
            responsible_area,
            author_id
        )
        VALUES
        (
            :carrier,
            :reason,
            :destination,
            :user_authorizer,
            :responsible_area,
            :author_id
        )
";
try {
    $res = $conn->prepare($sql);
    $res->bindParam(':carrier', $data['carrier'], PDO::PARAM_STR);
    $res->bindParam(':reason', $data['reason'], PDO::PARAM_STR);
    $res->bindParam(':destination', $data['destination'], PDO::PARAM_STR);
    $res->bindParam(':user_authorizer', $data['authorized_by'], PDO::PARAM_INT);
    $res->bindParam(':responsible_area', $data['responsible_area'], PDO::PARAM_INT);
    $res->bindParam(':author_id', $data['author'], PDO::PARAM_INT);
    $res->execute();

    $info_id = $conn->lastInsertId();


} catch (\PDOException $e) {
    $data['success'] = false; 
    $exception .= "<hr />" .$e->getMessage();
    $data['message'] = message('warning', 'Ooops!', TRANS('SOME_ERROR_DONT_PROCEED') . $exception,'');
    echo json_encode($data);
    return false;
}



/**
 * Segunda parte, inserção em assets_x_traffic
 */
foreach ($new_asset_ids as $asset_id) {
    $sql = "INSERT INTO assets_x_traffic 
            (
                info_id, 
                asset_id
            ) 
            VALUES 
            (
                :info_id, 
                :asset_id
            )";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':info_id', $info_id);
        $res->bindParam(':asset_id', $asset_id);
        $res->execute();
    } catch (\PDOException $e) {
        $data['success'] = false; 
        $exception .= "<hr />" .$e->getMessage();
        $data['message'] = message('warning', 'Ooops!', TRANS('SOME_ERROR_DONT_PROCEED') . $exception,'');
        echo json_encode($data);
        return false;
    }
}



/** 
 * Terceira parte: identificação e carregamento do modelo de formulário de trânsito
*/

if (!empty($data['term_id'])) {
    $models = getCommitmentModels($conn, $data['term_id'], null, null, null);
} else {
    $models = getCommitmentModels($conn, null, null, null, 2);
}
$trafficTermInfo = (!empty($models) ? $models[0] : []);


/**
 * Quarta parte: Processo para transpor as variáveis de ambiente
 * 
 * %tabela_de_ativos%
 * %portador%
 * %autor%
 * %area_responsavel%
 * %destino%
 * %autorizado_por%
 * %justificativa%
 * %data_e_hora%
 * %data%
 * 
*/


$assetVars = []; // variáveis dinâmicas relacionadas aos ativos presentes no formulário de trânsito

foreach ($new_asset_ids as $asset) {
    $assets_info[] = getAssetBasicInfo($conn, $asset);
}
$assets_info = arraySortByColumn($assets_info, 'tipo_nome', SORT_ASC);


/**
 * Formatação para a geração do PDF
 */
$htmlTable = <<< EOT
<style>
    
    table {
        width: 100%;
        border-collapse: collapse;
    }

    table.term-class {
        width: 100%;
        border: 1px solid #000;
        border-collapse: collapse;
        font-size: 10px;
    }

    thead.term-class {
        font-weight: bold;
    }

    tr.term-class {
        border: 1px solid #000;
    }

    td.term-class {
        padding: 10px;
    }
</style>
EOT;

$htmlTable .= '<table class="term-class">';
$htmlTable .= '<thead class="term-class">';
$htmlTable .= '<tr class="term-class">';
$htmlTable .= '<td class="term-class">' . TRANS('ASSET_TYPE') . '</td>';
$htmlTable .= '<td class="term-class">' . TRANS('SERIAL_NUMBER') . '</td>';
$htmlTable .= '<td class="term-class">' . TRANS('ASSET_TAG_TAG') . '</td>';
$htmlTable .= '<td class="term-class">' . TRANS('COL_UNIT') . '</td>';
$htmlTable .= '<td class="term-class">' . TRANS('CLIENT') . '</td>';
$htmlTable .= '<td class="term-class">' . TRANS('DEPARTMENT') . '</td>';
$htmlTable .= '</tr>';
$htmlTable .= '</thead>';



/**
 * Formatação exclusiva para os emails - Cabeçalhos
 */
$mailTableTdStyle = 'align="center" bgcolor="#dcdcdc" width="600" height="35" style="font-family:Arial;"
';

$mailTable = "";
$mailTable .= '<table border="1" style="border-collapse:collapse;">';
$mailTable .= '<thead>';
$mailTable .= '<tr>';
$mailTable .= '<td ' . $mailTableTdStyle . '><b>' . TRANS('ASSET_TYPE') . '</b></td>';
$mailTable .= '<td ' . $mailTableTdStyle . '><b>' . TRANS('ASSET_TAG_TAG') . '</b></td>';
$mailTable .= '<td ' . $mailTableTdStyle . '><b>' . TRANS('COL_UNIT') . '</b></td>';
$mailTable .= '<td ' . $mailTableTdStyle . '><b>' . TRANS('CLIENT') . '</b></td>';
$mailTable .= '<td ' . $mailTableTdStyle . '><b>' . TRANS('DEPARTMENT') . '</b></td>';
$mailTable .= '</tr>';
$mailTable .= '</thead>';



foreach ($assets_info as $asset) {
    
    $asset_description = $asset['tipo_nome'] . '&nbsp;' . $asset['fab_nome'] . '&nbsp;' . $asset['marc_nome'];

    /** Formatação para o PDF */
    $htmlTable .= '<tr class="term-class">';
    $htmlTable .= '<td class="term-class">' . $asset_description . '</td>';
    $htmlTable .= '<td class="term-class">' . $asset['comp_sn'] . '</td>';
    $htmlTable .= '<td class="term-class">' . $asset['comp_inv'] . '</td>';
    $htmlTable .= '<td class="term-class">' . $asset['inst_nome'] . '</td>';
    $htmlTable .= '<td class="term-class">' . $asset['cliente'] . '</td>';
    $htmlTable .= '<td class="term-class">' . $asset['local'] . '</td>';
    $htmlTable .= '</tr>';



    /** Formatação para o email */
    $mailTable .= '<tr>';
    $mailTable .= '<td ' . $mailTableTdStyle . '>' . $asset_description . '</td>';
    $mailTable .= '<td ' . $mailTableTdStyle . '>' . $asset['comp_inv'] . '</td>';
    $mailTable .= '<td ' . $mailTableTdStyle . '>' . $asset['inst_nome'] . '</td>';
    $mailTable .= '<td ' . $mailTableTdStyle . '>' . $asset['cliente'] . '</td>';
    $mailTable .= '<td ' . $mailTableTdStyle . '>' . $asset['local'] . '</td>';
    $mailTable .= '</tr>';


}
$htmlTable .= '</table>';
$mailTable .= '</table>';

$tableVar['%tabela_de_ativos%'] = $htmlTable;

$singleVars = [];


$singleVars['%portador%'] = $data['carrier'];
$singleVars['%area_responsavel%'] = $areaInfo['area_name'];
$singleVars['%destino%'] = $data['destination'];
$singleVars['%autorizado_por%'] = $authorizerInfo['nome'];
$singleVars['%justificativa%'] = $data['reason'];
$singleVars['%autor%'] = $authorInfo['nome'];
$singleVars['%data_e_hora%'] = date("d/m/Y H:i:s");
$singleVars['%data%'] = date("d/m/Y");

$vars = array_merge($tableVar, $singleVars);

$trafficTermInfo['html_content'] = transvars($trafficTermInfo['html_content'], $vars);


/**
 * Quinta parte: geração do arquivo pdf com base no modelo de formulário de trânsito
 */

$dompdf = new Dompdf();
$dompdf->loadHtml($trafficTermInfo['html_content']);
$dompdf->setPaper('A4', 'portrait');
// Render the HTML as PDF
$dompdf->render();
$output = $dompdf->output();

$dateString = date("ymdHis");
$db_filename = generate_slug(TRANS('TRAFFIC_FORM')) . '_' . $dateString . '.pdf';
$tmp_file_prefix = 'oc_';
$tmp_dir = sys_get_temp_dir();
$tmp_path_and_name = tempnam($tmp_dir, $tmp_file_prefix);

file_put_contents($tmp_path_and_name, $output);
$file_size = filesize($tmp_path_and_name);
$mime_type = "application/pdf";
/* Removendo o arquivo temporário */
unlink($tmp_path_and_name);


/**
 * Sexta parte: Gravar o arquivo no banco de dados
*/

$sql = "INSERT
        INTO
        traffic_files
        (
            info_id,
            file,
            file_name,
            mime_type,
            file_size
        )
        VALUES
        (
            :info_id,
            :file,
            :file_name,
            :mime_type,
            :file_size
        )
";

try {
    $res = $conn->prepare($sql);
    $res->bindParam(':info_id', $info_id);
    $res->bindParam(':file', $output);
    $res->bindParam(':file_name', $db_filename);
    $res->bindParam(':mime_type', $mime_type);
    $res->bindParam(':file_size', $file_size);
    $res->execute();
} catch (\PDOException $e) {
    $data['success'] = false; 
    $exception .= "<hr />" .$e->getMessage();
    $data['message'] = message('warning', 'Ooops!', TRANS('SOME_ERROR_DONT_PROCEED') . $exception,'');
    echo json_encode($data);
    return false;
}



/**
 * Sexta parte: Enviar email para o usuário autorizador
*/

$vars['%tabela_de_ativos%'] = $mailTable;

$mailSendMethod = 'send';
if ($mailConfig['mail_queue']) {
    $mailSendMethod = 'queue';
}

if ($data['sendEmailToAuthorizer']) {
    $event = "traffic-term-to-authorizer";
    $eventTemplate = getEventMailConfig($conn, $event);

    $recipient = $authorizerInfo['email'];

    /* Disparo do e-mail (ou fila no banco) para o usuário */
    $mail = (new Email())->bootstrap(
        transvars($eventTemplate['msg_subject'], $vars),
        transvars($eventTemplate['msg_body'], $vars),
        $recipient,
        $eventTemplate['msg_fromname']
    );

    if (!$mail->{$mailSendMethod}()) {
        $mailNotification .= "<hr>" . TRANS('EMAIL_NOT_SENT') . "<hr>" . $mail->message()->getText();
    }
}


$data['success'] = true; 
$data['message'] = TRANS('MSG_SUCCESSFULLY_GENERATED_TERM');
$_SESSION['flash'] = message('success', '', $data['message'] . $exception, '');

echo json_encode($data);
// dump($return);
return true;

