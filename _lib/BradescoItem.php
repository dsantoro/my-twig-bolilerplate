<?php

class BradescoItem {
	private $descritivo = '';
	private $quantidade = 1;
	private $unidade = 'UN';
	private $valor = 0.0;
	private $adicional;

	public function __construct($descritivo = null,$quantidade = null,$unidade = null,$valor = null,$adicional = null,$valor_adicional = null) {
		if ($descritivo) $this->setDescritivo($descritivo);
		if ($quantidade) $this->setQuantidade($quantidade);
		if ($unidade) $this->setUnidade($unidade);
		if ($valor) $this->setValor($valor);
		if ($adicional && $valor_adicional) $this->setAdicional($adicional,$valor);
	}

	public function setDescritivo($descritivo = '') {
		$this->descritivo = str_replace(array('(',')'),array('',''),$descritivo);
	}

	public function getDescritivo() {
		return $this->descritivo;
	}

	public function setQuantidade($quantidade = 1) {
		$this->quantidade = (int)$quantidade;
	}

	public function getQuantidade() {
		return $this->quantidade;
	}

	public function setUnidade($unidade = 'UN') {
		$this->unidade = preg_replace('/[^a-zA-Z]/','',$unidade);
	}

	public function getUnidade() {
		return $this->unidade;
	}

	public function setValor($valor = 0.0) {
		$this->valor = number_format((double)$valor,2,'','');
	}

	public function getValor() {
		return $this->valor/100.0;
	}

	public function setAdicional($adicional = '',$valor_adicional = 0.0) {
		$this->adicional = new stdclass();
		$this->adicional->titulo = str_replace(array('(',')'),array('',''),$adicional);
		$this->adicional->valor = number_format((double)$valor_adicional,2,'','');
	}

	public function getAdicional() {
		$adicional = $this->adicional;
		if (!$adicional) {
			$adicional = new stdclass();
			$adicional->titulo = '';
			$adicional->valor = 0;
		}
		return $adicional;
	}

	public function getTotal() {
		$adicional = $this->getAdicional();
		$total = ($this->getValor() + $adicional->valor/100.0) * $this->getQuantidade();
		return $total;
	}

	public function render() {
		$txt = "
			<descritivo>=({$this->descritivo})
			<quantidade>=({$this->quantidade})
			<unidade>=({$this->unidade})
			<valor>=({$this->valor})
		";
		$adicional = $this->getAdicional();
		if ($adicional->valor!=0 && $adicional->titulo != '') {
			$txt .= "
				<adicional>=({$this->adicional->titulo})
				<valorAdicional>=({$this->adicional->valor})
			";
		} 
		return $txt;
	}
}