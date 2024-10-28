<?php
	require_once("parametros.class.php");
	require_once("html.class.php");
	require_once("util.class.php");

	class Atualizacao {
		private $ultimaVersao = 1.16;
		private $db;
		private $parametros;
		private $util;
		private $html;

		function __construct($db){
			$this->db = $db;
			$this->parametros = new Parametros($db);
			$this->util = new Util();
			$this->html = new html($db);
		}

		public function getUltimaVersao(){
			return $this->ultimaVersao;
		}

		public function geraHistorico(){
			$sql = "SELECT * FROM versao_hist ORDER BY idversao_hist DESC";
			$res = $this->db->consultar($sql);
			foreach ($res as $reg) {
				$hist .= '<div class="row">';
					$hist .= '<div class="col-sm-12 col-12">';
						$hist .= '<div class="card shadow mb-4 border-left-primary">';
							$hist .= '<div class="card-header py-3">' . $reg['vhist_versao'] . '<span style="float: right;">' . $this->util->convertData($reg['vhist_data']) . '</span></div>';
							$hist .= '<div class="card-body">' . $reg['vhist_mensagem'] . '</div>';
						$hist .='</div>';
					$hist .= '</div>';
				$hist .= '</div>';
			}
			//
			if(empty($hist)) $hist = "Nenhum histórico de atualização encontrado!<br>Os históricos são deletados a cada 60 dias.";
			//
			return $hist;
		}

		public function atualizarSistema($versaoAtual){
			$dados['executaNovamente'] = true;
			$versaoAtual += 0.01;
			$versaoFormatada = (str_pad(intval($versaoAtual), 2, "0", STR_PAD_LEFT)) . "." . (str_pad( round(($versaoAtual - intval($versaoAtual)), 2) * 100  , 2, "0", STR_PAD_LEFT));
			//
			$versao = '$this->versao_';
			$versao .= (str_pad(intval($versaoAtual), 2, "0", STR_PAD_LEFT));
			$versao .= "_";
			$versao .= (str_pad( round(($versaoAtual - intval($versaoAtual)), 2) * 100  , 2, "0", STR_PAD_LEFT));
			$versao .= "();";
			//echo $versao;
			$msg = eval("return " . $versao . ";");  // chama a funcao de atualizacao da versao.
			//
			if(empty($msg)){
				$msg = "Dados sobre a atualização não especificados!";
			}
			//
			if($versaoAtual >= $this->ultimaVersao) $dados['executaNovamente'] = false;
			$dados['novaVersao'] = $versaoFormatada;
			$dados['msg'] = $msg;
			$dados['executado'] = true;
			if($this->db->erro()){
				$dados['executaNovamente'] = false;
				$dados['executado'] = false;
				$dados['msg'] = "Erro: " . $this->db->getErro();
			}
			return $dados;
		}
		//////////////////////////////////////
		//Exemplos de uso das funções       //
		//////////////////////////////////////
		//
		//$this->cadastraPrograma("arquivo_do_programa.php", 'Tipo no Grupo Acesso', 'Nome para o Menu',  'tipo: Programa ou Menu', 'Nome_da_imagem_menu.png', 'Qual Menu Aparece', Sepode ou Não Executar por Padrão: 0 ou 1, 'Pasta raiz do programa se for menu');
		//
		//////////////////////////////////////
		//Abaixo estão as versões do sistema//
		//////////////////////////////////////

		private function versao_01_16(){
			//
			// 27/10/2024 Vinicius
			//
			$this->cadastraPrograma("chave_acesso.php", 'Sistema', 'Chave de Acesso',  'menu', '', 'configuracoes', 0, '_Configuracoes');
			$this->cadastraPrograma("chave_acesso_grava.php", 'Sistema');
			//
			//Mensagem para o usuario
			return "Cadastro do programa para chave de acesso";
		}

		private function versao_01_15(){
			//
			// 27/10/2024 Vinicius
			//
			$this->parametros->cadastraParametros("sistema: sendereco web do SAC", "https://sac.vfweb.cloud/", "Parametro usado para definir o endereco web do sistema utilizado com SAC"); 
			//
			//
			//Mensagem para o usuario
			return "Criação do parametro com o endereço do SAC";
		}

		private function versao_01_14(){
			//
			// 26/10/2024 Vinicius
			//
			$sql = "ALTER TABLE pessoas ADD pess_chave_pagto VARCHAR(255) NULL";
			$this->db->executaSQL($sql); 
			//
			$sql = "ALTER TABLE pessoas ADD pess_vip varchar(5) DEFAULT 'NAO'";
			$this->db->executaSQL($sql); 
			//
			$this->parametros->cadastraParametros("sistema: chave de pagamento", "", "Parametro usado para definir e identificar a chave de pagamento do sistema", "constante", "CHAVE_PAGTO"); 
			//
			//
			//Mensagem para o usuario
			return "Criação de campo e parametro para controle de pagamento no sistema pelo SAC";
		}

		private function versao_01_13(){
			//
			// 13/10/2024 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS agenda(
						idagenda int(11) NOT NULL AUTO_INCREMENT,
						agen_titulo VARCHAR(255) NOT NULL,
						agen_inicio DATETIME NOT NULL,
						agen_fim DATETIME NULL,
						agen_descricao TEXT NULL,
						agen_cor VARCHAR(100) NULL,
						agen_idusuario INT NULL,
						agen_data_registro DATETIME NULL,
						PRIMARY KEY (idagenda)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
			$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela de agenda";
		}

		private function versao_01_12(){
			//
			// 13/10/2024 Vinicius
			//
			$this->cadastraPrograma("agenda.php", 'Menu', 'Agenda',  'menuRaiz', '<i class="fas fa-calendar d-none d-sm-inline"></i>', '', "1", '_Agenda', "1.1");
			$this->cadastraPrograma("agenda_grava.php", 'Agenda', '', 'programa', '', '', '1');
			//
			//Mensagem para o usuario
			return "Cadastro do programa para agenda";
		}

		private function versao_01_11(){
			//
			// 28/03/2024 Vinicius
			//
			$sql = "ALTER TABLE programas ADD CONSTRAINT unique_prog_file UNIQUE (prog_file);";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criado validação para impedir cadastro de dois programas com mesmo nome de arquivo";
		}

		private function versao_01_10(){
			//
			// 28/03/2024 Vinicius
			//
			$this->cadastraPrograma("programas.php", 'Sistema', 'Programas',  'menu', '', 'configuracoes', 0, '_Configuracoes');
			$this->cadastraPrograma("programas_grava.php", 'Sistema');
			//
			//Mensagem para o usuario
			return "Cadastro do programa para gerenciar modelos";
		}

		private function versao_01_09(){
			//
			// 06/09/2024 Vinicius
			//
			$sql = "ALTER TABLE pessoas ADD pess_cod_cliente VARCHAR(255) NULL";
			$this->db->executaSQL($sql); 
			//
			$this->parametros->cadastraParametros("sistema: sistema utilizado como SAC", "NAO", "Parametro usado para definir se o sistema é utilizado como SAC da empresa", "constante", "SISTEMA_SAC"); 
			$this->parametros->cadastraParametros("sistema: codigo do cliente no SAC", "", "Parametro usado para definir o codigo do cliente definido no SAC", "constante", "COD_CLIENTE_SAC"); 
			//
			//
			//Mensagem para o usuario
			return "Criação de campo e parametros para integração com SAC";
		}

		private function versao_01_08(){
			//
			// 06/09/2024 Vinicius
			//
			$sql = "ALTER TABLE programas ADD prog_modelo VARCHAR(255) NULL";
			$this->db->executaSQL($sql); 
			//
			//
			//Mensagem para o usuario
			return "Criação de campo para gravar o modelo de programas";
		}

		private function versao_01_07(){
			//
			// 06/08/2023 Vinicius
			//
			$sql = "ALTER TABLE salarios ADD sala_idempresas INT NULL";
			$this->db->executaSQL($sql); 
			//
			//
			//Mensagem para o usuario
			return "Criação de campo para gravar a empresa nas rotinas de salários";
		}

		private function versao_01_06(){
			//
			// 14/07/2023 Vinicius
			//
			$this->cadastraPrograma("contarec_sel.php", 'Relatório', 'Contas a Receber',  'menu', '', 'relatorios', 0, '_Relatorios');
			$this->cadastraPrograma("contarec.php", 'Relatório');
			//
			//Mensagem para o usuario
			return "Criação do programa de relatórios de contas a receber";
		}

		private function versao_01_05(){
			//
			// 13/07/2023 Vinicius
			//
			$sql = "ALTER TABLE tipo_contas ADD tico_cor varchar(50) NOT NULL DEFAULT '#000000'";
			$this->db->executaSQL($sql); 
			//
			//
			//Mensagem para o usuario
			return "Criação de campo para gravar a cor do tipo conta nos graficos";
		}

		private function versao_01_04(){
			//
			// 11/07/2022 Vinicius
			//
			$this->cadastraPrograma("contapag_sel.php", 'Relatório', 'Contas a Pagar',  'menu', '', 'relatorios', 0, '_Relatorios');
			$this->cadastraPrograma("contapag.php", 'Relatório');
			//
			//Mensagem para o usuario
			return "Criação do programa de relatórios de contas a pagar";
		}

		private function versao_01_03(){
			//
			// 06/07/2022 Vinicius
			//
			$this->cadastraPrograma("impressao_pedido.php", 'Impressões');
			$this->cadastraPrograma("impressao_pedcompra.php", 'Impressões');
			//
			//Mensagem para o usuario
			return "Cadastro de programas para impressão de pedidos";
		}

		private function versao_01_02(){
			//
			// 06/07/2022 Vinicius
			//
			$sql = "CREATE TRIGGER pedcompras_itens_insert
						AFTER INSERT
						ON pedcompras_itens
						FOR EACH ROW
							BEGIN
								UPDATE pedcompras set pcom_total_produtos = (pcom_total_produtos + NEW.pcit_total_item) WHERE idpedcompras = NEW.pcit_idpedcompras;
							END";
			$this->db->executaSQL($sql);
			//
			$sql = "CREATE TRIGGER pedcompras_itens_update
						AFTER UPDATE
						ON  pedcompras_itens
						FOR EACH ROW
							BEGIN
								UPDATE pedcompras set pcom_total_produtos = (pcom_total_produtos - OLD.pcit_total_item) WHERE idpedcompras = OLD.pcit_idpedcompras;
								UPDATE pedcompras set pcom_total_produtos = (pcom_total_produtos + NEW.pcit_total_item) WHERE idpedcompras = NEW.pcit_idpedcompras;
							END";
			$this->db->executaSQL($sql);
			//
			$sql = "CREATE TRIGGER pedcompras_itens_delete
						AFTER delete
						ON  pedcompras_itens
						FOR EACH ROW
							BEGIN
								UPDATE pedcompras set pcom_total_produtos = (pcom_total_produtos - OLD.pcit_total_item) WHERE idpedcompras = OLD.pcit_idpedcompras;
							END";
			$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da TRIGGER para gerar os valores de produtos no pedido de compra";
		}

		
		private function versao_01_01(){
			//
			// 06/07/2021 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS pedcompras_contas(
						idpedcompras_contas int(11) NOT NULL AUTO_INCREMENT,
						pccon_idpedcompras int NULL,
						pccon_idcontapag int NULL,
						pccon_idmeio_pagto int NULL,
						pccon_idbancos int NULL,
						pccon_idcc int NULL,
						pccon_idempresas int NULL,
						pccon_idtipo_contas int NULL,
						pccon_vencimento_dias INT NOT NULL,
						pccon_vencimento DATE NOT NULL,
						pccon_valor DECIMAL(10,2) NOT NULL DEFAULT 0.00,
						pccon_parcela varchar(50) NULL,
						PRIMARY KEY (idpedcompras_contas)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
			$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela de pedidos de compras contas";
		}

		private function versao_01_00(){
			//
			// 06/07/2021 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS pedcompras_itens(
						idpedcompras_itens int(11) NOT NULL AUTO_INCREMENT,
						pcit_idpedcompras int NULL,
						pcit_idprodutos int NULL,
						pcit_qte DECIMAL(10,2) NOT NULL DEFAULT 1,
						pcit_vlr_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
						pcit_porc_desconto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
						pcit_valor_desconto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
						pcit_total_item DECIMAL(10,2) AS (pcit_qte * pcit_vlr_unitario - pcit_valor_desconto) STORED,
						pcit_unidade_sigla VARCHAR(25) NULL,
						PRIMARY KEY (idpedcompras_itens)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
			$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela de pedidos de compras itens";
		}

		private function versao_00_99(){
			//
			// 06/07/2021 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS pedcompras(
						idpedcompras int(11) NOT NULL AUTO_INCREMENT,
						pcom_idfornecedor int NULL,
						pcom_abertura DATETIME NULL,
						pcom_fechamento DATETIME NULL,
						pcom_situacao VARCHAR(100) NULL,
						pcom_idforma_pagto int NULL,
						pcom_idmeio_pagto int NULL,
						pcom_idbancos int NULL,
						pcom_idcc int NULL,
						pcom_idempresas int NULL,
						pcom_idtipo_contas int NULL,
						pcom_obs TEXT NULL,
						pcom_qte_parcelas int NULL,
						pcom_total_produtos DECIMAL(10,2) NOT NULL DEFAULT 0.00,
						pcom_frete DECIMAL(10,2) NULL,
						pcom_porc_desconto DECIMAL(10,2) NULL,
						pcom_valor_desconto DECIMAL(10,2) NULL,
						pcom_total_pedido DECIMAL(10,2) AS (pcom_total_produtos + pcom_frete - pcom_valor_desconto) STORED,
						pcom_com_entrada VARCHAR(10) NULL,
						PRIMARY KEY (idpedcompras)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
			$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela de pedidos de compras";
		}

		private function versao_00_98(){
			//
			// 20/05/2023 Vinicius
			//
			$sql = "ALTER TABLE contarec ADD ctrc_parcela varchar(50) NULL";
			$this->db->executaSQL($sql); 
			//
			$sql = "ALTER TABLE contapag ADD ctpg_parcela varchar(50) NULL";
			$this->db->executaSQL($sql); 
			//
			$sql = "ALTER TABLE pedidos_contas ADD pcon_parcela varchar(50) NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação de campo para gravar a parcela da conta";
		}

		private function versao_00_97(){
			//
			// 20/05/2023 Vinicius
			//
			$sql = "ALTER TABLE contarec ADD ctrc_idpedidos int(11) NULL";
			$this->db->executaSQL($sql); 
			//
			$sql = "ALTER TABLE contapag ADD ctpg_idpedcompras int(11) NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação de campo para gravar o id dos pedidos nas contas";
		}

		private function versao_00_96(){
			//
			// 19/03/2023 Vinicius
			//
			$this->parametros->cadastraParametros("pedidos: baixa estoque no fechamento", "NAO", "Parametro usado para definir se no fechamento do pedido irá baixar o estoque dos produtos vendidos"); 
			//
			//Mensagem para o usuario
			return "Criação de parametros para baixar estoque na venda";
		}

		private function versao_00_95(){
			//
			// 28/11/2022 Vinicius
			//
			$sql = "ALTER TABLE pedidos ADD ped_com_entrada VARCHAR(10) NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação do campo para definir se o pedido é com ou sem entrada";
		}

		private function versao_00_94(){
			//
			// 28/11/2022 Vinicius
			//
			$sql = "ALTER TABLE pedidos_itens ADD peit_unidade_sigla VARCHAR(25) NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação do campo com a sigla da unidade no item do pedido para guardar a informação";
		}

		private function versao_00_93(){
			//
			// 25/11/2022 Vinicius
			//
			$this->parametros->cadastraParametros("sistema: senha do email usado para contato do sistema", "", "Parametro usado para definir a senha do email pelo qual o sistema enviará as notificações"); 
			$this->parametros->cadastraParametros("sistema: host smtp", "", "Parametro usado para definir o endereço do serviço smtp"); 
			//
			//Mensagem para o usuario
			return "Criação de parametros para configuração de email";
		}

		private function versao_00_92(){
			//
			// 25/11/2022 Vinicius
			//
			$this->parametros->cadastraParametros("sistema: email usado para contato do sistema", "", "Parametro usado para definir o email pelo qual o sistema enviará as notificações"); 
			$this->parametros->cadastraParametros("sistema: email para o financeiro", "", "Parametro usado para definir o email que receberá alertas do financeiro"); 
			//
			//Mensagem para o usuario
			return "Criação de parametros para configuração de email";
		}

		private function versao_00_91(){
			//
			// 09/10/2021 Vinicius
			//
			$this->cadastraPrograma("pessoas_recibo_cesta.php", 'Impressões');
			//
			//Mensagem para o usuario
			return "Cadastro de prgramas para recibo de cesta";
		}

		private function versao_00_90(){
			//
			// 22/09/2022 Vinicius
			//
			$sql = "CREATE TRIGGER pedidos_itens_insert
						AFTER INSERT
						ON pedidos_itens
						FOR EACH ROW
							BEGIN
								UPDATE pedidos set ped_total_produtos = (ped_total_produtos + NEW.peit_total_item) WHERE idpedidos = NEW.peit_idpedidos;
							END";
			$this->db->executaSQL($sql);
			//
			$sql = "CREATE TRIGGER pedidos_itens_update
						AFTER UPDATE
						ON  pedidos_itens
						FOR EACH ROW
							BEGIN
								UPDATE pedidos set ped_total_produtos = (ped_total_produtos - OLD.peit_total_item) WHERE idpedidos = OLD.peit_idpedidos;
								UPDATE pedidos set ped_total_produtos = (ped_total_produtos + NEW.peit_total_item) WHERE idpedidos = NEW.peit_idpedidos;
							END";
			$this->db->executaSQL($sql);
			//
			$sql = "CREATE TRIGGER pedidos_itens_delete
						AFTER delete
						ON  pedidos_itens
						FOR EACH ROW
							BEGIN
								UPDATE pedidos set ped_total_produtos = (ped_total_produtos - OLD.peit_total_item) WHERE idpedidos = OLD.peit_idpedidos;
							END";
			$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da TRIGGER para gerar os valores de produtos no pedido";
		}

		private function versao_00_89(){
			//
			// 30/11/2021 Vinicius
			//
			$sql = "ALTER TABLE pessoas ADD pess_idempresas INT NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação do campo com o codigo da empresa na tabela de pessoas";
		}

		private function versao_00_88(){
			//
			// 30/11/2021 Vinicius
			//
			$sql = "ALTER TABLE pedidos_itens CHANGE COLUMN peit_total_pedido peit_total_item DECIMAL(10,2) AS (peit_qte * peit_vlr_unitario - peit_valor_desconto) STORED";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Corrção do campo na tabela de itens do pedido";
		}

		private function versao_00_87(){
			//
			// 30/11/2021 Vinicius
			//
			$sql = "ALTER TABLE contapag ADD ctpg_idsalarios_funcionarios INT NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação do campo com o codigo do salario na tabela de contas a pagar";
		}

		private function versao_00_86(){
			//
			// 24/11/2021 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS pedidos_contas(
						idpedidos_contas int(11) NOT NULL AUTO_INCREMENT,
						pcon_idpedidos int NULL,
						pcon_idcontarec int NULL,
						pcon_idmeio_pagto int NULL,
						pcon_idbancos int NULL,
						pcon_idcc int NULL,
						pcon_idempresas int NULL,
						pcon_idtipo_contas int NULL,
						pcon_vencimento_dias INT NOT NULL,
						pcon_vencimento DATE NOT NULL,
						pcon_valor DECIMAL(10,2) NOT NULL DEFAULT 0.00,
						PRIMARY KEY (idpedidos_contas)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
			$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela de pedidos contas";
		}

		private function versao_00_85(){
			//
			// 24/11/2021 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS pedidos_itens(
						idpedidos_itens int(11) NOT NULL AUTO_INCREMENT,
						peit_idpedidos int NULL,
						peit_idprodutos int NULL,
						peit_qte DECIMAL(10,2) NOT NULL DEFAULT 1,
						peit_vlr_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
						peit_porc_desconto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
						peit_valor_desconto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
						peit_total_pedido DECIMAL(10,2) AS (peit_qte * peit_vlr_unitario - peit_valor_desconto) STORED,
						PRIMARY KEY (idpedidos_itens)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
			$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela de pedidos itens";
		}

		private function versao_00_84(){
			//
			// 20/08/2021 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS pedidos(
						idpedidos int(11) NOT NULL AUTO_INCREMENT,
						ped_idcliente int NULL,
						ped_abertura DATETIME NULL,
						ped_fechamento DATETIME NULL,
						ped_situacao VARCHAR(100) NULL,
						ped_idforma_pagto int NULL,
						ped_idmeio_pagto int NULL,
						ped_idbancos int NULL,
						ped_idcc int NULL,
						ped_idempresas int NULL,
						ped_idtipo_contas int NULL,
						ped_obs TEXT NULL,
						ped_qte_parcelas int NULL,
						ped_total_produtos DECIMAL(10,2) NOT NULL DEFAULT 0.00,
						ped_frete DECIMAL(10,2) NULL,
						ped_porc_desconto DECIMAL(10,2) NULL,
						ped_valor_desconto DECIMAL(10,2) NULL,
						ped_total_pedido DECIMAL(10,2) AS (ped_total_produtos + ped_frete - ped_valor_desconto) STORED,
						PRIMARY KEY (idpedidos)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
			$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela de pedidos";
		}

		private function versao_00_83(){
			//
			// 04/08/2021 Vinicius
			//
			$sql = "ALTER TABLE empresas ADD emp_logo_relatorio VARCHAR(10) NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação de dos campos que define se a empresa usa apenas a logo nos relatórios";
		}

		private function versao_00_82(){
			//
			// 09/07/2021 Vinicius
			//
			$sql = "ALTER TABLE contarec ADD ctrc_idempresas INT NULL";
			$this->db->executaSQL($sql); 
			//
			$sql = "ALTER TABLE contapag ADD ctpg_idempresas INT NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação de dos campos de código da empresa nas contas a receber e a pagar";
		}

		
		private function versao_00_81(){
			//
			// 09/07/2021 Vinicius
			//
			$this->parametros->cadastraParametros("sistema: codigo da empresa padrao", "", "Parametro usado para definir o código da empresa padrão no sistema", "constante", "CODIGO_EMPRESA"); 
			//
			//Mensagem para o usuario
			return "Criação de parametros para exibir assinatura no fim do relatório de coleta";
		}

		private function versao_00_80(){
			//
			// 09/07/2021 Vinicius
			//
			$this->parametros->cadastraParametros("sistema: relatorio coleta exibe assinatura", "NAO", "Parametro usado para definir se o sistema deverá exibir a assinatura no fim da coleta"); 
			$this->parametros->cadastraParametros("sistema: relatorio coleta texto para assinatura", "", "Parametro usado para definir o texto embaixo do campo de assinatura na coleta"); 
			$this->parametros->cadastraParametros("sistema: relatorio coleta assinatura digital", "", "Parametro usado para definir a imagem da assinatura no final da coleta (se deixado em branco aparecerá uma linha para assinatura manual)"); 
			//
			//Mensagem para o usuario
			return "Criação de parametros para exibir assinatura no fim do relatório de coleta";
		}

		private function versao_00_79(){
			//
			// 10/05/2021 Vinicius
			//
			$this->cadastraPrograma("empresas_edita.php", 'Cadastros', 'Empresas',  'menu', '', 'cadastros', 0, '_Cadastros');
			$this->cadastraPrograma("empresas_grava.php", 'Cadastros');
			//
			//Mensagem para o usuario
			return "Criação do programa para incluir dados das empresas";
		}

		private function versao_00_78(){
			//
			// 09/06/2021 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS empresas(
						idempresas int(11) NOT NULL AUTO_INCREMENT,
						emp_nome VARCHAR(255) NOT NULL,
						emp_cnpj varchar(255) NULL,
						emp_endereco varchar(255) NULL,
						emp_cep varchar(255) NULL,
						emp_telefone varchar(255) NULL,
						emp_logo varchar(255) NULL,
						emp_idcidades int NULL,
						PRIMARY KEY (idempresas)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
			$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela empresas";
		}

		private function versao_00_77(){
			//
			// 31/05/2021 Vinicius
			//
			$this->cadastraPrograma("coleta_sel.php", 'Relatório', 'Coletas',  'menu', '', 'relatorios', 0, '_Relatorios');
			$this->cadastraPrograma("coleta.php", 'Relatório');
			//
			//Mensagem para o usuario
			return "Criação do programa de relatórios de coletas";
		}

		private function versao_00_76(){
			//
			// 31/05/2021 Vinicius
			//
			$sql = "ALTER TABLE grupos_acessos ADD grac_menu_relatorios TEXT NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação do campo para o menu relatórios";
		}

		private function versao_00_75(){
			//
			// 11/05/2021 Vinicius
			//
			$sql = "ALTER TABLE coleta ADD cole_placa_veiculo VARCHAR(255) NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Adicionado o campo placa do veiculo para as coletas";
		}

		private function versao_00_74(){
			//
			// 10/05/2021 Vinicius
			//
			$sql = "ALTER TABLE produtos_movto ADD prmv_idtipo_movto_estoque int NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Adicionado o campo tipo de movimentação de estoque para movimentações manuais";
		}

		private function versao_00_73(){
			//
			// 10/05/2021 Vinicius
			//
			$this->cadastraPrograma("coleta_edita.php", 'Lançamentos', 'Coletas',  'menu', '', 'lancamentos', 0, '_Lancamentos');
			$this->cadastraPrograma("coleta_grava.php", 'Lançamentos');
			//
			//Mensagem para o usuario
			return "Criação do programa para incluir coletas";
		}

		private function versao_00_72(){
			//
			// 10/05/2021 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS coleta(
						idcoleta int(11) NOT NULL AUTO_INCREMENT,
						cole_data_entrada date NULL,
						cole_situacao varchar(255) NOT NULL DEFAULT 'Aberta',
						cole_idprodutos int NOT NULL,
						cole_qte decimal(10,2) NULL,
						PRIMARY KEY (idcoleta)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
					$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela coletas";
		}

		private function versao_00_71(){
			//
			// 10/05/2021 Vinicius
			//
			$this->cadastraPrograma("tipo_movto_estoque_edita.php", 'Cadastros', 'Tipo Estoque',  'menu', '', 'cadastros', 0, '_Cadastros');
			$this->cadastraPrograma("tipo_movto_estoque_grava.php", 'Cadastros');
			//
			//Mensagem para o usuario
			return "Criação do programa para incluir tipo de movimentação no estoque";
		}

		private function versao_00_70(){
			//
			// 10/05/2021 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS tipo_movto_estoque(
						idtipo_movto_estoque int(11) NOT NULL AUTO_INCREMENT,
						time_nome VARCHAR(255) NULL,
						time_entrada VARCHAR(6) NULL,
						time_saida VARCHAR(6) NULL,
						PRIMARY KEY (idtipo_movto_estoque)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
					$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela tipo de movimento de estoque";
		}

		private function versao_00_69(){
			//
			// 29/06/2020 Vinicius
			//
			$this->cadastraPrograma("contapag_recibo.php", 'Impressões');
			$this->cadastraPrograma("contarec_recibo.php", 'Impressões');
			//
			//Mensagem para o usuario
			return "Cadastro dos programas para imprimir recibos de contas";
		}

		private function versao_00_68(){
			//
			// 29/06/2020 Vinicius
			//
			$sql = "ALTER TABLE salarios_funcionarios CHANGE COLUMN safu_dias safu_dias DECIMAL(10,2) NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Alterando o campo faltas para receber meia falta no salario";
		}

		private function versao_00_67(){
			//
			// 29/06/2020 Vinicius
			//
			$this->cadastraPrograma("contapag_recibo_associado.php", 'Impressões');
			//
			//Mensagem para o usuario
			return "Cadastro do programa para imprimir recibo de associado";
		}

		private function versao_00_66(){
			//
			// 22/04/2021 Vinicius
			//
			$sql = "ALTER TABLE contapag ADD ctpg_idconta_salario int NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação do campo conta salario na tabela contas a pagar";
		}

		private function versao_00_65(){
			//
			// 18/04/2021 Vinicius
			//
			$this->parametros->cadastraParametros("sistema: recalcula contas tipo salario", "NAO", "Parametro usado para definir se o sistema deverá recalcular contas que sejam do tipo salario já baixadas"); 
			//
			//Mensagem para o usuario
			return "Criação de parametros para recalcular conta salario";
		}

		private function versao_00_64(){
			//
			// 16/04/2021 Vinicius
			//
			$this->parametros->cadastraParametros("sistema: incluir contas do tipo salario já quitadas", "NAO", "Parametro usado para definir se o sistema deverá incluir contas que sejam do tipo salario já baixadas"); 
			//
			//Mensagem para o usuario
			return "Criação de parametros para incluir conta salario ja quitada";
		}

		private function versao_00_63(){
			//
			// 15/04/2021 Vinicius
			//
			$this->parametros->cadastraParametros("sistema: codigo do tipo da conta padrao para salarios", "", "Parametro usado para definir id padrão do tipo da conta a ser usado em contas de salario geradas pelo sistema"); 
			$this->parametros->cadastraParametros("sistema: codigo do banco padrao para contas", "", "Parametro usado para definir id padrão do banco a ser usado em contas geradas pelo sistema"); 
			$this->parametros->cadastraParametros("sistema: codigo da conta bancaria padrao para contas", "", "Parametro usado para definir id padrão da conta bancaria a ser usado em contas geradas pelo sistema"); 
			$this->parametros->cadastraParametros("sistema: codigo do meio de pagamento padrao para contas", "", "Parametro usado para definir id padrão do meio de pagamento a ser usado em contas geradas pelo sistema"); 
			$this->parametros->cadastraParametros("sistema: feriados a ser considerados", "2021-01-01,2021-02-15,2021-02-16,2021-04-02,2021-04-21,2021-05-01,2021-06-03,2021-09-07,2021-10-12,2021-11-02,2021-11-15,2021-15-25", "Parametro usado para definir os feriados que o sistema deve considerar na hora de efetuar calculos", "variavel", "FERIADOS"); 
			//
			//Mensagem para o usuario
			return "Criação de parametros para criação de contas e parametro para registrar feriados";
		}

		private function versao_00_62(){
			//
			// 15/04/2021 Vinicius
			//
			$sql = "ALTER TABLE salarios ADD sala_data_fechamento DATETIME NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação do campo data de fechamento na tabela salarios";
		}

		private function versao_00_61(){
			//
			// 15/04/2021 Vinicius
			//
			$sql = "ALTER TABLE salarios ADD sala_mes INT NOT NULL";
			$this->db->executaSQL($sql); 
			//
			$sql = "ALTER TABLE salarios ADD sala_ano INT NOT NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação do campo mes e ano na tabela salarios";
		}

		private function versao_00_60(){
			//
			// 04/10/2020 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS salarios_funcionarios(
						idsalarios_funcionarios int(11) NOT NULL AUTO_INCREMENT,
						safu_idsalarios INT NULL,
						safu_idpessoas INT NULL,
						safu_dias INT NULL,
						safu_vlr_desconto_faltas DECIMAL(10,2) NULL,
						safu_total DECIMAL(10,2) NULL,
						safu_idcontapag INT NULL,
						PRIMARY KEY (idsalarios_funcionarios)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
					$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela salarios de funcionarios";
		}

		private function versao_00_59(){
			//
			// 04/10/2020 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS salarios(
						idsalarios int(11) NOT NULL AUTO_INCREMENT,
						sala_data DATETIME NOT NULL,
						sala_vlr_total DECIMAL(10,2) NULL,
						sala_situacao VARCHAR(100) NULL,
						PRIMARY KEY (idsalarios)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
					$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela salarios";
		}

		private function versao_00_58(){
			//
			// 07/10/2020 Vinicius
			//
			$this->cadastraPrograma("salarios_edita.php", 'Lançamentos', 'Salários',  'menu', '', 'lancamentos', 0, '_Lancamentos');
			$this->cadastraPrograma("salarios_grava.php", 'Lançamentos');
			//
			//Mensagem para o usuario
			return "Criação do programa para incluir salarios";
		}

		private function versao_00_57(){
			//
			// 24/09/2020 Vinicius
			//
			$sql = "ALTER TABLE contapag ADD ctpg_recalculou VARCHAR(6) NULL";
			$this->db->executaSQL($sql); 
			//
			$sql = "ALTER TABLE contapag ADD ctpg_processou VARCHAR(6) NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação do campo recalculou a conta e processor o vale/extra na tabela contas a pagar";
		}

		private function versao_00_56(){
			//
			// 04/10/2020 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS contapag_hist(
						idcontapag_hist int(11) NOT NULL AUTO_INCREMENT,
						cphi_idcontapag int(11) NOT NULL,
						cphi_operacao VARCHAR(255) NOT NULL,
						cphi_data DATETIME NOT NULL,
						cphi_valor DECIMAL(10,2) NULL,
						cphi_idoperador int(11) NOT NULL,
						cphi_data_pagto DATE NULL,
						cphi_idmeio_pagto INT NULL,
						cphi_idcc INT NULL,
						PRIMARY KEY (idcontapag_hist)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
					$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela contapag_hist";
		}

		private function versao_00_55(){
			//
			// 04/10/2020 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS contapag(
						idcontapag int(11) NOT NULL AUTO_INCREMENT,
						ctpg_idcliente int(11) NOT NULL,
						ctpg_vencimento DATE NOT NULL,
						ctpg_vlr_bruto decimal(10,2) NOT NULL,
						ctpg_vlr_desconto decimal(10,2) DEFAULT 0.00,
						ctpg_vlr_juros decimal(10,2) DEFAULT 0.00,
						ctpg_vlr_liquido DECIMAL(10,2) AS (ctpg_vlr_bruto + ctpg_vlr_juros - ctpg_vlr_desconto) STORED,
						ctpg_vlr_pago decimal(10,2) DEFAULT 0.00,
						ctpg_vlr_devedor DECIMAL(10,2) AS (ctpg_vlr_liquido - ctpg_vlr_pago) STORED,
						ctpg_a_vista VARCHAR(6) NULL,
						ctpg_porc_juros DECIMAL(10,2) DEFAULT 0.00,
						ctpg_porc_desconto DECIMAL(10,2) DEFAULT 0.00,
						ctpg_idmeio_pagto INT NULL,
						ctpg_idcc INT NULL,
						ctpg_idbancos INT NULL,
						ctpg_situacao VARCHAR(255) NULL,
						ctpg_inclusao DATE NULL,
						ctpg_idtipo_contas int(11) NULL,
						PRIMARY KEY (idcontapag)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
					$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela contapag";
		}

		private function versao_00_54(){
			//
			// 24/09/2020 Vinicius
			//
			$sql = "ALTER TABLE contarec ADD ctrc_a_vista VARCHAR(6) NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação do campo à vista na tabela contas a receber";
		}

		private function versao_00_53(){
			//
			// 27/09/2020 Vinicius
			//
			$this->parametros->cadastraParametros("sistema: incluir contas a vista já quitadas", "NAO", "Parametro usado para definir se o sistema deverá incluir contas à vista já baixadas"); 
			//
			//Mensagem para o usuario
			return "Criação do parametro para criar contas à vista já quitadas";
		}

		private function versao_00_52(){
			//
			// 24/09/2020 Vinicius
			//
			$sql = "ALTER TABLE cc_lanctos ADD ccla_obs TEXT NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação do campo observações na tabela lançamentos bancários";
		}

		private function versao_00_51(){
			//
			// 27/09/2020 Vinicius
			//
			//$this->parametros->cadastraParametros("sistema: incluir contas do tipo salario já quitadas", "NAO", "Parametro usado para definir se o sistema deverá incluir contas que sejam do tipo salario já baixadas"); 
			//
			//Mensagem para o usuario
			return "Criação do parametro para criar contas salarios já quitadas (Versão removida)";
		}

		private function versao_00_50(){
			//
			// 24/09/2020 Vinicius
			//
			$sql = "ALTER TABLE tipo_contas ADD tico_tipo_salario VARCHAR(6) NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação do campo tipo salario na tabela tipode de contas";
		}

		private function versao_00_49(){
			//
			// 24/09/2020 Vinicius
			//
			$sql = "ALTER TABLE cc_lanctos ADD ccla_inclusao DATETIME NULL";
			$this->db->executaSQL($sql); 
			//
			$sql = "ALTER TABLE cc_lanctos ADD ccla_idmeio_pagto INT NULL";
			$this->db->executaSQL($sql); 
			//
			$sql = "ALTER TABLE cc_lanctos ADD ccla_idoperador INT NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação dos campos inclusão, meio de pagamento e operador na tabela lançamentos bancários";
		}
		
		private function versao_00_48(){
			//
			// 27/09/2020 Vinicius
			//
			$this->parametros->cadastraParametros("sistema: gerar lancamentos bancarios pelas contas", "NAO", "Parametro usado para definir se o sistema deverá gerar lançamentos bancários ao baixar contas"); 
			//
			//Mensagem para o usuario
			return "Criação do parametro para gerar lançamentos bancarios";
		}

		private function versao_00_47(){
			//
			// 27/09/2020 Vinicius
			//
			$sql = "ALTER TABLE contarec ADD ctrc_idmeio_pagto INT NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação do campo meio de pagamento na tabela contas a receber";
		}

		private function versao_00_46(){
			//
			// 27/09/2020 Vinicius
			//
			$sql = "ALTER TABLE contarec ADD ctrc_porc_desconto DECIMAL(10,2) DEFAULT 0.00";
			$this->db->executaSQL($sql); 
			//
			$sql = "ALTER TABLE contarec ADD ctrc_porc_juros DECIMAL(10,2) DEFAULT 0.00";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação dos campos porcentagem de juros e desconto na tabela contas a receber";
		}

		private function versao_00_45(){
			//
			// 27/09/2020 Vinicius
			//
			$sql = "ALTER TABLE contarec_hist ADD crhi_idcc INT NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação do campo contas bancárias na tabela contas a receber historico";
		}

		private function versao_00_44(){
			//
			// 27/09/2020 Vinicius
			//
			$sql = "ALTER TABLE contarec ADD ctrc_idbancos INT NULL";
			$this->db->executaSQL($sql); 
			//
			$sql = "ALTER TABLE contarec ADD ctrc_idcc INT NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação dos campos banco e conta bancárias na tabela contas a receber";
		}

		private function versao_00_43(){
			//
			// 27/09/2020 Vinicius
			//
			$sql = "ALTER TABLE contarec_hist ADD crhi_idmeio_pagto INT NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação do campo meio de pagamento na tabela contas a receber historico";
		}

		private function versao_00_42(){
			//
			// 27/09/2020 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS cc_lanctos(
						idcc_lanctos int(11) NOT NULL AUTO_INCREMENT,
						ccla_idcc INT NOT NULL,
						ccla_data DATE NULL,
						ccla_tipo VARCHAR(10) NULL,
						ccla_valor DECIMAL(10,2) NULL,
						ccla_idcontapag INT NULL,
						ccla_idcontarec INT NULL,
						PRIMARY KEY (idcc_lanctos)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
					$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela lançamentos bancárias";
		}

		private function versao_00_41(){
			//
			// 27/09/2020 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS cc(
						idcc int(11) NOT NULL AUTO_INCREMENT,
						cc_nome VARCHAR(255) NOT NULL,
						cc_idbancos INT NULL,
						cc_agencia VARCHAR(100) NULL,
						cc_agencia_dg VARCHAR(20) NULL,
						cc_conta VARCHAR(100) NULL,
						cc_conta_dg VARCHAR(20) NULL,
						PRIMARY KEY (idcc)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
					$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela contas bancárias";
		}

		private function versao_00_40(){
			//
			// 27/09/2020 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS bancos(
						idbancos int(11) NOT NULL AUTO_INCREMENT,
						banc_nome VARCHAR(255) NOT NULL,
						PRIMARY KEY (idbancos)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
					$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela bancos";
		}

		private function versao_00_39(){
			//
			// 27/09/2020 Vinicius
			//
			$this->cadastraPrograma("bancos_edita.php", 'Cadastros', 'Bancos',  'menu', '', 'cadastros', 0, '_Cadastros');
			$this->cadastraPrograma("bancos_grava.php", 'Cadastros');
			//
			$this->cadastraPrograma("cc_edita.php", 'Cadastros', 'Contas Bancárias',  'menu', '', 'cadastros', 0, '_Cadastros');
			$this->cadastraPrograma("cc_grava.php", 'Cadastros');
			//
			//Mensagem para o usuario
			return "Cadastro dos programas de bancos, contas bancárias e suas dependencias";
		}

		private function versao_00_38(){
			//
			// 24/09/2020 Vinicius
			//
			$sql = "ALTER TABLE contarec ADD ctrc_inclusao DATE NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação do campo inclusão na tabela contas a receber";
		}

		private function versao_00_37(){
			//
			// 23/09/2020 Vinicius
			//
			$sql = "ALTER TABLE contarec_hist ADD crhi_data_pagto DATE NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação do campo data de pagamento na tabela contas a receber historico";
		}

		private function versao_00_36(){
			//
			// 23/09/2020 Vinicius
			//
			$sql = "ALTER TABLE contarec ADD ctrc_situacao VARCHAR(255) NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação do campo situacao na tabela contas a receber";
		}

		private function versao_00_35(){
			//
			// 23/09/2020 Vinicius
			//
			$sql = "ALTER TABLE tipo_contas ADD tico_tipo_extra VARCHAR(6) NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação do campo tipo extra na tabela de tipo de contas";
		}

		private function versao_00_34(){
			//
			// 23/09/2020 Vinicius
			//
			$sql = "ALTER TABLE tipo_contas ADD tico_tipo_vale VARCHAR(6) NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação do campos tipo vale na tabela de tipo de contas";
		}

		private function versao_00_33(){
			//
			// 23/09/2020 Vinicius
			//
			$this->cadastraPrograma("tipo_contas_edita.php", 'Cadastros', 'Tipo de Contas',  'menu', '', 'cadastros', 0, '_Cadastros');
			$this->cadastraPrograma("tipo_contas_grava.php", 'Cadastros');
			//
			//Mensagem para o usuario
			return "Cadastro dos programas tipo de contas e suas dependencias";
		}

		private function versao_00_32(){
			//
			// 20/09/2020 Vinicius
			//
			$this->cadastraPrograma("contarec_edita.php", 'Lançamentos', 'Contas a Receber',  'menu', '', 'lancamentos', 0, '_Lancamentos');
			$this->cadastraPrograma("contarec_grava.php", 'Lançamentos');
			//
			$this->cadastraPrograma("contapag_edita.php", 'Lançamentos', 'Contas a Pagar',  'menu', '', 'lancamentos', 0, '_Lancamentos');
			$this->cadastraPrograma("contapag_grava.php", 'Lançamentos');
			//
			$this->cadastraPrograma("pedidos_edita.php", 'Lançamentos', 'Pedidos',  'menu', '', 'lancamentos', 0, '_Lancamentos');
			$this->cadastraPrograma("pedidos_grava.php", 'Lançamentos');
			//
			$this->cadastraPrograma("pedcompras_edita.php", 'Lançamentos', 'Pedidos de Compra',  'menu', '', 'lancamentos', 0, '_Lancamentos');
			$this->cadastraPrograma("pedcompras_grava.php", 'Lançamentos');
			//
			//Mensagem para o usuario
			return "Cadastro dos programas de lançamentos de contarec, contapag, pedidos e pedcompras e suas dependencias";
		}

		private function versao_00_31(){
			//
			// 15/09/2020 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS tipo_contas(
						idtipo_contas int(11) NOT NULL AUTO_INCREMENT,
						tico_nome VARCHAR(255) NOT NULL,
						PRIMARY KEY (idtipo_contas)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
					$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela tipo_contas";
		}

		private function versao_00_30(){
			//
			// 20/09/2020 Vinicius
			//
			$sql = "ALTER TABLE contarec ADD ctrc_idtipo_contas int(11) NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação do campos tipo da conta na tabela de contas a receber";
		}

		private function versao_00_29(){
			//
			// 15/09/2020 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS contarec_hist(
						idcontarec_hist int(11) NOT NULL AUTO_INCREMENT,
						crhi_idcontarec int(11) NOT NULL,
						crhi_operacao VARCHAR(255) NOT NULL,
						crhi_data DATETIME NOT NULL,
						crhi_valor DECIMAL(10,2) NULL,
						crhi_idoperador int(11) NOT NULL,
						PRIMARY KEY (idcontarec_hist)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
					$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela contarec_hist";
		}

		private function versao_00_28(){
			//
			// 15/09/2020 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS contarec(
						idcontarec int(11) NOT NULL AUTO_INCREMENT,
						ctrc_idcliente int(11) NOT NULL,
						ctrc_vencimento DATE NOT NULL,
						ctrc_vlr_bruto decimal(10,2) NOT NULL,
						ctrc_vlr_desconto decimal(10,2) DEFAULT 0.00,
						ctrc_vlr_juros decimal(10,2) DEFAULT 0.00,
						ctrc_vlr_liquido DECIMAL(10,2) AS (ctrc_vlr_bruto + ctrc_vlr_juros - ctrc_vlr_desconto) STORED,
						ctrc_vlr_pago decimal(10,2) DEFAULT 0.00,
						ctrc_vlr_devedor DECIMAL(10,2) AS (ctrc_vlr_liquido - ctrc_vlr_pago) STORED,
						PRIMARY KEY (idcontarec)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
					$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela contarec";
		}

		private function versao_00_27(){
			//
			// 29/06/2020 Vinicius
			//
			$this->cadastraPrograma("pessoas_ficha_associado.php", 'Impressões');
			//
			//Mensagem para o usuario
			return "Cadastro do programa para imprimir ficha de associado";
		}

		private function versao_00_26(){
			//
			// 29/06/2020 Vinicius
			//
			$this->parametros->cadastraParametros("empresa: nome da empresa", "", "Parametro usado para definir o nome da empresa"); 
			$this->parametros->cadastraParametros("empresa: cnpj da empresa", "", "Parametro usado para definir o cnpj da empresa"); 
			$this->parametros->cadastraParametros("empresa: endereco da empresa", "", "Parametro usado para definir o encereço da empresa"); 
			$this->parametros->cadastraParametros("empresa: cidade da empresa", "", "Parametro usado para definir o cidade da empresa"); 
			$this->parametros->cadastraParametros("empresa: uf da empresa", "", "Parametro usado para definir o uf da empresa"); 
			$this->parametros->cadastraParametros("empresa: CEP da empresa", "", "Parametro usado para definir o CEP da empresa"); 
			$this->parametros->cadastraParametros("empresa: telefone de contato da empresa", "", "Parametro usado para definir o telefone de contato da empresa"); 
			$this->parametros->cadastraParametros("sistema: nome da logo usada para relatorios", "", "Parametro usado para definir a logo que será usada nos relatórios"); 
			//
			//Mensagem para o usuario
			return "Criação de parametro para definir dados da empresa";
		}

		private function versao_00_25(){
			//
			// 27/06/2020 Vinicius
			//
			$sql = "ALTER TABLE pessoas ADD pess_idfuncoes int(11) NULL";
			$this->db->executaSQL($sql); 
			$sql = "ALTER TABLE pessoas ADD pess_idsetores int(11) NULL";
			$this->db->executaSQL($sql); 
			$sql = "ALTER TABLE pessoas ADD pess_associado varchar(5) NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação dos campos para associado, setor e função das pessoas";
		}

		private function versao_00_24(){
			//
			// 27/06/2020 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS setores(
				idsetores int(11) NOT NULL AUTO_INCREMENT,
				set_nome VARCHAR(255) NOT NULL,
				PRIMARY KEY (idsetores)
			)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
			$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela setores";
		}

		private function versao_00_23(){
			//
			// 27/06/2020 Vinicius
			//
			$this->cadastraPrograma("setores_edita.php", 'Cadastros', 'Setores',  'menu', '', 'cadastros', 0, '_Cadastros');
			$this->cadastraPrograma("setores_grava.php", 'Cadastros');
			//
			//Mensagem para o usuario
			return "Cadastro do programa cadastro de setores e suas dependencias";
		}

		private function versao_00_22(){
			//
			// 27/06/2020 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS funcoes(
				idfuncoes int(11) NOT NULL AUTO_INCREMENT,
				func_nome VARCHAR(255) NOT NULL,
				PRIMARY KEY (idfuncoes)
			)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
			$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela funções";
		}

		private function versao_00_21(){
			//
			// 26/06/2020 Vinicius
			//
			$this->cadastraPrograma("funcoes_edita.php", 'Cadastros', 'Funções',  'menu', '', 'cadastros', 0, '_Cadastros');
			$this->cadastraPrograma("funcoes_grava.php", 'Cadastros');
			//
			//Mensagem para o usuario
			return "Cadastro do programa cadastro de funcoes e suas dependencias";
		}

		private function versao_00_20(){
			//
			// 17/03/2020 Vinicius
			//
			$this->cadastraPrograma("grupos_acessos.php", 'Sistema', 'Grupos de Acessos',  'menu', '', 'configuracoes', 0, '_Configuracoes');
			$this->cadastraPrograma("grupos_acessos_grava.php", 'Sistema');
			//
			//Mensagem para o usuario
			return "Cadastro do programa grupos de acessos e suas dependencias";
		}
		
		private function versao_00_19(){
			//
			// 16/03/2020 Vinicius
			//
			$this->parametros->cadastraParametros("sistema: nome usado para o sistema", "vWeb", "Parametro usado para definir o nome usado para o sistema", "constante", "NOME_SISTEMA"); 
			//
			//Mensagem para o usuario
			return "Criação de parametro para definir a nome para o sistema";
		}

		private function versao_00_18(){
			//
			// 16/03/2020 Vinicius
			//
			$sql = "ALTER TABLE grupos_acessos ADD grac_inativo int(11) NULL";
			$this->db->executaSQL($sql); 
			//
			//Mensagem para o usuario
			return "Criação de campo para inativar grupo de acesso";
		}
		
		private function versao_00_17(){
			//
			// 24/02/2020 Vinicius
			//
			$this->parametros->cadastraParametros("sistema: nome da imagem para logo", "padrao.png", "Parametro usado para definir a logo do sistema", "constante", "LOGO_EMPRESA"); 
			//
			//Mensagem para o usuario
			return "Criação de parametro para definir a logo da empresa";
		}

		private function versao_00_16(){
			//
			// 24/02/2020 Vinicius
			//
			$this->parametros->cadastraParametros("sistema: busca atualizacoes automaticamente", "NAO", "Parametro usado para que o sistema se atualize sozinho", "parametro"); 
			//
			//Mensagem para o usuario
			return "Criação de parametro para atualizar o sistema automaticamente";
		}

		private function versao_00_15(){
			//
			// 20/02/2020 Vinicius
			//
			$this->parametros->cadastraParametros("sistema: data da ultima atualizacao", "", "Parametro usado para baixar novas versões do sistema", "parametro");
			$this->parametros->cadastraParametros("sistema: endereco do servidor ftp", "ftp.servidor.vfweb.cloud", "Parametro usado para se conectar no servidor ftp", "parametro"); 
			$this->parametros->cadastraParametros("sistema: usuario do servidor ftp", "u864966404.vf_atualizacoes", "Parametro usado para se conectar no servidor ftp", "parametro");
			$this->parametros->cadastraParametros("sistema: senha do servidor ftp", "R90mzM1IwTCw7T07li2c", "Parametro usado para se conectar no servidor ftp", "parametro"); 
			//
			//Mensagem para o usuario
			return "Criação de parametro de data da ultima atualização";
		}

		private function versao_00_14(){
			//
			// 02/02/2020 Vinicius
			//
			global $SERVIDOR;
			global $PORTA;
			global $USUARIO;
			global $SENHA;
			global $DB_NAME;

			$this->parametros->cadastraParametros("constante: endereco do banco de dados", $SERVIDOR, "Parametro usado para gerar o constante com os dados do banco de dados", "variavel", "SERVIDOR");
			$this->parametros->cadastraParametros("constante: porta do banco de dados", $PORTA, "Parametro usado para gerar o constante com os dados do banco de dados", "variavel", "PORTA");
			$this->parametros->cadastraParametros("constante: usuario do banco de dados", $USUARIO, "Parametro usado para gerar o constante com os dados do banco de dados", "variavel", "USUARIO");
			$this->parametros->cadastraParametros("constante: senha do banco de dados", $SENHA, "Parametro usado para gerar o constante com os dados do banco de dados", "variavel", "SENHA");
			$this->parametros->cadastraParametros("constante: nome da base do banco de dados", $DB_NAME, "Parametro usado para gerar o constante com os dados do banco de dados", "variavel", "DB_NAME");
			//
			//Mensagem para o usuario
			return "Criação de parametros com valores para conexão do banco";
		}
		
		private function versao_00_13(){
			//
			// 01/10/2019 Vinicius
			//
			$sql = "CREATE TRIGGER produtos_movto_insert 
						AFTER INSERT 
						ON produtos_movto
					   FOR EACH ROW
							BEGIN
								IF NEW.prmv_maismenos = '+' THEN
									 UPDATE produtos SET prod_qte_estoque = (prod_qte_estoque + NEW.prmv_qte) WHERE idprodutos = NEW.prmv_idprodutos; 
								END IF;
								
								IF NEW.prmv_maismenos = '-' THEN
									 UPDATE produtos SET prod_qte_estoque = (prod_qte_estoque - NEW.prmv_qte) WHERE idprodutos = NEW.prmv_idprodutos;
								END IF;	 												
					END";
			$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da TRIGGER para movimento de produtos";
		}

		private function versao_00_12(){
			//
			// 01/10/2019 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS produtos_movto(
						idprodutos_movto int(11) NOT NULL AUTO_INCREMENT,
						prmv_idprodutos int(11) NOT NULL,
						prmv_data datetime NOT NULL,
						prmv_idoperador int(11) NOT NULL,
						prmv_idorigem int(11) NOT NULL,
						prmv_origem varchar(255) NOT NULL,
						prmv_qte decimal(10,2) NULL,
						prmv_maismenos varchar(2) NULL,
						PRIMARY KEY (idprodutos_movto)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
			$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela produtos_movto";
		}

		private function versao_00_11(){
			//
			// 20/09/2019 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS producao_itens(
						idprodutcao_itens int(11) NOT NULL AUTO_INCREMENT,
						pdci_idproducao int(11) NOT NULL,
						pdci_idprodutos int(11) NOT NULL,
						pdci_qte decimal(10,2) NULL,
						pdci_perca decimal(10,2) NULL,
						pdci_qte_perca decimal(10,2) NULL,
						PRIMARY KEY (idprodutcao_itens)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
			$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela producao_itens";
		}

		private function versao_00_10(){
			//
			// 20/09/2019 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS producao(
						idproducao int(11) NOT NULL AUTO_INCREMENT,
						pdc_data_abertura datetime NULL,
						pdc_data_fechamento datetime NULL,
						pdc_situacao varchar(255) NOT NULL DEFAULT 'Aberta',
						pdc_idprodutos int NOT NULL,
						pdc_qte_produzida decimal(10,2) NULL,
						pdc_calcula_automatico varchar(5) NOT NULL DEFAULT 'SIM',
						PRIMARY KEY (idproducao)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
			$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela producao";
		}

		private function versao_00_09(){
			//
			// 18/09/2019 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS meio_pagto(
						idmeio_pagto int(11) NOT NULL AUTO_INCREMENT,
						mpag_nome varchar(255) NOT NULL,
						PRIMARY KEY (idmeio_pagto)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
			$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela meio_pagto";
		}

		private function versao_00_08(){
			//
			// 14/09/2019 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS forma_pagto(
						idforma_pagto int(11) NOT NULL AUTO_INCREMENT,
						forp_nome varchar(255) NOT NULL,
						forp_tipo varchar(255) NULL,
						forp_dias varchar(255) NULL,
						PRIMARY KEY (idforma_pagto)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
			$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela forma_pagto";
		}

		private function versao_00_07(){
			//
			// 02/09/2019 Vinicius
			//
			$this->parametros->cadastraParametros("empresa: permite trabalhar com estoque negativo", "NAO", "Parametro usado para bloquear operações de estoque quando o mesmo estiver zerado ou negativo", "parametro");
			$this->parametros->cadastraParametros("empresa: libera estoque negativo com senha", "NAO", "Parametro usado para permitir operações de estoque (negativo ou zerado) com senha", "parametro");
			$this->parametros->cadastraParametros("empresa: senha para liberacao do estoque", "senha não informada", "Senha usada para liberação do estoque quando negativo ou zerado", "parametro");
			//
			//Mensagem para o usuario
			return "Criação de parametros para manipulações de estoque";
		}

		private function versao_00_06(){
			//
			// 02/09/2019 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS produtos_formulas(
						idprodutos_formulas int(11) NOT NULL AUTO_INCREMENT,
						pfor_idproduto_final int(11) NOT NULL,
						pfor_idprodutos int(11) NOT NULL,
						pfor_qte decimal(10,2) NOT NULL,
						pfor_porc_perca decimal(10,2) NULL,
						PRIMARY KEY (idprodutos_formulas)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
			$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela produtos_formulas";
		}

		private function versao_00_05(){
			//
			// 29/08/2019 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS produtos(
						idprodutos int(11) NOT NULL AUTO_INCREMENT,
						prod_nome VARCHAR(255) NOT NULL,
						prod_idgrupos int(11) NULL,
						prod_idsubgrupos int(11) NULL,
						prod_idunidades int(11) NULL,
						prod_tipo_produto VARCHAR(255) NULL,
						prod_qte_estoque decimal(10,2) NOT NULL DEFAULT '0.00',
						prod_preco_tabela decimal(10,2) NULL,
						PRIMARY KEY (idprodutos)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
			$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela Produtos";
		}

		private function versao_00_04(){
			//
			// 21/08/2019 Vinicius
			//
			$this->parametros->cadastraParametros("sistema: data da ultima execucao de tarefas diarias", date('Y-m-d'), "Parametro usado para o sistema definir se deve ou não executar as tarefas diarias", "parametro");
			//
			//Mensagem para o usuario
			return "Cadastro do parametro para controle das tarefas diarias";
		}

		private function versao_00_03(){
			//
			// 21/08/2019 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS subgrupos(
						idsubgrupos int(11) NOT NULL AUTO_INCREMENT,
						subg_nome VARCHAR(255) NOT NULL,
						PRIMARY KEY (idsubgrupos)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
			$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela SubGrupos";
		}

		private function versao_00_02(){
			//
			// 21/08/2019 Vinicius
			//
			$sql = "CREATE TABLE IF NOT EXISTS grupos(
						idgrupos int(11) NOT NULL AUTO_INCREMENT,
						grup_nome VARCHAR(255) NOT NULL,
						PRIMARY KEY (idgrupos)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
			$this->db->executaSQL($sql);
			//
			//Mensagem para o usuario
			return "Criação da tabela Grupos";
		}

		public function baixaAtualizacao($manual = false){
			$nomeArquivoAtz = 'sistema_atz.zip';
			$nomeArquivoAtz_local = '../sistema_atz.zip';
			$nomeArquivoAtzInfo = 'atz_info.txt';
			$nomeArquivoAtzInfo_local = '../atz_info.txt';
			$ftp_server = $this->parametros->buscaValor("sistema: endereco do servidor ftp"); 
			$ftp_user_name = $this->parametros->buscaValor("sistema: usuario do servidor ftp");
			$ftp_user_pass = $this->parametros->buscaValor("sistema: senha do servidor ftp"); 
			//
			//
			$conexaoFTP = ftp_connect($ftp_server);
			if($conexaoFTP === false && $manual){
				echo "Erro ao se conectar com o servidor!";
				return;
			}
			//
			// login
			$resultLogin = ftp_login($conexaoFTP, $ftp_user_name, $ftp_user_pass);
			if($resultLogin === false && $manual){
				echo "Erro ao efetuar login no servidor!";
				return;
			}
			//
			//			   
			$modoPassivo = ftp_pasv($conexaoFTP, true);
			if($modoPassivo === false && $manual){
				echo "Erro ao ativar modo passivo no servidor!";
				return;
			}
			//
			//
			if (ftp_get($conexaoFTP, $nomeArquivoAtzInfo_local, $nomeArquivoAtzInfo, FTP_ASCII)) {
				$infoAtz = file_get_contents($nomeArquivoAtzInfo_local);
				$dataUltimaAttSite = substr($infoAtz, 20, 20);
				$dataUltimaAttSistema = $this->parametros->buscaValor("sistema: data da ultima atualizacao");
				if (strtotime($dataUltimaAttSistema) >= strtotime($dataUltimaAttSite)) {
					//
					//Deleta os arquivos da atualização
					unlink($nomeArquivoAtzInfo_local);
					if($manual) echo "O seu sistema já está na versão mais recente!";
					return;
				}
			}else{
				if($manual) echo "Erro ao baixar arquivo de informações da atualização!";
				return;
			}
			//
			//Testou a data e a do sistema esta menor, então tenta atualiza
			if (ftp_get($conexaoFTP, $nomeArquivoAtz_local, $nomeArquivoAtz, FTP_BINARY)) {
				//
				//Descompacta o arquivo e atualiza o sistema
				$zip = new ZipArchive();
				if ($zip->open($nomeArquivoAtz_local) === true) {
						$zip->extractTo('../');
						$zip->close();
						//
						//Deleta os arquivos da atualização
						unlink($nomeArquivoAtzInfo_local);
						unlink($nomeArquivoAtz_local);
				}else{
					if($manual) echo "Erro ao abrir arquivo de atualização!";
					return;
				}
				//
				//Atualiza a data de atualização.
				unset($dados);
				$dados['id'] 			= $this->util->sgr('sistema: data da ultima atualizacao');
				$dados['para_valor'] 	= $this->util->sgr(date('Y-m-d H:i:s'));
				$this->parametros->gravaValor($dados);
				if($manual) echo "Atualização finalizada!.<br>Obrigado!";
				return;
			}else {
				$erro = error_get_last();
				if($manual) echo "Erro ao baixar arquivos de atualização do sistema!";
				if($manual) echo "<span class='Obs_claro'>" . $erro['message'] . "</span>";
				return;
			}
		}

		public function gerarAtualizacao(){
			$nomeArquivoAtz = 'sistema_atz.zip';
			$nomeArquivoAtz_local = '../sistema_atz.zip';
			$nomeArquivoAtzInfo = 'atz_info.txt';
			$nomeArquivoAtzInfo_local = '../atz_info.txt';
			$ftp_server = $this->parametros->buscaValor("sistema: endereco do servidor ftp"); 
			$ftp_user_name = $this->parametros->buscaValor("sistema: usuario do servidor ftp");
			$ftp_user_pass = $this->parametros->buscaValor("sistema: senha do servidor ftp"); 
			//
			// Cria uma pasta compactada
			$zip = new ZipArchive;
			$zip->open($nomeArquivoAtz_local, ZipArchive::CREATE);
			//
			$dir = new DirectoryIterator('../');
			foreach($dir as $file){
				if(!$file->isDot()){
					if($file->isDir()){
						$caminho = $file->getPathname();
						//
						//abre o diretorio
						$ArquivosSub = new DirectoryIterator($caminho);
						//
						//troca a barra invertida
						$caminho = str_replace("\\", "/", $caminho);
						//
						//ignora as pastas do git e a uploads
						if(strpos($caminho, "git")) continue;
						if(strpos($caminho, "uploads")) continue;
						if(strpos($caminho, "instaladores")) continue;
						foreach($ArquivosSub as $file){
							if(!$file->isDot()){
								if($file->isDir()){
									$caminhoSub = $file->getPathname();
									//
									//abre o diretorio
									$ArquivosSub_Sub = new DirectoryIterator($caminhoSub);
									//
									//troca a barra invertida
									$caminhoSub = str_replace("\\", "/", $caminhoSub);
									foreach($ArquivosSub_Sub as $file_Sub){
										if(!$file_Sub->isDot()){
											if( $file_Sub->isFile()){
												//
												$fileName = $file_Sub->getFilename();
												//
												//Salva o nome para pegar o arquivo
												$arquivoLocal = $caminhoSub . "/" . $fileName;
												//
												//Salva o nome que sera no zip e remove o ../
												$arquivoZip = $caminhoSub . "/" . $fileName;
												$arquivoZip = str_replace("../", "", $arquivoZip);
												//
												//Inclui no zip
												$zip->addFile($arquivoLocal, $arquivoZip);
											}
										}
									}
								}
								if( $file->isFile()){
									//
									$fileName = $file->getFilename();
									//
									//Salva o nome para pegar o arquivo
									$arquivoLocal = $caminho . "/" . $fileName;
									//
									//ignora os constantes
									if(pathinfo($arquivoLocal, PATHINFO_EXTENSION) == 'vf') continue;
									//
									//Salva o nome que sera no zip e remove o ../
									$arquivoZip = $caminho . "/" . $fileName;
									$arquivoZip = str_replace("../", "", $arquivoZip);
									//
									//Inclui no zip
									$zip->addFile($arquivoLocal, $arquivoZip);
								}
							}
						}
					}
				}
				//
				// listando somente os arquivos do diretório
				if($file->isFile()){
					// atribui o nome do arquivo a variável
					$fileName = $file->getFilename();
					//
					//ignora os arquivos do git e o arquivo de log de erros e constantes
					if(strpos($fileName, "git")) continue;
					if(strpos($fileName, "README")) continue;
					if(strpos($fileName, "erro")) continue;
					if(strpos($fileName, "constantes")) continue;
					//
					//Salva o nome para pegar o arquivo
					$arquivoLocal = "../" . $fileName;
					//
					//Salva o nome que sera no zip e remove o ../
					$arquivoZip = $fileName;
					//
					//Inclui no zip
					$zip->addFile($arquivoLocal, $arquivoZip);

				}
			}
			//
			// Fecha o objeto. Necessário para gerar o arquivo zip final.
			$zip->close();
			//
			//Gera o arquivo de informações
			$arquivoAtzInfo = fopen($nomeArquivoAtzInfo_local, "a");
			//
			$textoArquivoAtzInfo = "Ultima Atualizacao: " . date("Y-m-d H:i:s") . "\n";
			$textoArquivoAtzInfo .= "Ultima versao: " . $this->getUltimaVersao() . "\n";
			$textoArquivoAtzInfo .= "-----------Dados do responsavel pela geracao desta versao-----------\n";
			$textoArquivoAtzInfo .= "Usuario: " . $this->db->retornaUmCampoID("pess_nome", "pessoas", $_SESSION['idusuario']) . "\n";
			$textoArquivoAtzInfo .= "Id do Usuario: " .$_SESSION['idusuario'] . "\n";
			$textoArquivoAtzInfo .= "Ip da maquina: " . $_SERVER["REMOTE_ADDR"] . "\n";
			//
			fwrite($arquivoAtzInfo, $textoArquivoAtzInfo);
			fclose($arquivoAtzInfo);
			//
			$conexaoFTP = ftp_connect($ftp_server);
			if($conexaoFTP === false){
				echo "Erro ao se conectar com o servidor!";
				return;
			}
			//
			// Defina o timeout para a conexão
			ftp_set_option($conexaoFTP, FTP_TIMEOUT_SEC, 600);
			//
			// login
			$resultLogin = ftp_login($conexaoFTP, $ftp_user_name, $ftp_user_pass);
			if($resultLogin === false){
				echo "Erro ao efetuar login no servidor!";
				return;
			}
			//
			if (!file_exists($nomeArquivoAtz_local)) {
				echo "O arquivo local não foi localizado: $nomeArquivoAtz_local";
				return;
			}
			//
			if (filesize($nomeArquivoAtz_local) === 0) {
				echo "O arquivo local está vazio: $nomeArquivoAtz_local";
				return;
			}
			//
			//Envia o arquivo de atualização
			$enviou = ftp_put($conexaoFTP, $nomeArquivoAtz, $nomeArquivoAtz_local, FTP_BINARY);
			if(!$enviou){
				//
				//Deleta os arquivos da atualização
				unlink($nomeArquivoAtzInfo_local);
				unlink($nomeArquivoAtz_local);
				//
				$erro = error_get_last();
				//
				echo "Erro ao enviar arquivo de atualização do sistema! <br>";
				echo "<span class='Obs_claro'>" . $erro['message'] . "</span>";
				return;
			}
			//
			//Envia o arquivo de informações	
			$enviou = ftp_put($conexaoFTP, $nomeArquivoAtzInfo, $nomeArquivoAtzInfo_local, FTP_ASCII);	
			if($enviou){
				//
				//Deleta os arquivos da atualização
				unlink($nomeArquivoAtzInfo_local);
				unlink($nomeArquivoAtz_local);
				//
				echo "Nova versão gerada e enviada ao servido com sucesso!<br>Obrigado!";	
				return;
			}else{
				//
				//Deleta os arquivos da atualização
				unlink($nomeArquivoAtzInfo_local);
				unlink($nomeArquivoAtz_local);
				//
				$erro = error_get_last();
				//
				echo "Erro ao enviar arquivo com informações sobre a atualização! <br>";	
				echo "<span class='Obs_claro'>" . $erro['message'] . "</span>";
				return;
			}   
		}

		private function cadastraPrograma($file, $tipo_origem = '', $nome = '', $tipo = 'programa', $imagem = '', $tipo_menu = '', $pode_executar = 0, $diretorioRaiz = '', $posicao = ''){
			//
			if($nome == '') $nome = $file;
			//
			$this->db->setTabela("programas", "idprogramas");
			//
			$dados['prog_nome']        	= $this->util->sgr($nome);
			$dados['prog_file']        	= $this->util->sgr($file);
			$dados['prog_tipo']        	= $this->util->sgr($tipo);
			$dados['prog_imagem']      	= $this->util->sgr($imagem);
			$dados['prog_tipo_origem'] 	= $this->util->sgr($tipo_origem);
			$dados['prog_tipo_menu']   	= $this->util->sgr($tipo_menu);
			$dados['prog_raiz']   		= $this->util->sgr($diretorioRaiz);
			$dados['prog_posicao']   	= $this->util->sgr($posicao);
			//
			$this->db->gravarInserir($dados);
			//
			$idprogramas = $this->db->getUltimoID();
			//
			$sql = "SELECT * 
					FROM grupos_acessos";
			$res = $this->db->consultar($sql);
			foreach($res as $reg){
				$sql = "INSERT INTO grupos_acessos_programas (gap_idgrupos_acessos, gap_idprogramas, gap_executa)
							VALUES({$reg['idgrupos_acessos']}, {$idprogramas}, '{$pode_executar}')";
				$this->db->executaSQL($sql);
				//
				if($tipo == 'menu' || $tipo == 'menuRaiz'){
					$this->html->criaMenu($reg['idgrupos_acessos']);
				}
			}
			//
			//
		}

	}

?>
