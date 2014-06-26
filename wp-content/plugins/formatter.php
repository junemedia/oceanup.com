<?php (defined('PHP_SAPI') && PHP_SAPI == 'cli' ? null : die(header('Location: /')));

function usage($extra='') {
	echo $extra.PHP_EOL;
	echo 'php '.basename(__FILE__).' <filename>'.PHP_EOL;
	echo '  <filename> = path to file to reformat'.PHP_EOL;
	echo PHP_EOL;
	exit;
}

if ($argc < 2) usage('missing filename to format');

$filename = $argv[1];
if (!file_exists($filename)) usage('file ['.$filename.'] does not exist');
if (!is_readable($filename)) usage('cannot read file ['.$filename.']. probably permissions');

$raw = file_get_contents($filename);
echo 'found ['.strlen($raw).'] characters. parsing now'.PHP_EOL;
$content = '';

$digits = '0123456789';
$scops = '+-*/%^~.=&|@<>!';
$mcops = array('and', 'or', 'xor', '&&', '||', '>=', '=>', '<=', '.=', '+=', '-=', '>>', '<<', '==', '===', '!=', '!==');
$nlafter = '{}>;';
$only_before_var = array('->', '::', ' &');

$tabs = 0;
$tb = '  ';
$t = '';
$type = 'h';
$inq = '';
$inq_back = '';
$inc = '';
$ina = 0;
$inr = 0;
$inp = array();
$int = 0;
$qc = 0;

function t($cnt, $tb) { return $cnt > 0 ? str_repeat($tb, $cnt) : ''; }

while (strlen($raw) && ($char = substr($raw, 0, 1)) !== '' && $char !== false && ($raw = substr($raw, 1))) {
	//var_dump('+'.$char);
	$prev1 = substr($content, -1);
	$prevprev1 = substr($content, -2, 1);

	if (in_array($char, array('\'', '"'))) {
		if (empty($inq)) {
			$inq = $char;
			$char = trim($prev1) == '' || $type == 'h' ? $char : ' '.$char;
			$inq_back = substr($content, -6);
		} else if ($inq == $char && ($prev1 != '\\' || ($prev1 == '\\' && $prevprev1 == '\\'))) {
			$inq = $inq_back = '';
			$qc = 0;
			$char = $type == 'h' ? $char : $char.' ';
		}
		$content .= $char;
		continue;
	} else if ($inq) {
		$qc++;
		$content .= $char;
		continue;
	}

	if (trim($char) == '') {
		if (trim($prev1) == '') continue;
		else {
			$content .= ' ';
			continue;
		}
	}

	if ($char == '?' && $prev1 == '<') {
		if (substr($raw, 0, 3) == 'php') $raw = substr($raw, 3);
		$type = 'p';
		if (strlen($content) - 1 > 0) {
			$tabs++;
			$pt = $t;
			$t = t($tabs, $tb);
			$content = rtrim(substr($content, 0, strlen($content)-1)).PHP_EOL.$pt.'<?php'.PHP_EOL.$t;
		} else {
			$content = '<?php'.PHP_EOL;
		}
		continue;
	}

	if ($type == 'p') { // in php
		if ($inc) {
			if ($inc == '*' && $char == '/' && $prev1 == ' ' && $prevprev1 == '*') {
				$inc = '';
				$content = rtrim($content).'/';
				continue;
			}
		} else if ($prev1 == ' ' && $prevprev1 == '/') {
			if ($char == '/') {
				$content = rtrim(substr($content, 0, strlen($content)-2)).PHP_EOL.$t.'//'.substr($raw, 0, strpos($raw, PHP_EOL)).PHP_EOL.$t;
				$raw = substr($raw, strpos($raw, PHP_EOL)+strlen(PHP_EOL));
				continue;
			} else if ($char == '*') {
				$inc = '*';
				$content = rtrim($content).$char;
				continue;
			}
		}

		if ($char == '>' && $prev1 == '?') {
			$type = 'h';
			$tabs--;
			$tabs = $tabs < 0 ? 0 : $tabs;
			$t = t($tabs, $tb);
			$content = rtrim(substr($content, 0, strlen($content)-1)).PHP_EOL.$t.'?'.'>'.PHP_EOL.$t;
			continue;
		}

		if ($char == '{') {
			$inr++;
			$tabs++;
			$t = t($tabs, $tb);
			$content = rtrim($content).' {'.PHP_EOL.$t;
			continue;
		} else if ($char == '}') {
			$inr--;
			$tabs--;
			$t = t($tabs, $tb);
			$content = rtrim($content).PHP_EOL.$t.$char.PHP_EOL.PHP_EOL.$t;
			continue;
		}

		if ($char == '(') {
			if (substr($content, -5) == 'array' && substr($content, -6, 1) != '_') {
				$inp[] = 'a';
				$ina++;
				$tabs++;
				$t = t($tabs, $tb);
				$content = rtrim($content).$char.PHP_EOL.$t;
				continue;
			} else {
				$inp[] = 'f';
			}
			$content = rtrim($content);
			if (strtolower(substr($content, -2)) == 'if' && trim(substr($content, -3, 1)) == '') $content .= ' '.$char.' ';
			else $content .= $char.' ';
			continue;
		}

		if ($char == ')') {
			$pty = array_pop($inp);
			if ($ina && $pty == 'a') {
				if (substr($content, -(6 + strlen(PHP_EOL.$t))) == 'array('.PHP_EOL.$t && substr($content, -(7 + strlen(PHP_EOL.$t)), 1) != '_')
					$content = substr($content, 0, strlen($content)-(6 + strlen(PHP_EOL.$t))).'array()';
				else
					$content .= PHP_EOL.$t.$char;
				$tabs--;
				$t = t($tabs, $tb);
				$ina--;
				continue;
			} else if ($pty == 'f') {
				$content .= ($prev1 != ' ' ? ' ' : '').$char;
				continue;
			}
		} else if (($ic = count($inp)) && $inp[$ic-1] == 'a' && substr($content, -7) == 'array( ') {
			$tabs++;
			$t = t($tabs, $tb);
			$content .= PHP_EOL.$t;
		}

		if ($char == ')') {
			if ($ina && $prev1 != '(') {
				$content .= PHP_EOL.$t.')';
			} else {
				$content .= ')';
			}
			continue;
		}

		if ($char == ':' && substr($content, -2) == ' :') {
			$content = substr($content, 0, strlen($content)-2).'::';
			continue;
		}

		if ($char == '$') {
			if (in_array(substr($content, -2), $only_before_var)) {
				$content .= $char;
			} else {
				$content .= ($prev1 != ' ' ? ' ' : '').$char;
			}
			continue;
		}

		if ($char == ',') {
			if (($ic = count($inp)) && $inp[$ic-1] == 'a') $content = rtrim($content).$char.PHP_EOL.$t;
			else $content = rtrim($content).$char.' ';
			continue;
		}

		if ($char == '.') {
			if (strpos($digits, substr($content, -1)) !== false) {
				$content .= '.';
				continue;
			}
		}

		if ($char == ';') {
			$content .= $char.PHP_EOL.$t;
			continue;
		}

		if (strtolower($char) == 'e' && ($l = strlen(PHP_EOL.PHP_EOL.$t.'els')) && substr($content, -$l) == PHP_EOL.PHP_EOL.$t.'els') {
			$content = substr($content, 0, strlen($content)-$l).' else ';
			continue;
		}

		foreach ($mcops as $mcop) {
			$opl = strlen($mcop);
			if (strtolower(substr($content, -$opl).$char) == ' '.$mcop) {
				$content = substr($content, 0, strlen($content)-$opl).' '.$mcop;
				continue 2;
			}
		}

		if (strpos($scops, $char) !== false) {
			if ($char == '@') {
				$content .= ($prev1 == ' ' ? '@' : ' @');
			} else {
				if (strpos($scops, substr($content, -2, 1)) === false) $content = rtrim($content).' '.$char.' ';
				else $content = rtrim($content).$char.' ';
			}
			continue;
		}

		//var_dump($char);
		$content .= $char;
	} else if ($type == 'h') { // in html
		$content .= $char;
	} else { // unknown
		$content .= '{unknown}';
	}
}

$content = preg_replace('#\?'.'>$#x', '', trim($content));

echo $content.PHP_EOL;
if ($inq) echo 'still in quotes: '.$qc.PHP_EOL.'began at: '.$inq_back.PHP_EOL;
if ($inr) echo 'in range: '.$inr.PHP_EOL;
if ($inp) echo 'in parenthesis: '.$inp.PHP_EOL;
if ($inc) echo 'in comment: '.$inc.PHP_EOL;
if ($ina) echo 'in array: '.$ina.PHP_EOL;
if ($int) echo 'in tag: '.$int.PHP_EOL;
echo 'current tab level: '.$tabs.PHP_EOL;
echo 'current type: '.$type.PHP_EOL;
