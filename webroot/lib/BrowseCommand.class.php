<?php

require_once("inc/constants.inc.php");
require_once("inc/flickr.inc.php");
require_once("lib/utils.funcs.php");

// URL args and vals

// The flickr username to which the command applies
define("PARAM_USERNAME", "user");

// Commands (browse modes)
define("PARAM_CMD_MODE", "cmd");
define("CMD_MODE_CONTACTS", "usercontacts"); // all contacts' photos
define("CMD_MODE_POOLS", "userpools"); // photos in all the group pools of the specified member
define("CMD_MODE_POOL", "pool"); // photos in a specific group pool
define("CMD_MODE_SET", "userset"); // photos in a specific user set
define("CMD_MODE_USER", "userstream"); // the photostream of a specific user
define("CMD_MODE_TAG", "tag"); // photos tagged with a specific tag
define("CMD_MODE_EXPLORED", "explored"); // explored photos (today)
define("CMD_MODE_CONTACTSPOOLS", "contactsandpools"); // photos from all a user's contacts and all group pools combined
define("CMD_MODE_FAVS", "favs"); // favorites of a specific user

//
// Group results into sections; applies to some modes
define("PARAM_GROUPING", "grouping"); 
define("GROUPING_OFF", "0"); 
define("GROUPING_ON", "1"); 

// Tag 
define("PARAM_TAGNAME", "tag"); 

// Pool 
define("PARAM_POOLID", "poolid");
define("PARAM_POOLNAME", "poolname");

// Set
define("PARAM_SETNAME", "setname");

// Display mode
define("PARAM_DISPLAYSTYLE", "display"); 
define("DISPLAY_THUMBS", "thumb");
define("DISPLAY_MEDIUM", "med");

class BrowseCommand {

	var $username;
	var $mode;
	var $grouping;
	var $display_style;
	var $pool_id;
	var $pool_name;
	var $set_name;
	var $tag_name;

	function BrowseCommand() {
	}
	
	function parseFromRequest($request_params) {
		$this->username = getParamDflt($request_params, PARAM_USERNAME, NULL);
		$this->mode = getParamDflt($request_params, PARAM_CMD_MODE, CMD_MODE_EXPLORED);
		$this->grouping = getParamDflt($request_params, PARAM_GROUPING, GROUPING_OFF);
		$this->display_style = getParamDflt($request_params, PARAM_DISPLAYSTYLE, DISPLAY_THUMBS);
		$this->pool_id = getParamDflt($request_params, PARAM_POOLID, NULL);
		$this->pool_name = getParamDflt($request_params, PARAM_POOLNAME, NULL);
		$this->tag_name = getParamDflt($request_params, PARAM_TAGNAME, NULL);
		$this->set_name = getParamDflt($request_params, PARAM_SETNAME, NULL);
	}

	function isValid() {
		if ($this->mode == CMD_MODE_CONTACTS) {
			return ($this->username != NULL);
		} else if ($this->mode == CMD_MODE_POOLS) {
			return ($this->pool != NULL);
		} else if ($this->mode == CMD_MODE_POOL) {
			return ($this->pool_id != NULL || $this->pool_name != NULL);
		} else if ($this->mode == CMD_MODE_USER) {
			return ($this->username != NULL);
		} else if ($this->mode == CMD_MODE_SET) {
			if ($this->username != NULL && $this->set_name != NULL) {
				return true;
			} else {
				return false;
			}
		} else if ($this->mode == CMD_MODE_TAG) {
			return ($this->tag_name != NULL);
		} else if ($this->mode == CMD_MODE_EXPLORED) {
			return true;
		} else if ($this->mode == CMD_MODE_CONTACTSPOOLS) {
			if ($this->username != NULL && $this->pool_id != NULL) {
				return true;
			} else {
				return false;
			}
		} else if ($this->mode == CMD_MODE_FAVS) {
			return ($this->username != NULL);
		} else {
			return false;
		}
	}
	
	function toUrl() {
		$url = URL_BROWSE . "?" . PARAM_CMD_MODE . "=" . $this->mode;
		if ($this->mode == CMD_MODE_CONTACTS) {
			$url = $url . "&" . PARAM_GROUPING . "=" . $this->grouping;
			$url = $url . "&" . PARAM_USERNAME . "=" . $this->username;
			$url = $url . "&" . PARAM_DISPLAYSTYLE . "=" . $this->display_style;
		} else if ($this->mode == CMD_MODE_POOLS) {
			$url = $url . "&" . PARAM_USERNAME . "=" . $this->username;
			$url = $url . "&" . PARAM_DISPLAYSTYLE . "=" . $this->display_style;
		} else if ($this->mode == CMD_MODE_POOL) {
			$url = $url . "&" . PARAM_POOLID . "=" . $this->pool_id;
			$url = $url . "&" . PARAM_DISPLAYSTYLE . "=" . $this->display_style;
		} else if ($this->mode == CMD_MODE_USER) {
			$url = $url . "&" . PARAM_USERNAME . "=" . $this->username;
			$url = $url . "&" . PARAM_DISPLAYSTYLE . "=" . $this->display_style;
		} else if ($this->mode == CMD_MODE_TAG) {
			$url = $url . "&" . PARAM_TAGNAME . "=" . $this->tag_name;
			$url = $url . "&" . PARAM_DISPLAYSTYLE . "=" . $this->display_style;
		} else if ($this->mode == CMD_MODE_SET) {
			$url = $url . "&" . PARAM_SETNAME . "=" . $this->set_name;
			$url = $url . "&" . PARAM_DISPLAYSTYLE . "=" . $this->display_style;
		} else if ($this->mode == CMD_MODE_EXPLORED) {
			$url = $url . "&" . PARAM_DISPLAYSTYLE . "=" . $this->display_style;
		} else if ($this->mode == CMD_MODE_CONTACTSPOOLS) {
			$url = $url . "&" . PARAM_USERNAME . "=" . $this->username;
			$url = $url . "&" . PARAM_DISPLAYSTYLE . "=" . $this->display_style;
		} else if ($this->mode == CMD_MODE_FAVS) {
			$url = $url . "&" . PARAM_USERNAME . "=" . $this->username;
			$url = $url . "&" . PARAM_DISPLAYSTYLE . "=" . $this->display_style;
		} else {
			$url = "ERROR";
		}
		return $url;
	}

	static function browsePoolUrl($pool_id) {
		return URL_BROWSE . "?" . PARAM_CMD_MODE . "=" . CMD_MODE_POOL . "&" . PARAM_POOLID . "=" . urlencode($pool_id);
	}

	static function browseUserStreamUrl($username) {
		return URL_BROWSE . "?" . PARAM_CMD_MODE . "=" . CMD_MODE_USER . "&" . PARAM_USERNAME . "=" . urlencode($username);
	}

	static function browseTagUrl($tag_name) {
		return URL_BROWSE . "?" . PARAM_CMD_MODE . "=" . CMD_MODE_TAG . "&" . PARAM_TAGNAME . "=" . $tag_name;
	}
	
	static function browseUserSetUrl($username, $set_name) {
		return URL_BROWSE . "?" . PARAM_CMD_MODE . "=" . CMD_MODE_SET . "&" . PARAM_SETNAME . "=" . urlencode($set_name) 
		       . "&" . PARAM_USERNAME . "=" . urlencode($username);
	}
	
	function toString() {
		if ($this->mode == CMD_MODE_CONTACTS) {
			return "photos from " . getPosessive($this->username) . " contacts'";
		} else if ($this->mode == CMD_MODE_POOLS) {
			return getPosessive($this->username) . " pools' photos";
		} else if ($this->mode == CMD_MODE_POOL) {
			return "photos from the '" . $this->pool . "' pool";
		} else if ($this->mode == CMD_MODE_USER) {
			return getPosessive($this->username) . " photostream";
		} else if ($this->mode == CMD_MODE_TAG) {
			return "photos tagged with " . $this->tag_name . "";
		} else if ($this->mode == CMD_MODE_SET) {
			return "photos in " . getPosessive($this->username) . " '" . $this->set_name . "' set";
		} else if ($this->mode == CMD_MODE_EXPLORED) {
			return "explored photos";
		} else if ($this->mode == CMD_MODE_CONTACTSPOOLS) {
			return "photos from " . getPosessive($this->username) . " contacts and pools";
		} else if ($this->mode == CMD_MODE_FAVS) {
			return getPosessive($this->username) . " favorite photos";
		} else {
			$url = "ERROR";
		}
	}
}

?>
