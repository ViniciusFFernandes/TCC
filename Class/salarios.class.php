<?php
require_once("util.class.php");

class Salarios{
	private $db;
	private $util;

	function __construct($db){
		$this->db = $db;
		$this->util = new Util();
	}
	public function insereFuncionarios($idsalarios){
		$sql = "SELECT idpessoas 
				FROM pessoas 
				WHERE (pess_associado = 'SIM' 
					OR pess_funcionario = 'SIM')
					AND pess_inativo <> 'S'";
		$res = $this->db->consultar($sql);
		//
		$this->db->setTabela("salarios_funcionarios", "idsalarios_funcionarios");
		//
		foreach($res AS $reg){
			unset($dados);
			$dados['id']                    = 0;
			$dados['safu_idsalarios']       = $idsalarios;
			$dados['safu_idpessoas']   		= $this->util->igr($reg['idpessoas']);
			$this->db->gravarInserir($dados, true);	
		}
	}

	public function getFuncionarios($idsalarios){
		$sql = "SELECT * 
				FROM salarios_funcionarios
					JOIN pessoas ON (idpessoas = safu_idpessoas) 
				WHERE safu_idsalarios = " . $idsalarios;
		$res = $this->db->consultar($sql);
		//
		$sql = "SELECT * FROM salarios WHERE idsalarios = " . $idsalarios;
		$situacao = $this->db->retornaUmCampoSql($sql, "sala_situacao"); 
		$regSal = $this->db->retornaUmReg($sql);
		$readonlye = '';
		if($regSal['sala_situacao'] != 'Aberto'){
			$readonlye = ' readonly="true" ';
		}
		//
		$linhaFuncionarios = '<br>';
		$linhas = 1;
		//
		foreach($res AS $reg){
			//
			$linhaFuncionarios .= '<div class="row" padding-bottom: 5px;" id="linhaSalario_' . $reg['idsalarios_funcionarios'] . '">';
				$linhaFuncionarios .= '<div class="col-md-4 col-sm-4 col-12 pb-3">';
					$linhaFuncionarios .= '<b>' . $reg['pess_nome'] . '</b> <span id="retonoGravaFuncionario_' . $reg['idsalarios_funcionarios'] . '"></span>';
				$linhaFuncionarios .= '</div>';
				$linhaFuncionarios .= '<div class="col-md-3 col-sm-3 col-4 pb-3">';
					$linhaFuncionarios .= '<div class="input-group">';
						$linhaFuncionarios .= '<span class="input-group-addon">F</span>';
						$linhaFuncionarios .= '<input type="text" class="form-control" ' . $readonlye . ' onchange="gravaDadosSalarios(' . $reg['idsalarios_funcionarios'] . ')" id="safu_dias_' . $reg['idsalarios_funcionarios'] . '" name="safu_dias_' . $reg['idsalarios_funcionarios'] . '" placeholder="Faltas" value=' . $reg['safu_dias'] . '>';
					$linhaFuncionarios .= '</div>';
				$linhaFuncionarios .= '</div>';
				$linhaFuncionarios .= '<div class="col-md-3 col-sm-3 col-6 pb-3">';
					$linhaFuncionarios .= '<div class="input-group">';
						$linhaFuncionarios .= '<span class="input-group-addon">R$</span>';
						$linhaFuncionarios .= '<input type="text" class="form-control" ' . $readonlye . ' onchange="gravaDadosSalarios(' . $reg['idsalarios_funcionarios'] . ')" id="safu_total_' . $reg['idsalarios_funcionarios'] . '" name="safu_total_' . $reg['idsalarios_funcionarios'] . '" placeholder="Salario" value=' . $this->util->formataMoeda($reg['safu_total']) . '>';
					$linhaFuncionarios .= '</div>';
				$linhaFuncionarios .= '</div>';
				$linhaFuncionarios .= '<div class="col-md-2 col-sm-2 col-2 pb-3" style="padding-left: 0px;"> ';
					if(empty($readonlye)){
						$linhaFuncionarios .= '<button type="button" class="btn btn-light" id="btnExcluir_' . $reg['idsalarios_funcionarios'] . '" onclick="excluirFuncionario(' . $reg['idsalarios_funcionarios'] . ')"><i class="fas fa-trash text-danger"></i></button>';
						$linhaFuncionarios .= '<span style="margin-left: 5px;" id="spanAtt_' . $reg['idsalarios_funcionarios'] . '">&nbsp;</span>';
					}else{
						$linhaFuncionarios .= '<button type="button" class="btn ml-2 btn-light" id="btnImprimir_' . $reg['idsalarios_funcionarios'] . '" onclick="imprimir(\'contapag_recibo_associado.php\', ' . $reg['idsalarios_funcionarios'] . ')"><i class="fas fa-print text-primary"></i></button>';
						$linhaFuncionarios .= '<button type="button" class="btn btn-light" id="btnImprimirCesta_' . $reg['idsalarios_funcionarios'] . '" onclick="imprimir(\'../_Cadastros/pessoas_recibo_cesta.php\', \'' . $reg['safu_idpessoas'] . '&mesano=' . $regSal['sala_mes'] . '-' . $regSal['sala_ano'] . '\')"><i class="fas fa-print text-primary"></i></button>';
					}
				$linhaFuncionarios .= '</div>';
			$linhaFuncionarios .= '</div>';
			//
			$linhas++;
		}
		//
		return $linhaFuncionarios;
	}

	public function getConteudoImpressao(){
		$html = '<div class="row border border-dark text-dark mb-5">
					<div class="col-md-12 col-sm-12 col-12 text-center p-3">
						<img src="../uploads/##logoRelatorios##" class="pull-left" width="##tamanhoLogo##">
							##nomeEmpresa## <br>
							CNPJ: ##cnpjEmpresa## <br>
							##enderecoEmpresa##, ##cidadeEmpresa## - ##ufEmpresa##, ##cepEmpresa## <br>
							##telefoneEmpresa##
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 col-sm-12 col-12 text-center text-dark p-2" >
						<p style="padding-top: 2rem;"> 
							<b>Recibo de Despesas de Prestação de Serviços</b>
						</p>  
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 col-sm-12 col-12 text-center text-dark p-2" >
						<p> 
							<b>##mesExtenso## de ##ano##</b>
						</p>  
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 col-sm-12 col-12 text-center text-dark p-2" >
						<p> 
							<b>##pess_nome##</b>
						</p>  
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 col-sm-6 col-6 text-dark p-2" >
						MATERIAIS COMERCIALIZADO
					</div>
					<div class="col-md-6 col-sm-6 col-6 border border-dark text-dark p-2" >
						##vlr_bruto##
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 col-sm-6 col-6 text-dark p-2" >
						Complementação salarial
					</div>
					<div class="col-md-6 col-sm-6 col-6 border border-bottom-0 border-top-0 border-dark text-dark p-2" >
						##vlr_juros##
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 col-sm-6 col-6 text-dark p-2" >
						TOTAL DA RECEITA
					</div>
					<div class="col-md-6 col-sm-6 col-6 border border-dark text-dark p-2" >
						##receita_total##
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 col-sm-12 col-12 text-dark p-2" >&nbsp;</div>
				</div>
				<div class="row">
					<div class="col-md-6 col-sm-6 col-6 text-dark p-2" >
						Falta
					</div>
					<div class="col-md-6 col-sm-6 col-6 border border-dark text-dark p-2" >
						##falta_dias## (##desconto_faltas##)
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 col-sm-6 col-6 text-dark p-2" >
						Adiantamento
					</div>
					<div class="col-md-6 col.-sm-6 col-6 text-dark border border-bottom-0 border-top-0 border-dark p-2" >
						##vlr_adiantamento##
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 col-sm-6 col-6 text-dark p-2" >
						Desconto de Rateio - prensa/coleta/lider/estação
					</div>
					<div class="col-md-6 col-sm-6 col-6 text-dark border border-dark p-2" >
						-----
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 col-sm-6 col-6 text-dark p-2" >
						OUTROS
					</div>
					<div class="col-md-6 col-sm-6 col-6 text-dark border border-bottom-0 border-top-0 border-dark p-2" >
						-----
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 col-sm-6 col-6 text-dark p-2" >
						Total de Despesas
					</div>
					<div class="col-md-6 col-sm-6 col-6 text-dark border border-dark p-2" >
						##vlr_desconto##
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 col-sm-12 col-12 p-2" >&nbsp;</div>
				</div>
				<div class="row">
					<div class="col-md-6 col-sm-6 col-6 text-dark p-2" >
						Total de Receita
					</div>
					<div class="col-md-6 col-sm-6 col-6 text-dark border border-dark p-2" >
						##receita_total##
					</div>
				</div><div class="row">
					<div class="col-md-6 col-sm-6 col-6 text-dark p-2" >
						Total de Despesas
					</div>
					<div class="col-md-6 col-sm-6 col-6 text-dark border border-bottom-0 border-top-0 border-dark p-2" >
						##vlr_desconto##
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 col-sm-6 col-6 text-dark p-2" >
						Total Liquido a Receber
					</div>
					<div class="col-md-6 col-sm-6 col-6 text-dark border border-dark p-2" >
						##vlr_liquido##
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 col-sm-12 col-12 text-dark p-2" >&nbsp;</div>
				</div>
				<div class="row">
					<div class="col-md-12 col-sm-12 col-12 text-dark p-2" >&nbsp;</div>
				</div>
				<div class="row">
					<div class="col-md-12 col-sm-12 col-12 text-dark p-2" >
					Recebi da ##nomeEmpresa## a importância de ##vlr_liquido## (##vlr_liquito_extenso##) ##msg_cesta_basica##referente a serviços prestados no mes de ##mesExtenso## de ##ano##
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 col-sm-12 col-12 text-dark p-2" >&nbsp;</div>
				</div>
				<div class="row">
					<div class="col-md-6 col-sm-6 col-6 text-dark p-2" >&nbsp;</div>
					<div class="col-md-6 col-sm-6 col-6 text-dark p-2"  align="center">
					______________________________________________ <br> 
					##pess_nome##
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 col-sm-12 col-12 p-2" >&nbsp;</div>
				</div>
				<div class="row">
					<div class="col-md-12 col-sm-12 col-12 p-2" >&nbsp;</div>
				</div>
				<div class="row">
					<div class="col-md-6 col-sm-6 col-6 p-2" >&nbsp;</div>
					<div class="col-md-6 col-sm-6 col-6 text-dark p-2"  align="center">
						##cidadeEmpresa##, ##diaAtual## de ##mesAtual## de ##anoAtual##
					</div>
				</div>';

		return $html;
	}

	public function geraDescontoFaltas($salario, $faltas, $mes, $ano){
		$diasUteis = $this->util->retornaDiasUteisMes($mes, $ano);
		$valorDia = $salario / $diasUteis;
		$valorFaltas = $valorDia * $faltas;
		return $valorFaltas;
	}
}
?>
