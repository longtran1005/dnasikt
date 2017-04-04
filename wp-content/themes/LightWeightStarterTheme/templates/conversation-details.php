<?php if ( have_posts() ): while ( have_posts() ) : the_post(); ?>

<article class="conversation">

	<?php
	// Setup page vars
	$conversation_subject = if_empty( get_post_meta( $post->post_parent ?: $post->ID, '_conversation_subject', true ), 'Saknar ämne...' );
	$is_parent 	= ( $post->post_parent == '0' );

	if( ! $is_parent ) {
		$parent_obj = get_post( $post->post_parent );
		$parent_author = get_userdata( $parent_obj->post_author );
	}

	$repliers = get_repliers( $post->post_parent ?: $post->ID );
	// Sort the array with highest votes
	$sorted_repliers = json_decode(json_encode($repliers),true); // Convert stdClass -> Array
	usort($sorted_repliers, function($a, $b) {
	    return $b['votes'] - $a['votes'];
	});
	?>
	<div class="col-md-offset-2">
		<div class="header double">
			<span class="label-heading-red"><?php echo $conversation_subject ?></span>
			<h1 class="conversation-title article"><?php the_author(); ?>: <?php the_title(); ?></h1>
		</div>
	</div>
	<?php if ( $featured_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'dn-featured') ): ?>
		<!-- Featured Image -->
		<div class="featured-image">
			<img src="<?php echo $featured_image[0]; ?>" alt="" class="img-responsive" />
			<p class="text"><?php echo if_empty(get_post(get_post_thumbnail_id())->post_excerpt, 'Foto: DN'); ?></p>
		</div>
	<?php endif; ?>


	<div class="col-md-offset-2 conversation-body">
		<div class="content clearfix">
			<div>
				<span class="date">Publicerad: <?php the_date(); ?></span>
			</div>
			<div class="excerpt ff-serif-headline-black">
				<?php the_excerpt(); ?>
			</div>

			<?php
			//get votes percent etc
			$votes = get_conversation_votes_and_procent( $post->post_parent ?: $post->ID );
			$votesup = $votes['thumb_up_procent'];
			$votesdown = $votes['thumb_down_procent'];
			$result_none = false;

			if (($votesup + $votesdown) == 0) {
				$icon = 'vote-up';
				$result_none = true;
			}
			elseif ($votesup >= $votesdown) {
				$votes_total = $votesup;
				$icon = 'vote-up';
				$is_negative = '';
			}
			else {
				$votes_total = $votesdown;
				$icon = 'vote-down red';
				$is_negative = ' inte';
			}
			?>

				<div class="article-summary clearfix">
				<?php if( $is_parent ) : ?>
					<div class="vote updown">
						<a href="#conversation-vote" class="js--anchor_link span-link updown">
							<span class="question updown text-center">
								<i class="summary-icon dnicon-question background"></i>
								<span class="link">Vad tycker du?</span>
							</span>
							<span class="summary updown text-center">
								<i class="summary-icon dnicon-<?php echo $icon ?>"></i>
								<span class="summary-text">
									<?php if ($result_none): ?>
										<span>Ingen har röstat!</span>
									<?php else :  ?>
										<span class="count updown"><?php echo $votes_total ?>%</span> håller<?php echo $is_negative ?> med
										<span class="author"><?php the_author(); ?></span>
									<?php endif; ?>
								</span>
							</span>
						</a>
					</div>
				<?php else : ?>
					<div class="vote reply">
						<h2>Svar till <?php echo $parent_author->display_name; ?></h2>
						<p>Detta är ett svar på huvudinlägget på debatten "<?php echo $conversation_subject; ?>". <a href="<?php echo get_permalink( $post->post_parent ) ?>" class="line link">Till huvudinlägget</a></p>
					</div>
				<?php endif; ?>

					<div class="vote persons">
						<a href="#vote-person" class="js--anchor_link span-link updown">
							<span class="question updown text-center">
								<i class="summary-icon dnicon-question background"></i>
								<span class="link">Vilka ska svara?</span>
							</span>
							<span class="summary updown text-center">
								<i class="summary-icon dnicon-person"></i>
								<span class="summary-text">
									<?php if( ! isset( $sorted_repliers[0] ) ) : ?>
										<span>Lägg till en person</span>
									<?php else : ?>
										<?php if( $sorted_repliers[0]['votes'] >= 10 ) : ?>
											<span class="count persons"><?php echo $sorted_repliers[0]['votes'] ?></span> röstar på
											<span class="person"><?php echo $sorted_repliers[0]['display_name'] ?></span>
										<?php else: ?>
											<span>Rösta nu</span>
										<?php endif; ?>
									<?php endif ; ?>
								</span>
							</span>
						</a>
					</div>
				</div>

			<?php
				if( is_following_post( get_the_id() )[0] ) {
					$follow_btn_text = "Följer denna debatt";
					$follow_btn_attr = "Följ debatt";
					$follow_class = "following";
				} else {
					$follow_btn_text = "Följ debatt";
					$follow_btn_attr = "Följer denna debatt";
					$follow_class = "";
				}
			?>


			<div class="share-wrapper">
				<div class="share-btn share-facebook">
					<a class="js--share_this" href="http://www.facebook.com/sharer.php?u=<?php the_permalink(); ?>" data-id="<?php echo $post->ID; ?>" data-source="facebook" data-nonce="<?php echo wp_create_nonce( 'share-this' ); ?>">
						<span class="text">Facebook</span>
						<span class="count"><?php echo if_empty( get_post_meta( $post->ID, '_facebook_share', true ), 0 ); ?></span>
					</a>
				</div>
				<div class="share-btn share-twitter">
					<a class="js--share_this" href="https://twitter.com/intent/tweet?text=<?php the_title(); ?>&url=<?php the_permalink(); ?>" data-id="<?php echo $post->ID; ?>" data-source="twitter" data-nonce="<?php echo wp_create_nonce( 'share-this' ); ?>">
						<span class="text">Twitter</span>
						<span class="count"><?php echo if_empty( get_post_meta( $post->ID, '_twitter_share', true ), 0 ); ?></span>
					</a>
				</div>
				<div class="share-btn share-follow <?php echo $follow_class; ?>">
					<a href="#" class="js--follow_post" data-nonce="<?php echo wp_create_nonce( "follow_post_nonce" ); ?>" data-id="<?php echo $post->post_parent ?: $post->ID; ?>" data-text="<?php echo $follow_btn_attr; ?>">
						<span class="text"><?php echo $follow_btn_text; ?></span>
						<span class="icon"></span>
					</a>
				</div>
			</div>

			<?php the_content(); ?>
		</div>

		<div class="author">
			<div class="header double">
				<span class="sub-heading border">Om skribenten</span>
			</div>
			<?php if($anonymous = get_post_meta( $post->ID, '_author_anonymous', true )) : ?>
				<img src="<?php echo get_avatar_url( 'unknowed@gravatar.com' )  ?>" alt="author" class="author-image">
			<?php else : ?>
				<img src="<?php echo get_wp_user_avatar_src( $post->post_author, 'thumbnail' ); ?>" alt="author" class="author-image"/>
			<?php endif; ?>
			<div class="author-bio">
				<span class="author-name"><?php the_author(); ?></span>
				<?php echo if_empty(get_the_author_meta( 'description' ), 'Saknar beskrivning...'); ?>
			</div>
		</div>
	</div>

	<?php if($is_parent) : ?>
		<div class="conversation-vote" id="conversation-vote">
			<div class="header">
				<h3>Vad tycker du?</h3>
				<div class="right-side">
					<button class="js--toggle_information_box" data-rel="conversation-vote-information">
						<i class="dnicon-question visible-xs"></i>
						<span class="hidden-xs">Vad är detta?</span>
						<i class="dnicon-chevron-down"></i>
					</button>
				</div>
			</div>
			<div class="information-box" id="conversation-vote-information">
				<table>
					<tr>
						<td class="icon"><i class="fa fa-info-circle large turq"></i></td>
						<td><?php echo if_empty(get_option("info_conversation_vote_text"),"Skriv denna text i admin under Theme-settings"); ?></td>
					</tr>
				</table>
			</div>
			<div class="inner">
				<?php the_excerpt(); ?>
				<?php the_conversation_votes_control_html( $post->ID ); ?>
			</div>
			<div class="inner" id="conversation-vote-holder">
				<?php the_conversation_votes_html( $post->ID ); ?>
			</div>
		</div>

		<!-- /grey-box -->
	<?php endif; ?>

	<div class="conversation-list-container">
		<div class="header double">
			<span class="sub-heading red">Summering</span>
			<div class="h2"><?php echo $conversation_subject; ?></div>
		</div>
		<ul class="conversation-list">
			<?php
			// Get our children
			$post_ids = get_children( array( 'post_parent' => $post->post_parent ?: $post->ID, 'posts_per_page' => -1, 'post_type' => 'asikt', 'fields' => 'ids' ) );
			// Append our parent
			$post_ids[] = $post->post_parent ?: $post->ID;

			$args = array(
				'post_type' => 'asikt',
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'order' => 'ASC',
				'post__in' => $post_ids
			);

			$current_post_id = $post->ID;

			$loop = new WP_Query( $args );
			if ($loop->have_posts()) : while ($loop->have_posts()) : $loop->the_post(); $post = $loop->post; setup_postdata( $post ); ?>
				<li <?php  if( $post->ID == $current_post_id ) echo 'class="active"';  ?>>
					<span class="conversation-date date">Publicerad: <?php echo get_the_date(); ?></span>
					<h3 class="conversation-title">
						<a href="<?php the_permalink() ?>"><span class="author"><?php the_author(); ?>:</span> <?php the_title(); ?></a>
					</h3>
					<?php if( $post->post_parent == '0' AND $post->ID !== $current_post_id ) : ?>
						<p><?php echo get_the_excerpt(); ?></p>
						<?php the_conversation_votes_html( $post->ID ); ?>
					<?php endif; ?>
					</li>
			<?php endwhile; else : ?>
				<li><span>Inga svar ännu, du som har ett DN-konto kan rösta på vem som ska svara och även föreslå nya personer du vill ska svara</span></li>
			<?php endif; wp_reset_postdata(); ?>
		</ul>

		<div class="vote-persons" id="vote-person">
			<div class="header">
				<h3 class="has-dot">Rösta på vem som ska svara!</h3>
				<div class="right-side">
					<button class="js--toggle_information_box" data-rel="person-vote-information">
						<i class="dnicon-question visible-xs"></i>
						<span class="hidden-xs">Vad är detta?</span>
						<i class="dnicon-chevron-down"></i>
					</button>
				</div>
			</div>

			<div class="information-box" id="person-vote-information">
				<table>
					<tr>
						<td class="icon"><i class="fa fa-info-circle large turq"></i></td>
						<td><?php echo if_empty(get_option("info_person_vote_text"),"Skriv denna text i admin under Theme-settings"); ?></td>
					</tr>
				</table>
			</div>

			<div class="content">
				<ul class="list list-person list-vote">
				<?php if( empty( $repliers ) ): ?>
					<li>
						<span class="person_empty">Det finns ännu ingen person att rösta på, skicka gärna in förslag till redaktionen!</span>
					</li>
				<?php endif ?>
				<?php foreach( $repliers as $reply ) : ?>

					<li>
					<div class="row">
						<div class="col-sm-10">
							<span class="person"><?php echo $reply->display_name ?></span>
							<div class="row">
								<div class="col-sm-2">
									<span class="votes"><?php echo $reply->votes ?></span>
								</div>
								<div class="col-sm-10">
									<span class="description ff-sans-light"><?php echo $reply->motivation ?></span>
									<?php if($reply->status != 'pending'): ?>
										<span class="status ff-sans-light <?php echo $reply->status; ?>">
											<?php if($reply->status == 'declined'): ?>
												Personen har avböjt att delta i debatten
											<?php elseif($reply->status == 'unknown'): ?>
												DN har försökt få tag i personen utan att lyckas
											<?php else : if(isint( $reply->status )) :?>
												<?php if( $reply_post_obj = get_post( $reply->status ) ) : ?>
													<?php if( $reply_post_obj->post_status == 'publish' ) : ?>
														Vi har fått ett svar! <a href="<?php echo get_permalink( $reply->status ); ?>">Läs det här</a>
													<?php endif; ?>
												<?php endif; ?>
											<?php else: ?><?php endif; endif; ?>
										</span>
									<?php endif; ?>
								</div>
							</div>
						</div>

						<?php if(get_reply_vote($reply->id)) : ?>
							<div class="right"><a href="" class="js--add_reply_vote add-reply-vote selected" data-id="<?php echo $reply->id ?>" onclick="dataLayer.push({event: 'customEvent',eventCategory:'Person',eventAction:'Remove Vote',eventLabel:'<?php echo $reply->display_name; ?>'});">Ta bort röst</a></div>
						<?php else : ?>
							<div class="right"><a href="" class="js--add_reply_vote add-reply-vote" data-id="<?php echo $reply->id ?>" onclick="dataLayer.push({event: 'customEvent',eventCategory:'Person',eventAction:'Add Vote',eventLabel:'<?php echo $reply->display_name; ?>'});" >Rösta</a></div>
						<?php endif; ?>

					</div>
					</li>
				<?php endforeach; ?>
				</ul>
				<div id="add-suggestion">
					<?php if(is_user_logged_in()) : ?>
						<div class="row">
							<div class="col-sm-8">
								<form class="form-horizontal" id="suggestion-form">
									<input type="hidden" id="suggestion-nonce" value="<?php echo wp_create_nonce( "add_suggestion_nonce" ); ?>">
									<input type="hidden" id="suggestion-post_parent" value="<?php echo $post->post_parent ?: $post->ID ?>">
									<div class="form-group">
										<label for="inputEmail3" class="col-sm-3 control-label">Namn</label>
										<div class="col-sm-9">
											<div class="input-group">
												<div class="input-group-addon"><i class="dnicon-person"></i></div>
												<input type="text" name="name" class="form-control" id="suggestion-name" placeholder="Namn">
											</div>
										</div>
									</div>
									<div class="form-group">
										<label for="inputPassword3" class="col-sm-3 control-label">Kontakt</label>
										<div class="col-sm-9">
											<div class="input-group">
												<div class="input-group-addon"><i class="fa fa-link"></i></div>
												<input type="text" name="contact" class="form-control" id="suggestion-contact" placeholder="Ex. e-post, twitter">
											</div>
											<div class="help-block">Gärna specifik information så vi kan kontakta personen.</div>
										</div>
									</div>
									<div class="form-group">
										<label for="inputPassword3" class="col-sm-3 control-label">Motivering</label>
										<div class="col-sm-9">
											<div class="input-group">
												<div class="input-group-addon"><i class="fa fa-comment"></i></div>
												<textarea class="form-control" name="motivation" id="suggestion-motivation" rows="3" placeholder="Skriv något om varför den här personen bör svara på debatten"></textarea>
											</div>
										</div>
									</div>
									<div class="form-group">
										<div class="col-sm-offset-3 col-sm-8">
											<button class="btn btn-dn js--add_suggestion" onclick="dataLayer.push({event: 'customEvent',eventCategory:'Add person'});">Skicka</button>
										</div>
									</div>
								</form>
							</div>
							<div class="col-sm-4">
								<span class="sub-heading border">
									Skicka in förslag
								</span>
								<span>Förslagen skickas till DN-redaktionen som löpande uppdaterar listan med personer som går att rösta på. Tänk på att motivera varför just ditt förslag ska dyka upp i listan.</span>
							</div>
						</div>
						<!-- /row -->
					<?php endif; ?>
						<a href="#" class="btn btn-large btn-dn icon dnicon-plus js--toggle_add_suggestion_form"><span>Föreslå fler personer</span></a>
						<div class="eller">
							<span class="text">eller</span>
							<span class="border"></span>
						</div>
						<a href="<?php echo get_custom_page( array( 'pid' => $post->post_parent ?: $post->ID ) ); ?>" onclick="dataLayer.push({event: 'customEvent',eventCategory:'Add reply', eventAction:'<?php echo $conversation_subject; ?>', eventLabel:'<?php the_title() ?>'});" class="btn icon dnicon-person btn-danger"><span>Jag vill svara själv!</span></a>

				</div>
				<!-- /add-suggestion -->





			</div>
		</div>

	</div>
</article>

<?php endwhile; else: ?>

	<?php get_template_part( 'templates/post', 'not-found' ); ?>

<?php endif; ?>