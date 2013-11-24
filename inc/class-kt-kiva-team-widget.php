<?php

class KT_Kiva_Widget extends WP_Widget {

	public static function enqueue_scripts() {
		wp_enqueue_style( 'kiva-team-widget', plugin_dir_url( __FILE__ ) . 'assets/kiva-team-widget.css' );
	}

	public function __construct() {
		parent::__construct(
			'KT_Kiva_Widget',
			__('Kiva Team Widget', 'kiva-team-widget'),
			array( 'description' => __( 'Show info about a kiva team.', 'pagerduty-widget' ) )
		);
	}

	public function widget( $args, $instance ) {
		
		echo $args['before_widget'];

		$transient_key = 'kiva_team_' . md5( serialize( $instance ) );

		if ( ( $team = get_transient( $transient_key ) ) === false ) {
			$team = $this->get_team_info( $instance );
			set_transient( $transient_key, $team, 60 * 5 );
		}

		$team->link = 'http://www.kiva.org/team/' . $team->shortname;
		
		?>

		<h3 class="widget-title"><?php echo esc_html( $instance['title'] ) ?></h3>

		<ul class="kiva-team-stats">
			<li>
				<span class="kiva-team-stat-name">Loans</span>
				<a href="<?php echo esc_url( $team->link ) ?>" class="kiva-team-stat-value"><?php echo esc_html( $team->loan_count ) ?></a>
			</li>
			<li>
				<span class="kiva-team-stat-name">Amount</span>
				<a href="<?php echo esc_url( $team->link ) ?>" class="kiva-team-stat-value">$<?php echo esc_html( $team->loaned_amount ) ?></a>
			</li>
		</ul>

		<?php
		echo $args['after_widget'];
	}

	public function form( $instance ) {

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'kiva-team-widget' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( isset( $instance['title'] ) ? $instance['title'] : '' ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'team_id' ); ?>"><?php _e( 'Team ID:', 'kiva-team-widget' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'team_id' ); ?>" name="<?php echo $this->get_field_name( 'team_id' ); ?>" type="text" value="<?php echo esc_attr( isset( $instance['team_id'] ) ? $instance['team_id'] : '' ); ?>" />
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		
		return array( 
			'title' => sanitize_text_field( $new_instance['title'] ),
			'team_id' => absint( $new_instance['team_id'] ),
		);
	}

	private function get_team_info( $instance ) {

		$api = new KT_Kiva_API();

		$data = $api->request( 'GET', '/teams/' . $instance['team_id'] . '.json' );

		return $data->teams[0];
	}
}

function kt_register_kiva_team_widget() {
	register_widget( 'KT_Kiva_Widget' );
	add_action( 'wp_enqueue_scripts', array( 'KT_Kiva_Widget', 'enqueue_scripts' ) );
}
add_action( 'widgets_init', 'kt_register_kiva_team_widget' );