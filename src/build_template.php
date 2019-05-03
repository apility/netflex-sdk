<?php
// Netflex Web - template builder. Gets current template
// Developer document. Not for production

// Get template
$template_id = $page['template'];
$template = NF::$site->templates['pages'][$template_id];

// TP Convert function
function tpConvert($templatename) {
	$clean = str_replace("/","-", $templatename);
	$clean = htmlentities($clean, ENT_COMPAT, "UTF-8");
	$clean = str_replace("&oslash;","o", $clean);
	$clean = str_replace("&quot;","", $clean);
	$clean = str_replace("&Oslash;","o", $clean);
	$clean = str_replace("&aring;","a", $clean);
	$clean = str_replace("&Aring;","a", $clean);
	$clean = str_replace("&aelig;","e", $clean);
	$clean = str_replace("&AElig;","e", $clean);
	$clean = str_replace("&eacute;","e", $clean);
	$clean = str_replace("&egrave;","e", $clean);
	$clean = str_replace("&amp;","g", $clean);
	$clean = str_replace("[","", $clean);
	$clean = str_replace("]","", $clean);
	$clean = str_replace("%20","_", $clean);
	$clean = str_replace("%","_", $clean);
	$clean = str_replace("'","", $clean);
	$clean = str_replace('"','', $clean);
	$clean = str_replace(" ","-", $clean);
	$clean = str_replace("+","_", $clean);
	$clean = str_replace(",","-", $clean);
	$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $clean);
	$clean = strtolower($clean);
	return $clean;
}
// Convert template id to file name
if ($template['alias']==null) {
	$tp_file = tpConvert($template['name']);
} else {
	$tp_file = $template['alias'];
}
$templatealias = $tp_file;

// Include the file
NF::debug('templates/'.$tp_file, 'template');
require(NF::$site_root . 'templates/'.$tp_file.'.php');
NF::debug('templates/'.$tp_file, '!template');
