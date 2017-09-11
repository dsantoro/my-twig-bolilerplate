<?php

namespace Lib;

use \R;

class I18n {
	private static $i18n;
	private static $language;

	public static function setup($language) {
		static::$language = $language;
	}

	public static function getI18n() {
		if (static::$i18n !== null) return static::$i18n;
		$cms = Config::get('cms');
		$cms->setLanguage(static::$language);
		$traducoes = $cms->traducao->where('ativo=1');

		static::$i18n = array();
		foreach($traducoes as $traducao) {
			static::$i18n[$traducao->chave] = $traducao->valor;
		}

		return static::$i18n;
	}

	public static function get($key, $default) {
		$i18n = static::getI18n();
		if (!isset($i18n[$key])) {
			static::$i18n[$key] = $default;
			$traducao = R::findOne('traducao','chave = ? limit 1',array($key));
			if (!isset($traducao->id)) {
				$traducao = R::dispense('traducao');
			}
			$traducao->ativo=1;
			$traducao->chave = $key;
			$traducao->{"valor_".static::$language} = $default;
			R::store($traducao);
		}
		return $i18n[$key];
	}
}