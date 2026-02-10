<?php
session_start();
require_once (__DIR__ . "/" . "../../includes/include_basics_only.php");
require_once (__DIR__ . "/" . "../../includes/classes/ConnectPDO.php");
use includes\classes\ConnectPDO;

if (!array_key_exists('s_logado', $_SESSION) || $_SESSION['s_logado'] != 1) {
    exit;
}

$conn = ConnectPDO::getInstance();

$dateToday = date('Y-m-d');
$exceptions = "";
$user = $_SESSION['s_uid'];

$data = array();


/** Contagem de termos para serem assinados pelo usuário */
$countTermsToSign = 0;
/** Contagem de termos para serem gerados pelo administrador que alocou ativos aos usuários */
$countTermsToGenerate = 0;
/** Contagem de chamados abertos pelo usuário e que estão aguardando aprovação */
$countTicketsToApprove = 0;
/** Contagem de chamados abertos pelo usuário e que estão aguardando autorização sobre custo */
$countTicketsToAuthorize = 0;
/** Contagem de notificações gerais sobre os chamados abertos pelo usuário */
$countTicketsNotices = 0;
/** Noficação para quando um ativo for alocado para o usuário */
$countOtherNotifications = 0;

$countTermsToSign = (hasToSignTerm($conn, $user) ? 1 : 0);
$countTicketsToApprove = (userHasTicketWaitingToBeRated($conn, $user) ? 1 : 0);

// var_dump(getUserTicketsNotices($conn, $user, true));

$countTicketsNotices = getUserTicketsNotices($conn, $user, true)['notices_count'];

$countOtherNotifications = count(getUnreadUserNotifications($conn, $user));

// var_dump([
//     'countTermsToSign' => $countTermsToSign,
//     'countTicketsNotices' => $countTicketsNotices,
//     'countTermsToGenerate' => $countTermsToGenerate,
//     'countTicketsToApprove' => $countTicketsToApprove,
//     'countTicketsToAuthorize' => $countTicketsToAuthorize
// ]);


$data['notices_count'] =    $countTicketsNotices + 
                            $countTermsToSign + 
                            $countTermsToGenerate + 
                            $countTicketsToApprove + 
                            $countTicketsToAuthorize +
                            $countOtherNotifications;

echo json_encode($data);

?>
