<?php
	require_once("util.class.php");
	class html{
		private $db;
		private $util;

		function __construct($db){
			$this->db = $db;
			$this->util = new Util();
		}

		public function criaSelectSql($campoMostra, $campoValue, $nameInput, $valueReg, $sql, $class = "", $js = "", $geraZero = true, $valorPadraoZero = '---', $escondeOpcaoZero = true){
			//
			$res = $this->db->consultar($sql);
			//
			$comboBox = "<select name='{$nameInput}' id='{$nameInput}' class='{$class}' {$js}>";
				if($valueReg == '' || $valueReg == 0) $selected  = "selected='selected'";
				if($escondeOpcaoZero) $hide = "disabled hidden";
				if($geraZero) $comboBox .= "<option value='' {$hide} {$selected}>{$valorPadraoZero}</option>";
				foreach ($res as $reg) {
					$selected = "";
					if($reg[$campoValue] == $valueReg) $selected  = "selected='selected'";
					$comboBox .= "<option value='{$reg[$campoValue]}' {$selected}>{$reg[$campoMostra]}</option>";
				}
			$comboBox .= "</select>";
			//
			return $comboBox;
		}

		public function mostraErro($mensagem, $link = null, $fechaAba = false){
			echo '
			<!DOCTYPE html>
			<html lang="pt">
			<head>
				<meta charset="utf-8">
				<meta http-equiv="X-UA-Compatible" content="IE=edge">
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<title>' . NOME_SISTEMA . '</title>
			</head>
			<body>
				<link rel="stylesheet" href="../css/padrao.css">
				<div class="row">
					<div class="col-md-4 col-sm-1 col-1"></div>
					<div class="col-md-4 col-sm-10 col-10 mt-3">
						<div class="card shadow mb-4 border-left-danger">
							<div class="card-header py-3"> 
								<h6 class="m-0 font-weight-bold text-primary">Ops! Parece que algo deu errado.</h6>
							</div>	  
							<div class="card-body">
								<span class="font-weight-bold text-danger">Mas não se desespere!</span><br>
								ERRO: ' . $mensagem . '
							</div>
							<div class="card-footer">' ;
			if ($link != "") {
				echo '<button class="btn btn-danger btn-lg" onclick="window.location.replace(\'' . $link . '\');">Voltar</button>';
			}elseif($fechaAba){
				echo '<button class="btn btn-danger btn-lg" onclick="window.close();">Fechar</button>';
			}else{
				echo '<button class="btn btn-danger btn-lg" onclick="window.history.back();">Voltar</button>';
			}
			echo	'</div>
						</div>
					</div>
					<div class="col-md-4 col-sm-1 col-1"></div>
				</div>
				</body>
			</html>
			';
		}

		public function mostraMensagem($tipo, $mensagem, $id = ''){
			$msg = '
			<div class="alert alert-' . $tipo . ' alert-dismissible" role="alert" style="margin: 10px auto; box-shadow: 1px 1px 5px black;padding: 12px 20px;text-align: center;">
				<button type="button" id="botao_alerta" class="close closeMsg" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				' . $mensagem;
			if (!empty($id)) {
				$msg .= '(' . $id . ')';
			}
			$msg .= '</div>';
			return $msg;
		}

		public function defineChecked($string){
			if($string == "SIM"){
				return "checked";
			}else{
				return "";
			}
		}

		public function defineSelected($valorPadrao, $valor){
			if($valorPadrao == $valor){
				return 'selected="selected"';
			}else{
				return "";
			}
		}

		public function buscaHtml($gerarMenu = true, $modelo = ""){
			$menu = file_get_contents('../menu.html');
			$topBar = file_get_contents('../topBar.html');
			//
			$topBar = str_replace("##NomeUsuario##", $_SESSION['user'], $topBar);
			//
			$includes = file_get_contents('../includes.html');
			$includesRelatorios = file_get_contents('../includesRelatorios.html');
			//
			//
			if($gerarMenu) $opcoesMenu = $this->geraMenu($_SESSION['idgrupos_acessos']);
			//
			//
			$toggled = "";
			if($_SESSION['tamanho_tela'] < 992){
				$toggled = "toggled";
			}
			//
			$menu = str_replace($busca, 'class="active"', $menu);
			$menu = str_replace("##opcoesConfig##", $opcoes_config, $menu);
			$menu = str_replace("##imgLogo##", LOGO_EMPRESA, $menu);
			$menu = str_replace("##toggled##", $toggled, $menu);
			//
			$programaPadrao = basename($_SERVER['PHP_SELF']);
			if($modelo != ""){
				$programaPadrao = $modelo;
			}
			$nome = explode(".", $programaPadrao);
			$nomeArquivo = $nome[0] . ".html";
			$html = file_get_contents("_HTML/" . $nomeArquivo);
			$html = str_replace("##Menu##", $menu, $html);
			$html = str_replace("##topBar##", $topBar, $html);
			$html = str_replace("##opcoesMenuGrupo##", $opcoesMenu, $html);
			$html = str_replace("##includes##", $includes, $html);
			$html = str_replace("##includesRelatorios##", $includesRelatorios, $html);
			$html = str_replace("##nomeSistema##", NOME_SISTEMA, $html);
			//
			return $html;
		}

		public function criaMenu($idgrupos_acessos = '', $tipo_menu = ''){

			$sql = "SELECT * 
					FROM grupos_acessos_programas 
						JOIN programas ON (gap_idprogramas = idprogramas)
					WHERE prog_tipo = 'menuRaiz'";
			if(!empty($tipo_menu)){
				$sql .= " AND prog_tipo_menu = " . $this->util->sgr($tipo_menu);
			} 
			if(!empty($idgrupos_acessos)){
				$sql .= " AND gap_idgrupos_acessos = " . $this->util->igr($idgrupos_acessos);
			} 
			$sql .= " ORDER BY prog_posicao";
			$resMenu = $this->db->consultar($sql);
			//
			$menu = '';
			//
			foreach($resMenu as $regMenu){
				//
				//Se não puder ver no menu, não vai carregar as opçoes (ainda acessivel pela URL)
				//
				if($regMenu['gap_executa'] <> 1) continue;
				//
				if($regMenu['prog_tipo_menu'] == ''){
					$menu .= '<li class="nav-item">
										<a class="nav-link" href="#" onclick="direciona(\'../' . $regMenu['prog_raiz'] . '/' . $regMenu['prog_file'] . '\')">
											' . $regMenu['prog_imagem'] . '
											<span>' . $regMenu['prog_nome'] . '</span>
										</a>
									</li>';
				}else{
					$sql = "SELECT * 
							FROM grupos_acessos_programas 
								JOIN programas ON (gap_idprogramas = idprogramas)
							WHERE prog_tipo = 'menu'
								AND prog_tipo_menu = " . $this->util->sgr($regMenu['prog_tipo_menu']);
					if(!empty($idgrupos_acessos)){
						$sql .= " AND gap_idgrupos_acessos = " . $this->util->igr($idgrupos_acessos);
					} 
					$sql .= " ORDER BY gap_idgrupos_acessos, prog_tipo_menu, prog_nome";
					$res = $this->db->consultar($sql);
					//
					$ultimoTipo = '###';
					$primeiro = true;
					$qteColunas = 0;
					foreach($res as $reg){
						if($ultimoTipo != $reg['prog_tipo_menu']){
							if(!$primeiro && $ultimoTipo != '###'){
								$menu .= '		</div>
											</div>
										</li>';
							}
							$ultimoTipo = $reg['prog_tipo_menu'];
							$idgrupos_acessos = $reg['gap_idgrupos_acessos'];
							$qteColunas = 0;
							$menu .= ' <li class="nav-item">
										<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapse_' . $regMenu['prog_tipo_menu'] . '"
											aria-expanded="true" aria-controls="collapse_' . $regMenu['prog_tipo_menu'] . '">
											' . $regMenu['prog_imagem'] . '
											<span>' . $regMenu['prog_nome'] . '</span>
										</a>
										<div id="collapse_' . $regMenu['prog_tipo_menu'] . '" class="collapse" aria-labelledby="headingUtilities"
											data-parent="#accordionSidebar">
											<div class="bg-white py-2 collapse-inner rounded">';
						}

						if($reg['gap_executa'] == 1 || $reg['gap_idgrupos_acessos'] == 1){
								$menu .= '<a class="collapse-item" href="#" onclick="direciona(\'../' . $reg['prog_raiz'] . '/' . $reg['prog_file'] . '\')">' . $reg['prog_nome'] . '</a>';
						}
						$primeiro = false;
					}
					if($ultimoTipo != '###'){
						$menu .= '		</div>
									</div>
								</li>';
					}
				}
			}
			$sql = "UPDATE grupos_acessos SET grac_menu = " . $this->util->sgr($menu) . " WHERE idgrupos_acessos = " . $idgrupos_acessos;
			$this->db->executaSQL($sql);
			if($this->db->erro()){
				return false;
			}
			return true;
		}

		public function geraMenu($idgrupos_acessos){
			$sql = "SELECT grac_menu AS menu FROM grupos_acessos WHERE idgrupos_acessos = " . $idgrupos_acessos;
			$menu = $this->db->retornaUmCampoSql($sql, "menu");
			if(is_null($menu)){
				if($this->criaMenu($idgrupos_acessos)){
					$this->geraMenu($idgrupos_acessos);
				}
			}else{
				if(empty($menu)){
					return "<center><b style='color: #ac2925;'>Permissão negada!</b><br>Contate um administrador do sistema.</center>";
				}else{
					return $menu;
				}
				
			}	
		}
	}
?>
