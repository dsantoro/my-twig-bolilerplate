<?php

namespace Ecommerce;
use \Exception;

class Shipping {
	public $plugins = array();

	public function addPlugin($plugin) {
		$this->plugins[] = $plugin;
	}

	public function __call($method,$args) {
		if (count($args)) {
			$request = array_shift($args);			
		} else {
			$request = new stdclass();
		}

		foreach($this->plugins as $plugin) {
			if (method_exists($plugin, $method)) {
				$request = call_user_func_array(array($plugin,$method), array($request,$this));
			}
		}

		return $request;
	}

	public function run($cart) {
		$shipping = new stdclass();
		$shipping->origem = $cart->getOrigem();
		$shipping->destino = $cart->getDestino();
		$shipping->itens = $cart->getItens();

		$cart->shipping = $this->getRates($shipping);

		return $cart;
	}
}