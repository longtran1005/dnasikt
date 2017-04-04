<?php
// Custom types
require_once( 'conversation-post-type.php' );

// Metaboxes
$metaboxes = 'metaboxes/';
require_once( $metaboxes . 'reply-metabox.php' );
require_once( $metaboxes . 'fields-metabox.php' );
require_once( $metaboxes . 'author-metabox.php' );
require_once( $metaboxes . 'submit-page-metabox.php' );
// require_once( $metaboxes . 'vote-metabox.php' );

// Widgets
$widgets = 'widgets/';
require_once( $widgets . 'class-ad.php' );
require_once( $widgets . 'class-latest-news.php' );
require_once( $widgets . 'class-hot-or-not.php' );
require_once( $widgets . 'dashboard-conversation.php' );
?>