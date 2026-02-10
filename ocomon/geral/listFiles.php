<?php 
 /*                        Copyright 2023 Flávio Ribeiro

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
*/session_start();

if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
	$_SESSION['session_expired'] = 1;
    echo "<script>top.window.location = '../../index.php'</script>";
	exit;
}

require_once __DIR__ . "/" . "../../includes/include_geral_new.inc.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 3, 1);

$isAdmin = $_SESSION['s_nivel'] == 1;

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/datatables/datatables.min.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/my_datatables.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/estilos_custom.css" />

	<title><?= APP_NAME; ?>&nbsp;<?= VERSAO; ?></title>
</head>

<body>
	

	<?php
		$cod = (isset($_GET['COD']) && !empty($_GET['COD']) ? (int)$_GET['COD'] : '');
		$ticket = $cod;
		
		if (empty($cod)) {
			echo message('danger', 'Ooops!', TRANS('MSG_ERROR_PARAM'), '', '', 1);
			return;
		}

if (!$isAdmin) {
    /* Controle de acesso para arquivos de chamados */

    $ticketData = getTicketData($conn, $ticket, ['sistema', 'aberto_por', 'registration_operator']);

    $isRequester = $ticketData['aberto_por'] == $_SESSION['s_uid'];

    if (!$isRequester) {
        $isBasicUser = ($_SESSION['s_nivel'] == 3) ? true : false;
        $isRegistrationUser = $_SESSION['s_uid'] == $ticketData['registration_operator'];
        
        if ($isBasicUser && !$isRegistrationUser) {
            /* Pode ser gerente de área */
            $managebleAreas = getManagedAreasByUser($conn, $_SESSION['s_uid']);
            $managebleAreas = array_column($managebleAreas, 'sis_id');
            
            $openerArea = getOpenerInfo($conn, $ticketData['aberto_por'])['AREA'];
            $isAreaAdmin = in_array($openerArea, $managebleAreas);
            
            if (!$_SESSION['s_area_admin'] || !in_array($openerArea, $managebleAreas)) {
                echo message('danger', 'Ooops!', TRANS('MSG_NOT_ALLOWED'), '', '', true);
                exit;
            }
        } elseif ($_SESSION['s_nivel'] == 2 && !$isRegistrationUser) {
            /* Usuário com nível de operação */
            $uareas = explode(',', (string)$_SESSION['s_uareas']);
            if (!in_array($ticketData['sistema'], $uareas)) {
                echo message('danger', 'Ooops!', TRANS('MSG_NOT_ALLOWED'), '', '', true);
                exit;
            }
        }
        /* Se for o $isRegistrationUser, pode acessar o conteúdo */
    }
    /* Se for o solicitante, pode acessar o conteúdo */
}




	?>
	<div class="container">
		<div id="idLoad" class="loading" style="display:none"></div>
	</div>


	<div class="container-fluid">
		<h5 class="my-4"><i class="fas fa-paperclip text-secondary"></i>&nbsp;<?= TRANS('ATTACHED_FILES_TO_THE_TICKET'); ?>:&nbsp;<?= $cod; ?></h5>
		<div class="modal" id="modal" tabindex="-1" style="z-index:9001!important">
			<div class="modal-dialog modal-xl">
				<div class="modal-content">
					<div id="divDetails">
					</div>
				</div>
			</div>
		</div>

	<?php


	$sql = "SELECT img_cod, img_nome, img_tipo, img_altura, img_largura, img_size FROM imagens WHERE img_oco = {$cod} ";
	$res = $conn->query($sql);

	if (!$res->rowCount()) {
		echo message('info', 'Ooops!', TRANS('NO_RECORDS_FOUND'), '', '', 1);
		return;
	}

	?>
	<table id="attachments" class="stripe hover order-column row-border" border="0" cellspacing="0" width="100%">

	<thead>
		<tr class="header">
			<td class="line" scope="col">#</td>
			<td class="line" scope="col"><?= TRANS('COL_TYPE'); ?></td>
			<td class="line" scope="col"><?= TRANS('SIZE'); ?></td>
			<td class="line" scope="col"><?= TRANS('FILE'); ?></td>
		</tr>
	</thead>
	<tbody>


	<?php
	$i = 1;
	foreach ($res->fetchAll() as $rowFiles) {

		$size = round($rowFiles['img_size'] / 1024, 1);
		$rowFiles['img_tipo'] . "](" . $size . "k)";

		if (isImage($rowFiles["img_tipo"])) {
			

			$viewImage = "&nbsp;<a onClick=\"javascript:popupWH('../../includes/functions/showImg.php?" .
			"file=" . $cod . "&cod=" . $rowFiles['img_cod'] . "'," . $rowFiles['img_largura'] . "," . $rowFiles['img_altura'] . ")\" " .
			"title='".TRANS('VIEW')."'><i class='fa fa-search'></i></a>";
			
		} else {
			$viewImage = "";
		}
		?>
		<tr>
			<th scope="row"><?= $i; ?></th>
			<td class="line"><?= $rowFiles['img_tipo']; ?></td>
			<td class="line"><?= $size; ?>k</td>
			<td><a onClick="redirect('../../includes/functions/download.php?file=<?= $cod; ?>&cod=<?= $rowFiles['img_cod']; ?>')" title="Download the file"><?= $rowFiles['img_nome']; ?></a><?= $viewImage; ?></i></td>
		</tr>
		<?php
		$i++;
	}
	?>
	</tbody>
	</table>
	
	<script src="../../includes/javascript/funcoes-3.0.js"></script>
	<script src="../../includes/components/jquery/jquery.js"></script>
	<script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>

	<script type="text/javascript">
		$(function() {

			$('#attachments').DataTable({
				paging: true,
				deferRender: true,
				
				"language": {
					"url": "../../includes/components/datatables/datatables.pt-br.json"
				}
			});

		});

	</script>


</body>
</html>
