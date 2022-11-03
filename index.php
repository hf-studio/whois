<?php
require("./whoishub.php");
$domain = $_GET['q'];
$domain = preg_replace('/ /i', '', $domain);
$domain = preg_replace('/^http:\/\//i', '', $domain);
$domain = preg_replace('/^https:\/\//i', '', $domain);
$domain = strtolower(trim($domain));
$domain = explode('/', $domain);
$domain = trim($domain[0]);
if(substr_count($domain,".")==2) {
	$dotpos=strpos($domain,".");
	$domtld=strtolower(substr($domain,$dotpos+1));
	$whoisserver = $whoisservers[$domtld];
	if(!$whoisserver) {
		if(strpos($domain,"www")===false) {
		} else {
			$domain = preg_replace('/^www\./i', '', $domain);
		}
	}
}
function LookupDomain($domain) {
	global $whoisservers;
	$whoisserver = "";
	$dotpos=strpos($domain,".");
	$domtld=strtolower(substr($domain,$dotpos+1));
	$whoisserver = $whoisservers[$domtld];
	if(!$whoisserver) {
		return "<b>ERROR</b>: Whois server for <b>$domain</b> NOT FOUND!";
	}
	//if($whoisserver == "whois.verisign-grs.com") $domain = "=".$domain; // whois.verisign-grs.com requires the equals sign ("=") or it returns any result containing the searched string.
	$result = QueryWhoisServer($whoisserver, $domain);
	if(!$result) {
		return "Error: No results retrieved $domain !";
	}
	preg_match("/Whois Server: (.*)/", $result, $matches);
	$secondary = $matches[1];
	if($secondary) {
		$result = QueryWhoisServer($secondary, $domain);
	}
	return  $result;
}
function QueryWhoisServer($whoisserver, $domain) {
	$port = 43;
	$timeout = 10;
	$fp = @fsockopen($whoisserver, $port, $errno, $errstr, $timeout) or die("<pre>\nSocket Error " . $errno . " - " . $errstr);
	fputs($fp, $domain . "\r\n");
	$out = "";
	while(!feof($fp)) {
		$out .= fgets($fp);
	}
	fclose($fp);
	return $out;
}
?>

<html>
<head>
<title>Whois Search</title>
<link rel="shortcut icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACB0lEQVQ4T6WTPWhUQRDH/7P3ATYix3lv3vMKQWwkKhIbMRLTCYrYKEgUtRALsTWCRWIQRLBMlUYhhR8hUYyK2ChYCH4Ugoh2iu925x3XiJ08dmSPe8d5uUaccnfnN7Pz/w/hP4OG85MkqXvvZ1R1FxHtBrABwA8Ar4wxc9bazmDOX4A4jg+o6iMATwGslUqlN61WK2VmBfAOwDYiOuace11A+gBmPgvgNoATIrI8WIWZX4YOAHwG8ADAORG5E950Acy8FcAnIpp0zn0Y/hYzHwSwICJjzHy8V2hMRL4VgJuquinLsguDybVabWO1Wv0JwAJYFJFr4T6KoiUisiIyUwAeE9Gyc25pqPVQeVZEpgbPkySZ8N5fEZEjBaAFYH9oiZnvhSLGmBve+9+jAMy8GcBHEUm6gCiKUiLaA+AigJ0AAuQygL2jhlqv1+NyufxeRLYUgFVVvWWMOQ/gu4jMhTYBfAm6N5vNWp7nK92pE9313v8iopMicrQAXCeiqwBW8jy/1Ol03NAsgsSTqroK4DARHQJwf3CIXRmNMePW2q8jZHwG4G3orNFo7DPGPPHe72i329koI02JSDBNN5h5VlW3Z1l2Ko7jcVV9boyZtta+6BupeNyz8lrQvGfn4LwzRBRky4homohOO+cerrNycTBimaJwp6qLlUplPk3TIHk/1m3jv273H5JH5hEvO+ZNAAAAAElFTkSuQmCC">
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<meta name="keywords" content="Whois Search">
<meta name="description" content="Whois Search">  
<meta name="renderer" content="webkit">
<meta http-equiv="Cache-Control" content="no-siteapp"/>  
<link rel="stylesheet" type="text/css" href="./style.css">
<script src="./jquery-2.0.3.min.js"></script>
 <script type="text/javascript">
       function whois() {
	var domain = document.getElementById('domain').value;
	if(!domain) {
		alert('ERROR: Input cannot be empty.');
		document.getElementById("domain").focus();
	} else {
		window.location = './?q=' + domain;
	}
}
</script>
</head>
<body>
<div class="main">
<form action="<?php $_SERVER['PHP_SELF'];?>" id="form" class="form">
<div id="bg">
<div id="in">
<div class="search">
<input type="text" name="domain" id="domain" autocomplete="on" placeholder="Domain Name / IP Address" value="<?php echo $domain;?>">
<button id="submit" onclick="whois(); return false;" value="whois">WHOIS</button>
</div>
</div>
</div>
</form>
<?php
if($domain) {
	if(preg_match("/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/",$domain)) {
		$result = QueryWhoisServer("whois.apnic.net",$domain);
		//whois.apnic.net whois.lacnic.net
		echo "<pre>\n" . $result . "\n</pre>\n";
	} elseif(!preg_match("/^([-a-zA-Z0-9]{1,100})\.([a-z\.]{2,})$/i", $domain)) {
		die("<pre>\nERROR: Wrong format!\n</pre>\n");
	} else {
		$result = LookupDomain($domain);
		echo "<pre>\n" . $result . "\n</pre>\n";
	}
}
?>
</div>

<div class="footer"><br /><center>Copyright © 2021 <a href="./">Whois Search</center></div>
<div style="display:none">
<script>
document.getElementById("domain").focus();
if($('pre').length>0) {
document.getElementById("form").style.marginTop = "0";
} else {
document.getElementById("form").style.marginTop = "25%";
}
var _hmt = _hmt || [];
(function() {
  var hm = document.createElement("script");
  hm.src = "https://hm.baidu.com/hm.js?d75870c5f736e43afc590ee90880e0da";
  var s = document.getElementsByTagName("script")[0]; 
  s.parentNode.insertBefore(hm, s);
})();
</script>
</div>
<br />
</body>
</html>