<?php
    require_once("../_BD/conecta_login.php");
    require_once("../Class/tabelas.class.php");
    require_once("produtos.class.php");
    require_once("../phpqrcode/qrlib.php");
    //
    //
    //
    $produtos = new produtos($db);
    //
    $sql = "SELECT *
            FROM produtos 
            WHERE idprodutos = {$_REQUEST['id_cadastro']}";
    //
    $regProdutos = $db->retornaUmReg($sql);
    //
    $idempresa = CODIGO_EMPRESA;
    //
    $sqlEmpresas = "SELECT *
                    FROM empresas 
                        LEFT JOIN cidades ON (idcidades = emp_idcidades) 
                        LEFT JOIN estados ON (cid_idestados = idestados)
                    WHERE idempresas = {$idempresa}";
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
        $tamanhoLogo = "130px;";
        $nomeEmpresa = "";
    }else{
        $tamanhoLogo = "60px;";
    }
    //
    ob_start();
    // Gera o QR Code e o armazena no buffer de saída
    QRcode::png($regProdutos["idprodutos"], null, QR_ECLEVEL_L, 3, 2);
    // Captura o conteúdo do buffer e o armazena em uma variável
    $imageData = ob_get_contents();
    // Limpa o buffer de saída
    ob_end_clean();
    // Codifica a imagem em base64 para uso direto em uma tag <img>
    $qrcode = base64_encode($imageData);
    //
    $etiquetas = '';
    for ($i = 1; $i <= $_REQUEST['qte']; $i++) {
        //
        $etiquetas .=  $produtos->etiquetaQrCodePadrao($qrcode, $nomeEmpresa, $cnpjEmpresa, $enderecoEmpresa, $cidadeEmpresa, $ufEmpresa, $cepEmpresa, $telefoneEmpresa, $logoRelatorios, $tamanhoLogo, $util->formataMoeda($regProdutos["prod_preco_tabela"]));
    }
    //
    //
    //Abre o arquivo html e Inclui mensagens e trechos php
    $html = $html->buscaHtml(false);
    $html = str_replace("##etiquetas##", $etiquetas, $html);
    
    echo $html;
    exit;
?>
