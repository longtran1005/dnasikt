<h1>Arkiv</h1>
<?php if(is_search()) : ?>
<p class="tip ff-sans-light">Vi hittade <strong class="ff-sans-bold"><?php echo $wp_query->found_posts; ?> st</strong> träffar på "<strong class="ff-sans-bold"><?php echo get_search_query(); ?></strong>"</p>
<?php endif; ?>
<?php if ( have_posts() ): while ( have_posts() ) : the_post(); ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<?php
		$type = get_post_type_object( get_post_type( get_the_id() ) );
		?>

		<div class="header double">
			<span class="sub-heading red">
				<?php echo $type->labels->singular_name; ?>
			</span>
			<h2><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h2>
		</div>
		<span class="date">Publicerad: <?php echo get_the_date( ); ?></span>
		<?php the_excerpt(); ?>
	</article>

<?php endwhile; else: ?>

	<?php get_template_part( 'templates/post', 'not-found' ); ?>

<?php endif; ?>