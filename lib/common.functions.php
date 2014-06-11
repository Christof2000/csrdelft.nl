<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# common.functions.php
# -------------------------------------------------------------------

/**
 * Is the current request posted?
 * @return boolean
 */
function isPosted() {
	return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * @source http://stackoverflow.com/questions/834303/php-startswith-and-endswith-functions
 * @param string $haystack
 * @param string $needle
 * @return boolean
 */
function startsWith($haystack, $needle) {
	return $needle === "" || strpos($haystack, $needle) === 0;
}

/**
 * @source http://stackoverflow.com/questions/834303/php-startswith-and-endswith-functions
 * @param string $haystack
 * @param string $needle
 * @return boolean
 */
function endsWith($haystack, $needle) {
	return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

/**
 * Group by object property
 * 
 * @param string $prop
 * @param array $in
 * @return array $out
 */
function group_by($prop, $in) {
	$out = array();
	foreach ($in as $i => $obj) {
		$out[$obj->$prop][] = $obj; // add to array
		if (is_array($in)) {
			unset($in[$i]);
		}
	}
	return $out;
}

/**
 * Group by distinct object property
 * 
 * @param string $prop
 * @param array $in
 * @return array $out
 */
function group_by_distinct($prop, $in) {
	$out = array();
	foreach ($in as $i => $obj) {
		$out[$obj->$prop] = $obj; // overwrite existing
		if (is_array($in)) {
			unset($in[$i]);
		}
	}
	return $out;
}

/**
 * @source http://nl.php.net/manual/en/function.in_array.php
 */
function array_values_in_array($needles, $haystack) {
	if (is_array($needles)) {
		$found = true;
		foreach ($needles as $needle) {
			if (!in_array($needle, $haystack)) {
				$found = false;
			}
		}
		return $found;
	} else {
		return in_array($needles, $haystack);
	}
}

/**
 * Geeft een array terug met alleen de opgegeven keys.
 *
 * @param	$in		ééndimensionele array.
 * @param	$keys	Keys die uit de in-array gereturned moeten worden.
 * @return			Array met alleen keys die in $keys zitten
 *
 * @author			Jan Pieter Waagmeester (jieter@jpwaag.com)
 */
function array_get_keys(array $in, array $keys) {
	$out = array();
	foreach ($keys as $key) {
		if (isset($in[$key])) {
			$out[$key] = $in[$key];
		}
	}
	return $out;
}

/**
 * Invokes a client page (re)load the url.
 * 
 * @param string $url
 * @param string $melding
 * @param int $level
 */
function invokeRefresh($url = null, $melding = '', $level = -1) {
	// als $melding een array is die uit elkaar halen
	if (is_array($melding)) {
		list($melding, $level) = $melding;
	}
	if ($melding != '') {
		setMelding($melding, $level);
	}
	if ($url == null) {
		$url = CSR_ROOT . Instellingen::get('stek', 'request');
	}
	header('location: ' . $url);
	exit;
}

/**
 * rawurlencode() met uitzondering van slashes.
 * 
 * @param string $url
 * @return string
 */
function direncode($url) {
	return str_replace('%2F', '/', rawurlencode($url));
}

function is_utf8($string) {
	return checkEncoding($string, 'UTF-8');
}

function checkEncoding($string, $string_encoding) {
	$fs = $string_encoding == 'UTF-8' ? 'UTF-32' : $string_encoding;
	$ts = $string_encoding == 'UTF-32' ? 'UTF-8' : $string_encoding;
	return $string === mb_convert_encoding(mb_convert_encoding($string, $fs, $ts), $ts, $fs);
}

/**
 * User Contributed Notes
 * @source http://nl.php.net/manual/en/function.ip2long.php
 * @param string $addr IP address
 * @param array $cidr CIDR
 * @return boolean
 */
function matchCIDR($addr, array $cidr) {
	list($ip, $mask) = explode('/', $cidr);
	$bitmask = ($mask != 0) ? 0xffffffff >> (32 - $mask) : 0x00000000;
	return ((ip2long($addr) & $bitmask) == (ip2long($ip) & $bitmask));
}

function makepasswd($pass) {
	$salt = mhash_keygen_s2k(MHASH_SHA1, $pass, substr(pack('h*', md5(mt_rand())), 0, 8), 4);
	return "{SSHA}" . base64_encode(mhash(MHASH_SHA1, $pass . $salt) . $salt);
}

function valid_filename($name) {
	return preg_match('/^(?:[a-z0-9 \-_é]|\.(?!\.))+$/iD', $name);
}

function email_like($email) {
	if ($email == '') {
		return false;
	}
	return preg_match("/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i", $email);
}

function url_like($url) {
	if ($url == '') {
		return false;
	}
	#					  http://		  user:pass@
	return preg_match('#^(([a-zA-z]{1,6}\://)(\w+:\w+@)?' .
			#	f			oo.bar.   org	   :80
			'([a-zA-Z0-9]([-\w]+\.)+(\w{2,5}))(:\d{1,5})?)?' .
			#	/path	   ?file=http://foo:bar@w00t.l33t.h4x0rz/
			'(/~)?[-\w./]*([-@()\#?/&;:+,._\w= ]+)?$#', $url);
}

/**
 * Gebruik over de hele site dezelfde htmlentities parameters.
 * @param string $string
 * @return string
 */
function mb_htmlentities($string) {
	return htmlentities($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Komt de request vanaf Confide?
 * @return boolean
 */
function opConfide() {
	return ( isset($_SERVER['REMOTE_ADDR']) and defined('CONFIDE_IP') and in_array($_SERVER['REMOTE_ADDR'], explode(':', CONFIDE_IP)) );
}

/**
 * Komt de request van Feut (irc-bot)?
 * @return boolean
 */
function isFeut() {
	return isset($_SERVER['REMOTE_ADDR']) and defined('FEUT_IP') and $_SERVER['REMOTE_ADDR'] == FEUT_IP;
}

/**
 * Is de huidige server syrinx?
 * @return boolean
 */
function isSyrinx() {
	return $_SERVER['SERVER_NAME'] === 'csrdelft.nl';
}

/**
 * @param int $timestamp optional
 * @return string current DateTime formatted Y-m-d H:i:s
 */
function getDateTime($timestamp = null) {
	if ($timestamp === null) {
		$timestamp = time();
	}
	return date('Y-m-d H:i:s', $timestamp);
}

/**
 * @param int $timestamp
 * @return string aangepast ISO-8601 weeknummer met zondag als eerste dag van de week
 */
function getWeekNumber($timestamp) {
	if (date('w', $timestamp) == 0) {
		return date('W', strtotime('+1 day', $timestamp));
	} else {
		return date('W', $timestamp);
	}
}

/**
 * @param string $datum moet beginnen met 'yyyy-mm-dd' (wat daarna komt maakt niet uit)
 * @return boolean true als $datum geldig is volgens checkdate(); false otherwise
 */
function isGeldigeDatum($datum) {
	// De string opdelen en checken of er genoeg delen zijn.
	$delen = explode('-', $datum);
	if (count($delen) < 3) {
		return false;
	}
	// Checken of we geldige strings hebben, voordat we ze casten naar ints.
	$jaar = $delen[0];
	if (!is_numeric($jaar) OR strlen($jaar) != 4) {
		return false;
	}
	$maand = $delen[1];
	if (!is_numeric($maand) OR strlen($maand) != 2) {
		return false;
	}
	$dag = substr($delen[2], 0, 2); // Alleen de eerste twee karakters pakken.
	if (!is_numeric($dag) OR strlen($dag) != 2) {
		return false;
	}
	// De strings casten naar ints en de datum laten checken.
	return checkdate((int) $maand, (int) $dag, (int) $jaar);
}

/**
 * print_r een variabele met <pre>-tags eromheen als:
 *  - het ip van de gebruiker een admin-ip is
 * of
 *  - de gebruiker het recht P_ADMIN heeft.
 * @param string $sString
 * @param string $cssID
 */
function debugprint($sString, $cssID = 'pubcie_debug') {
	$adminIPs = array('145.94.61.229', '145.94.59.158', '192.168.16.101', '127.0.0.1');
	$isFromAdminIP = isset($_SERVER['REMOTE_ADDR']) AND in_array($_SERVER['REMOTE_ADDR'], $adminIPs);
	if ($isFromAdminIP OR LoginLid::mag('P_ADMIN')) {
		ob_start();
		echo '<pre class="' . $cssID . '">' . print_r($sString, true) . '</pre>';
	}
}

/**
 * Stores a message.
 *
 * Levels can be:
 *
 * -1 error
 *  0 info
 *  1 success
 *  2 notify
 *
 * @see    SimpleHTML::getMelding()
 * gebaseerd op DokuWiki code
 */
function setMelding($message, $lvl = -1) {
	$errors[-1] = 'error';
	$errors[0] = 'info';
	$errors[1] = 'success';
	$errors[2] = 'notify';

	$message = trim($message);
	if ($message != '') {
		if (!isset($_SESSION['melding']))
			$_SESSION['melding'] = array();
		//gooit verouderde gegevens weg FIXME tijdelijk tot dat iedereen een nieuwe sessie heeft.
		if (is_string($_SESSION['melding']))
			$_SESSION['melding'] = array();

		$_SESSION['melding'][] = array('lvl' => $errors[$lvl], 'msg' => $message);
	}
}

/**
 * Probeert uit invoer van uids of namen per zoekterm een unieke uid te bepalen, zoniet een lijstje suggesties en anders false.
 *
 * @param 	string $sNamen string met namen en/of uids op nieuwe regels en/of gescheiden door komma's
 * @param   array|string $filter zoekfilter voor Zoeker::zoekLeden, toegestane input: '', 'leden', 'oudleden' of array met stati
 * @return 	bool false bij geen matches
 * 			of een array met per zoekterm een entry met een unieke uid en naam òf een array met naamopties.
 * Voorbeeld:
 * Input: $sNamen = 'Lid, Klaassen'
 * Output: Array(
  [0] => Array (
  [naamOpties] => Array (
  [0] => Array (
  [uid] => 4444
  [naam] => Oud Lid
  )
  [1] => Array (
  [uid] => x101
  [naam] => Jan Lid
  )
  [2] => Array (
  ...
  )
  )
  [1] => Array (
  [uid] => 0431
  [naam] => Jan Klaassen
  )
  )
 */
function namen2uid($sNamen, $filter = 'leden') {
	$return = array();
	$sNamen = trim($sNamen);
	$sNamen = str_replace(array(', ', "\r\n", "\n"), ',', $sNamen);
	$aNamen = explode(',', $sNamen);
	$return = false;
	foreach ($aNamen as $sNaam) {
		$aNaamOpties = array();
		$aZoekNamen = Zoeker::zoekLeden($sNaam, 'naam', 'alle', 'achternaam', $filter);
		if (count($aZoekNamen) == 1) {
			$naam = $aZoekNamen[0]['voornaam'] . ' ';
			if (trim($aZoekNamen[0]['tussenvoegsel']) != '') {
				$naam.=$aZoekNamen[0]['tussenvoegsel'] . ' ';
			}
			$naam.=$aZoekNamen[0]['achternaam'];
			$return[] = array('uid' => $aZoekNamen[0]['uid'], 'naam' => $naam);
		} elseif (count($aZoekNamen) == 0) {
			
		} else {
			//geen enkelvoudige match, dan een array teruggeven
			foreach ($aZoekNamen as $aZoekNaam) {
				$lid = LidCache::getLid($aZoekNaam['uid']);
				$aNaamOpties[] = array(
					'uid' => $aZoekNaam['uid'],
					'naam' => $lid->getNaam());
			}
			$return[]['naamOpties'] = $aNaamOpties;
		}
	}
	return $return;
}

/**
 * Gebruik om alléén post of alléén get te checken.
 * @param string $key
 * @param string $type null/post/get
 */
function getOrPost($key, $type = null, $default = '') {
	if ($type != 'get' && isset($_POST[$key])) {
		return $_POST[$key];
	} elseif ($type != 'post' && isset($_GET[$key])) {
		return $_GET[$key];
	} else {
		return $default;
	}
}

/**
 * Sorteer op achternaam ASC, uid DESC.
 * @param string $a
 * @param string $b
 * @return int
 */
function sort_achternaam_uid($a, $b) {
	$vals = array('achternaam' => 'ASC', 'uid' => 'DESC');
	while (list($key, $val) = each($vals)) {
		if ($val == 'DESC') {
			if ($a[$key] > $b[$key]) {
				return -1;
			}
			if ($a[$key] < $b[$key]) {
				return 1;
			}
		}
		if ($val == 'ASC') {
			if ($a[$key] < $b[$key]) {
				return -1;
			}
			if ($a[$key] > $b[$key]) {
				return 1;
			}
		}
	}
}

function strNthPos($haystack, $needle, $nth = 1) {
	//Fixes a null return if the position is at the beginning of input
	//It also changes all input to that of a string ^.~
	$haystack = ' ' . $haystack;
	if (!strpos($haystack, $needle)) {
		return false;
	}
	$offset = 0;
	for ($i = 1; $i < $nth; $i++) {
		$offset = strpos($haystack, $needle, $offset) + 1;
	}
	return strpos($haystack, $needle, $offset) - 1;
}

function reldate($datum) {
	$nu = time();
	$moment = strtotime($datum);
	$verschil = $nu - $moment;
	if ($verschil <= 60) {
		$return = $verschil . ' ';
		if ($verschil == 1) {
			$return .= 'seconde';
		} else {
			$return .= 'seconden';
		}
		$return .= ' geleden';
	} elseif ($verschil <= 60 * 60) {
		$return = floor($verschil / 60);
		if (floor($verschil / 60) == 1) {
			$return .= ' minuut';
		} else {
			$return .= ' minuten';
		}
		$return .= ' geleden';
	} elseif ($verschil <= (60 * 60 * 4)) {
		$return = floor($verschil / (60 * 60)) . ' uur geleden';
	} elseif (date('Y-m-d') == date('Y-m-d', $moment)) {
		$return = 'vandaag om ' . date("G:i", $moment);
	} elseif (date('Y-m-d', $moment) == date('Y-m-d', strtotime('1 day ago'))) {
		$return = 'gisteren om ' . date("G:i", $moment);
	} else {
		$return = date("G:i j-n-Y", $moment);
	}
	return '<abbr class="timeago" title="' . date('Y-m-d\TG:i:sO', $moment) . '">' . $return . '</abbr>'; // ISO8601
}

function internationalizePhonenumber($phonenumber, $prefix = '+31') {
	$number = str_replace(array(' ', '-'), '', $phonenumber);
	if ($number[0] == 0) {
		return $prefix . substr($number, 1);
	} else {
		return $phonenumber;
	}
}

/**
 * Plaatje vierkant croppen.
 * @source http://abeautifulsite.net/blog/2009/08/cropping-an-image-to-make-square-thumbnails-in-php/
 */
function square_crop($src_image, $dest_image, $thumb_size = 64, $jpg_quality = 90) {

	// Get dimensions of existing image
	$image = getimagesize($src_image);

	// Check for valid dimensions
	if ($image[0] <= 0 || $image[1] <= 0)
		return false;

	// Determine format from MIME-Type
	$image['format'] = strtolower(preg_replace('/^.*?\//', '', $image['mime']));

	// Import image
	switch ($image['format']) {
		case 'jpg':
		case 'jpeg':
			$image_data = imagecreatefromjpeg($src_image);
			break;
		case 'png':
			$image_data = imagecreatefrompng($src_image);
			break;
		case 'gif':
			$image_data = imagecreatefromgif($src_image);
			break;
		default:
			// Unsupported format
			return false;
	}

	// Verify import
	if ($image_data == false) {
		return false;
	}

	// Calculate measurements
	if ($image[0] > $image[1]) {
		// For landscape images
		$x_offset = ($image[0] - $image[1]) / 2;
		$y_offset = 0;
		$square_size = $image[0] - ($x_offset * 2);
	} else {
		// For portrait and square images
		$x_offset = 0;
		$y_offset = ($image[1] - $image[0]) / 2;
		$square_size = $image[1] - ($y_offset * 2);
	}

	// Resize and crop
	$canvas = imagecreatetruecolor($thumb_size, $thumb_size);
	if (imagecopyresampled($canvas, $image_data, 0, 0, $x_offset, $y_offset, $thumb_size, $thumb_size, $square_size, $square_size)) {

		// Create thumbnail
		switch (strtolower(preg_replace('/^.*\./', '', $dest_image))) {
			case 'jpg':
			case 'jpeg':
				$return = imagejpeg($canvas, $dest_image, $jpg_quality);
				break;
			case 'png':
				$return = imagepng($canvas, $dest_image);
				break;
			case 'gif':
				$return = imagegif($canvas, $dest_image);
				break;
			default:
				// Unsupported format
				$return = false;
				break;
		}

		//plaatje ook voor de webserver leesbaar maken.
		if ($return) {
			chmod($dest_image, 0644);
		}
		return $return;
	} else {
		return false;
	}
}

/**
 * @param string $string
 * @return string input without unprintable characters (excluding newlines and tabs)
 */
function only_printable($string) {
	return $string;
	//er gaat nog wat mis, want ik postte net iets met á erin, en die
	//snoepte dit ding ook weg. Niet de bedoeling natuurlijk, dus ff hdb ermee...
	//FIXME return preg_replace('/[^\x0A^\x09\x20-\x7E]/', '', $string);
}

function format_filesize($size) {
	$units = array(' B', ' KB', ' MB', ' GB', ' TB');
	for ($i = 0; $size >= 1024 && $i < 4; $i++) {
		$size /= 1024;
	}
	return round($size, 2) . $units[$i];
}
