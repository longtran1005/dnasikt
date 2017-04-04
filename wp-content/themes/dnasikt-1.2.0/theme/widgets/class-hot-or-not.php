<?php
class WP_DN_Hot_Or_Not extends WP_Widget {
    const WIDGET_NAME = 'Hot or Not';
    const WIDGET_NAME_ID = 'dn-hot-or-not';
    const WIDGET_DESCRIPTION = 'Hämtar glöden';
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

        $postnum        = ( ! empty( $instance['postnum'] ) ) ? $instance['postnum'] : '5' ;


        echo $args['before_widget'];
        if ( $title ) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        ?>
            <ul class="list widget">

                <?php
                // WP_Query arguments
                $wp_query_args = array (
                    'post_type'     => 'asikt',
                    'post_status'   => 'publish',
                    'post_parent'   => 0,
                    'posts_per_page'=> 10,
                );

                // The Query
                $i = 1;
                $query = new WP_Query( $wp_query_args );
                // echo $query->found_posts;
                if ( $query->have_posts() ): while ( $query->have_posts() ) : $query->the_post();
                    $post = $query->post;
                    setup_postdata( $post );
                    $count =  new WP_Query( array ( 'post_type' => 'asikt', 'post_parent' => get_the_id() ) );
                    ?>
                    <li>
                        <span class="sidebar-label"><a href="<?php the_permalink() ?>"><?php the_author(); ?></a></span>
                        <span><a href="<?php the_permalink() ?>"><?php the_excerpt(); ?></a></span>
                        <?php the_conversation_votes_html( get_the_id(), true, $count->post_count ); ?>
                    </li>
                <?php
                if( $i >= $postnum ) break;
                $i++; endwhile; ?>
                <?php else: ?>
                    Inga inlägg
                <?php endif; wp_reset_postdata(); ?>
            </ul>
        <?php
        echo $args['after_widget'];


    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title']      = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['postnum']    = ( ! empty( $new_instance['postnum'] ) ) ? strip_tags( $new_instance['postnum'] ) : '';

        return $instance;
    }

    public function form( $instance ) {

        $title   = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : __( 'Ny titel', self::TEXT_DOMAIN ) ;
        $postnum = isset( $instance['postnum'] ) ? esc_attr( $instance['postnum'] ) : '5';
        ?>

        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>

        <p>

            <label for="<?php echo $this->get_field_id( 'postnum' ); ?>"><?php _e( 'Antal:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'postnum' ); ?>" name="<?php echo $this->get_field_name( 'postnum' ); ?>" type="text" value="<?php echo $postnum; ?>" />
        </p>

    <?php
    }

}

add_action( 'widgets_init', function () {
    register_widget( 'WP_DN_Hot_Or_Not' );
});
?>