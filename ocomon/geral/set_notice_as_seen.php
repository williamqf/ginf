<?php
session_start();
require_once (__DIR__ . "/" . "../../includes/include_basics_only.php");
require_once (__DIR__ . "/" . "../../includes/classes/ConnectPDO.php");
use includes\classes\ConnectPDO;

if ($_SESSION['s_logado'] != 1) {
    exit;
}

$conn = ConnectPDO::getInstance();

$dateToday = date('Y-m-d');
$exceptions = "";
$user = $_SESSION['s_uid'];

$post = $_POST;

$data = array();
$data['success'] = true;
$data['message'] = "";

$data['table'] = (isset($post['table']) ? noHtml($post['table']) : '');
$data['notice_id'] = (isset($post['notice_id']) ? (int)$post['notice_id'] : '');
$data['ticket'] = (isset($post['ticket']) ? (int)$post['ticket'] : '');

if (empty($data['table']) || empty($data['notice_id'])) {
    $data['success'] = false;
    $data['message'] = message('warning', '', TRANS('MSG_EMPTY_DATA'), '');
    echo json_encode($data);
    return false;
}

if ($data['table'] == 'assentamentos') {

    
    $ticketInfo = getTicketByEntryId($conn, $data['notice_id']);
    
    $isTreater = ($ticketInfo['operador'] == $user ? true : false);
    $isOpener = ($ticketInfo['aberto_por'] == $user ? true : false);
    
    $notice_ids = (!empty($data['ticket']) ? getUnreadNoticesIDsFromTicket($conn, $data['ticket'], $isTreater) : [$data['notice_id']]);
    /* Eventualmente o usuário pode ser o autor e tratador do chamado. 
    Nesses casos preciso chamar duas vezes a função de atualização */
    if ($isTreater) {
        // $successUpdate = setUserTicketNoticeSeen($conn, $data['table'], [$data['notice_id']], $isTreater);
        $successUpdate = setUserTicketNoticeSeen($conn, $data['table'], $notice_ids, $isTreater);
    }

    if ($isOpener) {
        // $successUpdate = setUserTicketNoticeSeen($conn, $data['table'], [$data['notice_id']]);
        $successUpdate = setUserTicketNoticeSeen($conn, $data['table'], $notice_ids);
    }

    
}

if ($data['table'] == 'users_notifications') {
    $successUpdate = setUserNotificationSeen($conn, $data['notice_id']);
}

if (!$successUpdate) {
    $data['success'] = false;
    $data['message'] = message('danger', '', TRANS('SOME_ERROR_DONT_PROCEED'), '');
    echo json_encode($data);
    return false;
}

echo json_encode($data);

?>
