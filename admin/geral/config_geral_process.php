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

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$post = $_POST;

$config = getConfig($conn);



$erro = false;
$screenNotification = "";
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['cod'] = (isset($post['cod']) ? intval($post['cod']) : "");
$data['numero'] = (isset($post['numero']) ? intval($post['numero']) : "");
$data['action'] = $post['action'];
$data['field_id'] = "";


// $data['lang_file'] = (isset($post['lang_file']) ? noHtml($post['lang_file']) : "");
$data['lang_file'] = (isset($post['lang_file']) ? noHtml(getLastPartOfPath($post['lang_file'])) : "");
$data['date_format'] = (isset($post['date_format']) ? noHtml($post['date_format']) : "");
$data['site'] = (isset($post['site']) ? noHtml($post['site']) : "");
$data['show_deprecated'] = (isset($post['show_deprecated']) ? ($post['show_deprecated'] == "yes" ? 1 : 0) : 0);
$data['allow_assets_btw_clients'] = (isset($post['allow_assets_btw_clients']) ? ($post['allow_assets_btw_clients'] == "yes" ? 1 : 0) : 0);
$data['allow_only_ops_assets_btw_clients'] = (isset($post['allow_only_ops_assets_btw_clients']) ? ($post['allow_only_ops_assets_btw_clients'] == "yes" ? 1 : 0) : 0);
if (!$data['allow_assets_btw_clients']) {
    $data['allow_only_ops_assets_btw_clients'] = 0;
}

$allowed_positions = ['default', 'top', 'bottom'];
$data['description_position'] = (isset($post['description_position']) && !empty($post['description_position']) ? noHtml($post['description_position']) : "default");

if (!in_array($data['description_position'], $allowed_positions)) {
    $data['description_position'] = 'default';
}



/** Tratamento para os pré filtros de categorias para tipos de solicitações */
$textPreFilters = "";
$data['pre_filters'] = (isset($post['pre_filters']) ? noHtml($post['pre_filters']) : "");
if (!empty($data['pre_filters'])) {
    $array_pre_filters = explode(',', $data['pre_filters']);
    // percorrer o array e verificar se cada elemento é um inteiro entre 1 e 6 (número de categorias existentes)
    foreach ($array_pre_filters as $pre_filter) {
        $pre_filter = intval($pre_filter);
        if ($pre_filter > 0 && $pre_filter <= 6) {
            if (strlen($textPreFilters) > 0)
                $textPreFilters .= ",";
            $textPreFilters .= $pre_filter;
        }
    }
    $data['pre_filters'] = $textPreFilters;
}


$data['allow_self_register'] = (isset($post['allow_self_register']) ? ($post['allow_self_register'] == "yes" ? 1 : 0) : 0);
$data['treat_own_ticket'] = (isset($post['treat_own_ticket']) ? ($post['treat_own_ticket'] == "yes" ? 1 : 0) : 0);
$data['isolate_areas'] = (isset($post['isolate_areas']) ? ($post['isolate_areas'] == "yes" ? 1 : 0) : 0);
$data['self_register_area'] = (isset($post['self_register_area']) ? noHtml($post['self_register_area']) : "");
$data['self_register_client'] = (isset($post['self_register_client']) ? noHtml($post['self_register_client']) : "");
$data['worktime_area_reference'] = (isset($post['worktime_area_reference']) ? noHtml($post['worktime_area_reference']) : "");
$data['msg'] = (isset($post['msg']) ? noHtml($post['msg']) : "");
$data['open_scheduling_status'] = (isset($post['open_scheduling_status']) ? noHtml($post['open_scheduling_status']) : "");
$data['edit_scheduling_status'] = (isset($post['edit_scheduling_status']) ? noHtml($post['edit_scheduling_status']) : "");

$data['scheduled_to_worker_status'] = (isset($post['scheduled_to_worker_status']) ? noHtml($post['scheduled_to_worker_status']) : "");
$data['status_in_worker_queue'] = (isset($post['status_in_worker_queue']) ? noHtml($post['status_in_worker_queue']) : "");
$data['reopening_status'] = (isset($post['reopening_status']) ? noHtml($post['reopening_status']) : 1);
$data['response_at_routing'] = (isset($post['response_at_routing']) ? noHtml($post['response_at_routing']) : "");


$data['status_done'] = (isset($post['status_done']) && $post['status_done'] != 4 ? (int)$post['status_done'] : "");
$data['status_done_rejected'] = (isset($post['status_done_rejected']) ? (int)$post['status_done_rejected'] : 1);
$data['time_to_close_after_done'] = (isset($post['time_to_close_after_done']) && ($post['time_to_close_after_done'] > 0) ? (int)$post['time_to_close_after_done'] : 0);
$data['only_weekdays_to_count'] = (isset($post['only_weekdays_to_count']) ? 1 : 0);

$data['default_automatic_rate'] = (isset($post['default_automatic_rate']) ? noHtml($post['default_automatic_rate']) : 'great');

/* Campo para custo e status para fluxo de autorização */
$data['tickets_cost_field'] = (isset($post['tickets_cost_field']) && !empty($post['tickets_cost_field']) ? (int)$post['tickets_cost_field'] : "");
$data['status_waiting_cost_auth'] = (isset($post['status_waiting_cost_auth']) && !empty($post['status_waiting_cost_auth']) ? (int)$post['status_waiting_cost_auth'] : "");
$data['status_cost_authorized'] = (isset($post['status_cost_authorized']) && !empty($post['status_cost_authorized']) ? (int)$post['status_cost_authorized'] : "");
$data['status_cost_refused'] = (isset($post['status_cost_refused']) && !empty($post['status_cost_refused']) ? (int)$post['status_cost_refused'] : "");
$data['status_cost_updated'] = (isset($post['status_cost_updated']) && !empty($post['status_cost_updated']) ? (int)$post['status_cost_updated'] : "");

$data['status_to_monitor'] = (isset($post['status_to_monitor']) && !empty($post['status_to_monitor']) ? array_map('intval', $post['status_to_monitor']) : []);
$data['status_to_monitor'] = (!empty($data['status_to_monitor']) ? implode(",", $data['status_to_monitor']) : "");
$data['days_to_close_by_inactivity'] = (isset($post['days_to_close_by_inactivity']) ? intval($post['days_to_close_by_inactivity']) : 7);
$data['only_weekdays_to_count_inactivity'] = (isset($post['only_weekdays_to_count_inactivity']) ? 1 : 0);
$data['default_inactivity_automatic_rate'] = (isset($post['default_inactivity_automatic_rate']) ? noHtml($post['default_inactivity_automatic_rate']) : 'great');
$data['status_out_inactivity'] = (isset($post['status_out_inactivity']) ? (int)$post['status_out_inactivity'] : 1);



$data['forward_status'] = (isset($post['forward_status']) ? noHtml($post['forward_status']) : "");
$data['justificativa'] = (isset($post['justificativa']) ? ($post['justificativa'] == "yes" ? 1 : 0) : 0);
$data['allow_reopen'] = (isset($post['allow_reopen']) ? ($post['allow_reopen'] == "yes" ? 1 : 0) : 0);
$data['reopen_deadline'] = (isset($post['reopen_deadline']) ? intval($post['reopen_deadline']) : 0);
$data['cfield_only_opened'] = (isset($post['cfield_only_opened']) ? ($post['cfield_only_opened'] == "yes" ? 1 : 0) : 0);

$data['sla_tolerance'] = (isset($post['sla_tolerance']) ? noHtml($post['sla_tolerance']) : "");
// $data['sla_tolerance'] = (!empty($data['sla_tolerance']) ? intval($data['sla_tolerance']) : "");

$data['img_max_size'] = (isset($post['img_max_size']) ? intval($post['img_max_size'])*1024*1024 : "");
$data['img_max_width'] = (isset($post['img_max_width']) ? noHtml($post['img_max_width']) : "");
$data['img_max_height'] = (isset($post['img_max_height']) ? noHtml($post['img_max_height']) : "");
$data['max_number_attachs'] = (isset($post['max_number_attachs']) ? intval($post['max_number_attachs']) : 0);

$data['days_before_expire'] = (isset($post['days_before_expire']) ? intval($post['days_before_expire']) : 0);

$data['area_to_alert'] = (isset($post['area_to_alert']) ? noHtml($post['area_to_alert']) : "");
$data['max_amount_batch_assets_record'] = (isset($post['max_amount_batch_assets_record']) && ($post['max_amount_batch_assets_record'] > 0) ? (int)$post['max_amount_batch_assets_record'] : 1);
$data['basic_users_can_request_as_others'] = (isset($post['basic_users_can_request_as_others']) ? ($post['basic_users_can_request_as_others'] == "yes" ? 1 : 0) : 0);

$data['assets_auto_department'] = (isset($post['assets_auto_department']) ? (int)$post['assets_auto_department'] : "");


// $data['label_prob_tipo_1'] = (isset($post['label_prob_tipo_1']) ? noHtml($post['label_prob_tipo_1']) : "");
// $data['label_prob_tipo_2'] = (isset($post['label_prob_tipo_2']) ? noHtml($post['label_prob_tipo_2']) : "");
// $data['label_prob_tipo_3'] = (isset($post['label_prob_tipo_3']) ? noHtml($post['label_prob_tipo_3']) : "");


// $fileTypes = "%%IMG%";
$fileTypes = "%%";
$fileTypes .= (isset($post['upld_img']) ? ($post['upld_img'] == "yes" ? "IMG%" : "") : "");
$fileTypes .= (isset($post['upld_txt']) ? ($post['upld_txt'] == "yes" ? "TXT%" : "") : "");
$fileTypes .= (isset($post['upld_odf']) ? ($post['upld_odf'] == "yes" ? "ODF%" : "") : "");
$fileTypes .= (isset($post['upld_ooo']) ? ($post['upld_ooo'] == "yes" ? "OOO%" : "") : "");
$fileTypes .= (isset($post['upld_pdf']) ? ($post['upld_pdf'] == "yes" ? "PDF%" : "") : "");
$fileTypes .= (isset($post['upld_mso']) ? ($post['upld_mso'] == "yes" ? "MSO%" : "") : "");
$fileTypes .= (isset($post['upld_nmso']) ? ($post['upld_nmso'] == "yes" ? "NMSO%" : "") : "");
$fileTypes .= (isset($post['upld_rtf']) ? ($post['upld_rtf'] == "yes" ? "RTF%" : "") : "");
$fileTypes .= (isset($post['upld_html']) ? ($post['upld_html'] == "yes" ? "HTML%" : "") : "");
$fileTypes .= (isset($post['upld_wav']) ? ($post['upld_wav'] == "yes" ? "WAV%" : "") : "");
$data['fileTypes'] = $fileTypes;

$formatBar = "%%";
$formatBar .= (isset($post['formatMural']) ? ($post['formatMural'] == "yes" ? "mural%" : "") : "");
$formatBar .= (isset($post['formatOco']) ? ($post['formatOco'] == "yes" ? "oco%" : "") : "");
$data['formatBar'] = $formatBar;

// var_dump($data); exit();

/* Checagem de preenchimento dos campos obrigatórios*/
if ($data['action'] == "edit") {

    if ($data['lang_file'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "lang_file";
    } elseif ($data['date_format'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "date_format";
    } elseif ($data['site'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "site";
    } elseif ($data['self_register_area'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "self_register_area";
    } elseif ($data['msg'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "msg";
    } elseif ($data['open_scheduling_status'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "open_scheduling_status";
    } elseif ($data['edit_scheduling_status'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "edit_scheduling_status";
    } elseif ($data['forward_status'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "forward_status";
    } 
    
    // elseif ($data['tickets_cost_field'] == "") {
    //     $data['success'] = false; 
    //     $data['field_id'] = "tickets_cost_field";
    // } 
    elseif ($data['status_waiting_cost_auth'] == "" && !empty($data['tickets_cost_field'])) {
        $data['success'] = false; 
        $data['field_id'] = "status_waiting_cost_auth";
    }
    
    elseif ($data['status_cost_authorized'] == "" && !empty($data['tickets_cost_field'])) {
        $data['success'] = false; 
        $data['field_id'] = "status_cost_authorized";
    }
    
    elseif ($data['status_cost_refused'] == "" && !empty($data['tickets_cost_field'])) {
        $data['success'] = false; 
        $data['field_id'] = "status_cost_refused";
    }
    
    elseif ($data['status_cost_updated'] == "" && !empty($data['tickets_cost_field'])) {
        $data['success'] = false; 
        $data['field_id'] = "status_cost_updated";
    }
    
    
    elseif ($data['sla_tolerance'] == "") {
        $data['success'] = false; 
    $data['field_id'] = "sla_tolerance";
    } elseif ($data['img_max_size'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "img_max_size";
    } elseif ($data['img_max_width'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "img_max_width";
    } elseif ($data['img_max_height'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "img_max_height";
    } /* elseif ($data['max_number_attachs'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "max_number_attachs";
    } */ /* elseif ($data['days_before_expire'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "days_before_expire";
    } */ elseif ($data['area_to_alert'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "area_to_alert";
    }

    if ($data['success'] == false) {
        $data['message'] = message('warning', '', TRANS('MSG_EMPTY_DATA'), '');
        echo json_encode($data);
        return false;
    }

    if (!filter_var($data['site'], FILTER_VALIDATE_URL)) {
        $data['success'] = false; 
        $data['field_id'] = "site";
        $data['message'] = message('warning', '', TRANS('WRONG_FORMATTED_URL'), '');
        echo json_encode($data);
        return false;
    }

    if ($data['status_done'] == "") {
        
        if ($data['time_to_close_after_done'] > 0) {
            $data['success'] = false; 
            $data['field_id'] = "status_done";
            $data['message'] = message('warning', '', TRANS('DEFINE_DONE_STATUS'), '');
            echo json_encode($data);
            return false;
        }
        $data['status_done'] = 4;
    }


    if (!empty($data['tickets_cost_field'])) {
        /* Testar para garantir que o campo customizado possui máscara adequada para moeda */

        $validCurrencyField = isCurrencyField($conn, $data['tickets_cost_field']);
        if (!$validCurrencyField) {
            $data['success'] = false; 
            $data['field_id'] = "tickets_cost_field";
            $data['message'] = message('warning', '', TRANS('MSG_COST_MUST_BE_WITH_MASK'), '');
            echo json_encode($data);
            return false;
        }

        // $valuesNotPassed = [];
        // $valuesToTest = [
        //     '1,00',
        //     '1,55',
        //     '10,00',
        //     '100,00',
        //     '1.000,00',
        //     '10.000,00',
        //     '100.000,00',
        //     '1.000.000,00',
        //     '10.000.000,00'
        // ];

        // $validField = true;
        // $cost_field_info = getCustomFields($conn, $data['tickets_cost_field']);
        // if ($cost_field_info['field_mask'] && $cost_field_info['field_mask_regex']) {
        //     foreach ($valuesToTest as $value) {
        //         if (!preg_match('/' . $cost_field_info['field_mask'] . '/i', $value)) {
        //             $validField = false;
        //             break;
        //         }
        //     }
        // } else {
        //     $validField = false;
        // }

        // if (!$validField) {
        //     $data['success'] = false; 
        //     $data['field_id'] = "tickets_cost_field";
        //     $data['message'] = message('warning', '', TRANS('MSG_COST_MUST_BE_WITH_MASK'), '');
        //     echo json_encode($data);
        //     return false;
        // }
    }



    if (!is_numeric($data['sla_tolerance'])) {
        $data['success'] = false; 
        $data['field_id'] = "sla_tolerance";
        $data['message'] = message('warning', '', TRANS('MSG_ERROR_WRONG_FORMATTED'), '');
        echo json_encode($data);
        return false;
    }

    if ($data['sla_tolerance'] < 0 || $data['sla_tolerance'] > 99) {
        $data['success'] = false; 
        $data['field_id'] = "sla_tolerance";
        $data['message'] = message('warning', '', TRANS('MSG_INVALID_SLA_TOLERANCE'), '');
        echo json_encode($data);
        return false;
    }


    /* Confere o tamanho máximo da imagem */
    if ($data['img_max_size'] > (1024 * 1024 * 20)) {
        /* 20mb */
        $data['success'] = false; 
        $data['field_id'] = "img_max_size";
        $data['message'] = message('warning', '', TRANS('FILE_TOO_HEAVY_IN_CONFIG'), '');
        echo json_encode($data);
        return false;
    }
    
    if ($data['max_number_attachs'] > 10) {
        $data['success'] = false; 
        $data['field_id'] = "max_number_attachs";
        $data['message'] = message('warning', '', TRANS('TOO_MANY_FILES'), '');
        echo json_encode($data);
        return false;
    }
}










/* Processamento */
if ($data['action'] == "edit") {

    /* Verificação de CSRF */
    if (!csrf_verify($post)) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');
        echo json_encode($data);
        return false;
    }

    /* ATUALIZAR TAMBÉM AS VARIÁVEIS DE SESSÃO */

    $exception = "";

    /* conf_prob_tipo_1 = '" . $data['label_prob_tipo_1'] . "', 
                conf_prob_tipo_2 = '" . $data['label_prob_tipo_2'] . "', 
                conf_prob_tipo_3 = '" . $data['label_prob_tipo_3'] . "',  */
    $sql = "UPDATE config SET 
                conf_language = '" . $data['lang_file'] . "', 
                conf_date_format = '" . $data['date_format'] . "', 
                conf_ocomon_site = '" . $data['site'] . "', 
                conf_schedule_status = '" . $data['open_scheduling_status'] . "', 
                conf_schedule_status_2 = '" . $data['edit_scheduling_status'] . "', 
                conf_foward_when_open = '" . $data['forward_status'] . "', 
                conf_desc_sla_out = '" . $data['justificativa'] . "', 
                conf_allow_reopen = '" . $data['allow_reopen'] . "', 
                conf_upld_size = '" . $data['img_max_size'] . "', 
                conf_upld_width = '" . $data['img_max_width'] . "', 
                conf_upld_height = '" . $data['img_max_height'] . "', 
                conf_qtd_max_anexos = '" . $data['max_number_attachs'] . "', 
                conf_formatBar = '" . $data['formatBar'] . "', 
                conf_days_bf = '" . $data['days_before_expire'] . "', 
                conf_wrty_area = '" . $data['area_to_alert'] . "', 
                conf_upld_file_types = '" . $data['fileTypes'] . "', 
                conf_wt_areas = '" . $data['worktime_area_reference'] . "', 
                conf_sla_tolerance = '" . $data['sla_tolerance'] . "', 
                conf_isolate_areas = '" . $data['isolate_areas'] . "', 
                conf_allow_op_treat_own_ticket = '" . $data['treat_own_ticket'] . "', 
                conf_cfield_only_opened = '" . $data['cfield_only_opened'] . "', 
                conf_reopen_deadline = '" . $data['reopen_deadline'] . "', 
                conf_status_scheduled_to_worker = '" . $data['scheduled_to_worker_status'] . "', 
                conf_status_in_worker_queue = '" . $data['status_in_worker_queue'] . "',
                set_response_at_routing = '" . $data['response_at_routing'] . "', 
                conf_status_reopen = '" . $data['reopening_status'] . "', 
                conf_status_done = '" . $data['status_done'] . "', 
                conf_status_done_rejected = '" . $data['status_done_rejected'] . "', 
                conf_time_to_close_after_done = '" . $data['time_to_close_after_done'] . "',
                conf_only_weekdays_to_count_after_done = '" . $data['only_weekdays_to_count'] . "',
                conf_rate_after_deadline = '" . $data['default_automatic_rate'] . "',
                conf_cat_chain_at_opening = " . dbField($data['pre_filters'], "text") . ",
                tickets_cost_field = " . dbField($data['tickets_cost_field'], "text") . ",
                status_waiting_cost_auth = " . dbField($data['status_waiting_cost_auth'], "text") . ",
                status_cost_authorized = " . dbField($data['status_cost_authorized'], "text") . ",
                status_cost_refused = " . dbField($data['status_cost_refused'], "text") . ",
                status_cost_updated = " . dbField($data['status_cost_updated'], "text") . ",
                stats_to_close_by_inactivity = " . dbField($data['status_to_monitor'], 'text') . ",
                days_to_close_by_inactivity = '" . $data['days_to_close_by_inactivity'] . "',
                stat_out_inactivity = '" . $data['status_out_inactivity'] . "', 
                only_weekdays_to_count_inactivity = '" . $data['only_weekdays_to_count_inactivity'] . "',
                rate_after_close_by_inactivity = '" . $data['default_inactivity_automatic_rate'] . "'    
                ";
		
    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('MSG_SUCCESS_CONFIG');

        $_SESSION['s_formatBarMural'] = ((strpos($data['formatBar'], '%mural%')) ? 1 : 0);
        $_SESSION['s_formatBarOco'] = ((strpos($data['formatBar'], '%oco%')) ? 1 : 0);
        $_SESSION['s_date_format'] = $data['date_format'];
        $_SESSION['s_allow_reopen'] = $data['allow_reopen'];
        $_SESSION['s_language'] = $data['lang_file'];
        $_SESSION['s_wt_areas'] = $data['worktime_area_reference'];

        $sql = "UPDATE configusercall SET 
				conf_user_opencall = '" . $data['allow_self_register'] . "', 
				conf_ownarea = '" . $data['self_register_area'] . "', 
				conf_scr_auto_client = " . dbField($data['self_register_client']) . ", 
                conf_scr_msg = '" . $data['msg'] . "' WHERE conf_cod = 1 ";
        
        try {
            $conn->exec($sql);
        }
        catch (Exception $e) {
            $exception .= $e->getMessage();
        }
        

        /* Configurações extras - a partir da versão 6 todas as novas configurações serão gravadas em config_keys */
        $dataConfigKeys = [];
        $dataConfigKeys['SHOW_DEPRECATED'] = $data['show_deprecated'];
        $dataConfigKeys['ALLOW_BASIC_USERS_REQUEST_AS_OTHERS'] = $data['basic_users_can_request_as_others'];
        $dataConfigKeys['MAX_AMOUNT_BATCH_ASSETS_RECORD'] = $data['max_amount_batch_assets_record'];
        $dataConfigKeys['ALLOW_USER_GET_ASSETS_BTW_CLIENTS'] = $data['allow_assets_btw_clients'];
        $dataConfigKeys['ALLOW_ONLY_OPS_GET_ASSETS_BTW_CLIENTS'] = $data['allow_only_ops_assets_btw_clients'];
        $dataConfigKeys['TICKET_DESCRIPTION_POS'] = $data['description_position'];
        $dataConfigKeys['ASSETS_AUTO_DEPARTMENT'] = $data['assets_auto_department'];

        $configKeysErrors = [];
        /* Atualização da configuração geral */
        foreach ($dataConfigKeys as $key => $value) {
            
            if (!setConfigValue($conn, $key, $value)) {
                $configKeysErrors[] = $key;
            }
        }

        
    } catch (Exception $e) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_SAVE_RECORD') . "<hr>" . $sql;
        $_SESSION['flash'] = message('danger', '', $data['message'], '');
        echo json_encode($data);
        return false;
    }

} 

if (!empty($exception) || !empty($configKeysErrors)) {
    $exception .= "<hr>" . implode("<hr>", $configKeysErrors);
    $data['message'] = $data['message'] . "<hr>" . $exception;
}

$_SESSION['flash'] = message('success', '', $data['message'], '');
echo json_encode($data);
return false;

echo json_encode($data);