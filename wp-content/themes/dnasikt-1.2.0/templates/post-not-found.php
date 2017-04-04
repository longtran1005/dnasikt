<?php if( is_single( ) ) : ?>
	<h3>Hittade inga inlägg eller poster</h3>
<?php elseif( is_search() ) : ?>
	<h3>Hittade inga inlägg eller poster</h3>
<?php elseif( is_page() ) : ?>
	<h2>Sidan kunde inte hittas</h2>
<?php endif; ?>