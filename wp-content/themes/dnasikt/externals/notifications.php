<?php
	/**
     * Add mail to queue (Database)
     */
    function add_notification( $args ) {
        global $wpdb;

        $defaults = array(
            'user_id' => get_current_user_id(),
            'subject' => '',
            'body' => '',
            'permalink' => '',
        );

        $args = wp_parse_args( $args, $defaults );

        $wpdb->insert(
            'notifications',
            array(
                'user_id' => $args['user_id'],
                'subject' => $args['subject'],
                'body' => $args['body'],
                'permalink' => $args['permalink'],
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
            )
        );
    }

    /**
     * Send SwiftMail
     */
    function send_swift_mail( $args ) {

        $to = isset( $args['to'] ) ? $args['to'] : '' ;
        $subject = isset( $args['subject'] ) ? $args['subject'] : '' ;
        $body = isset( $args['body'] ) ? $args['body'] : '' ;
        $permalink = isset( $args['permalink'] ) ? $args['permalink'] : '' ;
        $display_name = isset( $args['display_name'] ) ? $args['display_name'] : '' ;

        // Create the Transport
        if(class_exists('Swift_SmtpTransport') && class_exists('Swift_Mailer') && class_exists('Swift_Message')) {
        	try {
				$transport = Swift_SmtpTransport::newInstance( if_empty( get_option( 'smtp_host' ), '' ), if_empty( get_option( 'smtp_port' ), 465 ), if_empty( get_option( 'smtp_ssl' ), "ssl" ) )
	                ->setUsername( if_empty( get_option( 'smtp_user' ), '' ) )
	                ->setPassword( if_empty( get_option( 'smtp_pass' ), '' ) );

	            // Create the Mailer using your created Transport
	            $mailer = Swift_Mailer::newInstance($transport);

	            // Create a message
	            $message = Swift_Message::newInstance( $subject )
	                ->setFrom(array( if_empty( get_option( 'mail_from' ), 'no-reply@dn.se' ) => if_empty( get_option( 'mail_from_name' ), 'DN.SE' ) ) )
	                ->setTo( $to )
                    ->setBody( 'Hej '.$display_name.'!\\n\n'. $body .'\\n\n '. $permalink.'\\n\\nVänliga hälsningar\\nÅsiktsredaktionen', 'text/plain');
                $message->addPart('<p><strong>Hej '.$display_name.'!</strong></p><p>'.$body.'</p><p>'.$permalink.'</p><p>Vänliga hälsningar<br/>Åsiktsredaktionen</p>', 'text/html');
                
	            // Send the message
	            $result = $mailer->send($message);
	            return true;
        	} catch ( Swift_TransportException $e ) {
                logthis( "SwiftMailer Failed \n\rMessage: " . $e->getMessage() . "\n\rTo:" . array_keys($to)[0] );
        		return false;
        	}

        }
    }

	/**
	 * Create a new time interval
	 */
    add_filter( 'cron_schedules', function ( $schedules ) {
      $schedules['minute'] = array(
        'interval' => 60,
        'display' => __( 'Every min', 'text-domain' )
      );
      return $schedules;
    });

    /**
     * Run the cron
     */
    if ( ! wp_next_scheduled( 'send_notifications' ) ) {
        wp_schedule_event( time(), 'minute', 'send_notifications' );
    }

    /**
     * Cron Action
     */
    function send_notifications_function() {
		global $wpdb;

    	$notification_table = 'notifications';

    	$sql = "SELECT * FROM $notification_table n
				JOIN wp_users u ON (u.ID = n.user_id)
				WHERE n.is_sent = '0' AND failed < '3'";

    	$notifications = $wpdb->get_results( $sql );
    	$message = "= WP_CRON =========\r\nTime: " . current_time( 'mysql', true ) . "\r\n";
    	foreach( $notifications as $notification ) {
    		// Skip if user dont have any mail
    		if( '' == $notification->user_email ) continue;
            if( get_user_meta( $notification->user_id, 'no_email_notifications', true ) ) continue;

    		if( send_swift_mail( array(
                                    'to' => array( $notification->user_email => $notification->display_name ),
                                    'subject' => $notification->subject,
                                    'body' => $notification->body,
                                    'permalink' => $notification->permalink,
                                    'display_name' => $notification->display_name ) ) ) {
				$wpdb->update(
					$notification_table,
					array(
						'is_sent' => '1',
						'updated' => current_time( 'mysql', true ),
					),
					array( 'id' => $notification->id ),
					array( '%s', '%s' ),
					array( '%d' )
				);
				$message .= " - OK - Till " . $notification->display_name . " <" . $notification->user_email . "> \r\n";
    		} else {
				$wpdb->update(
					$notification_table,
					array(
						'failed' => ( $notification->failed + 1 ),
						'updated' => current_time( 'mysql', true ),
					),
					array( 'id' => $notification->id ),
					array( '%d', '%s' ),
					array( '%d' )
				);
				$message .= " - ERROR - Till " . $notification->display_name . " <" . $notification->user_email . "> \r\n";
    		}
    	}
    	$message .= "=================\r\n";

    	// logthis("= WP_CRON =========\r\nTime: " . current_time( 'mysql', true ) . "\r\n===============\r\n\r\n" );
    }
    add_action( 'send_notifications', 'send_notifications_function' );


    /**
     * ====================================================== *
     * ALL NOTIFICATION RELATED FUNCTION GOES BELOW THIS LINE *
     * ====================================================== *
     */

    /**
     * Notify followers when a post is created or updated
     */
    function notify_user_when_new_post_or_post_updated( $post_id, $post, $update ) {

    	 if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        // Do some checks
        if( 'asikt' != $post->post_type ) {
            return;
        }

        if( 'publish' != $post->post_status ) {
            return;
        }

        if( '' == $post->post_content ){
            return;
        }

        if( '0' == $post->post_parent ){
            $the_ID = $post->ID;
        } else {
            $the_ID = $post->post_parent;
        }

        $followers = get_post_meta( $the_ID, '_post_followers', false );
        $subject = get_post_meta( $the_ID, '_conversation_subject', true );

        $message = "= EMAIL NOTIS =========================\r\n";
        $message .= "Tid: " . date( "F jS Y, H:i", time() ) . "\r\n";
        $message .= "Ämne: " . $subject . "\r\n";
        $message .= "Rubrik: " . $post->post_title . "\r\n";
        $message .= "PostID: " . $post->ID . "\r\n";
        if(count($followers) == 0) {
            $message .= " - Inlägget har inga följare";
        }
        foreach($followers as $follower) {
            $user = get_userdata( $follower );
            add_notification( array( 'user_id' => $follower, 'subject' => $subject, 'body' => 'Debatten du följer har blivit uppdaterad.', 'permalink' => get_permalink( $post->ID ) ) );
            $message .= ($user->user_email) ? " - " . $user->user_email . "\r\n" : " - Personen hade ingen mail... \r\n" ;
        }
        $message .= "\r\n";
        $message .= "=======================================\r\n\r\n";
        logthis($message);
    }
    add_action( 'save_post', 'notify_user_when_new_post_or_post_updated', 10, 3  );

    function notify_author_on_publish( $new_status, $old_status, $post ) {
    	if( 'asikt' != $post->post_type ) {
            return;
        }
		if( 'publish' != $post->post_status ) {
            return;
        }
        if( '' == $post->post_content ){
            return;
        }
        if( ( 'draft' == $old_status || 'auto-draft' === $old_status ) && $new_status === 'publish' ) {
        	$user = get_userdata( $post->post_author )->data;
        	add_notification( array( 'user_id' => $post->post_author, 'subject' => 'Ditt inlägg har blivit publicerat', 'body' => 'Ditt inlägg är nu granskat och godkänt.', 'permalink' => get_permalink( $post->ID ) ) );
        }
    }
    add_action('transition_post_status', 'notify_author_on_publish', 10, 3);
?>
