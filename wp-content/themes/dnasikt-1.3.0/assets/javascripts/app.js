'use strict';

var applications = [];

var app = (function($) {

    var toggle_element = function (e) {
        e.preventDefault ? e.preventDefault() : e.returnValue = false;

        var $el = $(this);
        var selector = $el.attr("data-id");
        var new_text = $el.attr("data-text");
        var old_text = $el.text();

        $( selector ).slideToggle();

        $el.text( new_text ).attr("data-text", old_text);
    }

    var update_profile = function(e) {
        e.preventDefault ? e.preventDefault() : e.returnValue = false;

        // Check if no errors
        if( ! utils.validateInputs( [$("#display_name")] ) ) {
            return;
        }

        $(this).closest("form").submit();
    }

    return {
        init: function() {
            $(document).on('click', '.js--toggle_element', toggle_element);
            $(document).on('click', 'input#submit_update_profile', update_profile);
        }
    }

})(jQuery);
applications.push(app);


var ads = (function($) {

    var $all_ads = $("[class^='dnasikt-ad-']");

    var sizes = function (e) {
        var column_width = $('.col-md-2.banner-content').outerWidth();
        $('#fixed-ad').outerWidth( column_width );
        $('#not-fixed-ad').outerWidth( column_width );

        var $sidebar_one = $('.block-1 aside.sidebar .content');
        var $sidebar_two = $('.block-2 aside.sidebar .content');
        var $mobile_sidebar_one = $('.mobile-sidebar .content').eq(0);
        var $mobile_sidebar_two = $('.mobile-sidebar .content').eq(1);

        if( ResponsiveBootstrapToolkit.is('xs') ) {
            if( $sidebar_one.parent().hasClass("sidebar") ) {
                $sidebar_one.appendTo('.mobile-sidebar');
                $sidebar_two.appendTo('.mobile-sidebar');
            }
        } else {

            if( $mobile_sidebar_one.parent().hasClass("mobile-sidebar") ) {
                $mobile_sidebar_one.appendTo('.block-1 aside.sidebar');
                $mobile_sidebar_two.appendTo('.block-2 aside.sidebar');
            }
        }
    }

    var keep_ratio_resize = function() {
        $all_ads.each( function() {
            var $this = $( this );
            if($this.is(':visible')) {

                var original_width = $this.width();

                // Flash discards CSS, so we need to enforce size adjustments
                var $objects = $this.find("embed", "object");

                if( $objects.length ) {
                    for(index = 0; index < $objects.length; ++index) {

                        var width = $objects[index].width;
                        var height = $objects[index].height;

                        var ratio = original_width / width;

                        $objects[index].width = ratio * width;
                        $objects[index].height = ratio * height;

                        // 'dnasikt-adjustedwidth' is not used for any pratical reasons,
                        // and is only kept as a quick way to track down issues
                        $objects[index].className = $objects[index].className + ' dnasikt-adjustedwidth';
                    }
                }

                // Setting proper height for resized ads, avoiding tracking pixels in the process
                var $images = $this.find("img");

                if( $images.length ) {
                    for(index = 0; index < $images.length; ++index) {
                        if($images[index].height > 1) {
                            $images[index].className = $images[index].className + ' dnasikt-autoheight';
                        }
                    }
                }

                // var $wrapper = $this.parent('.ad-wrapper');
                // var current_width = $this.outerWidth();
                // var current_height = $this.outerHeight();
                // var optimized_width = $this.attr("data-width") || 0;

                // var wrapper_width = $wrapper.outerWidth();
                // var wrapper_height = $wrapper.outerHeight();

                // if( wrapper_width < optimized_width ) {
                //     var ratio = ( wrapper_width / current_width );
                //     $this.outerWidth( current_width * ratio );
                //     $this.outerHeight( current_height * ratio );
                //     if( current_width == 0 ) {
                //         $this.outerWidth( wrapper_width );
                //         $this.outerHeight( current_height * ratio );
                //     }
                // }
            }
        } );
    }

    return {
        init: function() {
            sizes();
            keep_ratio_resize();
            $('#fixed-ad').attr("data-offset-top", '280');
            $('#fixed-ad').attr("data-offset-bottom",($('.footer.container-footer').outerHeight(true) - 37));
            $(window).resize( function() {
                sizes();
                keep_ratio_resize();
                // $('#fixed-ad').attr("data-offset-bottom",($('.footer.container-footer').outerHeight(true) - 37));
            });
        }
    }

})(jQuery);
applications.push(ads);

var alerts = (function($,wp) {
    var $holder = $("#alerts");

    var addAlert = function ( options ) {
        var id = '_alert';
        var icon = '';
        var text = '';
        var color = 'white';
        var fade = true;

        if(typeof options !== 'object') {
            var options = {
                id: id,
                text: text,
                icon: icon,
                color: color,
                fade: fade
            }
        }

        if(typeof options.id !== 'undefined') id = options.id;
        if(typeof options.text !== 'undefined') text = options.text;
        if(typeof options.icon !== 'undefined') icon = '<i class="fa '+options.icon+'"></i>';
        if(typeof options.color !== 'undefined') color = options.color;
        if(typeof options.fade !== 'undefined') fade = options.fade;

        if(id == 'not-logged-in') text += ' <a href="'+wp.loginurl+'">Logga in</a> eller <a href="'+wp.createaccounturl+'">skapa ditt konto</a>!';

        if($('#'+id).length === 0) {
            $holder.append('<div class="'+color+'" id="'+id+'"><button class="close"><i class="dnicon-close"></i></button>'+icon+'<span>'+text+'</span></div>');
            var $el = $('#'+id).fadeIn();
            setTimeout(function() {
                $el.fadeOut();
                $el.remove();
            },5000);
        }
    }

    var removeAlert = function( $alert ) {
        $alert.remove();
    }

    var onClickClose = function(e) {
        e.preventDefault ? e.preventDefault() : e.returnValue = false;
        removeAlert( $(this).parent() );
    }

    return {
        init : function () {
            $(document).on('click', '#alerts > div > .close', onClickClose);
        },
        add : addAlert,
        remove : removeAlert,
    }

})(jQuery,WordpressGlobalVariables);
applications.push(alerts);

var utils = (function($,alerts) {

    var validateInputs = function ( required ) {
        var valid = true;
        required.forEach(function(element, index, array) {
            if(element.val() == '') {
                element.closest(".form-group").addClass("has-error");
                valid = false;
            } else {
                element.closest(".form-group").removeClass("has-error");
            }
        });

        return valid;
    }

    var printErrors = function ( errors ) {
        var valid = true;
        errors.forEach(function(element, index, array) {
            $("input[name='"+element.name+"']").closest(".form-group").addClass("has-error");
        });

        return valid;
    }

    var resetForm = function ( selector ) {
        var $form = $(selector);
        $form.find('input:text, input:password, input:file, select, textarea').val('');
        $form.find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
        $form.find('.form-group').removeClass("has-error");
    }

    var timestamp = function() {
        return Math.floor(Date.now() / 1000);
    }

    var isInt = function(n){
        return Number(n)===n && n%1===0;
    }

    var youAreSpaming = function() {
        console.log("Du spammar nu! Vänta i 3 sekunder");
    }

    return {
        validateInputs : validateInputs,
        printErrors : printErrors,
        resetForm : resetForm,
        timestamp : timestamp,
        isInt : isInt,
        youAreSpaming : youAreSpaming,
    }

})(jQuery, alerts);

var frontpage = (function($,wp) {

    var get_more_conversations = function(e) {
        e.preventDefault ? e.preventDefault() : e.returnValue = false;
        var $btn = $( this );
        var offset = $('article.conversation').length;

        var data = {
            action: 'get_more_posts',
            post_type: 'asikt',
            offset: offset
        }

        $.ajax({
            method: 'POST',
            url: wp.ajaxurl,
            dataType: 'json',
            data: data,
            success: function(result) {
                if(result.count != 0) {
                    $btn.before(result.html);
                } else {
                    alerts.add({id: 'no-more-posts',text:'Det finns inga fler debatter', icon: 'fa-list',color:'red'});
                    $btn.hide();
                }
            },
            error: function(result) {
                console.log("Error");
            }
        })
    }

    return {
        init: function() {
            $(document).on('click','.js--get-more-conversations', get_more_conversations);
        }
    }

})(jQuery,WordpressGlobalVariables);
applications.push(frontpage);





var navbar = (function($,wp) {


    var AJAX_RUNNING = false;
    var SPAM_TIMER = 1500;

    var toggle_search = function(e) {
        e.preventDefault ? e.preventDefault() : e.returnValue = false;
        var $btn = $( this );
        var $form = $('#searchform');
        var $input = $('#search');

        if( ( $input.hasClass("show") || $('.navbar-collapse').hasClass('in') ) && $input.val() !== '' ) {
            $form.submit();
        }
        if( $input.hasClass("show") ) {
            setTimeout(function() {
                $btn.find("span").show();
            },500);
        } else {
            $btn.find("span").hide();
        }
        $input.toggleClass("show");
    }

    var toggle_notification_bar = function(e) {
        e.preventDefault ? e.preventDefault() : e.returnValue = false;

        if(AJAX_RUNNING) {
            return;
        }

        // Elements
        var $btn = $(this);

        AJAX_RUNNING = true;
        $.ajax({
            type : "post",
            dataType : "json",
            url : wp.ajaxurl,
            data : {
                action: "clear_notifications"
            },
            success: function(result) {
                $btn.find(".badge").text('0');
            },
            error: function(result) {
                console.log("Error");
            },
            complete: function() {
                setTimeout(function() {
                    AJAX_RUNNING = false;
                }, SPAM_TIMER);
            },
        });

    }

    return {
        init: function() {
            $(document).on('click','.js--toggle-search-input', toggle_search);
            $(document).on('click','.js--toggle_notification_bar', toggle_notification_bar);
        }
    }

})(jQuery,WordpressGlobalVariables);
applications.push(navbar);

var conversation = (function($,wp,utils,alerts) {

    var AJAX_RUNNING = false;
    var SPAM_TIMER = 1500;

    var delete_field_person = function ( e ) {
        e.preventDefault ? e.preventDefault() : e.returnValue = false;
        $(this).closest('.repeatable_field').remove();
    }

    var add_reply_vote = function(e) {
        e.preventDefault ? e.preventDefault() : e.returnValue = false;
        if(AJAX_RUNNING) {
            utils.youAreSpaming();
            return;
        }

        // Elements
        var $el = $(this);
        var $buttons = $(".add-reply-vote");
        var $prev_button = $buttons.filter(".selected");
        var $current_row = $el.closest(".row");
        var $prev_vote_count = $prev_button.closest(".row").find(".votes");
        var $current_vote_count = $current_row.find(".votes");

        // Vars
        var reply_id = $el.attr("data-id");
        var prev_reply_id = $prev_button.attr("data-id") || 0;

        AJAX_RUNNING = true;
        $.ajax({
            type : "post",
            dataType : "json",
            url : wp.ajaxurl,
            data : {
                action: "add_vote",
                reply_id: reply_id,
                prev_reply_id: prev_reply_id,
                already_voted: reply_cookie_exists()
            },
            success: function(result) {
                if(result.status == 'error') {
                    alerts.add({id: 'not-logged-in', text: result.message, icon: 'dnicon-person',color:'red'});
                    return false;
                }
                if(result.prev !== null) $prev_vote_count.text( result.prev );
                $current_vote_count.text( result.current );

                $el.addClass("selected");
                $prev_button.removeClass("selected");

                if($el.text() == "Rösta") {
                    $el.text("Ta bort röst");
                }
                if($prev_button.text() === "Rösta") {
                    $prev_button.text("Ta bort röst");
                } else {
                    $prev_button.text("Rösta");
                }

                if(!result.loggedin)
                    set_reply_cookie();
            },
            error: function(result) {
                console.log("Error");
            },
            complete: function() {
                setTimeout(function() {
                    AJAX_RUNNING = false;
                }, SPAM_TIMER);
            },

        });
    }



    var follow_post = function(e) {
        e.preventDefault ? e.preventDefault() : e.returnValue = false;

        var $btn = $(this);
        var old_text = $btn.find('.text').text();
        var new_text = $btn.attr("data-text");

        if(AJAX_RUNNING) {
            utils.youAreSpaming();
            return;
        }

        AJAX_RUNNING = true;
        $.ajax({
            type : "post",
            dataType : "json",
            url : wp.ajaxurl,
            data : {
                action: "follow_post",
                nonce: $btn.attr("data-nonce"),
                post_id: $btn.attr("data-id")
            },
            success: function(result) {
                if(result.status == 'error') {
                    alerts.add({id: 'not-logged-in',text: result.message, icon: 'dnicon-person',color:'red'});
                    return false;
                }
                if(result.status == 'ok') {
                    $btn.attr("data-text", old_text).find('.text').text( new_text );
                    alerts.add({ id: 'follow-post' ,text: result.message, icon: '', color:'green' });

                    if($btn.parent().hasClass("following")) {
                        $btn.parent().removeClass("following");
                    }
                    else {
                        $btn.parent().addClass("following");
                    }

                }
            },
            error: function(result) {
                console.log("Error");
            },
            complete: function() {
                setTimeout(function() {
                    AJAX_RUNNING = false;
                }, SPAM_TIMER);
            },
        });
    }

    var add_suggestion = function(e) {
        e.preventDefault ? e.preventDefault() : e.returnValue = false;

        if(AJAX_RUNNING) {
            utils.youAreSpaming();
            return;
        }

        var required = [];
        var name = $("#suggestion-name");
        required.push(name);
        var contact = $("#suggestion-contact");
        //required.push(contact);
        var motivation = $("#suggestion-motivation");
        required.push(motivation);

        // Check if no errors
        if( ! utils.validateInputs( required ) ) {
            return;
        }

        AJAX_RUNNING = true;
        $.ajax({
            type : "post",
            dataType : "json",
            url : wp.ajaxurl,
            data : {
                action: "add_suggestion",
                nonce: $("#suggestion-nonce").val(),
                post_id: $("#suggestion-post_parent").val(),
                name: name.val(),
                contact: contact.val(),
                motivation: motivation.val()
            },
            success: function(result) {
                if(result.status == 'error') {
                    utils.printErrors( result.errors );
                }
                if(result.status == 'ok') {
                    utils.resetForm( "#suggestion-form" );
                    alerts.add({id: 'suggestion-added',text:'Tack för ditt förslag!', icon: 'dnicon-person',color:'green'});
                }
            },
            error: function(result) {
                console.log("Error");
            },
            complete: function() {
                setTimeout(function() {
                    AJAX_RUNNING = false;
                }, SPAM_TIMER);
            },
        });
    }

    var toggle_information_box = function (e) {
        e.preventDefault ? e.preventDefault() : e.returnValue = false;
        var $btn = $(this);
        $("#" + $btn.attr("data-rel") ).toggleClass("active").slideToggle(200);
        var $icon = $btn.find("i[class^='dnicon-chevron-']");
        $btn.toggleClass("active");
        if ($btn.hasClass("active")) {
            $icon.removeClass("dnicon-chevron-down").addClass("dnicon-chevron-up");
        } else {
            $icon.removeClass("dnicon-chevron-up").addClass("dnicon-chevron-down");
        }
    }

    var anchor_link = function (e) {
        e.preventDefault ? e.preventDefault() : e.returnValue = false;
        var target = this.hash;
        var $target = $(target);
        $('html, body').stop().animate({
            'scrollTop': $target.offset().top-100
        }, 900, 'swing', function () {
            window.location.hash = target;
        });
    }



    var toggle_add_suggestion_form = function (e) {
        e.preventDefault ? e.preventDefault() : e.returnValue = false;
        var $btn = $(this);
        var $row = $btn.siblings('.row');
        if($row.length == 0) {
            alerts.add({id: 'not-logged-in',text:'Du måste vara inloggad för att lägga till nya förslag.', icon: 'dnicon-person',color:'red'});
            return;
        }
        if( !$row.is(":visible") ) {
            $btn.find("span").text("Dölj formuläret");
            $btn.closest(".dnicon-plus").addClass("dnicon-minus").removeClass("dnicon-plus");
        } else {
            $btn.find("span").text("Föreslå fler personer");
            $btn.closest(".dnicon-minus").addClass("dnicon-plus").removeClass("dnicon-minus");
        }
        $row.slideToggle();
    }

    var add_conversation_vote = function(e) {
        e.preventDefault ? e.preventDefault() : e.returnValue = false;

        if(AJAX_RUNNING) {
            utils.youAreSpaming();
            return;
        }

        var $btn = $(this);
        var post_id = $btn.attr("data-id");
        var vote = $btn.attr("data-vote");

        AJAX_RUNNING = true;
        $.ajax({
            type : "post",
            dataType : "json",
            url : wp.ajaxurl,
            data : {
                action: "add_conversation_vote",
                nonce: $("#add-conversation-vote-nonce").val(),
                post_id: post_id,
                vote: vote,
                already_voted: post_cookie_exists()
            },
            success: function(result) {
                if(result.status == 'error') {
                    alerts.add({id: 'not-logged-in',text: result.message, icon: 'dnicon-person',color:'red'});
                    return false;
                }
                if(result.status == 'ok') {
                    alerts.add({id: 'add-conversation-vote',text:'Tack för din röst!', icon: 'dnicon-check',color:'green'});
                    $("#conversation-vote-holder").html( result.html );

                    if(result.action == "vote-deleted") {
                        $('.vote-agree').find(".btn").removeClass("selected");
                        $('.vote-agree').find(".btn").removeClass("opacity");
                        $('.vote-disagree').find(".btn").removeClass("selected");
                        $('.vote-disagree').find(".btn").removeClass("opacity");
                    } else if(result.vote == 1) {
                        $('.vote-agree').find(".btn").addClass("selected");
                        $('.vote-agree').find(".btn").addClass("opacity");
                        $('.vote-disagree').find(".btn").removeClass("selected");
                        $('.vote-disagree').find(".btn").addClass("opacity");
                    } else if(result.vote == 0) {
                        $('.vote-disagree').find(".btn").addClass("selected");
                        $('.vote-agree').find(".btn").addClass("opacity");
                        $('.vote-agree').find(".btn").removeClass("selected");
                        $('.vote-disagree').find(".btn").addClass("opacity");
                    }

                    if(!result.loggedin)
                        set_post_cookie();
                }
            },
            error: function(result) {
                console.log("Error");
            },
            complete: function() {
                setTimeout(function() {
                    AJAX_RUNNING = false;
                }, SPAM_TIMER);

            },
        });

    }

    var share_this = function(e) {
        e.preventDefault ? e.preventDefault() : e.returnValue = false;


        // Vars
        var $btn = $(this);
        var post_id = $btn.attr("data-id");
        var source = $btn.attr("data-source");
        var nonce = $btn.attr("data-nonce");

        // Dialog
        var winWidth = 520;
        var winHeight = 400;
        var winTop = (screen.height / 2) - (winHeight / 2);
        var winLeft = (screen.width / 2) - (winWidth / 2);
        var url = $btn.attr('href');

        window.open(url, 'sharer', 'top=' + winTop + ',left=' + winLeft + ',toolbar=0,status=0,width=' + winWidth + ',height=' + winHeight);

        // Prevent Ajax call if spaming share!
        if(AJAX_RUNNING) {
            return;
        }
        AJAX_RUNNING = true;
        $.ajax({
            type : "post",
            dataType : "json",
            url : wp.ajaxurl,
            data : {
                action: "share_this",
                nonce: nonce,
                source: source,
                post_id: post_id
            },
            complete: function() {
                setTimeout(function() {
                    AJAX_RUNNING = false;
                }, 5000);

            }
        });
        return false;
    }

    var postCookieKey = 'xoss_p_simple';
    var replyCookieKey = 'xoss_r_simple';

    var post_cookie_exists = function() {

        return docCookies.hasItem(postCookieKey) ? 1 : 0;
        
    }

    var reply_cookie_exists = function() {

        return docCookies.hasItem(replyCookieKey) ? 1 : 0;
        
    }

    var set_post_cookie = function() {

        set_cookie(postCookieKey);

    }

    var set_reply_cookie = function() {

        set_cookie(replyCookieKey);

    }

    // Save cookie to the local path (current page)
    var set_cookie = function(cookieKey) {

        var date = new Date();
        date.setTime(date.getTime() + (60*60*24*14))
        docCookies.setItem(cookieKey, 1, date);

    }

    return {
        init: function() {
            $(document).on('click', '.js--share_this', share_this);
            $(document).on('click', '.js--remove_field--person', delete_field_person);
            $(document).on('click', '.js--add_reply_vote', add_reply_vote);
            $(document).on('click', '.js--follow_post', follow_post);
            $(document).on('click', '.js--add_suggestion', add_suggestion);
            $(document).on('click', '.js--toggle_information_box', toggle_information_box);
            $(document).on('click', '.js--toggle_add_suggestion_form', toggle_add_suggestion_form);
            $(document).on('click', '.js--agree_question', add_conversation_vote);
            $(document).on('click', '.js--add_conversation_vote', add_conversation_vote);
            $(document).on('click', '.js--anchor_link', anchor_link);
        }
    }

})(jQuery, WordpressGlobalVariables, utils, alerts);
applications.push(conversation);


var submit_page = (function($,wp,utils) {

    var resizeBody = function() {
        $('.wizard .content').animate({ height: ($('.body.current').outerHeight() + 70) }, "fast");
    }

    var add_new_field_person = function (e) {
        e.preventDefault ? e.preventDefault() : e.returnValue = false;

        var template = $('#tmp--add_new_person_field').html();
        $('.repeatable_fields').append(template);

        resizeBody();
    }

    var readImage = function(input,callback) {
        if ( input.files && input.files[0] ) {
            var FR= new FileReader();
            FR.onload = function(e) {
                callback(e.target.result);
            };
            FR.readAsDataURL( input.files[0] );
        }
    }

    return {
        init: function() {

            $(document).on('click', '.js--add_new_person_field', add_new_field_person);


            // $(document).on('change','#conversation-image',function(){
            //     readImage( this, function(base64image) {
            //         $("#conversation-image-element").attr("src", base64image);
            //     });
            // });
            $(document).on('change','#user-avatar',function(){
                readImage( this, function(base64image) {
                    $("#user-avatar-element").attr("src", base64image);
                });
            });

            $("#submit-conversation-form").steps({
                headerTag: "h2",
                bodyTag: "div.form",
                transitionEffect: "slideLeft",
                autoFocus: true,
                labels: {
                    cancel: "Avbryt",
                    current: '',
                    pagination: "Pagination",
                    finish: "Skicka mitt inlägg",
                    next: "Nästa",
                    previous: "Föregående",
                    loading: "Laddar ..."
                },
                onInit: function() {
                    setTimeout(function() {
                        resizeBody();
                    },500);
                    dataLayer.push({event: 'customEvent',eventCategory:'Submitpage',eventAction:'Init form',eventLabel: $(this).find('.form.current').prev("h2").text()});
                },
                onStepChanging: function (event, currentIndex, newIndex) {
                    var $current_block = $(this).find('.form.current');
                    var $form_controls = $current_block.find("input[type='text'],input[type='checkbox'],input[type='radio'],input[type='password'],input[type='file'],select,textarea");
                    var required = [];

                    // Going back
                    if(currentIndex > newIndex) return true;

                    // CurrentIndex starts with 0
                    if((currentIndex + 1) == 1) {
                        $("#thecontent").val(tinymce.editors.thecontent.getContent());
                    }
                    if((currentIndex + 1) == 1) {
                        $("#thecontent").val(tinymce.editors.thecontent.getContent());
                    }

                    $form_controls.each(function() {
                        var $el = $(this);
                        if($el.attr("required")) {
                            required.push($el);
                        }
                    });

                    if( ! utils.validateInputs( required ) ) {
                        return false;
                    }

                    return true;
                },
                onStepChanged: function (event, currentIndex, priorIndex) {
                    var $current_block = $(this).find('.form.current');

                    dataLayer.push({event: 'customEvent',eventCategory:'Submitpage',eventAction:'Change tab',eventLabel: $current_block.prev("h2").text()});

                    if($current_block.hasClass("preview")) {
                        // Title
                        var preview_title = $("#conversation-title").val();
                        $("#preview .preview-title").text( preview_title );

                        // Content
                        var preview_content = tinymce.editors.thecontent.getContent();
                        $("#preview .preview-content").html( tinymce.editors.thecontent.getContent() );

                        // Summary
                        var preview_summary = $("#conversation-summary").val();
                        $("#preview .preview-summary").text( preview_summary );

                        // Author Full Name
                        var preview_author_name = $("#user-name").text();
                        $("#preview .preview-author-name").text( preview_author_name );

                        // Author Bio
                        var preview_author_bio = $("#user-bio").val();
                        $("#preview .preview-author-bio").text( preview_author_bio );

                        // Avatar Image
                        var preview_author_avatar = $("#user-avatar-element").attr("src");
                        $("#preview .preview-author-avatar").css( "background-image", "url('"+preview_author_avatar+"')" );

                        // Display persons
                        var output = '';
                        $(".repeatable_field").each(function() {

                            var name = $(this).find('input[name="suggestions[name][]"]').val();
                            var contact = $(this).find('input[name="suggestions[contact][]"]').val();
                            var motivation = $(this).find('textarea[name="suggestions[motivation][]"]').val();

                            var $template = $("#tmp--person_vote_preview").clone();

                            var $html = $( $template.html() );

                            if( name !== '' || contact !== '' || motivation !== '' ) {
                                $html.find("span").eq(0).html(name);
                                $html.find("span").eq(1).html(contact);
                                $html.find("span").eq(2).html(motivation);
                                output += $html.clone().wrap('<div>').parent().html();
                            }

                        });
                        $("#preview .preview-person-list ul.list").html( output );

                    }

                    resizeBody();
                    return true;
                },
                onFinishing: function (event, currentIndex) {
                    return true;
                },
                onFinished: function (event, currentIndex) {
                    if($('input#accept-tos').is(':checked')) {
                        dataLayer.push({event: 'customEvent',eventCategory:'Submitpage',eventAction:'Submit Form'});
                        $(this).submit();
                     } else {
                        alerts.add({id: 'forgot-accept-tos',text:'Du måste acceptera användarvilkoren för att kunna fortsätta.', icon: 'fa-warning',color:'red'});
                     }
                }
            });
        }
    }

})(jQuery,WordpressGlobalVariables, utils);
applications.push(submit_page);

var sidebarHeight = (function($,wp) {

    var $sidebar = $("body.page").find("aside.sidebar");
    var $main = $("body.page").find("main"); //search

    var set_height = function() {

        var sidebar_height = $sidebar.height();
        var main_height = $main.height();

        if ( $main.length == 0 ) {
            return;
        }
        if ( ResponsiveBootstrapToolkit.is('xs') ) {
            $sidebar.css("height", "auto");
            $main.css("height", "auto");
            return;
        }

        if (sidebar_height > main_height) {
            $main.css("min-height",sidebar_height);
        }
        else {
            $sidebar.css("min-height",main_height);
        }
    }

    return {
        init: function() {
            set_height();
            $(window).resize(set_height);
        }
    }
})(jQuery,WordpressGlobalVariables);
applications.push(sidebarHeight);


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