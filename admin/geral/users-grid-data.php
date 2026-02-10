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

include_once ("../../includes/include_basics_only.php");
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

$areaAdmin = 0;
if (isset($_SESSION['s_area_admin']) && $_SESSION['s_area_admin'] == '1') {
    $areaAdmin = 1;
}

if (!$areaAdmin) {
    $auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 1);
}

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();
/* Database connection end */

// storing  request (ie, get/post) global array to a variable  
// $requestData = $_REQUEST;
$requestData = $_POST;
$terms = "";


$searchStatusTermOptions = [
    '1' => TRANS('NO_ASSETS_LINKED'),
    '2' => TRANS('WITH_ASSETS_LINKED'),
    '3' => TRANS('TERM_OUTDATED'),
    '4' => TRANS('TERM_SIGNED'),
    '5' => TRANS('SIGNING_PENDING'),
];

$termsIndexes = [
    '' => "",
    '1' => " AND (utp.user_id IS NULL OR utp.user_id = 0) ",
    '2' => " AND utp.user_id IS NOT NULL ",
    '3' => " AND is_term_updated = 0 AND utp.user_id IS NOT NULL ",
    '4' => " AND is_term_signed = 1 ",
    '5' => " AND is_term_signed = 0 AND is_term_updated = 1 AND utp.user_id IS NOT NULL "
];

$terms = $termsIndexes[$requestData['term_status']];


$origin = "users.php";

$columns = array(
	// datatable column index  => database column name
    0 => 'nome',
    1 => 'login',
    2 => 'nickname',
    3 => 'user_admin',
    4 => 'sistema',
    5 => 'email',
    6 => 'nivel_nome',
    7 => 'last_logon',
    8 => 'user_id',
    9 => 'user_id'
);


// if (isset($requestData['areaAdmin']) && $requestData['areaAdmin'] == "1") {
if ($areaAdmin && $_SESSION['s_nivel'] != 1) {

    $userManageableAreas = getManagedAreasByUser($conn, $_SESSION['s_uid']);
    $csvAreas = "";
    foreach ($userManageableAreas as $mArea) {
        if (strlen((string)$csvAreas) > 0) 
            $csvAreas .= ',';
        $csvAreas .= $mArea['sis_id'];
    }
    // $terms .= " AND s.sis_id = '" . $_SESSION['s_area'] . "' ";
    $terms .= " AND s.sis_id IN ({$csvAreas}) ";
}


// getting total number records without any search
$sql = "SELECT u.*, n.*,s.*, cl.*, 
            utp.user_id as has_assets, utp.is_term_updated, utp.is_term_signed, utp.signed_at
        FROM usuarios u 
        LEFT JOIN sistemas as s on u.AREA = s.sis_id 
        LEFT JOIN nivel as n on n.nivel_cod = u.nivel
        LEFT JOIN clients as cl on cl.id = u.user_client
        LEFT JOIN users_terms_pivot as utp on utp.user_id = u.user_id

        WHERE 
            u.user_id > 0 
            {$terms};
";


// var_dump($sql);


$sqlResult = $conn->query($sql);
$totalData = $sqlResult->rowCount();
$totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.

$sql = "SELECT u.*, n.*,s.*, cl.*, 
            utp.user_id as has_assets, utp.is_term_updated, utp.is_term_signed, utp.signed_at
		FROM usuarios u 
		LEFT JOIN sistemas as s on u.AREA = s.sis_id 
		LEFT JOIN nivel as n on n.nivel_cod = u.nivel 
        LEFT JOIN clients as cl on cl.id = u.user_client
        LEFT JOIN users_terms_pivot as utp on utp.user_id = u.user_id
";

$sql.=" WHERE u.user_id > 0 {$terms} ";


if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter

    $sql.=" AND ( nome LIKE '%".$requestData['search']['value']."%' ";  
	$sql.=" OR nickname LIKE '%".$requestData['search']['value']."%' ";
	$sql.=" OR nickname LIKE '%".$requestData['search']['value']."%' ";
	$sql.=" OR login LIKE '%".$requestData['search']['value']."%' ";
	$sql.=" OR sistema LIKE '%".$requestData['search']['value']."%' ";
	$sql.=" OR nivel_nome LIKE '%".$requestData['search']['value']."%' ";
	$sql.=" OR email LIKE '%".$requestData['search']['value']."%' )";
}


$sqlResult = $conn->query($sql);
$totalFiltered = $sqlResult->rowCount();
// echo($columns[$requestData['order'][0]['column']]);

$sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
//  dump($sql);
$sqlResult = $conn->query($sql);

$data = array();
foreach ($sqlResult->fetchall() as $row) {

    $area_admin = ($row['user_admin'] ? '<span class="text-success"><i class="fas fa-check"></i></span>' : '');

    $areasManaged = getManagedAreasByUser($conn, $row['user_id']);
    $areasManagedNames = '';

    if (!empty($areasManaged)) {
        foreach ($areasManaged as $areaM) {
            $areasManagedNames .= '<li class="area_admins">' . $areaM['sistema'] ?? '' . '</li>';
        }
    } else {
        $areasManagedNames = '<span class="text-danger"><i class="fas fa-ban"></i></span>';
    }
    
    
	$nestedData=array(); 

	$nestedData[] = $row['nome'];
    $nestedData[] = $row['login'];
    $nestedData[] = $row['nickname'];
    // $nestedData[] = $area_admin;
    $nestedData[] = $areasManagedNames;
    $nestedData[] = $row['sistema'];
    $nestedData[] = $row['email'];
    $nestedData[] = $row['nivel_nome'];
    $nestedData[] = dateScreen($row['last_logon']);
    
    $nestedData[] = "<button type='button' class='btn btn-secondary btn-sm' onClick=\"loadInIframe('users','action=edit&cod=". $row['user_id'] ."')\">" . TRANS('BT_EDIT') . "</button>";
    
    $nestedData[] = "<button type='button' class='btn btn-danger btn-sm' onclick=\"confirmDeleteModal('" . $row['user_id'] . "')\">" . TRANS('BT_REMOVE') . "</button>";
	$data[] = $nestedData;
}


$json_data = array(
    "draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
    "recordsTotal"    => intval( $totalData ),  // total number of records
    "recordsFiltered" => intval( $totalFiltered ), // total number of records after searching, if there is no searching then totalFiltered = totalData
    "data"            => $data   // total data array
    );

echo json_encode($json_data);  // send data as json format

?>