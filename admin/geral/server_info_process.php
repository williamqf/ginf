<?php
session_start();
require_once (__DIR__ . "/" . "../../includes/include_basics_only.php");
require_once (__DIR__ . "/" . "../../includes/classes/ConnectPDO.php");
use includes\classes\ConnectPDO;

if ($_SESSION['s_logado'] != 1 || $_SESSION['s_nivel'] != 1) {
    exit;
}

$conn = ConnectPDO::getInstance();
$data = [];


$serverName = rtrim($_SERVER['SERVER_NAME'], "/");
$uri = ltrim($_SERVER['REQUEST_URI'], "/");
$serverAndUri = $serverName . "/" . $uri;

$serverSoftware = $_SERVER['SERVER_SOFTWARE'];
$serverAddr = $_SERVER['SERVER_ADDR'];

$os = php_uname(); //PHP_OS;
$php_version = PHP_VERSION; // phpversion();

/* Versão do banco de dados */
$sql = "SELECT version() as version";
$result = $conn->query($sql);
$row = $result->fetch();
$db_version = $row['version'];

/* Checkpoint do banco de dados para o OcoMon */
$app_db_checkpoint = getConfigValue($conn, 'DB_CHECKPOINT');


/* Módulos PHP */
$php_required_modules = [];
$php_required_modules[] = 'curl';
$php_required_modules[] = 'fileinfo';
$php_required_modules[] = 'gd';
$php_required_modules[] = 'iconv';
$php_required_modules[] = 'imap';
$php_required_modules[] = 'ldap';
$php_required_modules[] = 'mbstring';
$php_required_modules[] = 'openssl';
$php_required_modules[] = 'pdo';
$php_required_modules[] = 'pdo_mysql';
$php_required_modules[] = 'xml';

$php_required_modules = array_map('strtolower', $php_required_modules);

$php_missing_modules = [];

foreach ($php_required_modules as $moduleName) {
    if (!extension_loaded($moduleName)) {
        $php_missing_modules[] = $moduleName;
    }
}


$data['serverName'] = $serverName;
$data['uri'] = $uri;
$data['serverAndUri'] = $serverAndUri;
$data['serverSoftware'] = $serverSoftware;
$data['serverAddr'] = $serverAddr;
$data['os'] = $os;
$data['php_version'] = $php_version;
$data['db_version'] = $db_version;
$data['app_db_checkpoint'] = $app_db_checkpoint;
$data['php_required_modules'] = $php_required_modules;
$data['php_missing_modules'] = $php_missing_modules;


echo json_encode($data);
return true;
?>