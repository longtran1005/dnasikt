<?php

/**
 * Calls the class on the post edit screen.
 */
function init_reply_metabox() {
    new ReplyMetabox();
}

if ( is_admin() ) {
    add_action( 'load-post.php', 'init_reply_metabox' );
    add_action( 'load-post-new.php', 'init_reply_metabox' );
}

/**
 * The Class.
 */
class ReplyMetabox {

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
	            'replies_metabox',
	            __( 'Personer som ska svara', 'lwst_text' ),
				array( $this, 'render_meta_box_content' ),
				$post_type,
				'advanced',
				'high'
	        );
        }
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save( $post_id ) {
		global $wpdb;
		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['lwst_save_reply_nonce'] ) )
			return $post_id;

		$nonce = $_POST['lwst_save_reply_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'lwst_save_reply_field' ) )
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

		$postids = $wpdb->get_col( $wpdb->prepare(
			"
			SELECT      id
			FROM        reply_suggestions
			WHERE       post_id = %s
			",
			$post_id
		) );

		// Delete all suggestions added in accepted list
		if(isset($_POST['suggestions'])) {
			foreach($postids as $id) {
				if(count($_POST['suggestions']['row_id']) == 0) break;
				if(!in_array($id, $_POST['suggestions']['row_id'])) {
					$wpdb->delete( 'reply_suggestions', array( 'id' => $id ), array( '%d' ) );
				}
			}
		} else {
			$wpdb->delete( 'reply_suggestions', array( 'post_id' => $post_id ), array( '%d' ) );
		}

		$replies = $wpdb->get_results( $wpdb->prepare(
			"
			SELECT      *
			FROM        replies
			WHERE       conversation_id = %d
			",
			$post_id
		) );
		foreach($replies as $row) {
			if(count($_POST['accepted']['author_id']) == 0) break;
			if(!in_array($row->user_id, $_POST['accepted']['author_id'])) {
				$wpdb->delete( 'replies',
					array(
						'user_id' => $row->user_id,
						'conversation_id' => $post_id
					),
					array(
						'%d',
						'%d'
					)
				);
			}
		}
		// Insert all new accepted
		if(isset($_POST['accepted'])) {
			for($i = 0; $i < count($_POST['accepted']['author_id']); $i++) {

				$id 		= $_POST['accepted']['author_id'][$i];
				$motivation = $_POST['accepted']['motivation'][$i];
				$status 	= $_POST['accepted']['status'][$i];

				if(empty($id)) continue;

				$exist = $wpdb->get_row( $wpdb->prepare(
					"
						SELECT 	*
						FROM 	replies
						WHERE 	conversation_id = %d
						AND		user_id = %d
						LIMIT 1
					",
					$post_id,
					$id
				) );

				if(count($exist) == 0) {
					$wpdb->insert(
						'replies',
						array(
							'user_id' => $id,
							'conversation_id' => $post_id,
							'motivation' => $motivation,
							'visible' => '0',
							'status' => $status
						),
						array(
							'%d',
							'%d',
							'%s', // Motivation (string)
							'%s',
							'%s' // Status (string)
						)
					);
				} else {
					$wpdb->update(
						'replies',
						array( 
							'motivation' => $motivation,
							'status' => $status 
						), // SET
						array( 'id' => (int) $exist->id ), // WHERE
						array( 
							'%s', 
							'%s'
						), // SET FORMAT
						array( '%d' ) // WHERE FORMAT
					);
				}
			}
		}
	}


	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box_content( $post ) {
		global $wpdb, $post;
		wp_nonce_field( 'lwst_save_reply_field', 'lwst_save_reply_nonce' );
	    $select_users = $wpdb->get_results( "SELECT * FROM wp_users ORDER BY display_name" );
	    ?>
		<input type="hidden" id="post_id" value="<?php echo $post->ID; ?>">
		<input type="hidden" id="post_title" value="<?php echo $post->post_title; ?>">

	    <p style="color: red; display: none;">Glöm inte att uppdatera inlägget om du har ändrat något!</p>
		<h3>Accepterade</h3>
		<table id="table1" class="wp-list-table widefat fixed striped pages">
			<tr>
				<th>Person</th>
				<th>Alternativ</th>
			</tr>
			<!-- Template -->
			<?php
			$results = $wpdb->get_results( "SELECT * FROM replies r JOIN wp_users u ON (r.user_id = u.ID) WHERE conversation_id = $post->ID" );
			foreach($results as $row) :?>
			<tr class="author-row">
				<td>
					<input type="hidden" value="<?php echo $row->user_id ?>" class="input-author_id" name="accepted[author_id][]">
					<input type="hidden" value="<?php echo $row->status ?>" class="input-status" name="accepted[status][]">

					<strong class="text-author_name"><?php echo $row->display_name; ?></strong><br>
					<textarea rows="3" style="display:none;width:100%;" class="input-motivation" name="accepted[motivation][]"><?php echo $row->motivation ?></textarea>
					<p class="text-motivation"><?php echo $row->motivation ?></p>
					<p class="text-contact"><?php echo if_empty($row->user_email,'Ingen e-post') ?></p>
					<a href="" class="js__add_conversation">Skapa inlägg</a> |
					<a href="" class="js__show">Ändra motivering</a> |
					<a href="" class="js__remove">Ta bort</a> |
					<?php if(isint($row->status)) echo '<a href="'.get_permalink( $row->status ).'">Visa inlägg</a>'; ?>
					<select class="js__status">
						<option value="pending" <?php if($row->status=="pending") echo "selected" ?>>Normal</option>
						<option value="waiting" <?php if($row->status=="waiting") echo "selected" ?>>Väntar på svar</option>
						<option value="declined" <?php if($row->status=="declined") echo "selected" ?>>Ville inte svara</option>
						<option value="unknown" <?php if($row->status=="unknown") echo "selected" ?>>Hittade inte personen</option>
					</select>
				</td>
				<td>
					<strong>Status:</strong> <em class="text-status"><?php echo $row->status ?></em><br>
					<strong>Röster:</strong> <em class="text-votes"></em> (<a href="" class="js__vote" rel-data-vote="1" rel-data-id="<?php echo $row->id ?>">+<?php echo get_reply_votes($row->id) ?></a>)
				</td>
			</tr>
			<?php endforeach; ?>
		</table>

		<!-- Template -->
		<table id="table-template" style="display: none;">
			<tr class="author-row">
				<td>
					<input type="hidden" value="" class="input-author_id" name="accepted[author_id][]">
					<input type="hidden" value="pending" class="input-status" name="accepted[status][]">

					<strong class="text-author_name"></strong><br>
					<textarea rows="3" style="display:none;width:100%;" value="" class="input-motivation" name="accepted[motivation][]"></textarea>
					<p class="text-motivation"></p>
					<p class="text-contact"></p>
					<select class="js__status">
						<option value="pending">Normal</option>
						<option value="waiting">Väntar på svar</option>
						<option value="declined">Ville inte svara</option>
						<option value="unknown">Hittade inte personen</option>
					</select>
				</td>
				<td>
					<strong>Status:</strong> <em class="text-status">pending</em><br>
					<strong>Röster:</strong> <em class="text-votes">0 st</em>
				</td>
			</tr>
		</table>

		<h3 style="margin-top: 30px;">Förslag</h3>
		<table class="wp-list-table widefat fixed striped pages">
			<tr>
				<th>Person</th>
				<th>Alternativ</th>
			</tr>
			<?php
			$results = $wpdb->get_results( "SELECT * FROM reply_suggestions WHERE post_id = $post->ID" );
			foreach($results as $row) : ?>
			<tr>
				<td>
					<input type="hidden" value="<?php echo $row->id ?>" class="input-row_id" name="suggestions[row_id][]">

					<strong class="text-author_name"><?php echo $row->name ?></strong><br>
					<p class="text-motivation"><?php echo $row->motivation ?></p>
					<p class="text-contact"><?php echo $row->contact ?></p>
					<a href="" class="js__remove">Ta bort</a>
				</td>
				<td>
					<select name="" id="" class="select-author">
						<option value="0">Verkar inte finnas</option>
						<?php foreach($select_users as $user) : ?>
							<option value="<?php echo $user->ID ?>" <?php if( strtolower($row->name) == strtolower($user->display_name) ) echo "selected"; ?>><?php echo $user->display_name ?></option>
						<?php endforeach; ?>
					</select>
					<button class="js__add_user">Välj användare</button>
					<button class="js__create">Skapa användare</button>
				</td>
			</tr>
			<?php endforeach; ?>
		</table>
		<h3 style="margin-top: 30px;">Lägg till förslag</h3>
		<div>
			<select name="" id="" class="_select-author">
				<option value="0">Välj person</option>
				<?php foreach($select_users as $user) : ?>
					<option value="<?php echo $user->ID ?>"><?php echo $user->display_name ?> (<?php echo $user->user_email ?>)</option>
				<?php endforeach; ?>
			</select>
			eller skapa en ny
			<input type="text" class="_input-author_name">
		</div>
		<textarea class="_input-motivation" rows="2" style="width: 100%; margin-top: 10px;" placeholder="Motivation"></textarea>

		<button class="js__admin_create">Lägg till / skapa</button>
		<script>
			jQuery(document).ready(function() {

				function add_new_row(author_id, author_name, motivation) {
					// Get our template
					var $el = jQuery('table#table-template tr').clone();
					$el.appendTo("#table1");
					// Add our data
					$el.find(".text-author_name").text(author_name);
					$el.find(".input-author_id").val(author_id);
					$el.find(".text-motivation").text(motivation);
					$el.find(".input-motivation").val(motivation);
				}

				jQuery(document).on('click', '.js__add_user', function(e) {
					e.preventDefault();
					var $current_row = jQuery(this).closest("tr");

					// Get curret row values
					var author_name = $current_row.find(".select-author option:selected").text();
					var author_id = $current_row.find(".select-author option:selected").val();
					var motivation = $current_row.find(".text-motivation").text();

					// Check if something is selected or not
					if(author_id === '0') {
						alert("Du kan inte välja en tom användare!");
						return false;
					}

					// Add new row
					add_new_row(author_id,author_name,motivation);

					// Remove current row
					$current_row.remove();
				});

				// Create WP user
				jQuery(document).on('click', '.js__create', function(e) {
					e.preventDefault();

					var $current_row = jQuery(this).closest("tr");

					// Get curret row values
					var contact = $current_row.find(".text-contact").text();
					var author_name = $current_row.find(".text-author_name").text();
					author_name = prompt("Stämmer namnet?", author_name);
					var motivation = $current_row.find(".text-motivation").text();

					if(author_name === null) return false;

					jQuery.ajax({
						type : "post",
						dataType : "json",
						url : ajaxurl,
						data : {
							action: "create_new_wp_user",
							post_id : jQuery("#post_id").val(),
							name: author_name,
							contact: contact
						},
						success: function(data) {
							console.log(data);
							if(data.status !== 'error') {
								add_new_row(data.user_id,data.name,motivation);
								$current_row.remove();
							} else {
								alert("Ett fel uppstod: " + data.message);
							}
						},
						error: function(data) {
							console.log("Error");
							console.log(data);
						}
					});

				});

				// Create WP user
				jQuery(document).on('click', '.js__admin_create', function(e) {
					e.preventDefault();


					var author_id = jQuery('._select-author option:selected').val();
					var motivation = jQuery('._input-motivation').val();

					if(author_id !== '0') {
						var author_name = jQuery('._select-author option:selected').text();
						add_new_row(author_id, author_name, motivation);

					}
					else {
						var author_name = jQuery('._input-author_name').val();
						jQuery.ajax({
							type : "post",
							dataType : "json",
							url : ajaxurl,
							data : {
								action: "create_new_wp_user",
								post_id : jQuery("#post_id").val(),
								name: author_name
							},
							success: function(data) {
								console.log(data);
								if(data.status !== 'error') {
									add_new_row(data.user_id,data.name,motivation);
									$current_row.remove();
								} else {
									alert("Ett fel uppstod: " + data.message);
								}
							},
							error: function(data) {
								console.log("Error");
								console.log(data);
							}
						});
					}

				});

				jQuery(document).on('click', '.js__show', function(e) {
					e.preventDefault();
					var $current_input_motivation = jQuery(this).closest("tr").find(".input-motivation").first();
					var $current_text_motivation = jQuery(this).closest("tr").find(".text-motivation").first();
					// Enable row
					$current_input_motivation.show();
					$current_text_motivation.hide();
				});
				jQuery(document).on('click', '.js__remove', function(e) {
					e.preventDefault();
					var $current_row = jQuery(this).closest("tr");
					// Remove row
					$current_row.remove();
				});
				jQuery(document).on('change', '.js__status', function(e) {
					e.preventDefault();
					var $el = jQuery(this);
					var $current_row = $el.closest("tr");

					var old_status = $current_row.find(".input-status").val();
					var new_status = $el.find("option:selected").val();

					$current_row.find(".input-status").val(new_status);
					$current_row.find(".text-status").text(new_status);
				});

				jQuery(document).on('click', '.js__add_conversation', function(e) {
					e.preventDefault();
					var $el = jQuery(this);
					var $current_row = $el.closest("tr");


					var email = prompt("E-postadress till skribenten", $current_row.find(".text-contact").text());

					if(email === null) return false;

					jQuery.ajax({
						type : "post",
						dataType : "json",
						url : ajaxurl,
						data : {
							action: "create_new_conversation",
							title : jQuery("#post_title").val(),
							post_id : jQuery("#post_id").val(),
							user_id: $current_row.find(".input-author_id").val(),
							email: email,
							subject: jQuery("#conversation-subject").val()
						},
						success: function(data) {
							console.log(data);
							prompt("Dela länken: (Lösenord: "+data.password+")", data.url);
						},
						error: function(data) {
							console.log("Error");
							console.log(data);
						}
					});
				});
				jQuery(document).on('click', '.js__vote', function(e) {
					e.preventDefault();
					var $el = jQuery(this);
					jQuery.ajax({
						type : "post",
						dataType : "json",
						url : ajaxurl,
						data : {
							action: "add_vote",
							reply_id: jQuery(this).attr("rel-data-id")
						},
						success: function(data) {
							$el.text('+' + data.votes);
						},
						error: function(data) {
							console.log("Error");
							console.log(data);
						}
					});
				});

			});
		</script>
		<?php
	}

	public function visible_meta_box() {
	?>
	        <script type="text/javascript">

	        jQuery(document).ready( function($) {

	        	var toggleVisible = function(option) {
	        		var val = option.attr('class');
	        		if(typeof val === 'undefined') {
						$metabox.show();
		        	} else {
		        		$metabox.hide();
		        	}
	        	}

	        	var $select = $('select[name=parent_id]');
	        	var $metabox = $('#replies_metabox');

	        	toggleVisible( $select.find('option:selected') );
	        	$(document).on('change','select[name=parent_id]', function() {
	        		toggleVisible( $(this).find('option:selected') );
	        	});

	        });
	        </script>
	<?php
	}
}