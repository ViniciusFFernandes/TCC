<?php

class Util{

	public function sgr($string){
		if ($string != "") {
          $string = "'" . $string . "'";
        }else {
          return "NULL";
        }
        return $string;
	}
	
	public function pgr($string){
		if ($string != "") {
          $string = "(" . $string . ")";
        }else {
          return "NULL";
        }
        return $string;
	}

	public function dgr($string, $incluiHora = ""){
		if ($string != "") {
					$string = explode(" ", $string);
					$data = explode("/", $string[0]);
					$dataFormatada = $data[2] . "-" . $data[1] . "-" . $data[0];
					if (!empty($incluiHora)) {
							$dataFormatada .= " " . $incluiHora;
					}elseif (!empty($string[1])) {
							$dataFormatada .= " " . $string[1];				
					}else{
						$dataFormatada .= " 00:00:00";		
					}

				return "'" . $dataFormatada . "'";
		}
	}

	public function convertData($string){
		if ($string != "") {
					$string = explode(" ", $string);
					$data = explode("-", $string[0]);
					$dataFormatada = $data[2] . "/" . $data[1] . "/" . $data[0];
					if (!empty($string[1])) {
						$dataFormatada .= " " . $string[1];
					}
				}else {
					return "NULL";
				}
				return $dataFormatada;
	}

	public function mostraErro($mensagem, $link = null){
		echo '
	    <link rel="stylesheet" href="../css/bootstrap.min.css">
		<div class="row">
        	<div class="col-md-4 col-sm-1"></div>
          	<div class="col-md-4 col-sm-10">
	    		<div class="panel panel-default">
	      			<div class="panel-heading">Ops! Tivemos algum probleminha.</div>
	      			<div class="panel-body">
	      	 			<span style="color: red;">Mas não se desespere!</span><br>
	        			ERRO: <br>' . $mensagem . '
	      			</div>
	      			<div class="panel-footer">' ;
	    if ($link != "") {
	    	echo '<button class="btn btn-danger btn-lg" onclick="window.location.replace(\'' . $link . '\');">Voltar</button>';
	    }else{
	     	echo '<button class="btn btn-danger btn-lg" onclick="window.history.back();">Voltar</button>';
	    }
	    echo	'</div>
		   		 	</div>
		   		</div>
		   		<div class="col-md-4 col-sm-1"></div>
		   	</div>
		';
	}

	public function mostraMensagem($tipo, $mensagem, $id = ''){
		$msg = '
		<div class="alert alert-' . $tipo . ' alert-dismissible" role="alert">
			<button type="button" id="botao_alerta" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<center>' . $mensagem;
		if (!empty($id)) {
			$msg .= '(' . $id . ')';
		}
		$msg .= '</center> </div>';
		return $msg;
	}

	public function defineChecked($string){
		if($string == "SIM"){
			return "checked";
		}else{
			return "";
		}
	}

	public function geraTabelaTel($res){
		$linhaColorida = false;
    	echo "<table class='table' width='100%' style='margin-top: 3px;'>";
	    foreach ($res as $reg) {
	     echo '<tr ';
			 if ($linhaColorida) echo "class='info'";
			 echo '>';
	     echo '<td width="80px">(' . $reg['pnum_DDD'] . ')</td>
	        <td>' . $reg['pnum_numero'] . '</td>
	        <td width="20px;" align="left"><img src="../icones/excluir.png" onclick="excluirTelefone(' . $reg['idpessoas_numeros'] . ')" style="cursor:pointer;"></td>
	      </tr>';
	 if ($linhaColorida) {
		    $linhaColorida = false;
		  }else {
		    $linhaColorida = true;
		  }
	  	}
	  echo "</table>";
	}

	public function geraTabelaPes($res, $db){
		 $linhaColorida = false;
	    echo "<div style='max-height: 250px; overflow: auto;'><table class='table' style='margin-top: 3px'>";
	    //echo $res;
	    foreach ($res as $reg) {
	      echo '<tr';
				if ($linhaColorida) {echo " class='info'";}
				echo ' onclick="abrePessoa(' . $reg['idpessoas'] . ')" style="cursor:pointer" id="linhasPessoas">
	        <td width="6%">' . $reg['idpessoas'] . '</td>
	        <td width="30%">' . $reg['pess_nome'] . '</td>
	        <td width="28%">' . $db->retornaUmTel($reg['idpessoas']) . '</td>
	        <td>' . $reg['pess_endereco'] . '</td>
	        <td>' . $reg['pess_cidade'] . '</td>
	      </tr>';
			if ($linhaColorida) {
		    $linhaColorida = false;
		  }else {
		    $linhaColorida = true;
		  }
	  }
	  echo "</table></div>";
	}

	public function comboboxSql($campoMostra, $campoValue, $where, $db, $tabela){
		$db->setTabela($tabela);
		$res = $db->consultar($where);
		echo '<select class="form-control" name="' . $campoValue . '" id="' . $campoValue . '" >';
		echo '<option value="0" selected="selected">-----------</option>';
		foreach ($res as $reg) {
			echo '<option value="' . $reg[$campoValue] . '">' . $reg[$campoMostra] . '</option>';
		}
		echo '</select>';
	}

	public function buscaHtml($btnMenu = ''){
		$menu = file_get_contents('../menu.html');
		//
		if ($btnMenu == "inicio"){
			$busca = '##inicio##';
		}elseif($btnMenu == "cadastros"){
			$busca = '##cadastros##';
		}elseif($btnMenu == "lancamentos"){
			$busca = '##lancamentos##';
		}elseif($btnMenu == "relatorios"){
			$busca = '##relatorios##';
		}elseif($btnMenu == "quemsomos"){
			$busca = '##QuemSomos##';
		}
		$menu = str_replace($busca, 'class="active"', $menu);
		$menu = str_replace("##NomeUsuario##", $_SESSION['user'], $menu);
		//
		$nome = explode(".", basename($_SERVER['PHP_SELF']));
		$nomeArquivo = $nome[0] . ".html";
		$html = file_get_contents("_HTML/" . $nomeArquivo);
		$html = str_replace("##Menu##", $menu, $html);
		//
		return $html;
	}
}
?>
