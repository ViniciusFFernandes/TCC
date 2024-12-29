
function abrirScamProduto(){
    bootbox.dialog({
        title: "Escaneie o QR Code",
        message: '<div id="divLoad" style="width: 100%;"><center>Iniciando a Camera <img src="../icones/carregando.gif" width="25px"></center></div><div id="qr-reader" style="width: 100%;"></div>',
        size: "large",
        onShown: function() {
            // Inicia o scanner html5-qrcode
            const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                bootbox.hideAll(); // Fecha o modal ao escanear com sucesso
                html5QrcodeScanner.stop();
                console.log(decodedText);
                //buscaDadosProduto(decodedText);
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