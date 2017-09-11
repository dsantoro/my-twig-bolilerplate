<?php

class Cart {
	public static function setStatusPedido($pedido,$status) {
		
	}
	public static function bradescoStatusHandler($retorno) {
		$pedido = R::findOne('pedido','id=?',array((int)$retorno->numero));
		if (!isset($pedido->id)) {
			error_log(__FILE__." - Pedido {$numero} não encontrado.");
			return;
		}
		static::setStatusPedido($pedido,$retorno->status);
	}
	public static function bradescoBoletoRepository($id) {
		
        $pedido = R::findOne('pedido','id=?',array($id));
        if (!isset($pedido->id)) return false;

        $boleto = new BradescoBoleto();
        

        $boleto->order->setId($id);

        $boleto->sacado->setNome($pedido->cliente_nome);
        $boleto->sacado->setCPF($pedido->cliente_cpf);
        $boleto->sacado->setCEP($pedido->endereco_cep);
        $boleto->sacado->setLogradouro($pedido->endereco_logradouro);
        $boleto->sacado->setNumero($pedido->endereco_numero);
        $boleto->sacado->setBairro($pedido->endereco_bairro);
        $boleto->sacado->setCidade($pedido->endereco_cidade);
        $boleto->sacado->setUF($pedido->endereco_uf);

        $itens = R::find('item','deleted = 0 and pedido_id=?',array($id));
        $desconto_item = 0.0;
        if ((double)$pedido->desconto != 0.0) {
            $desconto_item = $pedido->desconto / count($itens);
        }
        foreach($itens as $item) {
            $boleto->order->addItem($item->titulo,$item->quantidade,'UN',$item->valor + $desconto_item/$item->quantidade,'',0.00);
            
        }
        if ((double)$pedido->frete != 0.0) {
            $boleto->order->addItem('Frete',1,'UN',$pedido->frete,'',0.0);            
        }
        
        if ((double)$pedido->extras != 0.0) {
            $boleto->order->addItem('Extras',1,'UN',$pedido->extras,'',0.0);            
        }


        $boleto->addInstrucao("Não receber após o vencimento.");
        $boleto->addInstrucao("Pagável em qualquer agência bancária.");

        $txt = $boleto->render();
        $txt = preg_replace('/\t/','',$txt);
        $txt = trim(preg_replace('/\n+/',"\n",$txt),"\n");
        
        return trim(toISO($txt));
	}
}