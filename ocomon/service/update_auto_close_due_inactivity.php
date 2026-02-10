<?php
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

include (__DIR__ . "/" . "../../includes/config.inc.php");
include (__DIR__ . "/" . "../../includes/versao.php");
require_once (__DIR__ . "/" . "../../includes/functions/functions.php");
require_once (__DIR__ . "/" . "../../includes/functions/dbFunctions.php");
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

require_once  __DIR__ . "/" . "../../api/ocomon_api/vendor/autoload.php";

use OcomonApi\Support\Email;
use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$exception = "";
$testing = false;

/* 1: A data de referência será a data do último assentamento do solicitante com base na data corrente, 
    ignorando a data da alteração do status para qualquer dos status configurados para serem monitorados   
*/
/* 2: A data de referência será considerada a partir da mudança de status para qualquer dos status 
configurados para serem monitorados 
*/
$referenceDateConfig = 2;

$now = date("Y-m-d H:i:s");
$configExt = getConfigValues($conn);
$config = getConfig($conn);
$mailConfig = getMailConfig($conn);

$statusToMonitor = $config['stats_to_close_by_inactivity'];
/* Status que deverão ser monitorados quanto à inatividade */
if (empty($statusToMonitor)) {
    return false;
}
$arrayStatusToMonitor = explode(',', (string)$statusToMonitor);
$textStatusToMonitor = implode(', ', $arrayStatusToMonitor);

$maxDaysOfInactivity = $config['days_to_close_by_inactivity'];
/* Apenas para teste imediato */
// $maxDaysOfInactivity = 0;


$onlyBusinessDays = ($config['only_weekdays_to_count_inactivity'] ? true : false);
$automaticRate = $config['rate_after_close_by_inactivity'];

$entry = TRANS('TICKET_AUTO_CLOSED_DUE_INACTIVITY');

/* Gera uma data de referência, no passado, com base na data corrente e o maximo de dias de inatividade
    - 
*/
$dateFrom = subDaysFromDate($now, $maxDaysOfInactivity, $onlyBusinessDays);
// $dateFrom = "2023-09-13 21:20:00";

$usersToIgnore = [];
/* Usuário configurado para abertura de chamados por email */
$stringUserTicketByEmail = $configExt['API_TICKET_BY_MAIL_USER'];
if (!empty($stringUserTicketByEmail)) {
    $usersToIgnore[] = getUserInfo($conn, null, $stringUserTicketByEmail)['user_id'];
}

/* Usuário configurado para abertura de chamados sem autenticação */
$userOpenForm = $configExt['ANON_OPEN_USER'];
if (!empty($userOpenForm)) {
    $usersToIgnore[] = (int)$userOpenForm;
}

$terms = "";
if (!empty($usersToIgnore)) {
    $usersToIgnore = implode(',', $usersToIgnore);
    $terms = "AND o.aberto_por NOT IN ({$usersToIgnore})";
}


if ($testing) {
    var_dump([
        'dateFrom' => $dateFrom,
        'usersToIgnore' => $usersToIgnore,
        'arrayStatusToMonitor' => $arrayStatusToMonitor
    ]);
}


/** Pré-filtro de chamados elegíveis para encerramento de forma automática 
 * Não é definitivo pois é necessário tratar casos onde o resultado da função "subDaysFromDate" não é preciso
 *  (em função de aberturas realizadas em finais de semana e a configuração for apenas para dias uteis)
 * 
 * Elegíveis para encerramento automático:
 * - chamados com status em ($textStatusToMonitor);
 * - O solicitante não pode ser um usuário de abertura por e-mail ou por formulário sem autenticação
 * - o último comentário/assentamento do solicitante seja anterior ao $dateFrom
 * - o último assentamento do chamado não tenha sido feito pelo solicitante e sim por outro operador
 * - não importa a data do assentamento/comentário caso o responsável não seja o solicitante do chamado
 * 
 * Caso não exista nenhum assentamento/comentário realizado pelo solicitante, a data de abertura do chamado
 * será utilizada como referência para a contagem do tempo decorrido
*/

$sql = "SELECT
            o.numero
        FROM
            ocorrencias o,
            assentamentos a
        WHERE
            o.numero = a.ocorrencia AND
            a.responsavel = o.aberto_por AND
            o.operador <> o.aberto_por AND
            a.created_at <= '{$dateFrom}' AND 

            a.numero = ( SELECT MAX(numero) FROM assentamentos WHERE ocorrencia = o.numero AND responsavel = o.aberto_por) AND
            
            a.numero <> ( SELECT MAX(numero) FROM assentamentos WHERE ocorrencia = o.numero ) AND
            
            o.status IN ({$textStatusToMonitor})
            -- o.status NOT IN (4)
            {$terms}
        ORDER BY
            o.numero
            
            ";


/* Chamados em que o solicitante não fez nenhum comentário - Neste caso, considero a data de abertura do chamado como referência */
$sql2 = "SELECT 
            o.numero
            -- , o.aberto_por
            FROM
                ocorrencias o, assentamentos a
            WHERE
                o.numero = a.ocorrencia AND
                o.aberto_por NOT IN (
                    SELECT responsavel FROM assentamentos WHERE ocorrencia = o.numero  
                    -- AND `created_at` <= '{$dateFrom}'
                ) AND

                o.data_abertura <= '{$dateFrom}' AND
                o.operador <> o.aberto_por AND
                o.status IN ({$textStatusToMonitor})
                -- o.status NOT IN (4)
                {$terms}
            GROUP BY 
                o.numero
            ORDER BY
                o.numero

";

// dump($sql2);

/* Chamados com status de monitoramento e que o assentamento feito pelo solicitante já superou o 
tempo limite e o último assentamento foi feito por algum operador - 
o último assentamento não foi realizado pelo solicitante */
$res = $conn->query($sql);
$tickets = [];
if ($res->rowCount()) {
    foreach ($res->fetchAll() as $rowTicket) {
        $tickets[] = $rowTicket['numero'];
    }
}

/* Chamados em status de monitoramento e que já foram abertos à mais tempo do que o limite e não possuem nenhum 
    assentamento pelo solicitante
 */
$res = $conn->query($sql2);
if ($res->rowCount()) {
    foreach ($res->fetchAll() as $rowTicket) {
        $tickets[] = $rowTicket['numero'];
    }
}

$tickets = array_unique($tickets);
sort($tickets);
if ($testing) {
    var_dump($tickets);
}

/* Para cada ticket elegível, avaliar se pode ser encerrado automaticamente */
foreach ($tickets as $ticket) {
    
    $sql = "SELECT 
                o.numero,
                o.aberto_por,
                o.status,
                o.data_atendimento,
                a.created_at as `data`
            FROM
                ocorrencias o, assentamentos a
            WHERE 
                o.numero = a.ocorrencia AND
                o.aberto_por = a.responsavel AND
                a.numero = (select max(numero) from assentamentos where ocorrencia = {$ticket} and responsavel = o.aberto_por) and
                o.numero = {$ticket}
            ";
    
    $res = $conn->query($sql);

    
    if (!$res->rowCount()) {
        /* Nenhum assentamento do próprio solicitante, neste caso, a checagem é em relação à data de abertura do chamado */
        $sql = "SELECT 
                o.numero, 
                o.aberto_por, 
                o.status, 
                o.data_atendimento, 
                o.data_abertura as `data` 
            FROM
                ocorrencias o
            WHERE 
                o.numero = {$ticket}
            ";
        $res = $conn->query($sql);
    }
    
    if ($res->rowCount()) {
        
        $row = $res->fetch();

        $referenceDate = $row['data'];
        if ($referenceDateConfig == 2) {
            /* Considera a data da última alteração de status do chamado */
            $referenceDate = getTicketLastChangeDateByLogKey($conn, $row['numero'], 'log_status');
            if (empty($referenceDate)) {
                $referenceDate = $row['data'];
            }
        }
        
        /** Data limite para comentário do solicitante do chamado */
        $deadlineToActionFromRequester = addDaysToDate($referenceDate, $maxDaysOfInactivity, $onlyBusinessDays);
        // $deadlineToActionFromRequester = "2023-09-13 20:47:00";

        /** Aqui cada ticket elegível é checado individualmente para se ter certeza que pode ser encerrado automaticamente */
        $canBeAutoClosed = $deadlineToActionFromRequester < $now ;

        
        if ($testing) {
            var_dump([
                'ticket' => $row['numero'],
                'status' => $row['status'],
                'Data de referência (ou alteração de status ou último comentário do solicitante)' => $referenceDate,
                'data máxima para ação do solicitante' => $deadlineToActionFromRequester,
                'hoje' => $now ,
                'Pode ser auto encerrado?' => $canBeAutoClosed
            ]);
        }
        

        if ($canBeAutoClosed && !$testing) {

            $termsToClose = "";
            if (empty($row['data_atendimento'])) {
                $termsToClose .= " data_atendimento = '" . $now . "', ";
            }

            /* Atualiza as informações do chamado */
            $sql = "UPDATE 
                        ocorrencias 
                        SET 
                        `status`= 4, 
                        {$termsToClose}
                        data_fechamento = '" . $now . "',
                        oco_scheduled = 0
                    WHERE
                        numero = {$row['numero']}
            ";

            try {
                $res = $conn->exec($sql);
                /* Atualiza ou insere a avaliação do chamado */
                if (hasRatingRow($conn, $row['numero'])) {
                    $sqlRating = "UPDATE 
                                    tickets_rated
                                SET 
                                    rate = '{$automaticRate}',
                                    rate_date = '{$now}',
                                    automatic_rate = 1
                                WHERE 
                                    ticket = {$row['numero']}
                                    ";
                } else {
                    $sqlRating = "INSERT INTO tickets_rated
                                    (
                                        ticket, 
                                        rate, 
                                        rate_date,
                                        automatic_rate 
                                    )
                                    VALUES
                                    (
                                        {$row['numero']},
                                        '{$automaticRate}',
                                        '{$now}',
                                        1
                                    )";
                }

                try {
                    $resRating = $conn->exec($sqlRating);

                    /* Assentamento - Comentário - Tipo de assentamento obtido pela função getEntryType */
                    $sqlEntry = "INSERT INTO assentamentos 
                                    (ocorrencia, assentamento, created_at, responsavel, tipo_assentamento) 
                                    VALUES 
                                    (" . $row['numero'] . ", '{$entry}', '{$now}', 0 , 30 )"; //tratar usuario 0 (zero) para ser do sistema
                    try {
                        $resultAssent = $conn->exec($sqlEntry);

                        $notice_id = $conn->lastInsertId();
                        // $ticketData = getTicketData($conn, $data['numero'], ['aberto_por']);
                        // if ($_SESSION['s_uid'] != $ticketData['aberto_por']) {
                        setUserTicketNotice($conn, 'assentamentos', $notice_id);
                        // }

                        
                        /* Arrays para a função recordLog */
                        $arrayBeforePost = [];
                        $arrayBeforePost['status_cod'] = $row['status'];
                        $afterPost = [];
                        $afterPost['status'] = 4;

                        /* Função que grava o registro de alterações do chamado */
                        $recordLog = recordLog($conn, $row['numero'], $arrayBeforePost, $afterPost, 20, 0);

                        /* A primeira entrada serve apenas para gravar a conclusão do status anterior ao encerramento */
                        $stopTimeStage = insert_ticket_stage($conn, $row['numero'], 'stop', 4);

                        $startTimeStage = insert_ticket_stage($conn, $row['numero'], 'start', 4, null, $now);
                        $stopTimeStage = insert_ticket_stage($conn, $row['numero'], 'stop', 4, null, $now);
                    }
                    catch (Exception $e) {
                        $exception .= "<hr>" . $e->getMessage();
                    }

                }
                catch (Exception $e) {
                    $exception .= "<hr>" . $e->getMessage();
                }

                /* Atualização do campo auto_closed na tabela tickets_extended */
                setOrUpdateTicketExtendedInfoByCols($conn, $row['numero'], ['auto_closed'], [1]);

                /* Envio do email para cada chamado encerrado automaticamente */
                /* Variáveis de ambiente para envio de e-mail: todos os actions */
                $VARS = getEnvVarsValues($conn, $row['numero']);

                $mailSendMethod = 'send';
                if ($mailConfig['mail_queue']) {
                    $mailSendMethod = 'queue';
                }

                $requesterInfo = getUserInfo($conn, $row['aberto_por']);
                $recipient = $requesterInfo['email'];

                $event = 'closed-by-inactivity';
		        $eventTemplate = getEventMailConfig($conn, $event);

                /* Disparo do e-mail (ou fila no banco) para o contato */
                $mail = (new Email())->bootstrap(
                    transvars($eventTemplate['msg_subject'], $VARS),
                    transvars($eventTemplate['msg_body'], $VARS),
                    $recipient,
                    $eventTemplate['msg_fromname'],
                    $row['numero']
                );

                if (!$mail->{$mailSendMethod}()) {
                    $mailNotification .= "<hr>" . TRANS('EMAIL_NOT_SENT') . "<hr>" . $mail->message()->getText();
                }
                
            }
            catch (Exception $e) {
                $exception .= "<hr>" . $e->getMessage();
            }            
        }
    }
}
    
echo $exception;
echo $now;

return true;
