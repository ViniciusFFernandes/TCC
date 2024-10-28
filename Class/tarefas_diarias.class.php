<?php
	class Tarefas_Diarias {
		private $data_atual;
		private $parametros;
		private $ultima_execucao;
		private $db;
		private $util;
		private $atualizacao;
		private $email;
		private $usuarios;

		function __construct($parametros, $db, $util, $atualizacao, $email, $usuarios){
			$this->data_atual = date('Y-m-d');
			$this->db = $db;
			$this->util = $util;
			$this->parametros = $parametros;
			$this->atualizacao = $atualizacao;
			$this->email = $email;
			$this->usuarios = $usuarios;
			$this->ultima_execucao = $this->parametros->buscaValor("sistema: data da ultima execucao de tarefas diarias");
		}

		public function executa_tarefas(){
	      	if (strtotime($this->ultima_execucao) >= strtotime($this->data_atual)) {
				//
	        	//Removido pois está agendado a execução e só ocorre uma vez ao dia 
				//
				//return;
		  	}
		  	//Atualiza a data de execução.
		  	unset($dados);
		  	$dados['id'] 			= $this->util->sgr('sistema: data da ultima execucao de tarefas diarias');
		  	$dados['para_valor'] 	= $this->util->sgr($this->data_atual);
     		$this->parametros->gravaValor($dados);
     		//
     		//Executa tarefas
     		$this->apagaHistoricoAtualizacao();
     		$this->buscaAtualizacoes();
			$this->emailContaPagVencida();
			$this->emailContaRecVencida();
			$this->buscaChavePagto();
		}

		private function buscaChavePagto(){
			if(SISTEMA_SAC != "SIM"){
				$this->usuarios->busca_chave_pagto();
			}
		}

		private function apagaHistoricoAtualizacao(){
			$sql = "DELETE FROM versao_hist WHERE DATE_ADD(vhist_data, INTERVAL 60 DAY) <= NOW()";
			$this->db->executaSQL($sql);
		}

		private function buscaAtualizacoes(){
			if($this->parametros->buscaValor("sistema: busca atualizacoes automaticamente") == 'SIM'){
				$this->atualizacao->baixaAtualizacao();
			}
		}

		private function emailContaPagVencida(){
			$emailTo = $this->parametros->buscaValor("sistema: email para o financeiro");
			//
			$sql = "SELECT * 
					FROM contapag 
						JOIN pessoas ON (ctpg_idcliente = idpessoas)
					WHERE ctpg_vencimento = CURDATE()
						AND ctpg_situacao = 'Pendente'";
			$res = $this->db->consultar($sql);
			//
			$html = '';
			//
			if(!empty($res)){
				//
				foreach($res as $reg){
					$html .= "<div class='row'>";
						$html .= "<div class='col-12'>";
							$html .= $reg['pess_nome'] . "<br>";
							$html .= "Contas: {$reg['idcontapag']} Valor: " . $this->util->formataMoeda($reg['ctpg_vlr_devedor']);
						$html .= "</div>";
					$html .= "</div>";
				}
				//
				$corpo = file_get_contents("_HTML/corpoEmailContapag.html");
				$corpo = str_replace("##data##", date("d/m/Y"), $corpo);
				$corpo = str_replace("##contas##", $html, $corpo);
				//
				$this->email->enviaEmailSimples($emailTo, "Financeiro - Contas a Pagar Vencidas", $corpo);
			}
		}

		private function emailContaRecVencida(){
			$emailTo = $this->parametros->buscaValor("sistema: email para o financeiro");
			//
			$sql = "SELECT * 
					FROM contarec 
						JOIN pessoas ON (ctpg_idclientes = idpessoas)
					WHERE ctrc_vencimento = CURDATE()
						AND ctrc_situacao = 'Pendente'";
			$res = $this->db->consultar($sql);
			//
			$html = '';
			//
			if(!empty($res)){
				//
				foreach($res as $reg){
					$html .= "<div class='row'>";
						$html .= "<div class='col-12'>";
							$html .= $reg['pess_nome'] . "<br>";
							$html .= "Contas: {$reg['idcontapag']} Valor: " . $this->util->formataMoeda($reg['ctpg_vlr_devedor']);
						$html .= "</div>";
					$html .= "</div>";
				}
				//
				$corpo = file_get_contents("_HTML/corpoEmailContarec.html");
				$corpo = str_replace("##data##", date("d/m/Y"), $corpo);
				$corpo = str_replace("##contas##", $html, $corpo);
				//
				$this->email->enviaEmailSimples($emailTo, "Financeiro - Contas a Receber Vencidas", $corpo);
			}
		}
	}

?>
