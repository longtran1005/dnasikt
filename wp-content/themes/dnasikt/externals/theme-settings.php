<?php
	/**
	 * Create Admin Menu
	 */
	function lwst_manage_admin_menu() {
		add_menu_page( 'Theme Settings', 'Theme Settings', 'manage_options', 'lwst_theme_settings', 'cb_lwst_theme_settings_page', '', 1 );
	}
	add_action( 'admin_menu', 'lwst_manage_admin_menu' );

	/**
	 * Admin Page
	 */
	function cb_lwst_theme_settings_page() {
		?>
		<section class="section panel" id="poststuff" style="padding-right: 20px;">
			<h1>Inställningar för DN.Åsikter</h1>
			<form method="post" enctype="multipart/form-data" action="options.php">

				<?php wp_nonce_field('update-options') ?>
				<input type="hidden" name="action" value="update" />
	            <input type="hidden" name="page_options" value="<?php echo lwst_get_page_option_string(); ?>" />

				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
				</p>

				<?php lwst_print_options(); ?>

				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
				</p>

			</form>
		</section>
		<?php
	}

	function lwst_get_sections() {
		return array(
			array(
				'title' => 'Settings',
				'fields' => array(
					array(
						'type' => 'text',
						'name' => 'dn_site_name',
						'title' => 'Sidans namn',
						'default' => '',
						'description' => 'Ex. DN.Åsikt',
					),
					array(
						'type' => 'text',
						'name' => 'dn_site_url',
						'title' => 'Sidans url',
						'default' => '',
						'description' => 'Ex. asikt.dn.se',
					),
					array(
						'type' => 'text',
						'name' => 'notification_email',
						'title' => 'Notifiering E-post',
						'default' => '',
						'description' => 'E-postadress som ska få alla notisar',
					),
					array(
						'type' => 'textarea',
						'name' => 'info_conversation_vote_text',
						'title' => 'Hjälp-text rösta på fråga',
						'default' => 'Här sammanfattar skribenten sin huvudsakliga ståndpunkt där du som läsare kan rösta på om du håller med eller inte. Du kan när som helst ändra ditt svar. ',
						'description' => 'Hjälp-text för vad tycker du',
					),
					array(
						'type' => 'textarea',
						'name' => 'info_person_vote_text',
						'title' => 'Hjälp-text rösta på person',
						'default' => 'Du som läsare kan rösta på vilka personer du tycker ska vara med och svara i denna debatt! Du kan endast rösta på en person men du kan flytta din röst hur många gånger du vill. Flera personer kan läggas till under debattens gång.',
						'description' => 'Hjälp-text för rösta på person',
					),
					array(
						'type' => 'textarea',
						'name' => 'info_conversation_closed',
						'title' => 'Information om att debatten är avslutad',
						'default' => 'Debatten är avslutad.',
						'description' => 'Information om att debatten är avslutad',
					),
					array(
						'type' => 'textarea',
						'name' => 'info_conversation_closed_no_voting',
						'title' => 'Information om att röstningen är avslutad',
						'default' => 'Nu kan du inte rösta längre - debatten är stängd.',
						'description' => 'Information om att röstningen är avslutad',
					)
				)
			),
			array(
				'title' => 'Inställningar - Mobilnavigering',
				'fields' => array(
					array(
						'type' => 'text',
						'name' => 'mob_nav_question',
						'title' => 'URL till Om DN.Åsikt',
						'default' => 'http://asikt.dn.se/om-dn-asikt/',
						'description' => 'Knappen för frågetecken-ikonen',
					),
					array(
						'type' => 'text',
						'name' => 'mob_nav_pencil',
						'title' => 'URL till Jag vill skriva',
						'default' => 'http://asikt.dn.se/borja-skapa-ditt-inlagg/',
						'description' => 'Knappen för penna-ikonen',
					),
				)
			),
			array(
				'title' => 'E-postinställningar',
				'fields' => array(
					array(
						'type' => 'text',
						'name' => 'smtp_host',
						'title' => 'SMTP Host',
						'default' => '',
						'description' => '',
					),
					array(
						'type' => 'text',
						'name' => 'smtp_port',
						'title' => 'SMTP Port',
						'default' => '',
						'description' => '',
					),
					array(
						'type' => 'text',
						'name' => 'smtp_ssl',
						'title' => 'SMTP ssl',
						'default' => '',
						'description' => 'TLS eller SSL',
					),
					array(
						'type' => 'text',
						'name' => 'smtp_user',
						'title' => 'SMTP User',
						'default' => '',
						'description' => '',
					),
					array(
						'type' => 'text',
						'name' => 'smtp_pass',
						'title' => 'SMTP Password',
						'default' => '',
						'description' => '',
					),
				)
			),
		);
	}

	function lwst_print_options() {
		foreach(lwst_get_sections() as $section) : ?>
			<div class="postbox">
				<h3 style="border-bottom: 1px solid #E5E5E5;"><span><?php echo $section['title']; ?></span></h3>
				<div class="inside">
					<table class="form-table">
						<tbody>
							<?php
							foreach($section['fields'] as $field) {
								lwst_print_field($field);
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		<?php endforeach;
	}

	/**
	 * Call this to set default values to databas on theme activation
	 */
	function lwst_set_default_theme_options() {
		foreach(lwst_get_sections() as $section) {
			foreach($section['fields'] as $field) {
				if(isset($field['default'])) {
					$value = get_option( $field['name'] );
					if($value == "" OR empty($value)) {
						update_option( $field['name'], $field['default'] );
					}
				}
			}
		}
	}

	function lwst_get_page_option_string() {
		$str = "";
		foreach(lwst_get_sections() as $section) {
			foreach($section['fields'] as $field) {
				if($field['type'] == 'text_between') {
					foreach($field['fields'] as $f) {
						$str .= "," . $f['name'];
					}
				} else {
					$str .= "," . $field['name'];
				}

			}
		}
		return ltrim($str, ',');
	}
	function lwst_print_field($field) {
		switch($field['type']) {
			case 'text' :
			?>
				<tr valign="top">
					<th scope="row"><label title="<?php echo $field['name'] ?>" for="<?php echo $field['name'] ?>"><?php echo $field['title'] ?></label></th>
					<td>
						<input type="<?php echo $field['type'] ?>" title="<?php echo $field['name'] ?>" id="<?php echo $field['name'] ?>" name="<?php echo $field['name'] ?>" class="regular-text" value="<?php echo esc_attr( get_option($field['name']) ) ?>"> <small><?php if(isset($field['default'])) echo "Standard: " . $field['default']; ?></small>
						<p class="description"><?php echo $field['description'] ?></p>
					</td>
				</tr>
			<?php
			break;
			case 'textarea' :
			?>
				<tr valign="top">
					<th scope="row"><label title="<?php echo $field['name'] ?>" for="<?php echo $field['name'] ?>"><?php echo $field['title'] ?></label></th>
					<td>
						<textarea title="<?php echo $field['name'] ?>" id="<?php echo $field['name'] ?>" name="<?php echo $field['name'] ?>" cols="70" rows="3"><?php echo esc_attr( get_option($field['name']) ) ?></textarea> <small><?php if(isset($field['default'])) echo "Standard: " . $field['default']; ?></small>
						<p class="description"><?php echo $field['description'] ?></p>
					</td>
				</tr>
			<?php
			break;
			case 'text_between' :
			?>
				<tr valign="top">
					<th scope="row"><label><?php echo $field['title'] ?></label></th>
					<td>
						<?php foreach($field['fields'] as $f): ?>
							<small><?php echo $f['title'] ?>: </small><input type="<?php echo $f['type'] ?>" title="<?php echo $f['name'] ?>" id="<?php echo $f['name'] ?>" name="<?php echo $f['name'] ?>" class="small-text" value="<?php echo esc_attr( get_option($f['name']) ) ?>">&nbsp;&nbsp;
						<?php endforeach; ?>
					</td>
				</tr>
			<?php
			break;
		}
	}
?>