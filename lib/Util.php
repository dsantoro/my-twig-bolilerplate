<?php

namespace Lib;

class Util {
	public static function linkfy($str) {
		$foreign_characters = array(
			'/ä|æ|ǽ/' => 'ae',
			'/ö|œ/' => 'oe',
			'/ü/' => 'ue',
			'/Ä/' => 'Ae',
			'/Ü/' => 'Ue',
			'/Ö/' => 'Oe',
			'/À|Á|Â|Ã|Ä|Å|Ǻ|Ā|Ă|Ą|Ǎ/' => 'A',
			'/à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª/' => 'a',
			'/Ç|Ć|Ĉ|Ċ|Č/' => 'C',
			'/ç|ć|ĉ|ċ|č/' => 'c',
			'/Ð|Ď|Đ/' => 'Dj',
			'/ð|ď|đ/' => 'dj',
			'/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě/' => 'E',
			'/è|é|ê|ë|ē|ĕ|ė|ę|ě/' => 'e',
			'/Ĝ|Ğ|Ġ|Ģ/' => 'G',
			'/ĝ|ğ|ġ|ģ/' => 'g',
			'/Ĥ|Ħ/' => 'H',
			'/ĥ|ħ/' => 'h',
			'/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ/' => 'I',
			'/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı/' => 'i',
			'/Ĵ/' => 'J',
			'/ĵ/' => 'j',
			'/Ķ/' => 'K',
			'/ķ/' => 'k',
			'/Ĺ|Ļ|Ľ|Ŀ|Ł/' => 'L',
			'/ĺ|ļ|ľ|ŀ|ł/' => 'l',
			'/Ñ|Ń|Ņ|Ň/' => 'N',
			'/ñ|ń|ņ|ň|ŉ/' => 'n',
			'/Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ/' => 'O',
			'/ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º/' => 'o',
			'/Ŕ|Ŗ|Ř/' => 'R',
			'/ŕ|ŗ|ř/' => 'r',
			'/Ś|Ŝ|Ş|Š/' => 'S',
			'/ś|ŝ|ş|š|ſ/' => 's',
			'/Ţ|Ť|Ŧ/' => 'T',
			'/ţ|ť|ŧ/' => 't',
			'/Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ/' => 'U',
			'/ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ/' => 'u',
			'/Ý|Ÿ|Ŷ/' => 'Y',
			'/ý|ÿ|ŷ/' => 'y',
			'/Ŵ/' => 'W',
			'/ŵ/' => 'w',
			'/Ź|Ż|Ž/' => 'Z',
			'/ź|ż|ž/' => 'z',
			'/Æ|Ǽ/' => 'AE',
			'/ß/'=> 'ss',
			'/Ĳ/' => 'IJ',
			'/ĳ/' => 'ij',
			'/Œ/' => 'OE',
			'/ƒ/' => 'f'
		);



		$link = strtolower(preg_replace(array_keys($foreign_characters), array_values($foreign_characters), $str));
		$link = preg_replace("/[^a-z0-9]/",' ',$link);
		$link = preg_replace("/\s\s+/",' ',$link);
		$link = str_replace(" ",'-',trim($link));
		return $link;
	}

	public static function mask($mascara='',$string='') {
		$string = preg_replace('/\D/','',$string);

		$predefined = array(
			'phone' => '(##) ####-####',
			'phone_large' => '(##) ####-#####',
			'cpf' => '###.###.###-##'
		);

		if (isset($predefined[$mascara])) {
			if ($mascara == 'phone' && strlen($string) == 11) {
				$mascara = 'phone_large';
			}			
			$mascara = $predefined[$mascara];
		}

		$string = str_replace(" ","",$string);
		for($i=0;$i<strlen($string);$i++) {
			$mascara[strpos($mascara,"#")] = $string[$i];
		}
		return $mascara;
	}

	public static function text_break($text,$limit) {		
		$texto = explode(' ', strip_tags($text));
		$retorno = '';
		foreach ($texto as $word) {
			if (strlen($retorno.$word)<=$limit) {
				$retorno.=$word.' ';
				} else {
				break;
			}
		}
		$retorno = trim($retorno);
		if (strlen($retorno)!=strlen(trim(strip_tags($text)))) $retorno.='...';

		
		return $retorno;
	}
	
	public static function text_break2($text,$limit=1) {
		
		$doc = phpQuery::newDocument($text);
		phpQuery::selectDocument($doc);
		
		$result = "";
		$count=0;
		foreach(pq('p') as $p) {    	
			$parts = explode("\n",trim(pq($p)->text(),"\n"));
			foreach($parts as $part) {
				$count++;
				if (trim($part,"\n")=='') continue;
				if ($count<=$limit) {
					if ($result != "") $result.="\n";
					$result.=$part;
					} else {
					break;
				}
			}
			if ($count>$limit) break;
		}
		return nl2br($result);
	}
	
}