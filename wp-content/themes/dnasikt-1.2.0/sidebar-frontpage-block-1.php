<?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar('default-sidebar-block-1')) : endif; ?>

<?php if(is_home()) : ?>

<?php elseif(is_single()) : ?>

<?php elseif(is_page()) : ?>

<?php endif ; ?>
