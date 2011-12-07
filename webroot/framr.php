<?php

require_once("lib/BrowseCommand.class.php");
require_once("inc/session.inc.php");
require_once("inc/flickr.inc.php");

// Params
$photo_id = getParamDflt($_GET, "photo_id", "");
$photo_page = getParamDflt($_GET, "photo_page", "");
$bg_color = getParamDflt($_GET, "bg", "Black");
$fg_color = getParamDflt($_GET, "fg", "LightGray");
$size = getParamDflt($_GET, "size", "medium");
$cmd = getParamDflt($_GET, "cmd", "config");

// If no explicit photo, can we figure one out from the referer?
if ($photo_id == "" && $photo_page == "") {
	dbglog("No photo specified...looking for referer...");
	if (isset($_SERVER['HTTP_REFERER'])) {
		$referer = $_SERVER['HTTP_REFERER'];
		dbglog("Got referer: " . $referer);
		if (strpos($referer, 'http://www.flickr.com') !== false) {
			
			// Add the photo page param, and redirect back
			$_GET["photo_page"] = $referer;
			$full_url = getFullUrl();
			header("Location: " . $full_url) ;
		}
	}
}

// Handle the different modes
if ($cmd == "config") {
	$page_title = "FlickrPowr Framr";
	$page_add_head = "<script type=\"text/javascript\" src=\"js/framr.js\"></script>";
	include("inc/pagestart.inc.php");
?>

	<script type="text/javascript" >
		window.onLoad = function() { 
			alert("loaded"); 
		};
	</script>
	
	<div id="contentpage">
		<h1>Framr</h1>
		<p>
			Show your Flickr picture on a nice plain background of white, black, or whatever.  Fill out the form, Preview it to see what it looks like, then grab the code or URL.
		</p>
		<h2>Set it up</h2>
		<form id="viewerOpts" action="nicrviewr.php" method="GET">
			<input type="hidden" name="cmd" value="view" />
			Color scheme (enter any HTML or CSS colors):<br/><br/>
			<span id="bgColor">
				&nbsp; &nbsp; background color: <input type="text" name="bg" value="<?= $bg_color ?>" />
			</span>
			<span id="fgColor">
				&nbsp; &nbsp; foreground color: <input type="text" name="fg" value="<?= $fg_color ?>" />
			</span>
			<br/>
			<br/>
			The size to display: &nbsp; 
			<span id="size">
				<select name="size" id="size">
					<option value="medium" <?= ($size == "medium") ? "selected=\"selected\"" : "" ?> >medium</option>
					<option value="large" <?= ($size == "large") ? "selected=\"selected\"" : "" ?> >large</option>
				</select>
			</span>
			<h3>Which photo do you want to display?</h3>
			<span>
				You can copy the code down below into the Description or Comments field of ANY Flickr photo page...
			</span>
			<br/>
			<br/>
			&nbsp; &nbsp;<b>OR</b>
			<br/>
			<br/>
			<span id="photoPage">
				Flickr Photo Page URL (http://flickr/com/photos/XXX/XXXX): <input size="80" type="text" name="photo_page" value="<?= $photo_page ?>"/>
			</span>
			<br/>
			<br/>
			&nbsp; &nbsp;<b>OR</b>
			<br/>
			<br/>
			<span id="photoId">
				The Flickr ID of the photo (a long number): <input type="text" name="photo_id" value="<?= $photo_id ?>"/>
			</span>
			<br/>
			<br/>
			<input type="button" value="preview" onclick="popPreview('<?= getServerBaseRequest() . $_SERVER["PHP_SELF"] ?>');" />
			<input type="button" value="get the code" onclick="updateCode('<?= getServerBaseRequest() . $_SERVER["PHP_SELF"] ?>');" />
		</form>

		<h2>Share your Preview</h2>
		<h3>Share the URL</h3>
		<p>
			The URL below is the URL to your viewer:
		</p>
		<textarea id="viewUrl" rows="1" cols="80"></textarea>
		<h3>Copy this HTML</h3>
		<p>
			Copy the code below and stick it into your HTML page (including Flickr Descriptions and Comments) to pop up the viewer:
		</p>
		<textarea id="viewButton" rows="1" cols="80"></textarea>
		<h2>Coming soon...</h2>
		Browser bookmarklet, copy and past box.	 
	</div>
<?php
	include("inc/footer.inc.php");
	include("inc/pageend.inc.php");
} else if ($cmd == "view") {

	// Viewer mode...
	$page_title = "FlickrPowr Framr";
	
	// Get the photo
	if ($photo_id != "") {
		dbglog("Looking up photo by ID (" . $photo_id . ")...");
		$photo = $g_flickr->photos_getInfo($photo_id, NULL);
	} else if ($photo_page != "") {
		dbglog("Have photo url (" . $photo_page . "), figuring out ID...");
		$elems = explode("/", $photo_page);
		$photo_id = $elems[count($elems)-2];
		dbglog("Looking up photo by ID (" . $photo_id . ")...");
		$photo = $g_flickr->photos_getInfo($photo_id, NULL);
	}

	// If we got it, build the markup for the photo itself, page title, etc.
	if (isset($photo) && ($photo != NULL)) {

		// Get the real page tite
		$page_title = getPhotoTitle($photo) . " by " . getPhotoAuthor($photo);

		// Gen all the markup
		$img_url = $g_flickr->buildPhotoURL($photo, $size);
		$author_url = $g_flickr->urls_getUserPhotos($photo["owner"]["nsid"]);
		$markup = ("<h1><a href=\"" . getPhotoPage($photo) . "\">". getPhotoTitle($photo) . "</a></h1>");
		$markup = $markup . ("<h2><a href=\"" . $author_url . "\">by " . getPhotoAuthor($photo) . "</a></h2>");
		$img_tag = "<img src=\"" . $img_url . "\"/>";
		$markup = $markup . ("<a href=\"" . getPhotoPage($photo) . "\"" . $img_tag . "</a>"); 
	} else {
		$_GET["cmd"] = "config";
		$full_url = getFullUrl();
		$markup = "Sorry, we can't find that photo...(you can try <a href=\"" . $full_url . "\"><u>reconfiguring</u> this viewer</a>)";
	}
	
	// We've got to add some style overrides to the page head
	$page_add_head = "<style>";
	$page_add_head = $page_add_head . "body {background: " . $bg_color . "; color: " . $fg_color . ";}";
	$page_add_head = $page_add_head . "h1 {color: " . $fg_color . ";}";
	$page_add_head = $page_add_head . "h3 {color: " . $fg_color . ";}";
	$page_add_head = $page_add_head . "#viewr a {color: " . $fg_color . ";}";
	$page_add_head = $page_add_head . "#viewr a:hover {color: " . $fg_color . ";}";
	$page_add_head = $page_add_head . "#viewr-footer {background-color: " . $bg_color . ";}";
	$page_add_head = $page_add_head . "#viewr-footer a {color: " . $fg_color . ";}";
	$page_add_head = $page_add_head . "#viewr-footer a:hover {color: " . $fg_color . ";}";
	$page_add_head = $page_add_head . "</style>";
	
	// Render the HTML
	include("inc/pagestart.inc.php");
?>
	<div id="viewr">
		<?= $markup?>
	</div>
	<div id="viewr-footer" >
		<?php
		$_GET["fg"] = "LightGray";
		$_GET["bg"] = "Black";
		?>
		<a href="<?= getFullUrl() ?>">[on black]</a> &nbsp;
		<?php
		$_GET["fg"] = "Black";
		$_GET["bg"] = "White";
		?>
		<a href="<?= getFullUrl() ?>">[on white]</a> &nbsp;
		<!--
		<a href="<?= getFullUrl("true", $photo_id, $photo_page, $fg_color, $bg_color, $size) ?>">reconfigure viewer</a> &nbsp;
		-->	
		displayed by <a href="/framr.php">Framr</a><br/>
	</div>

<?php
}

?>