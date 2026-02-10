<?php 
session_start();


require_once (__DIR__ . "/../include_basics_only.php");
require_once __DIR__ . "/" . "../classes/ConnectPDO.php";
use includes\classes\ConnectPDO;


$contexts = [
    'tickets',
    'assets',
    'assets_models'
];

$globalAccess = false;
$context = (isset($_GET['context']) && in_array($_GET['context'], $contexts) ? $_GET['context'] : 'tickets');
$ticket = (isset($_GET['file']) ? (int)$_GET['file'] : '');
$fileId = (isset($_GET['cod']) && !empty($_GET['cod']) ? (int)$_GET['cod'] : ''); 
$model = (isset($_GET['model']) && !empty($_GET['model']) ? (int)$_GET['model'] : '');
$asset_id = (isset($_GET['asset_id']) && !empty($_GET['asset_id']) ? (int)$_GET['asset_id'] : '');


if ($context != 'tickets' && !isset($_SESSION['s_logado']) || $_SESSION['s_logado'] != 1) {
    exit;
}

if (empty($fileId)) {
    exit;
}


if ($context == 'tickets' && empty($ticket)) {
    exit;
} elseif ($context == 'assets_models' && empty($model)) {
    exit;
} elseif ($context == 'assets' && empty($asset_id)) {
    exit;
}




$id = (isset($_GET['id'])) ? $_GET['id'] : '';

$conn = ConnectPDO::getInstance();


if (!empty($id)) {
    $id = str_replace(" ", "+", (string)$id);
    $id = noHtml($id);

    if (asEquals($id, getGlobalTicketId($conn, $ticket))) {
        $globalAccess = true;
    }
}
if (((!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] != 1) && !$globalAccess)) {
    exit;
}


$isAdmin = (isset($_SESSION['s_nivel']) && $_SESSION['s_nivel'] == 1) ? true : false;

if ($context == 'tickets' && !$globalAccess && !$isAdmin) {
    /* Controle de acesso para arquivos de chamados */

    $ticketData = getTicketData($conn, $ticket, ['sistema', 'aberto_por', 'registration_operator']);

    $isRequester = $ticketData['aberto_por'] == $_SESSION['s_uid'];

    if (!$isRequester) {
        $isBasicUser = ($_SESSION['s_nivel'] == 3) ? true : false;
        $isRegistrationUser = $_SESSION['s_uid'] == $ticketData['registration_operator'];
        
        if ($isBasicUser && !$isRegistrationUser) {
            /* Pode ser gerente de área */
            $managebleAreas = getManagedAreasByUser($conn, $_SESSION['s_uid']);
            $managebleAreas = array_column($managebleAreas, 'sis_id');
            
            $openerArea = getOpenerInfo($conn, $ticketData['aberto_por'])['AREA'];
            $isAreaAdmin = in_array($openerArea, $managebleAreas);
            
            if (!$_SESSION['s_area_admin'] || !in_array($openerArea, $managebleAreas)) {
                echo message('danger', 'Ooops!', TRANS('MSG_NOT_ALLOWED'), '', '', true);
                exit;
            }
        } elseif ($_SESSION['s_nivel'] == 2 && !$isRegistrationUser) {
            /* Usuário com nível de operação */
            $uareas = explode(',', (string)$_SESSION['s_uareas']);
            if (!in_array($ticketData['sistema'], $uareas)) {
                echo message('danger', 'Ooops!', TRANS('MSG_NOT_ALLOWED'), '', '', true);
                exit;
            }
        }
        /* Se for o $isRegistrationUser, pode acessar o conteúdo */
    }
    /* Se for o solicitante, pode acessar o conteúdo */
}

if ($context == 'assets_models' || $context == 'assets') {
    if (!isset($_SESSION['s_invmon']) || !$_SESSION['s_invmon']) {
        exit;
    }
}

if ($context == 'assets') {

    /* Chegar se o usuário logado tem permissão para acessar as informações sobre esse ativo */
	if (!canAccessAssetInfo($conn, $asset_id, $_SESSION['s_allowed_clients'], $_SESSION['s_allowed_units'])) {
		echo message('danger', 'Ooops!', TRANS('MODULE_NOT_ALLOWED_MSG'), '', '', 1);
		return;
	}

    $assetData = getEquipmentInfo($conn, null, null, $asset_id);

    if (empty($assetData)) {
        exit;
    }
    $asset_unit = $assetData['comp_inst'];
    $asset_tag = $assetData['comp_inv'];
}



$contextTerms = [
    'tickets' => 'img_oco = :ticket',
    'assets' => 'img_inst = :unit AND img_inv = :asset_tag',
    'assets_models' => 'img_model = :asset_model'
];

$query = "SELECT 
                img_nome, img_tipo, img_size, img_bin 
            FROM 
                imagens 
            WHERE 
                img_cod = :file_id AND 
                {$contextTerms[$context]}
            LIMIT 1";

try {
    $result = $conn->prepare($query);
    $result->bindValue(':file_id', $fileId, PDO::PARAM_INT);

    if ($context == 'tickets') {
        $result->bindValue(':ticket', $ticket, PDO::PARAM_INT);
    } elseif ($context == 'assets_models') {
        $result->bindValue(':asset_model', $model, PDO::PARAM_INT);
    } elseif ($context == 'assets') {
        $result->bindValue(':unit', $asset_unit, PDO::PARAM_INT);
        $result->bindValue(':asset_tag', $asset_tag, PDO::PARAM_INT);
    }
    $result->execute();

    $row = $result->fetch();

    if ($row) {
        if (ob_get_length()) {
            ob_end_clean(); // Limpa o buffer de saída se houver
        }

        // Enviar os cabeçalhos apropriados para download da imagem
        header("Content-Length: " . $row['img_size']);
        header("Content-Type: " . $row['img_tipo']);
        header("Content-Disposition: attachment; filename=" . $row['img_nome']);
        // Enviar o conteúdo binário da imagem
        echo $row['img_bin'];
    } else {
        echo 'Imagem não encontrada.';
    }
} catch (Exception $e) {
    echo TRANS('MSG_ERR_GET_DATA') . "<br>: " . $e->getMessage();
    return;
}
?>