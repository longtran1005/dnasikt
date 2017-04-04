<?php for( $block = 1; $block <= 2; $block++ ) : ?>
<div class="row block-<?php echo $block; ?>">

	<?php include( get_template_directory() . '/templates/ad-banner.php' ); ?>

	<main class="col-sm-8 col-md-9">
		<?php
		// WP_Query arguments
		$args = array (
			'post_type'		=> 'asikt',
			'post_status'	=> 'publish',
			'post_parent' 	=> 0,
			'posts_per_page'=> 3,
			'offset'=> ( 3 * ( $block - 1 ) ),
		);

		// The Query
		$query = new WP_Query( $args );
		// echo $query->found_posts;

		if ( $query->have_posts() ): while ( $query->have_posts() ) : $query->the_post(); ?>

				<?php get_template_part( 'templates/conversation', 'loop-content' ); ?>

		<?php endwhile; ?>

			<?php if( $block == 2 ) : ?>
				<button class="js--get-more-conversations btn btn-primary">Hämta fler inlägg</button>
			<?php endif; ?>

		<?php else: ?>

			<?php get_template_part( 'templates/post', 'not-found' ); ?>

		<?php endif; wp_reset_postdata(); ?>
	</main>
	<aside class="col-sm-4 col-md-3 sidebar">
		<div class="content">
			<?php get_sidebar( 'frontpage-block-' . $block ) ?>
		</div>
	</aside>
</div>
<!-- /.aside col-sm-4 col-md-3 -->
<?php endfor; ?>