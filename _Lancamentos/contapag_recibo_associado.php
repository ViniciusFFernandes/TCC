<?php
    require_once("../_BD/conecta_login.php");
    require_once("salarios.class.php");

    //
    //Operações do banco de dados
    //
    $salarios = new Salarios($db);
    //
    if(!empty($_REQUEST['id_cadastro'])){
        if($_REQUEST['tipo'] != "completo"){
            $sql = "SELECT *
                FROM salarios_funcionarios 
                    JOIN salarios ON (safu_idsalarios = idsalarios)
                    JOIN contapag ON (safu_idcontapag = idcontapag)
                    LEFT JOIN tipo_contas ON (ctpg_idtipo_contas = idtipo_contas)
                    JOIN pessoas ON (ctpg_idcliente = idpessoas) 
                    LEFT JOIN setores ON (idsetores = pess_idsetores)
                    LEFT JOIN funcoes ON (idfuncoes = pess_idfuncoes)
                    LEFT JOIN cidades ON (idcidades = pess_idcidades)
                    LEFT JOIN estados ON (idestados = cid_idestados)
                WHERE idsalarios_funcionarios = {$_REQUEST['id_cadastro']}";
        }else{
            $sql = "SELECT *
                FROM salarios_funcionarios 
                    JOIN salarios ON (safu_idsalarios = idsalarios)
                    JOIN contapag ON (safu_idcontapag = idcontapag)
                    LEFT JOIN tipo_contas ON (ctpg_idtipo_contas = idtipo_contas)
                    JOIN pessoas ON (ctpg_idcliente = idpessoas) 
                    LEFT JOIN setores ON (idsetores = pess_idsetores)
                    LEFT JOIN funcoes ON (idfuncoes = pess_idfuncoes)
                    LEFT JOIN cidades ON (idcidades = pess_idcidades)
                    LEFT JOIN estados ON (idestados = cid_idestados)
                WHERE idsalarios = {$_REQUEST['id_cadastro']}";
        }
        $res = $db->consultar($sql);
    }else{
        $html->mostraErro("Salario não encontrada!<br>Código não infomado!");
        exit;
    }
    //
    $conteudoRecibos = "";
    $primeiro = true;
    //
    foreach ($res as $reg) {
        //
        if(!$priemiro){
            $conteudoRecibos .= '<div class="page-break"></div>';
        }
        //
        if($reg['idcontapag'] <= 0){
            $html->mostraErro("Conta não encontrada!");
            exit;
        }
        if($reg['tico_tipo_salario'] != 'SIM'){
            $html->mostraErro("Está conta não é do tipo salario!");
            exit;
        }
        if($reg['idpessoas'] <= 0){
            $html->mostraErro("Pessoa não encontrada!");
            exit;
        }
        if(empty($reg['func_nome'])) $reg['func_nome'] = "Não Informado";
        if(empty($reg['set_nome'])) $reg['set_nome'] = "Não Informado";
        //
        if(empty($reg['ctpg_idempresas'])) $reg['ctpg_idempresas'] = CODIGO_EMPRESA;
        //
        $sqlEmpresas = "SELECT *
                        FROM empresas 
                            LEFT JOIN cidades ON (idcidades = emp_idcidades) 
                            LEFT JOIN estados ON (cid_idestados = idestados)
                        WHERE idempresas = " . $reg['ctpg_idempresas'];
        $regEmpresas = $db->retornaUmReg($sqlEmpresas);
        //
        $logoRelatorios = $regEmpresas['emp_logo'];
        //
        $nomeEmpresa = $regEmpresas['emp_nome'];
        $cnpjEmpresa = $regEmpresas['emp_cnpj'];
        $enderecoEmpresa = $regEmpresas['emp_endereco'];
        $cidadeEmpresa = $regEmpresas['cid_nome'];
        $ufEmpresa = $regEmpresas['est_uf'];
        $cepEmpresa = $regEmpresas['emp_cep'];
        $telefoneEmpresa = $regEmpresas['emp_telefone'];
        //
        if($regEmpresas['emp_logo_relatorio'] == 'SIM'){
            $tamanhoLogo = "150px;";
            $nomeEmpresa = "";
        }else{
            $tamanhoLogo = "85px;";
        }
        //
        $msgCesta = '';
        if($reg['safu_dias'] <= 0){
            $msgCesta = ' e uma cesta basica ';
        }
        //
        $vlr_desconto = "---";
        $receita_total = "---";
        $vlr_liquido = "---";
        $vlr_bruto = "---";
        $vlr_juros = "---";
        $desconto_faltas = "R$ 0,00";
        $vlr_adiantamento = "---";
        //
        if($reg['ctpg_vlr_desconto'] > 0){
            $vlr_desconto = "R$ " . $util->formataMoeda($reg['ctpg_vlr_desconto']);
        }
        if($reg['ctpg_vlr_bruto'] > 0 || $reg['ctpg_vlr_juros'] > 0){
            $receita_total = "R$ " . $util->formataMoeda($reg['ctpg_vlr_bruto'] + $reg['ctpg_vlr_juros']);
        }
        if($reg['ctpg_vlr_liquido'] > 0){
            $vlr_liquido = "R$ " . $util->formataMoeda($reg['ctpg_vlr_liquido']);
        }
        if($reg['ctpg_vlr_bruto'] > 0){
            $vlr_bruto = "R$ " . $util->formataMoeda($reg['ctpg_vlr_bruto']);
        }
        if($reg['ctpg_vlr_juros'] > 0){
            $vlr_juros = "R$ " . $util->formataMoeda($reg['ctpg_vlr_juros']);
        }
        if($reg['safu_vlr_desconto_faltas'] > 0){
            $desconto_faltas = "R$ " . $util->formataMoeda($reg['safu_vlr_desconto_faltas']);
        }
        if($reg['ctpg_vlr_desconto'] > 0 || $reg['safu_vlr_desconto_faltas'] > 0){
            $vlr_adiantamento = "R$ " . $util->formataMoeda($reg['ctpg_vlr_desconto'] - $reg['safu_vlr_desconto_faltas']);
        }
        //
        $recibo = $salarios->getConteudoImpressao();
        //
        $recibo = str_replace("##logoRelatorios##", $logoRelatorios, $recibo);
        $recibo = str_replace("##nomeEmpresa##", $nomeEmpresa, $recibo);
        $recibo = str_replace("##cnpjEmpresa##", $cnpjEmpresa, $recibo);
        $recibo = str_replace("##enderecoEmpresa##", $enderecoEmpresa, $recibo);
        $recibo = str_replace("##cidadeEmpresa##", $cidadeEmpresa, $recibo);
        $recibo = str_replace("##ufEmpresa##", $ufEmpresa, $recibo);
        $recibo = str_replace("##cepEmpresa##", $cepEmpresa, $recibo);
        $recibo = str_replace("##telefoneEmpresa##", $telefoneEmpresa, $recibo);
        $recibo = str_replace("##mesExtenso##", $util->mesExtenso($reg['sala_mes']), $recibo);
        $recibo = str_replace("##ano##", $reg['sala_ano'], $recibo);
        $recibo = str_replace("##pess_nome##", $reg['pess_nome'], $recibo);
        $recibo = str_replace("##vlr_bruto##", $vlr_bruto, $recibo);
        $recibo = str_replace("##vlr_juros##", $vlr_juros, $recibo);
        $recibo = str_replace("##receita_total##", $receita_total, $recibo);
        $recibo = str_replace("##falta_dias##", $reg['safu_dias'], $recibo);
        $recibo = str_replace("##desconto_faltas##", $desconto_faltas, $recibo);
        $recibo = str_replace("##vlr_adiantamento##", $vlr_adiantamento, $recibo);
        $recibo = str_replace("##vlr_desconto##", $vlr_desconto, $recibo);
        $recibo = str_replace("##vlr_liquido##", $vlr_liquido, $recibo);
        $recibo = str_replace("##vlr_liquito_extenso##", $util->valorPorExtenso($reg['ctpg_vlr_liquido']), $recibo);
        $recibo = str_replace("##diaAtual##", date('d'), $recibo);
        $recibo = str_replace("##mesAtual##", $util->mesExtenso(date('m')), $recibo);
        $recibo = str_replace("##anoAtual##", date('Y'), $recibo);
        $recibo = str_replace("##msg_cesta_basica##", $msgCesta, $recibo);
        $recibo = str_replace("##tamanhoLogo##", $tamanhoLogo, $recibo); 
        //
        $conteudoRecibos .= $recibo;
        //
        $priemiro = false;
    }
    //
    //Abre o arquivo html e Inclui mensagens e trechos php
    $html = $html->buscaHtml(false);
    $html = str_replace("##conteudoRecibos##", $conteudoRecibos, $html); 
    echo $html;
    exit;
?>
