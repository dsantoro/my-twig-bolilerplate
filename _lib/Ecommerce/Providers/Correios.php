<?php

namespace Ecommerce\Providers;

class Correios {
	public $servicos = array(
		'40010' => 'SEDEX',
		'41106' => 'PAC'
	);

	public $destino;
	public $origem;
	public $client;
	public $soap_url = "https://wsdl.studiogt.com.br/correios/calculador/CalcPrecoPrazo.wsdl";

	public function __construct() {
		$this->client = new \SoapClient($this->soap_url);
	}

	public function setDestino($destino = '') {
		$destino = preg_replace('/\D/','',$destino);
		if (strlen($destino) < 8) throw new \Exception("CEP inválido.");
		$this->destino = $destino;
		return $this;
	}

	public function setOrigem($origem = '') {
		$origem = preg_replace('/\D/','',$origem);
		if (strlen($origem) < 8) throw new \Exception("CEP inválido.");
		$this->origem = $origem;
		return $this;
	}
	public function getRates($shipping,$Shipping) {
		$this->setOrigem($shipping->origem);
		$this->setDestino($shipping->destino);

		if (!isset($shipping->packages)) {
			$shipping = $Shipping->getPackages($shipping);
		}

		if (!isset($shipping->packages)) {
			return $shipping;
		}

		$results = array();
		$errors = array();
		
		foreach($this->servicos as $codigo => $servico) {
			$errors[$codigo] = false;
		}

		
		foreach($shipping->packages as $package_index=>$package) {
			$params = array(
				'nCdEmpresa' => '',
				'sDsSenha' => '',
				'nCdServico' => join(',',array_keys($this->servicos)),
				'sCepOrigem' => $this->origem,
				'sCepDestino' => $this->destino,
				'nVlPeso' => number_format($package->peso_cubico,1,'.',''),
				'nCdFormato' => 1,
				'nVlComprimento' => 20.0,
				'nVlAltura' => 20.0,
				'nVlLargura' => 20.0,
				'nVlDiametro' => 0.0,
				'sCdMaoPropria' => 'N',
				'nVlValorDeclarado' => $package->valor,
				'sCdAvisoRecebimento' => 'N'
			);

			$resp = $this->client->CalcPrecoPrazo($params);
			$resp = $resp->CalcPrecoPrazoResult->Servicos->cServico;

			$package->rates = array();

			foreach($resp as $index=>$servico) {
				if ($servico->Erro != 0) {
					$errors[$servico->Codigo] = true;
					unset($resp->index);
					continue;
				}
				
				$servico->Servico = $this->servicos[$servico->Codigo];
				
				$rate = new \stdclass();
				$rate->valor = (double)str_replace(',','.',str_replace('.','',$servico->Valor));
				$rate->prazo = (int)$servico->PrazoEntrega;
				$rate->codigo = $servico->Codigo;
				$rate->servico = $servico->Servico;
				$package->rates[] = $rate;



				if (isset($results[$servico->Codigo])) {
					$result = $results[$servico->Codigo];
					$result->valor += $rate->valor;
					$result->prazo = max($result->prazo,$rate->prazo);
				} else {
					$result = new \stdclass();
					$result->valor = $rate->valor;
					$result->prazo = max($rate->prazo,2);
					$result->servico = $rate->servico;
					$result->codigo = $rate->codigo;					
				}
				

				$results[$servico->Codigo] = $result;
			}
			$shipping->packages[$package_index] = $package;
		}

		foreach($errors as $codigo=>$erro) {
			if (isset($results[$codigo]) && $erro!=0) unset($results[$codigo]); 
		}

		foreach($results as $codigo => $rate) {
			$shipping->rates[$codigo] = $rate;
		}
		
		return $shipping;
	}
}