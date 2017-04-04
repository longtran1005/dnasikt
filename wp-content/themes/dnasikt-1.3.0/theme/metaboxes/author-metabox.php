<?php

/**
 * Calls the class on the post edit screen.
 */
function init_author_metabox() {
    new AuthorMetabox();
}

if ( is_admin() ) {
    add_action( 'load-post.php', 'init_author_metabox' );
    add_action( 'load-post-new.php', 'init_author_metabox' );
}

/**
 * The Class.
 */
class AuthorMetabox {

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
					'author_metabox'
					,__( 'Författare', 'lwst_text' )
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
		if ( ! isset( $_POST['lwst_save_author_nonce'] ) )
			return $post_id;

		$nonce = $_POST['lwst_save_author_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'lwst_save_author_field' ) )
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
		$author_anonymous = sanitize_text_field( isset( $_POST['author_anonymous'] ) ? $_POST['author_anonymous'] : '' );

		// Update the meta field.
		update_post_meta( $post_id, '_author_anonymous', $author_anonymous );
	}


	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box_content( $post ) {
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'lwst_save_author_field', 'lwst_save_author_nonce' );

		$author_anonymous = get_post_meta( $post->ID, '_author_anonymous', true );

		$post_author_id = $post->post_author;
		$user = get_userdata( $post_author_id );
		?>
		<table class="author-table">
			<tr>
				<td style="padding-right: 20px;">
					<img src="<?php echo if_empty(get_wp_user_avatar_src( $post->post_author, 'thumbnail' ), 'http://placehold.it/80x80'); ?>" alt="" style="border-radius: 100px; width: 80px;">
				</td>
				<td>
					<table>
						<tr>
							<td><strong>Namn</strong></td>
							<td><?php echo $user->display_name; ?></td>
						</tr>
						<tr>
							<td><strong>E-post</strong></td>
							<td><?php echo $user->user_email; ?></td>
						</tr>
						<tr>
							<td><strong>Alternativ e-post</strong></td>
							<td><?php echo user_meta($user->ID,'alt_email','Ingen') ?></td>
						</tr>
						<tr>
							<td><strong>Telefon</strong></td>
							<td><?php echo user_meta($user->ID,'phone_number','Inget') ?></td>
						</tr>
						<tr>
							<td><strong>Om skribenten</strong></td>
							<td><?php echo user_meta($user->ID,'description','Saknar en beskrivning, ') ?></td>
						</tr>
						<tr>
							<td><label for=""><strong>Anonym i denna post</strong></label></td>
							<td><input type="checkbox" name="author_anonymous" value="1" <?php if($author_anonymous == 1) echo 'checked'; ?>></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<?php
	}

	public function visible_meta_box() {
	?>
			<style>

			</style>
	        <script type="text/javascript">
	        jQuery(document).ready( function($) {
	        	var $authordiv = $("#authordiv .inside");
	        	var $authormeta = $("#author_metabox").hide();
	        	$authordiv.append($authormeta.find("table.author-table"));
	        });
	        </script>
	<?php
	}
}