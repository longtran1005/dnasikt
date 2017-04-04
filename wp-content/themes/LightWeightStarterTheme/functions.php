<?php
      // Set upload limit
    @ini_set( 'upload_max_size' , '64M' );
    @ini_set( 'post_max_size', '64M');
    @ini_set( 'max_execution_time', '300' );

    // Set default timezone
    date_default_timezone_set('Europe/Berlin');

    // Requires
    require_once('externals/theme-functions.php');
    require_once('externals/theme-settings.php');
    require_once('externals/ajax-calls.php');
    require_once('externals/post-calls.php');
    require_once('externals/notifications.php');

    // Theme
    require_once('theme/theme-bootstrap.php');

    // Init
    add_action('init', function() {
        remove_filter( 'excerpt_length', 'qaplus_excerpt_length' );
        remove_filter( 'excerpt_more', 'qaplus_auto_excerpt_more' );
        remove_filter( 'get_the_excerpt', 'qaplus_custom_excerpt_more' );

        remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        remove_action( 'wp_print_styles', 'print_emoji_styles' );

        // Fix
        if(isset($_POST)) $_POST = array_map( 'stripslashes_deep', $_POST );
        if(isset($_GET)) $_GET = array_map( 'stripslashes_deep', $_GET );
        if(isset($_COOKIE)) $_COOKIE = array_map( 'stripslashes_deep', $_COOKIE );
        if(isset($_REQUEST)) $_REQUEST = array_map( 'stripslashes_deep', $_REQUEST );
    });

    /**
     * On Theme Activation
     */
    function lwst_theme_activation($oldname, $oldtheme=false) {
        // Create DB tables
        if(function_exists('lwst_create_reply_suggestions')) lwst_create_reply_suggestions();
        if(function_exists('lwst_create_replies')) lwst_create_replies();
        if(function_exists('lwst_create_replies_votes')) lwst_create_replies_votes();
        if(function_exists('lwst_create_conversation_votes')) lwst_create_conversation_votes();
        if(function_exists('lwst_create_notifications')) lwst_create_notifications();

    }
    add_action("after_switch_theme", "lwst_theme_activation", 10 ,  2);

    function lwst_create_reply_suggestions() {
        global $wpdb;
        $table_name = "reply_suggestions";
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    post_id int(11) NOT NULL,
                    user_id int(11) NOT NULL,
                    name varchar(100) NOT NULL,
                    contact varchar(200) NULL,
                    motivation text NOT NULL,
                    PRIMARY KEY (id)
                ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    function lwst_create_replies() {
        global $wpdb;
        $table_name = "replies";
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    user_id int(11) NOT NULL,
                    conversation_id int(11) NOT NULL,
                    motivation text NOT NULL,
                    visible enum('1','0') NOT NULL DEFAULT '0',
                    status varchar(15) NOT NULL DEFAULT 'pending',
                    PRIMARY KEY (id)
                ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    function lwst_create_replies_votes() {
        global $wpdb;
        $table_name = "replies_votes";
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    user_id int(11) NOT NULL,
                    reply_id int(11) NOT NULL,
                    vote enum('0','1'),
                    PRIMARY KEY (id)
                ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    function lwst_create_conversation_votes() {
        global $wpdb;
        $table_name = "conversation_votes";
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    user_id int(11) NOT NULL,
                    conversation_id int(11) NOT NULL,
                    vote enum('0','1','x'),
                    PRIMARY KEY (id)
                ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    function lwst_create_notifications() {
        global $wpdb;
        $table_name = "notifications";
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    user_id int(11) NOT NULL,
                    subject varchar(150) NOT NULL,
                    body text NOT NULL,
                    permalink varchar(300) NULL DEFAULT NULL,
                    is_sent enum('0','1') DEFAULT '0',
                    is_read enum('0','1') DEFAULT '0',
                    failed int(1) DEFAULT '0',
                    updated TIMESTAMP NULL DEFAULT NULL,
                    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id)
                ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**
     * Custom thumbnails
     */
    function lwst_theme_support() {
        add_theme_support( 'post-thumbnails' );
        add_image_size( 'dn-featured', 790, 444, true ); // (cropped)
    }
    add_action( 'after_setup_theme', 'lwst_theme_support' );

    /**
     * Enqueue stylesheets ans javascript
     */
    function lwst_theme_resources() {
        wp_register_style('lwst-application-style', get_template_directory_uri() . '/assets/build/css/main.css', array(), '1.0', 'all');
        wp_enqueue_style('lwst-application-style');

        if( !is_admin() ){
            wp_deregister_script('jquery');
            // wp_register_script('jquery', ('https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js'), false, '');
            wp_register_script('jquery', (get_template_directory_uri() . '/jquery.js'), false, '');
            wp_enqueue_script('jquery');
        }

        wp_enqueue_script( 'lwst-application-script', get_template_directory_uri() . '/assets/build/js/app.min.js', array( 'jquery' ), '', true );
        wp_localize_script( 'lwst-application-script', 'WordpressGlobalVariables', array(
            'ajaxurl'               => admin_url( 'admin-ajax.php' ),
            'loginurl'               => serviceplus_login_url( wp_current_url() ),
            'createaccounturl'               => serviceplus_create_account_url( wp_current_url() ),
            // 'wp_nonce_more_posts'   => wp_create_nonce('more_posts'),
            )
        );
    }
    add_action( 'wp_enqueue_scripts', 'lwst_theme_resources' );

    /**
     * ADMIN Enqueue stylesheets ans javascript
     */
    function load_custom_wp_admin_style() {
        wp_register_style( 'custom_wp_admin_css', get_template_directory_uri() . '/assets/build/css/admin.css', false, '1.0.0' );
        wp_enqueue_style( 'custom_wp_admin_css' );

        wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_register_style( 'jquery-ui-styles','http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css' );

        wp_enqueue_script( 'lwst-application-admin-script', get_template_directory_uri() . '/assets/build/js/admin.min.js', array( 'jquery', 'jquery-ui-autocomplete' ), '', true );
    }
    add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_style' );

    /**
     * Removes width and height attr from images
     */
    function remove_width_attribute( $html ) {
       $html = preg_replace( '/(width|height)="\d*"\s/', "", $html );
       return $html;
    }
    add_filter( 'post_thumbnail_html', 'remove_width_attribute', 10 );
    add_filter( 'image_send_to_editor', 'remove_width_attribute', 10 );

    function remove_empty_p( $content ){
        // clean up p tags around block elements
        $content = preg_replace( array(
            '#<p>\s*<(div|aside|section|article|header|footer)#',
            '#</(div|aside|section|article|header|footer)>\s*</p>#',
            '#</(div|aside|section|article|header|footer)>\s*<br ?/?>#',
            '#<(div|aside|section|article|header|footer)(.*?)>\s*</p>#',
            '#<p>\s*</(div|aside|section|article|header|footer)#',
        ), array(
            '<$1',
            '</$1>',
            '</$1>',
            '<$1$2>',
            '</$1',
        ), $content );

        return preg_replace('#<p>(\s|&nbsp;)*+(<br\s*/*>)?(\s|&nbsp;)*</p>#i', '', $content);
    }
    add_filter( 'the_content', 'remove_empty_p', 20, 1 );

    function insert_add_after_p( $matches ) {
        $count += strlen( $matches[0] );
        debug($count);
        return $matches[0];
    }
    function add_before_content($content) {
        $chars_count = strlen( $content );

        $count = 0;
        $ad_count = 0;
        $content = preg_replace_callback( "/\<p\>(.*)\<\/p\>/", function($matches) use (&$count,&$ad_count) {
            $text = ( isset( $matches[0] ) ) ? $matches[0] : '' ;
            $count += strlen( $text );

            if( $count > 1000 AND $ad_count == 0 ) {
                $text .= '  <div class="ad-wrapper"><div class="ad-text">Annons:</div>';
                $text .= get_ad( array( 'name' => "k2a1" /**"l_art1"*/, 'width' => '500', 'class' => 'hidden-xs' ) );
                $text .= get_ad( array( 'name' => "m_Middle1" /**"s_art1"*/, 'width' => '320', 'class' => 'visible-xs' ) );
                $text .= '</div>';
                $count = 0;
                $ad_count++;
            }

            return $text;
        }, $content);

        return $content;
    }
    add_filter('the_content', 'add_before_content', 30, 1 );

    /**
     * Remove buttons from TinyMCE FRONTEND!
     */
    function custom_disable_mce_buttons( $opt ) {
        if(is_page()) {
            $opt['theme_advanced_disable'] = 'bold,justifyfull,forecolor,removeformat,justifycenter,justifyright,justifyleft,charmap,indent,outdent,undo,redo';
        }
        return $opt;
    }
    add_filter('tiny_mce_before_init', 'custom_disable_mce_buttons');

    /**
     * Add anonymous author
     */

    function anonymous_author_name( $name ) {
        global $post;

        $anonymous = get_post_meta( $post->ID, '_author_anonymous', true );

        if(is_admin() and $anonymous) {
            return '(Anonym) ' . $name;
        }

        if( $anonymous ) return 'Anonym';

        return $name;
    }
    add_filter( 'the_author', 'anonymous_author_name' );
    add_filter( 'get_the_author_display_name', 'anonymous_author_name' );

    /**
     * Add anonymous author description / bio
     */
    function anonymous_author_description( $desc ) {
        global $post;

        $anonymous = get_post_meta( $post->ID, '_author_anonymous', true );

        if(is_admin() and $anonymous) {
            return $desc;
        }

        if( $anonymous ) return 'Personen i fråga vill vara anonym';

        return $desc;
    }
    add_filter( 'get_the_author_description', 'anonymous_author_description' );

    /**
     * Hide WP Admin Bar
     */
    function csstricks_hide_admin_bar() {
        if (!current_user_can('edit_posts')) {
            show_admin_bar(false);
        }
    }
    add_action('set_current_user', 'csstricks_hide_admin_bar');

    /**
     * Rewrite Search URL
     */
    function fb_change_search_url_rewrite() {
        if ( is_search() && ! empty( $_GET['s'] ) ) {
            wp_redirect( home_url( "/search/" ) . urlencode( get_query_var( 's' ) ) );
            exit();
        }
    }
    add_action( 'template_redirect', 'fb_change_search_url_rewrite' );

    /**
      * Filter to fix the Post Author Dropdown
      */

    function theme_post_author_override( $output ) {
        global $post,$user_ID;
        // return if this isn't the theme author override dropdown
        if (!preg_match('/post_author_override/', $output))
            return $output;

        // return if we've already replaced the list (end recursion)
        if (preg_match ('/post_author_override_replaced/', $output))
            return $output;

        // replacement call to wp_dropdown_users
        $output = wp_dropdown_users( array(
            'echo' => 0,
            'name' => 'post_author_override_replaced',
            'selected' => empty($post->ID) ? $user_ID : $post->post_author,
            'include_selected' => true
        ) );

        // put the original name back
        $output = preg_replace('/post_author_override_replaced/', 'post_author_override', $output);

        return $output;
    }
    add_filter('wp_dropdown_users', 'theme_post_author_override');

    /**
     * Change FAQ permalink
     */
    function change_faq_permalink($url) {
        global $post;
        if( 'qa_faqs' !== $post->post_type ) return $url;
        return home_url( '/faq/' );
    }
    add_filter('the_permalink', 'change_faq_permalink');
    /**
     * Register sidebars
     */
    if (function_exists('register_sidebar')) {
        register_sidebar(array(
            'name'=> 'Sidor / Inlägg',
            'id' => 'default-sidebar',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h4>',
            'after_title' => '</h4>',
        ));
        register_sidebar(array(
            'name'=> 'Förstasidan 1',
            'id' => 'default-sidebar-block-1',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h4>',
            'after_title' => '</h4>',
        ));
        register_sidebar(array(
            'name'=> 'Förstasidan 2',
            'id' => 'default-sidebar-block-2',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h4>',
            'after_title' => '</h4>',
        ));
        register_sidebar(array(
            'name'=> 'Footer DN Åsikt-meny',
            'id' => 'footer-menu',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h3>',
            'after_title' => '</h3>',
        ));
        register_sidebar(array(
            'name'=> 'Footer Extra-meny Ett',
            'id' => 'footer-one',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h3>',
            'after_title' => '</h3>',
        ));
        register_sidebar(array(
            'name'=> 'Footer Extra-meny Två',
            'id' => 'footer-two',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h3>',
            'after_title' => '</h3>',
        ));
        register_sidebar(array(
            'name'=> 'Footer Extra-meny Tre',
            'id' => 'footer-three',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h3>',
            'after_title' => '</h3>',
        ));
        register_sidebar(array(
            'name'=> 'Footer Punkten',
            'id' => 'footer-four',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h3>',
            'after_title' => '</h3>',
        ));
        register_sidebar(array(
            'name'=> 'Footer Credits',
            'id' => 'footer-five',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h3>',
            'after_title' => '</h3>',
        ));


    }

    function change_position_banner_if_admin(){
        if ( current_user_can( 'publish_posts' ) ) {
            echo '<style type="text/css" media="screen">';
            echo '.navbar.navbar-fixed-top { top: 32px; }';
            echo '@media screen and (max-width: 783px) {';
            echo '.navbar.navbar-fixed-top { top: 46px; }';
            echo '}';
            echo '</style>';
        }
    }
    add_filter( 'wp_head' , 'change_position_banner_if_admin');



    function pre_submit_page_validation() {
        global $wp_query;
        if( ! is_page_template( 'page-submit-conversation.php' ) ) {
            return;
        }

        $cid        = ( isset( $_GET['cid'] ) ) ? $_GET['cid'] : null ;
        $pid        = ( isset( $_GET['pid'] ) ) ? $_GET['pid'] : null ;
        $token      = ( isset( $_GET['token'] ) ) ? $_GET['token'] : null ;
        $state      = ( isset( $_GET['state'] ) ) ? $_GET['state'] : null ;

        // Check if page exits with that CID and TOKEN
        if( $cid AND $token AND !$pid ) {

            $args = array (
                'p'                 => $cid,
                'post_type'         => 'asikt',
                'post_status'       => 'draft',
                'meta_query'        => array(
                    array(
                        'key'       => '_conversation_token',
                        'value'     => $token,
                    ),
                ),
            );

            $query = new WP_Query( $args );

            if( ! $query->have_posts() ) {
                $wp_query->set_404();
                status_header( 404 );
                die("404 - Sidan hittades inte");
                exit;
            }
            $reply_on_post = $query->post;
        }

        // Check if a parent page exits with PID
        if( $pid AND ! $cid AND ! $token ) {

            $args = array (
                'p'             => $pid,
                'post_type'     => 'asikt',
                'post_status'   => 'published'
            );

            $query = new WP_Query( $args );

            if( ! $query->have_posts() ) {
                $wp_query->set_404();
                status_header( 404 );
                die("404 - Sidan hittades inte");
                exit;
            }

        }
    }
    add_action('wp', 'pre_submit_page_validation');


    add_action('init', 'start_session_support', 1);
    add_action('wp_logout', 'end_session_support');
    add_action('wp_login', 'end_session_support');

    function start_session_support() {
        if(!session_id()) {
            session_start();
        }
    }

    function end_session_support() {
        session_destroy ();
    }
?>