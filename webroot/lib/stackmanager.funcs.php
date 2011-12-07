<?php
/**
 * Function and constant declarations for things related to the browse history
 * stack.
 */

define("PARAM_STACK_OP", "store");
define("PARAM_STACK_POPCOUNT", "delcount");
define("STACK_PUSH", "add");
define("STACK_CLEAR", "clear");
define("STACK_POP", "del");
define("STACK_NEWTOP", "newlast");

function logStackState() {
    dbglog("Stack array is currently: " . count($_SESSION["stack"]));
}

function clearStack() {
    unset($_SESSION["stack"]);
}

function getStackArray() {
    return $_SESSION["stack"];
}

/**
 * Looks at the stack control params and does the right thing.  We take the
 * browse request that we have so far, and potentially modify it based on the
 * stack controls, returning in any case the browse request that should be used
 * for this page render.
 *
 * We may redirect the browser if we want to hide the storage flags from the url
 * so they don't get bookmarked.
 */
function handleStack($request_params, $browse_req) {
    global $_SESSION;

    $stack_op = getParamDflt($request_params, PARAM_STACK_OP, STACK_NEWTOP);
    dbglog("Handling stack; operation is: " . $stack_op);
    logStackState();
    
    if ($stack_op == STACK_POP) {

    	// We're popping the stack...how far?
        $pop_count = getParamDflt($request_params, PARAM_STACK_POPCOUNT, "1");
        dbglog("Popping the stack, by " . $pop_count);

    	// The appropriate browse command will be whatever is at the new top position of the stack
        $stack_size = count($_SESSION["stack"]);
    	if ($pop_count > $stack_size) {
            dbglog("Something wrong, stack state is incorrect, going to the start of the stack instead.");
            if ($stack_size == 0) {
                clearStack();
                return NULL;
            } else {
                $pop_count = $stack_size;
            }
        }
        $i = 0;
        while ($i < $pop_count) {
		    array_pop($_SESSION["stack"]);
            $i++;
        }

        logStackState();

        // Get the new top of the stack, and redirect to it (or back to default if needed),
        // so the stack op is not in the address bar
        $stack_size = count($_SESSION["stack"]);
        if ($stack_size > 0) {
            $browse_req = $_SESSION["stack"][$stack_size-1];
            header("Location: " . $browse_req->toUrl());
        } else {
            header("Location: " . URL_BROWSE);
        }
        return $browse_req;
    } else if ($stack_op == STACK_CLEAR) {
        dbglog("Clearing stack...");
        clearStack();
        dbglog("Clearing stack...");
        return NULL;
    } else if ($stack_op == STACK_PUSH) {
        dbglog("Pushing the request onto the stack...");
        logStackState();
        pushStack($browse_req);
        logStackState();
        return $browse_req;
    } else {
        dbglog("REPLACE top item on stack...");
        logStackState();

    	// No specific stack option set, so we'll do a replace of the last element with the current browse, if we have a valid one
        if ($browse_req != NULL && $browse_req->isValid()) {
            if (!isset($_SESSION["stack"])) {
                dbglog("No stack, creating and adding element...");
                pushStack($browse_req);
            } else {
                $stack_size = count($_SESSION["stack"]);
                dbglog("Replacing element to stack at pos " . ($stack_size - 1));
                $_SESSION["stack"][($stack_size - 1)] = $browse_req;
            }
            logStackState();
        }
        return $browse_req;
    }
}

function pushStack($browse_req) {
    if (!isset($_SESSION["stack"])) {
        dbglog("No stack, creating one and adding element to position 0...");
        $_SESSION["stack"] = array();
        $_SESSION["stack"][0] = $browse_req;
    } else {
        $stack_size = count($_SESSION["stack"]);
        dbglog("Adding element to stack at pos " . $stack_size);
        $_SESSION["stack"][$stack_size] = $browse_req;
    }
}


?>
