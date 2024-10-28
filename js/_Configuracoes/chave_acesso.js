function atualizarChaveSistema(){
    $("#btnAtualizar").html("Atualizando chave, aguarde... <img src='../icones/carregando_engrenagens.gif' width='25px;'>");
    $.post("chave_acesso_grava.php", 
          {operacao: 'atualizarChave'},
          function(data){
            // console.log(data);
            if(data.chavePagto != ""){
                $("#chavePgtoSistema").val(data.chavePagto);
                $("#chavePgtoSac").val(data.chavePagto);
                $("#btnAtualizar").html("Chave de acesso atualizada com sucesso!");
            }else{
              alertaPequeno("Erro ao atualizar chave de acesso!", '', 'tada');
              return;
            }
    }, 'json')
}