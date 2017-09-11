<?php

namespace Ecommerce\Plugins;

class Cubagem {
	public function cubagem($item) {
		$largura = $item->getLargura();
		$altura = $item->getAltura();
		$comprimento = $item->getComprimento();
		$peso = $item->getPeso();

		$peso_cubico = $altura * $largura * $comprimento / 6000.0;

		if ($peso_cubico < 10) {
			return $peso / 1000.0;
		}
		return max($peso_cubico, $peso / 1000.0);
	}
	public function getCubagem($shipping) {
		$shipping->peso_cubico_total = 0;
		foreach($shipping->itens as $index=>$item) {
			$item->peso_cubico = $this->cubagem($item);
			$item->peso_cubico_total = $item->peso_cubico * $item->getQuantidade();
			$shipping->itens[$index] = $item;
			$shipping->peso_cubico_total += $item->peso_cubico;
		}

		return $shipping;
	}
}