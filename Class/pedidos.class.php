<?php
    require_once("util.class.php");
    require_once("parametros.class.php");
    require_once("util.class.php");
    require_once("html.class.php");
    require_once("cc.class.php");

    class Pedidos{
        private $db;
		private $util;
		private $parametros;
		private $html;
		private $cc;

		function __construct($db){
			$this->db = $db;
			$this->util = new Util();
			$this->parametros = new Parametros($db);
			$this->html = new html($db);
			$this->cc = new cc($db);
		}

        public function retornaDadosDesconto($idpedidos, $valorPedido, $valorDesconto, $porcDesconto){
            if($idpedidos > 0){
                $totalPedidos = $this->db->retornaUmCampoID('ped_total_produtos + ped_frete', 'pedidos', $idpedidos);
            }else{
                $totalPedidos = $valorPedido;
            }
            //
            $dados = array();
            //
            if($valorDesconto > 0){
                $dados['valor'] = $valorDesconto;
                $dados['porcentagem'] = ($valorDesconto * 100) / $totalPedidos;
            }elseif($porcDesconto > 0){
                $dados['porcentagem'] = $porcDesconto;
                $dados['valor'] = ($totalPedidos * $porcDesconto) / 100;
            }else{
                $dados['valor'] = 0;
                $dados['porcentagem'] = 0; 
            }
            //
            return $dados;
        }

        public function retornaItensPedido($idpedidos){
            //
            $sql = "SELECT * 
                    FROM pedidos 
                        JOIN pedidos_itens ON (peit_idpedidos = idpedidos) 
                        LEFT JOIN produtos ON (idprodutos = peit_idprodutos)
                    WHERE peit_idpedidos = " . $idpedidos;
            $res = $this->db->consultar($sql);
            //
            if(empty($res)) return; //Retorna em branco caso nao tenha itens
            //
            $div = '<div class="row pb-3">';
                $div .= '<div class="col-md-12 col-sm-12 col-lg-12 col-12">';
                //
                    $div .= '<div class="row linhaPedidoItens font-weight-bold">';
                        $div .= '<div class="col-lg-3 d-none d-lg-block">';
                            $div .= 'Produto';
                        $div .= '</div>';
                        $div .= '<div class="col-lg-2 d-none d-lg-block text-right">';
                            $div .= 'Qte';
                        $div .= '</div>';
                        $div .= '<div class="col-lg-2 d-none d-lg-block text-right">';
                            $div .= 'Unitário';
                        $div .= '</div>';
                        $div .= '<div class="col-lg-2 d-none d-lg-block text-right">';
                            $div .= 'Desconto';
                        $div .= '</div>';
                        $div .= '<div class="col-lg-2 d-none d-lg-block text-right">';
                            $div .= 'Total';
                        $div .= '</div>';
                        $div .= '<div class="col-lg-1 d-none d-lg-block text-right">';
                        $div .= '</div>';
                    $div .= '</div>';
                    //
                    foreach($res AS $reg){
                        $div .= '<div class="row border-prod">';
                            $div .= '<div class="col-lg-3 col-sm-12 col-md-12 col-12 pt-2">';
                                $div .= $reg['idprodutos'] . ' - ' . $reg['prod_nome'];
                                if($reg["ped_situacao"] == "Aberto"){
                                    $div .= '<i style="float: right;" class="pointer fas fa-trash-alt pl-1 d-block d-lg-none"  onclick="excluiItem(' . $reg['idpedidos_itens'] . ')"></i>';
                                    $div .= '<i style="float: right;" class="pointer far fa-edit pr-1 d-block d-lg-none" onclick="editarItem(' . $reg['idpedidos_itens'] . ')"></i>';
                                }
                                $div .= '<div class="row">';
                                    $div .= '<div class="col-6 col-sm-6 col-md-6 d-block d-lg-none dadosProdPed">';
                                        $div .= 'Qte: ' . $this->util->formataNumero($reg['peit_qte']);
                                    $div .= '</div>';
                                    $div .= '<div class="col-6 col-sm-6 col-md-6 d-block d-lg-none dadosProdPed">';
                                        $div .= 'Unitário: ' . $this->util->formataMoeda($reg['peit_vlr_unitario']);
                                    $div .= '</div>';
                                    $div .= '<div class="col-6 col-sm-6 col-md-6 d-block d-lg-none dadosProdPed">';
                                        $div .= 'Desconto: ' . $this->util->formataMoeda($reg['peit_valor_desconto']);
                                    $div .= '</div>';
                                    $div .= '<div class="col-6 col-sm-6 col-md-6 d-block d-lg-none dadosProdPed">';
                                        $div .= 'Total: ' . $this->util->formataMoeda($reg['peit_total_item']);
                                    $div .= '</div>';
                                $div .= '</div>';
                            $div .= '</div>';
                            $div .= '<div class="col-lg-2 pt-2 d-none d-lg-block text-right">';
                                $div .= $this->util->formataNumero($reg['peit_qte']);
                            $div .= '</div>';
                            $div .= '<div class="col-lg-2 pt-2 d-none d-lg-block text-right">';
                                $div .= $this->util->formataMoeda($reg['peit_vlr_unitario']);
                            $div .= '</div>';
                            $div .= '<div class="col-lg-2 pt-2 d-none d-lg-block text-right">';
                                $div .= $this->util->formataMoeda($reg['peit_valor_desconto']);
                            $div .= '</div>';
                            $div .= '<div class="col-lg-2 pt-2 d-none d-lg-block text-right">';
                                $div .= $this->util->formataMoeda($reg['peit_total_item']);
                            $div .= '</div>';
                            $div .= '<div class="col-lg-1 pt-2 d-none d-lg-block text-right">';
                                if($reg["ped_situacao"] == "Aberto"){
                                    $div .= '<i class="pointer far fa-edit mr-2" onclick="editarItem(' . $reg['idpedidos_itens'] . ')"></i>';
                                    $div .= '<i class="pointer fas fa-trash-alt ml-2"  onclick="excluiItem(' . $reg['idpedidos_itens'] . ')"></i>';
                                }
                            $div .= '</div>';
                        $div .= '</div>';
                    }
                //
                $div .= '</div>';
            $div .= '</div>';
            //
            return $div;
        }

        public function retornaContasPedido($idpedidos){
            //
            $sql = "SELECT * 
                    FROM pedidos
                        JOIN pedidos_contas ON (pcon_idpedidos = idpedidos)
                        LEFT JOIN meio_pagto ON (idmeio_pagto = pcon_idmeio_pagto)
                        LEFT JOIN cc ON (idcc = pcon_idcc)
                        LEFT JOIN tipo_contas ON (idtipo_contas = pcon_idtipo_contas)
                    WHERE pcon_idpedidos = " . $idpedidos;
            $res = $this->db->consultar($sql);
            //
            if(empty($res)) return ""; //Retorna em branco caso nao tenha itens
            //
            $div = '<div class="row pb-3">';
                $div .= '<div class="col-md-12 col-lg-12 col-sm-12 col-12">';
                //
                    $div .= '<div class="row linhaPedidoContas font-weight-bold">';
                        $div .= '<div class="col-lg-1 d-none d-lg-block">';
                            $div .= 'Dias';
                        $div .= '</div>';
                        $div .= '<div class="col-lg-2 d-none d-lg-block">';
                            $div .= 'Vencimento';
                        $div .= '</div>';
                        $div .= '<div class="col-lg-2 d-none d-lg-block">';
                            $div .= 'Conta Bancaria';
                        $div .= '</div>';
                        $div .= '<div class="col-lg-2 d-none d-lg-block">';
                            $div .= 'Meio de Pagamento';
                        $div .= '</div>';
                        $div .= '<div class="col-lg-1 d-none d-lg-block">';
                            $div .= 'Conta';
                        $div .= '</div>';
                        $div .= '<div class="col-lg-2 d-none d-lg-block">';
                            $div .= 'Tipo';
                        $div .= '</div>';
                        $div .= '<div class="col-lg-1 d-none d-lg-block text-right">';
                            $div .= 'Valor';
                        $div .= '</div>';
                        $div .= '<div class="col-lg-1 d-none d-lg-block text-right">';
                        $div .= '</div>';
                    $div .= '</div>';
                    //
                    foreach($res AS $reg){
                        $classePointer = "";
                        $functionAbreConta = "";
                        if($reg['pcon_idcontarec'] > 0){
                            $classePointer = 'pointer';
                            $functionAbreConta = 'onclick="abreConta(' . $reg['pcon_idcontarec'] . ')"';
                        }
                        $div .= '<div class="row border-prod">';
                            $div .= '<div class="col-lg-1 pt-2 d-none d-lg-block">';
                                $div .= $reg['pcon_vencimento_dias'];
                            $div .= '</div>';
                            $div .= '<div class="col-lg-1 col-md-12 col-sm-12 col-12 pt-2">';
                                $div .=  '<span class="d-lg-none">' . $reg['pcon_vencimento_dias'] . ' - </span>' . $this->util->convertData($reg['pcon_vencimento']);
                                if($reg["ped_situacao"] == "Aberto"){
                                    $div .= '<i style="float: right;" class="pointer fas fa-trash-alt pl-1 d-block d-lg-none"  onclick="excluiConta(' . $reg['idpedidos_contas'] . ')"></i>';
                                    $div .= '<i style="float: right;" class="pointer far fa-edit pr-1 d-block d-lg-none" onclick="editarConta(' . $reg['idpedidos_contas'] . ')"></i>';
                                }
                                $div .= '<div class="row">';
                                    $div .= '<div class="col-6 col-sm-6 col-md-6 d-block d-lg-none dadosContasPed">';
                                        $div .= 'Conta: ' . $reg['cc_nome'];
                                    $div .= '</div>';
                                    $div .= '<div class="col-6 col-sm-6 col-md-6 d-block d-lg-none dadosContasPed">';
                                        $div .= 'Pagamento: ' . $reg['mpag_nome'];
                                    $div .= '</div>';
                                    $div .= '<div class="col-6 col-sm-6 col-md-6 d-block d-lg-none dadosContasPed ' . $classePointer . '" ' . $functionAbreConta . '>';
                                        $div .= 'Código: ' . $reg['pcon_idcontarec'];
                                    $div .= '</div>';
                                    $div .= '<div class="col-6 col-sm-6 col-md-6 d-block d-lg-none dadosContasPed">';
                                        $div .= 'Valor: ' . $this->util->formataMoeda($reg['pcon_valor']);
                                    $div .= '</div>';
                                $div .= '</div>';
                            $div .= '</div>';
                            $div .= '<div class="col-lg-2 pt-2 d-none d-lg-block">';
                                $div .= $reg['cc_nome'];
                            $div .= '</div>';
                            $div .= '<div class="col-lg-2 pt-2 d-none d-lg-block">';
                                $div .= $reg['mpag_nome'];
                            $div .= '</div>';
                            $div .= '<div class="col-lg-1 pt-2 d-none d-lg-block ' . $classePointer . '" ' . $functionAbreConta . '>';
                                $div .= $reg['pcon_idcontarec'];
                            $div .= '</div>';
                            $div .= '<div class="col-lg-2 pt-2 d-none d-lg-block">';
                                $div .= $reg['tico_nome'];
                            $div .= '</div>';
                            $div .= '<div class="col-lg-1 pt-2 d-none d-lg-block text-right">';
                                $div .= $this->util->formataMoeda($reg['pcon_valor']);
                            $div .= '</div>';
                            $div .= '<div class="col-lg-1 pt-2 d-none d-lg-block text-right">';
                                if($reg["ped_situacao"] == "Aberto"){
                                    $div .= '<i class="pointer far fa-edit mr-2" onclick="editarConta(' . $reg['idpedidos_contas'] . ')"></i>';
                                    $div .= '<i class="pointer fas fa-trash-alt ml-2"  onclick="excluiConta(' . $reg['idpedidos_contas'] . ')"></i>';
                                }
                            $div .= '</div>';
                        $div .= '</div>';
                    }
                //
                $div .= '</div>';
            $div .= '</div>';
            //
            return $div;
        }

    }
?>
