<?php get_header(); ?>

<div class="row">

	<?php get_template_part( 'templates/ad', 'banner' ); ?>

	<main class="col-sm-8 col-md-9">

		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			<h1><?php the_title(); ?></h1>
			<?php the_content(); ?>
		<?php endwhile; ?>
		<!-- post navigation -->
		<?php else: ?>
		<!-- no posts found -->
		<?php endif; ?>

	</main>

	<?php get_sidebar(); ?>

</div>

<?php get_footer(); ?>