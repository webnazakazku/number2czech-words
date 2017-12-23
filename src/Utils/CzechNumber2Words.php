<?php

namespace Webnazakazku\Utils;

class CzechNumber2Words
{
	
	/**
	* Original EN implementation: http://www.karlrixon.co.uk/writing/convert-numbers-to-words-with-php/
	* Original SK implementation: http://www.synet.sk/php/sk/330-cislo-na-slovo
	* @param int $number
	* @param int $units
	* @param int $level
	*/
	public static function number2Words($number, $units = null, $level = -1)
	{
		++$level;
		$hyphen      = ''; // in english "-", v češtině žádný
		$conjunction = ''; // in english ' and ' v češtině nepoužíváme
		$separator   = '';
		$dictionary  = array(
			0					=> 'nula',
			1					=> 'jeden', // jeden milion, jedna miliarda
			2					=> 'dva', // dvojtvar dve, dve - napr. 22000 - dvadsatDVA tisic, 200 = DVE sto
			3					=> 'tři',
			4					=> 'čtyři',
			5					=> 'pět',
			6					=> 'šest',
			7					=> 'sedm',
			8					=> 'osm',
			9					=> 'devět',
			10					=> 'deset',
			11					=> 'jedenáct',
			12					=> 'dvanáct',
			13					=> 'třináct',
			14					=> 'čtrnáct',
			15					=> 'patnáct',
			16					=> 'šestnáct',
			17					=> 'sedmnáct',
			18					=> 'osmnáct',
			19					=> 'devatenáct',
			20					=> 'dvacet',
			30					=> 'třicet',
			40					=> 'čtyřicet',
			50					=> 'padesát',
			60					=> 'šedesát',
			70					=> 'sedmdesát',
			80					=> 'osmdesát',
			90					=> 'devadesát',
			100					=> 'sto',
			1000				=> 'tisíc',
		);

		if (!is_numeric($number)) {
			return false;
		}
		
		if ($number <= 0 || $number > 999999) {
			// overflow
			throw new \Nette\Application\ApplicationException('Invalid number range - value must be between 0 and 999 999');
		}

		switch (true) {
			case $number < 21:
				$dict = $dictionary[$number];
				if($units){
					if($number == 1){
						// ludia chcu "jednosto"
						$dict = ''; // nie jedentisic, jedensto
						if($level <= 1){ // first loop = 0, pridame "jedno"sto na zaciatku slova
							if($units == 100){
								$dict = 'jedno'; // jednosto
							}elseif(in_array($units, [1e3, 1e6])){
								$dict = 'jeden'; // jedentisíc
							}elseif(in_array($units, [1e9, 1e15])){
								$dict = 'jedna'; // jedna miliarda
							}
						}
					}
				}
				$string = $dict;
				break;
			case $number < 100:
				$tens   = ((int) ($number / 10)) * 10;
				$units  = $number % 10;
				$string = $dictionary[$tens];
				if ($units) {
					$string .= $hyphen . $dictionary[$units];
				}
				break;
			case $number < 1000:
				$hundreds  = floor($number / 100);
				$remainder = $number % 100;
				if($hundreds == 1){
					// ludi chtějí "jednosto"
					$dict = ''; // ne čtyřitisic jednostoosmdesat, jedenstopatnáct
					if(!$level){ // jednosto na začátku slova
						$dict = $dictionary[$hundreds]; // nie čtyřitisic jedenstoomdesát, jednostopatnást
						if($number < 200){
							$dict = 'jedno'; // jednostodvanact, ne jednosto
						}
					}
				}elseif($hundreds == 2){
					$dict = 'dvě'; // dvěsta, ne dvěsto
				}else{
					$dict = $dictionary[$hundreds];
				}
				if($hundreds > 1 && $hundreds < 5) {
					$string = $dict . "sta";
				} elseif($hundreds > 4) {
					$string = $dict . "set";
				} else {
					$string = $dict . $dictionary['100'];
				}
				if ($remainder) {
					$string .= self::number2words($remainder, null, $level);
				}
				break;
			default:
				$baseUnit = pow(1000, floor(log($number, 1000)));
				$numBaseUnits = (int) ($number / $baseUnit);
				$remainder = $number % $baseUnit;
				// CZ declination
				$append = $dictionary[$baseUnit];
				if($baseUnit > 1000){
					$bigNumSep = ' ';
					// neskloňujeme tisice, jen milion a vyssí
					if(in_array($baseUnit, [1e9, 1e15])){
						$append = explode('|', $append);
						if($numBaseUnits >= 2 && $numBaseUnits <= 4){
							$append = $append[1]; // 2, 3, 4 miliardy, biliardy
						}elseif($numBaseUnits >= 5 ){
							$append = $append[2]; // 5,6 ... 99 miliard, biliárd
						}else{
							$append = $append[0]; // 1 miliarda, 1 biliarda
						}
					}else{
						if($numBaseUnits >= 2 && $numBaseUnits <= 4){
							$append .= 'y'; // 2, 3, 4 miliony, biliony, triliony, ..
						}elseif($numBaseUnits >= 5 ){
							$append .= 'ov'; // 5,6 ... 99 milionů
						}
					}
				}else{
					$bigNumSep = '';
				}
				$string = self::number2words($numBaseUnits, $baseUnit, $level) . $bigNumSep . $append;
				if ($remainder) {
					$string .= $remainder < 100 ? $conjunction : $separator;
					$string .= self::number2words($remainder, null, $level);
				}
				break;
		}
		
		return $string;
    }
}
