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
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";
require_once __DIR__ . "/" . "../../includes/components/dompdf/vendor/autoload.php";

use OcomonApi\Support\Email;
use includes\classes\ConnectPDO;
use Dompdf\Dompdf;

$conn = ConnectPDO::getInstance();

$post = $_POST;
$now = date('Y-m-d H:i:s');


$hasTerm = true;
$isTermUpdated = true;
$isTermSigned = false;
$signedAt = null;


$exception = "";
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['sendEmailToUser'] = true;
$data['action'] = (isset($post['action']) && !empty($post['action']) ? $post['action'] : "");
$data['user_id'] = (isset($post['user_id']) ? (int)$post['user_id'] : "");

if ($data['action'] == "sign" || $data['action'] == "update_html_doc") {
    $data['user_id'] = $_SESSION['s_uid'];
    $data['sendEmailToUser'] = false;
}

$termInfo = [];
$uploaded_at = $now;
if ($data['action'] == "update_html_doc" || $data['action'] == "sign") {
    $termInfo = getUserLastCommitmentTermInfo($conn, $data['user_id']);

    if (!empty($termInfo) && !empty($termInfo['uploaded_at'])) {
        $uploaded_at = $termInfo['uploaded_at'];
    }
}




$successMsgIdx = ($data['action'] == "sign" ? "SUCCESS_SIGNING_TERM_OF_COMMITMENT" : "SUCCESS_GENERATING_TERM_OF_COMMITMENT");

if (empty($data['user_id'])) {
    $data['success'] = false;
    echo json_encode([]);
    return false;
}

$userInfo = getUserInfo($conn, $data['user_id']);
if (empty($userInfo)) {
    $data['success'] = false;
    echo json_encode([]);
    return false;
}


$termSigned = false;
$termUpdated = false;

if ($data['action'] == "sign") {

    $signature_info = getUserSignatureFileInfo($conn, $data['user_id']);
    if (empty($signature_info)) {
        $data['success'] = false;
        $data['message'] = TRANS('YOU_NEED_TO_DEFINE_SIGNATURE_FIRST');
        $data['message'] = message('warning', 'Ooops!', $data['message'], '');
        echo json_encode($data);
        return false;
    }

    /* Checa se o termo mais recente já está assinado */
    $termSigned = isLastUserTermSigned($conn, $data['user_id']);
    if ($termSigned) {
        $data['success'] = false;
        $data['message'] = TRANS('TERM_IS_ALREADY_SIGNED');
        $data['message'] = message('warning', 'Ooops!', $data['message'], '');
        echo json_encode($data);
        return false;
    }

    $isTermSigned = true;
    $signedAt = $now;

}

$mailConfig = getMailConfig($conn);


/* Se o termo já estiver atualizado, não é necessário gerar novamente */
$termUpdated = isUserTermUpdated($conn, $data['user_id']);




if (!$termUpdated || !$termSigned || $data['action'] == "update_html_doc") {

    $commitmentTermInfo = [];
    
    if (!empty($userInfo['term_unit'])) {
        
        $models = getCommitmentModels($conn, null, $userInfo['term_unit'], null, 1);
        $commitmentTermInfo = (!empty($models) ? $models[0] : []);
    } elseif (!empty($userInfo['user_client'])) {
        $models = getCommitmentModels($conn, null, null, $userInfo['user_client'], 1);
        $commitmentTermInfo = (!empty($models) ? $models[0] : []);
    } else {
        /* Termo de compromisso padrão: código 1 */
        $models = getCommitmentModels($conn, 1, null, null);
        $commitmentTermInfo = (!empty($models) ? $models[0] : []);
    }

    if (empty($commitmentTermInfo)) {
        /* Caso não exista cadastro de termo específico para o cliente do usuário - Termo de compromisso padrão: código 1 */
        $commitmentTermInfo = getCommitmentModels($conn, 1, null, null)[0];
    }

    /**
     * Processo para transpor as variáveis de ambiente
     * 
     * %tabela_de_ativos%
     * %usuario_reponsavel%
     * %data_e_hora%
     * %data%
     * 
     */

    $assetVars = []; // variáveis dinâmicas relacionadas aos ativos vinculados ao usuário

    $user_assets = getAssetsFromUser($conn, $data['user_id']);
    $assets_info = [];
    if (!empty($user_assets)) {
        foreach ($user_assets as $asset) {
            $assets_info[] = getAssetBasicInfo($conn, $asset['asset_id']);
        }
    }

    $assets_info = arraySortByColumn($assets_info, 'tipo_nome', SORT_ASC);

    /** Formatação para o PDF */
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
    $htmlTable .= '<td class="term-class">' . TRANS('ASSET_TAG_TAG') . '</td>';
    $htmlTable .= '<td class="term-class">' . TRANS('COL_UNIT') . '</td>';
    $htmlTable .= '<td class="term-class">' . TRANS('CLIENT') . '</td>';
    $htmlTable .= '<td class="term-class">' . TRANS('DEPARTMENT') . '</td>';
    $htmlTable .= '</thead>';



    /**
     * Formatação exclusiva para os emails - Cabeçalhos
     */
    $mailTableTdStyle = 'align="center" bgcolor="#dcdcdc" width="300" height="35" style="font-family:Arial;"
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
    $singleVars['%usuario_responsavel%'] = $userInfo['nome'];
    // $singleVars['%data_e_hora%'] = date("d/m/Y H:i:s");
    $singleVars['%data_e_hora%'] = dateScreen($uploaded_at);
    // $singleVars['%data%'] = date("d/m/Y");
    $singleVars['%data%'] = dateScreen($uploaded_at, null, 'd/m/Y');

    $signed_at = null;
    if ($data['action'] == 'sign') {
        $signed_at = date("Y-m-d H:i:s");
        $singleVars['%data_assinatura%'] = date("d/m/Y H:i");
        $singleVars['%assinatura%'] = '<img src="' . $signature_info['signature_src'] . '" width="200" />';
    }

    $vars = array_merge($tableVar, $singleVars);

    $commitmentTermInfo['html_content'] = transvars($commitmentTermInfo['html_content'], $vars);

    if (empty($data['action']) || $data['action'] == 'sign') {

        $dompdf = new Dompdf();
        $dompdf->loadHtml($commitmentTermInfo['html_content']);
        $dompdf->setPaper('A4', 'portrait');
        // Render the HTML as PDF
        $dompdf->render();
        $output = $dompdf->output();
    
        // Output the generated PDF to Browser
        // $dompdf->stream();
        // $dompdf->stream('documento.pdf',['Attachment'=>false] );
        // $dompdf->stream('documento.pdf');
    
    
        $slugUserName = generate_slug($userInfo['nome']);
    
        $db_filename = generate_slug(TRANS('COMMITMENT_TERM')) . '_' . $slugUserName . '.pdf';
        $tmp_file_prefix = 'oc_';
        $tmp_dir = sys_get_temp_dir();
        $tmp_path_and_name = tempnam($tmp_dir, $tmp_file_prefix);
    
    
        file_put_contents($tmp_path_and_name, $output);
        /* Trabalhando diretamente sobre o buffer : finfo::buffer */
        // $finfo = new finfo(FILEINFO_MIME);
        // $finfo = new finfo(FILEINFO_EXTENSION);
        // $finfo = new finfo();
        // echo $finfo->buffer($output);
        // exit;
    
        $file_size = filesize($tmp_path_and_name);
        // $mime_type = mime_content_type("{$tmp_path_and_name}");
        $mime_type = "application/pdf";
    
        /* Removendo o arquivo temporário */
        unlink($tmp_path_and_name);

        $sql = "INSERT INTO users_x_files
        (
            user_id,
            file_type,
            html_doc,
            file,
            file_name,
            mime_type,
            file_size,
            uploaded_at,
            signed_at
        )
        VALUES
        (
            {$data['user_id']},
            1,
            :html_doc,
            :file,
            :file_name,
            :mime,
            :file_size,
            :uploaded_at,
            :signed_at
        )
        ";
        try {
            $res = $conn->prepare($sql);
            $res->bindParam(':html_doc', $commitmentTermInfo['html_content']);
            $res->bindParam(':file', $output);
            $res->bindParam(':file_name', $db_filename);
            $res->bindParam(':mime', $mime_type);
            $res->bindParam(':file_size', $file_size);
            $res->bindParam(':uploaded_at', $uploaded_at);
            $res->bindParam(':signed_at', $signed_at);
            $res->execute();

            $data['success'] = true;

            $data['message'] = TRANS($successMsgIdx);
            $data['message'] = message('success', '', $data['message'], '');

            $_SESSION['flash'] = message('success', '', TRANS($successMsgIdx), '', '');

        } catch (\PDOException $e) {
            $data['success'] = false; 
            $data['message'] = message('warning', 'Ooops!', $e->getMessage() . '<hr />' . $sql,'');
            echo json_encode($data);
            return false;
        }
    } elseif ($data['action'] == 'update_html_doc') {
        /* Apenas atualizar a coluna html_doc */
        if (!empty($termInfo) && empty($termInfo['html_doc'])) {

            $sql = "UPDATE users_x_files
            SET
                html_doc = :html_doc
            WHERE
                id = :id
            ";
            try {
                $res = $conn->prepare($sql);
                $res->bindParam(':html_doc', $commitmentTermInfo['html_content']);
                $res->bindParam(':id', $termInfo['id']);
                $res->execute();

                $data['success'] = true;
                $data['message'] = TRANS('HTML_DOC_UPDATED');
                echo json_encode($data);
                return false;

            } catch (\PDOException $e) {
                $data['success'] = false; 
                $data['message'] = message('warning', 'Ooops!', $e->getMessage() . '<hr />' . $sql,'');
                echo json_encode($data);
                return false;
            }
        }

    }

    





    if (empty($data['action'])) {
        /**
        * Enviar email para o usuário responsável pelos ativos registrados
        */

        $vars['%tabela_de_ativos%'] = $mailTable;

        $mailSendMethod = 'send';
        if ($mailConfig['mail_queue']) {
            $mailSendMethod = 'queue';
        }

        if ($data['sendEmailToUser']) {
            $event = "term-to-user";
            $eventTemplate = getEventMailConfig($conn, $event);

            $recipient = $userInfo['email'];

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
    }


    /**
     * Atualização da tabela pivot
     * Bool $hasTerm
     * Bool $isTermUpdated
     * Bool $isTermSigned
     * String $signedAt
     */
    $updatePivotTable = insertOrUpdateUsersTermsPivotTable($conn, $data['user_id'], $isTermUpdated, $isTermSigned, $signedAt);

    echo json_encode($data);
    return true;
}



$data['success'] = false;
$data['message'] = TRANS('TERM_IS_ALREADY_UPDATED');
$data['message'] = message('success', '', $data['message'], '');

echo json_encode($data);
return true;