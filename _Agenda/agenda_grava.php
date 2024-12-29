<?php
require_once("../_BD/conecta_login.php");
require_once("tabelas.class.php");
// print_r($_POST);
// exit;
$paginaRetorno = 'agenda.php';
//
if ($_POST['operacaoAjax'] == 'registrarEvento'){
    //
    //
  	$db->setTabela("agenda", "idagenda");

    $dados['id']                                = $_POST['id_cadastro'];
    $dados['agen_titulo'] 	                    = $util->sgr($_POST['agen_titulo']);
    $dados['agen_descricao'] 	                = $util->sgr($_POST['agen_descricao']);
    $dados['agen_inicio'] 	                    = $util->sgr($_POST['agen_inicio'], true);
    $dados['agen_fim'] 	                        = $util->sgr($_POST['agen_fim'], true);
    $dados['agen_cor'] 	                        = $util->sgr($_POST['agen_cor']);
    if($_POST['id_cadastro'] <= 0){
        $dados['agen_idusuario'] 	            = $util->igr($_SESSION['idusuario']);
        $dados['agen_data_registro'] 	        = $util->dgr(date('d/m/Y H:i'));
    }
    //
    $db->gravarInserir($dados, true);
    //
    if($db->erro()){
        $ret['retorno'] = "erro";
        $ret['msg'] = $db->getErro();
      }else{
        $ret['retorno'] = "ok";
        if ($_POST['id_cadastro'] > 0) {
            $ret["idagenda"] = $_POST['id_cadastro'];
            $ret["eventoAtt"] = true;
        }else{
            $ret["idagenda"] = $db->getUltimoID();
            $ret["eventoAtt"] = false;
        }
        //
        //
        // Processa os arquivos enviados
        if (isset($_FILES['arquivo'])) {
            $uploadDir = '../uploadsAgenda/'; // Diretório para salvar os arquivos
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $db->setTabela("agenda_anexos", "idagenda_anexos");
            //
            foreach ($_FILES['arquivo']['tmp_name'] as $key => $tmpName) {
                $originalName = $_FILES['arquivo']['name'][$key];
                $fileExtension = pathinfo($originalName, PATHINFO_EXTENSION);
                $fileType = mime_content_type($tmpName);

                // Gera um nome aleatório para o arquivo
                do {
                    $randomName = uniqid('file_', true) . '.' . $fileExtension;
                    $filePath = $uploadDir . $randomName;
                } while (file_exists($filePath)); // Garante que o nome seja único

                if (move_uploaded_file($tmpName, $filePath)) {
                    unset($dadosArquivo);
                    // Salve a referência do arquivo no banco de dados
                    $dadosArquivo['agan_idagenda']      = $ret["idagenda"];
                    $dadosArquivo['agan_nome_original'] = $util->sgr($originalName);
                    $dadosArquivo['agan_nome_sistema']  = $util->sgr($randomName);
                    $dadosArquivo['agan_tipo_arquvo']   = $util->sgr($fileType); // Salva o tipo do arquivo
                    $dadosArquivo['agan_caminho']       = $util->sgr($filePath);
                    //
                    $db->gravarInserir($dadosArquivo, true);
                }
            }
        }
    }
    //
    echo json_encode($ret);
    exit;
}

if ($_POST['operacaoAjax'] == 'carregaEventos'){
    $sql = "SELECT * FROM agenda";
    $res = $db->consultar($sql);
    //
    $ret["result"] = $res;
    //
    echo json_encode($ret);
    exit;
}

if ($_POST['operacaoAjax'] == 'buscaEvento'){
    $sql = "SELECT * FROM agenda WHERE idagenda = " . $_POST["id_cadastro"];
    $regAgenda = $db->retornaUmReg($sql);
    //
    $sql = "SELECT * FROM agenda_anexos WHERE agan_idagenda = " . $_POST["id_cadastro"];
    $res = $db->consultar($sql);
    //
    $imagensAgenda = '';
    //
    foreach ($res as $reg) {
        $filePath = "../uploadsAgenda/" . $reg['agan_nome_sistema'];
        $fileExtension = pathinfo($reg['agan_nome_sistema'], PATHINFO_EXTENSION);
        //
        $imagensAgenda .= '<div class="col-md-4 col-12 pb-3" id="uploadAgenda_' . $reg['idagenda_anexos'] . '">';
            $imagensAgenda .= '<div class="image-container divAnexosAgenda d-flex justify-content-center align-items-center" style="position: relative;">';
                if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
                    // Visualização de imagens
                    $imagensAgenda .= '<img src="' . $filePath . '" width="200px" class="img-thumbnail img-clickable" onclick="abrirModal(\'' . $filePath . '\', \'img\', \'' . $fileExtension . '\')">';
                } elseif (in_array($fileExtension, ['mp4', 'webm', 'ogg'])) {
                    // Visualização de vídeos
                    $imagensAgenda .= '<i class="fa fa-file-video img-clickable" style="font-size: 50px; color: blue;" onclick="abrirModal(\'' . $filePath . '\', \'video\', \'' . $fileExtension . '\')"></i>';
                } elseif ($fileExtension === 'pdf') {
                    // Link para abrir PDFs em uma nova guia
                    $imagensAgenda .= '<a href="' . $filePath . '" target="_blank">
                                        <i class="fa fa-file-pdf img-clickable" style="font-size: 50px; color: red;"></i>
                                    </a>';
                } else {
                    // Download para outros arquivos
                    $imagensAgenda .= '<a href="' . $filePath . '" download>
                                        <i class="fa fa-download img-clickable" style="font-size: 50px; color: blue;"></i>
                                    </a>';
                }
                $imagensAgenda .= '<i class="fa fa-trash btn-delete" onclick="confirmaExclusaoAnexos(' . $reg['idagenda_anexos'] . ')" aria-hidden="true"></i>';
            $imagensAgenda .= '</div>';
        $imagensAgenda .= '</div>';
    }
    //
    $regAgenda['imagensAgenda'] = $imagensAgenda;
    //
    echo json_encode($regAgenda);
    exit;
}

if ($_POST['operacaoAjax'] == 'excluirEvento'){
    //
    //Busca os anexos para apagar
    //
    $sql = "SELECT * FROM agenda_anexos WHERE agan_idagenda = " . $_POST["id_cadastro"];
    $res = $db->consultar($sql);
    //
    $db->setTabela("agenda_anexos", "idagenda_anexos");
    foreach ($res as $reg) {
        if (file_exists($reg['agan_caminho'])) {
            unlink($reg['agan_caminho']);
        }
        $db->excluir($reg['idagenda_anexos']);
    }
    //
    $db->setTabela("agenda", "idagenda");
    $db->excluir($_POST['id_cadastro']);
    if($db->erro()){
        $ret['retorno'] = "erro";
        $ret['msg'] = $db->getErro();
    }else{
        $ret['retorno'] = "ok";
    }
    //
    echo json_encode($ret);
    exit;
}
//
if ($_POST['operacaoAjax'] == 'excluirFoto'){
    //
    $sql = "SELECT * FROM agenda_anexos WHERE idagenda_anexos = " . $_POST["id_cadastro"];
    $reg = $db->retornaUmReg($sql);
    //
    if (file_exists($reg['agan_caminho'])) {
        unlink($reg['agan_caminho']);
    }
    //
    $db->setTabela("agenda_anexos", "idagenda_anexos");
    $db->excluir($_POST['id_cadastro']);
    if($db->erro()){
        $ret['retorno'] = "erro";
        $ret['msg'] = $db->getErro();
    }else{
        $ret['retorno'] = "ok";
    }
    //
    echo json_encode($ret);
    exit;
}

?>
