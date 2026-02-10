<?php session_start();
/*   
	Copyright 2023 Flávio Ribeiro

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


require_once __DIR__ . "/" . "../../includes/include_geral_new.inc.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$configExt = getConfigValues($conn);
$GLOBALACCESS = false;


/** 
 * Destruo a sessão pois as ações possíveis desse script não devem sofrer os controles padrão em função do usuário logado
*/
unset($_SESSION);
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"],$params["httponly"]);
}
session_destroy();

// if (isset($_SESSION['s_logado']) && $_SESSION['s_logado'] == 1) {
//     echo "<script>top.window.location = '../../login.php'</script>";
//     exit();
// }


if (!isset($_GET['numero']) || !isset($_GET['id'])) {
    echo "<script>top.window.location = '../../login.php'</script>";
    exit();
}

$numero = (int)$_GET['numero'];
$id = $_GET['id'];
$id = str_replace(" ", "+", $id);
$id = noHtml($id);

// var_dump([
//     'id' => $id,
//     'getGlobalTicketId' => getGlobalTicketId($conn, $numero),
//     'asEquals()' => asEquals($id, getGlobalTicketId($conn, $numero)),
//     '$_GET[rating_id]' => $_GET['rating_id'],

// ]); exit;



if (asEquals($id, getGlobalTicketId($conn, $numero))) {
    $GLOBALACCESS = true;
} else {
    echo "<script>top.window.location = '../../login.php'</script>";
    exit();
}

/** 
 * Rating_id: possibilitará que o chamado seja avaliado
*/
$rating_id = "";
if (isset($_GET['rating_id']) && !empty($_GET['rating_id'])) {
    $rating_id = noHtml(str_replace(" ", "+", $_GET['rating_id']));
}

$config = getConfig($conn);

$onlyBusinessDays = ($config['conf_only_weekdays_to_count_after_done'] ? true : false);


/* Para manter a compatibilidade com versões antigas */
$table = getTableCompat($conn);

if (!isset($_POST['submit']) || empty($_POST)) {
?>
    <!DOCTYPE html>
    <html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= TRANS('TICKET_OPENING'); ?></title>

        <link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
        <link rel="stylesheet" type="text/css" href="../../includes/css/switch_radio.css" />

        <link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
        <link rel="stylesheet" type="text/css" href="../../includes/components/datatables/datatables.min.css" />
        <link rel="stylesheet" type="text/css" href="../../includes/css/my_datatables.css" />
        <link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
        <link rel="stylesheet" type="text/css" href="../../includes/css/util.css" />
    	<link rel="stylesheet" type="text/css" href="../../includes/css/estilos_custom.css" />

        <link rel="shortcut icon" href="../../includes/icons/favicon.webp">

        <style>
            .container-mt {
                margin-top: 70px;
                margin-bottom: 50px;
            }

            .container-message {
                margin-top: 50px;
                /* margin-bottom: 50px; */
            }

            .navbar-nav>.nav-link:hover {
                background-color: #3a4d56 !important;
            }

            .nav-pills>li>a.active {
                /* background-color: #6c757d !important; */
                background-color: #48606b !important;
            }

            .navbar-nav i {
                margin-right: 3px;
                font-size: 12px;
                width: 20px;
                height: 20px;
                line-height: 20px;
                text-align: center;
                -ms-flex-negative: 0;
                flex-shrink: 0;
                /* background-color: #3a4d56; */
                border-radius: 4px;
            }
        </style>

    </head>

    <body>

        <div class="topo topo-color fixed-top p-2">
            <div id="header_logo">
                <!-- <span class="logo"><img src="../../MAIN_LOGO.svg" width="240"></span> -->
                <span class="logo header-mainlogo"></span>
            </div>
            <div id="header_elements" class="fs-13">
                <span class="font-weight-bold d-none d-sm-block"> <?= TRANS('USER_NOT_LOGGED') . "&nbsp;&nbsp;|&nbsp;&nbsp;"; ?>
                    <a class="topo-color fs-18" title="<?= TRANS('ENTER_IN'); ?>" href="../../index.php" data-toggle="popover" data-content="<?= TRANS('LOGIN_TO_ACCESS'); ?>" data-placement="left" data-trigger="hover"><i class="fas fa-sign-in-alt "></i></a>
                </span>
                <span class="d-block d-sm-none text-right">
                    <a class="topo-color fs-18" href="../../index.php" title="<?= TRANS('ENTER_IN'); ?>" data-toggle="popover" data-content="<?= TRANS('LOGIN_TO_ACCESS'); ?>" data-placement="left" data-trigger="hover"><i class="fas fa-sign-in-alt "></i></a>
                </span>
            </div>
        </div>

        <div class="container-fluid container-mt">
            





    <?php

    $query = $QRY["ocorrencias_full_ini"] . " where numero in (" . $numero . ") order by numero";
    $resultado = $conn->query($query);
    $row = $resultado->fetch();


    $query2 = "SELECT a.*, u.* FROM assentamentos a LEFT JOIN usuarios u ON u.user_id = a.responsavel WHERE a.ocorrencia = {$numero} and a.asset_privated = 0";
    $resultAssets = $conn->query($query2);
    $assentamentos = $resultAssets->rowCount();


    /* CHECA SE A OCORRÊNCIA É SUB CHAMADO */
    $sqlPai = "select * from ocodeps where dep_filho = " . $numero . " ";
    $execpai = $conn->query($sqlPai);
    $rowPai = $execpai->fetch();
    if ($rowPai && $rowPai['dep_pai'] != "") {
    
        $msgPai = TRANS('FIELD_OCCO_SUB_CALL') . "<strong onClick=\"location.href = '" . $_SERVER['PHP_SELF'] . "?numero=" . $rowPai['dep_pai'] . "'\" style='cursor: pointer'>" . $rowPai['dep_pai'] . "</strong>";
    } else
        $msgPai = "";


    /* Checagem para identificar chamados relacionados */
    $qrySubCall = "SELECT * FROM ocodeps WHERE dep_pai = {$numero} OR dep_filho = {$numero}";
    $execSubCall = $conn->query($qrySubCall);
    $existeSub = $execSubCall->rowCount();


    /* INÍCIO DAS CHECAGENS PARA A MONTAGEM DO MENU DE OPÇÕES */
    $showItemClosure = false;
    $showItemEdit = false;
    $showItemAttend = false;
    $showItemOpenSubcall = false;
    $showItemReopen = false;
    $showItemPrint = true;
    $showItemSla = true;
    $showItemDocTime = false; /* Essa função será removida - pouca utilidade e muito onerosa */
    $showItemSendMail = false;
    $showItemHistory = true;

    $showItemSchedule = false;

    $itemClosure = "";
    $itemEdit = "";
    $itemAttend = "";
    $itemOpenSubcall = "";
    $itemReopen = "";

    $itemPrint = "../geral/print_ticket.php?numero=" . $numero . "&id=" . $id; /* TRANS('FIELD_PRINT_OCCO') */
    $itemSendMail = "";
    $itemHistory = "ticket_history.php?numero=" . $row['numero'];


    /* INÍCIO DAS CONSULTAS REFERENTES À OCORRÊNCIA */

    $isClosed = ($row['status_cod'] == 4 ? true : false);
    $isScheduled = ($row['oco_scheduled'] == 1 ? true : false);
    $ratedInfo = getRatedInfo($conn, $numero);

    $isDoneStatus = ($row['status_cod'] == $config['conf_status_done']);
    // $isRejected = (!empty($ratedInfo) && empty($ratedInfo['rate']) && !$isDoneStatus);
    $isRejected = isRejected($conn, $row['numero']);
    $isRequester = false;
    $showApprovalOption = false;


    /** 
     * Definirá se o chamado poderá ser avaliado nessa tela
     */
    $canBeRated = false;
    $isRated = isRated($conn, $numero);

    if (!empty($rating_id) && asEquals($rating_id, getGlobalTicketRatingId($conn, $numero))) {
        /* Se foi passado o rating_id corretamente, significa que o usuário possui o link para avaliação, pontanto, solicitante */
        $isRequester = true;
        /* Checar o limite de tempo para a validacao do atendimento */
        if (!empty($row['data_fechamento'])) {
            $deadlineToApprove = addDaysToDate($row['data_fechamento'], $config['conf_time_to_close_after_done'], $onlyBusinessDays);
            
            /* if (daysFromDate($row['data_fechamento']) <= $config['conf_time_to_close_after_done']) {
                $showApprovalOption = ($isRequester && !$isResponsible && !$isRated ? true : false);
                $canBeRated = (!$isRated ? true : false);
            } */
            if (date('Y-m-d H:i:s') < $deadlineToApprove) {
                
                if ($isDoneStatus) {
                    $showApprovalOption = ($isRequester && !$isRated ? true : false);
                    $canBeRated = (!$isRated ? true : false);
                }
                
            }
        }
    }

    // if ($showApprovalOption) {
    //     $itemClosure = " onClick=\"approvingTicket({$numero})\""; 
    // }

    $issueDetails = (!empty($row['prob_cod'] && $row['prob_cod'] != '-1') ? getIssueDetailed($conn, $row['prob_cod'])[0] : []);

    $descricao = "";
    if (isset($_GET['destaca'])) {
        $descricao = destaca($_GET['destaca'], toHtml(nl2br($row['descricao'])));
    } else {
        $descricao = toHtml(nl2br($row['descricao']));
    }

    $ShowlinkScript = "";
    $qryScript = "SELECT * FROM prob_x_script WHERE prscpt_prob_id = " . $row['prob_cod'] . "";
    $execQryScript = $conn->query($qryScript);
    if ($execQryScript->rowCount() > 0)
        $ShowlinkScript = "<a onClick=\"popup_alerta('../../admin/geral/scripts_documentation.php?action=endview&prob=" . $row['prob_cod'] . "')\" title='" . TRANS('HNT_SCRIPT_PROB') . "'><i class='far fa-lightbulb text-success'></i></a>";


    $global_link = getGlobalUri($conn, $numero);


    $dateOpen = (!empty($row['oco_real_open_date'] && $row['oco_real_open_date'] != '0000-00-00 00:00:00') ? formatDate($row['oco_real_open_date']) : formatDate($row['data_abertura']));
    $dateClose = formatDate($row['data_fechamento']);
    $dateLastSchedule = formatDate($row['oco_scheduled_to']);
    $dateScheduleTo = "";
    $timeScheduleTo = "";
    $dateRealOpen = formatDate($row['oco_real_open_date']);
    $scriptSolution = "";

    if ($isClosed) {
        if ($row['data_abertura'] != $row['oco_real_open_date'] && $row['oco_real_open_date'] != '0000-00-00 00:00:00') {
            $dateLastSchedule = formatDate($row['data_abertura']);
            $dateClose = formatDate($row['data_fechamento']);
            $dateRealOpen = formatDate($row['oco_real_open_date']);
        } else {
            $dateOpen = (!empty($row['oco_real_open_date'] && $row['oco_real_open_date'] != '0000-00-00 00:00:00') ? formatDate($row['oco_real_open_date']) : formatDate($row['data_abertura']));
            $dateClose = formatDate($row['data_fechamento']);
        }

        $scriptSolution = $row['script_desc'];
    } else {
        if ($isScheduled) {
            $dateOpen = (!empty($row['oco_real_open_date'] && $row['oco_real_open_date'] != '0000-00-00 00:00:00') ? formatDate($row['oco_real_open_date']) : formatDate($row['data_abertura']));

            $dateScheduleTo = dateScreen($row['oco_scheduled_to'], 0, 'd/m/Y H:i');
            $timeScheduleTo = explode(" ", $row['oco_scheduled_to'])[1];
        } else {
            $dateOpen = (!empty($row['oco_real_open_date'] && $row['oco_real_open_date'] != '0000-00-00 00:00:00') ? formatDate($row['oco_real_open_date']) : formatDate($row['data_abertura']));
        }

        if ($row['data_abertura'] != $row['oco_real_open_date'] && $row['oco_real_open_date'] != '0000-00-00 00:00:00' && !empty($row['oco_real_open_date'])) {
            $dateLastSchedule = formatDate($row['data_abertura']);
            $dateRealOpen = formatDate($row['oco_real_open_date']);
        }
    }

    $qryMail = "SELECT * FROM mail_hist m, usuarios u WHERE m.mhist_technician=u.user_id AND
    m.mhist_oco=" . $numero . " ORDER BY m.mhist_date";
    $execMail = $conn->query($qryMail);
    $emails = $execMail->rowCount();


    $sqlFiles = "select * from imagens where img_oco = " . $numero . "";
    $resultFiles = $conn->query($sqlFiles);
    $hasFiles = $resultFiles->rowCount();


    /* FINAL DAS CONSULTAS REFERENTES À OCORRÊNCIA */



    $colLabel = "col-sm-3 text-md-right font-weight-bold p-2";
    $colsDefault = "small text-break border-bottom rounded p-2 bg-white"; /* border-secondary */
    $colContent = $colsDefault . " col-sm-3 col-md-3";
    $colContentLine = $colsDefault . " col-sm-9";
    $colContentLineFile = " text-break border-bottom rounded p-2 bg-white col-sm-9";
    ?>


    <div class="container">
        <div id="idLoad" class="loading" style="display:none"></div>
    </div>

    <!-- MENU DE OPÇÕES -->
    <nav class="navbar navbar-expand-md navbar-light  p-0 rounded" style="background-color: #48606b;">
        <!-- bg-secondary -->
        <!-- style="background-color: #dbdbdb; -->
        <div class="ml-2 font-weight-bold text-white"><?= TRANS('TICKET') . " " . TRANS('NUMBER_ABBREVIATE'); ?> <?= $row['numero']; ?> <small>(<?= TRANS('ONLY_VIEW'); ?>)</small></div> <!-- navbar-brand -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#idMenuOcorrencia" aria-controls="idMenuOcorrencia" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="idMenuOcorrencia">
            <div class="navbar-nav ml-2 mr-2">

                <?php
                
                if ($showItemPrint) {
                ?>
                    <a class="nav-link small text-white" href="<?= $itemPrint; ?>"><i class="fas fa-print"></i><?= TRANS('FIELD_PRINT_OCCO'); ?></a>
                <?php
                }
                ?>

            </div>
        </div>
    </nav>
    <!-- FINAL DO MENU DE OPÇÕES-->



    <div class="modal" tabindex="-1" style="z-index:9001!important" id="modalSubs">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div id="divSubDetails"></div>
            </div>
        </div>
    </div>

    <?php
        if (isset($_SESSION['flash']) && !empty($_SESSION['flash'])) {
            echo $_SESSION['flash'];
            $_SESSION['flash'] = '';
        }
    ?>




    <?php
        if ($canBeRated) {
    ?>
        <!-- Modal de validacao do atendimento -->
        <div class="modal fade" id="modalValidate" tabindex="-1" style="z-index:2001!important" role="dialog" aria-labelledby="myModalValidate" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div id="divResultValidate"></div>
                    <div class="modal-header text-center bg-light">

                        <h4 class="modal-title w-100 font-weight-bold text-secondary"><i class="fas fa-check"></i>&nbsp;<?= TRANS('APPROVING_AND_CLOSE'); ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="row mx-2 mt-4">
                        <div class="form-group col-md-12">
                            <h5><?= TRANS('HAS_YOUR_REQUEST_BEEN_FULFILLED'); ?></h5>
                        </div>
                    </div>
                    
                    <!-- Valida o atendimento ou nao -->
                    <div class="row mx-2">
                        <div class="form-group col-md-12 switch-field container-switch">
                            <input type="radio" id="approved" name="approved" value="yes" checked/>
                            <label for="approved"><?= TRANS('YES'); ?>&nbsp;&nbsp;<i class="fas fa-thumbs-up"></i></label>
                            <input type="radio" id="approved_no" name="approved" value="no" />
                            <label for="approved_no"><?= TRANS('NOT'); ?>&nbsp;&nbsp;<i class="fas fa-thumbs-down"></i></label>
                        </div>
                    </div>

                    <!-- Avaliação do atendimento -->
                    <div class="row mx-2 mt-4">
                        <div class="form-group col-md-12">
                            <h5 id="rating_title"><?= TRANS('HOW_DO_YOU_RATE_THE_SERVICE'); ?></h5>
                        </div>
                    </div>
                    <div class="row mx-2" id="options-to-rate">
                        <div class="form-group col-md-12 switch-field container-switch">
                            <div class="multi-switch">
                                <input type="radio" id="rating_great" name="rating" value="rating_great" checked/>
                                <label for="rating_great" class="color-great"><?= TRANS('ASSESSMENT_GREAT'); ?></label>
                                <input type="radio" id="rating_good" name="rating" value="rating_good" />
                                <label for="rating_good" class="color-good"><?= TRANS('ASSESSMENT_GOOD'); ?></label>
                                <input type="radio" id="rating_regular" name="rating" value="rating_regular" />
                                <label for="rating_regular" class="color-regular"><?= TRANS('ASSESSMENT_REGULAR'); ?></label>
                                <input type="radio" id="rating_bad" name="rating" value="rating_bad" />
                                <label for="rating_bad" class="color-bad"><?= TRANS('ASSESSMENT_BAD'); ?></label>
                            </div>
                        </div>
                    </div>

                    <!-- Comentário / justificativa -->
                    <div class="row mx-2">
                        <div class="form-group col-md-12 ">
                            <textarea class="form-control " name="service_done_comment" id="service_done_comment" placeholder="<?= TRANS('PLACEHOLDER_ASSENT'); ?>"></textarea>
                        </div>
                    </div>
                    
                    <div class="row mx-2 mt-0 d-none" id="msg-case-not-approved">
                        <div class="form-group col-md-12">
                            <?= message('info', '', TRANS('MSG_CASE_NOT_APPROVED'), '', '', 1); ?>
                        </div>
                    </div>
                    

                    <input type="hidden" name="rating_id" id="rating_id" value="<?= $rating_id; ?>"/>
                    <input type="hidden" name="ticket_id" id="ticket_id" value="<?= $id; ?>"/>
                    <input type="hidden" name="ticketNumber" id="ticketNumber" value="<?= $numero; ?>">

                    <div class="modal-footer d-flex justify-content-end bg-light">
                        <button id="confirmApproved" class="btn "><?= TRANS('BT_OK') ?></button>
                        <button id="cancelApproved" class="btn btn-secondary" data-dismiss="modal" aria-label="Close"><?= TRANS('BT_CANCEL'); ?></button>
                    </div>
                </div>
            </div>
        </div>
<?php
        }
?>



    <!-- Modal de SLAs -->
    <div class="modal fade" id="modalSla" tabindex="-1" style="z-index:2001!important" role="dialog" aria-labelledby="mymodalSla" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header text-center bg-light">

                    <h4 class="modal-title w-100 font-weight-bold text-secondary"><i class="fas fa-handshake"></i>&nbsp;<?= TRANS('MENU_SLAS'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="row p-3">
                    <div class="col">
                        <p><?= TRANS('SLAS_HELPER'); ?>: <span class="badge badge-secondary p-2"><?= $row['numero']; ?></span></p>
                    </div>
                </div>

                <div class="row mx-2">
                    <div class="col-7"><?= TRANS('RESPONSE_SLA'); ?> <span class="badge badge-secondary p-2 mb-2" id="idRespostaLocal"></span></div>
                    <div id="idResposta" class="col-5"></div>
                </div>
                <div class="row mx-2 mb-4">
                    <div class="col-7"><?= TRANS('SOLUTION_SLA'); ?> <span class="badge badge-secondary p-2 mb-2" id="idSolucaoProblema"></span></div>
                    <div id="idSolucao" class="col-5"></div>
                </div>

                <div class="row mx-2">
                    <div class="col-7"><?= TRANS('HNT_RESPONSE_TIME'); ?> (<?= TRANS('FILTERED'); ?>)</div>
                    <div id="idResponseTime" class="col"></div>
                </div>
                <div class="row mx-2 mb-4">
                    <div class="col-7"><?= TRANS('TICKET_LIFESPAN'); ?> (<?= TRANS('FILTERED'); ?>)</div>
                    <div id="idFilterTime" class="col"></div>
                </div>

                <div class="row mx-2">
                    <div class="col-7"><?= TRANS('HNT_RESPONSE_TIME'); ?> <?= TRANS('ABSOLUTE'); ?></div>
                    <div id="idAbsResponseTime" class="col"></div>
                </div>
                <div class="row mx-2 mb-4">
                    <div class="col-7"><?= TRANS('TICKET_ABSOLUTE_LIFESPAN'); ?></div>
                    <div id="idAbsSolutionTime" class="col"></div>
                </div>

                <div class="row mx-2 mb-4">
                    <div class="col-7"></div>
                    <div class="col"><a href="#" onclick="showStages('<?= $row['numero']; ?>')"><i class="fab fa-stack-exchange"></i>&nbsp;<?= TRANS('STATUS_STACK'); ?></a></div>
                </div>


                <div class="modal-footer d-flex justify-content-end bg-light">
                    <button id="cancelSchedule" class="btn btn-secondary" data-dismiss="modal" aria-label="Close"><?= TRANS('BT_CLOSE'); ?></button>
                </div>
            </div>
        </div>
    </div>
    <!-- FINAL DA MODAL DE SLAS -->


    <!-- Modal de PILHA de status -->
    <div class="modal fade" id="modalStages" tabindex="-1" style="z-index:2001!important" role="dialog" aria-labelledby="mymodalStages" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header text-center bg-light">

                    <h4 class="modal-title w-100 font-weight-bold text-secondary"><i class="fab fa-stack-exchange"></i>&nbsp;<?= TRANS('STATUS_STACK'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="row p-3">
                    <div class="col">
                        <p><?= TRANS('STATUS_STACK_HELPER'); ?>: <span class="badge badge-secondary p-2"><?= $row['numero']; ?></span></p>
                    </div>
                </div>
                <div class="row header px-3 bold">
                    <div class="col-3"><?= TRANS('COL_STATUS'); ?></div>
                    <div class="col-3"><?= TRANS('DATE'); ?></div>
                    <div class="col-3"><?= TRANS('CARDS_ABSOLUTE_TIME'); ?></div>
                    <div class="col-3"><?= TRANS('CARDS_FILTERED_TIME'); ?></div>
                </div>
                <div class="row p-3" id="idStages">
                    <!-- Conteúdo carregado via ajax -->
                </div>


                <div class="modal-footer d-flex justify-content-end bg-light">
                    <button id="cancelSchedule" class="btn btn-secondary" data-dismiss="modal" aria-label="Close"><?= TRANS('BT_CLOSE'); ?></button>
                </div>
            </div>
        </div>
    </div>
    <!-- FINAL DA MODAL DE PILHA DE STATUS -->



    <div class="container bg-light">

        <div id="divResult"></div>
        <?php
        /* MENSAGEM SE FOR SUBCHAMADO */
        if (!empty($msgPai)) {
            ?>
            <div class="w-100 mb-4"></div>
            <?php
            echo message('info', '', $msgPai, '', '', true);
        }
        $client = getClientByTicket($conn, $row['numero']);
        $ticket_rate = getRatedInfo($conn, $row['numero']);

        $client_class = (count($ticket_rate) || $canBeRated ? $colContent : $colContentLine);

        ?>

        <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('CLIENT'); ?></div>
            <div class="<?= $client_class; ?>"><?= $client['nickname'] ?></div>
            <?php
                if (count($ticket_rate) || $canBeRated) {
                    // var_dump([
                    //     'ticket_rate' => $ticket_rate['rate'] ?? null,
                    //     'isDoneStatus' => $isDoneStatus,
                    //     'isRequester' => $isRequester,
                    //     'isRejected' => $isRejected
                    // ]);
                    ?>
                        <div class="<?= $colLabel; ?>"><?= TRANS('SERVICE_RATE'); ?></div>
                        <div class="<?= $colContent; ?>"><?= renderRate($ticket_rate['rate'] ?? null, $isDoneStatus, $isRequester, $isRejected, 'rate-your-ticket'); ?></div>
                    <?php
                }
            ?>
        </div>
        <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('OPENED_BY'); ?></div>
            <div class="<?= $colContent; ?>"><?= noHtml($row['aberto_por']); ?></div>
            <div class="<?= $colLabel; ?>"><?= TRANS('DEPARTMENT'); ?></div>
            <div class="<?= $colContent; ?>"><?= noHtml($row['setor']); ?></div>
        </div>


        <?php
        if (!empty($row['area_cod']) && $row['area_cod'] != '-1') {
            $areaInfo = getAreaInfo($conn, $row['area_cod']);
            $worktime = getWorktime($conn, $areaInfo['wt_profile']);
            $worktimeSets = getWorktimeSets($worktime);
    
            $renderWtSets = TRANS("FROM_MON_TO_FRI") . " " . $worktimeSets['week'];
            $renderWtSets .= "<br>" . TRANS("SATS") . " " . $worktimeSets['sat'];
            $renderWtSets .= "<br>" . TRANS("SUNS") . " " . $worktimeSets['sun'];
            $renderWtSets .= "<br>" . TRANS("MNL_FERIADOS") . " " . $worktimeSets['off'];
        }
        ?>
        <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('REQUESTER_AREA'); ?></div>
            <div class="<?= $colContent; ?>"><?= noHtml($row['area_solicitante']); ?></div>
            <div class="<?= $colLabel; ?>"><?= TRANS('RESPONSIBLE_AREA'); ?></div>
            <div class="<?= $colContent; ?>" id="divArea" data-toggle="popover" data-content="" title="<?= noHtml($row['area']); ?>"><?= noHtml($row['area']); ?></div>
        </div>

        <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('ISSUE_TYPE'); ?></div>
            <div class="<?= $colContent; ?>"><?= noHtml($row['problema']) . "&nbsp;" . $ShowlinkScript; ?></div>
            <div class="<?= $colLabel; ?>"><?= TRANS('COL_CAT_PROB'); ?></div>
            <?php
            if ($issueDetails) {
                $categories = "";
                $catKeys = ["probt1_desc", "probt2_desc", "probt3_desc", "probt4_desc", "probt5_desc", "probt6_desc"];
                $groupNames = [
                    $config['conf_prob_tipo_1'],
                    $config['conf_prob_tipo_2'],
                    $config['conf_prob_tipo_3'],
                    $config['conf_prob_tipo_4'],
                    $config['conf_prob_tipo_5'],
                    $config['conf_prob_tipo_6']
                ];

                $arrayCategories = [];
                foreach ($catKeys as $key => $catKey) {
                    if (!empty($issueDetails[$catKey]) && strlen($issueDetails[$catKey]) > 1) {
                        $arrayCategories[] = $groupNames[$key] . ": " . $issueDetails[$catKey];
                    }
                }
                $categories = implode(",", $arrayCategories);
            ?>
                <div class="<?= $colContent; ?>"><?= strToTags($categories, 0, "secondary", "issue-category"); ?></div>
            <?php
            } else {
            ?>
                <div class="<?= $colContent; ?>"></div>
            <?php
            }
            ?>

        </div>

        <!-- Descrição -->
        <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('DESCRIPTION'); ?></div>
            <div class="<?= $colContentLine; ?>"><?= $descricao; ?></div>
        </div>

        <!-- Tags -->
        <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('INPUT_TAGS'); ?></div>
            <div class="<?= $colContentLine; ?>"><?= strToTags($row['oco_tag']); ?></div>
        </div>


        <?php
            $fontColor = (!empty($row['cor_fonte']) ? $row['cor_fonte']: "#FFFFFF");
            $bgColor = (!empty($row['cor']) ? $row['cor']: "#CCCCCC");
            $priorityBadge = "<span class='btn btn-sm cursor-no-event p-2' style='color: " . $fontColor . "; background-color: " . $bgColor . "'>" . $row['pr_descricao'] . "</span>";
        ?>
        <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('OCO_PRIORITY'); ?></div>
            <div class="<?= $colContent; ?>"><?= $priorityBadge; ?></div>
            <?php
                $channel = getChannels($conn, $row['oco_channel']);
            ?>
            <div class="<?= $colLabel; ?>"><?= TRANS('OPENING_CHANNEL'); ?></div>
            <div class="<?= $colContent; ?>"><?= $channel['name'] ?? ''; ?></div>
        </div>
        <div class="w-100"></div>
        <div class="row my-2">

            <div class="<?= $colLabel; ?>"><?= TRANS('COL_UNIT'); ?></div>
            <div class="<?= $colContent; ?>"><?= noHtml($row['unidade']); ?></div>
            <div class="<?= $colLabel; ?>"><?= TRANS('FIELD_TAG_EQUIP'); ?></div>
            <div class="<?= $colContent; ?>"><?= noHtml($row['etiqueta']); ?></div>
        </div>
        <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('CONTACT'); ?></div>
            <div class="<?= $colContent; ?>"><?= noHtml($row['contato']); ?></div>
            <div class="<?= $colLabel; ?>"><?= TRANS('COL_PHONE'); ?></div>
            <div class="<?= $colContent; ?>"><?= noHtml($row['telefone']); ?></div>
        </div>
        <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('CONTACT_EMAIL'); ?></div>
            <div class="<?= $colContent; ?>"><?= noHtml($row['contato_email']); ?></div>
            <div class="<?= $colLabel; ?>"><?= TRANS('OPENING_DATE'); ?></div>
            <div class="<?= $colContent; ?>"><?= noHtml($dateOpen); ?></div>

            <?php
            if ($isScheduled) {
            ?>
                <div class="<?= $colLabel; ?>"><?= TRANS('FIELD_SCHEDULE_TO'); ?></div>
                <div class="<?= $colContent; ?>"><?= $dateScheduleTo; ?></div>
            <?php
            }
            ?>

        </div>
        <?php
        if ($isClosed) {
        ?>
            <div class="row my-2">
                <div class="<?= $colLabel; ?>"><?= TRANS('FIELD_DATE_CLOSING'); ?></div>
                <div class="<?= $colContent; ?>"><?= noHtml($dateClose); ?></div>
                <div class="<?= $colLabel; ?>"><?= TRANS('COL_SCRIPT_SOLUTION'); ?></div>
                <div class="<?= $colContent; ?>"><?= noHtml($scriptSolution); ?></div>
            </div>
        <?php
        }


        ?>

        <div class="row my-2">
            <?php
            $statusBadge = "<span class='btn btn-sm cursor-no-event text-wrap p-2' style='color: " . $row['textcolor'] . "; background-color: " . ($row['bgcolor'] ?? '#FFFFFF') . "'>" . $row['chamado_status'] . "</span>";
            ?>
            <div class="<?= $colLabel; ?>"><?= TRANS('COL_STATUS'); ?></div>
            <div class="<?= $colContent; ?>"><?= $statusBadge; ?></div>
            
            <?php
                if (!empty($row['registration_operator_cod'])) {
                    $registratorInfo = getUserInfo($conn, $row['registration_operator_cod']);
                    ?>
                        <div class="<?= $colLabel; ?>"><?= TRANS('REGISTRATION_AUTHOR'); ?></div>
                        <div class="<?= $colContent; ?>"><?= noHtml($registratorInfo['nome']); ?></div>
                    <?php
                }
            ?>
        </div>

        <?php
            $ticketTreaterInfo = getTicketTreater($conn, $row['numero']);
            if (!empty($ticketTreaterInfo)) {
                ?>
                <div class="row my-2">
                    <div class="<?= $colLabel; ?>"><?= TRANS('OCO_RESP'); ?></div>
                    <div class="<?= $colContent; ?>"><?= $ticketTreaterInfo['nome']; ?></div>
                </div>
                <?php
            }
        ?>

        <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('GLOBAL_LINK'); ?></div>
            <div class="<?= $colContentLine; ?>"><?= $global_link; ?></div>

        </div>

        <?php
            /* Exibição dos Campos personalizados */
            $hiddenCustomFields = [];
            $profile_id = $row['profile_id'];
            
            $hiddenCustomFields = ($profile_id ? explode(',', (string)getScreenInfo($conn, $profile_id)['cfields_user_hidden']) : []);


            $custom_fields = getTicketCustomFields($conn, $row['numero']);

            /* Removendo campos marcados como invisíveis para o usuário final */
            if (!empty($custom_fields['field_id'])) {
                foreach ($custom_fields as $key => $field) {
                    if (!empty($hiddenCustomFields) && in_array($field['field_id'], $hiddenCustomFields)) {
                        unset($custom_fields[$key]);
                    }
                }
            }

            $number_of_collumns = 2;
            if (count($custom_fields) && !empty($custom_fields[0]['field_id'])) {

                ?>
                <div class="w-100"></div>
                <p class="h6 text-center font-weight-bold mt-4"><?= TRANS('EXTRA_FIELDS'); ?></p>
                <?php
                $col = 1;
                foreach ($custom_fields as $field) {

                    $value = "";
                    $field_value = $field['field_value'] ?? '';
                    
                    if ($field['field_type'] == 'date' && !empty($field['field_value'])) {
                        $field_value = dateScreen($field['field_value'],1);
                    } elseif ($field['field_type'] == 'datetime' && !empty($field['field_value'])) {
                        $field_value = dateScreen($field['field_value'], 0, 'd/m/Y H:i');
                    } elseif ($field['field_type'] == 'checkbox' && !empty($field['field_value'])) {
                        $field_value = '<span class="text-success"><i class="fas fa-check"></i></span>';
                    }
                    
                    $col = ($col > $number_of_collumns ? 1 : $col);

                    if ($col == 1) {
                    ?>
                        <div class="row my-2">
                    <?php
                    }
                    ?>
                        <div class="<?= $colLabel; ?>"><?= $field['field_label']; ?></div>
                        <div class="<?= $colContent; ?>"><?= $field_value; ?></div>
                    <?php
                    if ($col == $number_of_collumns) {
                    ?>
                        </div>
                    <?php
                    }
                    $col ++;
                }

                if ($col == $number_of_collumns) {
                ?>
                    </div>
                <?php
                }
                
            }


        /* ABAS */

        $classDisabledAssent = ($assentamentos > 0 ? '' : ' disabled');
        $ariaDisabledAssent = ($assentamentos > 0 ? '' : ' true');
        $classDisabledEmails = ($emails > 0 ? '' : ' disabled');
        $ariaDisabledEmails = ($emails > 0 ? '' : ' true');
        $classDisabledFiles = ($hasFiles > 0 ? '' : ' disabled');
        $ariaDisabledFiles = ($hasFiles > 0 ? '' : ' true');
        $classDisabledSubs = ($existeSub > 0 ? '' : ' disabled');
        $ariaDisabledSubs = ($existeSub > 0 ? '' : ' true');

        ?>
        <div class="row my-2">
            <div class="<?= $colLabel; ?>"></div>
            <div class="<?= $colContentLine; ?>">
                <!-- <div class="<?= $colsDefault; ?> col-sm-12 d-flex justify-content-md-center"> -->
                <ul class="nav nav-pills " id="pills-tab" role="tablist">
                    <li class="nav-item" role="assentamentos">
                        <a class="nav-link active <?= $classDisabledAssent; ?>" id="divAssentamentos-tab" data-toggle="pill" href="#divAssentamentos" role="tab" aria-controls="divAssentamentos" aria-selected="true" aria-disabled="<?= $ariaDisabledAssent; ?>"><i class="fas fa-comment-alt"></i>&nbsp;<?= TRANS('TICKET_ENTRIES'); ?>&nbsp;<span class="badge badge-light p-1"><?= $assentamentos; ?></span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $classDisabledEmails; ?>" id="divEmails-tab" data-toggle="pill" href="#divEmails" role="tab" aria-controls="divEmails" aria-selected="true" aria-disabled="<?= $ariaDisabledEmails; ?>"><i class="fas fa-envelope"></i>&nbsp;<?= TRANS('EMAILS'); ?>&nbsp;<span class="badge badge-light pt-1"><?= $emails; ?></span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $classDisabledFiles; ?>" id="divFiles-tab" data-toggle="pill" href="#divFiles" role="tab" aria-controls="divFiles" aria-selected="true" aria-disabled="<?= $ariaDisabledFiles; ?>"><i class="fas fa-paperclip"></i>&nbsp;<?= TRANS('FILES'); ?>&nbsp;<span class="badge badge-light pt-1"><?= $hasFiles; ?></span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $classDisabledSubs; ?>" id="divSubs-tab" data-toggle="pill" href="#divSubs" role="tab" aria-controls="divSubs" aria-selected="true" aria-disabled="<?= $ariaDisabledSubs; ?>"><i class="fas fa-stream"></i>&nbsp;<?= TRANS('TICKETS_REFERENCED'); ?>&nbsp;<span class="badge badge-light pt-1"><?= $existeSub; ?></span></a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- FINAL DAS ABAS -->



        <!-- LISTAGEM DE ASSENTAMENTOS -->

        <div class="tab-content" id="pills-tabContent">
            <?php
            if ($assentamentos) {
            ?>

                <div class="tab-pane fade show active" id="divAssentamentos" role="tabpanel" aria-labelledby="divAssentamentos-tab">

                    <div class="row my-2">

                        <div class="col-sm-12 border-bottom rounded p-0 bg-white " id="assentamentos">
                            <!-- collapse -->
                            <table class="table  table-hover table-striped rounded">
                                <!-- table-responsive -->
                                <thead class="text-white" style="background-color: #48606b;">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col"><?= TRANS('PRIVACY'); ?></th>
                                        <th scope="col"><?= TRANS('AUTHOR'); ?></th>
                                        <th scope="col"><?= TRANS('DATE'); ?></th>
                                        <th scope="col"><?= TRANS('COL_TYPE'); ?></th>
                                        <th scope="col"><?= TRANS('TICKET_ENTRY'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    foreach ($resultAssets->fetchAll() as $rowAsset) {
                                        $transAssetText = "";
                                        if ($rowAsset['asset_privated'] == 1) {
                                            $transAssetText = "<span class='badge badge-danger p-2'>" . TRANS('CHECK_ASSET_PRIVATED') . "</span>";
                                        } else {
                                            $transAssetText = "<span class='badge badge-success p-2'>" . TRANS('CHECK_ASSET_PUBLIC') . "</span>";
                                        }
                                        /* Badge da primeira resposta */
                                        $badgeFirstResponse = "";
                                        if (!empty($row['data_atendimento']) && $row['data_atendimento'] == $rowAsset['created_at']) {
                                            $badgeFirstResponse = '&nbsp;<span class="badge badge-info p-2">' . TRANS('FIRST_RESPONSE') . '</span>';
                                        }
                                        $author = $rowAsset['nome'] ?? TRANS('AUTOMATIC_PROCESS');
                                        ?>
                                        <tr>
                                            <th scope="row"><?= $i; ?></th>
                                            <td><?= $transAssetText; ?></td>
                                            <td><?= noHtml($author); ?></td>
                                            <td data-sort="<?= $rowAsset['created_at']; ?>"><?= formatDate($rowAsset['created_at']) . $badgeFirstResponse; ?></td>
                                            <td><?= getEntryType($rowAsset['tipo_assentamento']); ?></td>
                                            <td><?= nl2br(noHtml($rowAsset['assentamento'])); ?></td>
                                        </tr>
                                        <?php
                                        $i++;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php
            }
            /* FINAL DA LISTAGEM DE ASSENTAMENTOS */




            /* INÍCIO DO TRECHO PARA E-MAILS ENVIADOS */
            if ($emails) {
            ?>
                <div class="tab-pane fade" id="divEmails" role="tabpanel" aria-labelledby="divEmails-tab">
                    <div class="row my-2">
                        <div class="col-12" id="divError">
                        </div>
                    </div>

                    <div class="row my-2">

                        <div class="col-sm-12 border-bottom rounded p-0 bg-white " id="emails">
                            <!-- collapse -->
                            <table class="table  table-hover table-striped rounded">
                                <!-- table-responsive -->
                                <!-- <thead class="bg-secondary text-white"> -->
                                <thead class="text-white" style="background-color: #48606b;">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col"><?= TRANS('SUBJECT'); ?></th>
                                        <th scope="col"><?= TRANS('MHIST_LISTS'); ?></th>
                                        <th scope="col"><?= TRANS('MAIL_BODY_CONTENT'); ?></th>
                                        <th scope="col"><?= TRANS('DATE'); ?></th>
                                        <th scope="col"><?= TRANS('AUTHOR'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    foreach ($execMail->fetchAll() as $rowMail) {
                                        $limite = 30;
                                        $shortBody = trim($rowMail['mhist_body']);
                                        if (strlen((string)$shortBody) > $limite) {
                                            $shortBody = substr($shortBody, 0, ($limite - 4)) . "...";
                                        }
                                    ?>
                                        <tr onClick="showEmailDetails(<?= $rowMail['mhist_cod']; ?>)" style="cursor: pointer;">
                                            <!-- <tr data-toggle="modal" data-target="#myModal"> -->
                                            <th scope="row"><?= $i; ?></th>
                                            <td><?= $rowMail['mhist_subject']; ?></td>
                                            <td><?= NVL($rowMail['mhist_listname']); ?></td>
                                            <td><?= $shortBody; ?></td>
                                            <td><?= formatDate($rowMail['mhist_date']); ?></td>
                                            <td><?= $rowMail['nome']; ?></td>
                                        </tr>
                                    <?php
                                        $i++;
                                    }
                                    ?>

                                    <div class="modal" tabindex="-1" id="modalEmails">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modal_title"></h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-12" id="para"></div>
                                                        <div class="col-12" id="copia"></div>
                                                        <div class="col-12" id="subject"></div>
                                                        <div class="col-12">
                                                            <hr>
                                                        </div>
                                                        <div class="col-12" id="mensagem"></div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= TRANS('LINK_CLOSE'); ?></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php
            }
            /* FINAL DO TRECHO PARA OS EMAILS ENVIADOS */



            /* TRECHO PARA EXIBIÇÃO DA LISTAGEM DE ARQUIVOS ANEXOS */
            if ($hasFiles) {
            ?>
                <div class="tab-pane fade" id="divFiles" role="tabpanel" aria-labelledby="divFiles-tab">
                    <div class="row my-2">

                        <div class="col-sm-12 border-bottom rounded p-0 bg-white " id="files">
                            <!-- collapse -->
                            <table class="table  table-hover table-striped rounded">
                                <!-- table-responsive -->
                                <!-- <thead class="bg-secondary text-white"> -->
                                <thead class=" text-white" style="background-color: #48606b;">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col"><?= TRANS('COL_TYPE'); ?></th>
                                        <th scope="col"><?= TRANS('SIZE'); ?></th>
                                        <th scope="col"><?= TRANS('FILE'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    foreach ($resultFiles->fetchAll() as $rowFiles) {

                                        $size = round($rowFiles['img_size'] / 1024, 1);
                                        $rowFiles['img_tipo'] . "](" . $size . "k)";

                                        if (isImage($rowFiles["img_tipo"])) {

                                            $viewImage = "&nbsp;<a onClick=\"javascript:popupWH('../../includes/functions/showImg.php?" .
                                                "file=" . $row['numero'] . "&id=" . $id . "&cod=" . $rowFiles['img_cod'] . "'," . $rowFiles['img_largura'] . "," . $rowFiles['img_altura'] . ")\" " .
                                                "title='view'><i class='fa fa-search'></i></a>";

                                        } else {
                                            $viewImage = "";
                                        }
                                    ?>
                                        <tr>
                                            <th scope="row"><?= $i; ?></th>
                                            <td><?= $rowFiles['img_tipo']; ?></td>
                                            <td><?= $size; ?>k</td>
                                            <td><a onClick="redirect('../../includes/functions/download.php?file=<?= $numero; ?>&cod=<?= $rowFiles['img_cod']; ?>&id=<?= $id; ?>')" title="Download the file"><?= $rowFiles['img_nome']; ?></a><?= $viewImage; ?></i></td>
                                        </tr>
                                    <?php
                                        $i++;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php
            }

            /* FINAL DO TRECHO DE LISTAGEM DE ARQUIVOS ANEXOS*/
            ?>


            <!-- LISTAGEM DE SUBCHAMADOS -->
            <?php
            if ($existeSub) {
            ?>
                <div class="tab-pane fade" id="divSubs" role="tabpanel" aria-labelledby="divSubs-tab">
                    <div class="row my-2">

                        <div class="col-sm-12 border-bottom rounded p-0 bg-white " id="subs">
                            <!-- collapse -->
                            <table class="table  table-hover table-striped rounded">
                                <!-- table-responsive -->
                                <!-- <thead class="bg-secondary text-white"> -->
                                <thead class=" text-white" style="background-color: #48606b;">
                                    <tr>
                                        <th scope="col"><?= TRANS('TICKET_NUMBER'); ?></th>
                                        <th scope="col"><?= TRANS('AREA'); ?></th>
                                        <th scope="col"><?= TRANS('ISSUE_TYPE'); ?></th>
                                        <th scope="col"><?= TRANS('CONTACT') . "<br />" . TRANS('COL_PHONE'); ?></th>
                                        <th scope="col"><?= TRANS('DEPARTMENT') . "<br />" . TRANS('DESCRIPTION'); ?></th>
                                        <th scope="col"><?= TRANS('FIELD_LAST_OPERATOR') . "<br />" . TRANS('COL_STATUS'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $comDeps = false;
                                    $i = 1;
                                    $key = "";
                                    $label = "";
                                    foreach ($execSubCall->fetchAll() as $rowSubPai) {

                                        // $sqlStatus = "select o.*, s.* from ocorrencias o, `status` s  where o.numero=" . $rowSubPai['dep_filho'] . " and o.`status`=s.stat_id and s.stat_painel not in (3) ";
                                        // $execStatus = $conn->query($sqlStatus);
                                        // $regStatus = $execStatus->rowCount();
                                        // if ($regStatus > 0) {
                                        //     $comDeps = true;
                                        // }
                                        // if ($comDeps) {
                                        //     $imgSub = ICONS_PATH . "view_tree_red.png";
                                        // } else {
                                        //     $imgSub = ICONS_PATH . "view_tree_green.png";
                                        // }

                                        $key = $rowSubPai['dep_filho'];
                                        $label = "<span class='badge badge-oc-wine p-2'>" . TRANS('CHILD_TICKET') . "</span>";
                                        // $comDeps = false;
                                        if ($rowSubPai['dep_pai'] != $numero) {
                                            $key = $rowSubPai['dep_pai'];
                                            $label = "<span class='badge badge-oc-teal p-2'>" . TRANS('PARENT_TICKET') . "</span>";
                                        }

                                        $qryDetail = $QRY["ocorrencias_full_ini"] . " WHERE  o.numero = " . $key . " ";
                                        $execDetail = $conn->query($qryDetail);
                                        $rowDetail = $execDetail->fetch();

                                        $texto = noHtml($rowDetail['descricao']);
                                        if (strlen((string)$texto) > 200) {
                                            $texto = substr($texto, 0, 195) . " ..... ";
                                        };

                                    ?>
                                        <tr onClick="showSubsDetails(<?= $rowDetail['numero']; ?>)" style="cursor: pointer;">
                                            <th scope="row"><?= $rowDetail['numero']; ?></a>&nbsp;<?= $label; ?></th>
                                            <td><?= $rowDetail['area']; ?></td>
                                            <td><?= $rowDetail['problema']; ?></td>
                                            <td><?= noHtml($rowDetail['contato']) . "<br/>" . noHtml($rowDetail['telefone']); ?></td>
                                            <td><?= $rowDetail['setor'] . "<br/>" . $texto; ?></td>
                                            <td><?= noHtml($rowDetail['nome']) . "<br/>" . $rowDetail['chamado_status']; ?></td>

                                        </tr>
                                    <?php
                                        $i++;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>
            <!-- FINAL DA LISTAGEM DE SUBCHAMADOS -->

        </div> <!-- tab-content -->

    </div>


        <!-- FOOTER -->
        <small>
            <div class=" fixed-bottom ">
                <div class="  bg-light border-top text-center p-2 " style="z-index:4; ">
                    <div class="footer-text">
                        <span>
                            <a href="<?= APP_URL; ?>" target="_blank">
                                <strong><?= APP_NAME; ?></strong>
                            </a>
                            &nbsp;-&nbsp;
                            <?= TRANS('OCOMON_ABSTRACT'); ?><br />
                            <?= TRANS('COL_VERSION') . ": <strong>" . VERSAO . "</strong> - " . TRANS('MNS_MSG_LIC') . " GPL"; ?>
                        </span>
                    </div>
                </div>
            </div>
        </small>

    <?php
}
    ?>


    <script src="../../includes/javascript/funcoes-3.0.js"></script>
    <script src="../../includes/components/jquery/jquery.js"></script>
    <script src="../../includes/components/bootstrap/js/bootstrap.bundle.js"></script>
	<script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>


    <script>
        $(function() {

            $(function() {
                $('[data-toggle="popover"]').popover()
            });


            $('.table').DataTable({
				language: {
					url: "../../includes/components/datatables/datatables.pt-br.json",
				},
				paging: true,
				deferRender: true,
				order: [3, 'DESC'],
			});

            $('.popover-dismiss').popover({
                trigger: 'focus'
            });


            if ($('#modalValidate').length > 0) {
                
                if ($('#rate-your-ticket').length > 0) {

                    approvingTicket($('#ticketNumber').val());
                    $('#rate-your-ticket').css('cursor', 'pointer').on('click', function(){
                        approvingTicket($('#ticketNumber').val());
                    });
                }
                
                
                
                $('[name="approved"]').on('change', function() {
                    if ($(this).val() == "no") {
                        disableRating();
                    } else 
                    if ($(this).val() == "yes") {
                        enableRating();
                    }
                });


                $('#modalValidate').on('hidden.bs.modal', function (e) {
                    $('#divResultValidate').html('');
                    $('#service_done_comment').removeClass('is-invalid');
                });
            }
            

        });

        function approvingTicket(id) {
            $('#modalValidate').modal();
            $('#confirmApproved').html('<a class="btn btn-primary" onclick="setApproved(' + id + ')"><?= TRANS('BT_OK'); ?></a>');
        }

        function setApproved (numero){
            var approved = ($('#approved').is(':checked') ? true : false);
            
            var loading = $(".loading");
            $(document).ajaxStart(function() {
                loading.show();
            });
            $(document).ajaxStop(function() {
                loading.hide();
            });

            $.ajax({
                url: '../geral/ticket_approval_process.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    'numero': numero,
                    'approved': approved,
                    'ticket_id': $('#ticket_id').val(),
                    'rating_id': $('#rating_id').val(),
                    'rating_great': $('#rating_great').is(':checked'),
                    'rating_good': $('#rating_good').is(':checked'),
                    'rating_regular': $('#rating_regular').is(':checked'),
                    'rating_bad': $('#rating_bad').is(':checked'),
                    'service_done_comment': $('#service_done_comment').val(),
                },
            }).done(function(response) {
                if (!response.success) {
                    if (response.field_id != "") {
                        $('#' + response.field_id).focus().addClass('is-invalid');
                    }
                    $('#divResultValidate').html(response.message);
                } else {
                    $('#modalValidate').modal('hide');
                    location.reload();
                }
            });
            return false;
        }

        function disableRating () {
            $('#options-to-rate').hide();
            $('#rating_great').prop('disabled', true).prop('checked', false);
            $('#rating_good').prop('disabled', true).prop('checked', false);
            $('#rating_regular').prop('disabled', true).prop('checked', false);
            $('#rating_bad').prop('disabled', true).prop('checked', false);
            $('#rating_title').text('<?= TRANS('YOU_CAN_RATE_THE_SERVICE_AFTER_DONE'); ?>');
            $('#service_done_comment').attr('placeholder', '<?= TRANS('DESCRIBE_HOW_YOUR_REQUEST_IS_NOT_DONE'); ?>');
            $('#msg-case-not-approved').removeClass('d-none');
        }
        function enableRating() {
            $('#options-to-rate').show();
            $('#rating_great').prop('disabled', false).prop('checked', true);
            $('#rating_good').prop('disabled', false);
            $('#rating_regular').prop('disabled', false);
            $('#rating_bad').prop('disabled', false);
            $('#rating_title').text('<?= TRANS('HOW_DO_YOU_RATE_THE_SERVICE'); ?>');
            $('#service_done_comment').attr('placeholder', '<?= TRANS('PLACEHOLDER_ASSENT'); ?>');
            $('#msg-case-not-approved').addClass('d-none');
        }
    </script>
    </body>

    </html>