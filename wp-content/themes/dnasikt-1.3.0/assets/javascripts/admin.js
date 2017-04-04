'use strict';

var applications = [];

// = DASHBAORD ====================== //
var dashboard = (function($) {

    var demo = function (e) {
        e.preventDefault ? e.preventDefault() : e.returnValue = false;
    }

    return {
        init: function() {
        	// Dashboard
        	var suggestion_count = $("#suggestion_count").val();
			$("#dashboard_widget_persons").find("h3").prepend( '<span class="label red">' + suggestion_count + ' st</span>' );

        	var post_count = $("#post_count").val();
			$("#dashboard_widget_conversation").find("h3").prepend( '<span class="label red">' + post_count + ' st</span>' );
        }
    }

})(jQuery);
applications.push(dashboard);


// = CONVERSATION ====================== //
var conversation = (function($,ajaxurl) {

    var demo = function (e) {
        e.preventDefault ? e.preventDefault() : e.returnValue = false;
    }

    return {
        init: function() {

        }
    }
})(jQuery,ajaxurl);
applications.push(conversation);





(function($) {

	$(document).ready(function() {

	    applications.forEach(function (app) {
	        if (typeof app.init == 'function') {
	            app.init()
	        } else {
	            console.log("STARTUP ERROR: Finns ingen funktion som heter app.init");
	            console.log(app);
	        }
	    });

	});

})(jQuery);
