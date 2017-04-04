<?php
/*
Template Name: Submitpage
*/
get_header();
if ( have_posts() ): while ( have_posts() ) : the_post(); ?>
	<script>
	// Ful hack
	$(document).ready(function() {
		(function() {

			$("#thecontent").prop("required", true);

		})();
	});
	</script>
	<div class="row">
		<?php get_template_part( 'templates/ad', 'banner' ); ?>
		<main class="col-sm-8 col-md-9">

			<?php
			$cid 		= ( isset( $_GET['cid'] ) ) ? $_GET['cid'] : null ;
			$pid 		= ( isset( $_GET['pid'] ) ) ? $_GET['pid'] : null ;
			$token 		= ( isset( $_GET['token'] ) ) ? $_GET['token'] : null ;
			$state 		= ( isset( $_GET['state'] ) ) ? $_GET['state'] : null ;

			$is_finished 			= ( $state == 'finished' );
			$sessions				= ( isset( $_SESSION['add_reply'] ) ) ? $_SESSION['add_reply'] : array() ;
			$is_cid_reply 			= ( $cid AND $token AND ! $pid AND ! $is_finished  );
			$is_pid_reply 			= ( $pid AND ! $cid AND ! $token AND ! $is_finished   );
			$is_new_conversation 	= ( ! $is_pid_reply AND ! $is_cid_reply AND ! $is_finished  );

			if( $state == 'finished' ) : ?>

				<h1><?php echo if_empty( get_post_meta( $post->ID, '_state_finished_title', true ),'Tack för ditt inlägg') ?></h1>
				<?php echo if_empty(apply_filters( 'the_content', get_post_meta( $post->ID, '_state_finished_content', true ) ),'Vi kommer att granska ditt inlägg och återkommer om vi publicerar det.') ?>

			<?php endif;

			// LÖSENORD SKYDDAD SVARSIDA
			if( $is_cid_reply ) :
				$current_post = get_post( $cid );
				$reply_post = get_post( $current_post->post_parent );
				$current_user	= get_userdata( $current_post->post_author );

				?>
				<?php if( ! in_array( $cid, $sessions ) ) : ?>
					<div class="row">
						<div class="col-sm-8 col-sm-offset-2 col-xs-10 col-xs-offset-1 grey-box login-reply-container">
							<form action="" method="POST">
								<h1><?php echo if_empty( get_post_meta( $post->ID, '_state_cid_title', true ),'Logga in för att svara') ?></h1>
								<div>Hej <?php echo $current_user->display_name ?></div>
								<?php echo if_empty(apply_filters( 'the_content', get_post_meta( $post->ID, '_state_cid_content', true ) ),'Skriv ditt lösenord du fick i ditt epost-meddelande där du öppnade denna länk från för att logga in') ?>
								<div class="input-group">
									<span class="input-group-addon input-lg"><i class="fa fa-lock"></i></span>
									<input type="text" name="password" class="form-control input-lg">
								</div>
								<button type="submit" name="confirm_conversation_password" class="btn btn-lg btn-info btn-block btn-center" value="Logga in">Logga in</button>
							</form>
						</div>
					</div>
				<?php
				else :

					get_template_part( 'templates/submit', 'form' );

				endif;
			endif; ?>

			<?php
			// JAG VILL SVARA
			if( $is_pid_reply ) :
				if( $state != 'working' || ! is_user_logged_in()  ) : ?>
					<h1><?php echo if_empty( get_post_meta( $post->ID, '_state_pid_title', true ),'(PID) Innan du börjar') ?></h1>
					<?php echo if_empty(apply_filters( 'the_content', get_post_meta( $post->ID, '_state_pid_content', true ) ),'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Tenetur dolor omnis quibusdam, odit voluptas accusamus. Quam quibusdam, exercitationem? Facere praesentium quibusdam libero, id. Facilis rerum tempora officiis voluptatem repellat, soluta.') ?>
					<?php get_template_part( 'templates/submit', 'continue-login' ); ?>
				<?php
				else :

					$reply_post = get_post( $pid );
					$current_user	= get_userdata( get_current_user_id() );
					get_template_part( 'templates/submit', 'form' );

				endif;
			endif; ?>

			<?php
			// SKAPA NY KONVERSATION
			if( $is_new_conversation ) : ?>
				<?php if( $state != 'working' || ! is_user_logged_in() ) : ?>
					<h1><?php the_title(); ?></h1>
					<?php the_content(); ?>
					<?php get_template_part( 'templates/submit', 'continue-login' ); ?>
				<?php
				else :

					$current_user	= get_userdata( get_current_user_id() );
					get_template_part( 'templates/submit', 'form' );

				endif ;
			endif ; ?>

		</main>
		<?php get_sidebar(); ?>
	</div>
	<script id="tmp--add_new_person_field" type="text/template">
		<div class="form-group repeatable_field grey-box">
			<div class="row inner">
				<span class="js--remove_field--person remove"><i class="fa fa-times"></i></span>
				<div class="col-sm-8">
					<div class="form-horizontal">
						<div class="form-group">
							<label for="inputEmail3" class="col-sm-3 control-label">Namn</label>
							<div class="col-sm-9">
								<div class="input-group">
									<div class="input-group-addon"><i class="fa fa-user"></i></div>
								<input type="text" name="suggestions[name][]" class="form-control" id="" placeholder="Namn" required>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Kontakt</label>
							<div class="col-sm-9">
								<div class="input-group">
									<div class="input-group-addon"><i class="fa fa-link"></i></div>
									<input type="text" name="suggestions[contact][]" class="form-control" id="" placeholder="Ex. e-post, twitter" required>
								</div>
								<div class="help-block">Gärna specifik information så vi kan kontakta personen.</div>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Motivering</label>
							<div class="col-sm-9">
								<div class="input-group">
									<div class="input-group-addon"><i class="fa fa-comment"></i></div>
									<textarea name="suggestions[motivation][]" id="" rows="3" class="form-control" placeholder="Skriv något om varför den här personen bör svara på debatten" required></textarea>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</script>

	<script id="tmp--person_vote_preview" type="text/template">
		<li>
			<span class="preview-person-name"></span>
			<span class="preview-person-contact meta"></span>
			<span class="preview-person-motivation"></span>
		</li>
	</script>

<?php endwhile; else: ?>

	<?php get_template_part( 'templates/post', 'not-found' ); ?>

<?php endif; ?>

<?php get_footer(); ?>