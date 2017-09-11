<?php

class I18n {

	public static $language;
	public static $idiomas;
	public static $traducoes;
	public static $regex;


	public static function setup($language='br',$alternative=false) {
		static::$language = $language;
		static::$regex = '/\{\{\s?([a-z0-9\-]*)\s?\|\s?default\s[\'"]([^\'"]*)[\'"]\s?\}\}/im';

		if ($alternative) {
			static::$regex = '/_\(\s{0,}([^\s\,]+)\s{0,},\s{0,}[\'\"]([^\'\"]*)[\'\"]\s{0,}\)/im';
		}
	}

	public static function getIdiomas() {
		if (isset(static::$idiomas)) return static::$idiomas;

		$idiomas = array();
		if (class_exists('R')) {
			$idiomas = R::getCol('SELECT idioma.sigla FROM idioma WHERE idioma.deleted=0 and idioma.sigla!="" GROUP BY idioma.sigla');			
		}
		if (count($idiomas)==0) {
			$idiomas = array('br');
		}
		static::$idiomas = $idiomas;
		return $idiomas;
	}

	public static function getTraducoes() {
		if (isset(static::$traducoes)) return static::$traducoes;

		$idiomas = static::getIdiomas();

		static::$traducoes = array();
		foreach($idiomas as $idioma) {
			static::$traducoes[$idioma] = array();
		}

		if (class_exists('R')) {			
			$traducoes = R::find('traducao','deleted=0');
			foreach($traducoes as $traducao) {
				foreach($idiomas as $idioma) {
					static::$traducoes[$idioma][$traducao->chave] = $traducao->{"valor_".$idioma};
				}
			}
		}

		return static::$traducoes;
	}

	public static function addTraducao($chave,$default) {
		$idiomas = static::getIdiomas();

		if (class_exists('R')) {
			$traducao = R::dispense('traducao');
			$traducao->chave = $chave;
			$traducao->ativo = 1;
			foreach($idiomas as $idioma) {
				$traducao->{"valor_".$idioma} = $default;
				static::$traducoes[$idioma][$chave] = $default;
			}
			R::store($traducao);
		}

		foreach($idiomas as $idioma) {			
			static::$traducoes[$idioma][$chave] = $default;
		}

	}

	public static function translate($txt) {
		$traducoes = static::getTraducoes();
		$traducoes = $traducoes[static::$language];

		$from = array();
		$to = array();

		$regex = '/\{\{\s?([a-z0-9\-]*)\s?\|\s?default\s[\'"]([^\'"]*)[\'"]\s?\}\}/im';

		if (preg_match_all(static::$regex, $txt, $matches)) {
			foreach($matches[1] as $index=>$value) {
				
				$keyword = $matches[0][$index];
				$chave = $matches[1][$index];
				$default = $matches[2][$index];

				$chave = trim($chave);
				$default = trim($default);

				if (isset($traducoes[$chave])) {
					$valor = $traducoes[$chave];
				} else {
					$valor = $default;
					static::addTraducao($chave,$default);
					static::$traducoes = null;
					$traducoes = static::getTraducoes();

					if (!isset($traducoes[static::$language])) {
					}
					
					$traducoes = $traducoes[static::$language];
				}

				$from[$keyword] = $keyword;
				$to[$keyword] = $traducoes[$chave];
			}
		}
		$content = str_replace($from,$to,$txt);
		return $content;


	}


}