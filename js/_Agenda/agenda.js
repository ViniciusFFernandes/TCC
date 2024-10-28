$(document).ready(function() {
      //
      carregarEventos();
});

function gravarEvento(){
    var agen_titulo = $('#agen_titulo').val();
    var agen_descricao = $('#agen_descricao').val();
    var agen_inicio = $('#agen_inicio').val();
    var agen_fim = $('#agen_fim').val();
    var agen_cor = $('#agen_cor').val();
    var id_cadastro = $('#id_cadastro').val();
    //
    if(agen_titulo == ""){
        alertaPequeno('O título do evento é obrigatório.');
        return;
    }
    if(agen_inicio == ""){
        alertaPequeno('A data de inicio do evento é obrigatória.');
        return;
    }
    //
    $.post("agenda_grava.php", 
        { operacaoAjax: "registrarEvento", 
            agen_titulo: agen_titulo,
            agen_descricao: agen_descricao,
            agen_inicio: agen_inicio,
            agen_fim: agen_fim,
            agen_cor:  agen_cor,
            id_cadastro: id_cadastro
        }, 
        function(data){
            //alert(data);
            //return;
            if(data.retorno == "ok"){
                carregarEventos();
                $('#eventModal').modal('hide');
                limpaCamposAgenda();
            }else{
                alertaPequeno('Erro ao registrar evento.');
                console.log(data.msg);
            }
            //
        }, "json");
    //
}

function carregarEventos(){
    //
    $.post("agenda_grava.php", 
        { operacaoAjax: "carregaEventos"
        }, 
        function(data){
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: window.innerWidth < 768 ? 'listWeek' : 'dayGridMonth',
                locale: 'pt-br', // Define o idioma para português do Brasil
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                    buttonText: {
                    today: 'Hoje',
                    month: 'Mês',
                    week: 'Semana',
                    day: 'Dia',
                    list: 'Lista'
                },
                events: [],
                selectable: true, // Permite selecionar a data para criar um novo evento
                select: function(info) {
                    //
                    limpaCamposAgenda();
                    //
                    $('#eventModalLabel').text('Criar novo evento');
                    // Formata as datas de início e término para o formato YYYY-MM-DDTHH:mm
                    var inicial = info.start.toISOString().slice(0, 16);
                    var final = info.end.toISOString().slice(0, 16);
                
                    // Cria um objeto Date com a data e hora atual
                    var dateAtual = new Date(new Date().getTime() + (-3) * 60 * 60 * 1000).toISOString().slice(0, 16);
                    
                    // Prepara a data atual para comparação
                    var [dataAtual] = dateAtual.split("T");
                
                    // Prepara a data final para comparação
                    var [dataFinal] = final.split("T");

                    // Cria objeto para manipular data final
                    var dateFinal = new Date(dataFinal.split("T")[0] + "T" + dateAtual.split("T")[1]);
                    dateFinal.setDate(dateFinal.getDate() - 1);
                    
                    // Formata a nova data final
                    var finalFormatada = dateFinal.toISOString().slice(0, 16);
                    var [inicialData] = inicial.split("T");
                    var [finalData] = finalFormatada.split("T");

                    // Verifica se as datas são iguais
                    var mesmaData = finalData === inicialData;
                
                    // Preenche os campos de acordo com a condição
                    $('#agen_inicio').val(inicialData + "T" + dateAtual.split("T")[1]); // Preenche com a hora atual
                
                    if (mesmaData) {
                        $('#agen_fim').val(''); // Limpa o campo de fim se for o mesmo dia
                    } else {
                        $('#agen_fim').val(finalData + "T" + dateAtual.split("T")[1]); // Preenche o campo de fim
                    }
                
                    // Exibe o modal
                    $('#eventModal').modal('show');
                },
                eventClick: function(info) {
                    // Preenche os campos com as informações do evento e altera o modal para "editar"
                    limpaCamposAgenda();
                    //
                    $('#eventModalLabel').text('Editar evento');
                    $('#btnApagaEvento').show();  // Limpa o campo de ID
                    //
                    buscaDadosEvento(info.event.id);
                    //
                    $('#eventModal').modal('show');
                  },
                height: 'auto',
                contentHeight: 'auto'
            });

            calendar.render();
            //
            //
            //
            var diaTodo = "";
            var dataFim = "";
            $.each(data.result, function(index, reg) {
                //

                diaTodo = false;
                dataFim = reg.agen_fim;
                if(reg.agen_fim == null){
                    diaTodo = true;
                    dataFim = reg.agen_inicio;
                }
                calendar.addEvent({
                    id: reg.idagenda, 
                    title: reg.agen_titulo,
                    start: reg.agen_inicio,
                    end: dataFim,
                    allDay: diaTodo,
                    backgroundColor: reg.agen_cor,
                    borderColor: reg.agen_cor,
                    textColor: '#ffffff',
                    extendedProps: {
                        description: reg.agen_descricao
                    }
                });
            });
        }, "json");
}

function limpaCamposAgenda(){
    // $('#eventModalLabel').text('Criar novo evento');
    $('#agen_titulo').val('');
    $('#agen_descricao').val('');
    $('#agen_inicio').val("");
    $('#agen_fim').val("");
    $('#agen_cor').val('#4e73df');
    $('#id_cadastro').val('');  // Limpa o campo de ID
    $('#btnApagaEvento').hide();  // Limpa o campo de ID
    // $('#eventModal').modal('show');
}

function buscaDadosEvento(id_cadastro){
    $.post("agenda_grava.php", 
        { operacaoAjax: "buscaEvento", id_cadastro: id_cadastro
        }, 
        function(data){
            $('#agen_titulo').val(data.agen_titulo);
            $('#agen_descricao').val(data.agen_descricao);
            $('#agen_inicio').val(data.agen_inicio);
            $('#agen_fim').val(data.agen_fim);
            $('#agen_cor').val(data.agen_cor);
            $('#id_cadastro').val(data.idagenda);
        }, "json");
}

function excluirEvento(){
    $.post("agenda_grava.php", 
        { operacaoAjax: "excluirEvento", id_cadastro: $("#id_cadastro").val()
        }, 
        function(data){
            if(data.retorno == "ok"){
                carregarEventos();
                $('#eventModal').modal('hide');
            }else{
                alertaPequeno('Erro ao registrar evento.');
                console.log(data.msg);
            }
        }, "json");
}