<?php

/**
 * Calls the class on the post edit screen.
 */
function init_submit_page_metabox() {
    new SubmitPageMetabox();
}

if ( is_admin() ) {
    add_action( 'load-post.php', 'init_submit_page_metabox' );
    add_action( 'load-post-new.php', 'init_submit_page_metabox' );
}

/**
 * The Class.
 */
class SubmitPageMetabox {

	/**
	 * Hook into the appropriate actions when the class is constructed.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save' ) );
		add_action( 'admin_head', array( $this, 'visible_meta_box' ) );
	}

	/**
	 * Adds the meta box container.
	 */
	public function add_meta_box( $post_type ) {
            $post_types = array('page');     //limit meta box to certain post types
            if ( in_array( $post_type, $post_types )) {
				add_meta_box(
					'submit_page_metabox'
					,__( 'Inställningar', 'lwst_text' )
					,array( $this, 'render_meta_box_content' )
					,$post_type
					,'advanced'
					,'high'
				);
            }
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save( $post_id ) {

		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['lwst_save_submit_page_nonce'] ) )
			return $post_id;

		$nonce = $_POST['lwst_save_submit_page_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'lwst_save_submit_page_field' ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted,
                //     so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;

		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) )
				return $post_id;
		}

		/* OK, its safe for us to save the data now. */

		// Sanitize the user input.
		$state_finished_title 	= sanitize_text_field( $_POST['state_finished_title'] );
		$state_finished_content = $_POST['state_finished_content'];
		$state_cid_title 		= sanitize_text_field( $_POST['state_cid_title'] );
		$state_cid_content 		= $_POST['state_cid_content'];
		$state_pid_title 		= sanitize_text_field( $_POST['state_pid_title'] );
		$state_pid_content 		= $_POST['state_pid_content'];

		// Update the meta field.
		update_post_meta( $post_id, '_state_finished_title', $state_finished_title );
		update_post_meta( $post_id, '_state_finished_content', $state_finished_content );
		update_post_meta( $post_id, '_state_cid_title', $state_cid_title );
		update_post_meta( $post_id, '_state_cid_content', $state_cid_content );
		update_post_meta( $post_id, '_state_pid_title', $state_pid_title );
		update_post_meta( $post_id, '_state_pid_content', $state_pid_content );
	}


	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box_content( $post ) {
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'lwst_save_submit_page_field', 'lwst_save_submit_page_nonce' );

		// Use get_post_meta to retrieve an existing value from the database.
		$state_finished_title = get_post_meta( $post->ID, '_state_finished_title', true );
		$state_finished_content = get_post_meta( $post->ID, '_state_finished_content', true );

		$state_cid_title = get_post_meta( $post->ID, '_state_cid_title', true );
		$state_cid_content = get_post_meta( $post->ID, '_state_cid_content', true );

		$state_pid_title = get_post_meta( $post->ID, '_state_pid_title', true );
		$state_pid_content = get_post_meta( $post->ID, '_state_pid_content', true );

		?>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><label for="state_finished_title">Titel (Finished)</label></th>
					<td>
						<input type="text" id="state_finished_title" name="state_finished_title" class="regular-text" value="<?php echo esc_attr( $state_finished_title ); ?>"> <small></small>
						<p class="description"></p>
					</td>
				</tr>
				<tr valign="top">
					<td colspan="2">
						<?php wp_editor($state_finished_content, 'state_finished_content', array(
				            'wpautop'		=> true,
				            'media_buttons' => false,
				            'textarea_name' => 'state_finished_content',
				            'textarea_rows' => 10,
				            'teeny'			=> true
				            )); ?>
					</td>
				</tr>


				<tr valign="top">
					<th scope="row"><label for="state_cid_title">Titel ([Inbjuden] skicka in svar)</label></th>
					<td>
						<input type="text" id="state_cid_title" name="state_cid_title" class="regular-text" value="<?php echo esc_attr( $state_cid_title ); ?>"> <small></small>
						<p class="description"></p>
					</td>
				</tr>
				<tr valign="top">
					<td colspan="2">
						<?php wp_editor($state_cid_content, 'state_cid_content', array(
				            'wpautop'		=> true,
				            'media_buttons' => false,
				            'textarea_name' => 'state_cid_content',
				            'textarea_rows' => 10,
				            'teeny'			=> true
				            )); ?>
					</td>
				</tr>



				<tr valign="top">
					<th scope="row"><label for="state_pid_title">Titel ([Svar] Skicka in svar)</label></th>
					<td>
						<input type="text" id="state_pid_title" name="state_pid_title" class="regular-text" value="<?php echo esc_attr( $state_pid_title ); ?>"> <small></small>
						<p class="description"></p>
					</td>
				</tr>
				<tr valign="top">
					<td colspan="2">
						<?php wp_editor($state_pid_content, 'state_pid_content', array(
				            'wpautop'		=> true,
				            'media_buttons' => false,
				            'textarea_name' => 'state_pid_content',
				            'textarea_rows' => 10,
				            'teeny'			=> true
				            )); ?>
					</td>
				</tr>

			</tbody>
		</table>


		<?php
	}

	public function visible_meta_box() {
	?>
			<style>
			#submit_page_metabox {
				display: none;
			}
			</style>
	        <script type="text/javascript">
	        jQuery(document).ready( function($) {

	        	var toggleVisible = function( option, $element ) {
	        		if(option.val() === 'page-submit-conversation.php') {
						$element.show();
		        	} else {
		        		$element.hide();
		        	}
	        	}


	        	toggleVisible( $('select[name=page_template] option:selected'), $('#submit_page_metabox'));

	        	$(document).on('change','select[name=page_template]', function() {
	        		toggleVisible( $(this).find('option:selected'), $('#submit_page_metabox'));
	        	});
	        });
	        </script>
	<?php
	}
}