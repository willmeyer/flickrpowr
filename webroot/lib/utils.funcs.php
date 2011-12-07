<?php
/**
 * Function declarations for miscellaneous helpful utilities.
 */

function dbglog($msg) {
	$log_line = $msg . "<br/>";
	//print($log_line);
	error_log($log_line);
}

function errlog($msg) {
	$log_line = $msg . "<br/>";
	//print($log_line);
	error_log($log_line);
}

function printError($msg) {
	print(buildError($msg));
}

function buildError($msg) {
    $msg = "<div class=\"error_message\">" . $msg . "</div>";
	return $msg;
}

function getParamDflt($params, $name, $dflt_val) {
    if (isset($params[$name])) {
		return $params[$name];
	} else {
		return $dflt_val;
	}	
}

function getPosessive($username) {
	// TODO
	return $username . "'s";
}

function getFullUrl() {
	$query_string = "";
	foreach ($_GET as $key => $value) {
		if ($key != "C") {  // ignore this particular $_GET value
	    	$query_string .= $key . "=" . urlencode($value) . "&";
	    }
	}
	return $SERVER["PHP_SELF"] . "?" . $query_string;
}

// Gets the http://server:port part of the request
function getServerBaseRequest() {
	$name = $_SERVER["SERVER_NAME"];
	if (isset($_SERVER["HTTPS"])) {
		if ($_SERVER["SERVER_PORT"] == "443") {
			$str = "https://" . $name;
		} else {
			$str = "https://" . $name . ":" . $_SERVER["SERVER_PORT"];
		}
	} else {
		if ($_SERVER["SERVER_PORT"] == "80") {
			$str = "http://" . $name;
		} else {
			$str = "http://" . $name . ":" . $_SERVER["SERVER_PORT"];
		}
	}
	return $str;
}

?>