<?php /*                        Copyright 2023 Flávio Ribeiro

        This file is part of OCOMON.

        OCOMON is free software; you can redistribute it and/or modify
        it under the terms of the GNU General Public License as published by
        the Free Software Foundation; either version 2 of the License, or
        (at your option) any later version.

        OCOMON is distributed in the hope that it will be useful,
        but WITHOUT ANY WARRANTY; without even the implied warranty of
        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
        GNU General Public License for more details.

        You should have received a copy of the GNU General Public License
        along with Foobar; if not, write to the Free Software
        Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/ session_start();

if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
    $_SESSION['session_expired'] = 1;
    echo "<script>top.window.location = '../../index.php'</script>";
    exit;
}

require_once __DIR__ . "/" . "../../includes/include_geral_new.inc.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";
require_once __DIR__ . "/" . "../../includes/components/html2text-master/vendor/autoload.php";


use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 3);


$dateToday = date('Y-m-d');
$exceptions = "";
$user = $_SESSION['s_uid'];
$loadedNotificationsCount = 0;

$sent = false;
$notice_id = [];

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/datatables/datatables.min.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/css/my_datatables.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/css/my_datatables_cards.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/css/estilos_custom.css" />

    <title><?= APP_NAME; ?>&nbsp;<?= VERSAO; ?></title>
    <style>
        .spanTicketNumber, 
        .seenTicketNumber, 
        .spanTicketNumberOther, 
        .seenTicketNumberOher {
            cursor: pointer;
            font-size: larger;
        }

        hr.thick {
            border: 1px solid;
            border-radius: 5px;
        }

        .line-spacing {
            line-height: 1.5;
        }

        
    </style>
</head>

<body>
    <div class="container">
        <div id="idLoad" class="loading" style="display:none"></div>
        <div id="divResult"></div>
    </div>
    <div class="container-fluid">
        <h4 class="my-4"><i class="fas fa-bell text-secondary"></i>&nbsp;<?= TRANS('MY_NOTICES'); ?>&nbsp;<span id="refreshButton"></span></h4> <!-- float-right -->

        <input type="hidden" name="notitifications_count" id="notitifications_count">
        <input type="hidden" name="refresh_button_text" id="refresh_button_text" value="<?= TRANS('CLICK_TO_REFRESH'); ?>">

        <?php

        $badgeSeen = '<small><span class="badge badge-pill badge-success" id="badgeRead">'.TRANS("SEEN").'</span></small>';

        
        $hasToSignTerm = hasToSignTerm($conn, $user);
        $userHasTicketWaitingToBeRated = userHasTicketWaitingToBeRated($conn, $user);

        if ($hasToSignTerm || $userHasTicketWaitingToBeRated) {
            ?>
            <h5 class="my-4"><i class="fas fa-exclamation-triangle text-secondary"></i>&nbsp;<?= TRANS('NOTICES_NEED_MY_ACTION'); ?></h5>
            <?php

            /* Mensagem sobre necessidade de assinatura em termo de compromisso */
            if ($hasToSignTerm) {
                $loadedNotificationsCount++;
                $profileUrl = "../../admin/geral/users.php?action=profile";
                $userProfileIcon = ($_SESSION['s_nivel'] == 1 ? "fa fa-user-cog" : ($_SESSION['s_nivel'] == 2 ? "fa fa-user-edit" : "fa fa-user"));
                $spanProfileUrl = '&nbsp;&nbsp;<span class="badge badge-secondary p-2" data-link="' . $profileUrl . '" id="user-profile"><i class="' . $userProfileIcon . '"></i></span>&nbsp;&nbsp;';
                echo message('danger', '', TRANS('MSG_NEED_TO_SIGN_TERM') . $spanProfileUrl, '', '', true);
            }

            /* Se existirem chamados para serem avaliados pelo usuário */
            if ($userHasTicketWaitingToBeRated) {
                $loadedNotificationsCount++;
                echo message('danger', '', TRANS('NOTIFICATION_YOUR_PENDING_RATING'), '', '', true, 'fas fa-star-half-alt');
            }
        }


        /* Notificações pontuais - não relacionadas aos chamados */
        $ticketNumberSeparator = "%tkt%";
        $UserOtherNotifications = getUnreadUserNotifications($conn, $user);
        $UserSeenOtherNotifications = getSeenUserNotifications($conn, $user);
        $cols = [TRANS('AUTHOR'), TRANS('DATE')];
        $values = [];
        
        if (count($UserOtherNotifications)) {
            $loadedNotificationsCount += count($UserOtherNotifications);
            ?>
                <h5 class="my-4"><i class="fas fa-exclamation-circle text-secondary"></i>&nbsp;<?= TRANS('MISC_NOTIFICATIONS'); ?></h5>
            <?php
        }
        foreach ($UserOtherNotifications as $notification) {
            $values = [$notification['author'], dateScreen($notification['created_at'])];

            $textParts = explode($ticketNumberSeparator, $notification['text']);
            if (count($textParts) > 1) {
                $spanTicketText = '&nbsp;<span class="spanTicketNumberOther badge badge-secondary" data-link="'. $textParts[1] .'" data-notice="' . $notification['id'] . '">' . $textParts[1] . '</span>';

                $text = "<i class='fas fa-quote-left'></i>&nbsp;" .(new \Html2Text\Html2Text($textParts[0]))->getText() . $spanTicketText;
            } else {
                $text = "<i class='fas fa-quote-left'></i>&nbsp;" .(new \Html2Text\Html2Text($notification['text']))->getText();
            }

            echo alertNotice('secondary', '', $cols, $values, $text, $notification['id'] . '_' . 'users_notifications', 'users_notifications');
        }


        /* Notificações sobre chamados relacionados ao usuário - Não lidas */
        $ticketsNotifications = getUserTicketsNotices($conn, $user);

        /* Notificações sobre chamados relacionados ao usuário - lidas */
        $tickets_notifications_seen = getUserTicketsNotices($conn, $user, false, true);


        if (count($ticketsNotifications) == 0) {
            echo message('success', '', TRANS('MSG_NO_NOTICES_ABOUT_YOUR_TICKETS'), '', '', true);
        } else {
            $loadedNotificationsCount += count($ticketsNotifications);
        ?>
            <h5 class="my-4"><i class="fas fa-exclamation-circle text-secondary"></i>&nbsp;<?= TRANS('TICKETS_NOTIFICATIONS'); ?></h5>
            
            <?php
            //TRANS('COL_TYPE')
            $cols = [TRANS('TICKET'), TRANS('ACTION'), TRANS('AUTHOR'), TRANS('DATE')];
            $values = [];

            $ticketsNotifications = arraySortByColumn($ticketsNotifications, 'created_at', SORT_DESC);
            foreach ($ticketsNotifications as $notice) {

                $spanToTicket = '<span data-link="'. $notice['ocorrencia'] .'" data-notice="' . $notice['notice_id'] . '" class="spanTicketNumber badge badge-primary">' . $notice['ocorrencia'] . '</span>';

                $values = [$spanToTicket, getEntryType($notice['type']), $notice['author'], dateScreen($notice['created_at'])];

                $text = "<i class='fas fa-quote-left'></i>&nbsp;" .(new \Html2Text\Html2Text($notice['assentamento']))->getText(); // . "&nbsp;<i class='fas fa-quote-right'></i>";
                
                echo alertNotice('info', TRANS($notice['type_name']), $cols, $values, $text, $notice['notice_id'] . '_' . 'assentamentos', 'assentamentos');
            }
        }



        /**
         * Notificações sobre chamados relacionados ao usuário - lidas
         */

        if (count($UserSeenOtherNotifications)) {
            ?>
            <div id="seen_other_notifications" class="mb-4">
                <div class="accordion" id="accordionSeenOtherNotifications">
                    <div class="card border-secondary ">
                        <div class="card-header bg-light border-secondary" id="showSeenOtherNotifications">
                            <button id="idBtnSeenOtherNotifications" class="btn btn-block text-center  " type="button" data-toggle="collapse" data-target="#listSeenOtherNotifications" aria-expanded="true" aria-controls="listSeenOtherNotifications" onclick="this.blur();">
                                <h4><i class="fas fa-check"></i>&nbsp;<?= TRANS('OTHER_SEEN_NOTIFICATIONS') . '&nbsp;' . $badgeSeen ?>&nbsp;<span id="idTotalSeenOtherNotifications" class="badge badge-light"></span></h4>
                            </button>
                        </div>

                        <div id="listSeenOtherNotifications" class="collapse" aria-labelledby="showSeenOtherNotifications" data-parent="#accordionSeenOtherNotifications">
                            <div class="card-body" id="idCardSeenOtherNotifications">
                                <div class="row">
                                    <div class="col-12 ">

                                        <h5 class="my-4 mt-4"><i class="fas fa-check text-secondary"></i>&nbsp;<?= TRANS('OTHER_SEEN_NOTIFICATIONS'); ?></h5>

                                        <table id="table_other_notifications_seen" class="table cards" border="0" cellspacing="0" width="100%">
                                            <thead>
                                                <tr class="header">
                                                    <th class="line"></th>
                                                    <th class="line"><?= TRANS('AUTHOR'); ?></th>
                                                    <th class="line"><?= TRANS('DATE'); ?></th>
                                                    <th class="line"><?= TRANS('NOTIFICATION'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    foreach ($UserSeenOtherNotifications as $notice) {
                                                        
                                                        $textParts = explode($ticketNumberSeparator, $notice['text']);

                                                        if (count($textParts) > 1) {
                                                            $spanTicketText = '&nbsp;<span class="seenTicketNumberOher badge badge-secondary" data-link="'. $textParts[1] .'" data-notice="' . $notice['id'] . '">' . $textParts[1] . '</span>';

                                                            $text = "<i class='fas fa-quote-left'></i>&nbsp;" . (new \Html2Text\Html2Text($textParts[0]))->getText() . $spanTicketText;
                                                        } else {
                                                            $text = "<i class='fas fa-quote-left'></i>&nbsp;" . (new \Html2Text\Html2Text($notice['text']))->getText();
                                                        }
                                                        
                                                        ?>
                                                        <tr class="line-spacing">
                                                            <td class="line"></td>
                                                            <td class="line"><?= $notice['author']; ?></td>
                                                            <td class="line" data-sort="<?= $notice['created_at']; ?>"><?= dateScreen($notice['created_at']); ?></td>
                                                            <td class="line"><?= $text; ?></td>
                                                        <?php
                                                    }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }




        if (count($tickets_notifications_seen)) {
            ?>
            <div id="seen_tickets_notifications" class="mb-4">
                <div class="accordion" id="accordionSeenTicketsNotifications">
                    <div class="card border-secondary">
                        <div class="card-header bg-light border-secondary" id="showSeenTicketsNotifications">
                            <button id="idBtnSeenTicketsNotifications" class="btn btn-block text-center  " type="button" data-toggle="collapse" data-target="#listSeenTicketsNotifications" aria-expanded="true" aria-controls="listSeenTicketsNotifications" onclick="this.blur();">
                                <h4><i class="fas fa-check"></i>&nbsp;<?= TRANS('TICKETS_SEEN_NOTIFICATIONS') . '&nbsp;' . $badgeSeen; ?>&nbsp;<span id="idTotalSeenTicketsNotifications" class="badge badge-light"></span></h4>
                            </button>
                        </div>

                        <div id="listSeenTicketsNotifications" class="collapse" aria-labelledby="showSeenTicketsNotifications" data-parent="#accordionSeenTicketsNotifications">
                            <div class="card-body" id="idCardSeenTicketsNotifications">
                                <div class="row">
                                    <div class="col-12 ">

                                        <h5 class="my-4 mt-4"><i class="fas fa-check text-secondary"></i>&nbsp;<?= TRANS('TICKETS_SEEN_NOTIFICATIONS'); ?></h5>

                                        <table id="table_tickets_notifications_seen" class="table cards" border="0" cellspacing="0" width="100%">
                                            <thead>
                                                <tr class="header">
                                                    <th class="line"></th>
                                                    <th class="line"><?= TRANS('TICKET'); ?></th>
                                                    <th class="line"><?= TRANS('ACTION'); ?></th>
                                                    <th class="line"><?= TRANS('AUTHOR'); ?></th>
                                                    <th class="line"><?= TRANS('DATE'); ?></th>
                                                    <th class="line"><?= TRANS('NOTIFICATION'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    $tickets_notifications_seen = arraySortByColumn($tickets_notifications_seen, 'created_at', SORT_DESC);
                                                    foreach ($tickets_notifications_seen as $notice) {
                                                        $text = (new \Html2Text\Html2Text($notice['assentamento']))->getText();
                                                        ?>
                                                        <tr class="line-spacing">
                                                            <td class="line"><?= TRANS($notice['type_name']); ?></td>
                                                            <td class="line"><span class="seenTicketNumber badge badge-primary p-2" data-link="<?= $notice['ocorrencia']; ?>"><?= $notice['ocorrencia']; ?></span></td>
                                                            <td class="line"><?= getEntryType($notice['type']); ?></td>
                                                            <td class="line"><?= $notice['author']; ?></td>
                                                            <td class="line" data-sort="<?= $notice['created_at']; ?>"><?= dateScreen($notice['created_at']); ?></td>
                                                            <td class="line"><?= $text; ?></td>
                                                        <?php
                                                    }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
        <input type="hidden" name="loaded_notifications_count" id="loaded_notifications_count" value="<?= $loadedNotificationsCount; ?>">
    </div>
    <script src="../../includes/javascript/funcoes-3.0.js"></script>
    <script src="../../includes/components/jquery/jquery.js"></script>
    <script src="../../includes/components/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>
    <script>
        $(function() {

            $('#idLoad').css('display', 'block');
            $( document ).ready(function() {
                $('#idLoad').css('display', 'none');
            });

            setInterval(function() {
                getCountUserNotifications();
            }, 10000);

            // renderRefreshButton();

            var tableTicketsSeen = $('#table_tickets_notifications_seen').DataTable({
                language: {
                    url: "../../includes/components/datatables/datatables.pt-br.json",
                },
                paging: true,
                deferRender: true,
                order : [[ 4, "desc" ]],
                // 'select': 'multiple',
                /* columnDefs: [{
                    searchable: false,
                    orderable: false,
                    targets: ['editar', 'remover']
                }], */
            });

            var tableOtherSeen = $('#table_other_notifications_seen').DataTable({
                language: {
                    url: "../../includes/components/datatables/datatables.pt-br.json",
                },
                paging: true,
                deferRender: true,
                order : [[ 2, "desc" ]],
                // 'select': 'multiple',
                /* columnDefs: [{
                    searchable: false,
                    orderable: false,
                    targets: ['editar', 'remover']
                }], */
            });


            /* Percorrendo todos os alerts com a classe assentamentos */
            $('.alert.assentamentos').on('closed.bs.alert', function () {
                setNoticeAsSeen($(this).attr('id'), 'assentamentos');
            });
            /* Percorrendo todos os alerts com a classe users_notifications */
            $('.alert.users_notifications').on('closed.bs.alert', function () {
                setNoticeAsSeen($(this).attr('id'), 'users_notifications');
            });


            /* Transformando a tabela para exibição em cards */
            var tables = $('.cards');
            var table_headers = [];
            tables.each(function() {
                var th = [];
                $(this).find('thead th').each(function() {
                    th.push($(this).text());
                });
                table_headers.push(th);
            });
            // Add a data-label attribute to each cell
            // with the value of the corresponding column header
            // Iterate through each table
            tables.each(function(table) {
                var table_index = table;
                // Iterate through each row
                $(this).find('tbody tr').each(function() {
                    // Finally iterate through each column/cell
                    $(this).find('td').each(function(column) {
                        $(this).attr('data-label', table_headers[table_index][column]);
                    });
                });
            });


            $('#user-profile').css('cursor', 'pointer').on('click', function() {
                redirect($(this).attr('data-link'));
            });

            $('.spanTicketNumber').on('click', function() {
                var ticket = $(this).attr('data-link');
                setNoticeAsSeen($(this).attr('data-notice'), 'assentamentos', 'redirect', ticket);
            });
            $('.spanTicketNumberOther').on('click', function() {
                var ticket = $(this).attr('data-link');
                setNoticeAsSeen($(this).attr('data-notice'), 'users_notifications', 'redirect', ticket);
            });
            $('.seenTicketNumber').on('click', function() {
                var ticket = $(this).attr('data-link');
                redirect('./ticket_show.php?numero=' + ticket);
            });
            $('.seenTicketNumberOher').on('click', function() {
                var ticket = $(this).attr('data-link');
                redirect('./ticket_show.php?numero=' + ticket);
            });
        });

        /* Functions */
        function setNoticeAsSeen(id, table, action, ticket) {
            $(document).ajaxStart(function() {
                $(".loading").show();
            });
            $(document).ajaxStop(function() {
                $(".loading").hide();
            });

            // if (table == 'assentamentos' && action == 'redirect') {

            // }
            
            $.ajax({
                url: 'set_notice_as_seen.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    'notice_id': id,
                    'table': table,
                    'ticket': ticket
                },
            }).done(function(response) {
                if (!response.success) {
                    $('#divResult').html(response.message); 
                } else if (action == 'redirect') {
                    redirect('./ticket_show.php?numero=' + ticket);
                } else {
                    location.reload();
                }
            });
            return false;
        }


        function getCountUserNotifications() {
            $.ajax({
                url: './get_count_user_notifications.php',
                method: 'POST',
                dataType: 'json',

            }).done(function(data) {
                if (data.notices_count != $('#loaded_notifications_count').val()) {
                    renderRefreshButton();
                }
            });
            return false;   
        }

        function renderRefreshButton() {
            $('#idLoad').css('display', 'block');
            $( document ).ready(function() {
                $('#idLoad').css('display', 'none');
            });

            let html = '';
            html += '<button type="button" class="btn btn-primary btn-sm" id="idRefresh" onclick="location.reload();"><i class="fas fa-sync-alt"></i>';
            html += '&nbsp;' + $('#refresh_button_text').val();
            html += '</button>';
            $('#refreshButton').html(html);
            return false;
        }


    </script>
</body>

</html>