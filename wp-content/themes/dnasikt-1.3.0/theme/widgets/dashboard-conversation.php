<?php
	add_action( 'wp_dashboard_setup', function () {
	 	wp_add_dashboard_widget( 'dashboard_widget_persons', 'Förslag på personer', 'dashboard_widget_persons_function' );

	 	// Globalize the metaboxes array, this holds all the widgets for wp-admin

	 	global $wp_meta_boxes;

	 	// Get the regular dashboard widgets array
	 	// (which has our new widget already but at the end)

	 	$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

	 	// Backup and delete our new dashboard widget from the end of the array

	 	$example_widget_backup = array( 'dashboard_widget_persons' => $normal_dashboard['dashboard_widget_persons'] );
	 	unset( $normal_dashboard['dashboard_widget_persons'] );

	 	// Merge the two arrays together so our widget is at the beginning

	 	$sorted_dashboard = array_merge( $example_widget_backup, $normal_dashboard );

	 	// Save the sorted array back into the original metaboxes

	 	$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
	});
	function dashboard_widget_persons_function() {
		global $wpdb;
		// $sql = $wpdb->prepare( "SELECT * FROM reply_suggestions" );
		$sql = "SELECT p.ID,p.post_title,u.display_name,rs.name,rs.contact,rs.motivation FROM reply_suggestions rs JOIN wp_users u ON (rs.user_id = u.id) JOIN wp_posts p ON (rs.post_id = p.ID)";
		$result = $wpdb->get_results( $sql );
		?>
		<input type="hidden" id="suggestion_count" value="<?php echo count($result); ?>">
		<?php foreach($result as $row) : ?>
			<div class="dashboard-box">
				<div class="title"><a href="<?php echo get_edit_post_link( $row->ID ); ?>"><?php echo $row->post_title ?></a></div>
				<div class="inner">
					<em>Från: <strong><?php echo $row->display_name ?></strong></em>
					<hr>
					Namn: <strong><?php echo $row->name ?></strong><br>
					Kontakt: <strong><?php echo $row->contact ?></strong><br>
					Motivering: <strong><?php echo $row->motivation ?></strong>
				</div>
			</div>
		<?php endforeach;
	}

	add_action( 'wp_dashboard_setup', function () {
	 	wp_add_dashboard_widget( 'dashboard_widget_conversation', 'Nya inlägg', 'dashboard_widget_conversation_function' );

	 	// Globalize the metaboxes array, this holds all the widgets for wp-admin

	 	global $wp_meta_boxes;

	 	// Get the regular dashboard widgets array
	 	// (which has our new widget already but at the end)

	 	$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

	 	// Backup and delete our new dashboard widget from the end of the array

	 	$example_widget_backup = array( 'dashboard_widget_conversation' => $normal_dashboard['dashboard_widget_conversation'] );
	 	unset( $normal_dashboard['dashboard_widget_conversation'] );

	 	// Merge the two arrays together so our widget is at the beginning

	 	$sorted_dashboard = array_merge( $example_widget_backup, $normal_dashboard );

	 	// Save the sorted array back into the original metaboxes

	 	$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
	});
	function dashboard_widget_conversation_function() {
		$args = array(
			'post_type' => 'asikt',
			'post_status' => 'draft',
			);

		$query = new WP_Query( $args );
		if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); ?>
			<div class="dashboard-box">
				<div class="title"><a href="<?php echo get_edit_post_link( get_the_id() ); ?>"><?php the_title(); ?></a><span class="pull-right"><?php echo human_time_diff( get_the_time('U'), current_time('timestamp') ) . ' sedan'; ?></span></div>
				<div class="inner">
					<em>Författare: <strong><?php the_author(); ?></strong></em>
					<hr>
					<?php the_excerpt(); ?>
				</div>
			</div>


		<?php endwhile;  else: ?>

		<?php endif; ?>

		<input type="hidden" id="post_count" value="<?php echo $query->post_count ?>">
		<?php
	}

	/**
	 *
	 * Show custom post types in dashboard activity widget
	 *
	 */

	// unregister the default activity widget
	add_action('wp_dashboard_setup', 'remove_dashboard_widgets' );
	function remove_dashboard_widgets() {

	    global $wp_meta_boxes;
	    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);

	}

	// register your custom activity widget
	add_action('wp_dashboard_setup', 'add_custom_dashboard_activity' );
	function add_custom_dashboard_activity() {
	    wp_add_dashboard_widget('custom_dashboard_activity', 'Activities', 'custom_wp_dashboard_site_activity');
	}

	// the new function based on wp_dashboard_recent_posts (in wp-admin/includes/dashboard.php)
	function wp_dashboard_recent_post_types( $args ) {

	/* Chenged from here */

		if ( ! $args['post_type'] ) {
			$args['post_type'] = 'any';
		}

		$query_args = array(
			'post_type'      => $args['post_type'],

	/* to here */

			'post_status'    => $args['status'],
			'orderby'        => 'date',
			'order'          => $args['order'],
			'posts_per_page' => intval( $args['max'] ),
			'no_found_rows'  => true,
			'cache_results'  => false
		);
		$posts = new WP_Query( $query_args );

		if ( $posts->have_posts() ) {

			echo '<div id="' . $args['id'] . '" class="activity-block">';

			if ( $posts->post_count > $args['display'] ) {
				echo '<small class="show-more hide-if-no-js"><a href="#">' . sprintf( __( 'See %s more…'), $posts->post_count - intval( $args['display'] ) ) . '</a></small>';
			}

			echo '<h4>' . $args['title'] . '</h4>';

			echo '<ul>';

			$i = 0;
			$today    = date( 'Y-m-d', current_time( 'timestamp' ) );
			$tomorrow = date( 'Y-m-d', strtotime( '+1 day', current_time( 'timestamp' ) ) );

			while ( $posts->have_posts() ) {
				$posts->the_post();

				$time = get_the_time( 'U' );
				if ( date( 'Y-m-d', $time ) == $today ) {
					$relative = __( 'Today' );
				} elseif ( date( 'Y-m-d', $time ) == $tomorrow ) {
					$relative = __( 'Tomorrow' );
				} else {
					/* translators: date and time format for recent posts on the dashboard, see http://php.net/date */
					$relative = date_i18n( __( 'M jS' ), $time );
				}

	 			$text = sprintf(
					/* translators: 1: relative date, 2: time, 4: post title */
	 				__( '<span>%1$s, %2$s</span> <a href="%3$s">%4$s</a>' ),
	  				$relative,
	  				get_the_time(),
	  				get_edit_post_link(),
	  				_draft_or_post_title()
	  			);

	 			$hidden = $i >= $args['display'] ? ' class="hidden"' : '';
	 			echo "<li{$hidden}>$text</li>";
				$i++;
			}

			echo '</ul>';
			echo '</div>';

		} else {
			return false;
		}

		wp_reset_postdata();

		return true;
	}

	// The replacement widget
	function custom_wp_dashboard_site_activity() {

	    echo '<div id="activity-widget">';

	    $future_posts = wp_dashboard_recent_post_types( array(
	        'post_type'  => 'any',
	        'display' => 10,
	        'max'     => 10,
	        'status'  => 'draft',
	        'order'   => 'ASC',
	        'title'   => __( 'Ej granskade' ),
	        'id'      => 'not-reviewed-posts',
	    ) );

	    $recent_posts = wp_dashboard_recent_post_types( array(
	        'post_type'  => 'any',
	        'display' => 10,
	        'max'     => 10,
	        'status'  => 'publish',
	        'order'   => 'DESC',
	        'title'   => __( 'Recently Published' ),
	        'id'      => 'published-posts',
	    ) );


	    if ( !$future_posts && !$recent_posts ) {
	        echo '<div class="no-activity">';
	        echo '<p class="smiley"></p>';
	        echo '<p>' . __( 'No activity yet!' ) . '</p>';
	        echo '</div>';
	    }

	    echo '</div>';
	}
?>