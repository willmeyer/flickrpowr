<?php
// Looks for $browse_request to be set to the current browse request; that's what we're going to use for the control
// Renders the stack as well

require_once("lib/BrowseCommand.class.php");
require_once("lib/stackmanager.funcs.php");

?>

<script type="text/javascript">

function initPage() {
    //alert("initPage: entered");
    adjustBrowseChoices();
}

//window.onLoad = function() { initPage(); }
//window.ondomready = function() { initPage(); }
$(document).ready(function() {
   initPage();
 });

function adjustBrowseChoices() {
	
	// Do it based on mode...
    //alert("adjustBrowseChoices: initting...");
	var mode = document.getElementById("mode").value;
	//alert("mode: " + mode);
    var elem;
	
	// Adjust mode selector
	elem = document.getElementById("mode");
    elem.value = mode;

    // Adjust tag input
	elem = document.getElementById("tag");
	if (mode != "<?= CMD_MODE_TAG ?>") {
		elem.style.display = "none";
	} else {
		elem.style.display = "inline-block";
	}

	// Adjust grouping control
	elem = document.getElementById("grouping");
	if (mode == "<?= CMD_MODE_CONTACTS ?>" || mode == "<?= CMD_MODE_CONTACTSPOOLS ?>") {
		elem.style.display = "inline-block";
	} else {
		elem.style.display = "none";
	}
	
	// Adjust pool control
	elem = document.getElementById("pool");
	if (mode == "<?= CMD_MODE_POOL ?>") {
		elem.style.display = "inline-block";
	} else {
		elem.style.display = "none";
	}
	
	// Adjust username control
	elem = document.getElementById("username");
	if (mode == "<?= CMD_MODE_POOL ?>" || mode == "<?= CMD_MODE_TAG ?>" || mode == "<?= CMD_MODE_EXPLORED ?>") {
		elem.style.display = "none";
	} else {
		elem.style.display = "inline-block";
	}
	
}
</script>

<div id="browse_topbar">
	<form id="browseOpts" action="<?= URL_BROWSE ?>" method="GET">
		browse: 
		<select onchange="adjustBrowseChoices()" name=<?= PARAM_CMD_MODE ?> id="mode">
			<option value=<?= CMD_MODE_USER ?> <?= ($browse_req->mode == CMD_MODE_USER) ? "selected=\"selected\"" : "" ?> >user's photostream</option>
			<option value=<?= CMD_MODE_CONTACTS ?> <?= ($browse_req->mode == CMD_MODE_CONTACTS) ? "selected=\"selected\"" : "" ?> >user's contacts</option>
			<option value=<?= CMD_MODE_POOLS ?> <?= ($browse_req->mode == CMD_MODE_POOLS) ? "selected=\"selected\"" : "" ?> >user's pools</option>
			<option value=<?= CMD_MODE_CONTACTSPOOLS ?> <?= ($browse_req->mode == CONTACTSPOOLS) ? "selected=\"selected\"" : "" ?> >user's contacts + groups</option>
			<option value=<?= CMD_MODE_FAVS ?> <?= ($browse_req->mode == CMD_MODE_FAVS) ? "selected=\"selected\"" : "" ?> >user's favorites</option>
			<option value=<?= CMD_MODE_EXPLORED ?> <?= ($browse_req->mode == CMD_MODE_EXPLORED) ? "selected=\"selected\"" : "" ?> >explored</option>
			<option value=<?= CMD_MODE_POOL ?> <?= ($browse_req->mode == CMD_MODE_POOL) ? "selected=\"selected\"" : "" ?> >a pool</option>
			<option value=<?= CMD_MODE_TAG ?> <?= ($browse_req->mode == CMD_MODE_TAG) ? "selected=\"selected\"" : "" ?> >a tag</option>
		</select>

		<span id="username">
			&nbsp; &nbsp; username: <input type="text" name=<?= PARAM_USERNAME ?> value="<?= $browse_req->username ?>"/>
		</span>

		<span id="grouping">
			&nbsp; &nbsp; group results: <select name=<?= PARAM_GROUPING ?> id="gb">
				<option value=<?= GROUPING_OFF ?> <?= ($browse_req->grouping == GROUPING_OFF) ? "selected=\"selected\"" : "" ?> >off</option>
				<option value=<?= GROUPING_ON ?> <?= ($browse_req->grouping == GROUPING_ON) ? "selected=\"selected\"" : "" ?> >on</option>
			</select>
		</span>

		<span id="tag">
			&nbsp; &nbsp; tag: <input type="text" name=<?= PARAM_TAGNAME ?> value="<?= $browse_req->tag_name ?>" />
		</span>

		<span id="pool">
			&nbsp; &nbsp; pool: <input type="text" name=<?= PARAM_POOLNAME ?> value="<?= $browse_req->pool_name ?>" />
		</span>

		<span id="display">
			&nbsp; &nbsp; display: <select name="<?= PARAM_DISPLAYSTYLE ?>" id="d" >
				<option value=<?= DISPLAY_THUMBS ?> <?= ($browse_req->display_style == DISPLAY_THUMBS) ? "selected=\"selected\"" : "" ?> >thumbnail</option>
				<option value=<?= DISPLAY_MEDIUM ?> <?= ($browse_req->display_style == DISPLAY_MEDIUM) ? "selected=\"selected\"" : "" ?> >medium</option>
			</select>
		</span>
		&nbsp; 
		&nbsp; 
		&nbsp; 
		<input type="submit" value="browse"/ onload="adjustBrowseChoices()">
		&nbsp; add to my history <input type="checkbox" name="<?= PARAM_STACK_OP ?>" value="<?= STACK_PUSH ?>">
	</form>
	<?php
	print(buildStackMarkup(getStackArray()));
	?>
</div>

<?php

// Builds and returns the complete markup for the browse stack
function buildStackMarkup($requests) {
	dbglog("Building stack markup...");
    if (isset($requests) && (count($requests) > 0)) {
		$markup = "<div id=\"browse_stack\">";
		$markup .= "<a href=\"" . "/" . URL_BROWSE . "?" . PARAM_STACK_OP . "=" . STACK_CLEAR . "\">[home]</a> ";
		$stack_size = (count($requests));
        $i = 0;
		foreach($requests as $request) {
			$browse_text = $request->toString();

            // All but the last item get a link to that browse operation
            if ($i < ($stack_size - 1)) {
                $browse_url = $request->toUrl();
                $browse_url = $browse_url . "&" . PARAM_STACK_OP . "=" . STACK_POP . "&" . PARAM_STACK_POPCOUNT . "=" . ($stack_size - $i - 1);
                $markup .= ">> <a href=\"" . $browse_url . "\">" . $browse_text . "</a> ";
            } else {
                $markup .= ">> " . $browse_text;
            }
			$i++;
		}
        $markup .= "</div>";
	} else {
        dbglog("No stack at all...");
    }
	return $markup;
}

?>
