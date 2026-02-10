<?php
session_start();
require_once (__DIR__ . "/" . "../../includes/include_basics_only.php");
require_once (__DIR__ . "/" . "../../includes/classes/ConnectPDO.php");


use includes\classes\ConnectPDO;

if ($_SESSION['s_logado'] != 1 || ($_SESSION['s_nivel'] > 2)) {
    exit;
}
$conn = ConnectPDO::getInstance();
$exception = "";

$post = $_POST;

$client = (isset($post['client']) && !empty($post['client']) ? $post['client'] : "");

$terms = "";
$terms .= (!empty($client) ? " AND u.inst_client = {$client}" : "");



/* Controle de visão em função das configurações da área da qual o usuário pertence */
if (!empty($_SESSION['s_allowed_units'])) {
    $terms .= " AND u.inst_cod IN ({$_SESSION['s_allowed_units']}) ";
}
if (!empty($_SESSION['s_allowed_clients'])) {
    $terms .= " AND u.inst_client IN ({$_SESSION['s_allowed_clients']}) ";
}



$sql = "SELECT 
            e.comp_cod, cl.nickname AS client, u.inst_nome AS unit, e.comp_inv AS tag,
            e.comp_coment AS comment, e.comp_data_compra AS purchase_date,
            l.local AS department, e.comp_sn AS serial_number, e.comp_nf AS invoice_number, 
            t.tipo_nome AS equipment_type, f.fab_nome as manufacturer, m.marc_nome as model, 
            fo.forn_nome AS supplier,  
            date_add(date_format(e.comp_data_compra, '%Y-%m-%d'), INTERVAL warranty_time.tempo_meses MONTH) AS expiring_date  

        FROM  
            equipamentos e
                LEFT JOIN fornecedores fo on fo.forn_cod = e.comp_fornecedor,
            fabricantes f,  
            tipo_equip t, marcas_comp m, 
            instituicao u
                LEFT JOIN clients cl ON cl.id = u.inst_client,
            localizacao l, 
            tempo_garantia warranty_time  
        WHERE  
            f.fab_cod = e.comp_fab AND
            e.comp_inst = u.inst_cod AND 
            e.comp_local = l.loc_id AND 
            e.comp_garant_meses = warranty_time.tempo_cod AND 
            e.comp_tipo_equip = t.tipo_cod AND 
            e.comp_marca = m.marc_cod AND
            date_add(e.comp_data_compra, INTERVAL warranty_time.tempo_meses MONTH) >= :start AND
            date_add(e.comp_data_compra, INTERVAL warranty_time.tempo_meses MONTH) <= :end 
            {$terms}

        ORDER BY 
            expiring_date, model
    ";

try {
    $res = $conn->prepare($sql);
    $res->bindParam(':start', $post['start']);
    $res->bindParam(':end', $post['end']);
    
    $res->execute();
}
catch (Exception $e) {
    $exception .= "<hr>" . $e->getMessage();
    echo $exception;
    return;
}



$data = array();

$i = 0;
foreach ($res->fetchAll() AS $row) {

    // if (!empty($worker_id)) {
    //     $data[$i]['textColor'] = $row['mainworker_textcolor'];
    //     $data[$i]['borderColor'] = 'white';
    //     $data[$i]['backgroundColor'] = $row['mainworker_bgcolor'];
    // } else {
    //     $data[$i]['textColor'] = $post['color'];
    //     $data[$i]['borderColor'] = $post['borderColor'];
    //     $data[$i]['backgroundColor'] = $post['bgColor'];
    // }
    
    $data[$i]['id'] = $row['comp_cod'];
    $data[$i]['asset_type'] = $row['equipment_type'] . ' - ' . $row['manufacturer'] . ' - ' . $row['model'];
    // $data[$i]['title'] = $row['equipment_type'];
    $data[$i]['title'] = $data[$i]['asset_type'];
    $data[$i]['client'] = $row['client'] ?? '';
    $data[$i]['unit'] = $row['unit'];
    $data[$i]['tag'] = $row['tag'];
    $data[$i]['serial_number'] = $row['serial_number'] ?? '';
    $data[$i]['purchase_date'] = dateScreen($row['purchase_date'], 1) ?? '';
    $data[$i]['expiring_date'] = dateScreen($row['expiring_date'], 1);
    $data[$i]['comment'] = $row['comment'];
    
    
    $data[$i]['start'] = $row['expiring_date'];
    $data[$i]['departamento'] = $row['department'];
    // $data[$i]['start_date'] = $post['start'];

    $i++;
}

echo json_encode($data);

?>
