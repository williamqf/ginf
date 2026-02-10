<?php
session_start();
require_once (__DIR__ . "/" . "../../includes/include_basics_only.php");
require_once (__DIR__ . "/" . "../../includes/classes/ConnectPDO.php");
use includes\classes\ConnectPDO;

if ($_SESSION['s_logado'] != 1 || ($_SESSION['s_nivel'] > 2)) {
    return;
}

$conn = ConnectPDO::getInstance();

$post = $_POST;
$data = array();

$data['is_product'] = null;
if (isset($post['is_product']) && !empty($post['is_product'])) {
    $data['is_product'] = (int)$post['is_product'];
}

$types = getAssetsTypes($conn, null, null, null, null, null, $data['is_product']);

$data = $types;


echo json_encode($data);

?>
