<?php

namespace Ecommerce\Plugins;

class Packages {
	public $peso_maximo = 30;
	public function __construct($peso_maximo = 30) {
		$this->setPesoMaximo($peso_maximo);
	}
	public function setPesoMaximo($peso_maximo = 30) {
		$this->peso_maximo = (int)$peso_maximo;
		if ($this->peso_maximo == 0) $this->peso_maximo = 30;
	}
	public function itemSort($a,$b) {
		if ($a->peso_cubico == $b->peso_cubico) return 0;
		return $a->peso_cubico > $b->peso_cubico ? -1 : 1;
	}
	public function getPackages($shipping,$Shipping) {
		$shipping->packages = array();

		$package_index = 0;

		uasort($shipping->itens, array($this,'itemSort'));

		if (!isset($shipping->peso_cubico_total)) {
			$shipping = $Shipping->getCubabem($shipping);			
		}

		if (!isset($shipping->peso_cubico)) {
			return $shipping;
		}

		foreach($shipping->itens as $item) {
			for($i=1;$i<=$item->getQuantidade();$i++) {
				$package_index = -1;
				foreach($shipping->packages as $index=>$package) {
					if ($package->peso_cubico + $item->peso_cubico > $this->peso_maximo) {
						continue;
					}
					$package_index = $index;
					break;
				}

				if ($package_index == -1) {
					$package = new \stdclass();
					$package->peso_cubico = $item->peso_cubico;
					$package->itens = array($item);
					$package->valor = $item->getValor();
					$shipping->packages[] = $package;
				} else {
					$package = $shipping->packages[$package_index];
					$package->itens[] = $item;
					$package->peso_cubico += $item->peso_cubico;
					$package->valor += $item->getValor();
					$shipping->packages[$package_index] = $package;
				}
			}
		}
		return $shipping;
	}
}