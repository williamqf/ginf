<?php
    session_start();
    require_once __DIR__ . "/" . "../../includes/include_geral_new.inc.php";
    require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

    use includes\classes\ConnectPDO;
    $conn = ConnectPDO::getInstance();

    $auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2, 2);

    $_SESSION['s_page_invmon'] = $_SERVER['PHP_SELF'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap-select/dist/css/bootstrap-select.min.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/my_bootstrap_select.css" />
    <link href="../../includes/components/fullcalendar/lib/main.css" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/my_fullcalendar.css" />

    <style>
        .pointer {
            cursor: pointer;
        }
    </style>
</head>

<body>
    
<div class="container-fluid">
    <h4 class="my-4"><i class="fas fa-calendar-alt text-secondary"></i>&nbsp;<?= TRANS('ASSETS_EXPIRING_WARRANTY_CALENDAR'); ?></h4>
    
    <!-- Modal de detalhes do evento clicado no calendário -->
    <div class="modal fade child-modal" id="modalEvent" tabindex="-1" style="z-index:9002!important" role="dialog" aria-labelledby="mymodalEvent" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header text-center bg-light">

                    <h4 class="modal-title w-100 font-weight-bold text-secondary"><i class="fas fa-info-circle"></i>&nbsp;<?= TRANS('ASSET_DETAILS'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <input type="hidden" name="eventAssetId" id="eventAssetId">
                <input type="hidden" name="eventTicketUrl" id="eventTicketUrl">
                
                <div class="row mx-2 mt-4">
                    <div class="form-group col-md-3 font-weight-bold text-right">
                        <?= TRANS('ASSET_TYPE'); ?>:
                    </div>
                    <div class="form-group col-md-8 small" id="asset_type"></div>
                </div>

                <div class="row mx-2">

                    <div class="form-group col-md-3 font-weight-bold text-right">
                        <?= TRANS('CLIENT'); ?>:
                    </div>
                    <div class="form-group col-md-3 small" id="client"></div>
                    <div class="form-group col-md-3 font-weight-bold text-right">
                        <?= TRANS('COL_UNIT'); ?>:
                    </div>
                    <div class="form-group col-md-3 small" id="unit"></div>
                    
                    
                </div>

                

                <div class="row mx-2">
                    <div class="form-group col-md-3 font-weight-bold text-right">
                        <?= TRANS('ASSET_TAG'); ?>:
                    </div>
                    <div class="form-group col-md-3 pointer'"><span class="badge badge-secondary p-2 pointer" id="tag" onclick="goToAssetDetails()"></span></div>
                    
                    <div class="form-group col-md-3 font-weight-bold text-right">
                        <?= TRANS('DEPARTMENT'); ?>:
                    </div>
                    <div class="form-group col-md-3 small" id="department"></div>
                </div>


                <div class="row mx-2">
                    <div class="form-group col-md-3 font-weight-bold text-right">
                        <?= TRANS('PURCHASE_DATE'); ?>:
                    </div>
                    <div class="form-group col-md-3 small" id="purchase_date"></div>
                    
                    <div class="form-group col-md-3 font-weight-bold text-right">
                        <?= TRANS('WARRANTY_EXPIRE'); ?>:
                    </div>
                    <div class="form-group col-md-3 small" id="warranty_expire"></div>
                </div>

                <div class="row mx-2">
                    <div class="form-group col-md-3 font-weight-bold text-right">
                        <?= TRANS('OBSERVATIONS'); ?>:
                    </div>
                    <div class="form-group col-md-9 small" id="comment"></div>
                </div>

                <div class="modal-footer d-flex justify-content-end bg-light">
                    <button id="cancelEventDetails" class="btn btn-secondary" data-dismiss="modal" aria-label="Close"><?= TRANS('BT_CLOSE'); ?></button>
                </div>
            </div>
        </div>
    </div>
    <!-- FINAL DA MODAL DE EVENTOS DO CALENDÁRIO -->

    <div class="form-group row my-0">
        <div class="form-group col-md-4 mt-0 mb-0">
            <select class="form-control " id="client_calendar" name="client_calendar">
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
            <small class="form-text text-muted"><?= TRANS('HELPER_CLIENT_FILTER'); ?></small>
        </div>

        <div class="form-group col-md-4 mt-0 mb-0">
        </div>
        
        <div class="form-group col-md-4 mt-0 mb-0">
        </div>

    </div>

    <input type="hidden" name="opened-colors" class="event-opened" id="opened-colors">
    <input type="hidden" name="closed-colors" class="event-closed" id="closed-colors">
    <input type="hidden" name="scheduled-colors" class="event-scheduled" id="scheduled-colors">

    <div id="calendar" class="calendar"></div>

</div>
    <script src="../../includes/components/jquery/jquery.js"></script>
    <script src="../../includes/components/bootstrap/js/bootstrap.bundle.js"></script>
    <script src="../../includes/components/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="../../includes/components/fullcalendar/lib/main.js"></script>
    <script src="../../includes/components/fullcalendar/lib/locales/pt-br.js"></script>
    <script src="./warranties_calendar.js"></script>
    <script>
        $(function(){

            let params = {};

            $.fn.selectpicker.Constructor.BootstrapVersion = '4';
            $('#worker-calendar, #area-calendar, #client_calendar').selectpicker({
				/* placeholder */
				title: "<?= TRANS('ALL', '', 1); ?>",
				liveSearch: true,
				liveSearchNormalize: true,
				liveSearchPlaceholder: "<?= TRANS('BT_SEARCH', '', 1); ?>",
				noneResultsText: "<?= TRANS('NO_RECORDS_FOUND', '', 1); ?> {0}",
				style: "",
				styleBase: "form-control ",
			});

            let client = $('#client_calendar').val();
            params.client = client;

            if ($('#client_calendar').length > 0){
                $('#client_calendar').on('change', function () {
                    $('#client_calendar').selectpicker('refresh');
                    client = $('#client_calendar').val();
                    params.client = client;
                    showCalendar('calendar', params);
                });
            }

            showCalendar('calendar', params);
            $(window).resize(function() {
                showCalendar('calendar', params);
            });
        });
        
        
        function goToAssetDetails() {
            let url = ('../../invmon/geral/asset_show.php?asset_id='+$('#eventAssetId').val());
            if (url != '') {
                window.open(url, '_blank','left=100,dependent=yes,width=900,height=600,scrollbars=yes,status=no,resizable=yes');
            }
        }

        
    </script>

</body>

</html>