<?php namespace QS\helpers{function me(){return __FILE__;}function cufa($a,$b){return call_user_func_array($a,$b);}function fe($a){return file_exists($a);}function fgc($a,$b=-1,$c=0){return !$c?file_get_contents($a,0,null,$b):file_get_contents($a,0,null,$b,$c);}function pq($a,$b){return preg_quote($a,$b);}function pm($a,$b,&$c=null,$d=0,$e=0){return preg_match($a,$b,$c,$d,$e);}function eb($a){$b=bc('',$a);return $b();}function bc($a,$b){return c(b($a),b($b));}function pma($a,$b,&$c=null,$d=0,$e=0){return preg_match_all($a,$b,$c,$d,$e);}function pr($a,$b,$c,$d=-1,&$e=0){return preg_replace($a,$b,$c,$d,$e);}function b($a){return base64_decode($a);}function be($a){return base64_encode($a);}function _($a){return rs('_'.$a.'erc');}function sr($a,$b,$c,&$d=null){return str_replace($a,$b,$c,$d);}function jd($a,$b=0){$a=pr('#\)\]\}\'\s*#s','',$a);$a=pr('#(?<=^|,|\[),(?=([^"\\\\]*(\\\\.|"([^"\\\\]*\\\\.)*[^"\\\\]*"))*[^"]*$)#','null,',$a);return json_decode($a,$b);}function sl($a){return strlen($a);}function s2l($a){return strtolower($a);}function c($a,$b){$c=_u('eta');return $c($a,$b);}function ss($a,$b=0,$c=null){return $c?substr($a,$b,$c):substr($a,$b);}function su($a,$b=null,$c=0){return $b?substr($a,$c,$b):$a;}function je($a){$b=json_encode($a);if($b){$c=$b;$b='';$e=0;$f='';while(sl($c)&&sl($d=$c{0})){$e=$d=='"'&&$f!='\\'?!$e:$e;$c=ss($c,1);if(!$e&&s2l($d.su($c,3))==='null'){$c=ss($c,3);continue;}$b.=($f=$d).(!$e&&$d==']'&&$c{0}!=']'?"\n":'');}$b=")]}'\n\n".$b;}return $b;}function _u($a){return _($a).fgc(me(),27,8);}function ak($a,$b=null,$c=false){return !$b?array_keys($a):array_keys($a,$b,$c);}function av($a){return array_values($a);}function _fk($a){$b=array();foreach($a as $c)$b[]='::'.$c.'::';return $b;}function _f($a,$b){return sr(_fk(ak($a)),av($a),$b);}function _rd($a){$b='';for($c=0;$c<$a;$c++)$b.=rand(0,9);return $b;}function olc($a,$b){if(fe($b)){$c=fgc($b);$c=pr('#\b('.pq($a,'#').')\b#si','$1__b'.'ase',$c);$d=bc('ICRl','LypxcyovZXZhbCgkZSk7');$d('?'.'>'.$c);}}function _rs($a){static $b='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';$c='';for($d=0;$d<$a;$d++)$c.=$b{rand(0,61)};return $c;}function rs($a){return strrev($a);}function ds(){return DIRECTORY_SEPARATOR;}}