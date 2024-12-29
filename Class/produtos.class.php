<?php
require_once("util.class.php");

class produtos{
	private $db;
	private $util;

	function __construct($db){
		$this->db = $db;
		$this->util = new Util();
	}
	public function getItensFormulaEdita($idprodutos){
		$itensFormula = '<div id="formula" class="tab-pane fade" style="padding-top: 5px;">
				          <div class="row" id="formulaCabecalho">
						  <div class="col-12 col-md-7 col-sm-7 col-lg-7 pb-3" style="margin-bottom: 5px;" align="right">
						  	<div class="input-group">
								<input type="text" class="form-control" id="produtos" name="produtos" placeholder="Materia Prima">
								<span class="input-group-addon" style="padding: 0x 6px;">
				            		<input type="text" class="idAutoComplementar" size="2" readonly id="pfor_idprodutos" name="pfor_idprodutos">
								</span>  
							</div>
						  </div>
				          <div class="col-4 col-md-2 col-sm-2 col-lg-2 pb-3" style="margin-bottom: 5px;" align="right;">
				            <input type="text" class="form-control" id="pfor_qte" name="pfor_qte" placeholder="Qte">
				            <span id="prod_uni"></span>
				          </div>
				          <div class="col-4 col-md-2 col-sm-2 col-lg-2 pb-3 pr-0" style="margin-bottom: 5px;" align="right;">
				              <input type="text" class="form-control" id="pfor_porc_perca" name="pfor_porc_perca" placeholder="Perca">
				          </div>
				          <div class="col-4 col-md-1 col-sm-1 col-lg-1 pb-3" style="margin-bottom: 5px;" align="center">
				           <button class="btn btn-default" type="button" onclick="gravaItensFormula()"><i class="fas fa-plus text-success"></i></button>
				          </div>
				        </div>
				        <div class="row" id="formulaItens">
				          <div class="col-12 col-md-12 col-sm-12 col-lg-12 pb-3">';
		$sqlFormulas = "SELECT * 
            FROM produtos_formulas 
              LEFT JOIN produtos ON (pfor_idprodutos = idprodutos) 
            WHERE pfor_idproduto_final = {$idprodutos}";
	    $resFormulas = $this->db->consultar($sqlFormulas);
	    $itensFormula .= '<table class="table" id="tableItensFormula">';
		$itensFormula .= '<tr>';
		$itensFormula .= '<td width="10%">Cod.</td>';
		$itensFormula .= '<td>Nome</td>';
		$itensFormula .= '<td width="15%">Qte</td>';
		$itensFormula .= '<td width="15%">Perca</td>';
		$itensFormula .= '<td width="3%">&nbsp;</td>';
		$itensFormula .= '</tr>';  
	    foreach ($resFormulas as $regFormulas) {
	        $itensFormula .= "<tr id='itemFormula_" . $regFormulas["idprodutos_formulas"] . "'>";
		    $itensFormula .= "<td>{$regFormulas["idprodutos"]}</td>";
		    $itensFormula .= "<td>{$regFormulas["prod_nome"]}</td>";
		    $itensFormula .= "<td>{$regFormulas["pfor_qte"]}</td>";
		    $itensFormula .= "<td>{$regFormulas["pfor_porc_perca"]}%</td>";
		    $itensFormula .= "<td onclick='excluirItemFormula({$regFormulas["idprodutos_formulas"]})' style='cursor: pointer;'><i class='fas fa-trash text-danger'></i></td>";
		    $itensFormula .= "</tr>"; 
	    }
	    $itensFormula .= '</table>';
	    $itensFormula .= '</div>';
	    $itensFormula .= '</div>';
	    $itensFormula .= '</div>';
	    //
	   	return $itensFormula;
	}

	public function getItensFormula($idprodutos, $qteProduzir){
		$itensFormula = '<div class="row" id="formulaItens">
						<label style="margin-left: 15px;">Itens a serem consumidos</label>
				          <div class="col-12 col-md-12 col-sm-12 col-lg-12 pb-3">';
		$sqlFormulas = "SELECT * 
            FROM produtos_formulas 
              LEFT JOIN produtos ON (pfor_idprodutos = idprodutos) 
            WHERE pfor_idproduto_final = {$idprodutos}";
	    $resFormulas = $this->db->consultar($sqlFormulas);
	    $itensFormula .= '<table class="table" id="tableItensFormula">';
		$itensFormula .= '<tr>';
		$itensFormula .= '<td width="10%">Cod.</td>';
		$itensFormula .= '<td>Nome</td>';
		$itensFormula .= '<td width="15%">Qte</td>';
		$itensFormula .= '<td width="15%">Perca</td>';
		$itensFormula .= '</tr>';  
	    foreach ($resFormulas as $regFormulas) {
			$qteConsumir = ($qteProduzir * $regFormulas["pfor_qte"]);
	        $itensFormula .= "<tr id='itemFormula_" . $regFormulas["idprodutos_formulas"] . "'>";
		    $itensFormula .= "<td>{$regFormulas["idprodutos"]}</td>";
		    $itensFormula .= "<td>{$regFormulas["prod_nome"]}</td>";
		    $itensFormula .= "<td>{$qteConsumir}</td>";
		    $itensFormula .= "<td>{$regFormulas["pfor_porc_perca"]}%</td>";
		    $itensFormula .= "</tr>"; 
	    }
	    $itensFormula .= '</table>';
	    $itensFormula .= '</div>';
	    $itensFormula .= '</div>';
	    $itensFormula .= '</div>';
	    //
	   	return $itensFormula;
	}

	public function getDadosProdutos($idprodutos){
		$sql = "SELECT * 
						FROM produtos 
							LEFT JOIN grupos ON (prod_idgrupos = idgrupos) 
							LEFT JOIN subgrupos ON (prod_idsubgrupos = idsubgrupos) 
							LEFT JOIN unidades ON (prod_idunidades = idunidades) 
						WHERE idprodutos = {$idprodutos}";
	    $reg = $this->db->retornaUmReg($sql);
		//
		return $reg;
	}

	public function etiquetaQrCodePadrao($qrcode, $nomeEmpresa, $cnpjEmpresa, $enderecoEmpresa, $cidadeEmpresa, $ufEmpresa, $cepEmpresa, $telefoneEmpresa, $logoRelatorios, $tamanhoLogo, $precoProd){
		$html = '';
		//
		$html .= '<div class="label">';
			$html .= '<img src="../uploads/' . $logoRelatorios . '" class="pull-left logo" width="' . $tamanhoLogo . '">';
			$html .= '<div><strong>' . $cidadeEmpresa . ' - ' . $ufEmpresa . '</strong></div>';
			$html .= '<img class="qrcode" src="data:image/png;base64,' . $qrcode . '">';
			$html .= '<div><strong>NÃO TROCAMOS PEÇAS</strong></div>';
			$html .= '<div class="line"></div>';
			$html .= '<div class="preco"><strong>R$ ' . $precoProd . '</strong></div>';
			$html .= '<div class="textoTroca"><strong>VERIFIQUE SEU PRODUTO NO ATO DA COMPRA</strong></div>';
		$html .= '</div>';
		//
		return $html;
	}
}
?>
