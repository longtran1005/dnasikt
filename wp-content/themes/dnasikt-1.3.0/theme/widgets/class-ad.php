<?php
class WP_DN_Ad extends WP_Widget {
    const WIDGET_NAME = 'Reklam';
    const WIDGET_NAME_ID = 'dn-ad';
    const WIDGET_DESCRIPTION = 'Hämtar en reklambanner 300px bred';
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

        echo $args['before_widget'];
        ?>
            <div class="ad-wrapper">
                <div class="ad-text">Annons:</div>
                <?php do_ad( array( 'name' => 'insider1' /**'l_sidebar'*/, 'width' => '300', 'class' => 'hidden-xs' ) ); ?>
                <?php do_ad( array( 'name' => 'mob_3' /**'s_sidebar'*/, 'width' => '320', 'class' => 'visible-xs' ) ); ?>
            </div>
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

    <?php
    }

}

add_action( 'widgets_init', function () {
    register_widget( 'WP_DN_Ad' );
});
?>
