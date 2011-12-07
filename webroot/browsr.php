<?php

/*
The main Browsr page, puts all the pieces together for a complete page render, including setting up the main 
elements -- the browse bar, the list_pane, and the detail_pane
*/
require_once("inc/constants.inc.php");
require_once("lib/BrowseCommand.class.php");
require_once("lib/stackmanager.funcs.php");
require_once("inc/session.inc.php");
require_once("inc/flickr.inc.php");
require_once("lib/utils.funcs.php");
require_once("lib/detailpane.funcs.php");
dbglog("--BROWSE START --------------------");

function buildGroupedPhotoList($photos_by_group, $display) {
	global $g_flickr;
	$markup = "";
	foreach (array_keys($photos_by_group) as $section_name) {
		$markup .= "<b>" . $section_name . "</b><br/><br/>";
		$photos = $photos_by_group[$section_name];
		$markup .= buildListPaneImagesMarkup($photos["photo"], $display_style);
		$markup .= "<br/><br/>";
	}
	return $markup;
}

function buildPoolLink($pool_id, $pool_name) {
	if (!isset($pool_name)) {
		$pool_name = getGroupPoolName($pool_id);
	}
	$url = BrowseCommand::browsePoolUrl($pool_id);
	return "<a href=\"" . $url . "\">" . $pool_name . "</a>";
}

/*
 * Builds the markup good for rendering a thumbnail image in the list pane for the given photo object, with appropriate javascript etc hooks.
 */
function buildListPaneThumbnailImgMarkup($photo) {
	global $g_flickr;
	
	// Build URL and tooltip text
	$url = $g_flickr->buildPhotoURL($photo, $size = "Thumbnail");
	$tip = getPhotoTitleByAuthorSimple($photo, TRUE);
	
	// Gen the markup, a linked image with a list_pane_thumbnail class, ID'd with the photo id and with attached JS
	$markup = "<img class=\"list_pane_thumbnail\" id=\"thumbnail_" . $photo["id"] . "\" name=\"thumbnail\" title=\"" . $tip . "\" src=\"" . $url . "\" onClick=\"handleListPaneThumbOnClick(" . $photo["id"] . ");\" onMouseOver=\"handleListPaneThumbOnHover(" . $photo["id"] . ");\"/>";
	return $markup;
}

/*
 * Builds the markup good for rendering a medium image in the list pane for the given photo object, with appropriate javascript etc hooks.
 */
function buildListPaneMediumImgMarkup($photo) {
	global $g_flickr;
	
	// Build URL and tooltip text
	$url = $g_flickr->buildPhotoURL($photo, $size = "medium");
	$tip = getPhotoTitleByAuthorSimple($photo, TRUE);

	// Gen the markup, a linked image with a list_pane_medium class, ID'd with the photo id and with attached JS
	$markup = "<img class=\"list_pane_medium\" id=\"thumbnail_" . $photo["id"] . "\" name=\"thumbnail\" title=\"" . $tip . "\" src=\"" . $url . "\" onClick=\"handleListPaneThumbOnClick(" . $photo["id"] . ");\" onMouseOver=\"handleListPaneThumbOnHover(" . $photo["id"] . ");\"/>";
	return "<div style=\"text-align: center;\">" . $markup . "</div>";
}

function buildContactsList($nsid, $group_by, $display_style) {
	global $g_flickr;
	$contacts = $g_flickr->contacts_getPublicList($nsid, 1, 10);
	if ($contacts == NULL) {
		return "Error getting contacts!";
	} else {
		$list = "";
		if ($group_by == "1") {
			
			// Grouping on, which means just enum all the arrays and build them into a [contact][photolist] array
			foreach($contacts["contact"] as $contact) {
				$photos = $g_flickr->people_getPublicPhotos($contact["nsid"], NULL, 30, 1);
				$photos_by_contact[$contact["username"]] = $photos["photo"];
			}
			return buildGroupedPhotoList($photos_by_contact, $display_style);
		} else {
			
			// No grouping, so we'll just get all of the contacts photos together, sorted
			$photos = $g_flickr->photos_getContactsPublicPhotos ($nsid, 50, NULL, NULL, NULL, "owner_name");
			if ($photos == NULL) {
				return "Error getting photos!";
			} else {
				return buildListPaneImagesMarkup($photos, $display_style);
			}
		}
	}
}

function buildListPaneImagesMarkup($photo_array, $display_style = DISPLAY_THUMBS) {
	$markup = "";
	foreach($photo_array as $photo) {
		$markup .= (($display_style == DISPLAY_THUMBS) ? buildListPaneThumbnailImgMarkup($photo) : buildListPaneMediumImgMarkup($photo));
	}
	return $markup;
}

function buildListPaneHeaderMarkup($content) {
	dbglog("Header markup...: " . $content);
	$markup = "<div id=\"list_pane_header\">";
	$markup .= $content;
	$markup .= "</div>";
	return $markup;
}

function buildFavoritesList($username, $nsid, $display_style) {
	global $g_flickr;
	$photos = $g_flickr->favorites_getPublicList($nsid, "owner_name", 500, 1);
	$list_pane_markup = "Error getting favorites!";
	if ($photos != NULL) {
		$user_url = BrowseCommand::browseUserStreamUrl($username);
		$header_markup = "<h1>Browsing <a href=\"" . $user_url . "\">" . getPosessive($username) . "</a> favorite photos</h1>";
		$list_pane_markup = buildListPaneHeaderMarkup($header_markup);
		$list_pane_markup .= buildListPaneImagesMarkup($photos["photo"], $display_style);
	}
	return $list_pane_markup;
}

function buildTagPhotosList($tag_name, $display_style) {
	global $g_flickr;
	$photos = $g_flickr->photos_search(array("tags"=>$tag_name, "tag_mode"=>"any", "per_page"=>500, "page"=>1, "extras"=>"owner_name"));
	$list_pane_markup = "Error getting tagged photos!";
	if ($photos != NULL) {
		$tag_url = BrowseCommand::browseTagUrl($tag_name);
		$header_markup = "<h1>Browsing photos tagged <a href=\"" . $tag_url . "\">" . $tag_name . "</a></h1>";
		$list_pane_markup = buildListPaneHeaderMarkup($header_markup);
		$list_pane_markup .= buildListPaneImagesMarkup($photos["photo"], $display_style);
	}
	return $list_pane_markup;
}

function buildExploredPhotosList($nsid, $display_style) {
	global $g_flickr;
	$photos = $g_flickr->interestingness_getList(NULL, "owner_name", 500, 1);
	$list_pane_markup = "Error getting most interesting photos!";
	if ($photos != NULL) {
		$header_markup = "<h1>Explored photos for today</h1>";
		$list_pane_markup = buildListPaneHeaderMarkup($header_markup);
		$list_pane_markup .= buildListPaneImagesMarkup($photos["photo"], $display_style);
	}
	return $list_pane_markup;
}

function buildUserPhotosList($username, $nsid, $display_style) {
	global $g_flickr;
	dbglog("Fetching photos for NSID " . $nsid);
	$list_pane_markup = "Unable to get photos!";
	$photos = $g_flickr->people_getPublicPhotos($nsid, NULL, 500, 1);
	if ($photos != NULL) {
		$user_url = BrowseCommand::browseUserStreamUrl($username);
		$header_markup = "<h1>Browsing <a href=\"" . $user_url . "\">" . getPosessive($username) . "</a> photostream</h1>";
		$list_pane_markup = buildListPaneHeaderMarkup($header_markup);
		$list_pane_markup .= buildListPaneImagesMarkup($photos["photo"], $display_style);
	}
	return $list_pane_markup;
}

function buildPoolPhotosList($pool_id, $pool_name, $display_style) {
	global $g_flickr;
	
	// Get the pool ID if we don't have one
	if (!isset($pool_id)) {
		$pool_id = getGroupPoolByName($pool_name);
		if ($pool_id == NULL) {
			return "Sorry, we can't find a pool with that name.";
		}
	}
	$photos = $g_flickr->groups_pools_getPhotos ($pool_id, NULL, NULL, "owner_name", 500, 1);
	$list_pane_markup = "Unable to get pool photos!";
	if ($photos != NULL) {
		$header_markup = "<h1>Browsing pool " . buildPoolLink($pool_id, $pool_name) . " </h1>";
		$list_pane_markup = buildListPaneHeaderMarkup($header_markup);
		$list_pane_markup .= buildListPaneImagesMarkup($photos["photo"], $display_style);
	}
	return $list_pane_markup;
}

function buildUserSetPhotosList($username, $nsid, $set_name, $display_style) {
	global $g_flickr;
	$set_id = getIdOfSet($nsid, $set_name);
	if (isset($set_id)) {
		$photos = $g_flickr->photosets_getPhotos($set_id, "owner_name", NULL, 500, 1);
		if ($photos == NULL) {
			return "Error getting photos!";
		} else {
			$user_url = BrowseCommand::browseUserStreamUrl($username);
			$set_url = BrowseCommand::browseUserSetUrl($username, $set_name);
			$header_markup = "<h1>Browsing <a href=\"" . $user_url . "\">" . getPosessive($username) . "</a> <a href=\"" . $set_url . "\">" . $set_name . "</a> set</h1>";
			$list_pane_markup = buildListPaneHeaderMarkup($header_markup);
			$list_pane_markup .= buildListPaneImagesMarkup($photos["photo"], $display_style);
			return $list_pane_markup;
		}
	} else {
		return "Unable to find a set '" . $set_name . "' for this user!";
	}
}

function renderListPane($content) {
	print("<div id=\"list_pane\">");
	print($content);
	print("</div>");
}

// Renders a two-section view consisting of a scrollable list_pane and a fixed detail_pane on its right
function renderTwoSectionView($list_content, $detail_content) {
	renderDetailPane($detail_content);
	renderListPane($list_content);
}

function doContactBrowse($nsid, $group_by, $display) {
	renderTwoSectionView(buildContactsList($nsid, $group_by, $display), getDfltDetailPaneContent());
}

function doFavoritesBrowse($username, $nsid, $display_style) {
	renderTwoSectionView(buildFavoritesList($username, $nsid, $display_style), getDfltDetailPaneContent());
}

function doExploredBrowse($nsid, $display) {
	renderTwoSectionView(buildExploredPhotosList($nsid, $display), getDfltDetailPaneContent());
}

function doPoolBrowse($pool_id, $pool_name, $display_style) {
	renderTwoSectionView(buildPoolPhotosList($pool_id, $pool_name, $display_style), getDfltDetailPaneContent());
}

function doUserSetBrowse($username, $nsid, $set_name, $display_style) {
	renderTwoSectionView(buildUserSetPhotosList($username, $nsid, $set_name, $display_style), getDfltDetailPaneContent());
}

function doUserBrowse($username, $nsid, $display) {
	renderTwoSectionView(buildUserPhotosList($username, $nsid, $display), getDfltDetailPaneContent());
}

function doTagBrowse($tag_name, $display_style) {
	renderTwoSectionView(buildTagPhotosList($tag_name, $display_style), getDfltDetailPaneContent());
}

// Get the browse request, either by params or default
dbglog("Building BrowseCommand...");
$browse_req = new BrowseCommand();
$browse_req->parseFromRequest($_GET);

// Handle the stack
dbglog("Handling stack...");
$new_browse_req = handleStack($_GET, $browse_req);
if ($new_browse_req != NULL) {
    $browse_req = $new_browse_req;
}

// Start the page
include("inc/pagestart.inc.php");

// Dump the JS
?>

<script type="text/javascript">

var g_pinnedPhotoId = null;
var g_hoveredPhotoId = null;

function clearDetailPane() {
	document.getElementById("detail_pane").innerHTML = "<?= getDfltDetailPaneContent() ?>";
}

function sendPhotoHoverToDetailPane(photoId) {
    var url = "/<?= URL_DETAILPANE ?>" + "?<?= PARAM_DETAILMODE ?>=<?= MODE_PHOTOHOVER ?>&<?= PARAM_PHOTOID ?>=" + photoId;
    $("#detail_pane").html("Loading image...");
    $("#detail_pane").load(url);
}

function sendPhotoPinnedToDetailPane(photoId) {
    $("#detail_pane_meta").html("Loading details...");
    var url = "/<?= URL_DETAILPANE ?>" + "?<?= PARAM_DETAILMODE ?>=<?= MODE_PHOTOMETA ?>&<?= PARAM_PHOTOID ?>=" + photoId;
    $("#detail_pane_meta").load(url);
}

// If the first click on an unpinned list-view image, pin the image into the detail pane
// If the second click, on an image pinned with the first, open the flickr photo page in a new window
// If the first click on an image when another one is pinned, unpin the first
function handleListPaneThumbOnClick(photoId) {
	if (g_pinnedPhotoId == photoId) {

		// The click of an image that is already pinned

		// Open the flickr page
        var flickrUrl = "/<?= URL_DETAILPANE ?>?<?= PARAM_PHOTOID?>=" + photoId + "&<?= PARAM_DETAILMODE ?>=<?= MODE_PHOTOFLICKR ?>";
		window.open(flickrUrl);
	} else if (g_pinnedPhotoId == null) {

		// The click of an image, none is pinned, so pin this one
		g_pinnedPhotoId = photoId;
		sendPhotoPinnedToDetailPane(photoId);
		$("thumbnail_" + g_pinnedPhotoId).css("border", "white 2px solid");
	} else {

		// The click of an image different than the one that is currently pinned; unpin it, and hover this one
		$("thumbnail_" + g_pinnedPhotoId).css("border", "none");
		g_pinnedPhotoId = null;
		//clearDetailPane();
		sendPhotoHoverToDetailPane(photoId);
	}
}

function handleListPaneThumbOnHover(photoId) {

	// If there is nothing pinned, show the hovered image in the detail pane
	if (g_pinnedPhotoId == null) {
		if (g_hoveredPhotoId != photoId) {
			g_hoveredPhotoId = photoId;
			sendPhotoHoverToDetailPane(photoId);
		}
	}
}

function xhrStateChange() {
	if (xmlhttp.readyState==4)
	  {// 4 = "loaded"
	  if (xmlhttp.status==200)
	    {// 200 = OK
			var isMeta = !(xmlhttp.responseText.substring(0, 28) == "<div id=\"detail_pane_title\">");
			if (isMeta) {
				document.getElementById("detail_pane_meta").innerHTML = xmlhttp.responseText;
			} else {
				document.getElementById("detail_pane").innerHTML = xmlhttp.responseText;
			}
	    }
	  else
	    {
	    alert("Problem retrieving XML data");
	    }
	  }
}
</script>

<?php
dbglog("Rendering browse control bar...");
include("inc/browsebar.php");

// Do the actual browse operation, unless we don't have a browse request, or we have an invaid one
dbglog("Starting browse process...");
if ($browse_req->isValid()) {

	// Handle the different browse modes
	if ($browse_req->mode == CMD_MODE_CONTACTS) {
		dbglog("Contact browse...");
		$nsid = getNSID($browse_req->username);
		doContactBrowse($nsid, $browse_req->grouping, $browse_req->display_style);
	} else if ($browse_req->mode == CMD_MODE_POOLS) {
		print("TODO");
	} else if ($browse_req->mode == CMD_MODE_POOL) {
		$pool_id = $browse_req->pool_id;
		doPoolBrowse($pool_id, $browse_req->pool_name, $browse_req->display_style);
	} else if ($browse_req->mode == CMD_MODE_SET) {
		$nsid = getNSID($browse_req->username);
		doUserSetBrowse($browse_req->username, $nsid, $browse_req->set_name, $browse_req->display_style);
	} else if ($browse_req->mode == CMD_MODE_USER) {
		dbglog("User browse...");
		$nsid = getNSID($browse_req->username);
		doUserBrowse($browse_req->username, $nsid, $browse_req->display_style);
	} else if ($browse_req->mode == CMD_MODE_TAG) {
		dbglog("Tag browse...");
		doTagBrowse($browse_req->tag_name, $browse_req->display_style);
	} else if ($browse_req->mode == CMD_MODE_EXPLORED) {
		dbglog("Explored browse...");
		$nsid = getNSID($browse_req->username);
		doExploredBrowse($nsid, $browse_req->display_style);
	} else if ($browse_req->mode == CMD_MODE_CONTACTSPOOLS) {
		print("TODO");
	} else if ($browse_req->mode == CMD_MODE_FAVS) {
		dbglog("Favorites browse...");
		$nsid = getNSID($browse_req->username);
		dbglog("   display: " . $browse_req->display_style);
		doFavoritesBrowse($browse_req->username, $nsid, $browse_req->display_style);
	} else {
        errlog("Invalid browse operation!");
	}
} else {
	dbglog("Invalid browse operation!");
	if ($browse_req != NULL) {
        dbglog("  browse operation:" . $browse_req->mode);
    }
    printError("Sorry, we don't know how to do that...");
}

// All done
//var_dump($g_flickr->error_msg);
include("inc/footer.inc.php");
include("inc/pageend.inc.php");
dbglog("--BROWSE END --------------------");
?>

 

