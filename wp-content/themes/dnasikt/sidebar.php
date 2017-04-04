<aside class="col-sm-4 col-md-3 sidebar">
	<?php if( 1 + 1 ): ?>
		<?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar('default-sidebar')) : endif; ?>
	<?php endif; ?>
	<?php if(is_home()) : ?>

	<?php elseif(is_single()) : ?>

	<?php elseif(is_page()) : ?>

	<?php endif ; ?>

</aside>