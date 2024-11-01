<?php

function get_getresponse($args = null) {
	global $gr;
	$gr->getresponse($args);
}
function get_getresponse_campaigns() {
	global $gr;
	return $gr->getresponse_campaigns();
}