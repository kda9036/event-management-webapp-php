<?php

//sanitize input
function sanitizeString($var) {
	$var = trim($var);
	$var = stripslashes($var);
	$var = strip_tags($var);
	return $var;
}

// validation
function alphabeticSpace($value) {
	$reg = "/^[A-Za-z ]+$/";
	return preg_match($reg, $value);
}

function alphabeticNumericPunct($value) {
	$reg = "/^[A-Za-z0-9 _.,!?\"']+$/";
	return (preg_match($reg, $value));
}