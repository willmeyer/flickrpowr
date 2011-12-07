<?php

require_once("inc/flickr.inc.php");
require_once("lib/BrowseCommand.class.php");
require_once("lib/detailpane.funcs.php");
dbglog("--DETAILPANE START --------------------");

// Handle it...modes ("m"):
//  fast: show just the image and title, not for interaction
//  pinned: show all the photo detail
//  flickr: redirect to flickr photo page
//  pinned-meta: show the photo detail for pinned mode, but only the detail_pane_meta container.
$photo_id = $_GET[PARAM_PHOTOID];
$mode = $_GET[PARAM_DETAILMODE];
$photo = $g_flickr->photos_getInfo($photo_id, NULL);
if ($mode == MODE_PHOTOPINNED) {
	$contexts = $g_flickr->photos_getAllContexts($photo_id);
	drawPhotoPanePinned($photo, $contexts);
} else if ($mode == MODE_PHOTOHOVER) {
	drawPhotoPaneFast($photo);
} else if ($mode == MODE_PHOTOMETA) {
	$contexts = $g_flickr->photos_getAllContexts($photo_id);
	drawPhotoPanePinned($photo, $contexts, TRUE);
} else if ($mode == MODE_PHOTOFLICKR) {
	$photoPageUrl = getPhotoPage($photo);
	header("Location: " . $photoPageUrl) ;
}

dbglog("--DETAILPANE END --------------------");
?>
