<?php if ( have_posts() ): while ( have_posts() ) : the_post(); ?>

	<h1><?php the_title(); ?></h1>
	<?php the_content(); ?>

<?php endwhile; ?>

<?php else: ?>

	<?php get_template_part( 'templates/page', 'not-found' ); ?>

<?php endif; wp_reset_postdata(); ?>