<?php

class Tabelas{

	public function geraTabelaTel($res){
    	$tabela = "<table class='table' width='100%' id='listaTelefone' style='margin-top: 3px; margin-bottom: 0px;'>";
	    foreach ($res as $reg) {
	     $tabela .= '<tr id="linhaFone_' . $reg['idpessoas_numeros'] . '">';
	     $tabela .= '<td width="50px">(' . trim($reg['pnum_DDD']) . ')</td>
	        <td>' . $reg['pnum_numero'] . '</td>
	        <td width="40px;" class="pl-0 pr-0" align="center" id="btnExcluiTelefone_' . $reg['idpessoas_numeros'] . '"><img src="../icones/excluir.png" onclick="excluirTelefone(' . $reg['idpessoas_numeros'] . ')" style="cursor:pointer;"></td>
	      </tr>';
	  	}
	  $tabela .= "</table>";
	  //
	  //escreve a tabela
	  echo $tabela;
	}

	public function geraTabelaPes($res, $db){
		$linhaColorida = false;
	    $tabela = "<div class='tableBusca' style='max-height: 350px; overflow: auto;'>";
	    //
	    if(empty($res)){
	    	$tabela .= "Nenhum registro encontrado!";
	    	$escondeTabela = "display: none;";
	    }
	    //
		$tabela .= "<table class='table' style='margin-top: 3px;{$escondeTabela}'>";
		$tabela .= "<thead>";
			$tabela .= "<tr class='bg-primary'>";
				$tabela .= "<td class='d-none d-sm-table-cell'><b>Código<b></td>";
				$tabela .= "<td><b>Nome<b></td>";
				$tabela .= "<td class='d-none d-sm-table-cell'><b>Endereço<b></td>";
				$tabela .= "<td class='d-none d-sm-table-cell'><b>Cidade<b></td>";
			$tabela .= "</tr>";
		$tabela .= "</thead>";
	    foreach ($res as $reg) {
	    	//
	    	//Monta o nome da cidade com a sigla do estado
	    	$cidadeEstado = '';
	    	if(!empty($reg['idcidades'])){
	    		$cidadeEstado = $reg['cid_nome'] . " - " . $reg['est_uf'];
	    	}
	    	//
	      $tabela .= '<tr';
				if ($linhaColorida) {$tabela .= " class='info'";}
				$tabela .= ' onclick="abreCadastro(' . $reg['idpessoas'] . ', \'pessoas_edita.php\')" style="cursor:pointer" id="linhasBusca">
	        <td width="6%" class="d-none d-sm-table-cell">' . $reg['idpessoas'] . '</td>
	        <td>' . $reg['pess_nome'] . ' <div class="row"> <div class="col-6 d-block d-sm-none"><i style="font-size: 13px;">' . $cidadeEstado . '</i></div><div class="col-6"><i style="font-size: 13px;">' . $db->retornaUmTel($reg['idpessoas']) . '</i></div></div></td>
	        <td class="d-none d-sm-table-cell">' . $reg['pess_endereco'] . '</td>
	        <td class="d-none d-sm-table-cell">' . $cidadeEstado . '</td>
	      </tr>';
			if ($linhaColorida) {
		    $linhaColorida = false;
		  }else {
		    $linhaColorida = true;
		  }
	  }
	  $tabela .= "</table></div>";
	  //
	  //escreve a tabela
	  return $tabela;
	}
	
	public function geraTabelaBusca($res, $db, $colunas, $link, $cabecalho = ''){
		$linhaColorida = false;
	    $tabela = "<div class='tableBusca' style='max-height: 250px; overflow: auto;'>";
	    if(empty($res)){
			$tabela .= "Nenhum registro encontrado!";
			$tabela .= "</div>";
			//
			//escreve a tabela
			return $tabela;
	    }
		$tabela .= "<table class='table' {$escondeTabela}' >";
		if(!empty($cabecalho)){
			$tabela .= "<thead>";
				$tabela .= "<tr class='bg-primary'>";
				foreach($cabecalho as $titulo => $config){
					$tabela .= "<td {$config}><b>{$titulo}<b></td>";
				}
				$tabela .= "</tr>";
			$tabela .= "</thead>";
		}
		foreach($res as $reg){
			//
			$primeiraLinha = true;
			foreach ($colunas as $coluna => $configuracoes) {
				if($primeiraLinha){
					$tabela .= "<tr onclick=\"abreCadastro({$reg[$coluna]}, '{$link}')\" style='cursor:pointer' id='linhasBusca'>";
					$primeiraLinha = false;
				}
				$tabela .= "<td {$configuracoes}>{$reg[$coluna]}</td>";
			}
			$tabela .- "</tr>";
		}
		$tabela .= "</table>";
		$tabela .= "</div>";
		//
	  	//escreve a tabela
		return $tabela;
	}

	public function geraTabelaPadrao($res, $db, $colunas, $cabecalho = '', $campoTotal = '', $util = '', $corTituloColuna = 'bg-primary', $tituloTabela = "", $corTitulo = 'bg-primary'){
	    if(empty($res)){
	    	$tabela .= "Nenhum registro encontrado!";
	    	$escondeTabela = "display: none;";
	    }
		//
		$BordaRedonda = "rounded-top";
		//
		$tabela .= "<table class='table text-dark border-dark' style='margin-top: 3px;{$escondeTabela}' >";
		if(!empty($tituloTabela)){
			$tabela .= "<thead>";
				$tabela .= "<tr>";
					$tabela .= "<td class='{$corTitulo} {$BordaRedonda} border-0' colspan='" . count($colunas) . "' align='center'><b>{$tituloTabela}<b></td>";
				$tabela .= "</tr>";
			$tabela .= "</thead>";
			//
			$BordaRedonda = '';
		}
		//
		if(!empty($BordaRedonda)){
			$BordaRedondaP = "rounded-top-left";
			$BordaRedondaU = "rounded-top-right";
		}
		if(!empty($cabecalho)){
			$tabela .= "<thead>";
				$tabela .= "<tr>";
				$linha = 1;
				foreach($cabecalho as $titulo => $config){
					$BordaRedonda = "";
					if($linha == 1){
						$BordaRedonda = $BordaRedondaP; 
					}elseif($linha == count($cabecalho)){
						$BordaRedonda = $BordaRedondaU;
					}
					$tabela .= "<td class='{$corTituloColuna} {$BordaRedonda} border-0' {$config}><b>{$titulo}<b></td>";
					$linha++;
				}
				$tabela .= "</tr>";
			$tabela .= "</thead>";
			//
			$BordaRedonda = "";
		}
		$total = 0;
		foreach ($res as $reg) {
			//
			$primeiraLinha = true;
			foreach ($colunas as $coluna => $tamanho) {
				if($primeiraLinha){
					$tabela .= "<tr>";
					$primeiraLinha = false;
				}
				$tabela .= "<td {$tamanho}>{$reg[$coluna]}</td>";
			}
			$tabela .= "</tr>";
			if(!empty($campoTotal)){
				$total += $util->vgr($reg[$campoTotal]);
			}
		}
		if(!empty($campoTotal)){
			$tabela .= "<tr><td colspan='" . (count($colunas) - 1) . "' align='right'><b>Total...</b></td><td align='right'><b>" . $util->formataMoeda($total) . "</b></td></tr>";
		}
		$tabela .= "</table>";
		//
	  	//escreve a tabela
		return $tabela;
	}
}
?>
