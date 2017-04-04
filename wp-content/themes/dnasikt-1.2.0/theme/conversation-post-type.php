<?php
	// Register Custom Post Type
	function dn_conversation_post_type() {

		$labels = array(
			'name'                => _x( 'Åsikter', 'Post Type General Name', 'dnasikt' ),
			'singular_name'       => _x( 'Åsikt', 'Post Type Singular Name', 'dnasikt' ),
			'menu_name'           => __( 'Åsikt', 'dnasikt' ),
			'name_admin_bar'      => __( 'Åsikt', 'dnasikt' ),
			'parent_item_colon'   => __( 'Parent åsikt:', 'dnasikt' ),
			'all_items'           => __( 'Alla åsikter', 'dnasikt' ),
			'add_new_item'        => __( 'Skapa ny åsikt', 'dnasikt' ),
			'add_new'             => __( 'Skapa ny', 'dnasikt' ),
			'new_item'            => __( 'Ny åsikt', 'dnasikt' ),
			'edit_item'           => __( 'Redigera åsikt', 'dnasikt' ),
			'update_item'         => __( 'Uppdatera åsikt', 'dnasikt' ),
			'view_item'           => __( 'Visa åsikt', 'dnasikt' ),
			'search_items'        => __( 'Sök åsikt', 'dnasikt' ),
			'not_found'           => __( 'Hittade inte...', 'dnasikt' ),
			'not_found_in_trash'  => __( 'Finns inte i papperskorgen', 'dnasikt' ),
		);
		$rewrite = array(
			'slug'                => 'debatt',
			'with_front'          => true,
			'pages'               => true,
			'feeds'               => true,
		);
		$args = array(
			'hierarchical'        => true,
			'label'               => __( 'asikt', 'dnasikt' ),
			'description'         => __( 'Post Type Description', 'dnasikt' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'page-attributes', 'custom-fields'),
			'taxonomies'          => array(  ),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
            'menu_icon'           => 'dashicons-format-chat',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'rewrite'             => $rewrite,
			'capability_type'     => 'page',
		);
		register_post_type( 'asikt', $args );
		flush_rewrite_rules();
	}

	// Hook into the 'init' action
	add_action( 'init', 'dn_conversation_post_type', 0 );

    function brdesign_enable_pages() {
        error_log('trying to add menu');
        add_submenu_page('edit.php?post_type=page', 'Custom Post Type Admin', 'Custom Settings', 'edit_posts', basename(__FILE__));
    }
    add_action('admin_menu' , 'brdesign_enable_pages');


	// Custom columns
	add_filter( 'manage_edit-asikt_columns', function ( $columns ) {

		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Titel' ),
			'subject' => __( 'Ämne' ),
			'persons' => __( 'Personer' ),
			'date' => __( 'Datum' ),
		);

		return $columns;
	}, 100 );

	add_action( 'manage_asikt_posts_custom_column', function ( $column, $post_id ) {
		global $post, $wpdb;

		$post_parent 	= ( $post->post_parent ) ? get_post( $post->post_parent ) : 0 ;
		$is_child 		= ( $post_parent );

		switch( $column ) {
			case 'subject' :

				if( $is_child ) {
					echo '<span class="label lightred">' . if_empty( get_post_meta( $post->post_parent, '_conversation_subject', true ), 'Saknar ämne...' ) . '</span>';
				} else {
					// echo '<i class="fa fa-star"></i>';
					echo '<span class="label red">' . if_empty( get_post_meta( $post->ID, '_conversation_subject', true ), 'Saknar ämne...' ) . '</span>';
				}

				break;

			case 'persons' :

				if( ! $is_child ) {
					$accepted = $wpdb->get_var( "SELECT count(*) FROM replies r JOIN wp_users u ON (r.user_id = u.ID) WHERE conversation_id = $post->ID" );
					$suggestions = $wpdb->get_var( "SELECT count(*) FROM reply_suggestions WHERE post_id = $post->ID" );
					echo 'Accepterade: <strong>' . $accepted . '</strong><br>';
					echo 'Förslag: <strong>' . $suggestions . '</strong>';
				} else {
					echo '-';
				}

				break;

			default :
				break;
		}
	}, 100, 2 );

	add_filter( 'manage_edit-asikt_sortable_columns', function ( $columns ) {

		$columns['subject'] = 'subject';
		$columns['persons'] = 'persons';

		return $columns;
	});

	add_action( 'load-edit.php', function () {

		add_filter( 'request', function ( $vars ) {
			/* Check if we're viewing the 'movie' post type. */
			if ( isset( $vars['post_type'] ) && 'asikt' == $vars['post_type'] ) {

				// Conversation Subject
				if ( isset( $vars['orderby'] ) && 'subject' == $vars['orderby'] ) {

					/* Merge the query vars with our custom variables. */
					$vars = array_merge(
						$vars,
						array(
							'meta_key' => '_conversation_subject',
							'orderby' => 'meta_value'
						)
					);
				}

				// Conversation persons
				if ( isset( $vars['orderby'] ) && 'persons' == $vars['orderby'] ) {

					/* Merge the query vars with our custom variables. */
					$vars = array_merge(
						$vars,
						array(
							'meta_key' => '_conversation_subject',
							'orderby' => 'meta_value'
						)
					);
				}
			}

			return $vars;
		});

	});
