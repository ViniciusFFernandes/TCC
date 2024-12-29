
 $(document).ready(function(){
    $("#prod_preco_tabela").mask("9999999999.99", 
          {translation: {
              '9': {
                pattern: /[0-9]/,
                optional: false
              }
            }, 
          reverse: true
          });
    $("#pfor_qte").mask("9999999999.99", 
          {translation: {
              '9': {
                pattern: /[0-9]/,
                optional: false
              }
            }, 
          reverse: true
          });
    $("#pfor_porc_perca").mask("9999999999.99", 
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
        buscaCadastro('produtos_grava.php');
      }
    });
 });

function testaDados(){
  // alert('sadnsa');
  // return;
  if($("#prod_nome").val() == ""){
    $("#prod_nome").css("border-color", "red");
    alertaPequeno("Por favor, informe um nome!");
    return;
  }else{
    $("#prod_nome").css("border-color", "green");
  }
  //
  if($("#prod_idunidade").val() == ""){
    $("#prod_idunidade").css("border-color", "red");
    alertaPequeno("Por favor, selecione uma unidade!");
    return;
  }else{
    $("#prod_idunidade").css("border-color", "green");
  }
  //
  if($("#prod_idgrupos").val() == ""){
    $("#prod_idgrupos").css("border-color", "red");
    alertaPequeno("Por favor, selecione um grupo!");
    return;
  }else{
    $("#prod_idgrupos").css("border-color", "green");
  }
  //
  chamaGravar('gravar')
}

function excluirItemFormula(idItemFormula, confirmado = false){
  if(confirmado) {
    $.post("produtos_grava.php", {operacao: 'excluirItemFormula', idItemFormula: idItemFormula},
      function(data){
        $("#itemFormula_" + idItemFormula).remove();
        alertaPequeno("Item removido com sucesso!");
      }, "html");
  }else{
    confirmar("Deseja excluir este item?", '', "excluirItemFormula("+ idItemFormula +", true)", "tada");
  }
}


function gravaItensFormula(){
  var _pfor_porc_perca = 0;
  var _idprodutos = $("#id_cadastro").val();
  var _idproduto_item = $("#pfor_idprodutos").val();
  var _qte = $("#pfor_qte").val();
  
  if(_idprodutos == ''){
    alertaPequeno("Nenhum produto base selecionado!");
    return;
  }
  if(_idproduto_item == ''){
    alertaPequeno("Selecione um item antes de inserir!");
    return;
  }
  if(_qte == '' || _qte <= 0){
    alertaPequeno("Informe a quantidade!");
    return;
  }
  if($("#pfor_porc_perca").val() > 0){
    _pfor_porc_perca = $("#pfor_porc_perca").val();
  }
  $.post("produtos_grava.php",
        {operacao: 'gravarItem',
        pfor_idproduto_final: _idprodutos,
        pfor_idprodutos: _idproduto_item,
        pfor_qte: _qte,
        pfor_porc_perca: _pfor_porc_perca},
        function(data){
          $("#produtos").val(""),
          $("#pfor_idprodutos").val(""),
          $("#pfor_qte").val(""),
          $("#pfor_porc_perca").val(""),
          $("#tableItensFormula").append(data);
        }, "html");
}

function imprimirEtiqueta(link){
  var idEnvio = '';
  idEnvio = $("#id_cadastro").val();
  //
  var linkCompleto = link + '?id_cadastro=' + idEnvio;
  //
  var qte = 1;
  //
  bootbox.prompt({
    title: "Informe a quantidade de etiquetas",
    inputType: 'number',
    value: 1,
    buttons: {
      confirm: {
          label: 'Imprimir',
          className: 'btn-primary'
      },
      cancel: {
          label: 'Cancelar',
          className: 'btn-secondary'
      }
    },
    callback: function(result) {
        if (result !== null) {
            //
            linkCompleto = linkCompleto + '&qte=' + result;
            //
            window.open(linkCompleto, '_blank');
        }else {
            // console.log("Operação cancelada pelo usuário.");
        }
    }
  });

  
} 