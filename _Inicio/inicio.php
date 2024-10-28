<?php
  require_once("../_BD/conecta_login.php");
  //
  //
  //Rotinas para graficos
  //
  //Grafico de contas pagas
  //
  $sql = "SELECT principal.cphi_data_pagto,
            tico_principal.tico_nome,
            tico_principal.idtipo_contas,
            tico_principal.tico_cor,
            IFNULL(
                SUM(
                    CASE
                        WHEN principal.cphi_operacao = 'Baixa' THEN principal.cphi_valor
                        WHEN principal.cphi_operacao = 'Reabertura' THEN -principal.cphi_valor
                        ELSE 0
                    END
                ),
                0
            ) AS total_dia
          FROM
            contapag_hist AS principal
            JOIN contapag AS ctpg_principal ON (principal.cphi_idcontapag = ctpg_principal.idcontapag)
            JOIN tipo_contas AS tico_principal ON (ctpg_principal.ctpg_idtipo_contas = tico_principal.idtipo_contas)
          WHERE
            principal.cphi_operacao IN ('Baixa', 'Reabertura')
            AND principal.cphi_data_pagto BETWEEN " . $util->sgr(date('Y-m') . '-01') . " AND " . $util->sgr(date('Y-m-d')) . "
          GROUP BY
            principal.cphi_data_pagto,
            tico_principal.idtipo_contas,
            tico_principal.tico_nome,
            tico_principal.tico_cor
          HAVING
            total_dia > 0
          ORDER BY
            principal.cphi_data_pagto";
  $res = $db->consultar($sql);
  //
  $dados = [];
  $labels = [];
  $titulos = [];
  $cores = [];
  $elemento = 'graficoContasPagas';
  foreach ($res as $reg) {
    $dataPagto = $reg['cphi_data_pagto'];
    // Adiciona a data aos rótulos se ainda não estiver presente
    if (!in_array($dataPagto, $labels)) {
      $labels[] = $dataPagto;
    }
  }
  //
  foreach ($res as $reg) {
    $dataPagto = $reg['cphi_data_pagto'];
    $titulo = $reg['tico_nome'];
    $corLinha = $reg['tico_cor'];
    $valor = $reg['total_dia'];

    // Adiciona o título às linhas se ainda não estiver presente
    if (!in_array($titulo, $titulos)) {
        $titulos[] = $titulo;
        $cores[$titulo] = $corLinha;
        $dados[$titulo] = array_fill(0, count($labels), 0); // Preenche com valores nulos
    }
    
    $indiceData = array_search($dataPagto, $labels);
    $dados[$titulo][$indiceData] = $valor;

  }
  //
  $scriptGraficoContasPagas = $util->criarGraficoBarraData($dados, $labels, $titulos, $cores, "graficoContasPagas");
  //
  //Grafico de contas recebidas
  //
  $sql = "SELECT principal.crhi_data_pagto,
            tico_principal.tico_nome,
            tico_principal.idtipo_contas,
            tico_principal.tico_cor,
            IFNULL(
                SUM(
                    CASE
                        WHEN principal.crhi_operacao = 'Baixa' THEN principal.crhi_valor
                        WHEN principal.crhi_operacao = 'Reabertura' THEN -principal.crhi_valor
                        ELSE 0
                    END
                ),
                0
            ) AS total_dia
          FROM
            contarec_hist AS principal
            JOIN contarec AS ctrc_principal ON (principal.crhi_idcontarec = ctrc_principal.idcontarec)
            JOIN tipo_contas AS tico_principal ON (ctrc_principal.ctrc_idtipo_contas = tico_principal.idtipo_contas)
          WHERE
            principal.crhi_operacao IN ('Baixa', 'Reabertura')
            AND principal.crhi_data_pagto BETWEEN " . $util->sgr(date('Y-m') . '-01') . " AND " . $util->sgr(date('Y-m-d')) . "
          GROUP BY
            principal.crhi_data_pagto,
            tico_principal.idtipo_contas,
            tico_principal.tico_nome,
            tico_principal.tico_cor
          HAVING
            total_dia > 0
          ORDER BY
            principal.crhi_data_pagto";
  $res = $db->consultar($sql);
  //
  $dados = [];
  $labels = [];
  $titulos = [];
  $cores = [];
  $elemento = 'graficoContasRecebidas';
  foreach ($res as $reg) {
    $dataPagto = $reg['crhi_data_pagto'];
    // Adiciona a data aos rótulos se ainda não estiver presente
    if (!in_array($dataPagto, $labels)) {
      $labels[] = $dataPagto;
    }
  }
  //
  foreach ($res as $reg) {
    $dataPagto = $reg['crhi_data_pagto'];
    $titulo = $reg['tico_nome'];
    $corLinha = $reg['tico_cor'];
    $valor = $reg['total_dia'];
    
    // Adiciona o título às linhas se ainda não estiver presente
    if (!in_array($titulo, $titulos)) {
      $titulos[] = $titulo;
      $cores[$titulo] = $corLinha;
      $dados[$titulo] = array_fill(0, count($labels), 0); // Preenche com valores nulos
    }
    
    $indiceData = array_search($dataPagto, $labels);
    $dados[$titulo][$indiceData] = $valor;
    
  }
  //
  $scriptGraficoContasRecebidas = $util->criarGraficoBarraData($dados, $labels, $titulos, $cores, "graficoContasRecebidas");
  //
  //Grafico de contas mensais
  //
  $dados = array();
  $cores = array();
  //
  $sql = "SELECT (IFNULL(SUM(CASE WHEN crhi_operacao = 'Baixa' THEN crhi_valor ELSE 0 END), 0) - IFNULL(SUM(CASE WHEN crhi_operacao = 'Reabertura' THEN crhi_valor ELSE 0 END), 0)) AS totalReceitas
          FROM contarec_hist
          WHERE crhi_data_pagto BETWEEN " . $util->sgr(date('Y-m') . '-01') . " AND " . $util->sgr(date('Y-m-d'));
  $totalReceitas = $db->retornaUmCampoSql($sql, "totalReceitas");
  //
  if($totalReceitas > 0){
    $dados["Recebimentos"] = $totalReceitas;
  }
  //
  //
  $sql = "SELECT (IFNULL(SUM(CASE WHEN cphi_operacao = 'Baixa' THEN cphi_valor ELSE 0 END), 0) - IFNULL(SUM(CASE WHEN cphi_operacao = 'Reabertura' THEN cphi_valor ELSE 0 END), 0)) AS totalDespesas
          FROM contapag_hist
          WHERE cphi_data_pagto BETWEEN " . $util->sgr(date('Y-m') . '-01') . " AND " . $util->sgr(date('Y-m-d'));
  $totalDespesas = $db->retornaUmCampoSql($sql, "totalDespesas");
  //
  if($totalDespesas > 0){
    $dados["Pagamentos"] = $totalDespesas;
  }
  //
  $cores[] = "#0062ff";
  $cores[] = "#ea1d06";
  //
  $scriptGraficoContasTotais = $util->criarGraficoPizzaData($dados, $cores, "graficoContasTotais");
  //
  if (isset($_SESSION['mensagemChavePagto'])) {
    $msg = $html->mostraMensagem("warning", $_SESSION['mensagemChavePagto']);
  }
  //
  //Abre o arquivo html e Inclui mensagens e trechos php
  $html = $html->buscaHtml(true);
  $html = str_replace("##Mensagem##", $msg, $html);
  $html = str_replace("##mesGrafico##", $util->mesExtenso(date("m")), $html);
  $html = str_replace("##scriptGraficoContasPagas##", $scriptGraficoContasPagas, $html);
  $html = str_replace("##scriptGraficoContasRecebidas##", $scriptGraficoContasRecebidas, $html);
  $html = str_replace("##scriptGraficoContasTotais##", $scriptGraficoContasTotais, $html);

  echo $html;
  exit;
  
  ?>
