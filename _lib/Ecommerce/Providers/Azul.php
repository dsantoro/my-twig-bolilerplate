<?php

namespace Ecommerce\Providers;
use \R;

class Azul {
	public $destino;
	public $localidade;
	public $prazo;
	public $redespacho = false;
	public $tipo_tarifa;
	public $cidade;
	public $uf;
	public $sigla_tabela;
	public $tabela;
	public $pesos = array();
	public $valor_redespacho_adicional = 0.0;
	public $valor_kilo_adicinal = 0.0;
	public $valor_kilo_adicinal_interior = 0.0;
	public $tabela_interior;

	public function setDestino($destino = '') {
		$destino = preg_replace('/\D/','',$destino);
		if (strlen($destino) < 8) throw new \Exception("CEP inválido.");
		$destino = mascara_string("#####-###",$destino);
		$this->destino = $destino;
		$this->localidade = R::findOne("localidadeazul","deleted=0 and cep_inicial <= ? and cep_final >= ? limit 1",array($this->destino,$this->destino));
		$this->uf = $this->localidade->uf;
		$this->cidade = $this->localidade->cidade;
		$this->prazo = max($this->localidade->prazo_standard,$this->localidade->prazo_amanha,$this->localidade->prazo_ecommerce);
		$this->redespacho = (int)$this->localidade->redespacho == 1 ? true : false;
		$this->tipo_tarifa = $this->localidade->tarifa;
		$this->sigla_tabela = $this->localidade->tabela_precos;
		if ($this->tipo_tarifa == 'Interior') {
			$this->tabela = (object)R::getRow('SELECT tarifaazul.* FROM tarifaazul INNER JOIN tarifaazultabela ON tarifaazultabela.deleted=0 and tarifaazultabela.tarifaazul_id = tarifaazul.id and tarifaazultabela.sigla=? WHERE tarifaazul.deleted=0 and tarifaazul.tipo=?  limit 1',array($this->sigla_tabela,$this->tipo_tarifa));
			if (!isset($this->tabela->id)) {
				$this->tabela_interior = (object)R::getRow('SELECT tarifaazul.* FROM tarifaazul INNER JOIN tarifaazultabela ON tarifaazultabela.deleted=0 and tarifaazultabela.tarifaazul_id = tarifaazul.id and tarifaazultabela.sigla=? WHERE tarifaazul.deleted=0 and tarifaazul.tipo=?  limit 1',array($this->uf,$this->tipo_tarifa));
				$this->tabela = (object)R::getRow('SELECT tarifaazul.* FROM tarifaazul INNER JOIN tarifaazultabela ON tarifaazultabela.deleted=0 and tarifaazultabela.tarifaazul_id = tarifaazul.id and tarifaazultabela.sigla=? WHERE tarifaazul.deleted=0 and tarifaazul.tipo=?  limit 1',array($this->sigla_tabela,'Capital'));
				$this->valor_kilo_adicinal_interior = $this->tabela_interior->valor_adicional;
			}
		} else {
			$this->tabela = (object)R::getRow('SELECT tarifaazul.* FROM tarifaazul INNER JOIN tarifaazultabela ON tarifaazultabela.deleted=0 and tarifaazultabela.tarifaazul_id = tarifaazul.id and tarifaazultabela.sigla=? WHERE tarifaazul.deleted=0 and tarifaazul.tipo=?  limit 1',array($this->sigla_tabela,$this->tipo_tarifa));
		}
		$this->valor_kilo_adicinal = $this->tabela->valor_adicional;
		$pesos = R::find('azulpeso','deleted=0 and peso != 0 order by peso asc');
		if ($this->redespacho) {
			$this->valor_redespacho_adicional = (double)R::getCell("SELECT valor_redespacho FROM azulpeso WHERE deleted=0 and peso=0");
			$this->valor_kilo_adicinal_interior = $this->valor_redespacho_adicional;
		}
		foreach($pesos as $peso) {
			$field = "valor_".((int)($peso->peso*1000));
			$peso->valor = $this->tabela->{$field};
			$peso->valor_interior = 0.0;
			if (isset($this->tabela_interior->id)) {
				$peso->valor_interior = $this->tabela_interior->{$field};
			}
			if ($this->redespacho) {
				$peso->valor_interior = $peso->valor_redespacho;
			}
			$this->pesos[$peso->peso] = $peso;
		}
	}

	public function getRates($shipping,$Shipping) {

		$this->setDestino($shipping->destino);

		if (!isset($shipping->peso_cubico_total)) {
			$shipping = $Shipping->getCubagem($shipping);
		}

		if (!isset($shipping->peso_cubico_total)) {
			return $shipping;
		}

		$peso_total = $shipping->peso_cubico_total;
		$peso_base = 0;
		foreach($this->pesos as $p => $peso) {
			$peso_base = $p;
			if ($p >= $peso_total) break;

		}
		$peso_excedente = 0;
		if ($peso_total > $peso_base) {
			$peso_excedente = ceil($peso_total - $peso_base);
		}
		$peso = $this->pesos[$peso_base];
		$valor_capital = $peso->valor;
		$valor_adicional = $this->valor_kilo_adicinal;
		$valor_interior = $peso->valor_interior;
		$valor_adicional_interior = $this->valor_kilo_adicinal_interior;
		$valor = $valor_capital + $valor_interior;

		$valor_adicional = ($valor_adicional + $valor_adicional_interior) * $peso_excedente;
		
		$valor_total = $valor + $valor_adicional;
		$result = new \stdclass();
		$result->valor = $valor_total;
		$result->prazo = max((int)$this->prazo,2);
		$result->servico = "TRANSPORTADORA";
		$result->codigo = "AZUL";

		$shipping->rates['AZUL'] = $result;

		return $shipping;
	}
}