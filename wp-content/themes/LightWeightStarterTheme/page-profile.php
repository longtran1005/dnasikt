<?php
/*
Template Name: Profile Page
*/
get_header(); ?>
	<div class="row">
		<?php get_template_part( 'templates/ad', 'banner' ); ?>
		<main class="col-sm-8 col-md-9">
			<div class="profile">
			<?php if( is_user_logged_in() ) : ?>
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			<!-- post -->
				<?php
					$user = get_userdata( get_current_user_id() );
				?>
				<?php if( isset( $_GET['edit'] ) ) : ?>
					<h1>Redigera profil</h1>
					<form action="" method="post" enctype="multipart/form-data">
						<?php wp_nonce_field( 'update_profile_nonce' ); ?>
						<input type="hidden" name="redirect" value="<?php echo wp_current_url(); ?>">
						<input type="hidden" name="submit_update_profile" value="1">
						<div class="form-group">
							<label for="">Visningsnamn</label>
							<input type="text" class="form-control" name="display_name" id="display_name" value="<?php echo if_empty( $user->display_name, '' ) ?>" readonly>
						</div>
						<div class="form-group">
							<label for="">E-postadress</label>
							<input type="text" class="form-control" name="email" value="<?php echo if_empty( $user->user_email, '' ) ?>" readonly>
							<div class="help-block"><p>Kontakta kundtjänst om du vill ändra din primära e-postadress</p></div>
						</div>
						<div class="form-group">
							<label for="">Alt e-postadress</label>
							<input type="text" class="form-control" name="alt_email" value="<?php echo user_meta( get_current_user_id(), 'alt_email', '' ) ?>">
						</div>
						<div class="form-group">
							<label for="">Telefonnummer</label>
							<input type="text" class="form-control" name="phone_number" value="<?php echo user_meta( get_current_user_id(), 'phone_number', '' ) ?>">
						</div>
						<div class="form-group">
							<label for="">Några meningar om dig själv</label>
							<textarea name="bio" id="" rows="4" class="form-control"><?php echo $user->description ?></textarea>
						</div>
						<div class="form-group">
							<label for="">Profilbild</label>
							<input type="file" name="avatar" class="form-control" id="user-avatar">
						</div>
						<div class="form-group">
							<div class="checkbox">
								<label>
									<input type="checkbox" name="no_email_notifications" <?php if(user_meta( get_current_user_id(), 'no_email_notifications', '')) echo 'checked'; ?>> Jag vill <strong>inte</strong> ha några e-postutskick när nytt material kommer ut som jag följer.
								</label>
							</div>
						</div>
						<div class="form-group text-right">
							<a href="<?php echo strtok( wp_current_url(), '?'); ?>" class="btn btn-dn"  onclick="dataLayer.push({event: 'customEvent',eventCategory:'Profile',eventAction:'Cancel Edit'});">Avbryt</a>
							<input type="submit" class="btn btn-primary" id="submit_update_profile" value="Spara inställningar"  onclick="dataLayer.push({event: 'customEvent',eventCategory:'Profile',eventAction:'Save profile'});">
						</div>
					</form>
				<?php else : ?>
					<?php if( isset( $_GET['status'] ) ) : ?>
						<?php if( $_GET['status'] == 'profile_updated') : ?>
							<div class="alert alert-success">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<strong>Uppdaterad</strong> Dina ändringar sparades utan några problem.
							</div>
						<?php endif; ?>
					<?php endif; ?>
					<div class="row">
						<div class="col-sm-4">
							<div class="avatar-holder">
								<img class="img-circle" src="<?php echo get_wp_user_avatar_src( get_current_user_id(), 'thumbnail' ); ?>" alt="">
							</div>
							<ul class="list">
								<li><a href="?edit" onclick="dataLayer.push({event: 'customEvent',eventCategory:'Profile',eventAction:'Click Edit'});">Redigera profil</a></li>
								<li><a href="https://kund.dn.se/mitt-konto/" target="_blank" onclick="dataLayer.push({event: 'customEvent',eventCategory:'Profile',eventAction:'Customerservice from Profile'});">Till kundservice</a></li>
							</ul>
						</div>
						<div class="col-sm-8">
							<div class="header double">
								<div class="sub-heading red">Min profil</div>
								<h2><?php echo $user->display_name; ?></h2>
							</div>
							<p><strong>E-postadress:</strong> <?php echo $user->user_email; ?></p>
							<p><strong>Alt. e-postadress:</strong> <?php echo user_meta( get_current_user_id(), 'alt_email', 'Ingen angiven' ); ?></p>
							<p><strong>Telefon:</strong> <?php echo user_meta( get_current_user_id(), 'phone_number', 'Inget angivet' ); ?></p>
							<?php echo $user->description; ?>

						</div>
					</div>
					<hr>
					<div class="header double">
						<div class="sub-heading red">Saker du har gjort</div>
						<h2 class="h1">Inlägg</h2>
					</div>
					<div class="row">
						<?php
						$args = array(
							'author'      =>  get_current_user_id(),
							'post_type'   => 'asikt',
						);
						$query = new WP_Query( $args );
						$i = 1; if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); $post = $query->post;  ?>
							<div class="col-sm-6">
								<span class="label-heading-red"><?php echo get_subject( $post->post_parent ?: $post->ID ); ?></span>
								<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<?php the_excerpt(); ?>
							</div>
							<?php if( $i % 2 == 0 ) echo '<div class="clearfix"></div>'; ?>
						<?php $i++; endwhile; ?>

						<?php else: ?>
							<div class="col-sm-12"><p>Du har inte skrivit några inlägg ännu. <a href="<?php echo get_custom_page( array( 'template' => 'page-submit-conversation.php' ) ) ?>"><strong>Tryck här för att skapa ditt första inlägg</strong></a></p></div>
						<?php endif; wp_reset_postdata(); ?>

					</div>

					<h2 class="h1">Din åsikt</h2>
					<?php
					$sql = $wpdb->prepare(
						"SELECT vote,conversation_id FROM conversation_votes WHERE user_id = %d",
						get_current_user_id()
						);
					$results = $wpdb->get_results( $sql );

					?>
					<?php if( count( $results ) == 0 ) : ?>
						<p>Du har inte röstat på något inlägg ännu.</p>
					<?php endif; ?>
					<?php foreach( $results as $row ) : $post = get_post( $row->conversation_id ); setup_postdata( $post ); ?>
						<div class="media">
							<div class="pull-left">
								<img class="img-circle" src="<?php echo get_wp_user_avatar_src( $post->post_author, 'thumbnail' ); ?>" alt="">
							</div>
							<div class="media-body">
								<span class="label-heading-red"><?php echo get_subject( get_the_id() ); ?></span>
								<h3><a href="<?php the_permalink(); ?>"><?php the_author(); ?>: <?php the_title(); ?></a></h3>
								<?php the_excerpt(); ?>
								<?php if( $row->vote ) : ?>
									<p><i class="fa fa-check-circle-o"></i>&nbsp;&nbsp;<strong>Jag håller med</strong> <?php the_author(); ?>:s åsikt</p>
								<?php else: ?>
									<p><i class="fa fa-times"></i>&nbsp;&nbsp;Jag håller <strong>INTE</strong> med <?php the_author(); ?>:s åsikt</p>
								<?php endif; ?>
							</div>
						</div>

					<?php endforeach; wp_reset_postdata(); ?>

					<h2 class="h1">Personröster</h2>
					<?php
					$sql = $wpdb->prepare(
						"SELECT r.user_id, r.conversation_id, r.motivation, (SELECT COUNT(rv2.id) FROM replies_votes rv2 WHERE rv2.reply_id = r.id) as vote FROM replies r JOIN replies_votes rv ON (r.id = rv.reply_id) WHERE rv.user_id = %d",
						get_current_user_id()
						);
					$results = $wpdb->get_results( $sql );
					?>
					<?php if( count( $results ) == 0 ) : ?>
						<p>Du har inte röstat på någon person ännu.</p>
					<?php endif; ?>
					<?php foreach( $results as $row ) : $user = get_userdata( $row->user_id ); ?>
						<div class="media">
							<div class="pull-left">
								<img class="img-circle" src="<?php echo get_wp_user_avatar_src( $post->user_id, 'thumbnail' ); ?>" alt="">
							</div>
							<div class="media-body">
								<span class="label-heading-red"><?php echo get_subject( $row->conversation_id ); ?></span>
								<h3><?php echo $user->display_name; ?> (+<?php echo $row->vote ?>)</h3>
								<p><?php echo $row->motivation; ?></p>
								<p><a href="<?php echo get_permalink( $row->conversation_id ); ?>"><strong>Besök debatten</strong></a></p>
							</div>
						</div>
					<?php endforeach; ?>

				<?php endif; // isset( $_GET['edit'] ) ?>

			<?php endwhile; ?>
			<!-- post navigation -->
			<?php else: ?>
			<!-- no posts found -->
			<?php endif; ?>
			<?php else: ?>
				<?php get_template_part( 'templates/post', 'not-found' ); ?>
			<?php endif; ?>
			</div>
		</main>
		<?php get_sidebar(); ?>
	</div>


<?php get_footer(); ?>