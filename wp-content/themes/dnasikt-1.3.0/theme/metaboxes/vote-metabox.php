<?php

/**
 * Calls the class on the post edit screen.
 */
function init_vote_metabox() {
    new VoteMetabox();
}

if ( is_admin() ) {
    add_action( 'load-post.php', 'init_vote_metabox' );
    add_action( 'load-post-new.php', 'init_vote_metabox' );
}

/**
 * The Class.
 */
class VoteMetabox {

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
            $post_types = array('asikt');     //limit meta box to certain post types
            if ( in_array( $post_type, $post_types )) {
				add_meta_box(
					'vote_metabox'
					,__( 'Röster', 'lwst_text' )
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
		if ( ! isset( $_POST['lwst_save_vote_nonce'] ) )
			return $post_id;

		$nonce = $_POST['lwst_save_vote_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'lwst_save_vote_field' ) )
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
		$question_text = sanitize_text_field( $_POST['question_text_field'] );
		$question_agree_text = sanitize_text_field( $_POST['question_agree_text_field'] );
		$question_disagree_text = sanitize_text_field( $_POST['question_disagree_text_field'] );

		// Update the meta field.
		update_post_meta( $post_id, '_question_text', $question_text );
		update_post_meta( $post_id, '_question_agree_text', $question_agree_text );
		update_post_meta( $post_id, '_question_disagree_text', $question_disagree_text );
	}


	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box_content( $post ) {

		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'lwst_save_vote_field', 'lwst_save_vote_nonce' );

		// Use get_post_meta to retrieve an existing value from the database.
		$question_text = get_post_meta( $post->ID, '_question_text', true );
		$question_agree_text = get_post_meta( $post->ID, '_question_agree_text', true );
		$question_disagree_text = get_post_meta( $post->ID, '_question_disagree_text', true );
		?>
			<table>
				<tr>
					<td colspan="2">
						<label for="">Fråga</label>
						<textarea name="question_text_field" id="" rows="3" style="width: 100%;"><?php echo esc_attr( $question_text ) ?></textarea>
					</td>
				</tr>
				<tr>
					<td>
						<label for="">Håller med</label>
						<input type="text" name="question_agree_text_field" value="<?php echo esc_attr( $question_agree_text ) ?>" >
					</td>
					<td>
						<label for="">Håller inte med</label>
						<input type="text" name="question_disagree_text_field" value="<?php echo esc_attr( $question_disagree_text ) ?>">
					</td>
				</tr>
			</table>

		<?php
	}

	public function visible_meta_box() {
	?>
	        <script type="text/javascript">

	        jQuery(document).ready( function($) {

	        	var toggleVisible = function(option) {
	        		var val = option.attr('class');
	        		console.log(typeof val);
	        		if(typeof val === 'undefined') {
						$metabox.show();
		        	} else {
		        		$metabox.hide();
		        	}
	        	}

	        	var $select = $('select[name=parent_id]');
	        	var $metabox = $('#vote_metabox');

	        	toggleVisible( $select.find('option:selected') );
	        	$(document).on('change','select[name=parent_id]', function() {
	        		toggleVisible( $(this).find('option:selected') );
	        	});

	        });
	        </script>
	<?php
	}
}