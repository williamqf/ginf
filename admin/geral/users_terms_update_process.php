<?php session_start();
/*  Copyright 2023 Flávio Ribeiro

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

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 1);
$exception = "";
$data = [];
$data['success'] = true;


/**
 * Atualizar a tabela users_terms_pivot com base nas informações da tabela users_x_files pois a users_terms_pivot 
 * foi criada depois de a tabela users_x_files já possuir informações cadastradas.
 * Só deve rodar se a tabela users_terms_pivot estiver vazia.
 * 
 * IMPORTANTE: Esse script só faz sentido na versão GTGroup a partir da atualização para esta versão
 * - Não será necessário em nenhuma outra versão, mesmo na GTGroup
 */



/* Se já existirem dados na users_terms_pivot significa que esse script já foi executado */
$sql = "SELECT id FROM users_terms_pivot LIMIT 1
";
try {
    $res = $conn->query($sql);
    if ($res->rowCount() > 0) {
        $data['success'] = true;
        $data['message'] = "Termos já atualizados.";
        echo json_encode($data);
        return true;
    }
} catch (PDOException $e) {
    $data['success'] = false;
    $data['message'] = $e->getMessage();
    echo json_encode($data);
    return true;
}


$sql = "SELECT 
            MAX(id) AS id
        FROM
            users_x_files
        WHERE
            file_type = 1
        GROUP BY
            user_id
";

try {
    $res = $conn->query($sql);
    if ($res->rowCount() > 0) {
        foreach ($res->fetchall() as $rowID) {
            $sql2 = "SELECT * FROM users_x_files WHERE id = " . $rowID['id'] . "";
            $res2 = $conn->query($sql2);
            
            foreach ($res2->fetchAll() as $row2) {
                $isTermUpdated = isUserTermUpdated($conn, $row2['user_id']);
                insertOrUpdateUsersTermsPivotTable($conn, $row2['user_id'], $isTermUpdated, false, $row2['signed_at']);
            }
        }
    }

} catch (PDOException $e) {
    $exception = $e->getMessage();
    $data['success'] = false;
    $data['message'] = $exception;
    echo json_encode($data);
    return true;
}

$data['success'] = true; 
$data['message'] = "Atualização dos termos dos usuários executada com sucesso!";

echo json_encode($data);
return true;
