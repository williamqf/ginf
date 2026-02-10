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

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 1, 2);

$_SESSION['s_page_invmon'] = $_SERVER['PHP_SELF'];


$categories = getAssetsCategories($conn);


$json = 0;
$json2 = 0;
$json3 = 0;

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
            line-height: 1.5em;
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
        <h5 class="my-4"><i class="fas fa-hand-holding-usd text-secondary"></i>&nbsp;<?= TRANS('ASSETS_COST_BY_USER'); ?></h5>
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

        $criterio = "";



        if (!isset($_POST['action'])) {

        ?>
            <form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">
                <div class="form-group row my-4">
                    
                
                    <label for="client" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('CLIENT'); ?></label>
                    <div class="form-group col-md-10">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text" title="<?= TRANS('CONSIDER_ASSET_CLIENT_INSTEAD'); ?>" data-placeholder="<?= TRANS('CONSIDER_ASSET_CLIENT_INSTEAD'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                    <i class="fas fa-qrcode"></i>&nbsp;
                                    <input type="checkbox" class="first-check" name="use_asset_client" id="use_asset_client" value="1" disabled>
                                </div>
                            </div>
                            <select class="form-control bs-select" id="client" name="client">
                                <option value="" selected><?= TRANS('ALL'); ?></option>
                                <?php
                                    $clients = getClients($conn, null, null, $_SESSION['s_allowed_clients']);
                                    foreach ($clients as $client) {
                                        ?>
                                        <option value="<?= $client['id']; ?>"><?= $client['nickname']; ?></option>
                                        <?php
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                
                    <label for="asset_unit" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_UNIT'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control bs-select" id="asset_unit" name="asset_unit[]" multiple="multiple">
                            <option value="" selected><?= TRANS('ALL'); ?></option>
                        </select>
                    </div>


                    <label for="asset_department" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('DEPARTMENT'); ?></label>
                    <div class="form-group col-md-10">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text" title="<?= TRANS('CONSIDER_ASSET_DEPARTMENT_INSTEAD'); ?>" data-placeholder="<?= TRANS('CONSIDER_ASSET_DEPARTMENT_INSTEAD'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                    <i class="fas fa-qrcode"></i>&nbsp;
                                    <input type="checkbox" class="first-check" name="use_asset_department" id="use_asset_department" value="1" disabled>
                                </div>
                            </div>
                            <select class="form-control bs-select" id="asset_department" name="asset_department[]" multiple="multiple">
                                <option value="" selected><?= TRANS('ALL'); ?></option>
                            </select>
                        </div>
                    </div>

                    <label for="asset_user" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_USER'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control bs-select" id="asset_user" name="asset_user[]" multiple="multiple">
                            
                        </select>
                    </div>


                    <label for="asset_category" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('CATEGORY'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control bs-select" id="asset_category" name="asset_category[]" multiple="multiple">
                            <?php
                                foreach ($categories as $category) {
                                    ?>
                                    <option value="<?= $category['id']; ?>"><?= $category['cat_name']; ?></option>
                                    <?php
                                }
                            ?>
                        </select>
                    </div>

                    <label for="asset_type" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_TYPE'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control bs-select" id="asset_type" name="asset_type[]" multiple="multiple">
                            
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
        <div id="table" class="table-responsive mb-4"></div>
        <div id="table_2" class="table-responsive mt-4"></div>
        <div id="table_3" class="table-responsive mt-4"></div>
        <div class="chart-container mt-4" id="container01">
        </div>
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
                // actionsBox: true,
                // deselectAllText: "<?= TRANS('DESELECT_ALL', '', 1); ?>",
                // selectAllText: "<?= TRANS('SELECT_ALL', '', 1); ?>",
                liveSearch: true,
                liveSearchNormalize: true,
                liveSearchPlaceholder: "<?= TRANS('BT_SEARCH', '', 1); ?>",
                noneResultsText: "<?= TRANS('NO_RECORDS_FOUND', '', 1); ?> {0}",
                style: "",
                styleBase: "form-control input-select-multi",

            });

            loadUnits();
            loadDepartments($("#client").val());
            loadUsers($("#client").val());
            loadAssetTypes();

            $("#client").on('change', function() {
				loadUnits();
                loadDepartments($(this).val());
                loadUsers($(this).val());
                
                checkboxControl("use_asset_client", $(this).val());
			});

            $('#asset_department').on('change', function() {
                checkboxControl("use_asset_department", $(this).val());
            });

            $("#asset_category").on('change', function() {
				loadAssetTypes();
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
					url: './report_assets_cost_by_user_process.php',
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

                        let table = '';
                        table += '<table id="table_cost_by_users" class="table table-striped table-bordered" cellspacing="0" width="100%">';
                        table += '<caption class="title"><span class="title">' + data['table'].title + '</span><br />' + data.criteria + '</caption>';
                        // table += '<caption>' + data.criteria + '</caption>';
                        table += '<thead>';
                            table += '<tr class="header table-borderless">';
                                table += '';
                                table += '<td class="line"><?= mb_strtoupper(TRANS('FIELD_USER')); ?></td>';
                                table += '<td class="line"><?= mb_strtoupper(TRANS('CLIENT')); ?></td>';
                                table += '<td class="line"><?= mb_strtoupper(TRANS('DEPARTMENT')); ?></td>';
                                table += '<td class="line"><?= mb_strtoupper(TRANS('TOTAL')); ?></td>';
                            table += '</tr>';

                        table += '</thead>';
                        table += '</tbody>';
                        for (var i in data['table']) {

                            if (data['table'][i].nome !== undefined) {
                                table += '<tr>';
                                    table += '<td class="line">' + data['table'][i].nome + '</td>';
                                    table += '<td class="line">' + data['table'][i].cliente_usuario + '</td>';
                                    table += '<td class="line">' + data['table'][i].departamento_usuario + '</td>';
                                    table += '<td class="line" data-sort="' + data['table'][i].price_db + '">' + data['table'][i].amount + '</td>';
                                table += '</tr>';
                            }
                        }
                        table += '</tbody>';
                        table += '<tfoot>';
                            table += '<tr class="header table-borderless">';
                                table += '<td colspan="3"><?= TRANS('TOTAL'); ?></td>';
                                table += '<td>' + data.total + '</td>';
                            table += '</tr>';
                        table += '</tfoot>';
                        table += '</table>';





                        let table_2 = '<table id="table_2_cost_by_users" class="table table-striped table-bordered" cellspacing="0" width="100%">';
                        table_2 += '<caption class="title"><span class="title">' + data['table_2'].title + '</span><br />' + data.criteria + '</caption>';
                        // table_2 += '<caption>' + data.criteria + '</caption>';
                        table_2 += '<thead>';
                            table_2 += '<tr class="header table-borderless">';
                                table_2 += '';
                                table_2 += '<td class="line"><?= mb_strtoupper(TRANS('CLIENT')); ?></td>';
                                table_2 += '<td class="line"><?= mb_strtoupper(TRANS('DEPARTMENT')); ?></td>';
                                table_2 += '<td class="line"><?= mb_strtoupper(TRANS('TOTAL')); ?></td>';
                            table_2 += '</tr>';

                        table_2 += '</thead>';
                        table_2 += '</tbody>';
                        for (var i in data['table_2']) {

                            if (data['table_2'][i].departamento_usuario !== undefined) {
                                table_2 += '<tr>';
                                    table_2 += '<td class="line">' + data['table_2'][i].cliente_usuario + '</td>';
                                    table_2 += '<td class="line">' + data['table_2'][i].departamento_usuario + '</td>';
                                    table_2 += '<td class="line" data-sort="' + data['table_2'][i].price_db + '">' + data['table_2'][i].amount + '</td>';
                                table_2 += '</tr>';
                            }
                        }
                        table_2 += '</tbody>';
                        table_2 += '<tfoot>';
                            table_2 += '<tr class="header table-borderless">';
                                table_2 += '<td colspan="2"><?= TRANS('TOTAL'); ?></td>';
                                table_2 += '<td>' + data.total_2 + '</td>';
                            table_2 += '</tr>';
                        table_2 += '</tfoot>';
                        table_2 += '</table>';



                        let table_3 = '<table id="table_3_cost_by_users" class="table table-striped table-bordered" cellspacing="0" width="100%">';
                        table_3 += '<caption class="title"><span class="title">' + data['table_3'].title + '</span><br />' + data.criteria + '</caption>';
                        // table_3 += '<caption>' + data.criteria + '</caption>';
                        table_3 += '<thead>';
                            table_3 += '<tr class="header table-borderless">';
                                table_3 += '';
                                table_3 += '<td class="line"><?= mb_strtoupper(TRANS('CLIENT')); ?></td>';
                                table_3 += '<td class="line"><?= mb_strtoupper(TRANS('TOTAL')); ?></td>';
                            table_3 += '</tr>';

                        table_3 += '</thead>';
                        table_3 += '</tbody>';
                        for (var i in data['table_3']) {

                            if (data['table_3'][i].cliente_usuario !== undefined) {
                                table_3 += '<tr>';
                                    table_3 += '<td class="line">' + data['table_3'][i].cliente_usuario + '</td>';
                                    table_3 += '<td class="line" data-sort="' + data['table_3'][i].price_db + '">' + data['table_3'][i].amount + '</td>';
                                table_3 += '</tr>';
                            }
                        }
                        table_3 += '</tbody>';
                        table_3 += '<tfoot>';
                            table_3 += '<tr class="header table-borderless">';
                                table_3 += '<td><?= TRANS('TOTAL'); ?></td>';
                                table_3 += '<td>' + data.total_3 + '</td>';
                            table_3 += '</tr>';
                        table_3 += '</tfoot>';
                        table_3 += '</table>';



                        $('#table').html(table);
                        $('#table_2').html(table_2);
                        $('#table_3').html(table_3);



                        /* Aqui ocorrerá a montagem dos gráficos */
                        let canvas01 = '<canvas id="graph_01" class="mb-5"></canvas>'
                        let canvas02 = '<canvas id="graph_02" class="mb-5"></canvas>'
                        $('#container01').empty().append(canvas01);
                        $('#container01').append(canvas02);
                        
                        let instances = Object.keys(window.Chart.instances).length;

                        const chart_01 = report_assets_general(data['chart_01'], 'table_2', 'departamento_usuario', 'graph_01');
                        const chart_02 = report_assets_general(data['chart_02'], 'table_3', 'cliente_usuario', 'graph_02');
                        
                        addPercentageLabels(chart_01, (1 + instances));
                        addPercentageLabels(chart_02, (2 + instances));
                        /* Final na montagem dos gráficos */

						$('#divResult').html('');
						$('input, select, textarea').removeClass('is-invalid');
						$("#idSubmit").prop("disabled", false);
						return false;
					}
				});
				return false;
			});



            /* Adicionei o mutation observer em função dos elementos que são adicionados após o carregamento do DOM */
            var obs = $.initialize("#table_cost_by_users", function() {

                
                $(function() {
                    $('[data-toggle="popover"]').popover()
                });

                $('.popover-dismiss').popover({
                    trigger: 'focus'
                });
                
                
                var criterios = $('#divCriterios').text();

                var table = $('#table_cost_by_users').DataTable({
                    language: {
                        url: "../../includes/components/datatables/datatables.pt-br.json",
                    },
                    paging: true,
                    deferRender: true,
                    "pageLength": 50,
                });

                var table_2 = $('#table_2_cost_by_users').DataTable({
                    language: {
                        url: "../../includes/components/datatables/datatables.pt-br.json",
                    },
                    paging: true,
                    deferRender: true,
                    "pageLength": 50,
                });

                var table_3 = $('#table_3_cost_by_users').DataTable({
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



		function loadUnits(targetId = 'asset_unit') {

            var loading = $(".loading");
            $(document).ajaxStart(function() {
                loading.show();
            });
            $(document).ajaxStop(function() {
                loading.hide();
            });

            $.ajax({
                url: '../../ocomon/geral/get_units_by_client.php',
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

                let subtext = '';
                $.each(data, function(key, data) {
                    subtext = data.nickname != '' ? ' (' + data.nickname + ')' : '';
                    
                    $('#' + targetId).append('<option data-subtext="' + subtext + '" value="' + data.inst_cod + '">' + data.inst_nome + '</option>');
                });

                $('#' + targetId).selectpicker('refresh');
                if ($('#parent_id').val() != '') {
                    $('#' + targetId).selectpicker('val', $('#parent_unit').val());
                    $('#' + targetId).selectpicker('refresh');
                }
            });
        }



        function loadDepartments(elementValue) {

			if ($("#asset_department").length > 0) {
				var loading = $(".loading");
				$(document).ajaxStart(function() {
					loading.show();
				});
				$(document).ajaxStop(function() {
					loading.hide();
				});

				$.ajax({
					url: '../../admin/geral/get_departments.php',
					method: 'POST',
					dataType: 'json',
					data: {
						client: elementValue,
					},
				}).done(function(data) {

                   
					$('#asset_department').empty();

                    if (Object.keys(data).length > 1) {
                        // $('#asset_department').append('<option value=""><?= TRANS("SEL_SELECT"); ?></option>');
                    }
                    $.each(data, function(key, data) {

                        let unit = "";
                        if (data.unidade != null) {
                            unit = ' (' + data.unidade + ')';
                        }
                        $('#asset_department').append('<option data-subtext="' + unit + '" value="' + data.loc_id + '">' + data.local + '</option>');
                    });
					
                    $('#asset_department').selectpicker('refresh');

				});
			}
		}
		
        
        function loadUsers(elementValue) {

			if ($("#asset_user").length > 0) {
				var loading = $(".loading");
				$(document).ajaxStart(function() {
					loading.show();
				});
				$(document).ajaxStop(function() {
					loading.hide();
				});

				$.ajax({
					url: '../../ocomon/geral/get_users_by_client.php',
					method: 'POST',
					dataType: 'json',
					data: {
						client: elementValue,
					},
				}).done(function(data) {

					$('#asset_user').empty();

                    if (Object.keys(data).length > 1) {
                        // $('#asset_user').append('<option value=""><?= TRANS("SEL_SELECT"); ?></option>');
                    }
                    $.each(data, function(key, data) {

                        let user_subtext = "";
                        if (data.login != null) {
                            user_subtext = ' (' + data.login + ')';
                        }
                        $('#asset_user').append('<option data-subtext="' + user_subtext + '" value="' + data.user_id + '">' + data.nome + '</option>');
                    });
					
                    $('#asset_user').selectpicker('refresh');

				});
			}
		}

        
        function loadAssetTypes(targetId = 'asset_type') {

            var loading = $(".loading");
            $(document).ajaxStart(function() {
                loading.show();
            });
            $(document).ajaxStop(function() {
                loading.hide();
            });

            $.ajax({
                url: '../../invmon/geral/get_asset_types_by_category.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    asset_category: $("#asset_category").val()
                },
            }).done(function(data) {
                $('#' + targetId).empty();
                // if (Object.keys(data).length > 1) {
                //     $('#' + targetId).append('<option value=""><?= TRANS("SEL_SELECT"); ?></option>');
                // }
                $.each(data, function(key, data) {
                    $('#' + targetId).append('<option value="' + data.tipo_cod + '">' + data.tipo_nome + '</option>');
                });

                $('#' + targetId).selectpicker('refresh');
                if ($('#parent_id').val() != '') {
                    $('#' + targetId).selectpicker('val', $('#parent_unit').val());
                    $('#' + targetId).selectpicker('refresh');
                }
            });
        }

        function enableCheckbox (id) {
            $("#" + id).prop("disabled", false);
        }

        function disableCheckbox (id) {
            $("#" + id).prop("checked", false).prop("disabled", true);
        }

        function checkboxControl(id, value) {
            if (value != "") {
                enableCheckbox(id);
            } else {
                disableCheckbox(id);
            }
        }



    </script>
</body>

</html>