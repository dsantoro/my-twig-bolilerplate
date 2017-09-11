<?php

class TNT {
	public static $client = null;
	public static $credentials = null;

	public static function getClient() {
		if (static::$client !== null) return static::$client;
		$client = new SoapClient("http://ws.tntbrasil.com.br/servicos/CalculoFrete?wsdl",array('trace'=>1));
		static::$client = $client;
		return $client;
	}

	public static function setCredentials($email,$cnpj,$ie,$situacao='ME',$divisao=1) {
		$credentials = new stdclass();
		$credentials->email = $email;
		$credentials->cnpj = $cnpj;
		$credentials->ie = $ie;
		$credentials->situacao = $situacao;
		$credentials->divisao = $divisao;
		$credentials->tipo_pessoa = strlen(preg_replace('/\D/','',$cnpj))==11?'F':'J';

		static::$credentials = $credentials;
	}

	public static function getCredentials() {
		return static::$credentials;
	}



	public static function frete($origem = '',$destino = '',$peso = 0.3,$valor = 1.00, $cnpj = '') {
		if ($peso <= 0) $peso = 0.3;
		$client = static::getClient();
		$credentials = static::getCredentials();

		$origem = preg_replace('/\D/', '', $origem);
		$destino = preg_replace('/\D/', '', $destino);

		$arguments = array (
		  'in0' =>  array(
		        'login' => $credentials->email,
		        'nrIdentifClienteRem' => $credentials->cnpj,
		        'tpSituacaoTributariaRemetente' => $credentials->situacao,
		        'nrInscricaoEstadualRemetente' => $credentials->ie,
		        'tpPessoaRemetente' => $credentials->tipo_pessoa,
		        'cdDivisaoCliente' => $credentials->divisao,
		        'nrInscricaoEstadualDestinatario' => '',
		        'nrIdentifClienteDest' => '007.550.710-26',
		        'tpPessoaDestinatario' => 'F',
		        'tpSituacaoTributariaDestinatario' => 'ME',
		        'cepOrigem' => $origem,
		        'cepDestino' => $destino,
		        'vlMercadoria' => number_format($valor,2,'.',''),
		        'psReal' => number_format($peso,3,'.',''),
		        'tpServico' => 'RNC',
		        'tpFrete' => 'C',
		        'senha' => ''
		      )
			); 


		$res = $client->calculaFrete($arguments);
		$out = $res->out;
		$resp = new stdclass;
		$resp->codigo = "00002";
		$resp->servico = "TRANSPORTADORA";
		$resp->prazo = (int)$out->prazoEntrega;
		$resp->valor = (double)$out->vlTotalFrete;

		//var_dump($out);
		if (isset($out->errorList)) {
			if (isset($out->errorList->string)) {
				error_log(__FILE__." - {$origem} - {$destino}  -".str_replace("\n"," ",print_r($out->errorList->string,true)));
			}
		}
		
		if ($resp->valor == 0.0) return array();

		return array($resp);
	}
}