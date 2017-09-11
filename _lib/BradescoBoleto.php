<?php

class BradescoBoleto {

	private $dias_vencimento = 5;
	private $vencimento = '';
	private $emissao = '';
	private $processamento = '';
	public $sacado;
	public $order;
	private $total = 0.0;
	private $instrucoes = array();

	public function __construct() {
		$this->emissao = date('Y-m-d');
		$this->processamento = $this->emissao;
		$this->sacado = new BradescoSacado();
		$this->order = new BradescoOrder();		

	}

	

	public function addInstrucao($instrucao) {
		if (count($this->instrucoes)>=12) return;
		$instrucoes = explode("\n",$instrucao);

		if (count($instrucoes)>1) {
			foreach($instrucoes as $instrucao) {
				$this->addInstrucao($instrucao);
			}
		} else {
			$this->instrucoes[] = str_replace(array('(',')'),array('',''),$instrucao);
		}

	}

	public function getInstrucoes() {
		return $this->instrucoes;
	}

	public function setDiasVencimento($dias_vencimento = 5) {
		$this->dias_vencimento = (int)$dias_vencimento;
	}

	public function getDiasVencimento() {
		return $this->dias_vencimento;
	}

	public function setVencimento($vencimento = '') {		
		if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/',$vencimento)) {
			$vencimento = explode('/',$vencimento);
			$vencimento = array_reverse($$vencimento);
			$vencimento = join("-",$vencimento);
		} else if (!preg_match('/^\d{4}\-\d{2}\-\d{2}$/',$vencimento)) {
			return;
		}
		$this->vencimento = $vencimento;
	}

	public function getVencimento() {
		if ($this->vencimento == '') {
			$vencimento = new DateTime($this->emissao) ;
			$vencimento->add(new DateInterval("P{$this->dias_vencimento}D"));
		} else {
			$vencimento = new DateTime($this->vencimento);
		}

		return $vencimento->format('d/m/Y');
	}
	
	public function setEmissao($emissao = '') {		
		if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/',$emissao)) {
			$emissao = explode('/',$emissao);
			$emissao = array_reverse($$emissao);
			$emissao = join("-",$emissao);
		} else if (!preg_match('/^\d{4}\-\d{2}\-\d{2}$/',$emissao)) {
			return;
		}
		$this->emissao = $emissao;
	}

	public function getEmissao() {
		if ($this->emissao == '') {
			$emissao = new DateTime();
		} else {
			$emissao = new DateTime($this->emissao);
		}

		return $emissao->format('d/m/Y');
	}

	public function setProcessamento($processamento = '') {		
		if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/',$processamento)) {
			$processamento = explode('/',$processamento);
			$processamento = array_reverse($$processamento);
			$processamento = join("-",$processamento);
		} else if (!preg_match('/^\d{4}\-\d{2}\-\d{2}$/',$processamento)) {
			return;
		}
		$this->processamento = $processamento;
	}

	public function getProcessamento() {
		if ($this->processamento == '') {
			$processamento = new DateTime();
		} else {
			$processamento = new DateTime($this->processamento);
		}

		return $processamento->format('d/m/Y');
	}


	public function render() {
		$cedente = Bradesco::getCedente();
		$banco = Bradesco::getBanco();
		$agencia = Bradesco::getAgencia();
		$conta = Bradesco::getConta();
		$assinatura = Bradesco::getAssinatura();

		$data_emissao = $this->getEmissao();
		$data_processamento = $this->getProcessamento();
		$data_vencimento = $this->getVencimento();

		$dados_sacado = $this->sacado->render();
		$numero_pedido = $this->order->getId();
		$valor_documento = 'R$'.number_format($this->order->getTotal(),2,',','.');
		$shoppingId = Bradesco::getShoppingId();

		$instrucoes = '';
		foreach($this->instrucoes as $index=>$instrucao) {
			$i = $index+1;			
			$instrucoes .= "
				<INSTRUCAO{$i}>=({$instrucao})
			";
		}

		$txt = $this->order->render();
		$txt .= "
			<BEGIN_BOLETO_DESCRIPTION>
				<CEDENTE>=({$cedente})
				<BANCO>=({$banco})
				<NUMEROAGENCIA>=({$agencia})
				<NUMEROCONTA>=({$conta})
				<ASSINATURA>=({$assinatura})
				<DATAEMISSAO>=({$data_emissao})
				<DATAPROCESSAMENTO>=({$data_processamento})
				<DATAVENCIMENTO>=({$data_vencimento})
				{$dados_sacado}
				<NUMEROPEDIDO>=({$numero_pedido})
				<VALORDOCUMENTOFORMATADO>=({$valor_documento})
				<SHOPPINGID>=({$shoppingId})
				<NUMDOC>=({$numero_pedido})
				{$instrucoes}
			<END_BOLETO_DESCRIPTION>
		";
		return $txt;
	}
	
}