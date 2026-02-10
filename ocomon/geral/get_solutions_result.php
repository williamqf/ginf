<?php
/* Copyright 2023 Flávio Ribeiro

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
 */ session_start();

if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
	$_SESSION['session_expired'] = 1;
    echo "<script>top.window.location = '../../index.php'</script>";
	exit;
}

require_once __DIR__ . "/" . "../../includes/include_geral_new.inc.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2, 1);

$config = getConfig($conn);

$status_done = $config['conf_status_done'];
$closured_status = ($status_done <> 4 ? '4,' . $status_done : 4);

$filter_areas = "";
if (isAreasIsolated($conn) && $_SESSION['s_nivel'] != 1) {
    /* Visibilidade isolada entre áreas para usuários não admin */
    $u_areas = $_SESSION['s_uareas'];
    $filter_areas = " ar.sis_id IN ({$u_areas}) AND ";
}

$post = $_POST;
$exception = "";
$data = [];
$raw_data = [];
$clean_data = [];
$fulltext_search = true;
$array_not_having = [];

if (empty($post['problema']) || strlen(trim($post['problema'])) < 5 ) {
	echo message('warning','', TRANS('FILL_AT_LEAST_5_CHARS'), '');
	exit;
}

$chars = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜüÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿRr';
$words = array_unique(str_word_count($post['problema'], 1, $chars));


/* Descarta palavras com menos de 5 caracteres */
$words = array_values(
    array_filter(
        $words,
        function ($value) {
            return strlen((string)$value) >= 5;
        }
    )
);


$words_not_having = (isset($post['not_having']) && !empty($post['not_having'])? $post['not_having'] : '');
if (!empty($words_not_having)) {
	$array_not_having = array_unique(str_word_count($post['not_having'], 1, $chars));
}

/* Descarta palavras com menos de 5 caracteres */
$array_not_having = array_values(
    array_filter(
        $array_not_having,
        function ($value) {
            return strlen((string)$value) >= 5;
        }
    )
);

/* Adiciona o prefixo para a pesquisa de exclusão: '-' */
$array_not_having = array_map(function ($value) {
	return '-' . $value;
}, $array_not_having);


$words_not_having = (!empty($array_not_having) ? implode(' ', $array_not_having) : '');



// var_dump([
// 	'words' => $words,
// 	'array_not_having' => $array_not_having,
// 	'words_not_having' => $words_not_having
// ]); exit;


/* Ver se utilizarei na montagem do sql */
$html_words = array_map('htmlentities', $words);

$highlight_words = implode("|", $words);

// var_dump([
// 	'words' => $words,
// 	'html_words' => $html_words,
// ]);

$data['start_date'] = (isset($post['data_inicial']) && !empty($post['data_inicial']) ? dateDB($post['data_inicial']) : '');
$data['end_date'] = (isset($post['data_final']) && !empty($post['data_final']) ? dateDB($post['data_final']) : '');
$data['search_in_comments'] = (isset($post['search_in_comments']) && $post['search_in_comments'] == 'on' ? 1 : 0);
$data['search_in_progress_tickets'] = (isset($post['search_in_progress_tickets']) && $post['search_in_progress_tickets'] == 'on' ? 1 : 0);
$data['any_word'] = (isset($post['anyword']) && $post['anyword'] == 'on' ? 1 : 0);
$data['with_attachments'] = (isset($post['onlyImgs']) && $post['onlyImgs'] == 'on' ? 1 : 0);
$data['operator'] = (isset($post['operador']) && $post['operador'] != '-1' ? $post['operador'] : '');


$words_operation = ($data['any_word'] ? ' OR ' : ' AND ');
$with_attachments_from = ($data['with_attachments'] ? ', imagens i ' : '');
$with_attachments_where = ($data['with_attachments'] ? ' AND o.numero = i.img_oco ' : '');
$with_operator_where = ($data['operator'] ? ' AND u.user_id = '. $data['operator'] : '');

$date_from_sql = (!empty($data['start_date']) ? " o.data_fechamento >= '{$data['start_date']}' AND " : '');
$date_to_sql = (!empty($data['end_date']) ? " o.data_fechamento <= '{$data['end_date']}' AND " : '');

/** Se for buscar na base de assentmentos */
$words_entries_select = ' a.assentamento AS assentamento, ';
$words_entries_from = ', assentamentos a ';
$words_entries_where = 's.numero = a.ocorrencia AND ';


$statusTerm = " o.status IN ({$closured_status}) AND ";
if ($data['search_in_progress_tickets']) {
	$statusTerm = "";
}

/** Pesquina nas tabelas ocorrencias e solucoes */
$words_search_sql = "";
/** Pesquina na tabela de assentamentos */
$words_entries_sql = "";



if (!$fulltext_search) {
	foreach ($words as $word) {
		if (strlen((string)$words_search_sql))
			$words_search_sql .= $words_operation;
		
		$words_search_sql .= " ((o.descricao) LIKE ('%{$word}%') OR";
		$words_search_sql .= " (s.problema) LIKE ('%{$word}%') OR";
		$words_search_sql .= " (s.solucao) LIKE ('%{$word}%'))";
	
		if (strlen((string)$words_entries_sql))
			$words_entries_sql .= $words_operation;
	
		$words_entries_sql .= " (a.assentamento) LIKE ('%{$word}%')";
	}
	$words_entries_sql = ' OR ' . $words_entries_sql;
} else {
	

	if (!$data['any_word']) {
		$words = array_map(function ($value) {
			return '+' . $value;
		}, $words);
	}
	
	$words_in_terms = implode(' ', $words) . ' ' . $words_not_having;
	$words_search_sql = "MATCH (o.descricao) AGAINST ('{$words_in_terms}' IN BOOLEAN MODE) OR ";
	$words_search_sql .= "MATCH (s.problema, s.solucao) AGAINST ('{$words_in_terms}' IN BOOLEAN MODE)";
	
	if ($data['search_in_comments']) {
		$words_search_sql .= " OR MATCH (a.assentamento) AGAINST ('{$words_in_terms}' IN BOOLEAN MODE)";
	}
	
}


if (!$data['search_in_comments']) {
	$words_entries_select = '';
	$words_entries_from = '';
	$words_entries_where = '';
	$words_entries_sql = '';
}


if ($data['search_in_progress_tickets']) {
	$sqlBase = "SELECT 
			o.numero AS numero,
			o.descricao AS descricao,
			COALESCE(s.problema, '-') AS problema,
			-- s.problema AS problema,
			COALESCE(s.solucao, '-') AS solucao,
			-- s.solucao AS solucao,
			s.data AS data_fechamento,
			u.nome AS responsavel,
			{$words_entries_select}
			ar.sistema AS area
		FROM
			ocorrencias o 
			LEFT JOIN solucoes s ON o.numero = s.numero,
			sistemas ar, 
			usuarios u
			{$with_attachments_from}
			{$words_entries_from}
		WHERE
			{$date_from_sql} 
			{$date_to_sql}
			o.sistema = ar.sis_id AND 
			{$filter_areas} 
			{$words_entries_where}
			-- s.responsavel = u.user_id 
			u.user_id = o.operador
			{$with_operator_where}
			{$with_attachments_where} AND
			(
				{$words_search_sql}
				{$words_entries_sql}
			)
		ORDER BY
			o.numero
		LIMIT 1000
	";
} else {

	$sqlBase = "SELECT 
		o.numero AS numero,
		o.descricao AS descricao,
		s.problema AS problema,
		s.solucao AS solucao,
		s.data AS data_fechamento,
		u.nome AS responsavel,
		{$words_entries_select}
		ar.sistema AS area
	FROM
		ocorrencias o,
		sistemas ar, 
		solucoes s,
		usuarios u
		{$with_attachments_from}
		{$words_entries_from}
	WHERE
		{$date_from_sql} 
		{$date_to_sql}
		o.status IN ({$closured_status}) AND
		o.sistema = ar.sis_id AND 
		{$filter_areas} 
		o.numero = s.numero AND 
		{$words_entries_where}
		s.responsavel = u.user_id 
		{$with_operator_where}
		{$with_attachments_where} AND
		(
			{$words_search_sql}
		)
	ORDER BY
		o.numero
	LIMIT 1000
	";
}

try {
	$res = $conn->query($sqlBase);
	foreach ($res->fetchAll() as $row) {
		$raw_data[] = $row;
	}
}
catch (Exception $e) {
	$exception .= "<hr>" . $e->getMessage();
}

$clean_data = unique_multidim_array($raw_data, 'numero');
$total_result = count($clean_data);

if ($total_result > 0) {
	$message = TRANS('WERE_FOUND') . '&nbsp;<span class="bold">' . $total_result . '</span>&nbsp;' . TRANS('POSSIBLE_RECORDS_ACORDING_TO_CRITERIA');
	?>
	<div class="row">
		<div class="col-12">
			<?= message('success', '', $message, '', '', 1); ?>
		</div>
	</div>
	<table id="table_solutions" class="stripe hover order-column row-border" border="0" cellspacing="0" width="100%">
		<thead>
			<tr class="header">
				<th class='line'><?= TRANS('NUMBER_ABBREVIATE'); ?></th>
				<th class='line'><?= TRANS('AREA'); ?></th>
				<th class='line descricao'><?= TRANS('DESCRIPTION'); ?></th>
				<th class='line descricao_tech'><?= TRANS('TXT_DESC_TEC_PROB'); ?></th>
				<th class='line solucao'><?= TRANS('SOLUTION'); ?></th>
				<th class='line'><?= TRANS('OCO_RESP'); ?></th>
				<th class='line'><?= TRANS('DONE_DATE'); ?></th>
			</tr>
		</thead>
	<?php

	foreach ($clean_data as $row) {
		?>
			<tr>
				<td class="line"><span class="pointer" onClick="openTicketInfo('<?= $row['numero']; ?>')"><?= "<b>" . $row['numero'] . "</b>"; ?></span></td>
				<td class="line"><?= $row['area']; ?></td>
				<td class="line"><?= destaca($highlight_words, $row['descricao']); ?></td>
				<td class="line"><?= destaca($highlight_words, $row['problema']); ?></td>
				<td class="line"><?= destaca($highlight_words, $row['solucao']); ?></td>
				<td class="line"><?= $row['responsavel']; ?></td>
				<td class="line" data-sort="<?= $row['data_fechamento']; ?>"><?= dateScreen($row['data_fechamento']); ?></td>
			</tr>
		<?php
	}
	?>
		</table>
<?php
} else {
	?>
		<div class="row"> 
			<div class="col-12">
				<?= message('warning', 'Ooops!', TRANS('MSG_THIS_CONS_NOT_RESULT'), '', '', 1); ?>
			</div>
		</div>
	<?php
}
