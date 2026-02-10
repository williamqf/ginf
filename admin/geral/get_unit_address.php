<?php session_start();
/*      Copyright 2023 Flávio Ribeiro

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
require_once (__DIR__ . "/" . "../../includes/include_basics_only.php");
require_once (__DIR__ . "/" . "../../includes/classes/ConnectPDO.php");
use includes\classes\ConnectPDO;

if ($_SESSION['s_logado'] != 1 || ($_SESSION['s_nivel'] != 1)) {
    return;
}

$conn = ConnectPDO::getInstance();

$post = $_POST;
if (!isset($post['unit']) || empty($post['unit'])) {
    echo "";
    return false;
}

$addressKeys = ['addr_street', 'addr_number', 'addr_complement', 'addr_neighborhood', 'addr_cep', 'addr_city', 'addr_uf'];
$unitInfo = getUnits($conn, null, (int)$post['unit']);

$locationArray = [];
foreach ($addressKeys as $key) {
    $locationArray[] = $unitInfo[$key];
}
$address = implode(" - ", array_filter($locationArray));

// echo json_encode($data['address']);
echo $address;

return true;