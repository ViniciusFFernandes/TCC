$(document).ready(function() {
    //
    carregarEventos();
    //
    $('#imageModal').on('hidden.bs.modal', function () {
        console.log('Modal fechado');
        limparModalVisualizacao();
    });
});

function selecionaArquivos(input){
    var numFiles = input.files.length;
    if(numFiles > 0) {
      $('#fileCount').text(numFiles + ' arquivo(s) selecionado(s)');
    }else{
      $('#fileCount').text('Nenhum arquivo selecionado');
    }
}

function gravarEvento() {
    //
    //
    var agen_titulo = $('#agen_titulo').val();
    var agen_descricao = $('#agen_descricao').val();
    var agen_inicio = $('#agen_inicio').val();
    var agen_fim = $('#agen_fim').val();
    var agen_cor = $('#agen_cor').val();
    var id_cadastro = $('#id_cadastro').val();
    
    // Validações
    if (agen_titulo == "") {
        alertaPequeno('O título do evento é obrigatório.');
        return;
    }
    if (agen_inicio == "") {
        alertaPequeno('A data de início do evento é obrigatória.');
        return;
    }
    //
    $("#agen_titulo").attr("readonly", true);
    $("#agen_cor").attr("readonly", true);
    $("#agen_inicio").attr("readonly", true);
    $("#agen_fim").attr("readonly", true);
    $("#agen_descricao").attr("readonly", true);
    //
    $("#btnGravarEvento").attr("disabled", true);
    $("#btnCancelar").attr("disabled", true);
    $("#btnApagaEvento").attr("disabled", true);
    $('#inputFile').attr('disabled', true);
    //
    // Adicionar a classe 'disabled' à label para alterar o estilo
    $('#inputLabel').addClass('disabled');
    //
    if(id_cadastro != ""){
        $("#btnGravarEvento").html("Atualizando <img src='../icones/carregando.gif' width='20px'>");
    }else{
        $("#btnGravarEvento").html("Gravando <img src='../icones/carregando.gif' width='20px'>");
    }

   
    // Cria o objeto FormData para incluir dados e arquivos
    var formData = new FormData();
    formData.append('operacaoAjax', 'registrarEvento');
    formData.append('agen_titulo', agen_titulo);
    formData.append('agen_descricao', agen_descricao);
    formData.append('agen_inicio', agen_inicio);
    formData.append('agen_fim', agen_fim);
    formData.append('agen_cor', agen_cor);
    formData.append('id_cadastro', id_cadastro);

    // Adiciona os arquivos selecionados
    var arquivos = document.getElementById('inputFile').files;
    if (arquivos.length > 0) {
        for (var i = 0; i < arquivos.length; i++) {
            formData.append('arquivo[]', arquivos[i]);
        }

        // Exibe a barra de progresso se houver arquivos
        $('#fileCount').html('Enviando arquivos <img src="../icones/carregando.gif" width="30px">');
        $('#progressContainer').show();
        $('#progressBar').css('width', '0%').attr('aria-valuenow', 0).text('0%');
    } else {
        // Oculta a barra de progresso se não houver arquivos
        $('#progressContainer').hide();
    }

    // Requisição AJAX com XMLHttpRequest
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "agenda_grava.php", true);

    // Evento de progresso
    if (arquivos.length > 0) {
        xhr.upload.onprogress = function (e) {
            if (e.lengthComputable) {
                var percentComplete = Math.round((e.loaded / e.total) * 100);
                $('#progressBar').css('width', percentComplete + '%').attr('aria-valuenow', percentComplete).text(percentComplete + '%');
            }
        };
    }

    // Evento de conclusão
    xhr.onload = function () {
        //
        $("#agen_titulo").attr("readonly", false);
        $("#agen_cor").attr("readonly", false);
        $("#agen_inicio").attr("readonly", false);
        $("#agen_fim").attr("readonly", false);
        $("#agen_descricao").attr("readonly", false);
        //
        $("#btnCancelar").attr("disabled", false);
        $("#btnApagaEvento").attr("disabled", false);
        $("#btnGravarEvento").attr("disabled", false);
        $('#inputFile').attr('disabled', false);
        //
        $('#inputLabel').removeClass('disabled');
        $("#btnGravarEvento").html("Gravar");

        $('#progressContainer').hide();

        if (xhr.status === 200) {
            var data = JSON.parse(xhr.responseText);
            if (data.retorno == "ok") {
                carregarEventos();
                $('#eventModal').modal('hide');
                limpaCamposAgenda();
            } else {
                alertaPequeno('Erro ao registrar evento.');
                console.log(data.msg);
            }
        } else {
            alertaPequeno('Erro ao processar o pedido.');
        }
    };

    // Evento de erro
    xhr.onerror = function () {
        alertaPequeno('Erro ao processar o pedido.');
        $('#progressContainer').hide();
    };

    xhr.send(formData);
}


function carregarEventos() {
    $.post("agenda_grava.php", 
        { operacaoAjax: "carregaEventos" }, 
        function(data) {
            var calendarEl = document.getElementById('calendar');

            // Recupera visualização e data salvas no localStorage
            var savedView = localStorage.getItem('calendarView') || (window.innerWidth < 768 ? 'listWeek' : 'dayGridMonth');
            var savedDate = localStorage.getItem('calendarDate') || new Date().toISOString();

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: savedView,
                initialDate: savedDate,
                locale: 'pt-br',
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
                selectable: true,
                select: function(info) {
                    limpaCamposAgenda();
                    $('#eventModalLabel').text('Criar novo evento');
                    //
                    console.log(info);
                    // Verifica se é uma seleção de único dia (mensal) ou período
                    if (calendar.view.type === 'dayGridMonth') {
                        var inicial = info.start.toISOString().slice(0, 16);
                        var final = info.end.toISOString().slice(0, 16);
                        var dateAtual = new Date(new Date().getTime() + (-3) * 60 * 60 * 1000).toISOString().slice(0, 16);
                        
                        var [inicialData] = inicial.split("T");
                        var [dataFinal] = final.split("T");
    
                        var dateFinal = new Date(dataFinal.split("T")[0] + "T" + dateAtual.split("T")[1]);
                        dateFinal.setDate(dateFinal.getDate() - 1);
                        var finalFormatada = dateFinal.toISOString().slice(0, 16);
                        // Caso o usuário tenha clicado em um único dia no modo mensal
                        $('#agen_inicio').val(inicialData + "T" + dateAtual.split("T")[1]); // Define o início com a data e hora atual
                        //
                        if (finalFormatada.split("T")[0] === inicialData) {
                            $('#agen_fim').val('');
                        } else {
                            $('#agen_fim').val(finalFormatada.split("T")[0] + "T" + dateAtual.split("T")[1]);
                        }
                    } else {
                        var inicial = info.start.toISOString().slice(0, 16);
                        var final = info.end.toISOString().slice(0, 16);
                        //
                        var [dataInicial] = inicial.split("T");
                        var [dataFinal] = final.split("T");
                        // Caso o usuário tenha selecionado um intervalo de tempo (semanal ou diário)
                        var horaInicio = info.start.getHours().toString().padStart(2, '0') + ':' + info.start.getMinutes().toString().padStart(2, '0'); // Hora inicial
                        var horaFim = info.end.getHours().toString().padStart(2, '0') + ':' + info.end.getMinutes().toString().padStart(2, '0'); // Hora final
                        
                        console.log(dataFinal);
                        console.log(dataInicial);
                        // Corrigir o caso onde o final está vindo com 1 dia a mais
                        if (info.allDay) {
                            $('#agen_fim').val('');
                        } else {
                            $('#agen_fim').val(dataFinal + "T" + horaFim);
                        }
                
                        // Define o início
                        $('#agen_inicio').val(dataInicial + "T" + horaInicio); // Início com data e hora
                    }
                
                    $('#eventModal').modal('show');
                },
                eventClick: function(info) {
                    limpaCamposAgenda();
                    $('#eventModalLabel').text('Editar evento');
                    $('#btnApagaEvento').show();
                    buscaDadosEvento(info.event.id);
                    $('#eventModal').modal('show');
                },
                height: 'auto',
                contentHeight: 'auto',

                // Salva a visualização e data atual sempre que a visualização muda
                datesSet: function(info) {
                    localStorage.setItem('calendarView', info.view.type);
                    localStorage.setItem('calendarDate', info.startStr);
                }
            });

            calendar.render();

            $.each(data.result, function(index, reg) {
                var diaTodo = reg.agen_fim == null;
                var dataFim = diaTodo ? reg.agen_inicio : reg.agen_fim;

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
    $('#listaImagensAgenda').html("");  // Limpa o campo de ID
    $('#inputFile').val('');  // Limpa o campo de ID
    $('#fileCount').html("Nenhum arquivo selecionado");  // Limpa o campo de ID
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
            $('#listaImagensAgenda').html(data.imagensAgenda);
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

function abrirModal(imageUrl, tipo, extensao) {
    if(tipo == 'img'){
        $('#modalImage').attr('src', imageUrl);
        $('#modalImage').show();
        $('#modalVideo').hide();
    }
    if(tipo == 'video'){
        $('#modalVideo').attr('src', imageUrl);
        $('#modalVideo').attr('type', 'video/' + extensao);
        $('#modalVideo').show();
        $('#modalImage').hide();
    }
    $('#imageModal').modal('show');
}

function confirmaExclusaoAnexos(idagenda_anexos){
    confirmar('Tem certeza de que deseja excluir esta imagem?', '<b>Confirme sua ação</b>', 'excluirImagem(' + idagenda_anexos + ')', 'tada');
}

function excluirImagem(idagenda_anexos){
    $.ajax({
        url: 'agenda_grava.php',
        type: 'POST',
        data: { operacaoAjax: "excluirFoto",
            id_cadastro: idagenda_anexos
        },
        success: function(response) {
            var data = JSON.parse(response);
            if (data.retorno == "ok") {
                $("#uploadAgenda_" + idagenda_anexos).remove();  // Remove o container da imagem
            } else {
                alert('Erro ao excluir a imagem.');
            }
        },
        error: function() {
            alert('Erro ao tentar excluir a imagem.');
        }
    });
}


function limparModalVisualizacao() {
    // Limpa e oculta o elemento de imagem
    var modalImage = document.getElementById('modalImage');
    modalImage.src = "";
    modalImage.style.display = "none";

    // Limpa e oculta o elemento de vídeo
    var modalVideo = document.getElementById('modalVideo');
    modalVideo.pause(); // Pausa o vídeo
    modalVideo.src = "";
    modalVideo.load(); // Reseta o player
    modalVideo.style.display = "none";
    //
    $('body').addClass('modal-open');
    //
    console.log("limpei modal");
}
