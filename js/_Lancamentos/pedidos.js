$(document).ready(function(){
    
    $("#ped_frete").mask("9.999.999.999,99", 
        {translation: {
            '9': {
            pattern: /[0-9]/,
            optional: false
            }
        }, 
        reverse: true
    });
    
    $("#ped_valor_desconto").mask("9.999.999.999,99", 
        {translation: {
            '9': {
            pattern: /[0-9]/,
            optional: false
            }
        }, 
        reverse: true
    });

    $("#ped_porc_desconto").mask("9.999.999.999,99", 
        {translation: {
            '9': {
            pattern: /[0-9]/,
            optional: false
            }
        }, 
        reverse: true
    });

    $("#ped_total_pedido").mask("9.999.999.999,99", 
        {translation: {
            '9': {
            pattern: /[0-9]/,
            optional: false
            }
        }, 
        reverse: true
    });

    $('#idpedidos').on('keydown', function (e) {
        if (e.keyCode === 13) {
            abrePedido();
        }
    });

    $("#peit_unitario").mask("9.999.999.999,99", 
        {translation: {
            '9': {
            pattern: /[0-9]/,
            optional: false
            }
        }, 
        reverse: true
    });

    $("#peit_desconto").mask("9.999.999.999,99", 
        {translation: {
            '9': {
            pattern: /[0-9]/,
            optional: false
            }
        }, 
        reverse: true
    });

    $("#peit_desconto_porc").mask("9.999.999.999,99", 
        {translation: {
            '9': {
            pattern: /[0-9]/,
            optional: false
            }
        }, 
        reverse: true
    });

    $("#peit_qte").mask("9.999.999.999,99", 
        {translation: {
            '9': {
            pattern: /[0-9]/,
            optional: false
            }
        }, 
        reverse: true
    });

    $("#peit_total").mask("9.999.999.999,99", 
        {translation: {
            '9': {
            pattern: /[0-9]/,
            optional: false
            }
        }, 
        reverse: true
    });

    $("#pcon_valor").mask("9.999.999.999,99", 
        {translation: {
            '9': {
            pattern: /[0-9]/,
            optional: false
            }
        }, 
        reverse: true
    });

    $('#pesquisa').on('keydown', function(event) {
        if (event.keyCode == 13) { // Código da tecla "Enter" é 13
        buscaCadastro('pedidos_grava.php');
        }
    });
    
 });
 
function testaDados(operacao){
    if ($("#ped_idcliente").val() <= 0){
        alertaPequeno("Informe o cliente!");
        $("#ped_cliente").focus();
        return;
    }
    chamaGravar(operacao);
}

function abrePedido(){
    if($("#idpedidos").val() > 0){
        window.location.replace("pedidos_edita.php?id_cadastro=" + $("#idpedidos").val());
    }
}

function abreConta(idconta){
    window.location.replace("contarec_edita.php?id_cadastro=" + idconta);
}

function carregaComboBoxCC(tipo = ""){
    $("#comboBoxCC" + tipo).html("<br><img src='../icones/carregando.gif' width='20px'>");
    //
    var idbancos = '';
    if(tipo == ''){
        idbancos = $("#ped_idbancos").val();
    }else{
        idbancos = $("#pcon_idbancos").val();
    }
    
    if(idbancos == '' || idbancos <= 0){
        $("#comboBoxCC" + tipo).html("<br><font color='red'>*</font> Selecione o banco");
    }else{
        $.post("pedidos_grava.php", 
        {operacao: 'geraComboBoxCC', idbancos: idbancos, tipo: tipo},
        function(data){
            $("#comboBoxCC" + tipo).html(data);
        }, 'html');
    }
}

function buscaDadosProduto(idprodutosScam = ""){
    var idprodutos = "";
    if(idprodutosScam != ""){
        idprodutos = idprodutosScam;
        $("#idprodutos").val(idprodutosScam);
    }else{
        idprodutos = $("#idprodutos").val();
    }
    //
    $.post("pedidos_grava.php", {
        operacao: 'buscaDadosProduto',
        idprodutos: idprodutos
    }, function(data){
        $("#peit_qte").val(1);
        $("#peit_unitario").val(data.prod_preco);
        $("#peit_sigla_unidade").html(data.prod_unidade);
        $("#peit_qte").attr("readonly", false);
        $("#peit_unitario").attr("readonly", false);
        $("#peit_desconto").attr("readonly", false);
        $("#peit_desconto_porc").attr("readonly", false);
        calculoPreco();
        if(idprodutosScam != ""){
            gravarProduto();
        }
    }, "json");
}

function calculoDesconto(tipo){
    var valor_unitario = toFloat($("#peit_unitario").val());
    var qte = toFloat($("#peit_qte").val());
    var valor_total = qte * valor_unitario;
    var valor = 0;
    var desconto = 0;
    //
    if(tipo == 'vlr'){
        valor = toFloat($("#peit_desconto").val());
        desconto = (valor * 100) / valor_total;
        $("#peit_desconto_porc").val(desconto.toLocaleString('pt-BR'));
    }else{
        valor = toFloat($("#peit_desconto_porc").val());
        desconto = (valor * valor_total) / 100;
        $("#peit_desconto").val(desconto.toLocaleString('pt-BR'));
    }
    //
    calculoPreco();
}

function calculoPreco(){
    var valor_unitario = toFloat($("#peit_unitario").val());
    var qte = toFloat($("#peit_qte").val());
    var desconto = toFloat($("#peit_desconto").val());
    var valor_total = (qte * valor_unitario) - desconto;
    //
    $("#peit_total").val(valor_total.toLocaleString('pt-BR'));
}

function atzTotalPedido(){
    var id_cadastro = $("#idpedidos").val();
    //
    $.post("pedidos_grava.php", {
        operacao: 'atzTotalPedido',
        id_cadastro: id_cadastro
    }, function(data){
        $("#ped_total_pedido").val(data.ped_total_pedido);
    }, "json");
}

function limpaModalProd(){
    $("#idpedidos_itens").val('');
    $("#prod_nome").val('');
    $("#idprodutos").val('');
    $("#peit_qte").val('');
    $("#peit_unitario").val('');
    $("#peit_desconto").val('');
    $("#peit_desconto_porc").val('');
    $("#peit_total").val('');
    $("#peit_sigla_unidade").html('');
    $("#peit_qte").attr("readonly", true);
    $("#peit_unitario").attr("readonly", true);
    $("#peit_desconto").attr("readonly", true);
    $("#peit_desconto_porc").attr("readonly", true);
    $("#tituloModalProdutos").html("Inserir Produto");
}

function gravarProduto(){
    var id_cadastro = $("#idpedidos_itens").val();
    var idprodutos = $("#idprodutos").val();
    var idpedidos = $("#idpedidos").val();
    var peit_qte = $("#peit_qte").val();
    var peit_unitario = $("#peit_unitario").val();
    var peit_desconto = $("#peit_desconto").val();
    var peit_desconto_porc = $("#peit_desconto_porc").val();
    var peit_sigla_unidade = $("#peit_sigla_unidade").html();
    //
    if(idprodutos <= 0){
        alertaPequeno("Informe um Produto!");
        return;
    }
    //
    $.post("pedidos_grava.php", {
        operacao: 'gravarProduto',
        id_cadastro: id_cadastro,
        idpedidos: idpedidos,
        idprodutos: idprodutos,
        peit_qte: peit_qte,
        peit_unitario: peit_unitario,
        peit_desconto: peit_desconto,
        peit_desconto_porc: peit_desconto_porc,
        peit_sigla_unidade: peit_sigla_unidade
    }, function(data){
        if(data.retorno == 'ok'){
            limpaModalProd();
            attListaProdutos();
            if(id_cadastro > 0){
                $('#modalProdutos').modal('hide');
            }
        }else{
            alertaGrande('Erro ao inserir produto');
            console.log(data.msg);
        }
    }, "json");
}

function editarItem(idpedidos_itens){
    //
    $("#tituloModalProdutos").html("Editar Produto");
    //
    $.post("pedidos_grava.php", {
        operacao: 'editarProduto',
        id_cadastro: idpedidos_itens
    }, function(data){
            $('#modalProdutos').modal('show');
            //
            $("#idpedidos_itens").val(idpedidos_itens);
            $("#prod_nome").val(data.prod_nome);
            $("#idprodutos").val(data.peit_idprodutos);
            $("#peit_qte").val(data.peit_qte);
            $("#peit_unitario").val(data.peit_vlr_unitario);
            $("#peit_desconto").val(data.peit_valor_desconto);
            $("#peit_desconto_porc").val(data.peit_porc_desconto);
            $("#peit_sigla_unidade").html(data.peit_unidade_sigla);
            $("#peit_total").val(data.peit_total_item);
            $("#peit_qte").attr("readonly", false);
            $("#peit_unitario").attr("readonly", false);
            $("#peit_desconto").attr("readonly", false);
            $("#peit_desconto_porc").attr("readonly", false);
            //
    }, "json");
}


function excluiItem(idpedidos_itens){
    //
    $.post("pedidos_grava.php", {
        operacao: 'excluirProduto',
        id_cadastro: idpedidos_itens
    }, function(data){
        if(data.retorno == 'ok'){
            attListaProdutos();
        }else{
            alertaGrande('Erro ao excluir produto');
            console.log(data.msg);
        }
    }, "json");
}


function attListaProdutos(){
    var idpedidos = $("#idpedidos").val();
    //
    $.post("pedidos_grava.php", {
        operacao: 'attlistaProdutos',
        idpedidos: idpedidos
    }, function(data){
        $("#listaProdutos").html(data)
    }, "html");
    //
    atzTotalPedido();
}

function gerarParcelas(){
    //
    $("#btnGerarParcelas").html('<img src="../icones/carregando.gif" width="20px"> Gerando Parcelas...');
    $("#btnGerarParcelas").attr("disabled", true);
    //
    var idpedidos = $("#idpedidos").val();
    var ped_com_entrada = '';
    if($('#ped_com_entrada').is(":checked")){
        ped_com_entrada = 'SIM';
    }
    //
    $.post("pedidos_grava.php", {
        operacao: 'gerarParcelas',
        ped_idmeio_pagto: $("#ped_idmeio_pagto option:selected").val(),
        ped_idforma_pagto: $("#ped_idforma_pagto option:selected").val(),
        ped_idbancos: $("#ped_idbancos option:selected").val(),
        ped_idcc: $("#ped_idcc option:selected").val(),
        ped_idtipo_contas: $("#ped_idtipo_contas option:selected").val(),
        ped_qte_parcelas: $("#ped_qte_parcelas").val(),
        ped_com_entrada: ped_com_entrada,
        idpedidos: idpedidos
    }, function(data){
        $("#btnGerarParcelas").html('<i class="fas fa-percent"></i> Parcelar');
        $("#btnGerarParcelas").attr("disabled", false);
        //
        if(data.erro == 1){
            if(data.msgErro != ""){
                alertaGrande(data.msgErro);
            }else{
                alertaGrande("Erro ao gerar parcelas!");
            }
        }else{
            attListaContas();
        }
    }, 'json');
}

function attListaContas(){
    // $("#listaContas").html('<img src="../icones/carregando.gif" width="25px"> Buscando Parcelas...');
    //
    var idpedidos = $("#idpedidos").val();
    //
    $.post("pedidos_grava.php", {
        operacao: 'attParcelas',
        idpedidos: idpedidos
    }, function(data){
        $("#listaContas").html(data)
    }, 'html');
}

function alteraFormaPagto(){
    $.post("pedidos_grava.php", {
        operacao: 'retornaTipoFormaPagto',
        idforma_pagto: $("#ped_idforma_pagto option:selected").val()
    }, function(data){
        if(data.retorno != 'Parcelamento Livre'){
            $("#ped_qte_parcelas").attr("readonly", true);
            if(data.retorno == 'A Vista'){
                $("#ped_qte_parcelas").val("1");
            }
        }else{
            $("#ped_qte_parcelas").attr("readonly", false);
        }
    }, 'json');
}

function excluiConta(idpedidos_contas){
    $.post("pedidos_grava.php", {
        operacao: 'excluirConta',
        id_cadastro: idpedidos_contas
    }, function(data){
        if(data.retorno == 'ok'){
            attListaContas();
        }else{
            alertaGrande('Erro ao excluir conta');
            console.log(data.msg);
        }
    }, "json");
}

function limpaModalConta(){
    $("#idpedidos_contas").val('');
    $("#pcon_vencimento_dias").val('');
    $("#pcon_vencimento").val('');
    $("#pcon_valor").val('');
    $("#comboBoxMeioPagtoModal").html('');
    $("#comboBoxTipoContaModal").html('');
    $("#comboBoxBancosModal").html('');
    $("#comboBoxCCModal").html('');
}

function editarConta(idpedidos_contas){
    limpaModalConta()
    //
    $.post("pedidos_grava.php", {
        operacao: 'editarContas',
        id_cadastro: idpedidos_contas
    }, function(data){
            $('#modalContas').modal('show');
            //
            $("#idpedidos_contas").val(data.idpedidos_contas);
            $("#pcon_vencimento_dias").val(data.pcon_vencimento_dias);
            $("#pcon_vencimento").val(data.pcon_vencimento);
            $("#pcon_valor").val(data.pcon_valor);
            $("#comboBoxMeioPagtoModal").html(data.comboBoxMeioPagtoModal);
            $("#comboBoxTipoContaModal").html(data.comboBoxTipoContaModal);
            $("#comboBoxBancosModal").html(data.comboBoxBancosModal);
            $("#comboBoxCCModal").html(data.comboBoxCCModal);
            //
    }, "json");
}

function attDataVencto(tipo){
    var dataAtual = moment();
    var dataVencto = moment($("#pcon_vencimento").val());
    if(tipo == 'dias'){
        var dataCalculada = dataAtual.add($("#pcon_vencimento_dias").val(), 'days'); 
        $("#pcon_vencimento").val(dataCalculada.format('YYYY-MM-DD'));
    }
    if(tipo == 'data'){
        var diferencaDias = dataVencto.diff(dataAtual, 'days');
        diferencaDias = diferencaDias + 1;
        if(diferencaDias <= 0){
            $("#pcon_vencimento").val(dataAtual.format('YYYY-MM-DD'));
            $("#pcon_vencimento_dias").val(0);
        }else{
            $("#pcon_vencimento_dias").val(diferencaDias);
        }
    }
}

function gravarConta(){
    $.post("pedidos_grava.php", {
        operacao: 'gravarConta',
        id_cadastro: $("#idpedidos_contas").val(),
        pcon_vencimento_dias: $("#pcon_vencimento_dias").val(),
        pcon_vencimento: $("#pcon_vencimento").val(),
        pcon_valor: $("#pcon_valor").val(),
        ped_idmeio_pagto: $("#pcon_idmeio_pagto").val(),
        ped_idbancos: $("#pcon_idbancos").val(),
        ped_idcc: $("#pcon_idcc").val(),
        ped_idtipo_contas: $("#pcon_idtipo_contas").val()
    }, function(data){
            if(data.retorno == 'ok'){
                attListaContas();
                $('#modalContas').modal('hide');
            }else{
                alertaGrande('Erro ao gravar conta');
                console.log(data.msg);
            }
    }, "json");
}

function abrirScamProduto(){
    limpaModalProd();
    bootbox.dialog({
        title: "Escaneie o QR Code",
        message: '<div id="divLoad" style="width: 100%;"><center>Iniciando a Camera <img src="../icones/carregando.gif" width="25px"></center></div><div id="qr-reader" style="width: 100%;"></div>',
        size: "large",
        onShown: function() {
            // Inicia o scanner html5-qrcode
            const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                bootbox.hideAll(); // Fecha o modal ao escanear com sucesso
                html5QrcodeScanner.stop();
                // console.log(decodedText);
                buscaDadosProduto(decodedText);
            };

            const qrCodeErrorCallback = (errorMessage) => {
                // console.warn(`Erro ao escanear: ${errorMessage}`);
            };

            const html5QrcodeScanner = new Html5Qrcode("qr-reader");
            html5QrcodeScanner.start(
                { facingMode: "environment" },  // Use a câmera traseira
                {
                    fps: 30,                    // Taxa de quadros
                    qrbox: 250                  // Tamanho da área de leitura
                },
                qrCodeSuccessCallback,
                qrCodeErrorCallback
            ).then(() => {
                $("#divLoad").hide();
                $("#qr-reader").show();
            }).catch((err) => {
                console.error("Erro ao iniciar o scanner", err);
            });

            // Para o scanner ao fechar o modal
            $(".bootbox-close-button").on('click', function() {
                html5QrcodeScanner.stop().catch((err) => console.error("Erro ao parar o scanner", err));
            });
        },
        onHide: function() {
            // Opcional: Limpeza adicional ao fechar o modal
        }
    });
}