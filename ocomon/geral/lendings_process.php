<?php session_start();
/*      Copyright 2025 Flávio Ribeiro

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
$exception = "";
$data = [];
$data['success'] = true;
/* update | create | delete */
$data['action'] = (isset($post['action']) && !empty($post['action']) ? noHtml($post['action']) : "");

if (empty($data['action'])) {
    $data['success'] = false; 
    $data['field_id'] = "action";
    $data['message'] = message('warning', 'Ooops!', TRANS('SOME_ERROR_DONT_PROCEED'),'');
    echo json_encode($data);
    return false;
}


$data['cod'] = (isset($post['cod']) ? intval($post['cod']) : "");
$data['message'] = "";
$data['field_id'] = "";

$data['author'] = $_SESSION['s_uid'];
$data['csrf_session_key'] = (isset($post['csrf_session_key']) ? $post['csrf_session_key'] : "csrf_token");
$data['material'] = (isset($post['material']) && !empty($post['material']) ? noHtml($post['material']) : "");
$data['taker'] = (isset($post['taker']) && !empty($post['taker']) ? noHtml($post['taker']) : "");
$data['phone'] = (isset($post['phone']) && !empty($post['phone']) ? noHtml($post['phone']) : "");
$data['department'] = (isset($post['department']) && !empty($post['department']) ? (int)$post['department'] : "");
$data['authorizer'] = (isset($post['authorizer']) && !empty($post['authorizer']) ? (int)$post['authorizer'] : "");
$data['borrow_date'] = (isset($post['borrow_date']) && !empty($post['borrow_date']) ? noHtml($post['borrow_date']) : "");
$data['return_date'] = (isset($post['return_date']) && !empty($post['return_date']) ? noHtml($post['return_date']) : null);


if ($data['action'] == "create" || $data['action'] == "update") {
	
	if (empty($data['material'])) {
		$data['success'] = false; 
		$data['field_id'] = "material";
		$data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
		echo json_encode($data);
		return false;
	}

	if (empty($data['taker'])) {
		$data['success'] = false; 
		$data['field_id'] = "taker";
		$data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
		echo json_encode($data);
		return false;
	}

	if (empty($data['phone'])) {
		$data['success'] = false; 
		$data['field_id'] = "phone";
		$data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
		echo json_encode($data);
		return false;
	}

	if (empty($data['department'])) {
		$data['success'] = false; 
		$data['field_id'] = "department";
		$data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
		echo json_encode($data);
		return false;
	}

	if (empty($data['authorizer'])) {
		$data['success'] = false; 
		$data['field_id'] = "authorizer";
		$data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
		echo json_encode($data);
		return false;
	}

	if (empty($data['borrow_date'])) {
		$data['success'] = false; 
		$data['field_id'] = "borrow_date";
		$data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
		echo json_encode($data);
		return false;
	}

	$start_time = ' 00:00:00';
	$end_time = ' 23:59:59';

	if (!isValidDate($data['borrow_date'])) {
		$data['success'] = false; 
		$data['field_id'] = "borrow_date";
		$data['message'] = message('warning', 'Ooops!', TRANS('BAD_FIELD_FORMAT'),'');
		echo json_encode($data);
		return false;
	}

	$data['borrow_date'] = dateDB($data['borrow_date'] . date(' H:i:s'));

	if (!empty($data['return_date'])) {
		if (!isValidDate($data['return_date'])) {
			$data['success'] = false; 
			$data['field_id'] = "return_date";
			$data['message'] = message('warning', 'Ooops!', TRANS('BAD_FIELD_FORMAT'),'');
			echo json_encode($data);
			return false;
		}
		$data['return_date'] = dateDB($data['return_date'] . date(' H:i:s'));
	}
}




if ($data['action'] == "create") {

	if (!csrf_verify($post, $data['csrf_session_key'])) {
		$data['success'] = false; 
		$data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');

		echo json_encode($data);
		return false;
	}

	$sql = "INSERT INTO emprestimos 
				(material, responsavel, data_empr, data_devol, quem, local, ramal)
			VALUES 
				(:material, :responsavel, :data_empr, :data_devol, :quem, :local, :ramal)
		";

	try {
		$res = $conn->prepare($sql);
		$res->bindParam(':material', $data['material'], PDO::PARAM_STR);
		$res->bindParam(':responsavel', $data['authorizer'], PDO::PARAM_INT);
		$res->bindParam(':data_empr', $data['borrow_date'], PDO::PARAM_STR);
		$res->bindParam(':data_devol', $data['return_date']);
		$res->bindParam(':quem', $data['taker'], PDO::PARAM_STR);
		$res->bindParam(':local', $data['department'], PDO::PARAM_INT);
		$res->bindParam(':ramal', $data['phone'], PDO::PARAM_INT);

		$res->execute();
	
		
		/* Notificação para o usuário marcado como responsável */
		$sentNotification = false;
		if ($data['author'] != $data['authorizer']) {
			$sentNotification = setUserNotification($conn, $data['authorizer'], 1, TRANS('MSG_YOU_WERE_SET_AS_RESPONSIBLE_FOR_BORROWED_ITEM'), $data['author']);
		}



		$data['message'] = TRANS('MSG_SUCCESS_INSERT');
		$_SESSION['flash'] = message('success', 'Yeahh!', $data['message'] . $exception, '', '');
		echo json_encode($data);
		return false;
	
	
	} catch (PDOException $e) {
		$exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
		$data['success'] = false; 
		$data['message'] = message('warning', 'Ooops!', TRANS('MSG_ERR_SAVE_RECORD') . $exception, '', '');
		echo json_encode($data);
		return false;
	}

	

} elseif ($data['action'] == "update") {

	if (empty($data['cod'])) {
		$data['success'] = false; 
		$data['field_id'] = "cod";
		$data['message'] = message('warning', 'Ooops!', TRANS('SOME_ERROR_DONT_PROCEED'),'');
		echo json_encode($data);
		return false;
	}

	if (!csrf_verify($post, $data['csrf_session_key'])) {
		$data['success'] = false; 
		$data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');

		echo json_encode($data);
		return false;
	}

	$sql = "UPDATE emprestimos 
				SET
					material = :material,
					responsavel = :responsavel,
					ramal = :ramal,
					data_empr = :data_empr,	
					data_devol = :data_devol,
					quem = :quem,
					local = :local
			WHERE empr_id = :empr_id
		";

	try {
		$res = $conn->prepare($sql);
		$res->bindParam(':material', $data['material'], PDO::PARAM_STR);
		$res->bindParam(':responsavel', $data['authorizer'], PDO::PARAM_INT);
		$res->bindParam(':data_empr', $data['borrow_date'], PDO::PARAM_STR);
		$res->bindParam(':data_devol', $data['return_date']);
		$res->bindParam(':quem', $data['taker'], PDO::PARAM_STR);
		$res->bindParam(':local', $data['department'], PDO::PARAM_INT);
		$res->bindParam(':ramal', $data['phone'], PDO::PARAM_INT);
		$res->bindParam(':empr_id', $data['cod'], PDO::PARAM_INT);
	
		$res->execute();

		
		
		/* Se o registro sofreu modificação, envia Notificação para o usuário marcado como responsável */
		$sentNotification = false;
		if ($res->rowCount() > 0 && $data['author'] != $data['authorizer']) {
			$sentNotification = setUserNotification($conn, $data['authorizer'], 1, TRANS('MSG_YOU_WERE_SET_AS_RESPONSIBLE_FOR_BORROWED_ITEM_UPDATED'), $data['author']);
		}
		

		$data['message'] = TRANS('MSG_SUCCESS_EDIT');
		$_SESSION['flash'] = message('success', 'Yeahh!', $data['message'] . $exception, '', '');
		echo json_encode($data);
		return false;
	
	
	} catch (PDOException $e) {
		$exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
		$data['success'] = false; 
		$data['message'] = message('warning', 'Ooops!', TRANS('MSG_ERR_DATA_UPDATE') . $exception, '', '');
		echo json_encode($data);
		return false;
	}

} elseif ($data['action'] == "delete") {

	// $data['success'] = false;

	if (empty($data['cod'])) {
		$data['success'] = false; 
		$data['field_id'] = "cod";
		$data['message'] = message('warning', 'Ooops!', TRANS('SOME_ERROR_DONT_PROCEED'),'');
		echo json_encode($data);
		return false;
	}

	$sql = "DELETE FROM emprestimos WHERE empr_id = :empr_id";

	try {
		$res = $conn->prepare($sql);
		$res->bindParam(':empr_id', $data['cod'], PDO::PARAM_INT);
		$res->execute();

		$data['success'] = true;
		$data['message'] = TRANS('OK_DEL');
		$_SESSION['flash'] = message('success', 'Yeahh!', $data['message'] . $exception, '', '');
		echo json_encode($data);
		return false;
	
	
	} catch (PDOException $e) {
		$exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
		$data['success'] = false; 
		$data['message'] = message('warning', 'Ooops!', TRANS('MSG_ERR_DATA_DELETE') . $exception, '', '');
		echo json_encode($data);
		return false;
	}
}




echo json_encode($data);
