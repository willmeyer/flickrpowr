<?php

// Starts a page on the site, writing the page head and starting the body in a sitepage div.  
// Accepts optional variables:
//   $page_title: the title for the page
//   $page_add_head: some arbitrary markup to add to the page head

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
    <title>
<?php 
    if (isset($page_title)) { 
	    echo $page_title;
	} else {
	    echo "FlickrPowr: Flickr, and then some";
	}
?>
    </title>
	<meta http-equiv="pragma" content="no-cache">
	<meta http-equiv="cache-control" content="no-cache">
	<meta http-equiv="expires" content="0">    
	<meta http-equiv="keywords" content="flickr flickrPowr">
	<meta http-equiv="description" content="flickrPowr">
	<link rel="stylesheet" type="text/css" href="styles/main.css">
<?php 
    if (isset($page_add_head)) { 
	    echo $page_add_head;
	}
?>
    <script type="text/javascript" src="js/jquery/1.4.2/jquery.min.js"></script>
</head>
<body>
	<div id="sitepage">
