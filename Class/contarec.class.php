<?php
    require_once("util.class.php");
    require_once("parametros.class.php");
    require_once("html.class.php");
    require_once("cc.class.php");
    require_once("usuarios.class.php");

    class Contarec{
        private $db;
		private $util;
		private $parametros;
		private $html;
		private $cc;
        private $usuario;

		function __construct($db){
			$this->db = $db;
			$this->util = new Util();
			$this->parametros = new Parametros($db);
			$this->html = new html($db);
			$this->cc = new cc($db);
			$this->usuario = new Usuarios($this->db);
		}

        public function gerarHistorio($idconta, $operacao, $valor, $idoperador, $data = '', $idmeio_pagto = '', $idcc = ''){
            $this->db->setTabela("contarec_hist", "idcontarec_hist");
            //
            unset($dados);
            $dados['id']                    = 0;
            $dados['crhi_idcontarec'] 	    = $this->util->igr($idconta);
            $dados['crhi_operacao']         = $this->util->sgr($operacao);
            $dados['crhi_data_pagto'] 	    = $this->util->dgr($data);
            $dados['crhi_idmeio_pagto'] 	= $this->util->igr($idmeio_pagto);
            $dados['crhi_idcc']             = $this->util->igr($idcc);
            $dados['crhi_valor']            = $this->util->vgr($valor);
            $dados['crhi_idoperador'] 	    = $this->util->igr($idoperador);
            $dados['crhi_data']             = $this->util->dgr(date('d/m/Y H:i'));
            //
            $this->db->gravarInserir($dados);
        }

        public function baixaConta($idconta, $valor, $multa, $desconto, $idcc, $idmeio_pagto, $data, $idoperador){
            //
            $valor = $this->util->vgr($valor);
            //
            $sql = "SELECT * FROM contarec JOIN pessoas ON (ctrc_idcliente = idpessoas) WHERE idcontarec = " . $idconta;
            $reg = $this->db->retornaUmReg($sql);
            //
            if($reg['ctrc_situacao'] != 'Pendente' && $reg['ctrc_situacao'] != 'QParcial'){
                $this->html->mostraErro("Está conta está " . $reg['ctrc_situacao'] . " e não pode ser paga!");
                $this->db->rollBack();
                exit;
            }
            if($reg['ctrc_vlr_devedor'] <= 0){
                $this->html->mostraErro("Está conta já esta totalmente paga!");
                $this->db->rollBack();
                exit;
            }
            //
            $novoValorDevedor = ($reg['ctrc_vlr_devedor'] + $multa - $desconto);
            //
            if($novoValorDevedor < $valor){
                $this->html->mostraErro("Você está tentando receber um valor maior!");
                $this->db->rollBack();
                exit;
            }
            //
            if($novoValorDevedor == $valor){
                $novaSitucao = 'Quitada';
            }elseif($novoValorDevedor > $valor){
                $novaSitucao = 'QParcial';
            }
            //
            $this->db->setTabela("contarec", "idcontarec");
            //
            unset($dados);
            $dados['id']                    = $idconta;
            $dados['ctrc_situacao']         = $this->util->sgr($novaSitucao);
            $dados['ctrc_vlr_pago']         = $this->util->vgr($valor);
            if($multa > 0){
                $novoJuros = $reg['ctrc_vlr_juros'] + $multa;
                $novoPorcJuros = ($novoJuros * 100) / $reg['ctrc_vlr_bruto'];
                //
                $dados['ctrc_vlr_juros']    = $this->util->vgr($novoJuros);
                $dados['ctrc_porc_juros']   = $this->util->vgr($novoPorcJuros);
            }
            if($desconto > 0){
                $novoDescontos = $reg['ctrc_vlr_desconto'] + $desconto;
                $novoPorcDescontos = ($novoDescontos * 100) / $reg['ctrc_vlr_bruto'];
                //
                $dados['ctrc_vlr_desconto']    = $this->util->vgr($novoDescontos);
                $dados['ctrc_porc_desconto']   = $this->util->vgr($novoPorcDescontos);
            }
            //
            $this->db->gravarInserir($dados, true, "Pagamento");
            //
            //
            $this->gerarHistorio($idconta, "Baixa", $valor, $idoperador, $data, $idmeio_pagto, $idcc);
            //
            if($this->parametros->buscaValor("sistema: gerar lancamentos bancarios pelas contas") == 'SIM'){
                $this->cc->gerarLancamento($idconta, "C", $valor, $idoperador, $data, $idmeio_pagto, $idcc, "contarec", "Recebimento da conta");
            }
            //
            if(SISTEMA_SAC == "SIM"){
                $this->usuario->gerar_chave_pagto($reg["pess_cod_cliente"]);
            }
            //
        }

        public function reabrirConta($idconta, $idoperador){
            $sql = "SELECT * FROM contarec WHERE idcontarec = " . $idconta;
            $reg = $this->db->retornaUmReg($sql);
            //
            if($reg['ctrc_situacao'] != 'Quitada' && $reg['ctrc_situacao'] != 'QParcial'){
                $this->html->mostraErro("Está conta está " . $reg['ctrc_situacao'] . " e não pode ser reaberta!");
                $this->db->rollBack();
                exit;
            }
            //
            $this->db->setTabela("contarec", "idcontarec");
            //
            unset($dados);
            $dados['id']                    = $idconta;
            $dados['ctrc_situacao']         = $this->util->sgr("Pendente");
            $dados['ctrc_vlr_pago']         = $this->util->vgr(0);
            //
            $this->db->gravarInserir($dados, true, "Reabertura");
            //
            //
            $this->gerarHistorio($idconta, "Reabertura", $reg['ctrc_vlr_pago'], $idoperador, date('d/m/Y'), $reg['ctrc_idmeio_pagto'], $reg['ctrc_idcc']);
            //
            if($this->parametros->buscaValor("sistema: gerar lancamentos bancarios pelas contas") == 'SIM'){
                $this->cc->gerarLancamento($idconta, "D", $reg['ctrc_vlr_pago'], $idoperador, date("d/m/Y"), $reg['ctrc_idmeio_pagto'], $reg['ctrc_idcc'], "contarec", "Reabertura da conta");
            }
            //
        }

        public function geraHistorico($idconta){
			$sql = "SELECT * FROM contarec_hist WHERE crhi_idcontarec = " . $idconta;
			$res = $this->db->consultar($sql);
			foreach ($res as $reg) {
				$hist .= '<div class="row">';
					$hist .= '<div class="col-sm-12 col-12">';
                        $hist .= '<div class="card shadow mb-4 border-left-primary">';
                            $hist .= '<div class="card-header py-3"><b>Operação: </b>' . $reg['crhi_operacao'] . '<span style="float: right;">' . $this->util->convertData($reg['crhi_data']) . '</span></div>';
                            $hist .= '<div class="card-body">';
                                $hist .= '<div class="row">';
                                    $hist .= '<div class="col-sm-2 col-md-2 col-6">Valor: ' . $this->util->formataMoeda($reg['crhi_valor']) . '</div>';
                                    $hist .= '<div class="col-sm-3 col-md-3 col-6">Operador: ' . $this->db->retornaUmCampoID("pess_nome", "pessoas", $reg['crhi_idoperador']) . '</div>';
                                    $hist .= '<div class="col-sm-3 col-md-3 col-6">Conta Bancária: ' . $this->db->retornaUmCampoID("cc_nome", "cc", $reg['crhi_idcc']) . '</div>';
                                    $hist .= '<div class="col-sm-3 col-md-3 col-6">Meio de pagamento: ' . $this->db->retornaUmCampoID("mpag_nome", "meio_pagto", $reg['crhi_idmeio_pagto']) . '</div>';
                                    if(strtotime($reg['crhi_data_pagto']) > 0){
                                        $hist .= '<div class="col-sm-4 col-md-4 col-6">Pagamento: ' . $this->util->convertData($reg['crhi_data_pagto']) . '</div>';
                                    }
                                $hist .= '</div>';
                            $hist .= '</div>';
                        $hist .= '</div>';
					$hist .= '</div>';
				$hist .= '</div>';
			}
			//
			if(empty($hist)) $hist = "Nenhum histórico encontrado!<br>";
			//
			return $hist;
		}
    }
?>
