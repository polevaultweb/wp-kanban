<?php

class wpt_widget extends WP_Widget {
	
	/* Register widget with WordPress. */
	public function __construct() {
		parent::__construct(
			'wpt_widget', 
			'WP Trello', 
			array( 'description' => __( 'A widget to display data from Trello', 'wp-trello' ), )
		);
	}
	
	/* Front-end display of widget. */
	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$type = isset($instance['type']) ? $instance['type'] : false;
		$id = isset($instance['id']) ? $instance['id'] : false;
		$link = isset($instance['link']) ? $instance['link'] : false;
	
		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
		
		$html = wp_trello::trello_output($type, $id, $link);	
		echo $html;
		echo $after_widget;
	}
	
	/* Sanitize widget form values as they are saved. */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['type'] = strip_tags( $new_instance['type'] );
		$instance['id'] = strip_tags( $new_instance['id'] );
		$instance['link'] =  $new_instance['link'] ? 1 : 0;		
		return $instance;
	}
	
	/* Back-end widget form. */
	public function form( $instance ) {
		$defaults = array( 	'title' => 'From Trello' , 
							'type' => 'cards', 
							'id' => '',
							'link' => false);

		$instance = wp_parse_args( (array) $instance, $defaults );
		
		$title = strip_tags( $instance['title'] );
		$type = isset($instance['type']) ? $instance['type'] : false;
		$id = isset($instance['id']) ? $instance['id'] : false;
		$link = isset($instance['link']) ? $instance['link'] : false;		
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wp-trello' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'type' ); ?>"><?php _e( 'Type:', 'wp-trello'); ?></label> 
		<select class="widefat" id="<?php echo $this->get_field_id( 'size' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>">
			<option <?php selected('organizations', $type); ?> value="organizations">Organizations</option>
			<option <?php selected('boards', $type); ?> value="boards">Boards</option>
			<option <?php selected('lists', $type); ?> value="lists">Lists</option>
			<option <?php selected('cards', $type); ?>value="cards">Cards</option>
			<option <?php selected('card', $type); ?>value="card">Card</option>
		</select>
		</p>
		<label for="<?php echo $this->get_field_id( 'id' ); ?>"><?php _e( 'ID:', 'wp-trello'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'id' ); ?>" name="<?php echo $this->get_field_name( 'id' ); ?>" type="text" value="<?php echo $id; ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'link' ); ?>"><?php _e( 'Add Link', 'wp-trello'); ?></label> 
		<input class="checkbox" type="checkbox" <?php checked($link, true) ?> id="<?php echo $this->get_field_id('link'); ?>" name="<?php echo $this->get_field_name('link'); ?>" />
		</p>
		<?php 
	}
}