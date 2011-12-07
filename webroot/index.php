<?php

require_once("lib/BrowseCommand.class.php");
require_once("inc/session.inc.php");
require_once("inc/flickr.inc.php");
include("inc/pagestart.inc.php");

?>
<div id="contentpage">

<h1>Flickr, and then some</h1>

Do you use <a href="http://www.flickr.com">Flickr</a> a lot?  FlickrPowr is a set of tools to make life better for Flickr power users.  Some of these things are available elsewhere, others definitely aren't.  If you are confused, these tools probably aren't for you.

<h2>Browsr</h2>

<p>
A way to browse through Flickr way faster than on flickr.com, by scanning large numbers of thumbnails for your contacts, your pools, your favorites, the explore page, and more.  Plus it has a cool navigation history to help ou bounce around the site and go on tangents, but then get right back to where you were.
</p>
<a href="/browse.php">Take me to the Browsr</a>.

<h2>Framr</h2>

<p>
Framr is a "nicer" way to expose a photo to others than by sending them the Flickr photo page URL.  Get a URL that has your photo on plain white, plain black, or other colors as you wish.  And yeah, there are some other ways to do this, but this is a NO CLUTTER option so your photo can stand out in all its glory.
</p>
<a href="/framr.php">Take me to Framr</a>.

<h2>Huh?</h2>

<p>
Have questions, suggestions, comments, whatever?  I'd love to hear them.  For now, just email me at will at willmeyer dot com.
</p>

</div>
<?php
include("inc/footer.inc.php");
include("inc/pageend.inc.php");
?>