<?php
	require_once("../_BD/conecta_logado.php");
	require_once("tabelas.class.php");
	//
	set_time_limit(0);
	//
	// 
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		if($_POST["operacao"] == 'getChavePagto'){
			//
			$sql = "SELECT * 
					FROM pessoas 
					WHERE pess_cod_cliente = "  . $util->sgr($_POST["cod_cliente"]);
			//
			$reg = $db->retornaUmReg($sql);
			//
			echo json_encode(['chave_pagamento' => $reg["pess_chave_pagto"]]);
		}
	}
	
 ?>

