<?php
	class autoComplete{

		public function gerar($inputConsulta, $inputId, $tabela, $campoMostra, $campoId, $campoValor = '', $where = '', $qteLimite = 10, $functionSelected = ''){
			//Caso esteja em branco vai setar o campoMostra
			if(empty($campoValor)){
				$campoValor = $campoMostra;
			}
			//Utiliza base64 por causa das aspas simples
			$where = base64_encode($where);
			$campoMostra = base64_encode($campoMostra);
			$campoValor = base64_encode($campoValor);
			//
			$codigo_js = "$(document).ready(function(){
					          // Atribui evento e função para limpeza dos campos
					          $('#{$inputConsulta}').on('input', limpaCampos);

					          // Dispara o Autocomplete a partir do segundo caracter
					          $( '#{$inputConsulta}' ).autocomplete({
					            minLength: 2,
					            source: function( request, response ) {
					                $.ajax({
					                    url: '../autoComplete/consulta.php',
					                    dataType: 'json',
					                    method: 'POST', 
					                    data: {
					                      campoMostra: '{$campoMostra}',
					                      campoId: '{$campoId}',
					                      campoValor: '{$campoValor}',
					                      tabela: '{$tabela}',
					                      where: '{$where}',
					                      qteLimit : {$qteLimite},
					                      consulta:  $('#{$inputConsulta}').val()
					                    },
					                    success: function(data) {
					                       response(data);
					                    }
					                });
					            },
					            select: function(event, ui) {
					                $('#{$inputConsulta}').val(ui.item.valor);
					                $('#{$inputId}').val(ui.item.id);
									{$functionSelected}
					                return false;
					            }
					          })
					          .autocomplete('instance')._renderItem = function( ul, item ) {
								$('.ui-autocomplete').css('z-index', '99999');
					            return $( '<li>' )
					              .append( '<div>' + item.mostra + '</div>' )
					              .appendTo( ul );
					          };

					          // Função para limpar os campos caso a busca esteja vazia
					          function limpaCampos(){
					             var busca = $('#{$inputConsulta}').val();

					             if(busca == ''){
					              	$('#{$inputConsulta}').val(''); 
					             }
					          }
						})";

			return $codigo_js;      

		}

		public function criaCampos($campoTexto, $campoId, $placeholder, $exibeScam = false){
			$campos = '<div class="input-group">
						<input type="text" class="form-control" id="' . $campoTexto . '" name="' . $campoTexto . '" value="##' . $campoTexto . '##" placeholder="' . $placeholder . '">
						<span class="input-group-addon" style="padding: 0x 6px;">
							<input type="text" class="idAutoComplementar" size="2" readonly id="' . $campoId . '" name="' . $campoId . '" value="##' . $campoId . '##">
						</span>
					</div>';
			return $campos;
		}
		

	}


?>
