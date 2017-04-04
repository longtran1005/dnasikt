<?php
	/**
	 * Ajax Calls
	 */
	function cb_lwst_clear_notifications(){
		global $wpdb;

		if( ! is_user_logged_in() ) {
			echo wp_json_encode( array ('status' => 'error', 'message' => 'Du måste vara inloggad.'));
			die();
			exit;
		}

		$wpdb->update(
			'notifications',
			array( 'is_read' => '1' ),
			array( 'user_id' => get_current_user_id() ),
			array( '%s' ),
			array( '%d' )
		);

	    echo wp_json_encode(array('OK'));

	    exit();
	}
	add_action('wp_ajax_clear_notifications', 'cb_lwst_clear_notifications');
	add_action('wp_ajax_nopriv_clear_notifications', 'cb_lwst_clear_notifications');


	/**
	 * Share This
	 */
	function cb_lwst_share_this(){
		global $wpdb;

		check_ajax_referer( 'share-this', 'nonce' );

		$post_id = $_POST['post_id'];
		$source = $_POST['source'];

		if( $source == 'facebook' || $source == 'twitter' ) {
			$votes = get_post_meta( $post_id, '_'.$source.'_share', true );
			if( $votes == '' ) $votes = 0;
			update_post_meta( $post_id, '_'.$source.'_share', ($votes+1), $votes );
			echo wp_json_encode(array('status' => 'ok'));
			die();
			exit();
		}

		echo wp_json_encode(array('status' => 'error'));
		die();
		exit();
	}
	add_action('wp_ajax_share_this', 'cb_lwst_share_this');
	add_action('wp_ajax_nopriv_share_this', 'cb_lwst_share_this');


	// Create new user from edit.php
	function cb_lwst_create_new_user(){

		$post_id 	= $_POST['post_id'];
		$fullname 	= $_POST['name'];
		$username 	= sanitize_title( $fullname );
		$email 		= $username . '-' . rand(1000,9999) . '@dnasikt.se';

		if (filter_var($_POST['contact'], FILTER_VALIDATE_EMAIL)) {
			$email = $_POST['contact'];
		}

		if(trim($username) == '') {
			echo wp_json_encode( array (
					'status' => 'error',
					'message' => 'Empty username...'
				)
			);
			exit;
		}

		if(username_exists( $username )) {
			echo wp_json_encode( array (
					'status' => 'error',
					'message' => 'User already exits!'
				)
			);
			exit;
		}

		if(strpos($fullname, ' ') !== false) {
			$first_name 	= explode(" ", $fullname, 2)[0];
			$last_name 	= explode(" ", $fullname, 2)[1];
		}


		$random_password = wp_generate_password( $length=12, $include_standard_special_chars=true );

		$userdata = array(
		    'user_login'	=> $username,
		    'user_pass'		=> $random_password,
		    'first_name'	=> (isset($first_name)) ? $first_name : $fullname ,
		    'last_name'		=> (isset($last_name)) ? $last_name : '' ,
		    'display_name'	=> $fullname,
		    'user_email' 	=> $email,
		);

		$user_id = wp_insert_user( $userdata ) ;

		//On success
		if( !is_wp_error($user_id) ) {
			echo wp_json_encode( array (
					'status' => '200',
					'user_id' => $user_id,
					'name' => $fullname
				)
			);
		}


	    exit();
	}
	add_action('wp_ajax_create_new_wp_user', 'cb_lwst_create_new_user');
	add_action('wp_ajax_nopriv_create_new_wp_user', 'cb_lwst_create_new_user');

	// Create new conversation from edit.php
	function cb_lwst_create_new_conversation(){
		global $wpdb;

		$title 		= $_POST['title'];
		$post_id 	= $_POST['post_id'];
		$user_id 	= $_POST['user_id'];
		$email 		= $_POST['email'];
		$subject 	= $_POST['subject'];
		$token 		= md5(uniqid(rand(), true));
		$password 	= random_password(5);

		// 1. Create draft conversation
		$new_draft_conversation = array(
		  'post_title'   	=> $title,
		  'post_type' 		=> 'asikt',
		  'post_content' 	=> '',
		  'post_status'  	=> 'draft',
		  'post_author'  	=> $user_id,
		  'post_parent'		=> $post_id
		);
		$conversation_id = wp_insert_post( $new_draft_conversation );

		// Generate friendly password
		$user_object = get_userdata( $user_id );
		$password = friendly_password( $user_object->first_name, $user_object->last_name );

		// 2. Add Token as a meta!
		add_post_meta($conversation_id,'_conversation_token', $token);
		add_post_meta($conversation_id,'_conversation_password', md5($password));

		$link = get_custom_page( array( 'cid' => $conversation_id, 'token' =>$token ) );

		$message = <<<EOT
<p>Välkommen att skriva ditt svar på "<strong>$subject</strong>".</p>

<p>Nedan hittar du den länk och det lösenord du behöver för att skicka in ditt svar.</p>

<p><strong>URL:</strong> $link<br>
<strong>Lösenord:</strong> $password</p>

<p>En redaktör kommer att granska ditt svar innan det publiceras på sajten (<a href="http://asikt.dn.se/anvandarvillkor">här</a> kan du läsa vår innehållspolicy). Du kan alltid kontakta redaktionen direkt på asiktsredaktionen@dn.se om du har några frågor, synpunkter eller behöver hjälp.</p>

<p><strong>Vänliga hälsningar</strong>,<br>
Åsiktsredaktionen på Dagens Nyheter</p>
EOT;

		$headers[] = 'From: ' . if_empty( get_option( 'dn_site_name' ), 'DN.Åsikt' ) . ' <' . if_empty( get_option( 'smtp_user' ), 'asiktsredaktionen@dn.se' ) . '>';
		$headers[] = 'Content-Type: text/html; charset=UTF-8';

		wp_mail( $email, 'Skriv ditt svar på DN.Åsikt', $message, $headers );

		// Return
		echo wp_json_encode( array ('url' => $link, 'password' => $password ));
	    exit();
	}
	add_action('wp_ajax_create_new_conversation', 'cb_lwst_create_new_conversation');
	add_action('wp_ajax_nopriv_create_new_conversation', 'cb_lwst_create_new_conversation');


	// Create new conversation from edit.php
	function cb_lwst_create_new_conversation_frontend(){
		echo json_encode($_POST);
		exit;
		die();
	}
	add_action('wp_ajax_create_new_conversation_frontend', 'cb_lwst_create_new_conversation_frontend');
	add_action('wp_ajax_nopriv_create_new_conversation_frontend', 'cb_lwst_create_new_conversation_frontend');


	// Add vote to repliers
	function cb_lwst_add_vote(){

		// if(!is_user_logged_in()) {
		// 	echo wp_json_encode( array ('status' => 'error', 'message' => 'Du måste vara inloggad för att kunna rösta.'));
		// 	exit;
		// 	die();
		// }

		$logged_in = is_user_logged_in();

		$reply_id 	= $_POST['reply_id'];
		$prev_reply_id 	= ($_POST['prev_reply_id'] != 0) ? $_POST['prev_reply_id'] : $reply_id  ;

		$already_voted = (bool) $_POST['already_voted'];
		$vote_denied = false;

		$deleted = false;

		if($logged_in) {
			// Delete old votes
			$deleted = remove_reply_vote($prev_reply_id);

			if(($reply_id != $prev_reply_id ) OR !$deleted)
				add_reply_vote($reply_id);
		}
		// If user is not authenticated, and has not voted previously, we let them through
		else if(!$already_voted) {
			$vote_id = add_reply_vote($reply_id);
		}
		else {
			$vote_denied = true;
		}

		$current = get_reply_votes($reply_id);
		$prev = ( $prev_reply_id != $reply_id ) ? get_reply_votes($prev_reply_id) : null ;

		if($vote_denied) {
			echo wp_json_encode(array('status' => 'error', 'message' => 'Du kan bara rösta en gång.', 'loggedin' => $logged_in));
		}
		else {
			echo wp_json_encode( array ('status' => 'ok', 'current' => $current, 'prev' => $prev, 'new' =>  !$deleted));
		}
	    exit();
	}
	add_action('wp_ajax_add_vote', 'cb_lwst_add_vote');
	add_action('wp_ajax_nopriv_add_vote', 'cb_lwst_add_vote');

	// Frontpage - Get More Conversations
	function cb_lwst_get_more_posts(){

		// Default
	    $args = array(
			'post_type'		=> esc_html( $_POST['post_type'] ),
			'post_status'	=> 'publish',
			'post_parent' 	=> 0,
			'posts_per_page'=> 5,
	    	'offset' 		=> esc_html( $_POST['offset'] ),
	    );

	    $posts = new WP_Query($args);
	    $post_count = $posts->found_posts;

	    $html = '';
	    if ( $posts->have_posts() ){
	         while ( $posts->have_posts() ) {
	            global $post;
	            $posts->the_post();
	            $html .= load_template_part( 'templates/conversation', 'loop-content' );
	        }
	    }
	    echo wp_json_encode(array('count' => $post_count, 'html' => $html, 'args' => $args));
	    wp_reset_postdata();
	    die();
	    exit();
	}
	add_action('wp_ajax_get_more_posts', 'cb_lwst_get_more_posts');
	add_action('wp_ajax_nopriv_get_more_posts', 'cb_lwst_get_more_posts');

	function cb_lwst_add_suggestion(){

		check_ajax_referer( 'add_suggestion_nonce', 'nonce' );

		if( ! is_user_logged_in() ) {
			echo wp_json_encode(array('status' => 'error', 'message' => 'Du måste vara inloggad för att kunna rösta.'));
		    die();
		    exit();
		}

		// Validate
		$errors = validate_post_request(array(
			'name' => 'Titel måste vara ifyllt',
			'motivation' => 'Titel måste vara ifyllt',
			)
		);

		if($errors) {
			echo wp_json_encode(array('status' => 'error', 'errors' => $errors));
			die();
	    	exit();
		}

        $name = $_POST['name'];
        $contact = $_POST['contact'];
        $motivation = $_POST['motivation'];
        $post_title = get_the_title( $_POST['post_id'] );
		
		add_suggestion( $_POST['post_id'], array( 'name' => $name, 'contact' => $contact, 'motivation' => $motivation ) );

		echo wp_json_encode(array('status' => 'ok', 'message' => 'Tack för ditt förslag!'));
        
        $message = "Ett nytt personförslag har kommit in till debatten ".$post_title.".\n\nNamn: ".$name."\nKontaktinformation: ".$contact."\nMotivering: ".$motivation;
		$email = if_empty( get_option('notification_email'), get_option( 'admin_email' ) );
		wp_mail( $email, 'Notis: Nytt personförslag till debatten "'.$post_title . '"', $message );

	    die();
	    exit();
	}
	add_action('wp_ajax_add_suggestion', 'cb_lwst_add_suggestion');
	add_action('wp_ajax_nopriv_add_suggestion', 'cb_lwst_add_suggestion');

	// Add vote to post
	function cb_lwst_add_conversation_vote(){

		check_ajax_referer( 'add_conversation_vote_nonce', 'nonce' );

		// if( ! is_user_logged_in() ) {
		// 	echo wp_json_encode(array('status' => 'error', 'message' => 'Du måste vara inloggad för att kunna rösta.'));
		//     die();
		//     exit();
		// }

		$logged_in = is_user_logged_in();

		$post_id = absint ( intval( $_POST['post_id'] ) );
		$vote = absint ( intval( $_POST['vote'] ) );
		$already_voted = (bool) $_POST['already_voted'];
		$vote_id = 1;
		$vote_denied = false;
		$action = "vote-deleted";

		$prev_vote = null;

		// Remove old votes
		if( $logged_in ) {
			$prev_vote = get_conversation_vote( $post_id );
			remove_conversation_vote( $post_id );
		}

		// If user is authenticated, backend will handle multiple voting
		if( $logged_in) {
			// Add new vote if old vote != new vote
			if($prev_vote == null) {
				$vote_id = add_conversation_vote($post_id,$vote);
				$action = "vote-added";
			} else {
				if($prev_vote->vote != $vote) {
					$vote_id = add_conversation_vote($post_id,$vote);
					$action = "vote-added";
				}
			}
		}
		// If user is not authenticated, and has not voted previously, we let them through
		else if(!$already_voted) {
			$vote_id = add_conversation_vote($post_id,$vote);
			$action = "vote-added";
		}
		else {
			$vote_denied = true;
		}

		if($vote_denied) {
			echo wp_json_encode(array('status' => 'error', 'message' => 'Du kan bara rösta en gång.', 'loggedin' => $logged_in));
		}
		else if($vote_id != 0) {
			echo wp_json_encode(array('status' => 'ok', 'html' => get_conversation_votes_html($post_id), 'action' => $action, 'vote' => $vote, 'message' => 'Tack för din röst!', 'loggedin' => $logged_in));
		} else {
			echo wp_json_encode(array('status' => 'error', 'message' => 'Något gick fel..', 'loggedin' => $logged_in));
		}


	    die();
	    exit();
	}
	add_action('wp_ajax_add_conversation_vote', 'cb_lwst_add_conversation_vote');
	add_action('wp_ajax_nopriv_add_conversation_vote', 'cb_lwst_add_conversation_vote');

	function cb_lwst_follow_post(){

		check_ajax_referer( 'follow_post_nonce', 'nonce' );

		if( ! is_user_logged_in() ) {
			echo wp_json_encode(array('status' => 'error', 'message' => 'Du måste vara inloggad för att kunna följa debatten.'));
		    die();
		    exit();
		}

		list( $exits, $post_id ) = is_following_post( $_POST['post_id'] );

		if( $exits ) {
			delete_post_meta( $post_id, '_post_followers', get_current_user_id() );
			echo wp_json_encode(array('status' => 'ok', 'follow' => false, 'message' => 'Du följer inte denna debatt längre'));
		    die();
		    exit();
		} else {
			add_post_meta( $post_id, '_post_followers', get_current_user_id() );

			echo wp_json_encode(array('status' => 'ok', 'follow' => true, 'message' => 'Du kommer nu få notifieringar när något händer i denna debatt'));

		    die();
		    exit();
		}


		echo wp_json_encode(array('status' => 'error', 'message' => 'Hmm, något gick fel..'));
	    die();
	    exit();
	}
	add_action('wp_ajax_follow_post', 'cb_lwst_follow_post');
	add_action('wp_ajax_nopriv_follow_post', 'cb_lwst_follow_post');



?>