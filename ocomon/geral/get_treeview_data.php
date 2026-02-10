<?php session_start();
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
  */

if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
    $_SESSION['session_expired'] = 1;
    echo "<script>top.window.location = '../../index.php'</script>";
    exit;
}

require_once __DIR__ . "/" . "../../includes/include_basics_only.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";
require_once __DIR__ . "/" . "../../includes/classes/worktime/Worktime.php";
require_once __DIR__ . "/" . "../../includes/functions/getWorktimeProfile.php";
use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2, 1);

$isAdmin = $_SESSION['s_nivel'] == 1;
$exception = "";

$post = (isset($_POST) ? $_POST : []);

if (!isset($post['ticket']) || empty($post['ticket'])) {
    return false;
}

$post['ticket'] = (int)$post['ticket'];


/* Controle para limitar os resultados das consultas às áreas do usuário logado quando a opção estiver habilitada */
$qry_filter_areas = "";
/* Filtro de seleção de áreas - formulário no painel de controle */
$filtered_areas = "";


$aliasAreasFilter = "o.sistema";


if (empty($filtered_areas)) {
    if ($isAdmin) {
        $qry_filter_areas = "";
    } else {
        $qry_filter_areas = " AND (" . $aliasAreasFilter . " IN ({$_SESSION['s_uareas']}) OR " . $aliasAreasFilter . " = '-1')";
    }
}

$config = getConfig($conn);
$data = [];
$data['success'] = true;

/* Média absoluta de resposta e solução para chamados encerrados */
/* Final das info dos chamados encerrados na data corrente */


$storeSons = [];
$storeParent = [];
$firstOfAll = getFirstFather($conn, $post['ticket'], $storeParent);
$storeSons = getTicketDownRelations($conn, $firstOfAll, $storeSons);


foreach (array_keys($storeSons) as $key) {
    $sql = "SELECT
                s.sistema, p.problema
            FROM
                ocorrencias o,
                sistemas s,
                problemas p
            WHERE
                o.sistema = s.sis_id AND
                o.problema = p.prob_id AND
                o.numero = :ticket
                {$qry_filter_areas}
    ";

    try {
        $result = $conn->prepare($sql);
        $result->bindParam(':ticket', $key);
        $result->execute();
        $res = $result->fetch();
        $data[$key]['area'] = $res['sistema'];
        $data[$key]['problema'] = $res['problema'];
        // $data['area'][$key] = $res['sistema'];
        // $data['problema'][$key] = $res['problema'];
    } catch (Exception $e) {
        
        $data['success'] = false;
        $data['message'] = $e->getMessage();
        $data['sql'] = $sql;
        echo json_encode($data);
        return false;
    }
}


echo json_encode($data);
