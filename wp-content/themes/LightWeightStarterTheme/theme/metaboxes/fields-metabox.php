<?php

/**
 * Calls the class on the post edit screen.
 */
function init_fields_metabox() {
    new FieldsMetabox();
}

if ( is_admin() ) {
    add_action( 'load-post.php', 'init_fields_metabox' );
    add_action( 'load-post-new.php', 'init_fields_metabox' );
}

/**
 * The Class.
 */
class FieldsMetabox {

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
					'fields_metabox'
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
		if ( ! isset( $_POST['lwst_save_fields_nonce'] ) )
			return $post_id;

		$nonce = $_POST['lwst_save_fields_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'lwst_save_fields_field' ) )
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
		$conversation_subject = sanitize_text_field( $_POST['conversation_subject'] );
		$message_to_dn = sanitize_text_field( $_POST['message_to_dn'] );



		// Update the meta field.
		update_post_meta( $post_id, '_conversation_subject', $conversation_subject );
		update_post_meta( $post_id, '_message_to_dn', $message_to_dn );
	}


	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box_content( $post ) {
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'lwst_save_fields_field', 'lwst_save_fields_nonce' );

		// Use get_post_meta to retrieve an existing value from the database.
		$conversation_subject = get_post_meta( $post->ID, '_conversation_subject', true );
		$message_to_dn = get_post_meta( $post->ID, '_message_to_dn', true );

		?>
		<div class="conversation-subject">
			<label for="conversation-subject">Ämne</label>
			<input type="text" name="conversation_subject" id="conversation-subject" placeholder="Ex. Debatt om skolan" value="<?php echo esc_attr( $conversation_subject ) ?>">
		</div>

		<label for="message_to_dn">Meddelande till redaktör</label>
		<textarea name="message_to_dn" class="message_to_dn" id="message-to-dn" placeholder="" ><?php echo esc_attr( $message_to_dn ) ?></textarea>


		<?php
	}

	public function visible_meta_box() {
	?>
			<style>
				#titlediv {
					padding: 10px;
					background: #fff;
					border: 1px solid #DDD;
				}
				.conversation-subject {
					margin-bottom: 10px;
				}
				.conversation-subject label {
					font-weight: 800;
				}
				.conversation-subject input {
					padding: 5px 10px;
					width: 70%;
				}
				.message_to_dn {
					width: 100%;
				}
			</style>
	        <script type="text/javascript">
	        jQuery(document).ready( function($) {

	        	var $meta_container = $("#fields_metabox");
	        	var $input = $meta_container.find("div.conversation-subject");
	        	$input.prependTo("#titlediv");

	        	if($meta_container.find("input:visible, textarea").length == 0) {
	        		$meta_container.find(".inside").append("<p>Finns inga inställningar förtillfället</p>");
	        	}

	        	var toggleVisible = function(option) {
	        		var val = option.attr('class');
	        		if(typeof val === 'undefined') {
						$input.show();
		        	} else {
		        		$input.hide();
		        	}
	        	}

	        	var $select = $('select[name=parent_id]');

	        	toggleVisible( $select.find('option:selected') );
	        	$(document).on('change','select[name=parent_id]', function() {
	        		toggleVisible( $(this).find('option:selected') );
	        	});
	        });
	        </script>
	<?php
	}
}