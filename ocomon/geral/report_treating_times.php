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
        <h5 class="my-4"><i class="fas fa-user-clock text-secondary"></i>&nbsp;<?= TRANS('TREATING_TIMES'); ?></h5>
        <div class="modal" id="modal" tabindex="-1" style="z-index:9001!important">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div id="divDetails">
                    </div>
                </div>
            </div>
        </div>

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
        <div id="msg_1" class="d-none">
            <?= message('info','', TRANS('MSG_REPORT_TREATING_TIMES'), '', '', true); ?>
        </div>
        <div id="msg_1_none" class="d-none">
            <?= message('warning','', TRANS('MSG_REPORT_TREATING_TIMES_NONE'), '', '', true); ?>
        </div>
        <div id="table" class="table-responsive"></div>
        <div id="table_2" class="table-responsive"></div>
        <div id="table_3" class="table-responsive"></div>
        <div class="chart-container" id="container01"></div>
        <div id="msg_2" class="d-none mt-4">
            <?= message('info','', TRANS('MSG_REPORT_TREATING_TIMES_DIRECT_QUEUE'), '', '', true); ?>
        </div>
        <div id="msg_2_none" class="d-none">
            <?= message('warning','', TRANS('MSG_REPORT_TREATING_TIMES_DQUEUE_NONE'), '', '', true); ?>
        </div>
        <div id="table_4" class="table-responsive"></div>
        <div class="chart-container" id="container02"></div>
        
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

                $.ajax({
					url: './report_treating_times_process.php',
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
                        $('#msg_1').addClass('d-none');
                        $('#msg_1_none').addClass('d-none');
                        $('#msg_2').addClass('d-none');
                        $('#msg_2_none').addClass('d-none');
                        $('#table').html('');
                        $('#table_2').html('');
                        $('#table_3').html('');
                        $('#table_4').html('');
                        $('#container01').empty();
                        $('#container02').empty();
						$("#idSubmit").prop("disabled", false);
					} else {
                        /* Aqui ocorrerá as chamadadas para a montagem da tabela e também para os gráficos */
                        $('#table').html('');
                        $('#table_2').html('');
                        $('#table_3').html('');
                        $('#table_4').html('');
                        $('#container01').empty();
                        $('#container02').empty();
                        $('#msg_1').addClass('d-none');
                        $('#msg_1_none').addClass('d-none');
                        $('#msg_2').addClass('d-none');
                        $('#msg_2_none').addClass('d-none');
                        if (data['table_1'].totalProvided > 0) {
                            $('#msg_1').removeClass('d-none');
                        
                            let table = '<table id="table_treater_times" class="table table-striped table-bordered" cellspacing="0" width="100%">';
                            table += '<caption class="title"><span class="title">' + data['table_1'].title + '</span><br />' + data.criteria + '</caption>';

                            table += '<thead>';
                                table += '<tr class="header table-borderless">';
                                    table += '';
                                    table += '<td class="line"><?= mb_strtoupper(TRANS('WORKER')); ?></td>';
                                    table += '<td class="line"><?= mb_strtoupper(TRANS('TREATING_TIMES')); ?></td>';
                                table += '</tr>';

                            table += '</thead>';
                            table += '</tbody>';
                            let totalRecords = 0;
                            for (var i in data['table_1']) {

                                if (data['table_1'][i].nome !== undefined) {
                                    table += '<tr>';
                                    table += '<td class="line">' + data['table_1'][i].nome + '</td>';
                                    table += '<td data-sort="' + data['table_1'][i].seconds + '" class="line">' + data['table_1'][i].concated_time + '</td>';
                                    table += '</tr>';
                                }
                            }
                            table += '</tbody>';
                            table += '<tfoot>';
                            table += '<tr class="header table-borderless">';
                                    table += '<td class="line"><?= TRANS('TOTAL'); ?></td>';
                                    table += '<td class="line">' + data['table_1'].total + '</td>';
                                table += '</tr>';
                            table += '</tfoot>';
                            table += '</table>';

                            $('#table').html(table);

                            /* Segunda tabela */
                            let table_2 = '<table id="table_2_treater_times" class="table table-striped table-bordered" cellspacing="0" width="100%">';
                            table_2 += '<caption class="title"><span class="title">' + data['table_2'].title + '</span><br />' + data.criteria + '</caption>';

                            table_2 += '<thead>';
                                table_2 += '<tr class="header table-borderless">';
                                table_2 += '';
                                    table_2 += '<td class="line"><?= mb_strtoupper(TRANS('WORKER')); ?></td>';
                                    table_2 += '<td class="line"><?= mb_strtoupper(TRANS('CLIENT')); ?></td>';
                                    table_2 += '<td class="line"><?= mb_strtoupper(TRANS('TREATING_TIMES')); ?></td>';
                                table_2 += '</tr>';

                            table_2 += '</thead>';
                            table_2 += '</tbody>';
                            for (var i in data['table_2']) {

                                if (data['table_2'][i].nome !== undefined) {
                                    table_2 += '<tr>';
                                    table_2 += '<td class="line">' + data['table_2'][i].nome + '</td>';
                                    table_2 += '<td class="line">' + data['table_2'][i].client_name + '</td>';
                                    table_2 += '<td data-sort="' + data['table_2'][i].seconds + '" class="line">' + data['table_2'][i].concated_time + '</td>';
                                    table_2 += '</tr>';
                                }
                            }
                            table_2 += '</tbody>';
                            table_2 += '<tfoot>';
                                table_2 += '<tr class="header table-borderless">';
                                    table_2 += '<td class="line" colspan="2"><?= TRANS('TOTAL'); ?></td>';
                                    table_2 += '<td class="line">' + data['table_2'].total + '</td>';
                                table_2 += '</tr>';
                            table_2 += '</tfoot>';
                            table_2 += '</table>';

                            $('#table_2').html(table_2);

                            /* Terceira tabela */
                            let table_3 = '<table id="table_3_treater_times" class="table table-striped table-bordered" cellspacing="0" width="100%">';
                            table_3 += '<caption class="title"><span class="title">' + data['table_3'].title + '</span><br />' + data.criteria + '</caption>';

                            table_3 += '<thead>';
                                table_3 += '<tr class="header table-borderless">';
                                table_3 += '';
                                    table_3 += '<td class="line"><?= mb_strtoupper(TRANS('WORKER')); ?></td>';
                                    table_3 += '<td class="line"><?= mb_strtoupper(TRANS('SERVICE_AREA')); ?></td>';
                                    table_3 += '<td class="line"><?= mb_strtoupper(TRANS('TREATING_TIMES')); ?></td>';
                                table_3 += '</tr>';

                            table_3 += '</thead>';
                            table_3 += '</tbody>';
                            // let totalRecords = 0;
                            for (var i in data['table_3']) {

                                if (data['table_3'][i].nome !== undefined) {
                                    table_3 += '<tr>';
                                    table_3 += '<td class="line">' + data['table_3'][i].nome + '</td>';
                                    table_3 += '<td class="line">' + data['table_3'][i].sistema + '</td>';
                                    table_3 += '<td data-sort="' + data['table_3'][i].seconds + '" class="line">' + data['table_3'][i].concated_time + '</td>';
                                    table_3 += '</tr>';
                                }
                            }
                            table_3 += '</tbody>';
                            table_3 += '<tfoot>';
                                table_3 += '<tr class="header table-borderless">';
                                    table_3 += '<td class="line" colspan="2"><?= TRANS('TOTAL'); ?></td>';
                                    table_3 += '<td class="line">' + data['table_3'].total + '</td>';
                                table_3 += '</tr>';
                            table_3 += '</tfoot>';
                            table_3 += '</table>';

                            $('#table_3').html(table_3);

                        } else {
                            $('#msg_1_none').removeClass('d-none');
                        }

                        if (data['table_4'].totalInDirectQueue > 0) {
                            $('#msg_2').removeClass('d-none');
                        
                            /* Quarta tabela */
                            let table_4 = '<table id="table_4_treater_times" class="table table-striped table-bordered" cellspacing="0" width="100%">';
                            table_4 += '<caption class="title"><span class="title">' + data['table_4'].title + '</span><br />' + data.criteria + '</caption>';

                            table_4 += '<thead>';
                                table_4 += '<tr class="header table-borderless">';
                                table_4 += '';
                                    table_4 += '<td class="line"><?= mb_strtoupper(TRANS('WORKER')); ?></td>';
                                    table_4 += '<td class="line"><?= mb_strtoupper(TRANS('ABSOLUTE_TIME')); ?></td>';
                                    table_4 += '<td class="line"><?= mb_strtoupper(TRANS('FILTERED_TIME')); ?></td>';
                                table_4 += '</tr>';

                            table_4 += '</thead>';
                            table_4 += '</tbody>';
                            // let totalRecords = 0;
                            for (var i in data['table_4']) {

                                if (data['table_4'][i].name !== undefined) {
                                    table_4 += '<tr>';
                                    table_4 += '<td class="line">' + data['table_4'][i].name + '</td>';
                                    table_4 += '<td data-sort="' + data['table_4'][i].absoluteSeconds + '" class="line">' + data['table_4'][i].absolutetime + '</td>';
                                    table_4 += '<td data-sort="' + data['table_4'][i].filteredSeconds + '" class="line">' + data['table_4'][i].filteredtime + '</td>';
                                    table_4 += '</tr>';
                                }
                            }
                            table_4 += '</tbody>';
                            table_4 += '<tfoot>';
                                table_4 += '<tr class="header table-borderless">';
                                    table_4 += '<td class="line"><?= TRANS('TOTAL'); ?></td>';
                                    table_4 += '<td class="line">' + data['table_4'].totalAbsoluteTime + '</td>';
                                    table_4 += '<td class="line">' + data['table_4'].totalFilteredTime + '</td>';
                                table_4 += '</tr>';
                            table_4 += '</tfoot>';
                            table_4 += '</table>';

                            $('#table_4').html(table_4);
                        
                        } else {
                            $('#msg_2_none').removeClass('d-none');
                        }
                        
                        let instances = Object.keys(window.Chart.instances).length;
                        let chartIdx = 0;
                        
                        if (data.generate_chart == 1 && data['table_1'].totalProvided > 0) {
                            
                            chartIdx++;
                            /* Primeiro gráfico */
                            let canvas01 = '<canvas id="graph_01" class="mb-5"></canvas>'
                            $('#container01').empty().append(canvas01);

                            const chart_01 = general_chart(data, 'chart_01', 'nome', 'seconds', 'graph_01', 'secondsToHms');
                            addPercentageLabels(chart_01, (chartIdx + instances));

                        }

                        if (data.generate_chart == 1 && data['table_4'].totalInDirectQueue > 0) {
                        
                            chartIdx++;
                            /* Segundo gráfico */
                            let canvas02 = '<canvas id="graph_02" class="mb-5"></canvas>'
                            $('#container02').empty().append(canvas02);

                            const chart_02 = general_chart(data, 'chart_02', 'name', 'filteredSeconds', 'graph_02', 'secondsToHms');
                            
                            addPercentageLabels(chart_02, (chartIdx + instances));
                            /* Final na montagem dos gráficos */
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
            var obs = $.initialize("#table_treater_times", function() {
                
                $(function() {
                    $('[data-toggle="popover"]').popover()
                });

                $('.popover-dismiss').popover({
                    trigger: 'focus'
                });
                
                var criterios = $('#divCriterios').text();

                var table = $('#table_treater_times').DataTable({
                    language: {
                        url: "../../includes/components/datatables/datatables.pt-br.json",
                    },
                    order: [[1, 'desc']],
                    searching: true,
                    paging: true,
                    deferRender: true,
                    "pageLength": 50,
                });

            }, {
                target: document.getElementById('table')
            }); /* o target limita o scopo do mutate observer */


            var obs_2 = $.initialize("#table_2_treater_times", function() {
                
                $(function() {
                    $('[data-toggle="popover"]').popover()
                });

                $('.popover-dismiss').popover({
                    trigger: 'focus'
                });
                
                var criterios = $('#divCriterios').text();

                var table_2 = $('#table_2_treater_times').DataTable({
                    language: {
                        url: "../../includes/components/datatables/datatables.pt-br.json",
                    },
                    order: [[2, 'desc']],
                    searching: true,
                    paging: true,
                    deferRender: true,
                    "pageLength": 50,
                });

            }, {
                target: document.getElementById('table_2')
            }); /* o target limita o scopo do mutate observer */


            var obs_3 = $.initialize("#table_3_treater_times", function() {
                
                $(function() {
                    $('[data-toggle="popover"]').popover()
                });

                $('.popover-dismiss').popover({
                    trigger: 'focus'
                });
                
                var criterios = $('#divCriterios').text();

                var table_3 = $('#table_3_treater_times').DataTable({
                    language: {
                        url: "../../includes/components/datatables/datatables.pt-br.json",
                    },
                    order: [[2, 'desc']],
                    searching: true,
                    paging: true,
                    deferRender: true,
                    "pageLength": 50,
                });

            }, {
                target: document.getElementById('table_3')
            }); /* o target limita o scopo do mutate observer */


            var obs_4 = $.initialize("#table_4_treater_times", function() {
                
                $(function() {
                    $('[data-toggle="popover"]').popover()
                });

                $('.popover-dismiss').popover({
                    trigger: 'focus'
                });
                
                var criterios = $('#divCriterios').text();

                var table_4 = $('#table_4_treater_times').DataTable({
                    language: {
                        url: "../../includes/components/datatables/datatables.pt-br.json",
                    },
                    order: [[2, 'desc']],
                    searching: true,
                    paging: true,
                    deferRender: true,
                    "pageLength": 50,
                });

            }, {
                target: document.getElementById('table_4')
            }); /* o target limita o scopo do mutate observer */


        });


    </script>
</body>

</html>