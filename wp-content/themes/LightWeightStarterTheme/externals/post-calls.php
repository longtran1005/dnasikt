<?php
function cb_submit_new_draft_post() {
	global $wpdb;

	if( isset( $_POST['submit_update_profile'] ) ) {

		if( ! wp_verify_nonce( $_POST['_wpnonce'], 'update_profile_nonce' ) ) {
			die();
		}

		$errors = validate_post_request(array(
			'display_name' => 'Titel måste vara ifyllt'
			)
		);

		if( $errors ) {
			die("Ett fel har uppstått. Uppdatera sidan.");
		}

		$user_id = get_current_user_id();
		// $display_name = sanitize_text_field( $_POST['display_name'] );
		$bio = sanitize_text_field( $_POST['bio'] );
		$alt_email = sanitize_text_field( $_POST['alt_email'] );
		$phone_number = sanitize_text_field( $_POST['phone_number'] );

		if( isset( $_POST['no_email_notifications'] ) ) {
			update_user_meta( $user_id, 'no_email_notifications', '1' );
		} else {
			delete_user_meta( $user_id, 'no_email_notifications', '1' );
		}

		// Update user
		wp_update_user( array(
				'ID' => $user_id,
				'description' => $bio,
				// 'display_name' => $display_name
				) );

		update_user_meta( $user_id, 'alt_email', $alt_email );
		update_user_meta( $user_id, 'phone_number', $phone_number );

		// Upload Avatar Image
		if (isset($_FILES['avatar'])) {
			if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
				// Handle errors
			}
			$avatar_id = media_handle_upload( 'avatar', 0 );
			if( ! is_wp_error( $avatar_id ) ) {
				update_user_meta( $user_id, 'wp_user_avatar', $avatar_id );
			}
		}

		wp_redirect( strtok( $_POST['redirect'], '?' ) . '?status=profile_updated' );
		exit;
	}

	if(isset( $_POST['confirm_conversation_password'] )) {
		if(isset($_GET['cid']) AND isset($_GET['token'])) {
			$cid = $_GET['cid'];
			$token = $_GET['token'];
			$password = get_post_meta( $cid, '_conversation_password', true );
			$url = get_custom_page(array( 'cid' => $cid, 'token' => $token ));
			if($password == md5(trim($_POST['password']))) {
				$_SESSION['add_reply']['conversation_id'] = $cid;
				$url .= '&state=working';

			}
			wp_redirect( $url );
			exit;
		}
	}


	if(isset( $_POST['submit_new_draft_post'] )) {
		// Validate
		$errors = validate_post_request(array(
			'title' => 'Titel måste vara ifyllt',
			'thecontent' => 'Content måste vara ifyllt',
			'summary' => 'summary måste vara ifyllt',
			'bio' => 'Bio måste vara ifyllt',
			)
		);

		// Page 1
		$title = sanitize_text_field( $_POST['title'] );
		$thecontent = $_POST['thecontent'];
		$summary = sanitize_text_field( $_POST['summary'] );
		// Page 2
		$bio = sanitize_text_field( $_POST['bio'] );
		$alt_email = sanitize_text_field( $_POST['alt_email'] );
		$phone_number = sanitize_text_field( $_POST['phone_number'] );
		$message_to_dn = sanitize_text_field( $_POST['message_to_dn'] );




		// $question_text = sanitize_text_field( $_POST['question_text'] );
		// $question_agree_text = sanitize_text_field( $_POST['question_agree_text'] );
		// $question_disagree_text = sanitize_text_field( $_POST['question_disagree_text'] );

		// Any Errors?
		if(!$errors) {

			// 1. Create draft conversation
			$new_draft_conversation = array(
			  'post_title'   	=> $title,
			  'post_type' 		=> 'asikt',
			  'post_content' 	=> $thecontent,
			  'post_excerpt' 	=> $summary,
			  'post_status'  	=> 'draft',
			);

			$user_id = get_current_user_id();

			if(isset($_GET['cid'])) {
				$new_draft_conversation['ID'] = $_GET['cid'];
				$conversation_id = $_GET['cid'];
				$draft_post = get_post( $conversation_id );
				$user_id = $draft_post->post_author;
				wp_update_post( $new_draft_conversation );

				// Update replies status
				$wpdb->update(
					'replies',
					array( 'status' => $draft_post->ID ),
					array(
						'user_id' => $user_id,
						'conversation_id' => $draft_post->post_parent
					),
					array( '%s' ),
					array(
						'%d',
						'%d'
					)
				);

			} elseif(isset($_GET['pid'])) {
				$new_draft_conversation['post_parent'] = $_GET['pid'];
				$conversation_id = wp_insert_post( $new_draft_conversation );
			} else {
				$conversation_id = wp_insert_post( $new_draft_conversation );
			}

			// Add meta data
			update_post_meta( $conversation_id, '_message_to_dn', $message_to_dn );
			// update_post_meta( $conversation_id, '_question_text', $question_text );
			// update_post_meta( $conversation_id, '_question_agree_text', $question_agree_text );
			// update_post_meta( $conversation_id, '_question_disagree_text', $question_disagree_text );

			// X. Add meta_data for Post
			if(isset($_POST['suggestions']) AND !isset($_POST['cid'])) {
				for($i = 0; $i < count($_POST['suggestions']['name']); $i++) {

					$name = $_POST['suggestions']['name'][$i];
					$contact = $_POST['suggestions']['contact'][$i];
					$motivation = $_POST['suggestions']['motivation'][$i];

					// Insert into DB
					$wpdb->insert(
						'reply_suggestions',
						array(
							'post_id' => $conversation_id,
							'user_id' => get_current_user_id(),
							'motivation' => $motivation,
							'name' => $name
						),
						array(
							'%d',
							'%d',
							'%s',
							'%s',
						)
					);
				}
			}

			if (!function_exists('wp_generate_attachment_metadata')){
				require_once(ABSPATH . "wp-admin" . '/includes/image.php');
				require_once(ABSPATH . "wp-admin" . '/includes/file.php');
				require_once(ABSPATH . "wp-admin" . '/includes/media.php');
			}

            // X. Upload Conversation Image
			// if (isset($_FILES['image'])) {
			// 	if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
			// 		// Handle errors
			// 	}
			// 	$attach_id = media_handle_upload( 'image', $conversation_id );
			// 	if( ! is_wp_error( $attach_id ) ) {
			// 		update_post_meta($conversation_id,'_thumbnail_id',$attach_id);

			// 		$attachedimage = array(
			// 			'ID' => $attach_id,
			// 			'post_excerpt' => $imagetext,
			// 		);
			// 		// Update the post into the database
			// 		wp_update_post( $attachedimage );

			// 	}
			// }


			// X. Add meta_data for User
			update_user_meta( $user_id, 'alt_email', $alt_email );
			update_user_meta( $user_id, 'phone_number', $phone_number );
			update_user_meta( $user_id, 'description', $bio );

			// X. Upload Avatar Image
			if (isset($_FILES['avatar'])) {
				if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
					// Handle errors
				}
				$avatar_id = media_handle_upload( 'avatar', 0 );
				if( ! is_wp_error( $avatar_id ) ) update_user_meta( $user_id, 'wp_user_avatar', $avatar_id );
			}

			delete_post_meta( $conversation_id, '_conversation_password' );
			delete_post_meta( $conversation_id, '_conversation_token' );

			$headers[] = 'From: ' . if_empty( get_option( 'dn_site_name' ), 'DN.Åsikt' ) . ' <' . if_empty( get_option( 'smtp_user' ), 'asiktsredaktionen@dn.se' ) . '>';
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
			$message = "Ett nytt inlägg har kommit in!";
			$email = if_empty( get_option('notification_email'), get_option( 'admin_email' ) );
			wp_mail( $email, 'Notis: Nytt inlägg', $message, $headers );

			wp_redirect( get_permalink() . '?state=finished' );
			exit;
		}

	}
}
add_action( 'init', 'cb_submit_new_draft_post' );

function crop_image($url, $width, $height = null, $crop = null, $single = true) {

	//validate inputs
	if (!$url OR !$width)
	    return false;

	//define upload path & dir
	$upload_info = wp_upload_dir();
	$upload_dir = $upload_info['basedir'];
	$upload_url = $upload_info['baseurl'];

	//check if $img_url is local
	if (strpos($url, $upload_url) === false)
	    return false;

	//define path of image
	$rel_path = str_replace($upload_url, '', $url);
	$img_path = $upload_dir . $rel_path;

	//check if img path exists, and is an image indeed
	if (!file_exists($img_path) OR !getimagesize($img_path))
	    return false;

	//get image info
	$info = pathinfo($img_path);
	$ext = $info['extension'];
	list($orig_w, $orig_h) = getimagesize($img_path);

	//get image size after cropping
	$dims = image_resize_dimensions($orig_w, $orig_h, $width, $height, $crop);
	$dst_w = $dims[4];
	$dst_h = $dims[5];

	//use this to check if cropped image already exists, so we can return that instead
	$suffix = "{$dst_w}x{$dst_h}";
	$dst_rel_path = str_replace('.' . $ext, '', $rel_path);
	$destfilename = "{$upload_dir}{$dst_rel_path}-{$suffix}.{$ext}";

	if (!$dst_h) {
	//can't resize, so return original url
	    $img_url = $url;
	    $dst_w = $orig_w;
	    $dst_h = $orig_h;
	}
	//else check if cache exists
	elseif (file_exists($destfilename) && getimagesize($destfilename)) {
	    $img_url = "{$upload_url}{$dst_rel_path}-{$suffix}.{$ext}";
	}
	//else, we resize the image and return the new resized image url
	else {

	// Note: pre-3.5 fallback check
	    if (function_exists('wp_get_image_editor')) {

	        $editor = wp_get_image_editor($img_path);

	        if (is_wp_error($editor) || is_wp_error($editor->resize($width, $height, $crop)))
	            return false;

	        $resized_file = $editor->save();

	        if (!is_wp_error($resized_file)) {
	            $resized_rel_path = str_replace($upload_dir, '', $resized_file['path']);
	            $img_url = $upload_url . $resized_rel_path;
	        } else {
	            return false;
	        }
	    } else {

	        $resized_img_path = image_resize($img_path, $width, $height, $crop);
	        if (!is_wp_error($resized_img_path)) {
	            $resized_rel_path = str_replace($upload_dir, '', $resized_img_path);
	            $img_url = $upload_url . $resized_rel_path;
	        } else {
	            return false;
	        }
	    }
	}

	//return the output
	if ($single) {
	//str return
	    $image = $img_url;
	} else {
	//array return
	    $image = array(
	        0 => $img_url,
	        1 => $dst_w,
	        2 => $dst_h
	    );
	}

	return $image;
}
?>