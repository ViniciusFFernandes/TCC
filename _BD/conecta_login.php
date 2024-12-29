<?php
// Se o "Permanecer logado" estiver marcado
if (isset($_POST['permanece_logado'])) {
	// Define a duração do cookie para 30 dias (2592000 segundos)
	session_set_cookie_params(2592000, '/'); // 30 dias
	ini_set('session.gc_maxlifetime', 2592000); // 30 dias
} else {
	// Caso contrário, mantém o comportamento padrão (sessão expira no fechamento do navegador)
	session_set_cookie_params(0, '/');
	ini_set('session.gc_maxlifetime', 1440); // Expira após 24 minutos de inatividade, por exemplo
}
//
session_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
date_default_timezone_set('America/Sao_Paulo');
if(!realpath("../privado/constantes.vf")){
	header("Location: ../privado/_Constante/criaConstante_login.html");
	exit;
}
// print_r($_REQUEST);exit;
require_once("../set_path.php");
require_once("DB.class.php");
require_once("util.class.php");
require_once("logs.class.php");
require_once("parametros.class.php");
require_once("atualizacao.class.php");
require_once("tarefas_diarias.class.php");
require_once("usuarios.class.php");
require_once("html.class.php");
require_once("constantes.vf");
//
//Se não existe define como null para evitar avisos de erro
if (!isset($_POST['operacao'])) {
	$_POST['operacao'] = null;
}
if (!isset($_SESSION['logado'])) {
	$_SESSION['logado'] = null;
}
//
//inicia as classes nescessarias
$util = new Util();
$db = new Db($SERVIDOR, $PORTA, $USUARIO, $SENHA, $DB_NAME);
$log = new log($db);
$parametros = new Parametros($db);
$html = new html($db);
$atualizacao = new Atualizacao($db);
//$chat = new Chat();
//
//Conecta com o banco de dados
$db->conectar();
//
//inicio das operações
//
//Efetua o login
if ($_POST['operacao'] == "logar") {
	$db->setTabela("pessoas", "idpessoas");
	$resultado = false; 
	$dados = $db->buscarUsuario($_POST['usuario']);
	if($dados['idpessoas'] > 0 && $_POST['senha'] == $dados['pess_senha']){
		//
		// 25/11/2022 Vinicius
		// Removido pois agora o tarefas diarias será executado todos os dias uma vez por dia por agendamento de tarefas no servidor
		//
		//Executa tarefas diarias no primeiro login bem sucedido do dia
		//$tarefasDiarias = new Tarefas_Diarias($parametros, $db, $util, $atualizacao);
		//$tarefasDiarias->executa_tarefas();
		//
		$_SESSION['logado'] 						= true;
		$_SESSION['user'] 							= $_POST['usuario'];
		$_SESSION['idusuario']				 	    = $dados['idpessoas'];
		$_SESSION['idgrupos_acessos']				= $dados['pess_idgrupos_acessos'];
	    $_SESSION['ultima_atividade'] 				= time();
	    $_SESSION['permanece_logado'] 				= $_POST['permanece_logado'];
	    $_SESSION['tamanho_tela'] 					= $_POST['tamanhoTela'];
		header('Location: ../_Inicio/inicio.php');
		exit;
	}else{
		$_SESSION['logado'] = false;
		$_SESSION['mensagem'] = "Usuario ou senha incorretos!!!<br>Tente novamente";
    	$_SESSION['tipoMsg'] = "danger";
		header('location:../index.php');
		exit;
	}
}

//
//Operação para deslogar do sistema
if ($_POST['operacao'] == "Sair") {
	session_destroy();
	header('Location: ../index.php');
	exit;
}

if($IGNORA_SESSAO != "SIM"){
	//
	//Caso tente acessar as paginas pela url e nao esteja logado
	if (!$_SESSION['logado']) {
		$html->mostraErro("Você não esta logado.<br>Para continuar é necessário que faça o login!", "../index.php");
		exit;
	}

	if (((time() - $_SESSION['ultima_atividade']) > 1800) && ($_SESSION['permanece_logado'] != 'SIM')) {
		// última atividade foi mais de 10 minutos atrás
		session_unset();     // unset $_SESSION
		session_destroy();   // destroindo session data
			//
		header('Location: ../index.php');
		exit;
	}

	$_SESSION['ultima_atividade'] = time(); // update ultima ativ.
	//
	//Verifica se o ususario pode acessar a pagina atual
	$usuarios = new Usuarios($db, $_SESSION['idusuario'], $_SESSION['idgrupos_acessos']);
	if(!$usuarios->usuario_pode_executar()){
		$html->mostraErro("Você não tem permissão para executar este programa!<br>Consulte um administrador do sistema!<br> Programa: " . basename($_SERVER['PHP_SELF']));
		exit;
	}
	if(SISTEMA_SAC != "SIM"){
		//
		//Limpa a mensagem sobre chave de pagamento
		//
		unset($_SESSION["mensagemChavePagto"]);
		//
		//Valida a chave de pagamento do sistema
		//
		$chavePagto = $parametros->buscaValor("sistema: chave de pagamento");
		if($chavePagto == ""){
			$chavePagto = $usuarios->busca_chave_pagto();
		}
		//
		if($chavePagto != ""){
			//
			$dadosChave = $usuarios->le_chave_pagto($chavePagto);
			//
			if (strlen($chavePagto) < 44) {
				$html->mostraErro("Chave de pagamento invalida!<br>Consulta um administrador do sistema para mais informações.", "../index.php");
				exit;
			}
			//
			// Validações se o cliente não for VIP
			if ($dadosChave["vipPrefix"] !== "VIP") {
				// Valida se o código do cliente é igual ao COD_CLIENTE_SAC
				if ($dadosChave["codigoCliente"] !== str_pad(COD_CLIENTE_SAC, 10, '0', STR_PAD_LEFT)) {
					$html->mostraErro("Chave de pagamento invalida!<br>Consulta um administrador do sistema para mais informações.", "../index.php");
					exit;
				}
				//
				// Verifica se a data de vencimento está vencida
				if ($dadosChave["dataVencimentoTimestamp"] < $dadosChave["dataAtualTimestamp"]) {
					//
					if ($dadosChave["diferencaDias"] <= 30) {
						$_SESSION["mensagemChavePagto"] = "Atenção: Sua chave de pagamento está vencida a " . intval($dadosChave["diferencaDias"]) . " dias.";
					}else{
						//
						//Busca uma chave atualizar para verificar se já possui uma nova chave
						$chavePagto = $usuarios->busca_chave_pagto();
						$dadosChave = $usuarios->le_chave_pagto($chavePagto);
						if($dadosChave["diferencaDias"] > 30){
							$html->mostraErro("Chave de pagamento vencida a mais de 30 dias!<br>Consulta um administrador do sistema para mais informações.", "../index.php");
							exit;
						}
					}
				}
			}
		}else{
			$_SESSION["mensagemChavePagto"] = "Atenção: Chave de pagamento não localizada.<br>Contate um administrador do sistema.";
		}
	}
	//
}
?>
