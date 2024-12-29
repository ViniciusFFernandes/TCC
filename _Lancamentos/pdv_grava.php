<?php
require_once("../_BD/conecta_login.php");
require_once("pedcompras.class.php");

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $produto_id = $_POST['produto_id'];
        $quantidade = $_POST['quantidade'];

        // Buscar preço do produto
        $sql = "SELECT prod_preco FROM produtos WHERE idprodutos = " . $db->escape($produto_id);
        $preco = $db->retornaUmCampoSql($sql, 'prod_preco');

        $total = $quantidade * $preco;

        // Registrar a venda
        $db->beginTransaction();
        $db->setTabela("vendas", "idvendas");
        $dados = [
            'produto_id' => $produto_id,
            'quantidade' => $quantidade,
            'total' => $total,
            'data_venda' => date('Y-m-d H:i:s')
        ];
        $db->gravarInserir($dados);
        $db->commit();

        header("Location: pdv_edita.php?sucesso=1");
    }
?>