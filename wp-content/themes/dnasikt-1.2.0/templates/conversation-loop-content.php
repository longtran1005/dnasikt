<article class="conversation">

	<?php if ( $featured_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'dn-featured') ): ?>
		<!-- Featured Image -->
		<div class="featured-image">
			<a href="<?php the_permalink() ?>"  onclick="dataLayer.push({event: 'customEvent',eventCategory:'Visit article',eventAction:'<?php echo if_empty( get_post_meta( $post->ID, '_conversation_subject', true ), 'Saknar ämne...' ); ?>',eventLabel:'<?php the_title(); ?>'});">
				<img src="<?php echo $featured_image[0]; ?>" alt="" class="img-responsive" />
			</a>
			<p class="text"><?php echo if_empty(get_post(get_post_thumbnail_id())->post_excerpt, 'Foto: DN'); ?></p>
		</div>

	<?php endif; ?>

	<div>
		<div class="header double">
			<span class="label-heading-red"><?php echo if_empty( get_post_meta( $post->ID, '_conversation_subject', true ), 'Saknar ämne...' ); ?></span>
			<h1 class="conversation-title">
				<a href="<?php the_permalink() ?>"  onclick="dataLayer.push({event: 'customEvent',eventCategory:'Visit article',eventAction:'<?php echo if_empty( get_post_meta( $post->ID, '_conversation_subject', true ), 'Saknar ämne...' ); ?>',eventLabel:'<?php the_title(); ?>'});"><span class="author"><?php the_author(); ?>:</span> <?php the_title(); ?></a>
			</h1>
		</div>
	</div>

	<div>
		<div class="article-content">
			<span class="date">Publicerad: <?php echo get_the_date(); ?></span>
			<p class="ff-sans-regular"><?php echo get_the_excerpt(); ?></p>

			<div class="row">
				<div class="col-md-offset-2 col-md-8">
					<?php the_conversation_votes_html( $post->ID ); ?>

					<ul class="conversation-list">
						<?php
						$outer_post = $post;
						// setup your arguments:
						$args = array(
							'post_type' => 'asikt',
							'posts_per_page' => -1,
							'post_status' => 'publish',
							'order' => 'ASC',
							'post_parent' => $post->ID
						);
						$loop = new WP_Query( $args );
						$author_list[] = $post->post_author;
						if ($loop->have_posts()) : while ($loop->have_posts()) : $loop->the_post();
							$author_list[] = $loop->post->post_author;
						?>
							<li>
								<span class="date">Publicerad: <?php echo get_the_date(); ?></span>
								<h3 class="conversation-title">
									<a href="<?php the_permalink() ?>" onclick="dataLayer.push({event: 'customEvent',eventCategory:'Visit child article',eventAction:'<?php echo if_empty( get_post_meta( $outer_post->ID, '_conversation_subject', true ), 'Saknar ämne...' ); ?>',eventLabel:'<?php the_title(); ?>'});"><span class="author"><?php the_author(); ?>:</span> <?php the_title(); ?></a>
								</h3>
							</li>
						<?php endwhile; else : ?>
							<li><span class="ff-sans-light">Ingen har svarat, skriv ett svar eller rösta på vem som ska svara!</span></li>
						<?php endif; wp_reset_postdata(); ?>
					</ul>
				</div>
			</div>
		</div>

		<!-- /grey-box -->
	</div>
	<!-- /col-md-offset-1 -->

</article>