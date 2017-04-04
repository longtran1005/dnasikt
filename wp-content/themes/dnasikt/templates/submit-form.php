<?php
global $is_new_conversation,$is_cid_reply,$is_pid_reply,$current_user,$reply_post;
?>
	<?php  if ( $reply_post ) :
		// Setup postdata var
		$post = $reply_post;
		setup_postdata( $post );
		?>
		<div class="grey-box">
			<div class="row inner">
				<?php if(get_thumb( $post->ID )) :  ?>
				<div class="col-sm-3">
					<img class="img-responsive thumbnail" src="<?php echo get_thumb( $post->ID ) ?>" alt="">
				</div>
				<?php endif ?>
				<div class="col-sm-9">
					<div class="header double">
						<span class="sub-heading red">Du svarar på följande inlägg:</span>
						<h2><?php the_title(); ?></h2>
					</div>
					<span class="inline-link"><?php the_excerpt(); ?> <a href="<?php the_permalink(); ?>" target="_blank">Se hela inlägget</a></span>
				</div>
			</div>
		</div>
	<?php
	// Reset the postdata var
	wp_reset_postdata();
	endif;
	?>
<form action="" method="POST" role="form" enctype="multipart/form-data" id="submit-conversation-form" > <!-- FORM START -->

	<h2>Skriv ditt inlägg</h2>
	<div class="form write-post">

		<div class="form-section post-content">
			<input type="hidden" name="submit_new_draft_post">

			<div class="form-group">
				<label for="conversation-title" class="control-label">Rubrik</label>
				<input type="text" name="title" class="form-control input-lg" id="conversation-title" placeholder="Min debattartikel" required>
			</div>

			<div class="form-group">
				<label for="thecontent" class="control-label">Text</label>
				<style>.wp-editor-container { border: 1px solid #ccc; }</style>
				<?php wp_editor( '', 'thecontent', array(
					'media_buttons' => false,
					'editor_height' => 400,
					'quicktags' => false,
					'tinymce' => array(
						'block_formats' => "Stycke=p; Rubrik 1=h2; Rubrik 2=h3;",
						'toolbar1' => 'bold,italic,underline,bullist,numlist,blockquote',
						'toolbar2' => 'formatselect',
						'wpautop' => false,
						)
					)
				); ?>
			</div>
			<div class="form-group">
				<label for="conversation-summary" class="control-label">Sammanfattning</label>
				<textarea class="form-control" name="summary" id="conversation-summary" rows="3" placeholder="Skriv en kort sammanfattning av ditt inlägg och formulera din ståndpunkt" required></textarea>
				<?php if($is_new_conversation) : ?>
					<div class="help-block">Skriv en kort sammanfattning av ditt inlägg och formulera din ståndpunkt. Andra läsare kommer rösta på din ståndpunkt (håller med/håller inte med) så formulera den så tydligt som möjligt. Sammanfattningen och ståndpunkten kommer synas i samband med omröstningen "Håller du med?".</div>
				<?php else : ?>
					<div class="help-block">Skriv en kort sammanfattning om ditt inlägg.</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<!-- // WRITE POST -->


	<h2>Personliga uppgifter</h2>
	<div class="form personal-details">

		<div class="form-section author-content">
			<p class="tip ff-sans-light"><strong class="ff-sans-bold">Tips!</strong> Du kommer själv stå som avsändare för debatten. Det är bra om du skriver några rader om dig själv. Kanske varför du väljer att att skriva om just detta ämne eller vilka ämnen du brinner för och varför. Lägg gärna till en bild på dig själv. Om du inte har någon bild kommer en tom profil-bild att visas</p>

			<h3 id="user-name"><?php echo $current_user->display_name; ?></h3>
			<div class="row">
				<div class="col-sm-2">
					<img class="img-responsive thumbnail" id="user-avatar-element" src="<?php echo get_wp_user_avatar_src( $current_user->ID ); ?>" alt="">
				</div>
				<div class="col-sm-10">
					<div class="form-group">
						<textarea name="bio" id="user-bio" class="form-control" rows="3" placeholder="Skriv några rader om dig själv" required><?php echo user_meta($current_user->ID, 'description', ''); ?></textarea>
					</div>
				</div>
			</div>
			<div class="form-group">
				<div class="form-group">
					<input type="file" name="avatar" class="form-control" id="user-avatar">
				</div>
			</div>
		</div>

		<div class="form-section contact-content">
			<p class="tip ff-sans-light"><strong class="ff-sans-bold">Obs!</strong> DN kommer att kontakta dig innan vi publicerar din åsikt. Dina kontaktuppgifter kommer inte att synas för läsarna och kommer inte spridas till tredje part. DN ringer upp alla som publiceras på DN.åsikt, därför behöver vi ditt telefonnummer. Du kan lägga till en annan e-postadress om du vill att vi kontaktar dig på annat sätt än den vi har registrerat sedan tidigare.</p>
			<div class="row">
				<div class="col-sm-6">
					<div class="form-group">
						<label for="email">E-post</label>
						<input type="text" name="email" class="form-control" id="user-email" placeholder="Ex. namn@domän.se" value="<?php echo $current_user->user_email; ?>" readonly>
					</div>
					<div class="form-group">
						<input type="text" name="alt_email" class="form-control" id="user-alt-email" placeholder="Ex. namn@domän.se" value="<?php echo user_meta($current_user->ID,'alt_email', ''); ?>" style="display:none">
					</div>
					<a href="#" class="help-block js--toggle_element" data-id="#user-alt-email" data-text="- Jag vill inte lägga till en elternativ e-post">+ Lägg till alternativ e-post</a>
				</div>
				<div class="col-sm-6 form-group">
					<label for="phone_number">Telefonnunmmer</label>
					<input type="text" name="phone_number" class="form-control" id="user-phone-number" placeholder="Ex. 0700 01 01 01" value="<?php echo user_meta($current_user->ID,'phone_number', ''); ?>">
				</div>
			</div>

			<div class="form-group">
				<label for="message-to-dn">Meddelande till oss på redaktionen</label>
				<textarea name="message_to_dn" class="form-control" rows="3" placeholder="Ditt meddelande" id="§"></textarea>
				<p class="help-block">Här kan du skriva ett meddelande till redaktionen om det är något speciellt som redaktionen behöver veta om ditt inlägg. Detta är inget som kommer synas för andra läsare. </p>
			</div>
		</div>
	</div>
	<!-- // PERSONAL DETAILS -->

	<?php if($is_new_conversation): ?>
		<h2>Lägg till personer</h2>
		<div class="form add-persons">
			<div class="form-section persons-content">
				<p class="tip ff-sans-light"><strong class="ff-sans-bold">Tips!</strong> Här lägger du till en eller flera personer som du vill ska svara på din åsikt och som läsarna ska kunna rösta på.</p>
				<p class="tip ff-sans-light"><strong class="ff-sans-bold">Obs!</strong> Läsarna av debatten kommer sedan kunna skicka in egna förslag på ytterligare personer som kan röstas fram att svara.</p>

				<div class="repeatable_fields">
					<div class="form-group repeatable_field grey-box">
						<div class="row inner">
							<?php /* <span class="js--remove_field--person remove"><i class="fa fa-times"></i></span> */ ?>
							<div class="col-sm-8">
								<div class="form-horizontal">
									<div class="form-group">
										<label for="inputEmail3" class="col-sm-3 control-label">Namn</label>
										<div class="col-sm-9">
											<div class="input-group">
												<div class="input-group-addon"><i class="fa fa-user"></i></div>
											<input type="text" name="suggestions[name][]" class="form-control" id="" placeholder="Namn">
											</div>
										</div>
									</div>
									<div class="form-group">
										<label for="" class="col-sm-3 control-label">Kontakt</label>
										<div class="col-sm-9">
											<div class="input-group">
												<div class="input-group-addon"><i class="fa fa-link"></i></div>
												<input type="text" name="suggestions[contact][]" class="form-control" id="" placeholder="Ex. e-post, twitter">
											</div>
											<div class="help-block">Gärna specifik information så vi kan kontakta personen.</div>
										</div>
									</div>
									<div class="form-group">
										<label for="" class="col-sm-3 control-label">Motivering</label>
										<div class="col-sm-9">
											<div class="input-group">
												<div class="input-group-addon"><i class="fa fa-comment"></i></div>
												<textarea name="suggestions[motivation][]" id="" rows="3" class="form-control" placeholder="Skriv något om varför den här personen bör svara på debatten"></textarea>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<button class="btn btn-dn icon dnicon-plus btn-xs js--add_new_person_field">Lägg till fler personer</button>
			</div>
		</div>


	<?php endif; ?>

	<h2>Granska</h2>
	<div class="form preview">
		<div id="preview">

			<p class="tip ff-sans-light">
				<strong class="ff-sans-bold">Obs!</strong> Det är viktigt att du granskar ditt inlägg innan du godkänner och skickar in det. Behöver du ändra något kan du enkelt stega tillbaka med knapparna.
			</p>
			<?php /*
			<!-- BILD -->
			<div class="form-section-preview">
				<div class="preview-image">
					<img class="img-responsive preview-featured-image" src="http://fpoimg.com/600x300?text=Featured+Image" alt="">
					<div class="preview-image-text"></div>
				</div>
			</div>
				*/ ?>
			<div class="form-section-preview">
				<h1 class="preview-title">Min titel</h1>

				<!-- <p class="ff-sans-light preview-summary">Du kommer själv stå som avsändare för debatten. Det är bra om du skriver några rader om dig själv. Kanske varför du väljer att att skriva om just detta ämne eller vilka ämnen du brinner för och varför. Lägg gärna till en bild på dig själv. Om du inte har någon bild kommer en tom profil-bild att visas</p> -->
				<div class="preview-content"><p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Alias omnis repellendus error ipsum vel dignissimos, necessitatibus labore expedita ullam. Animi commodi voluptas veniam unde deserunt iste totam, fuga est facere!</p></div>

				<div class="preview-author">
					<div class="header double">
						<span class="sub-heading red">Författare</span>
						<h3 class="preview-author-name">Anna Andersson</h3>
						<div class="media">
							<div class="pull-left preview-author-avatar">

							</div>
							<div class="media-body">
								<p class="preview-author-bio">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Natus ratione, reiciendis veniam perferendis nulla in similique tempore quasi ut, optio necessitatibus veritatis, molestiae accusamus repellat! Perspiciatis voluptate repudiandae fugiat dolore!</p>
							</div>
						</div>
					</div>
				</div>
			</div>

			<?php if($is_new_conversation): ?>
				<div class="form-section-preview">
					<div class="grey-box">
						<div class="inner">
							<p class="ff-sans-light preview-summary">Du kommer själv stå som avsändare för debatten. Det är bra om du skriver några rader om dig själv. Kanske varför du väljer att att skriva om just detta ämne eller vilka ämnen du brinner för och varför. Lägg gärna till en bild på dig själv. Om du inte har någon bild kommer en tom profil-bild att visas</p>
							<?php the_conversation_votes_control_html(); ?>
						</div>
						<div class="inner">
							<?php the_conversation_votes_html(); ?>
						</div>
					</div>
				</div>

				<div class="form-section-preview preview-person-list">
					<div class="grey-box vote-persons">
						<div class="header">
							<h3>Vem ska svara?</h3>
						</div>
						<ul class="list">

						</ul>
					</div>
				</div>

			<?php endif; ?>

		</div>
		<!-- /#preview -->

		<div class="form-section">
			<div class="grey-box">
				<div class="inner">
					<div class="checkbox">
						<label>
							<input type="checkbox" id="accept-tos"> Jag har läst och godkänner härmed DN:s villkor och försäkrar att min text följer DN:s publiceringsregler
						</label>
						<p class="inline-link"><a href="#" data-toggle="modal" data-target="#disclaimer">Läs mer här</a></p>
					</div>
				</div>
			</div>
		</div>


	</div>
	<!-- // FORM PREVIEW -->
</form>
<!-- // FORM -->

<?php
// Gets our disclaimer page
$disclaimer = get_posts( array(  'name' => 'anvandarvillkor', 'post_type' => 'page' ) );
if(count($disclaimer) > 0) :
?>
	<!-- Modal -->
	<div class="modal fade" id="disclaimer" role="dialog">
		<div class="modal-dialog">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title"><?php echo apply_filters( 'the_title', $disclaimer[0]->post_title ); ?></h4>
			</div>
			<div class="modal-body">
				<?php echo apply_filters( 'the_content', $disclaimer[0]->post_content ); ?>
			</div>
		</div>

		</div>
	</div>
<?php endif; ?>
