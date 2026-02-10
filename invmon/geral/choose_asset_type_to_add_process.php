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

$erro = false;
$screenNotification = "";
$exception = "";
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['cod'] = (isset($post['cod']) ? intval($post['cod']) : "");

/* Quantidade máxima de registros por vez no cadastro em lote. Criar uma opção de configuração no painel de administração */
$maxAmountEachTime = getConfigValue($conn, 'MAX_AMOUNT_BATCH_ASSETS_RECORD');

if (!isset($maxAmountEachTime)) {
    setConfigValue($conn, 'MAX_AMOUNT_BATCH_ASSETS_RECORD', 1);
    $maxAmountEachTime = 1;
}

$data['field_id'] = "";

/* Ajuste para nao permitir o cadastro de mais um recurso do mesmo tipo+fabricamente+modelo */
$data['is_resource'] = (isset($post['is_product']) && $post['is_product'] != 0 ? (int)$post['is_product'] : 0);

$data['asset_type'] = (isset($post['asset_type']) ? noHtml($post['asset_type']) : "");
$data['asset_manufacturer'] = (isset($post['asset_manufacturer']) ? noHtml($post['asset_manufacturer']) : "");
$data['asset_model'] = (isset($post['asset_model']) ? noHtml($post['asset_model']) : "");

/* Validar se o asset_amount é um número */
if (!isset($post['asset_amount']) || empty($post['asset_amount']) || !filter_var($post['asset_amount'], FILTER_VALIDATE_INT) || $post['asset_amount'] <= 0) {
    $data['success'] = false; 
    $data['field_id'] = 'asset_amount';
    $data['message'] = message('warning', 'Ooops!', TRANS('MSG_ERROR_WRONG_VALUE'),'');
    echo json_encode($data);
    return false;
}

$data['asset_amount'] = (isset($post['asset_amount']) && $post['asset_amount'] > 0 ? (int)$post['asset_amount'] : 1);
//$data['asset_amount'] = ($data['asset_amount'] > $maxAmountEachTime ? $maxAmountEachTime : $data['asset_amount']);


$data['parent_id'] = (isset($post['parent_id']) ? noHtml($post['parent_id']) : "");
$data['load_saved_config'] = (isset($post['load_saved_config']) ? noHtml($post['load_saved_config']) : 0);
$data['profile_id'] = "";

$data['category_profile_id'] = "";

/* Validações */
if (empty($data['asset_type']) || empty($data['asset_manufacturer']) || empty($data['asset_model'])) {
    $data['success'] = false; 
    $data['field_id'] = (empty($data['asset_type']) ? "asset_type" : (empty($data['asset_manufacturer']) ? "asset_manufacturer" : "asset_model"));
    $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
    echo json_encode($data);
    return false;
}

if ($data['asset_amount'] > $maxAmountEachTime) {
    $data['success'] = false; 
    $data['field_id'] = "asset_amount";
    $data['message'] = message('warning', 'Ooops!', TRANS('ERR_MAX_AMOUNT_EACH_ASSET_BATCH') . '<hr />' . TRANS('CURRENT_MAX_AMOUNT_SETTED') . '&nbsp;' . $maxAmountEachTime, '', '');
    echo json_encode($data);
    return false;
}

if ($data['is_resource']) {
    /* Checar se já existe algum ativo do tipo recurso com os mesmos dados já cadastrados (tipo+fabricante+modelo) */
    $sql = "SELECT 
                e.comp_cod
            FROM 
                equipamentos e,
                marcas_comp mc,
                fabricantes f,
                tipo_equip t
                    LEFT JOIN assets_categories cat ON cat.id = t.tipo_categoria 
            WHERE
                e.comp_tipo_equip = t.tipo_cod AND
                e.comp_marca = mc.marc_cod AND
                e.comp_fab = f.fab_cod AND 
                -- cat.cat_is_product = 1 AND
                e.is_product = 1 AND
                t.tipo_cod = :tipo_cod AND
                mc.marc_cod = :marc_cod AND
                f.fab_cod = :fab_cod
            ";
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":tipo_cod", $data['asset_type']);
        $stmt->bindParam(":marc_cod", $data['asset_model']);
        $stmt->bindParam(":fab_cod", $data['asset_manufacturer']);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $data['success'] = false; 
            $data['field_id'] = "asset_model";
            $data['message'] = message('warning', 'Ooops!', TRANS('MSG_RESOURCE_ALREADY_EXISTS'),'');
            echo json_encode($data);
            return false;
        }
    } catch (PDOException $e) {
        $data['success'] = false; 
        $data['field_id'] = "asset_model";
        $data['message'] = message('danger', 'Ooops!', $e->getMessage(),'');
        echo json_encode($data);
        return false;
    }
}





/* Traz as informações relacionadas ao tipo: categoria|perfil de cadastro|etc */
$asset_type = getAssetsTypes($conn, $data['asset_type']);

$data['profile_id'] = $asset_type['profile_id'];
$data['category_id'] = $asset_type['id'];
// $data['is_product'] = ($asset_type['cat_is_product'] == 1 ? 1 : 0);
// $data['is_product'] = ($asset_type['can_be_product'] == 1 ? 1 : 0);
$data['is_product'] = $data['is_resource'];


if (empty($data['profile_id'])) {
    /* Se o tipo de ativo não tiver perfil de cadastro associado então utilizo o perfil de cadastro da categoria do ativo - se tiver perfil */
    $categorie = getAssetsCategories($conn, $data['category_id']);

    if (!empty($categorie)) {
        $data['profile_id'] = (array_key_exists('cat_default_profile', $categorie) ? $categorie['cat_default_profile'] : "");
    }
}

if (empty($data['profile_id'])) {
    /* Não tem perfil associado ao tipo de ativo e nem perfil associado à categoria do ativo */
    $data['profile_id'] = 0;
}

echo json_encode($data);
