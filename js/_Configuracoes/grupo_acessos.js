
$( document ).ready(function() {
  if($("#id_cadastro").val() > 0){
    consultarProgramas();
  }
  //
  $('#consulta').on('keydown', function(event) {
    if (event.keyCode == 13) { // Código da tecla "Enter" é 13
      consultarProgramas();
    }
  });
  //
  $('#pesquisa').on('keydown', function(event) {
    if (event.keyCode == 13) { // Código da tecla "Enter" é 13
      buscaCadastro('grupo_acessos_grava.php');
    }
  });
});

function ativarDesativar(operacao, idgrupos_acessos_programas){
  var novoBtn;
  var executa;
  if(operacao == 'Ativar'){
    executa = 1;
    novoBtn = '<button type="button" onclick="ativarDesativar(\'Desativar\', ' + idgrupos_acessos_programas + ')" class="btn btn-light"><i class="fas fa-check text-success"></i></button>';
  }

  if(operacao == 'Desativar'){
    executa = 0;
    novoBtn = '<button type="button" onclick="ativarDesativar(\'Ativar\', ' + idgrupos_acessos_programas + ')" class="btn btn-light"><i class="fas fa-times text-danger"></i></button>';
  }
  $("#btn_" + idgrupos_acessos_programas).html('<img src="../icones/carregando_engrenagens.gif" width="34px">');
  $.post("grupos_acessos_grava.php", 
  {operacao: 'ativarDesativarPrograma', gap_executa: executa, idgrupos_acessos_programas: idgrupos_acessos_programas}, 
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
            novoBtn = '<button type="button" onclick="ativarDesativar(\'Desativar\', ##idgrupos_acessos_programas##)" class="btn btn-light"><i class="fas fa-check text-success"></i></button>';
          }
          if(operacao == 'Desativar'){
            executa = 0;
            novoBtn = '<button type="button" onclick="ativarDesativar(\'Ativar\', ##idgrupos_acessos_programas##)" class="btn btn-light"><i class="fas fa-times text-danger"></i></button>';
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

function gerarMenu(){
  //
  $("#btnGerarMenu").attr("disabled", true);
  $("#btnGerarMenu").html('<img src="../icones/carregando_engrenagens.gif" width="20px"> Gerando...');
  //
  $.post("grupos_acessos_grava.php", 
    {operacao: "gerarMenu", id_cadastro: $("#id_cadastro").val()},
    function(data){
      if(data == 'Ok'){
        alertaPequeno("Menu gerado com sucesso!");
      }else{
        alertaPequeno("Erro ao gerar menu!");
      }
      $("#btnGerarMenu").attr("disabled", false);
      $("#btnGerarMenu").html('Gerar Menu');
    }, 'html');
}

function consultarProgramas(){
  //
  $("#listaProgramas").html('<img src="../icones/carregando.gif" width="25px">Buscando programas...');
  //
  $.post("grupos_acessos_grava.php",
        {operacao: "listarProgramas", 
        id_cadastro: $("#id_cadastro").val(),
        pesquisa: $("#consulta").val()},
        function(data){
          $("#listaProgramas").html(data);
        }, "html")
}