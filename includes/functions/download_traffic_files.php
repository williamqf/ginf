<?php 
session_start();

	require_once (__DIR__ . "/../include_basics_only.php");
	require_once __DIR__ . "/" . "../classes/ConnectPDO.php";
	use includes\classes\ConnectPDO;


	if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] != 1) {
		exit;
	}
    $conn = ConnectPDO::getInstance();

    $auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2, 2);


    /**
     * Checar se o usuário logado pode acessar o arquivo
     * - Apenas o usuário com o mesmo user_id ou administrador do sistema podem acessar
     * - Ver se pode-se permitir acesso também pelo gerente da área do usuário proprietário do arquivo
     */

    $isAdmin = (isset($_SESSION['s_nivel']) && $_SESSION['s_nivel'] == 1 ? true : false);

    if (!$isAdmin) {
        /* Controle sobre as unidades que podem ser visualizadas pela área primária do operador */
        if (!empty($_SESSION['s_allowed_units'])) {
            /* Unidades limitadas - ver controle */
            // inst.inst_cod IN ({$_SESSION['s_allowed_units']})
        }
    }


    $data = [];
    $data['file_id'] = (isset($_GET['file_id']) && !empty($_GET['file_id']) ? (int)$_GET['file_id'] : '');
    $data['user_id'] = $_SESSION['s_uid'];


    if (empty($data['user_id']) || empty($data['file_id'])) {
        echo TRANS('MSG_ERR_GET_DATA');
        return;
    }

    $userInfo = getUserInfo($conn, $data['user_id']);
    if (empty($userInfo)) {
        $data['success'] = false;
        echo json_encode([]);
        return false;
    }
    
    $terms = "";


	if (!empty($data['file_id'])) {

		$query = "SELECT 
                    file, 
                    file_name, 
                    mime_type,
                    file_size
                FROM 
                    traffic_files 
                WHERE 
                    id = {$data['file_id']} 
                    {$terms}
                    ";
		try {
			$result = $conn->query($query);
            $row = $result->fetch();

            header("Content-length: " . $row['file_size']);
            header("Content-type: " . $row['mime_type']);
            header("Content-Disposition: attachment; filename=" . $row['file_name']);
            echo $row['file'];
		}
		catch (Exception $e) {
			echo TRANS('MSG_ERR_GET_DATA');
            echo "<br>Retorno: " . $e->getMessage();
			return;
		}
        return;
	}
?>