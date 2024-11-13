window.setTimeout(function(){
    $(".closeMsg").click();
    // document.getElementById("botao_alerta").click();
}, 6000);

function alertaPequeno(msg, titulo = '', animate = ''){
    if(titulo == ''){
        titulo = "<b style='color: red;'>Atenção</b>";
    }
    if(animate == ''){
        animate = 'bounceInUp';
    }
    bootbox.alert({
        title: titulo,
        message: msg,
        size: 'small',
        className: animate + ' animated'
    });
}

function alertaGrande(msg, titulo = '', animate = ''){
    if(titulo == ''){
        titulo = "<b style='color: red;'>Atenção</b>";
    }
    if(animate == ''){
        animate = 'bounceInUp';
    }
    bootbox.alert({
        title: titulo,
        message: msg,
        size: 'large',
        className: animate + ' animated'
    });
}

function confirmar(msg, titulo = '', functionCallback = '', animate = ''){
    if(animate == ''){
        animate = 'bounceInUp';
    }
    if(titulo == ''){
        titulo = '<b>Atenção</b>';
    }
    bootbox.confirm({
        title: titulo,
        message: msg,
        className: animate + ' animated',
        buttons: {
            cancel: {
                label: '<img src="../icones/excluir2.png"> Cancelar'
            },
            confirm: {
                label: '<img src="../icones/certo.png"> Confirmar'
            }
        },
        callback: function(result){
            if(result && functionCallback != ''){
                eval(functionCallback);
            }
        }
    });
}

function toFloat(string){
    var valor = '';
    //
    string = string.replace(".", "");
    string = string.replace(".", "");
    string = string.replace(".", "");
    string = string.replace(",", ".");
    //
    if(string != 0 && $.isNumeric(string)){
        valor = parseFloat(string);
        return valor;
    }else{
        return 0;
    }
}

function imprimir(link, id){
    var idEnvio = '';
    if(id <= 0 || id == '' || id == undefined){
        idEnvio = $("#id_cadastro").val();
    }else{
        idEnvio = id;
    }
    var linkCompleto = link + '?id_cadastro=' + idEnvio;
    window.open(linkCompleto, '_blank');
}

function imprimir2(link){
    window.open(link, '_blank');
}

function moeda(a, e, r, t) {
    let n = ""
      , h = j = 0
      , u = tamanho2 = 0
      , l = ajd2 = ""
      , o = window.Event ? t.which : t.keyCode;
    if (13 == o || 8 == o)
        return !0;
    if (n = String.fromCharCode(o),
    -1 == "0123456789".indexOf(n))
        return !1;
    for (u = a.value.length,
    h = 0; h < u && ("0" == a.value.charAt(h) || a.value.charAt(h) == r); h++)
        ;
    for (l = ""; h < u; h++)
        -1 != "0123456789".indexOf(a.value.charAt(h)) && (l += a.value.charAt(h));
    if (l += n,
    0 == (u = l.length) && (a.value = ""),
    1 == u && (a.value = "0" + r + "0" + l),
    2 == u && (a.value = "0" + r + l),
    u > 2) {
        for (ajd2 = "",
        j = 0,
        h = u - 3; h >= 0; h--)
            3 == j && (ajd2 += e,
            j = 0),
            ajd2 += l.charAt(h),
            j++;
        for (a.value = "",
        tamanho2 = ajd2.length,
        h = tamanho2 - 1; h >= 0; h--)
            a.value += ajd2.charAt(h);
        a.value += r + l.substr(u - 2, u)
    }
    return !1
}

function escondeExibeMenu(){
    $("body").toggleClass("sidebar-toggled");
    $(".sidebar").toggleClass("toggled");
    if ($(".sidebar").hasClass("toggled")) {
      $('.sidebar .collapse').collapse('hide');
    };
}

function direciona(link){
    $(location).attr('href', link);
  }

      
  


(function($) {
    "use strict"; // Start of use strict
    
    // Close any open menu accordions when window is resized below 768px
    $(window).resize(function() {
      if ($(window).width() < 768) {
        $('.sidebar .collapse').collapse('hide');
      }
      //
      if ($(window).width() >= 992) {
        $(".sidebar").removeClass("toggled");
      }
      //
      // Toggle the side navigation when window is resized below 480px
      if ($(window).width() < 480 && !$(".sidebar").hasClass("toggled")) {
        $("body").addClass("sidebar-toggled");
        $(".sidebar").addClass("toggled");
        $('.sidebar .collapse').collapse('hide');
      };
    });
    
    // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
    $('body.fixed-nav .sidebar').on('mousewheel DOMMouseScroll wheel', function(e) {
      if ($(window).width() > 768) {
        var e0 = e.originalEvent,
          delta = e0.wheelDelta || -e0.detail;
        this.scrollTop += (delta < 0 ? 1 : -1) * 30;
        e.preventDefault();
      }
    });
  
    // Scroll to top button appear
    $(document).on('scroll', function() {
      var scrollDistance = $(this).scrollTop();
      if (scrollDistance > 100) {
        $('.scroll-to-top').fadeIn();
      } else {
        $('.scroll-to-top').fadeOut();
      }
    });
  
    // Smooth scrolling using jQuery easing
    $(document).on('click', 'a.scroll-to-top', function(e) {
      var $anchor = $(this);
      $('html, body').stop().animate({
        scrollTop: ($($anchor.attr('href')).offset().top)
      }, 1000, 'easeInOutExpo');
      e.preventDefault();
    });
  

  })(jQuery); // End of use strict
  
  function abreContaRec(idcontarec){
    if(idcontarec > 0){
        $(location).attr('href', "../_Lancamentos/contarec_edita.php?id_cadastro=" + idcontarec);
    }
  }

  function abreModalPesquisa(){
    setTimeout(function() {
        $("#pesquisa").focus();
      }, 500);
  }