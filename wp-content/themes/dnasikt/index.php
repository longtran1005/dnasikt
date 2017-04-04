<?php get_header(); ?>

<?php if( is_front_page() ) : ?>
	<?php get_template_part( 'templates/conversation', 'loop' ); ?>
<?php else :?>
<div class="row">

	<?php get_template_part( 'templates/ad', 'banner' ); ?>

	<main class="col-sm-8 col-md-9">
		<?php if( is_single() ) : ?>
			<?php get_template_part( 'templates/conversation', 'details' ); ?>
		<?php elseif( is_page() ) : ?>
			<?php get_template_part( 'templates/page', 'details' ); ?>
		<?php else : ?>
			<?php get_template_part( 'templates/standard', 'loop' ); ?>
			<?php if( function_exists( 'wp_bs_pagination' ) ) wp_bs_pagination(); ?>
		<?php endif; ?>

	</main>

	<?php get_sidebar(); ?>

</div>
<?php endif; ?>

<?php get_footer(); ?>