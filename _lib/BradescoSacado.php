<?php

class BradescoSacado {
	private $nome = '';
	private $logradouro = '';
	private $numero = '';
	private $complemento = '';
	private $bairro = '';
	private $cidade = '';
	private $uf = '';
	private $cep = '';
	private $cpf = '';

	public function __construct($nome=null,$cpf=null,$cep=null,$logradouro=null,$numero=null,$complemento=null,$bairro=null,$cidade=null,$uf=null) {
		if ($nome) $this->setNome($nome);
		if ($cpf) $this->setCPF($cpf);
		if ($cep) $this->setCEP($cep);
		if ($logradouro) $this->setLogradouro($logradouro);
		if ($numero) $this->setNumero($numero);
		if ($complemento) $this->setComplemento($complemento);
		if ($bairro) $this->setBairro($bairro);
		if ($cidade) $this->setCidade($cidade);
		if ($uf) $this->setUF($uf);
	}

	public function setNome($nome = '') {
		$this->nome = str_replace(array('(',')'),array('',''),$nome);
	}

	public function setCPF($cpf) {
		$cpf = preg_replace('/\D/','',$cpf);
		if (strlen($cpf) < 11) throw new Exception("CPF inválido.");
		if (strlen($cpf) != 11 && strlen($cpf) != 14) throw new Exception("CNPJ inválido.");

		$this->cpf = $cpf;
	}

	public function setCEP($cep = '') {
		$cep = preg_replace('/\D/', '',$cep);
		if (strlen($cep)!=8) throw new Exception("CEP inválido.");
		$this->cep = $cep;
	}

	public function setLogradouro($logradouro = '') {
		$this->logradouro = str_replace(array('(',')'),array('',''),$logradouro);
	}

	public function setNumero($numero = '') {
		$this->numero = str_replace(array('(',')'),array('',''),$numero);
	}

	public function setComplemento($complemento = '') {
		$this->complemento = str_replace(array('(',')'),array('',''),$complemento);
	}

	public function setBairro($bairro = '') {
		$this->bairro = str_replace(array('(',')'),array('',''),$bairro);
	}

	public function setCidade($cidade = '') {
		$this->cidade = str_replace(array('(',')'),array('',''),$cidade);
	}

	public function setUF($uf = '') {
		$uf = strtoupper(preg_replace('/[^a-zA-Z]/','',$uf));
		if (strlen($uf)!=2) throw new Exception("UF inválido.");
		$this->uf = $uf;
	}

	public function render() {
		$txt = "
			<NOMESACADO>=({$this->nome})
			<ENDERECOSACADO>=({$this->logradouro}, {$this->numero} {$this->complemento} - {$this->bairro})
			<CIDADESACADO>=({$this->cidade})
			<UFSACADO>=({$this->uf})
			<CEPSACADO>=({$this->cep})
			<CPFSACADO>=({$this->cpf})
		";

		return $txt;
	}
}