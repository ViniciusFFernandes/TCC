<?php
	require_once("../_BD/conecta_login.php");
	require_once("tabelas.class.php");
	//
	set_time_limit(0);
	//
	// print_r($_POST);
	// exit;
	if($_POST['operacao'] == 'atualizarChave'){
		//
		$dadosRetorno["chavePagto"] = $usuarios->busca_chave_pagto();
		//
		echo json_encode($dadosRetorno);
		exit;
	}
 ?>

