<?php if ( have_posts() ): while ( have_posts() ) : the_post(); ?>

	<article class="post">
		<h1><?php the_title(); ?></h1>
		<?php the_content(); ?>
		<hr>
	</article>

<?php endwhile; else: ?>

	<?php get_template_part( 'templates/post', 'not-found' ); ?>

<?php endif; ?>