<?php
	require_once("util.class.php");
	require_once("parametros.class.php");

   Class Usuarios{
       private $id;
       private $idgrupos_acessos;
       private $db;
       private $util;
       private $parametros;

    function __construct($db, $id = "", $idgrupos_acessos = ""){
        $this->id = $id;
        $this->idgrupos_acessos = $idgrupos_acessos;
        $this->db = $db;
        $this->parametros = new Parametros($db);
        $this->util = new Util();
    }

    public function usuario_pode_executar($prog_file = ''){
        //Grupo um sempre retorna true
        if($this->idgrupos_acessos == 1){
            return true;
        }
        
        if(empty($prog_file)){
            $prog_file = basename($_SERVER['PHP_SELF']);
        }

        $sql = "SELECT gap_executa 
                FROM grupos_acessos_programas 
                    JOIN programas ON (gap_idprogramas = idprogramas)
                WHERE gap_idgrupos_acessos = " . $this->idgrupos_acessos . " 
                AND prog_file = " . $this->util->sgr($prog_file);
        $reg = $this->db->retornaUmReg($sql);
        if($reg['gap_executa'] == 1){
            return true;
        }else{
            return false;
        }
    }

    public function busca_chave_pagto($apenasConsulta = false){
        if(COD_CLIENTE_SAC == ""){
            return "";
        }
        // Configurações da requisição
        $url = $this->parametros->buscaValor("sistema: sendereco web do SAC") . '_Configuracoes/recupera_chave_pagamento.php'; // URL do seu script
        //
        $data["operacao"] = 'getChavePagto'; // Operação que você deseja executar
        $data["cod_cliente"] = COD_CLIENTE_SAC; // Código do cliente
        //
        // Inicializa a sessão cURL
        $ch = curl_init();
        //
        // Configurações da requisição
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Retorna a resposta como string
        curl_setopt($ch, CURLOPT_POST, true); // Método POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        //
        // Executa a requisição
        $response = curl_exec($ch);
        //
        $retorno = json_decode($response);
        //
        // Fecha a sessão cURL
        curl_close($ch);
        //
        if($retorno->chave_pagamento != ""){
            if(!$apenasConsulta){
                unset($dados);
                $dados['id'] 			= $this->util->sgr('sistema: chave de pagamento');
                $dados['para_valor'] 	= $this->util->sgr($retorno->chave_pagamento);
                $this->parametros->gravaValor($dados);
            }
            //
            return $retorno->chave_pagamento;
        }else{
            return '';
        }
        //
    }

    public function le_chave_pagto($chavePagto){
        //
        // Extrai partes da chave
        $dadosChave["codigoCliente"] = substr($chavePagto, 10, 10); // 10 dígitos do código do cliente
        $dadosChave["vipPrefix"] = substr($chavePagto, 20, 3); // 3 dígitos do prefixo (VIP ou REG)
        $dadosChave["dataGeracao"] = substr($chavePagto, 23, 8); // 8 dígitos para data de geração
        $dadosChave["dataVencimento"] = substr($chavePagto, 31, 8); // 8 dígitos para data de vencimento
        //
        // Formata as datas
        $dadosChave["dataVencimentoFormatada"] = date('Y-m-d', strtotime($dadosChave["dataVencimento"]));
        $dadosChave["dataVencimentoTimestamp"] = strtotime($dadosChave["dataVencimentoFormatada"]);
        $dadosChave["dataAtualTimestamp"] = time();
        $dadosChave["diferencaDias"] = ($dadosChave["dataAtualTimestamp"] - $dadosChave["dataVencimentoTimestamp"]) / (60 * 60 * 24);
        //
        //Retorna dados da chave
        return $dadosChave;
    }

    public function gerar_chave_pagto($pess_cod_cliente, $tipo = ""){
        //
        $sql = "SELECT * 
                FROM pessoas 
                WHERE pess_cod_cliente = "  . $this->util->sgr($pess_cod_cliente);
        $regPess = $this->db->retornaUmReg($sql);
        //
        $sql = "SELECT * 
                FROM contarec 
                WHERE ctrc_situacao = 'Quitada'
                    AND ctrc_idcliente = "  . $regPess["idpessoas"] . "
                ORDER BY ctrc_vencimento DESC
                LIMIT 1";
        $regCtrc = $this->db->retornaUmReg($sql);
        //
        if($regPess["idpessoas"] <= 0){
            return "";
        }
        if($regPess["pess_vip"] != "SIM" && $tipo != "temporaria"){
            if($regCtrc["ctrc_vencimento"] == ""){
                return "";
            }
        }
        //
        $venciemntoConta = $regCtrc["ctrc_vencimento"];
        if($tipo == "temporaria"){
            $venciemntoConta = date("Y-m-d");
        }
        //
        // Aumentar o código do cliente para ter 5 dígitos
        $codigoCliente = str_pad($regPess["pess_cod_cliente"], 10, '0', STR_PAD_LEFT);
        //
        // Obter a data de geração
        $dataGeracao = date('Ymd'); // Ex: 20241026
        //
        // Calcular o novo vencimento
        $dataVencimento = date('Ymd', strtotime($venciemntoConta . ' +30 days'));
        //
        // Adicionar um prefixo de VIP ou padrão
        $vipPrefix = "REG";
        if($regPess["pess_vip"] == "SIM"){
            $vipPrefix = "VIP";
        }
        //
        // Combinar as partes para criar uma chave base com prefixo VF
        $chaveBase = "VF" . $codigoCliente . $vipPrefix . $dataGeracao . $dataVencimento;
        //
        //
        // Adicionar uma sequência de caracteres aleatórios para aumentar o tamanho
        $extra = bin2hex(random_bytes(4)); // Gera 16 caracteres extras
        $extra2 = bin2hex(random_bytes(4)); // Gera 16 caracteres extras
        //
        // Combinar tudo para formar a chave final
        $chaveFinal = strtoupper($extra . $chaveBase . $extra2);
        //
        //Atualiza a tabela pessoas com a nova chave pagto
        //
        $this->db->setTabela("pessoas", "idpessoas");
        //
        unset($dados);
        $dados['id']                    = $regPess["idpessoas"];
        $dados['pess_chave_pagto']      = $this->util->sgr($chaveFinal);
        //
        $this->db->gravarInserir($dados);
        //
        return $chaveFinal;
    }
   }

?>