<?php
/**
 * Common include file for any PHP that wants access to the Flickr API and our helpers for it; makes .
 */

require_once("lib/phpflickr/phpFlickr.php");

$KEY="a4567cee1cdc139f7bca62638e98437e";
$SECRET="47ea91b8cb6f48ac";

$g_flickr = new phpFlickr($KEY);

function getGroupPoolByName($group_name) {
	global $g_flickr;
	$groups = $g_flickr->groups_search($group_name, 5, 1);
	if ($groups != NULL) {
		foreach($groups["group"] as $group) {
			if ($group["name"] == $group_name) {
				return $group["nsid"]; 
			}
		}
	}
	return NULL;	
}

function getGroupPoolName($group_id) {
	global $g_flickr;
	$group = $g_flickr->groups_getInfo($group_id);
	return $group["name"];	
}

function getNSID($username) {
	global $g_flickr;
	$user = $g_flickr->people_findByUsername($username);
	$nsid = $user["nsid"];
	//$nsid = "33181597@N00";
	return $nsid;
}

function getIdOfSet($nsid, $set_name) {
	global $g_flickr;
	$sets = $g_flickr->photosets_getList($nsid);
	print("<br/><br/><br/><br/>");
	foreach($sets["photoset"] as $set) {
		if ($set["title"] == $set_name) {
			return $set["id"];
		}
	}
	return NULL;
}

function getPhotoPage($photo) {
	return $photo["urls"]["url"][0]["_content"];
}

function getPhotostreamPage($photo) {
	return $photo["urls"]["url"][0]["_content"];
}

function getPhotoTitle($photo, $quotes = FALSE) {
	$title = $photo["title"];
	if (strlen($title) == 0) {
		$title = "untitled";
	}
	if ($quotes) {
		$title = "'" . $title . "'";
	} 
	return $title;
}

function isNSID($string) {
	if (strpos($string, "@N") === false) {
		return false;
	} else {
		return true;
	}
}

// NULL if can't get it
function getPhotoAuthor($photo) {
	if (isset($photo["ownername"])) {
		return $photo["ownername"];
	} else if (isNSID($photo["owner"]) === true) {
		return NULL;
	} else {
		return $photo["owner"]["username"];
	}	
}

function getPhotoDescription($photo, $max=300) {
	if (isset($photo["description"]) && strlen($photo["description"]) > 0) {
		if (strlen($photo["description"]) > $max) {
			$desc = substr($photo["description"], 0, $max) . "...";
		} else {
			$desc = $photo["description"];			
		}
		return "<i>" . $desc . "</i>";
	}
	else {
		return NULL;
	}	
}

// title by author, one line
function getPhotoTitleByAuthorSimple($photo, $plain_text = FALSE) {
	if ($plain_text) {
		$str = "'" . getPhotoTitle($photo) . "'";
	} else {
		$str = "<b>" . getPhotoTitle($photo) . "</b>";
	}
	$author = getPhotoAuthor($photo);
	if ($author != NULL) {
		$str .= " by ";
		$str .= getPhotoAuthor($photo);
	}
	return $str;
}

?>
