function ativarDesativar(operacao, idgrupos_acessos_programas){
  var novoBtn;
  var executa;
  if(operacao == 'Ativar'){
    executa = 1;
    novoBtn = '<button type="button" onclick="ativarDesativar(\'Desativar\', ' + idgrupos_acessos_programas + ')" class="btn btn-danger">Desativar</button>';
  }

  if(operacao == 'Desativar'){
    executa = 0;
    novoBtn = '<button type="button" onclick="ativarDesativar(\'Ativar\', ' + idgrupos_acessos_programas + ')" class="btn btn-success">Ativar</button>';
  }
  $("#btn_" + idgrupos_acessos_programas).html('<img src="../icones/carregando_engrenagens.gif" width="34px">');
  $.post("grupos_acessos_grava.php", 
  {operacao: 'ativarDesativarProgram', gap_executa: executa, idgrupos_acessos_programas: idgrupos_acessos_programas}, 
  function(data){
    if(data.retorno == 'ok'){
      $("#btn_" + idgrupos_acessos_programas).html(novoBtn);
    }else{
      alertaGrande(data.msg)
    }
  }, "json")
}

function testaDados(){
  // alert('sadnsa');
  // return;
  if($("#grac_nome").val() == ""){
    $("#grac_nome").css("border-color", "red");
    alertaPequeno("Por favor, informe um nome!");
    return;
  }else{
    $("#grac_nome").css("border-color", "green");
  }
  //
 chamaGravar('gravar');
}

function ativarDesativarProgramas(operacao){
  //
  bootbox.confirm({
    title: "<b>Atenção</b>",
    message: "Deseja " + operacao + " Todos os Programas?",
    className: 'bounceInUp animated',
    buttons: {
        cancel: {
            label: '<img src="../icones/excluir2.png"> Não'
        },
        confirm: {
            label: '<img src="../icones/certo.png"> Sim'
        }
    },
    callback: function(result){
        if(result){
          var executa;
          var novoBtn;
          if(operacao == 'Ativar'){
            executa = 1;
            novoBtn = '<button type="button" onclick="ativarDesativar(\'Desativar\', ##idgrupos_acessos_programas##)" class="btn btn-danger">Desativar</button>';
          }
          if(operacao == 'Desativar'){
            executa = 0;
            novoBtn = '<button type="button" onclick="ativarDesativar(\'Ativar\', ##idgrupos_acessos_programas##)" class="btn btn-success">Ativar</button>';
          }
          $("td[name='tdBtnAtivarDesativar']" ).html('<img src="../icones/carregando_engrenagens.gif" width="34px">')
          $.post("grupos_acessos_grava.php", 
            {operacao: "ativarDesativarTodos", gap_idgrupos_acessos: $("#id_cadastro").val(), gap_executa: executa},
            function(data){
              $.each(data, function(index, reg) {
                novoBtn = novoBtn.replace('##idgrupos_acessos_programas##', reg.idgrupos_acessos_programas);
                $("#btn_" + reg.idgrupos_acessos_programas).html(novoBtn);
              });
            }, "json")
        }
    }
});
}