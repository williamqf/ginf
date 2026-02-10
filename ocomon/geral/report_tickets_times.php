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
        <h5 class="my-4"><i class="fas fa-clock text-secondary"></i>&nbsp;<?= TRANS('REPORT_TICKETS_TREATING_TIMES'); ?></h5>
        <div class="modal" id="modal" tabindex="-1" style="z-index:9001!important">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div id="divDetails" style="position:relative">
                        <iframe id="ticketsInfo"  frameborder="0" style="position:absolute;top:0px;width:100%;height:100vh;"></iframe>
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
        <div id="msg_1" class="d-none">
            <?= message('info','', TRANS('MSG_REPORT_TICKETS_TREATING_TIMES'), '', '', true); ?>
        </div>
        <div id="display-buttons" class="display-buttons d-none"></div>
        <div id="table" class="table-responsive"></div>
        <div class="chart-container" id="container01"></div>
        
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

            $('#modal').on('hidden.bs.modal', function(){
                $("#ticketsInfo").attr('src','');
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
					url: './report_tickets_times_process.php',
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
                        $('#container02').empty();
						$("#idSubmit").prop("disabled", false);
					} else {
                        /* Aqui ocorrerá as chamadadas para a montagem da tabela e também para os gráficos */
                        $('#msg_1').removeClass('d-none');
                        $('#display-buttons').empty().removeClass('d-none');
                        // $('#msg_2').removeClass('d-none');

                        
                        let table = '<table id="table_treater_times" class="table table-striped table-bordered" cellspacing="0" width="100%">';
                        table += '<caption id="table_caption" class="title"><span class="title">' + data['tickets'].title + '</span><br />' + data.criteria + '</caption>';

                        table += '<thead>';
                            table += '<tr class="header table-borderless">';
                                table += '';
                                table += '<td class="line"><?= mb_strtoupper(TRANS('TICKET_NUMBER')); ?></td>';
                                table += '<td class="line"><?= mb_strtoupper(TRANS('CLIENT')); ?></td>';
                                table += '<td class="line"><?= mb_strtoupper(TRANS('ISSUE_TYPE')); ?></td>';
                                table += '<td class="line"><?= mb_strtoupper(TRANS('SERVICE_AREA')); ?></td>';
                                table += '<td class="line"><?= mb_strtoupper(TRANS('PROVIDED_TIME')); ?></td>';
                                table += '<td class="line"><?= mb_strtoupper(TRANS('FILTERED_IN_DIRECT_QUEUE')); ?></td>';
                                table += '<td class="line"><?= mb_strtoupper(TRANS('ABSOLUTE_IN_DIRECT_QUEUE')); ?></td>';
                            table += '</tr>';

                        table += '</thead>';
                        table += '</tbody>';
                        let totalRecords = 0;
                        for (var i in data['tickets']) {

                            if (data['tickets'][i].numero !== undefined) {
                                table += '<tr>';
                                table += '<td class="line"><span class="ticket-number" data-ticket="' + data['tickets'][i].numero + '">' + data['tickets'][i].numero + '</span></td>';
                                table += '<td class="line">' + data['tickets'][i].cliente + '</td>';
                                table += '<td class="line">' + data['tickets'][i].problema + '</td>';
                                table += '<td class="line">' + data['tickets'][i].sistema + '</td>';
                                table += '<td data-sort="' + data['tickets'][i].seconds + '" class="line">' + data['tickets'][i].concated_time + '</td>';
                                table += '<td data-sort="' + data['tickets'][i].filtered_secs_in_direct_queue + '" class="line">' + data['tickets'][i].filtered_time_in_direct_queue + '</td>';
                                table += '<td data-sort="' + data['tickets'][i].abs_secs_in_direct_queue + '" class="line">' + data['tickets'][i].abs_time_in_direct_queue + '</td>';
                                
                                table += '</tr>';
                            }
                        }
                        table += '</tbody>';
                        table += '<tfoot>';
                            table += '<tr class="header table-borderless">';
                                table += '<td></td>';
                                table += '<td></td>';
                                table += '<td></td>';
                                table += '<td></td>';
                                table += '<td class="line">' + data['tickets'].totalProvidedInTime + '</td>';
                                table += '<td class="line">' + data['tickets'].totalInQueueFilteredInTime + '</td>';
                                table += '<td class="line">' + data['tickets'].totalInQueueAbsoluteInTime + '</td>';
                            table += '</tr>';
                        table += '</tfoot>';
                        table += '</table>';

                        $('#table').html(table);

                        
                        if (data.generate_chart == 1) {
                        
                            /* Aqui ocorrerá a montagem dos gráficos */
                            let canvas01 = '<canvas id="graph_01" class="mb-5"></canvas>'
                            let canvas02 = '<canvas id="graph_02" class="mb-5"></canvas>'
                            $('#container01').empty().append(canvas01);
                            $('#container02').empty().append(canvas02);
                            
                            let instances = Object.keys(window.Chart.instances).length;

                            const chart_01 = general_chart(data, 'chart_01', 'nome', 'seconds', 'graph_01', 'secondsToHms');
                            const chart_02 = general_chart(data, 'chart_02', 'name', 'filteredSeconds', 'graph_02', 'secondsToHms');
                            
                            addPercentageLabels(chart_01, (1 + instances));
                            addPercentageLabels(chart_02, (2 + instances));
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
            var obs = $.initialize("#table_treater_times", function() {
                
                $(function() {
                    $('[data-toggle="popover"]').popover()
                });

                $('.popover-dismiss').popover({
                    trigger: 'focus'
                });

                $('.ticket-number').css('cursor', 'pointer').on('click', function() {
                    var ticket = $(this).data('ticket');
                    openTicketInfo(ticket);
                });
                
                var criterios = $('#divCriterios').text();

                var table = $('#table_treater_times').DataTable({
                    language: {
                        url: "../../includes/components/datatables/datatables.pt-br.json",
                    },
                    order: [0, 'asc'],
                    searching: true,
                    paging: true,
                    deferRender: true,
                    "pageLength": 50,
                });



                new $.fn.dataTable.Buttons(table, {

                    buttons: [
                        {
                            extend: 'print',
                            text: '<?= TRANS('SMART_BUTTON_PRINT', '', 1) ?>',
                            title: '<?= TRANS('REPORT_TICKETS_TREATING_TIMES', '', 1) ?>',
                            message: $('#print-info').html(),
                            autoPrint: true,
                            footer: true,

                            customize: function(win) {
                                $(win.document.body).find('table').addClass('display').css('font-size', '10px');
                                $(win.document.body).find('tr:nth-child(odd) td').each(function(index) {
                                    $(this).css('background-color', '#f9f9f9');
                                });
                                $(win.document.body).find('h1').css('text-align', 'center');
                            },
                            exportOptions: {
                                columns: ':visible'
                            },
                        },
                        {
                            extend: 'copyHtml5',
                            text: '<?= TRANS('SMART_BUTTON_COPY', '', 1) ?>',
                            exportOptions: {
                                columns: ':visible'
                            },
                            footer: true,
                        },
                        {
                            extend: 'excel',
                            text: "Excel",
                            exportOptions: {
                                columns: ':visible'
                            },
                            filename: '<?= TRANS('REPORT_TICKETS_TREATING_TIMES', '', 1); ?>-<?= date('d-m-Y-H:i:s'); ?>',
                        },
                        {
                            extend: 'csvHtml5',
                            text: "CSV",
                            exportOptions: {
                                columns: ':visible'
                            },

                            filename: '<?= TRANS('REPORT_TICKETS_TREATING_TIMES', '', 1); ?>-<?= date('d-m-Y-H:i:s'); ?>',
                        },
                        {
                            extend: 'pdfHtml5',
                            text: "PDF",

                            exportOptions: {
                                columns: ':visible',
                            },
                            title: '<?= TRANS('REPORT_TICKETS_TREATING_TIMES', '', 1); ?>',
                            filename: '<?= TRANS('REPORT_TICKETS_TREATING_TIMES', '', 1); ?>-<?= date('d-m-Y-H:i:s'); ?>',
                            // orientation: 'landscape',
                            orientation: 'landscape',
                            pageSize: 'A4',
                            footer: true,

                            customize: function(doc) {
                                var criterios = $('#table_caption').text()
                                var rdoc = doc;
                                
                                var rcout = doc.content[doc.content.length - 1].table.body.length - 1;
                                doc.content.splice(0, 1);
                                
                                // console.log(doc.content)
                                // var rcout = doc.content[1].table.body.length - 1;
                                // doc.content.splice(0, 1);
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
                                                            text: '<?= TRANS('REPORT_TICKETS_TREATING_TIMES', '', 1); ?>',
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

                // $('.display-buttons').empty();
                table.buttons().container()
                .appendTo($('.display-buttons:eq(0)', table.table().container()));

            }, {
                target: document.getElementById('table')
            }); /* o target limita o scopo do mutate observer */

        });


        function openTicketInfo(ticket) {
            let location = 'ticket_show.php?numero=' + ticket;
            $("#ticketsInfo").attr('src',location)
            $('#modal').modal();
        }

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