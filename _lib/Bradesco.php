<?php

class Bradesco {
	public static $merchantId = '';
	public static $cedente = '';
	public static $banco = '237';
	public static $agencia = '';
	public static $conta = '';
	public static $assinatura = '';
	public static $manager = '';
	public static $senha = '';
	public static $shoppingId = 0;
	public static $teste = false;
	public static $boleto_repository = null;
	public static $status_handler = null;
	public static $status_cod = array(
        '10' => 'Aguardando Pagamento',
        '13' => 'Aguardando Pagamento',
        '14' => 'Aguardando Pagamento',
        '21' => 'Pago',
        '23' => 'Pago',
        '22' => 'Cancelado',
        '12' => 'Cancelado'
    );

	public static function setup($merchantId,$cedente,$agencia,$conta,$assinatura,$manager,$senha,$teste) {
		static::setMerchantId($merchantId);
		static::setCedente($cedente);
		static::setAgencia($agencia);
		static::setConta($conta);
		static::setAssinatura($assinatura);
		static::setManager($manager);
		static::setSenha($senha);
		static::setTeste($teste);
	}

	public static function setBoletoRepository($repository) {
		static::$boleto_repository = $repository;
	}	
	public static function setStatusHandler($handler) {
		static::$status_handler = $handler;
	}	

	public static function getBanco() {
		return static::$banco;
	}

	public static function getShoppingId() {
		return static::$shoppingId;
	}

	public static function setMerchantId($merchantId) {
		static::$merchantId = $merchantId;
	}

	public static function getMerchantId() {
		return static::$merchantId;
	}

	public static function setCedente($nome) {
		static::$cedente = str_replace(array('(',')'),array('',''),$nome);
	}

	public static function getCedente() {
		$cedente = static::$cedente;
		if (static::$teste) {
			$cedente = "AMBIENTE DE TESTE";
		}
		return $cedente;
	}

	public static function setAgencia($agencia) {
		static::$agencia = str_pad(preg_replace('/\D/','',$agencia),4,'0',STR_PAD_LEFT);;
	}

	public static function getAgencia() {
		$agencia = static::$agencia;
		if (static::$teste) {
			$agencia = "0001";
		}
		return $agencia;
	}

	public static function setConta($conta) {
		static::$conta = str_pad(preg_replace('/\D/','',$conta),7,'0',STR_PAD_LEFT);;
	}

	public static function getConta() {
		$conta = static::$conta;
		if (static::$teste) {
			$conta = '1234567';
		}
		return $conta;
	}

	public static function setAssinatura($assinatura = '') {
		static::$assinatura = $assinatura;
	}

	public static function getAssinatura() {
		$assinatura = static::$assinatura;
		if (static::$teste) {
			$assinatura = '233542AD8CA027BA56B63C2E5A530029F68AACD5E152234BFA1446836220CAA53BD3EA92B296CA94A313E4E438AD64C1E4CF2CBAD6C67DAA00DE7AC2C907A99979A5AB53BFEF1FD6DD3D3A24B278536929F7F747907F7F922C6C0F3553F8C6E29D68E1F6E0CA2566C46C63A2DD65AFF7DF4802FBF4811CA58619B33989B8DDF8';
		}
		return $assinatura;
	}

	public static function setManager($manager = '') {
		static::$manager = $manager;
	}

	public static function getManager() {
		return static::$manager;
	}

	public static function setSenha($senha = '') {
		static::$senha = $senha;
	}

	public static function getSenha() {
		return static::$senha;
	}

	public static function setTeste($teste = false) {
		static::$teste = $teste?true:false;
	}

	public static function getTeste() {
		return static::$teste?true:false;
	}

	public static function getEndpoint() {
		$merchantId = static::getMerchantId();
		//$endpoint = "https://mup.comercioeletronico.com.br/sepsBoletoRet/{$merchantId}/prepara_pagto.asp?MerchantId={$merchantId}";
		$endpoint = "https://mup.comercioeletronico.com.br";
		if (static::$teste) {
			//$endpoint = "http://mupteste.comercioeletronico.com.br/sepsBoletoRet/{$merchantId}/prepara_pagto.asp?MerchantId={$merchantId}";
			$endpoint = "http://mupteste.comercioeletronico.com.br";
		}

		return $endpoint;
	}

	public static function getToken($orderId) {
		if ($orderId=='' || !$orderId) throw new Exception("OrderId inválido.");

		$assinatura = static::getAssinatura();	
		$token= md5($assinatura.$orderId).$orderId;
		$token = $token.md5($token);
		return $token;
	}

	public static function getBoletoUrl($orderId) {
		$merchantId = static::getMerchantId();
		$endpoint = static::getEndpoint();
		$token = static::getToken($orderId);
		return $endpoint.="/paymethods/conteudo/pagamento/frame_boleto_ret.asp?MerchantId={$merchantId}&OrderId={$orderId}&token={$token}";		

	}

	public static function getBoletoHtml($orderId) {
		$token = static::getToken($orderId);

		$root = dirname(dirname(__FILE__));
		$files_path = $root.'/_files';
		$boletos_path = $files_path.'/boleto';


		$filename = $boletos_path.'/'.$token.'.html';

		if (!file_exists($filename)) {
			
			if (!file_exists($boletos_path)) {
				mkdir($boletos_path,0777,true);
			}				
			
			$url = Bradesco::getBoletoUrl($orderId);

			//echo $url;

			$html = file_get_contents($url);
			$doc = phpQuery::newDocument($html);
			phpQuery::selectDocument($doc);

			//pq('head')->prepend("<meta charset='utf-8' />");
			pq('head')->prepend("<base href='http://mupteste.comercioeletronico.com.br/paymethods/conteudo/pagamento/' />");
			pq('table:first')->remove();
			pq('#divFooter')->remove();
			pq('#escDIVpress')->remove();
			$txt = preg_replace('/\&amp;nbsp([^;])/','&nbsp;$1',$doc.'');


			file_put_contents($filename,$txt);

		}

		return Site::getBaseUrl()."_files/boleto/{$token}.html";

	}

	public static function getLinhaDigitavel($orderId) {
		$url = static::getBoletoHtml($orderId);

		$html = file_get_contents($url);
		$doc = phpQuery::newDocument($html);
		phpQuery::selectDocument($doc);
		$linha_digitavel = preg_replace('/[^\d\.]/',' ',pq("a b.txtarial")->text());
		$linha_digitavel = preg_replace('/\s+/',' ',$linha_digitavel);
		return $linha_digitavel;

	}

	public static function getBoletoPdf($orderId) {
		$token = static::getToken($orderId);

		$root = dirname(dirname(__FILE__));
		$files_path = $root.'/_files';
		$boletos_path = $files_path.'/boleto';


		$filename = $boletos_path.'/'.$token.'.pdf';

		if (!file_exists($filename)) {

			$boleto_url = static::getBoletoHtml($orderId);

			$url = "https://www.studiogt.com.br/wsdl/api.php/html/toPdf?".http_build_query(array('url'=>$boleto_url));

			file_put_contents($filename, file_get_contents($url));

		}

		return Site::getBaseUrl()."_files/boleto/{$token}.pdf";
	}

	private static function authBoleto() {
		$if = Request::post('if','');
		$numOrder = Request::post('numOrder','');
		$numeroTitulo = Request::post('NumeroTitulo','');
		$merchantId = Request::post('merchantId','');
		$cod = Request::post('cod','');
		$cctype = Request::post('cctype','');
		$valor_total = Request::post('valtotal','');

		/**

		TODO

		**/

		echo "<PUT_AUTH_OK>";
	}

	private static function getBoleto() {
		$merchantId = Request::post('merchantId','');
		$numOrder = Request::post('numOrder','');
		$token = Request::post('token');

		$orderId = substr($token, 32,-32);

		if (static::$boleto_repository) {
			$txt = call_user_func_array(static::$boleto_repository, array($orderId));		
			echo $txt;
		}
	}

	public static function exec() {
		$transId = Request::post('transId','');

		switch ($transId) {
			case 'putAuthBoleto':
				static::authBoleto();
				exit;
				break;
			case 'getBoleto':
				static::getBoleto();
				exit;
				break;
			default:
				# code...
				break;
		}
	}

	public static function getRetornoUrl($orderId='') {
		$endpoint = static::getEndpoint();
		$merchantId = static::getMerchantId();
		$manager = static::getManager();
		$senha = static::getSenha();
		$data = date('d/m/Y');

		$get = array();
		$get['merchantid'] = $merchantId;
		$get['data'] = $data;
		$get['Manager'] = $manager;
		$get['passwd'] = $senha;

		$orderId = preg_replace('/[^a-zA-Z0-9]/','',$orderId);

		$url = "/sepsmanager/ArqRetBradescoBoletoValorPago_XML.asp";

		if ($orderId!='') {
			$get['NumOrder'] = $orderId;
			$url = "/sepsmanager/ArqRetBradescoBoletoValorPago_XML2.asp";
		}

		$url = $endpoint.$url.'?'.http_build_query($get);

		//$url = "http://drop.studiogt.com.br/quartinho-bebe/index.php/teste";
		return $url;		
	}

	public static function getRetorno($orderId = '') {
		$url = static::getRetornoUrl($orderId);
		$text = file_get_contents($url);
		try {
			$xml = simplexml_load_string($text);
			if (!$xml) throw new Exception("Não foi possível processar o retorno.");

			foreach($xml->Bradesco->Pedido as $Pedido) {
				static::processaRetorno($Pedido);
			}
		} catch (Exception $e) {
			error_log(__FILE__.' - '.$e->getLine().' - '.$e->getMessage());
		}
	}

	public static function getStatusByCode($code) {
		return static::$status_cod[$code];
	}

	public static function processaRetorno($Pedido) {
		$retorno = new stdclass();
		$retorno->numero = $Pedido['Numero'].'';
		$retorno->valor = ($Pedido['Valor'].'')/100.0;
		$retorno->data = dmY2Ymd($Pedido['Data'].'');
		$retorno->linha_digitavel = $Pedido['LinhaDigitavel'].'';
		$retorno->valor_pago = ($Pedido['ValorPago'].'')/100.0;
		$retorno->data_pagamento = dmY2Ymd($Pedido['DataPagamento'].'');
		$retorno->status = static::getStatusByCode($Pedido['Status'].'');
		$retorno->erro = $Pedido['Erro'].'';

		if (static::$status_handler) {
			call_user_func_array(static::$status_handler, array($retorno));			
		}

	}
}

