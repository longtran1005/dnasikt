<?php
class WP_DN_Rss_Reader extends WP_Widget {
    const WIDGET_NAME = 'DN RSS';
    const WIDGET_NAME_ID = 'dn-tv';
    const WIDGET_DESCRIPTION = 'Hämtar senaste inlägg från RSS';
    const TEXT_DOMAIN = 'dn_widget_domain';

    public function __construct() {
        parent::__construct(
            self::WIDGET_NAME_ID, // Base ID
            __( self::WIDGET_NAME, self::TEXT_DOMAIN ), // Name
            array( 'description' => __( self::WIDGET_DESCRIPTION, self::TEXT_DOMAIN ), ) // Args
        );
    }

    public function widget( $args, $instance ) {


        $title          = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'RSS Läsare', self::TEXT_DOMAIN );
        $title          = apply_filters( 'widget_title', $title, $instance, $this->id_base );

        $rss            = ( ! empty( $instance['rss'] ) ) ? $instance['rss'] : 'http://www.dn.se/webb-tv/rss' ;
        $postnum        = ( ! empty( $instance['postnum'] ) ) ? $instance['postnum'] : '5' ;
        $show_images    = ( ! empty( $instance['show_images'] ) ) ? $instance['show_images'] : '0' ;

        echo $args['before_widget'];
        if ( $title ) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        $rss_feed_content   = false;
        $memcache_key       = sanitize_title( $args['widget_id'] );
        try {
            // Memcache
            if(class_exists('Memcache')) {
                $memcache = new Memcache;
                $memcache->connect(MEMCACHE_IP_ADDRESS, 11211);

                // Check if we have cached data
                $rss_feed_content = $memcache->get( $memcache_key );
                if($rss_feed_content === false) {
                    $rss_feed_content = file_get_contents($rss);
                    $memcache->set( $memcache_key, (string)$rss_feed_content, false, 60*20); // Expire every 20min
                }
            } else {
                $rss_feed_content = file_get_contents($rss);
            }
        } catch (Exception $e) {
            $rss_feed_content = file_get_contents($rss);
        }
        $news = @simplexml_load_string( $rss_feed_content, null, LIBXML_NOCDATA );
        if( $news !== false ) { ?>
            <ul class="list">
                <?php
                $i = 0; foreach($news->channel->item as $news): ?>
                    <?php if($show_images) : preg_match_all("/<img.+?src=\"([^\"]*)\".*?\\>/", (string)$news->description, $image);  ?>
                        <li>
                            <?php if(isset($image[1][0])) : ?>
                                <a href="<?php echo $news->link; ?>"><img src="<?php echo $image[1][0]; ?>" class="img-responsive pull-right widget-list-image"/></a>
                            <?php endif; ?>

                            <span><a href="<?php echo $news->link; ?>"><?php echo htmlspecialchars($news->title); ?></a></span>
                            <span class="meta"><?php echo date('Y-m-d H:i', strtotime($news->pubDate)); ?></span>
                        </li>
                    <?php else : ?>
                        <li>
                            <span class="meta pull-right"><?php echo date('G:i', strtotime($news->pubDate)); ?></span>
                            <span><a href="<?php echo $news->link; ?>" title="<?php echo htmlspecialchars($news->title); ?>" target="_blank">
                            <?php echo $news->title; ?></a></span>
                        </li>
                    <?php endif; ?>
                <?php $i++; if($i==$postnum) break; endforeach; ?>
            </ul>
        <?php }

        echo $args['after_widget'];
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title']      = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['rss']        = ( ! empty( $new_instance['rss'] ) ) ? strip_tags( $new_instance['rss'] ) : '';
        $instance['postnum']    = ( ! empty( $new_instance['postnum'] ) ) ? strip_tags( $new_instance['postnum'] ) : '';
        $instance['show_images']    = ! empty( $new_instance['show_images'] );

        return $instance;
    }

    public function form( $instance ) {

        $title   = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : __( 'Ny titel', self::TEXT_DOMAIN ) ;
        $rss     = isset( $instance['rss'] ) ? esc_attr( $instance['rss'] ) : '';
        $postnum = isset( $instance['postnum'] ) ? esc_attr( $instance['postnum'] ) : '5';
        ?>

        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>

        <p>

            <label for="<?php echo $this->get_field_id( 'rss' ); ?>"><?php _e( 'Rss:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'rss' ); ?>" name="<?php echo $this->get_field_name( 'rss' ); ?>" type="text" value="<?php echo $rss; ?>" />
        </p>

        <p>

            <label for="<?php echo $this->get_field_id( 'postnum' ); ?>"><?php _e( 'Antal:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'postnum' ); ?>" name="<?php echo $this->get_field_name( 'postnum' ); ?>" type="text" value="<?php echo $postnum; ?>" />
        </p>

        <p>
            <input id="<?php echo $this->get_field_id('show_images'); ?>" name="<?php echo $this->get_field_name('show_images'); ?>" type="checkbox" <?php checked(isset($instance['show_images']) ? $instance['show_images'] : 0); ?> />&nbsp;
            <label for="<?php echo $this->get_field_id('show_images'); ?>"><?php _e('Visa bild'); ?></label>
        </p>


    <?php
    }

}

add_action( 'widgets_init', function () {
    register_widget( 'WP_DN_Rss_Reader' );
});
?>