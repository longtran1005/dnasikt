<?php
	/**
	 * Debug
	 */
	function debug($pre) {
		echo "<pre>";
		print_r($pre);
		echo "</pre>";
	}
	/**
	 * Find root path
	 */
	function find_wp_root_path() {
		return ABSPATH;
	}
	/**
	 * Logger
	 */
	function logthis( $message ) {
		$file = find_wp_root_path() . '/logfile.log';
		$open = fopen( $file, "a" );
		$write = fputs( $open, $message );
		fclose( $open );
	}
	/**
	 * Check if vars is Int
	 */
	function isint($input){
    	return(ctype_digit(strval($input)));
	}

	/**
	 * Current URL
	 */
	function wp_current_url( $fullurl = true ) {
		global $wp;
		if( $fullurl )
			return 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
		else
			return $_SERVER['REQUEST_URI'];
	}

	/**
	 * Validate $_POST request
	 */
	function validate_post_request($required=array()) {
		foreach($_POST as $key => $value) {
			if(array_key_exists($key, $required)) {
				if(trim($value) === '')
					$errors[] = array( 'name' => $key, 'message' => $required[$key] );
			}
		}
		if(isset($errors)) return $errors;
		else return null;
	}

	/**
	 * Get the theme root path
	 * Ex /wp-content/themes/[THEMENAME]
	 */
	function theme_root() {
	    return bloginfo('stylesheet_directory');
	}

	/**
	 * Same as get_template_part
	 * Except this returns the value instead of printing it out
	 * Ex. $html = load_template_part( 'loop' )
	 */
	function load_template_part($template_name, $part_name=null) {
	    ob_start();
	    get_template_part($template_name, $part_name);
	    $var = ob_get_contents();
	    ob_end_clean();
	    return $var;
	}
	/**
	 * Generate easy-to-remember password
	 */
	function random_password($len = 8){
		if(($len%2)!==0){ // Length paramenter must be a multiple of 2
			$len=8;
		}
		$length=$len-2; // Makes room for the two-digit number on the end
		$conso=array('b','c','d','f','g','h','j','k','l','m','n','p','r','s','t','v','w','x','y','z');
		$vocal=array('a','e','i','o','u');
		$password='';
		srand ((double)microtime()*1000000);
		$max = $length/2;
		for($i=1; $i<=$max; $i++){
			$password.=$conso[rand(0,19)];
			$password.=$vocal[rand(0,4)];
		}
		$password.=rand(10,99);
		$newpass = $password;
		return $newpass;
	}
	/**
	 * Friendly password
	 */
	function friendly_password( $first, $last ) {
		$conso = array('b','c','d','f','g','h','j','k','l','m','n','p','r','s','t','v','w','x','y','z');
		$vocal=array('a','e','i','o','u');
		$replacement = $conso[rand(0,19)] . $vocal[rand(0,4)];

		$f = (strlen($first) > 2) ? substr(sanitize_title($first),0,2) : $replacement ;
		$l = (strlen($last) > 2) ? substr(sanitize_title($last),0,2) : $replacement ;

		return strtolower( $f . $l ) . rand(100,999);
	}
	/**
	 * Get User Meta
	 */
	function user_meta($user_id, $key, $default = '') {
		$meta = get_user_meta( $user_id, $key, true );
		return if_empty($meta, $default);
	}

	/**
	 * Print if has value, otherwise print empty string
	 */
	function if_empty($value, $default = '') {

			if(trim($value) == '') {
				return $default;
			} else {
				return $value;
			}

	}

	/**
	 * Get Thumbnail From Post Id
	 */
	function get_thumb($post_id, $size = 'medium') {
		return wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), $size )[0];
	}

	/**
	 * Get Conversation Subject from Meta data
	 */
	function get_subject( $post_id, $is_parent = true ) {

		if( $is_parent == false ) {
			$post = get_post( $post_id );
			$the_id = ( $post->post_parent != '0' ) ? $post->post_parent : $post->ID ;
		} else {
			$the_id = $post_id;
		}

		return if_empty( get_post_meta( $the_id, '_conversation_subject', true ), 'Saknar ämne...' );
	}

	/**
	 * Get Conversation Subject from Meta data
	 */
	function get_if_parent( $post_id ) {

		$post_obj = get_post( $post_id );

		if( $post_obj->post_parent != '0' ) {
			return get_post( $post_obj->post_parent );
		}

		return false;
	}

	/**
	 * Get Votes
	 */
	function get_reply_votes($reply_id) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare("select count(*) from replies_votes where reply_id = %d", $reply_id ) );
	}
	/**
	 * Add Vote
	 */
	function add_reply_vote($reply_id, $user_id = 0) {
		global $wpdb;

		if($user_id == 0) $user_id = get_current_user_id();

		$wpdb->insert(
			'replies_votes',
			array(
				'user_id' => $user_id,
				'reply_id' => $reply_id
			),
			array(
				'%d',
				'%d'
			)
		);

		return $wpdb->rows_affected;
	}
	/**
	 * Remove Vote
	 */
	function remove_reply_vote($reply_id, $user_id = 0) {
		global $wpdb;

		if($user_id == 0) $user_id = get_current_user_id();

		// It is critical that the user is logged in so that we don't erase all anonymous votes
		if($user_id == 0) return;

		$sql = $wpdb->prepare(
			"DELETE FROM replies_votes WHERE reply_id = %d AND user_id = %d",
			array( $reply_id, $user_id )
			);
		$wpdb->query( $sql );

		return $wpdb->rows_affected;
	}
	/**
	 * Vote Exists?
	 */
	function get_reply_vote($reply_id, $user_id = 0) {
		global $wpdb;

		if($user_id == 0) $user_id = get_current_user_id();

		// Since we can't differentiate between unauthenticated users' votes, return
		if($user_id == 0) return;

		$sql = $wpdb->prepare(
			"SELECT * FROM replies_votes WHERE reply_id = %d AND user_id = %d",
			array( $reply_id, $user_id )
			);
		return $wpdb->get_col( $sql );
	}

	/**
	 * Get Votes For Conversations
	 */
	function get_conversation_votes($conversation_id,$vote_type) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare("select count(*) from conversation_votes where conversation_id = %d AND vote = %s ", $conversation_id, $vote_type ) );
	}
	function get_conversation_votes_and_procent($conversation_id) {
		$up_votes = get_conversation_votes( $conversation_id, 1 );
		$down_votes = get_conversation_votes( $conversation_id, 0 );

		$total_votes = $up_votes + $down_votes;

		$up_votes_procent = ($up_votes != 0) ? round(($up_votes/$total_votes) * 100) : 0 ;
		$down_votes_procent =  ($down_votes != 0) ? round(($down_votes/$total_votes) * 100) : 0 ;

		return array(
			'thumb_up' => $up_votes,
			'thumb_up_procent' => $up_votes_procent,
			'thumb_down' => $down_votes,
			'thumb_down_procent' => $down_votes_procent,
			);
	}
	/**
	 * Add Vote For Conversations
	 */
	function add_conversation_vote($conversation_id, $vote_type, $user_id = 0) {
		global $wpdb;

		if($user_id == 0) $user_id = get_current_user_id();

		return $wpdb->insert(
			'conversation_votes',
			array(
				'user_id' => $user_id,
				'conversation_id' => $conversation_id,
				'vote' => $vote_type,
			),
			array(
				'%d',
				'%d',
				'%s'
			)
		);
	}

	/**
	 * Remove Conversation Vote
	 */
	function remove_conversation_vote($conversation_id, $user_id = 0) {
		global $wpdb;

		if($user_id == 0) $user_id = get_current_user_id();

		// It is critical that the user is logged in so that we don't erase all anonymous votes
		if($user_id == 0) return;

		$sql = $wpdb->prepare(
			"DELETE FROM conversation_votes WHERE conversation_id = %d AND user_id = %d",
			array( $conversation_id, $user_id )
			);
		return $wpdb->get_col( $sql );
	}

	/**
	 * Conversation Vote Exists?
	 */
	function get_conversation_vote($conversation_id, $user_id = 0) {
		global $wpdb;

		if($user_id == 0) $user_id = get_current_user_id();

		// Since we can't differentiate between unauthenticated users' votes, return
		if($user_id == 0) return;

		$sql = $wpdb->prepare(
			"SELECT vote FROM conversation_votes WHERE conversation_id = %d AND user_id = %d LIMIT 1",
			array( $conversation_id, $user_id )
			);
		return $wpdb->get_row( $sql );
	}
	/**
	 * Add Suggestion for Reply
	 */
	function add_suggestion( $post_id, $params ) {
		global $wpdb;

		extract( $params );

		$user_id = (isset($user_id)) ? $user_id :  get_current_user_id() ;
		$name = (isset($name)) ? $name : '' ;
		$contact = (isset($contact)) ? $contact : '' ;
		$motivation = (isset($motivation)) ? sanitize_text_field( $motivation ) : '' ;

		return $wpdb->insert(
			'reply_suggestions',
			array(
				'post_id' => $post_id,
				'user_id' => $user_id,
				'name' => $name,
				'contact' => $contact,
				'motivation' => $motivation,
			),
			array(
				'%d',
				'%d',
				'%s',
				'%s',
				'%s'
			)
		);
	}

	/**
	 * Get All Repliers For A Conversation
	 */
	function get_repliers($conversation_id) {
		global $wpdb;
		$sql = $wpdb->prepare(
			"SELECT r.id, r.user_id, r.motivation, u.display_name, r.visible, r.status, (SELECT count(*) FROM replies_votes rv WHERE rv.reply_id = r.id ) as votes
			FROM replies r JOIN wp_users u ON (r.user_id = u.ID)
			WHERE conversation_id = %d",
			array(
				$conversation_id
			)
		);
		return $wpdb->get_results( $sql );
	}

	/**
	 * Get The Excerpt
	 */
	function lwst_get_teaser( $post_id ) {
		global $post;
		$save_post = $post;
		$post = get_post($post_id);
		$output = get_the_excerpt();
		$post = $save_post;
		return $output;
	}

	/**
	 * Get Html for Conversation Vote Bar
	 */
	function get_conversation_votes_html( $post_id = null, $small = false, $replies = null ) {
		$up_votes = get_conversation_votes( $post_id, 1 );
		$down_votes = get_conversation_votes( $post_id, 0 );

		$total_votes = $up_votes + $down_votes;

		$up_votes_procent = ($up_votes != 0) ? round(($up_votes/$total_votes) * 100) : 0 ;
		$down_votes_procent =  ($down_votes != 0) ? round(($down_votes/$total_votes) * 100) : 0 ;

		if((($up_votes == 0 AND $down_votes == 0) || ($up_votes_procent == 0 AND $down_votes_procent == 0))) {
			$up_votes_procent = 50;
			$down_votes_procent = 50;
		}
		$small = ($small) ? 'small' : '' ;
		$html = '<div class="vote-bar-container '. $small .' ">
			<div class="vote-bar">
				<div class="positive" data-procent="' . ($total_votes > 0 ? $up_votes_procent : 0) . '" style="width:' . $up_votes_procent . '%;"></div>
				<div class="negative" data-procent="' . ($total_votes > 0 ? $down_votes_procent : 0) . '" style="width:' . $down_votes_procent . '%;"></div>
			</div>
		</div>'
		// <div class="vote-meta-container">
		// 	<span class="positive">' . $up_votes . ' röster</span>
		// 	<span class="negative pull-right">' . $down_votes . ' röster</span> if votes == 1 echo röst else echo röster
		// </div>
		;
        if($small) {
        	$html .= '<div class="vote-meta-container">
			<div class="sidebar-replies">Repliker: <span class="vote-meta">' . $replies . '</span></div>
			<div class="sidebar-votes">Röster: <span class="vote-meta">' . $total_votes . '</span></div>
		</div>
        ';
        }

		return $html;
	}
	function the_conversation_votes_html( $post_id = null, $small = false, $replies = null ) {
		echo get_conversation_votes_html( $post_id, $small, $replies );
	}

	function the_conversation_votes_control_html( $post_id = null ) {
		if($conversation_user_vote = get_conversation_vote( $post_id )) {
			$conversation_user_vote = $conversation_user_vote->vote;
		} else {
			$conversation_user_vote = -1;
		}
		?>
		<div class="vote-question-container">
			<?php if( $post_id !== null ): ?>
				<input type="hidden" id="add-conversation-vote-nonce" value="<?php echo wp_create_nonce( "add_conversation_vote_nonce" ); ?>">
			<?php endif; ?>
			
			<div class="vote-agree">
				<?php if( $post_id !== null ): ?>
					<button class="btn btn-dn js--add_conversation_vote <?php if($conversation_user_vote == 1) echo 'selected' ?> <?php if($conversation_user_vote >= 0) echo 'opacity' ?>" data-vote="1" data-id="<?php echo $post_id; ?>" onclick="dataLayer.push({event: 'customEvent',eventCategory:'Pagepoll',eventAction:'thumb-up',eventLabel:'ID: <?php echo $post_id; ?>'});"> Jag håller med</button>
				<?php else: ?>
					<div class="btn btn-dn opacity <?php if($conversation_user_vote == 1) echo 'selected' ?>" data-vote="1" data-id="<?php echo $post_id; ?>"> Jag håller med</div>
				<?php endif; ?>
			</div>
			<div class="vote-or">
				<span class="text">eller</span>
				<span class="border"></span>
			</div>
			<div class="vote-disagree">
				<?php if( $post_id !== null ): ?>
					<button class="btn btn-dn red js--add_conversation_vote <?php if($conversation_user_vote == 0) echo 'selected' ?> <?php if($conversation_user_vote >= 0) echo 'opacity' ?>" data-vote="0" data-id="<?php echo $post_id; ?>" onclick="dataLayer.push({event: 'customEvent',eventCategory:'Pagepoll',eventAction:'thumb-down',eventLabel:'ID: <?php echo $post_id; ?>'});">Jag håller inte med</button>
				<?php else: ?>
					<div class="btn btn-dn red opacity<?php if($conversation_user_vote == 0) echo 'selected' ?>" data-vote="0" data-id="<?php echo $post_id; ?>">Jag håller inte med</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Check if current user following a specific page
	 */
	function is_following_post( $post_id, $user_id = null ) {
		$post = get_post( $post_id );
		$the_id = ( '0' == $post->post_parent ) ? $post_id : $post->post_parent ;
		$user_id = ( $user_id == null ) ? get_current_user_id() : $user_id ;
		$user_ids = get_post_meta( $the_id, '_post_followers', false );

		if( in_array( $user_id, $user_ids ) ) {
			return array( true, $the_id );
		}
		return array( false, $the_id );

	}

	/**
	 * Get Submit page link
	 */
	function get_custom_page( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'template' => 'page-submit-conversation.php',
			'cid' => null,
			'token' => null,
			'pid' => null
		);

		$args = wp_parse_args( $args, $defaults );
		$page = $wpdb->get_row( $wpdb->prepare( "SELECT p.ID FROM $wpdb->postmeta m JOIN $wpdb->posts p ON (m.post_id = p.ID)  WHERE p.post_status = 'publish' AND m.meta_value = %s ORDER BY meta_id DESC", $args['template'] ) );
		$link = get_permalink( $page->ID );
		if($args['cid'] && $args['token']) {
			return $link . "?cid=".$args['cid']."&token=".$args['token'];
		}
		if($args['pid']) {
			return $link . "?pid=" . $args['pid'];
		}
		return $link;
	}

	/**
	 * Get Notifications for logged in user
	 */
	function get_notifications( $args = array() ) {
		global $wpdb;
		$defaults = array(
			'user_id' => get_current_user_id(),
			'limit' => 5,
			'orderby' => 'id',
			'order' => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		$order = $args['order'];
		$orderby = $args['orderby'];

		if( isset( $args['read'] ) ) {
			$sql = $wpdb->prepare(
				"
					SELECT * FROM notifications
					WHERE is_read = %s AND user_id = %s
					ORDER BY $orderby $order LIMIT %d
				",
				$args['read'],
				$args['user_id'],
				$args['limit']
			);
		} else {
			$sql = $wpdb->prepare(
				"
					SELECT * FROM notifications
					WHERE user_id = %s
					ORDER BY $orderby $order LIMIT %d
				",
				$args['user_id'],
				$args['limit']
			);
		}

		return $wpdb->get_results( $sql );
	}

	/**
	 * Bootstrap pagination function
	 */

	function wp_bs_pagination( $pages = '', $range = 4 ) {
		global $paged;

		$showitems = ($range * 2) + 1;
		if(empty($paged)) $paged = 1;

		if( $pages == '' ) {
			global $wp_query;
			$pages = $wp_query->max_num_pages;

			if( ! $pages ) {
				$pages = 1;
			}
		}

		if(1 != $pages) {
			echo '<div class="text-center">';
			echo '<nav><ul class="pagination"><li class="disabled hidden-xs"><span><span aria-hidden="true">Sida '.$paged.' av '.$pages.'</span></span></li>';

			if($paged > 2 && $paged > $range+1 && $showitems < $pages) echo "<li><a href='".get_pagenum_link(1)."' aria-label='First'>&laquo;<span class='hidden-xs'> Första</span></a></li>";

			if($paged > 1 && $showitems < $pages) echo "<li><a href='".get_pagenum_link($paged - 1)."' aria-label='Previous'>&lsaquo;<span class='hidden-xs'> Föregående</span></a></li>";

			for ($i=1; $i <= $pages; $i++) {
				if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems )) {
					echo ($paged == $i)? "<li class=\"active\"><span>".$i." <span class=\"sr-only\">(aktuell)</span></span></li>":"<li><a href='".get_pagenum_link($i)."'>".$i."</a></li>";
				}
			}

			if ($paged < $pages && $showitems < $pages) echo "<li><a href=\"".get_pagenum_link($paged + 1)."\"  aria-label='Next'><span class='hidden-xs'>Nästa </span>&rsaquo;</a></li>";
			if ($paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages) echo "<li><a href='".get_pagenum_link($pages)."' aria-label='Last'><span class='hidden-xs'>Sista </span>&raquo;</a></li>";

			echo "</ul></nav>";
			echo "</div>";
		}
	}

	/**
	 * Show add
	 */
	function do_ad( $args ) {
		echo get_ad( $args );
	}
	function get_ad( $args ) {
		$defaults = array(
			'name' => '',
			'width' => '',
			'class' => '',
		);
		$args = wp_parse_args( $args, $defaults );
		// data-name: if_empty( get_option( 'dn_site_url' ), 'asikt.dn.se' ) . '-' . $args['name']
		return '<div id="AD-' . $args['name'] . '-wrapper" class="dnasikt-ad-' . $args['name'] . ' ' . $args['class'] . '">
			<div id="AD-' . $args['name'] . '" data-width="' . $args['width'] . '" data-name="' . $args['name'] . '" class="burt-unit">
				<script type="text/javascript">
				window.Fusion.initAd("AD-' . $args['name'] . '", "' . $args['name'] . '", false);
				</script>
			</div>
		</div>';
	}

	/**
	 * Theme function, functions recieves urls from our Single Sign On Plugin
	 */
	function serviceplus_login_url( $cb = null ) {
	    if(!class_exists('ServicePlus'))
	        return '?sso_plugin_inactive';
	    $ServicePlus = new ServicePlus();
	    return $ServicePlus->login( $cb );
	}
	function serviceplus_check_logged_in_url( $cb = null ) {
	    if(!class_exists('ServicePlus'))
	        return '?sso_plugin_inactive';
	    $ServicePlus = new ServicePlus();
	    return str_replace('?appId', '/check-logged-in?appId', $ServicePlus->login( $cb ));
	}
	function serviceplus_create_account_url( $cb = null ) {
	    if(!class_exists('ServicePlus'))
	        return '?sso_plugin_inactive';
	    $ServicePlus = new ServicePlus();
	    return str_replace('?appId', '/register?appId', $ServicePlus->login( $cb ));
	}
	function serviceplus_logout_url() {
	    if(!class_exists('ServicePlus'))
	        return '?sso_plugin_inactive';
	    $ServicePlus = new ServicePlus();
	    return $ServicePlus->logout();
	}
?>
