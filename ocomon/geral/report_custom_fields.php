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
        <h5 class="my-4"><i class="fas fa-pencil-ruler text-secondary"></i>&nbsp;<?= TRANS('CUSTOM_FIELD_REPORT'); ?></h5>
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

                    <label for="unit" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_UNIT'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control bs-select" id="unit" name="unit[]" multiple="multiple">
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

                    <label for="custom_field" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_TO_GROUP'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control bs-select" id="custom_field" name="custom_field">
                            <option value=""><?= TRANS('SEL_SELECT'); ?></option>
                            <?php
                            $fieldsToGroup = getCustomFields($conn, null, 'ocorrencias');
                            foreach ($fieldsToGroup as $fieldToGroup) {
                                ?>
                                    <option value="<?= $fieldToGroup['id']; ?>"><?= $fieldToGroup['field_label']; ?></option>
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
                            <option value="2"<?= ($sess_state == 2 ? ' selected': ''); ?>><?= TRANS('STATE_OPEN_IN_SEARCH_RANGE'); ?></option>
                            <option value="3"<?= ($sess_state == 3 ? ' selected': ''); ?>><?= TRANS('STATE_OPEN_IN_SEARCH_RANGE_CLOSE_ANY_TIME'); ?></option>
                            <option value="4"<?= ($sess_state == 4 ? ' selected': ''); ?>><?= TRANS('STATE_OPEN_ANY_TIME_CLOSE_IN_SEARCH_RANGE'); ?></option>
                            <option value="5"<?= ($sess_state == 5 ? ' selected': ''); ?>><?= TRANS('STATE_JUST_OPEN_IN_SEARCH_RANGE'); ?></option>
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
        <div id="table" class="table-responsive"></div>
        <div class="chart-container" id="container01">
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

            loadUnits();
            $("#client").on('change', function() {
				loadUnits();
			});

            function loadUnits(targetId = 'unit') {

                var loading = $(".loading");
                $(document).ajaxStart(function() {
                    loading.show();
                });
                $(document).ajaxStop(function() {
                    loading.hide();
                });

                $.ajax({
                    url: './get_units_by_client.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        client: $("#client").val()
                    },
                }).done(function(data) {
                    $('#' + targetId).empty();
                    // if (Object.keys(data).length > 1) {
                    //     $('#' + targetId).append('<option value=""><?= TRANS("SEL_SELECT"); ?></option>');
                    // }
                    $.each(data, function(key, data) {
                        $('#' + targetId).append('<option value="' + data.inst_cod + '">' + data.inst_nome + '</option>');
                    });

                    $('#' + targetId).selectpicker('refresh');
                    if ($('#parent_id').val() != '') {
                        $('#' + targetId).selectpicker('val', $('#parent_unit').val());
                        $('#' + targetId).selectpicker('refresh');
                    }
                });
            }




            $('#idSubmit').on('click', function() {
                var loading = $(".loading");
                $(document).ajaxStart(function() {
                    loading.show();
                });
                $(document).ajaxStop(function() {
                    loading.hide();
                });

                $.ajax({
					url: './report_custom_fields_process.php',
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
                        $('#table').html('');
                        $('#container01').empty();
						$("#idSubmit").prop("disabled", false);
					} else {
                        /* Aqui ocorrerá as chamadadas para a montagem da tabela e também para os gráficos */

                        let table = '<table id="table_custom_fields" class="table table-striped table-bordered" cellspacing="0" width="100%">';
                        table += '<caption class="title"><span class="title">' + data['table'].title + '</span><br />' + data.criteria + '</caption>';

                        table += '<thead>';
                            table += '<tr class="header table-borderless">';
                                table += '';
                                table += '<td class="line"><?= mb_strtoupper(TRANS('LABEL')); ?></td>';
                                table += '<td class="line"><?= mb_strtoupper(TRANS('COL_VALUE')); ?></td>';
                                table += '<td class="line"><?= mb_strtoupper(TRANS('COL_AMOUNT')); ?></td>';
                                table += '<td class="line"><?= mb_strtoupper(TRANS('PERCENTAGE')); ?></td>';
                            table += '</tr>';

                        table += '</thead>';
                        table += '</tbody>';
                        let totalRecords = 0;
                        for (var i in data['table']) {

                            if (data['table'][i].field_label !== undefined) {
                                table += '<tr>';
                                table += '<td class="line">' + data['table'][i].field_label + '</td>';
                                table += '<td class="line">' + data['table'][i].field_value + '</td>';
                                    table += '<td class="line">' + data['table'][i].total + '</td>';
                                    
                                    totalRecords += parseInt(data['table'][i].total);

                                    let percent = parseFloat(data['table'][i].total * 100) / parseFloat(data['totalRecords'])
                                    

                                    table += '<td class="line">' + percent.toFixed(2) + '%</td>';
                                table += '</tr>';
                            }
                        }
                        table += '</tbody>';
                        table += '<tfoot>';
                            table += '<tr class="header table-borderless">';
                                table += '<td colspan="2"><?= TRANS('TOTAL'); ?></td>';
                                table += '<td>' + totalRecords + '</td>';
                                table += '<td></td>';
                            table += '</tr>';
                        table += '</tfoot>';
                        table += '</table>';

                        $('#table').html(table);


                        if (data.generate_chart == 1) {
                        
                            /* Aqui ocorrerá a montagem dos gráficos */
                            let canvas01 = '<canvas id="graph_01" class="mb-5"></canvas>'
                            // let canvas02 = '<canvas id="graph_02" class="mb-5"></canvas>'
                            $('#container01').empty().append(canvas01);
                            // $('#container01').append(canvas02);
                            
                            let instances = Object.keys(window.Chart.instances).length;

                            const chart_01 = general_chart(data, 'chart_01', 'field_value', 'total', 'graph_01');
                            // const chart_02 = report_assets_general(data, 'chart_02', 'categorie', 'graph_02');
                            
                            addPercentageLabels(chart_01, (1 + instances));
                            // addPercentageLabels(chart_02, (2 + instances));
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


            if (<?= $json ?> != 0) {
                showChart('canvasChart1');
            }


            /* Adicionei o mutation observer em função dos elementos que são adicionados após o carregamento do DOM */
            var obs = $.initialize("#table_custom_fields", function() {
                
                $(function() {
                    $('[data-toggle="popover"]').popover()
                });

                $('.popover-dismiss').popover({
                    trigger: 'focus'
                });
                
                var criterios = $('#divCriterios').text();

                var table = $('#table_custom_fields').DataTable({
                    language: {
                        url: "../../includes/components/datatables/datatables.pt-br.json",
                    },
                    paging: true,
                    deferRender: true,
                    "pageLength": 50,
                });

            }, {
                target: document.getElementById('table')
            }); /* o target limita o scopo do mutate observer */



        });


    </script>
</body>

</html>