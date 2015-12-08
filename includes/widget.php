<?php
/**
 * Adds BP_Site_Subscriber widget.
 */
class BP_Site_Subscriber_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'bp_site_subscriber_widget',
			__( 'Buddypress Site Subscriber', 'bp-site-subscriber' ),
			array( 'description' => __( 'Widget with a button that lets users subscribe to this site', 'bp-site-subscriber' ) )
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		// Only enqueue JS if widget is used
		wp_enqueue_script( 'bp-site-subscriber', WP_PLUGIN_URL . '/' . plugin_basename( BP_SITE_SUBSCRIBER_PLUGIN_DIR ) . '/js/bp-site-subscriber.js', array( 'jquery' ), '0.1', true );

		extract( $args );

		if ( isset( $instance['title'] ) ) :
			$title = apply_filters( 'widget_title', $instance['title'] );
		else :
			$title = '';
		endif;

		if ( isset( $instance['content'] ) ) :
			$content = wpautop( $instance['content'] );
		else :
			$content = '';
		endif;

		echo $before_widget;
		echo $before_title;
		echo $title;
		echo $after_title;
		echo $content;
		echo BP_Site_Subscriber::subscribe_button();
		echo $after_widget;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) :
			$title = $instance[ 'title' ];
		else :
			$title = '';
		endif;
		if ( isset( $instance[ 'content' ] ) ) :
			$content = $instance[ 'content' ];
		else :
			$content = '';
		endif;
		?>
		<p>
		<label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php esc_attr_e( 'Widget title', 'bp-site-subscriber' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_name( 'content' ); ?>"><?php esc_attr_e( 'Widget content', 'bp-site-subscriber' ); ?></label>
		<textarea class="widefat" id="<?php echo $this->get_field_id( 'content' ); ?>" name="<?php echo $this->get_field_name( 'content' ); ?>"><?php echo wp_strip_all_tags( $content ); ?></textarea>
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( !empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['content'] = ( !empty( $new_instance['content'] ) ) ? wp_strip_all_tags( $new_instance['content'] ) : '';

		return $instance;
	}

}

function bp_site_subscriber_register_widget() {
	register_widget( 'bp_site_subscriber_widget' );
}
add_action( 'widgets_init', 'bp_site_subscriber_register_widget' );