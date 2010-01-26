<?php
	define ("GB2312_UTF8", 1);
	define ("GB2312_UNICODE", 2);
	define ("GB2312_BIG5", 3);
	define ("GBK_UTF8", 4);
	define ("GBK_UNICODE", 5);
	define ("GBK_BIG5", 6);
	define ("BIG5_UTF8", 7);
	define ("BIG5_UNICODE", 8);
	define ("BIG5_GB2312", 9);
	define ("BIG5_GBK", 10);
	define ("UTF8_GB2312", 11);
	define ("UTF8_GBK", 12);
	define ("UTF8_BIG5", 13);
	define ("UNICODE_GB2312", 14);
	define ("UNICODE_GBK", 15);
	define ("UNICODE_BIG5", 16);
	define ("UTF8_UNICODE", 17);
	define ("UNICODE_UTF8", 18);

	class DouConv {
		var $table;
		var $form;
		var $path;

		function DouConv($form) {
			$this->path = dirname(__FILE__)."/douconv/";
			$this->form = $form;
			$this->table = $this->getTable($form);
		}

		function conv($str) {
			return $this->strConv($str, $this->form);
		}

		function convFile($filename) {
			if (function_exists("file_get_contnets")) {
				$content = file_get_contents($filename);
			} else {
				$fp = fopen($filename, "r");
				$content = fread($fp, filesize($filename));
				fclose($fp);
			}
			return $this->strConv($content, $this->form);
		}

		function convURL($url) {
			if(function_exists("file_get_contents")) {
				$content = file_get_contents($url);
			} else {
				$conent = "";
				$fp = fopen($url, "r");
				do {
					$data = fread($fp, 8192);
					if (strlen($data) == 0) {
						break;
					}
					$content .= $data;
				} while(true);
			}
			return $this->strConv($content, $this->form);
		}

		function getTable($form) {
			$unicode = false;
			switch ($form) {
				case GB2312_UTF8:
					$file_in="GB2312.table";
					$swap=false;
					break;
				case UTF8_GB2312:
					$file_in="GB2312.table";
					$swap=true;
					break;
				case GB2312_UNICODE:
					$file_in="GB2312.table1";
					$swap=false;
					$unicode=true;
					break;
				case UNICODE_GB2312:
					$file_in="GB2312.table1";
					$swap=true;
					$unicode=true;
					break;
				case GBK_UTF8:
					$file_in="GBK.table";
					$swap=false;
					break;
				case UTF8_GBK:
					$file_in="GBK.table";
					$swap=true;
					break;
				case GBK_UNICODE:
					$file_in="GBK.table1";
					$swap=false;
					$unicode=true;
					break;
				case UNICODE_GBK:
					$file_in="GBK.table1";
					$swap=true;
					$unicode=true;
					break;
				case BIG5_UTF8:
					$file_in="BIG5.table";
					$swap=false;
					break;
				case UTF8_BIG5:
					$file_in="BIG5.table";
					$swap=true;
					break;
				case BIG5_UNICODE:
					$file_in="BIG5.table1";
					$swap=false;
					$unicode=true;
					break;
				case UNICODE_BIG5:
					$file_in="BIG5.table1";
					$swap=true;
					$unicode=true;
					break;
				case UTF8_UNICODE:
				case UNICODE_UTF8:
					return 0;
					break;
				default:
					exit();
			}

			$arr_in=file($this->path.$file_in);
			foreach($arr_in as $value) {
				list($incode_char, $outcode_char)=explode("|", $value);
				$outcode_char = substr($outcode_char, 0, -1);
				if($unicode) $outcode_char="&#".hexdec($outcode_char).";";
				if (!$swap)
					$arr_out[$incode_char]=$outcode_char;
				else
					$arr_out[$outcode_char]=$incode_char;
			}
			for($i=1; $i<= 0x7F; $i++) $arr_out[chr($i)]=chr($i);
			return $arr_out;
		}

		function strConv($str, $form) {
			switch ($form) {
				case GB2312_UTF8:
				case GB2312_UNICODE:
					$pattern="/[\x01-\x7F]|[\xA1-\xFE][\xA1-\xFE]/";
					break;

				case GBK_UTF8:
				case GBK_UNICODE:
					$pattern="/[\x01-\x7F]|[\x81-\xFE][\x40-\xFE]/";
					break;

				case BIG5_UTF8:
				case BIG5_UNICODE:
					$pattern="/[\x01-\x7F]|[\xA1-\xF9][\x40-\x7E]|[\xA1-\xF9][\xA1-\xFE]/";
					break;

				case UTF8_GB2312:
				case UTF8_GBK:
				case UTF8_BIG5:
					$pattern="/([\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf])/";
					break;

				case UNICODE_GB2312:
				case UNICODE_GBK:
				case UNICODE_BIG5:
					$pattern="/&#[0-9]*;/";
					break;

				case UTF8_UNICODE:
					$pattern="/([\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf])/";
					preg_match_all($pattern, $str, $ar);

					$count = count($ar[0]);
					for ($i=0; $i<$count; $i++) {
						switch(strlen($ar[0][$i])) {
							case 1:
								break;
							case 2:
								$b0 = ord($ar[0][$i]{0}) & 0x1F;
								$b1 = ord($ar[0][$i]{1}) & 0x3F;
								$bb0 = substr("000000".decbin($b0), -5);
								$bb1 = substr("000000".decbin($b1), -6); 
								$ar[0][$i] = "&#".bindec($bb0.$bb1).";";
								break;
							case 3:
								$b0 = ord($ar[0][$i]{0}) & 0xF;
								$b1 = ord($ar[0][$i]{1}) & 0x3F;
								$b2 = ord($ar[0][$i]{2}) & 0x3F;
								$bb0 = substr("000000".decbin($b0), -4);
								$bb1 = substr("000000".decbin($b1), -6); 
								$bb2 = substr("000000".decbin($b2), -6);
								$ar[0][$i] = "&#".bindec($bb0.$bb1.$bb2).";";
								break;
						}
					}
					return join("", $ar[0]);
					break;

				case UNICODE_UTF8:
					$pattern="/&#[0-9]*;/";
					preg_match($pattern, $str, $ar, PREG_OFFSET_CAPTURE);
					break;

				default:
					exit();
			}
		
			preg_match_all($pattern, $str, $ar);
			//array_walk($ar[0], 'conv');

			//print_r($ar[0]);
			//echo $this->table[$ar[0][0]];
			//print_r($this->table);

			$count = count($ar[0]);
			for ($i=0; $i<$count; $i++) {
				$ar[0][$i] = $this->table[$ar[0][$i]];
			}
			
			return join("", $ar[0]);
		}
	}
/*
	$conv1 = new DouConv(UTF8_BIG5);
	echo $conv1->convFile("utf8.txt");
*/

if (!function_exists("iconv")) {
    $douconv_cache = array();

    function clean_charset($charset) {
        $charset = strtolower($charset);

        $charset = str_replace("//translit", "", $charset);
        $charset = str_replace("//ignore", "", $charset);

        // alias
        if ($charset == "utf-8") $charset="utf8";
        
        return $charset;
    }

    function iconv($in_charset, $out_charset, $str) {
        $in_charset = clean_charset($in_charset);
        $out_charset = clean_charset($out_charset);

        $form = 0;
        if ($in_charset == "gb2312" && $out_charset == "utf8") 
            $form = GB2312_UTF8;
        elseif ($in_charset == "gb2312" && $out_charset == "unicode")
            $form = GB2312_UNICODE;
        elseif ($in_charset == "gb2312" && $out_charset == "big5")
            $form = GB2312_BIG5;
        elseif ($in_charset == "gbk" && $out_charset == "utf8")
            $form = GBK_UTF8;
        elseif ($in_charset == "gbk" && $out_charset == "unicode")
            $form = GBK_UNICODE;
        elseif ($in_charset == "gbk" && $out_charset == "big5")
            $form = GBK_BIG5;
        elseif ($in_charset == "big5" && $out_charset == "utf8")
            $form = BIG5_UTF8;
        elseif ($in_charset == "big5" && $out_charset == "unicode")
            $form = BIG5_UNICODE;
        elseif ($in_charset == "big5" && $out_charset == "gbk")
            $form = BIG5_GBK;
        elseif ($in_charset == "utf8" && $out_charset == "gb2312")
            $form = UTF8_GB2312;
        elseif ($in_charset == "utf8" && $out_charset == "big5")
            $form = UTF8_BIG5;
        elseif ($in_charset == "unicode" && $out_charset == "gb2312")
            $form = UNICODE_GB2312;
        elseif ($in_charset == "unicode" && $out_charset == "gbk")
            $form = UNICODE_GBK;
        elseif ($in_charset == "unicode" && $out_charset == "big5")
            $form = UNICODE_BIG5;
        elseif ($in_charset == "utf8" && $out_charset == "unicode")
            $form = UTF8_UNICODE;
        elseif ($in_charset == "unicode" && $out_charset == "utf8")
            $form = UNICODE_UTF8;
        else
            $form = 0;
    
        if ($form != 0) {
            global $douconv_cache;
            
            if (!array_key_exists($form, $douconv_cache)) {
                $douconv_cache[$form] = new DouConv($form);
            }

            $conv = $douconv_cache[$form]; 
            return $conv->conv($str);
        } else {
            return "";
        }
    }
}

?>
