<?php
/**
 * Function and constant declarations for managing the browsr's detail pane.
 */

require_once("inc/flickr.inc.php");
require_once("lib/BrowseCommand.class.php");

define("PARAM_PHOTOID", "photo_id");
define("PARAM_DETAILMODE", "cmd");
define("MODE_PHOTOHOVER", "photo_preview");
define("MODE_PHOTOPINNED", "photo_detail");
define("MODE_PHOTOMETA", "photo_meta");
define("MODE_PHOTOFLICKR", "photo_flickr");

function buildPhotoHoverUrl($photo_id) {
    return ("/" . URL_DETAILPANE . "?" . PARAM_DETAILMODE . "=" . MODE_PHOTOHOVER . "&" . PARAM_PHOTOID . "=" . $photo_id);
}

function buildPhotoPinnedUrl($photo_id, $meta_only) {
    $mode = ($meta_only) ? MODE_PHOTOMETA : MODE_PHOTOPINNED;
    $url = ("/" . URL_DETAILPANE . "?" . PARAM_DETAILMODE . "=" . $mode . "&" . PARAM_PHOTOID . "=" . $photo_id);
}

function getDfltDetailPaneContent() {
	return "";
}

function renderDetailPane($content) {
	print("<div id=\"detail_pane\">");
	print($content);
	print("</div>");
}

function renderTagMarkup($tag, $linked=TRUE) {
	$tag_name = $tag["_content"];
	if ($linked) {
		$tag_url = BrowseCommand::browseTagUrl($tag_name);
		$markup = "<a href=\"" . $tag_url . "\">" . $tag_name . "</a>";
	} else {
		$markup = $tag_name;
	}
	return $markup;
}

function renderSetMarkup($username, $set, $linked=TRUE) {
	$set_name = $set["title"];
	if ($linked) {
		$set_url = BrowseCommand::browseUserSetUrl($username, $set_name);
		$markup = "[<a href=\"" . $set_url . "\">" . $set_name . "</a>]";
	} else {
		$markup = $set_name;
	}
	return $markup;
}

function renderPoolMarkup($pool, $linked = TRUE) {
	$pool_id = $pool["id"];
	$pool_name = $pool["title"];
	if ($linked) {
		$pool_url = BrowseCommand::browsePoolUrl($pool_id);
		$markup = "[<a href=\"" . $pool_url . "\">" . $pool_name . "</a>]";
	} else {
		$markup = $pool_name;
	}
	return $markup;
}

function buildTagSummaryMarkup($photo, $linked = TRUE) {
	$tag_markup = NULL;
	if (count($photo["tags"]["tag"]) > 0) {
		$tag_markup = "<b>Tagged:</b> ";
		foreach($photo["tags"]["tag"] as $tag) {
			$tag_markup .= renderTagMarkup($tag, $linked) . " ";
		}
	}
	return $tag_markup;
}

function buildSetSummaryMarkup($username, $contexts, $linked = TRUE) {
	$set_markup = NULL;
	if (count($contexts["set"]) > 0) {
		$set_markup = "<b>In sets:</b> ";
		foreach($contexts["set"] as $set) {
			$set_markup .= renderSetMarkup($username, $set, $linked) . " ";
		}
	}
	return $set_markup;
}

function buildPoolSummaryMarkup($contexts, $linked=TRUE) {
	$pool_markup = NULL;
	if (is_array($contexts["pool"]) && count($contexts["pool"]) > 0) {
		$pool_markup = "<b>In pools:</b> ";
		foreach($contexts["pool"] as $pool) {
			$pool_markup .= renderPoolMarkup($pool, $linked) . "&nbsp;";
		}
	}
	return $pool_markup;
}

function renderTitleArea($title) {
	print("<div id=\"detail_pane_title\">");
	print($title);
	print("</div>");
}

function renderImageArea($img_url) {
	print("<div id=\"detail_pane_image\">");
	print("<img src=\"" . $img_url . "\"/>");
	print("</div>");
	print("<br/>");
}

function renderDescriptionAreaIfSet($description_markup) {
	if (isset($description_markup)) {
		print("<div id=\"detail_pane_desc\">");
		print($description_markup);
		print("</div>");
		print("<br/>");
	}
}

function renderListOfLinksIfSet($list_markup) {
	if (isset($list_markup)) {
		print("<div class=\"detail_pane_listoflinks\">");
		print($list_markup);
		print("</div>");
	}
}

function renderNavptions($photo) {
	$author_username = getPhotoAuthor($photo);
	$photopage_url = getPhotoPage($photo);
	$user_url = BrowseCommand::browseUserStreamUrl($author_username);
	$onblack_url = "/framr.php?cmd=view&photo_id=" . $photo["id"];
	print("<b>Browse:</b> <a target=\"_blank\" href=\"" . $photopage_url . "\">" . "[on flickr]" . "</a>");
	print("&nbsp; <a target=\"_blank\" href=\"" . $onblack_url . "\">" . "[on b/w]" . "</a>");
	print("&nbsp; <a href=\"" . $user_url . "\">" . "[browse user]" . "</a>");
}

function drawPhotoPanePinned($photo, $contexts, $meta_only=FALSE) {
	global $g_flickr;

	// Build up useful stuff
	$title = getPhotoTitleByAuthorSimple($photo, FALSE);
	$author_username = getPhotoAuthor($photo);
	$img_url = $g_flickr->buildPhotoURL($photo, $size = "medium");
	$description_markup = getPhotoDescription($photo);
	$comment_markup = "comments...";
	$tag_markup = buildTagSummaryMarkup($photo);
	$set_markup = buildSetSummaryMarkup($author_username, $contexts);
	$pool_markup = buildPoolSummaryMarkup($contexts);

	// Render it
	if (!$meta_only) {
		renderTitleArea($title);
		renderImageArea($img_url);
		print("<div id=\"detail_pane_meta\">");
	}
	renderDescriptionAreaIfSet($description_markup);
	renderNavptions($photo);
	renderListOfLinksIfSet($tag_markup);
	renderListOfLinksIfSet($set_markup);
	renderListOfLinksIfSet($pool_markup);
	if (isset($comment_markup)) {
		print("Comments: " . $comment_markup . "  <br/><br/>");
	}
	if (!$meta_only) {
		print("</div>");
	}
}

function drawPhotoPaneFast($photo) {
	global $g_flickr;

	// Build up useful stuff
	$img_url = $g_flickr->buildPhotoURL($photo, $size = "medium");
	$title = getPhotoTitleByAuthorSimple($photo, FALSE);
	$description_markup = getPhotoDescription($photo);
	$tag_markup = buildTagSummaryMarkup($photo, FALSE);

	// Render it...
	renderTitleArea($title);
	renderImageArea($img_url);
	print("<div id=\"detail_pane_meta\">");
	renderDescriptionAreaIfSet($description_markup);
	renderListOfLinksIfSet($tag_markup);
	print("</div>");
}

?>
