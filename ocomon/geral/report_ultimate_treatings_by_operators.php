<?php
/* Copyright 2023 Flávio Ribeiro

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
 */ session_start();

if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
    $_SESSION['session_expired'] = 1;
    echo "<script>top.window.location = '../../index.php'</script>";
    exit;
}

require_once __DIR__ . "/" . "../../includes/include_geral_new.inc.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2, 1);

$_SESSION['s_page_ocomon'] = $_SERVER['PHP_SELF'];

$sess_client = (isset($_SESSION['s_rep_filters']['client']) ? $_SESSION['s_rep_filters']['client'] : '');
$sess_area = (isset($_SESSION['s_rep_filters']['area']) ? $_SESSION['s_rep_filters']['area'] : '-1');
$sess_d_ini = (isset($_SESSION['s_rep_filters']['d_ini']) ? $_SESSION['s_rep_filters']['d_ini'] : date('01/m/Y'));
$sess_d_fim = (isset($_SESSION['s_rep_filters']['d_fim']) ? $_SESSION['s_rep_filters']['d_fim'] : date('d/m/Y'));
$sess_state = (isset($_SESSION['s_rep_filters']['state']) ? $_SESSION['s_rep_filters']['state'] : 1);

$areas_list = getAreas($conn, 0, 1, 1);

if (isAreasIsolated($conn) && $_SESSION['s_nivel'] != 1) {
    /* Visibilidade isolada entre áreas para usuários não admin */
    $array_areas = explode(",", $_SESSION['s_uareas']);
    $areas_list = getAreas($conn, 0, 1, 1, $array_areas);
}



$json = 0;
$json2 = 0;

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/jquery/datetimepicker/jquery.datetimepicker.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/datatables/datatables.min.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/css/my_datatables.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap-select/dist/css/bootstrap-select.min.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/css/my_bootstrap_select.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/estilos_custom.css" />

    <style>

        caption {
            /* caption-side: top; */
            line-height: 1.8em;
        }

        caption.title {
            caption-side: top;
            /* font-size: large; */
        }

        span.title {
            font-size: large;
        }
        .chart-container {
            position: relative;
            /* height: 100%; */
            max-width: 100%;
            margin-left: 10px;
            margin-right: 10px;
            margin-bottom: 30px;
        }
    </style>

    <title><?= APP_NAME; ?>&nbsp;<?= VERSAO; ?></title>
</head>

<body>
    
    <div class="container">
        <div id="idLoad" class="loading" style="display:none"></div>
    </div>


    <div class="container">
        <h5 class="my-4"><i class="fas fa-user-md text-secondary"></i>&nbsp;<?= TRANS('REPORT_TREATINGS_AND_PARTICIPATIONS'); ?></h5>
        <div class="modal" id="modal" tabindex="-1" style="z-index:9001!important">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div id="divDetails">
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" name="report-mainlogo" class="report-mainlogo" id="report-mainlogo"/>
        <input type="hidden" name="logo-base64" id="logo-base64"/>

        <?php
        if (isset($_SESSION['flash']) && !empty($_SESSION['flash'])) {
            echo $_SESSION['flash'];
            $_SESSION['flash'] = '';
        }


        if (!isset($_POST['action'])) {

        ?>
            <form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">
                <div class="form-group row my-4">


                    <label for="client" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('CLIENT'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control bs-select" id="client" name="client">
                            <option value="" selected><?= TRANS('ALL'); ?></option>
                            <?php
                                $clients = getClients($conn);
                                foreach ($clients as $client) {
                                    ?>
                                    <option value="<?= $client['id']; ?>"
                                    <?= ($client['id'] == $sess_client ? ' selected' : ''); ?>
                                    ><?= $client['nickname']; ?></option>
                                    <?php
                                }
                            ?>
                        </select>
                    </div>

                    <label for="area" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('RESPONSIBLE_AREA'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control bs-select" id="area" name="area">
                            <option value=""><?= TRANS('ALL'); ?></option>
                            <?php
                            foreach ($areas_list as $rowArea) {
                                print "<option value='" . $rowArea['sis_id'] . "'";
                                echo ($rowArea['sis_id'] == $sess_area ? ' selected' : '');
                                print ">" . $rowArea['sistema'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <label for="treater" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('WORKER'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control bs-select" id="treater" name="treater">
                            <option value=""><?= TRANS('ALL'); ?></option>
                            <?php
                            $users = getUsers($conn, null, [1,2]);
                            foreach ($users as $user) {
                                ?>
                                <option value="<?= $user['user_id']; ?>"><?= $user['nome']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>




                    <label for="d_ini" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('START_DATE'); ?></label>
                    <div class="form-group col-md-10">
                        <input type="text" class="form-control " id="d_ini" name="d_ini" value="<?= $sess_d_ini; ?>" autocomplete="off" required />
                    </div>

                    <label for="d_fim" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('END_DATE'); ?></label>
                    <div class="form-group col-md-10">
                        <input type="text" class="form-control " id="d_fim" name="d_fim" value="<?= $sess_d_fim; ?>" autocomplete="off" required />
                    </div>

                    <label for="state" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('CONTEXT'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control sel2" id="state" name="state">
                            <option value="1" <?= ($sess_state == 1 ? ' selected': ''); ?>><?= TRANS('STATE_OPEN_CLOSE_IN_SEARCH_RANGE'); ?></option>
                            <option value="4"<?= ($sess_state == 4 ? ' selected': ''); ?>><?= TRANS('STATE_OPEN_ANY_TIME_CLOSE_IN_SEARCH_RANGE'); ?></option>
                        </select>
                    </div>


                    <div class="row w-100"></div>
                    <div class="form-group col-md-8 d-none d-md-block">
                    </div>
                    <div class="form-group col-12 col-md-2 ">

                        <input type="hidden" name="action" value="search">
                        <button type="submit" id="idSubmit" name="submit" class="btn btn-primary btn-block"><?= TRANS('BT_SEARCH'); ?></button>
                    </div>
                    <div class="form-group col-12 col-md-2">
                        <button type="reset" class="btn btn-secondary btn-block" onClick="parent.history.back();"><?= TRANS('BT_CANCEL'); ?></button>
                    </div>
                    

                </div>
            </form>
            <?php
        }

        ?>
        <div id="divResult"></div>
        
        <div id="tables_container">
            
            <div id="total_tickets" class="text-secondary mb-4"></div>
        
            <!-- Elemento apenas para servir de referência para o observer caso alguma das tabelas não possuam informações -->
            <div id="enable_list_tables" ></div> <!-- class="d-none" -->

            <div id="table_1_msg" class="mt-5 mb-0"></div>
            <div class="display-buttons"></div>
            <div id="table_1" class="table-responsive"></div>
            <div class="chart-container" id="container01"></div>
            <hr/>

            <div id="table_2_msg" class="mt-5 mb-0"></div>
            <div class="display-buttons"></div>
            <div id="table_2" class="table-responsive"></div>
            <div class="chart-container" id="container02"></div>
            <hr/>

            <div id="table_3_msg" class="mt-5 mb-0"></div>
            <div class="display-buttons"></div>
            <div id="table_3" class="table-responsive"></div>
            <div class="chart-container" id="container03"></div>
            <hr/>
            
            <div id="table_4_msg" class="mt-5 mb-0"></div>
            <div class="display-buttons"></div>
            <div id="table_4" class="table-responsive"></div>
            <div class="chart-container" id="container04"></div>
            <hr/>

            <div id="table_5_msg" class="mt-5 mb-0"></div>
            <div class="display-buttons"></div>
            <div id="table_5" class="table-responsive"></div>
            <div class="chart-container" id="container05"></div>
            <hr/>

            <div id="table_consolidated_msg" class="mt-5 mb-0"></div>
            <div class="display-buttons"></div>
            <div id="table_consolidated" class="table-responsive"></div>
            <div class="chart-container" id="container06"></div>
            <hr/>
        </div>
        
        
    <script src="../../includes/javascript/funcoes-3.0.js"></script>
    <script src="../../includes/components/jquery/jquery.js"></script>
    <script src="../../includes/components/jquery/jquery.initialize.min.js"></script>
    <script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>
    <script src="../../includes/components/jquery/datetimepicker/build/jquery.datetimepicker.full.min.js"></script>
    <script src="../../includes/components/bootstrap/js/bootstrap.bundle.js"></script>
    <script type="text/javascript" src="../../includes/components/chartjs/dist/Chart.min.js"></script>
    <script type="text/javascript" src="../../includes/components/chartjs/chartjs-plugin-colorschemes/dist/chartjs-plugin-colorschemes.js"></script>
    <script type="text/javascript" src="../../includes/components/chartjs/chartjs-plugin-datalabels/chartjs-plugin-datalabels.min.js"></script>
    
    <script src="../../includes/components/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="./js/default_chart_generate.js"></script>


    <script type='text/javascript'>
        $(function() {
            
            
            $.fn.selectpicker.Constructor.BootstrapVersion = '4';
            $('.bs-select').selectpicker({
                /* placeholder */
                title: "<?= TRANS('ALL', '', 1); ?>",
                liveSearch: true,
                liveSearchNormalize: true,
                liveSearchPlaceholder: "<?= TRANS('BT_SEARCH', '', 1); ?>",
                noneResultsText: "<?= TRANS('NO_RECORDS_FOUND', '', 1); ?> {0}",
                style: "",
                styleBase: "form-control ",
            });

            $('#new_search').css('cursor', 'pointer').on('click', function(){
                window.history.back();
            });
            
            /* Idioma global para os calendários */
            $.datetimepicker.setLocale('pt-BR');
            
            /* Calendários de início e fim do período */
            $('#d_ini').datetimepicker({
                format: 'd/m/Y',
                onShow: function(ct) {
                    this.setOptions({
                        maxDate: $('#d_fim').datetimepicker('getValue')
                    })
                },
                timepicker: false
            });
            $('#d_fim').datetimepicker({
                format: 'd/m/Y',
                onShow: function(ct) {
                    this.setOptions({
                        minDate: $('#d_ini').datetimepicker('getValue')
                    })
                },
                timepicker: false
            });


            $('#idSubmit').on('click', function() {
                var loading = $(".loading");
                $(document).ajaxStart(function() {
                    loading.show();
                });
                $(document).ajaxStop(function() {
                    loading.hide();
                });

                setLogoSrc();

                $.ajax({
					url: './report_ultimate_treatings_by_operators_process.php',
					method: 'POST',
					data: $('#form').serialize(),
					dataType: 'json',
				}).done(function(data) {

					if (!data.success) {
						$('#divResult').html(data.message);
						$('input, select, textarea').removeClass('is-invalid');
						if (data.field_id != "") {
							$('#' + data.field_id).focus().addClass('is-invalid');
						}
                        $('#total_tickets').html('');
                        $('#table_1_msg').html('');
                        $('#table_2_msg').html('');
                        $('#table_3_msg').html('');
                        $('#table_4_msg').html('');
                        $('#table_5_msg').html('');
                        $('#table_consolidated_msg').html('');
                        
                        
                        $('#enable_list_tables').html('');
                        $('#table_1').html('');
                        $('#table_2').html('');
                        $('#table_3').html('');
                        $('#table_4').html('');
                        $('#table_5').html('');
                        $('#table_consolidated').html('');
                        $('#container01').empty();
                        $('#container02').empty();
                        $('#container03').empty();
                        $('#container04').empty();
                        $('#container05').empty();
                        $('#container06').empty();

						$("#idSubmit").prop("disabled", false);
					} else {
                        /* Aqui ocorrerá as chamadadas para a montagem das tabelas e também para os gráficos */
                        $('#table_1_msg').html('');
                        $('#table_2_msg').html('');
                        $('#table_3_msg').html('');
                        $('#table_4_msg').html('');
                        $('#table_5_msg').html('');
                        $('#table_consolidated_msg').html('');
                        
                        $('#enable_list_tables').html('');
                        $('#table_1').html('');
                        $('#table_2').html('');
                        $('#table_3').html('');
                        $('#table_4').html('');
                        $('#table_5').html('');
                        $('#table_consolidated').html('');
                        $('#container01').empty();
                        $('#container02').empty();
                        $('#container03').empty();
                        $('#container04').empty();
                        $('#container05').empty();
                        $('#container06').empty();
                        

                        $('#total_tickets').html('<h4>' + data['total_msg'] + '</h4>');

                        let list_tables = '<input type="hidden" name="list_tables" id="list_tables" value="' + data.sucess + '" />';
                        $('#enable_list_tables').html(list_tables);

                        /* Tabela 1 */
                        if (data['table_1'].records > 0) {
                            $('#table_1_msg').html(data['table_1'].msg_context);
                        
                            let table = '<table id="table_list_1" class="table table-striped table-bordered" cellspacing="0" width="100%">';
                            table += '<caption class="title"><span class="title">' + data['table_1'].title + '</span><br />' + data.criteria + '</caption>';

                            table += '<thead>';
                                table += '<tr class="header table-borderless">';
                                    table += '';
                                    table += '<td class="line"><?= mb_strtoupper(TRANS('WORKER')); ?></td>';
                                    table += '<td class="line"><?= mb_strtoupper(TRANS('COL_AMOUNT')); ?></td>';
                                table += '</tr>';

                            table += '</thead>';
                            table += '</tbody>';
                            let totalRecords = 0;
                            for (var i in data['table_1']) {

                                if (data['table_1'][i].operator !== undefined) {
                                    table += '<tr>';
                                    table += '<td class="line">' + data['table_1'][i].operator + '</td>';
                                    table += '<td data-sort="' + data['table_1'][i].amount + '" class="line">' + data['table_1'][i].amount + '</td>';
                                    table += '</tr>';
                                }
                            }
                            table += '</tbody>';
                            table += '<tfoot>';
                            table += '<tr class="header table-borderless">';
                                    table += '<td class="line"><?= TRANS('TOTAL'); ?></td>';
                                    table += '<td class="line">' + data['table_1'].sum + '</td>';
                                table += '</tr>';
                            table += '</tfoot>';
                            table += '</table>';

                            $('#table_1').html(table);
                        } else {
                            $('#table_1_msg').html(data['table_1'].msg_no_results);
                        }


                        /* Tabela 2 */
                        if (data['table_2'].records > 0) {
                            $('#table_2_msg').html(data['table_2'].msg_context);
                        
                            let table = '<table id="table_list_2" class="table table-striped table-bordered" cellspacing="0" width="100%">';
                            table += '<caption class="title"><span class="title">' + data['table_2'].title + '</span><br />' + data.criteria + '</caption>';

                            table += '<thead>';
                                table += '<tr class="header table-borderless">';
                                    table += '';
                                    table += '<td class="line"><?= mb_strtoupper(TRANS('WORKER')); ?></td>';
                                    table += '<td class="line"><?= mb_strtoupper(TRANS('COL_AMOUNT')); ?></td>';
                                table += '</tr>';

                            table += '</thead>';
                            table += '</tbody>';
                            let totalRecords = 0;
                            for (var i in data['table_2']) {

                                if (data['table_2'][i].operator !== undefined) {
                                    table += '<tr>';
                                    table += '<td class="line">' + data['table_2'][i].operator + '</td>';
                                    table += '<td data-sort="' + data['table_2'][i].amount + '" class="line">' + data['table_2'][i].amount + '</td>';
                                    table += '</tr>';
                                }
                            }
                            table += '</tbody>';
                            table += '<tfoot>';
                            table += '<tr class="header table-borderless">';
                                    table += '<td class="line"><?= TRANS('TOTAL'); ?></td>';
                                    table += '<td class="line">' + data['table_2'].sum + '</td>';
                                table += '</tr>';
                            table += '</tfoot>';
                            table += '</table>';

                            $('#table_2').html(table);
                        } else {
                            $('#table_2_msg').html(data['table_2'].msg_no_results);
                        }


                        /* Tabela _3 */
                        if (data['table_3'].records > 0) {
                            $('#table_3_msg').html(data['table_3'].msg_context);
                        
                            let table = '<table id="table_list_3" class="table table-striped table-bordered" cellspacing="0" width="100%">';
                            table += '<caption class="title"><span class="title">' + data['table_3'].title + '</span><br />' + data.criteria + '</caption>';

                            table += '<thead>';
                                table += '<tr class="header table-borderless">';
                                    table += '';
                                    table += '<td class="line"><?= mb_strtoupper(TRANS('WORKER')); ?></td>';
                                    table += '<td class="line"><?= mb_strtoupper(TRANS('COL_AMOUNT')); ?></td>';
                                table += '</tr>';

                            table += '</thead>';
                            table += '</tbody>';
                            let totalRecords = 0;
                            for (var i in data['table_3']) {

                                if (data['table_3'][i].operator !== undefined) {
                                    table += '<tr>';
                                    table += '<td class="line">' + data['table_3'][i].operator + '</td>';
                                    table += '<td data-sort="' + data['table_3'][i].amount + '" class="line">' + data['table_3'][i].amount + '</td>';
                                    table += '</tr>';
                                }
                            }
                            table += '</tbody>';
                            table += '<tfoot>';
                            table += '<tr class="header table-borderless">';
                                    table += '<td class="line"><?= TRANS('TOTAL'); ?></td>';
                                    table += '<td class="line">' + data['table_3'].sum + '</td>';
                                table += '</tr>';
                            table += '</tfoot>';
                            table += '</table>';

                            $('#table_3').html(table);
                        } else {
                            $('#table_3_msg').html(data['table_3'].msg_no_results);
                        }


                        /* Tabela _4 */
                        if (data['table_4'].records > 0) {
                            $('#table_4_msg').html(data['table_4'].msg_context);
                        
                            let table = '<table id="table_list_4" class="table table-striped table-bordered" cellspacing="0" width="100%">';
                            table += '<caption class="title"><span class="title">' + data['table_4'].title + '</span><br />' + data.criteria + '</caption>';

                            table += '<thead>';
                                table += '<tr class="header table-borderless">';
                                    table += '';
                                    table += '<td class="line"><?= mb_strtoupper(TRANS('WORKER')); ?></td>';
                                    table += '<td class="line"><?= mb_strtoupper(TRANS('COL_AMOUNT')); ?></td>';
                                table += '</tr>';

                            table += '</thead>';
                            table += '</tbody>';
                            let totalRecords = 0;
                            for (var i in data['table_4']) {

                                if (data['table_4'][i].operator !== undefined) {
                                    table += '<tr>';
                                    table += '<td class="line">' + data['table_4'][i].operator + '</td>';
                                    table += '<td data-sort="' + data['table_4'][i].amount + '" class="line">' + data['table_4'][i].amount + '</td>';
                                    table += '</tr>';
                                }
                            }
                            table += '</tbody>';
                            table += '<tfoot>';
                            table += '<tr class="header table-borderless">';
                                    table += '<td class="line"><?= TRANS('TOTAL'); ?></td>';
                                    table += '<td class="line">' + data['table_4'].sum + '</td>';
                                table += '</tr>';
                            table += '</tfoot>';
                            table += '</table>';

                            $('#table_4').html(table);
                        } else {
                            $('#table_4_msg').html(data['table_4'].msg_no_results);
                        }


                        /* Tabela _5 */
                        if (data['table_5'].records > 0) {
                            $('#table_5_msg').html(data['table_5'].msg_context);
                        
                            let table = '<table id="table_list_5" class="table table-striped table-bordered" cellspacing="0" width="100%">';
                            table += '<caption class="title"><span class="title">' + data['table_5'].title + '</span><br />' + data.criteria + '</caption>';

                            table += '<thead>';
                                table += '<tr class="header table-borderless">';
                                    table += '';
                                    table += '<td class="line"><?= mb_strtoupper(TRANS('WORKER')); ?></td>';
                                    table += '<td class="line"><?= mb_strtoupper(TRANS('COL_AMOUNT')); ?></td>';
                                table += '</tr>';

                            table += '</thead>';
                            table += '</tbody>';
                            let totalRecords = 0;
                            for (var i in data['table_5']) {

                                if (data['table_5'][i].operator !== undefined) {
                                    table += '<tr>';
                                    table += '<td class="line">' + data['table_5'][i].operator + '</td>';
                                    table += '<td data-sort="' + data['table_5'][i].amount + '" class="line">' + data['table_5'][i].amount + '</td>';
                                    table += '</tr>';
                                }
                            }
                            table += '</tbody>';
                            table += '<tfoot>';
                            table += '<tr class="header table-borderless">';
                                    table += '<td class="line"><?= TRANS('TOTAL'); ?></td>';
                                    table += '<td class="line">' + data['table_5'].sum + '</td>';
                                table += '</tr>';
                            table += '</tfoot>';
                            table += '</table>';

                            $('#table_5').html(table);
                        } else {
                            $('#table_5_msg').html(data['table_5'].msg_no_results);
                        }


                        /* Tabela _consolidada */
                        if (data['table_consolidated'].records > 0) {
                            $('#table_consolidated_msg').html(data['table_consolidated'].msg_context);
                        
                            let table = '<table id="table_list_consolidated" class="table table-striped table-bordered" cellspacing="0" width="100%">';
                            table += '<caption class="title"><span class="title">' + data['table_consolidated'].title + '</span><br />' + data.criteria + '</caption>';

                            table += '<thead>';
                                table += '<tr class="header table-borderless">';
                                    table += '';
                                    table += '<td class="line"><?= mb_strtoupper(TRANS('WORKER')); ?></td>';
                                    table += '<td class="line"><?= mb_strtoupper(TRANS('COL_AMOUNT')); ?></td>';
                                table += '</tr>';

                            table += '</thead>';
                            table += '</tbody>';
                            let totalRecords = 0;
                            for (var i in data['table_consolidated']) {

                                if (data['table_consolidated'][i].operator !== undefined) {
                                    table += '<tr>';
                                    table += '<td class="line">' + data['table_consolidated'][i].operator + '</td>';
                                    table += '<td data-sort="' + data['table_consolidated'][i].amount + '" class="line">' + data['table_consolidated'][i].amount + '</td>';
                                    table += '</tr>';
                                }
                            }
                            table += '</tbody>';
                            table += '<tfoot>';
                            table += '<tr class="header table-borderless">';
                                    table += '<td class="line"><?= TRANS('TOTAL'); ?></td>';
                                    table += '<td class="line">' + data['table_consolidated'].sum + '</td>';
                                table += '</tr>';
                            table += '</tfoot>';
                            table += '</table>';

                            $('#table_consolidated').html(table);
                        } else {
                            $('#table_consolidated_msg').html(data['table_consolidated'].msg_no_results);
                        }


                        
                        let instances = Object.keys(window.Chart.instances).length;
                        let chartIdx = 0;
                        
                        if (data.generate_chart == 1 && data['table_1'].records > 0) {
                            
                            chartIdx++;
                            /* Primeiro gráfico */
                            let canvas01 = '<canvas id="graph_01" class="mb-5"></canvas>'
                            $('#container01').empty().append(canvas01);

                            const chart_01 = general_chart(data, 'chart_01', 'operator', 'amount', 'graph_01');
                            addPercentageLabels(chart_01, (chartIdx + instances));

                        }

                        if (data.generate_chart == 1 && data['table_2'].records > 0) {
                        
                            chartIdx++;
                            /* Segundo gráfico */
                            let canvas02 = '<canvas id="graph_02" class="mb-5"></canvas>'
                            $('#container02').empty().append(canvas02);

                            const chart_02 = general_chart(data, 'chart_02', 'operator', 'amount', 'graph_02');
                            
                            addPercentageLabels(chart_02, (chartIdx + instances));
                        }

                        if (data.generate_chart == 1 && data['table_3'].records > 0) {
                        
                            chartIdx++;
                            /* Terceiro gráfico */
                            let canvas03 = '<canvas id="graph_03" class="mb-5"></canvas>'
                            $('#container03').empty().append(canvas03);

                            const chart_03 = general_chart(data, 'chart_03', 'operator', 'amount', 'graph_03');
                            
                            addPercentageLabels(chart_03, (chartIdx + instances));
                        }
                        if (data.generate_chart == 1 && data['table_4'].records > 0) {
                        
                            chartIdx++;
                            /* Terceiro gráfico */
                            let canvas04 = '<canvas id="graph_04" class="mb-5"></canvas>'
                            $('#container04').empty().append(canvas04);

                            const chart_04 = general_chart(data, 'chart_04', 'operator', 'amount', 'graph_04');
                            
                            addPercentageLabels(chart_04, (chartIdx + instances));
                        }
                        if (data.generate_chart == 1 && data['table_5'].records > 0) {
                        
                            chartIdx++;
                            /* Terceiro gráfico */
                            let canvas05 = '<canvas id="graph_05" class="mb-5"></canvas>'
                            $('#container05').empty().append(canvas05);

                            const chart_05 = general_chart(data, 'chart_05', 'operator', 'amount', 'graph_05');
                            
                            addPercentageLabels(chart_05, (chartIdx + instances));
                        }

                        if (data.generate_chart == 1 && data['table_consolidated'].records > 0) {
                        
                            chartIdx++;
                            /* Terceiro gráfico */
                            let canvas06 = '<canvas id="graph_06" class="mb-5"></canvas>'
                            $('#container06').empty().append(canvas06);

                            const chart_06 = general_chart(data, 'chart_06', 'operator', 'amount', 'graph_06');
                            
                            addPercentageLabels(chart_06, (chartIdx + instances));
                        }

						$('#divResult').html('');
						$('input, select, textarea').removeClass('is-invalid');
						$("#idSubmit").prop("disabled", false);
						return false;
					}
				});
				return false;
			});


        /* Utilizo essa função para conseguir utilizar o datalabels dinamicamente em função das várias instancias do chart */
        function addPercentageLabels(chart, metaIndex) {
            chart.options.plugins.datalabels = {
                display: function(context) {
                    return context.dataset.data[context.dataIndex] >= 1; // or !== 0 or ...
                },
                formatter: (value, ctx) => {
                    let sum = ctx.dataset._meta[metaIndex-1].total;
                    let percentage = (value * 100 / sum).toFixed(2) + "%";
                    return percentage;
                },
            };
            chart.update();
        }


        /* Adicionei o mutation observer em função dos elementos que são adicionados após o carregamento do DOM */
        var obs = $.initialize("#list_tables", function() {
            
            $(function() {
                $('[data-toggle="popover"]').popover()
            });

            $('.popover-dismiss').popover({
                trigger: 'focus'
            });
            
            var criterios = $('#divCriterios').text();

            var tables = $('.table').DataTable({
                
                dom: 'lfrtBip',
                language: {
                    url: "../../includes/components/datatables/datatables.pt-br.json",
                },
                order: [1, 'desc'],
                searching: true,
                paging: true,
                deferRender: true,
                "pageLength": 10,
                buttons: [
                    {
                        extend: 'copyHtml5',
                        text: '<?= TRANS('SMART_BUTTON_COPY', '', 1) ?>',
                        exportOptions: {
                            columns: ':visible'
                        },
                        footer: true,
                    },
                    {
                        extend: 'csvHtml5',
                        text: "CSV",
                        exportOptions: {
                            columns: ':visible'
                        },
                        footer: true,
                        filename: '<?= TRANS('REPORT_TREATINGS_AND_PARTICIPATIONS', '', 1); ?>-<?= date('d-m-Y-H:i:s'); ?>',
                    },
                    {
                        extend: 'print',
                        text: '<?= TRANS('SMART_BUTTON_PRINT', '', 1) ?>',
                        title: '<?= TRANS('REPORT_TREATINGS_AND_PARTICIPATIONS', '', 1) ?>',
                        autoPrint: true,
                        footer: true,
                        exportOptions: {
                            columns: ':visible'
                        },
                        customize: function(win) {
                            $(win.document.body).find('table').addClass('display').css('font-size', '10px');
                            $(win.document.body).find('tr:nth-child(odd) td').each(function(index) {
                                $(this).css('background-color', '#f9f9f9');
                            });
                            $(win.document.body).find('h1').css('text-align', 'center');
                        },
                    },
                    {
                        extend: 'excel',
                        text: "Excel",
                        exportOptions: {
                            columns: ':visible'
                        },
                        footer: true,
                        filename: '<?= TRANS('REPORT_TREATINGS_AND_PARTICIPATIONS', '', 1); ?>-<?= date('d-m-Y-H:i:s'); ?>',
                    },
                    {
                        extend: 'pdfHtml5',
                        text: "PDF",
                        footer: true,
                        exportOptions: {
                            columns: ':visible',
                        },
                        title: '<?= TRANS('REPORT_TREATINGS_AND_PARTICIPATIONS', '', 1); ?>',
                        filename: '<?= TRANS('REPORT_TREATINGS_AND_PARTICIPATIONS', '', 1); ?>-<?= date('d-m-Y-H:i:s'); ?>',
                        // orientation: 'landscape',
                        orientation: 'portrait',
                        pageSize: 'A4',

                        customize: function(doc) {
                            // var criterios = $('#table_caption').text()
                            var rdoc = doc;
                            
                            var rcout = doc.content[doc.content.length - 1].table.body.length - 1;
                            // doc.content.splice(0, 1);
                            
                            // console.log(doc.content)
                            // var rcout = doc.content[1].table.body.length - 1;
                            doc.content.splice(0, 1);
                            var now = new Date();
                            var jsDate = now.getDate() + '/' + (now.getMonth() + 1) + '/' + now.getFullYear() + ' ' + now.getHours() + ':' + now.getMinutes() + ':' + now.getSeconds();
                            doc.pageMargins = [30, 70, 30, 30];
                            doc.defaultStyle.fontSize = 8;
                            doc.styles.tableHeader.fontSize = 9;

                            doc['header'] = (function(page, pages) {
                                return {
                                    columns: [
                                        {
                                            margin: [20, 10, 0, 0],
                                            image: getLogoSrc(),
                                            width: getLogoWidth()
                                        } ,
                                        {
                                            table: {
                                                widths: ['100%'],
                                                headerRows: 0,
                                                body: [
                                                    [{
                                                        text: '<?= TRANS('REPORT_TREATINGS_AND_PARTICIPATIONS', '', 1); ?>',
                                                        alignment: 'center',
                                                        
                                                        fontSize: 14,
                                                        bold: true,
                                                        margin: [0, 20, 0, 0]
                                                        
                                                    }],
                                                ]
                                            },
                                            layout: 'noBorders',
                                            margin: 10,
                                        }
                                    ],
                                }
                            });

                            doc['footer'] = (function(page, pages) {
                                return {
                                    columns: [{
                                            alignment: 'left',
                                            text: ['Criado em: ', {
                                                text: jsDate.toString()
                                            }]
                                        },
                                        {
                                            alignment: 'center',
                                            text: 'Total ' + rcout.toString() + ' linhas'
                                        },
                                        {
                                            alignment: 'right',
                                            text: ['página ', {
                                                text: page.toString()
                                            }, ' de ', {
                                                text: pages.toString()
                                            }]
                                        }
                                    ],
                                    margin: 10
                                }
                            });

                            var objLayout = {};
                            objLayout['hLineWidth'] = function(i) {
                                return .8;
                            };
                            objLayout['vLineWidth'] = function(i) {
                                return .5;
                            };
                            objLayout['hLineColor'] = function(i) {
                                return '#aaa';
                            };
                            objLayout['vLineColor'] = function(i) {
                                return '#aaa';
                            };
                            objLayout['paddingLeft'] = function(i) {
                                return 5;
                            };
                            objLayout['paddingRight'] = function(i) {
                                return 35;
                            };
                            // doc.content[doc.content.length - 1].layout = objLayout;
                            doc.content[1].layout = objLayout;
                        }
                    },

                ]
            });

        }, {
            target: document.getElementById('enable_list_tables'),
        }); /* o target limita o scopo do mutate observer */


    });


    function getLogoSrc() {
        return $('#logo-base64').val() ?? '';
    }

    function setLogoSrc() {

        let logoName = $('#report-mainlogo').css('background-image');

        if (logoName == 'none') {
            return;
        }
        logoName = logoName.replace(/.*\s?url\([\'\"]?/, '').replace(/[\'\"]?\).*/, '')
        logoName = logoName.split('/').pop();

        $.ajax({
            url: './get_reports_logo.php',
            method: 'POST',
            data: {
                'logo_name': logoName
            },
            dataType: 'json',
        }).done(function(data) {

            if (!data.success) {
                return;
            }
            $('#logo-base64').val(data.logo);
        });
    }

    function getLogoWidth() {
        let logoWidth = $('#report-mainlogo').width() ?? 150;
        return logoWidth;
    }

    </script>
</body>

</html>