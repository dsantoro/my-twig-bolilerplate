<?php

class BradescoOrder {
	private $id;
	private $itens = array();
	
	public function __construct($id=null) {
		if ($id) $this->setId($id);

	}

	public function setId($id = '') {
		$id = substr(preg_replace('/[^0-9]/','',$id),0,9);

		if (strlen($id)==0) throw new Exception("OrderId Inválido.".$id);
		$this->id = $id;
	}

	public function getId() {
		return $this->id;
	}

	public function addItem($descritivo,$quantidade,$unidade,$valor,$adicional,$valor_adicional) {		
		$this->itens[] = new BradescoItem($descritivo,$quantidade,$unidade,$valor,$adicional,$valor_adicional);
	}

	public function getItens() {
		return $this->itens;
	}

	public function render() {
		$txt = "
			<BEGIN_ORDER_DESCRIPTION>
			<orderid>=({$this->id})
		";
		foreach($this->itens as $item) {
			$txt.= $item->render();
		}
		$txt .= "<END_ORDER_DESCRIPTION>";
		return $txt;
	}

	public function getTotal() {
		$total = 0.0;
		foreach ($this->itens as $item) {
			$total += $item->getTotal();
		}
		return $total;
	}
}