/*
 * jQuery AjaxQ - AJAX request queueing for jQuery
 *
 * Version: 0.0.1
 * Date: July 22, 2008
 *
 * Copyright (c) 2008 Oleg Podolsky (oleg.podolsky@gmail.com)
 * Licensed under the MIT (MIT-LICENSE.txt) license.
 *
 * http://plugins.jquery.com/project/ajaxq
 * http://code.google.com/p/jquery-ajaxq/
 */

 /* modifications by SJM 26/12/2011 to add the jquery block calls */
// TO DO - should I move this to the jquery ajaxq?
$.blockUI.defaults.message = '<img src="images/ajax-loader.gif" /><br/><h1 class="wait"> Just a moment...</h1>';
 
 
jQuery.ajaxq = function (queue, options)
{
	// Initialize storage for request queues if it's not initialized yet
	if (typeof document.ajaxq == "undefined") document.ajaxq = {q:{}, r:null};

	// Initialize current queue if it's not initialized yet
	if (typeof document.ajaxq.q[queue] == "undefined") document.ajaxq.q[queue] = [];
	
	if (typeof options != "undefined") // Request settings are given, enqueue the new request
	{
		// Copy the original options, because options.complete is going to be overridden

		var optionsCopy = {};
		for (var o in options) optionsCopy[o] = options[o];
		options = optionsCopy;
		
		// Override the original callback

		var originalCompleteCallback = options.complete;

		options.complete = function (request, status)
		{
			// Dequeue the current request
			document.ajaxq.q[queue].shift ();
			document.ajaxq.r = null;
			
			// Run the original callback
			if (originalCompleteCallback) originalCompleteCallback (request, status);

			// Run the next request from the queue
			if (document.ajaxq.q[queue].length > 0) {
				document.ajaxq.r = jQuery.ajax (document.ajaxq.q[queue][0]);
			} else {
				$.unblockUI();
			}
			
		};

		// Enqueue the request
		document.ajaxq.q[queue].push (options);

		// Also, if no request is currently running, start it
		if (document.ajaxq.q[queue].length == 1) {
			// had to hack the cursor as in some browsers the cursor stayed on an hour glass http://old.nabble.com/-blockUI--IE:-Cursor-still-displays-hourglass-symbol-after-unblocking-td25593484s27240.html
			$.blockUI({
				timeout: 30000,
				css: { cursor: 'default' },
				overlayCSS: { cursor: 'default' }
			});
			document.ajaxq.r = jQuery.ajax (options);
		}
	}
	else // No request settings are given, stop current request and clear the queue
	{
		if (document.ajaxq.r)
		{
			document.ajaxq.r.abort ();
			document.ajaxq.r = null;
		}

		document.ajaxq.q[queue] = [];
	}
}