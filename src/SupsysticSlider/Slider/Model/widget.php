<?php
class sslWidget extends Wp_Widget {

    private $slider;

    /**
     * Register widget with WordPress.
     */
    function __construct() {
        parent::__construct(
            'sslWidget',
            'Slider by Supsystic Widget',
            array( 'description' => 'Slider by Supsystic plugin' )
        );

        $this->slider = new SupsysticSlider_Slider_Model_Sliders();
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

        echo $args['before_widget'];
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
        }

        $slider = $GLOBALS['supsysticSlider'];

        echo $slider->getEnvironment()->getModule('slider')->render(array('id' => $instance['slider_id']));

        //echo '<div id="'. $instance['slider_id'] .'" class="supsystic-slider-widget" data-ajax-url=' . admin_url('admin-ajax.php') . '></div>';

        echo $args['after_widget'];
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        }
        else {
            $title = 'Title';
        }

        $sliderIds = array();

        foreach($this->slider->getAll() as $slider) {
            array_push($sliderIds, array(
                'name' => $slider->title . ' ' . $slider->id,
                'value' => $slider->id
            ));
        }
        ?>

        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
            <label for="<?php echo $this->get_field_id( 'slider_id' ); ?>"><?php _e( 'Select slider: ' ); ?></label>
            <select id="<?php echo $this->get_field_id( 'slider_id' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'slider_id' ); ?>" type="text">
                <?php foreach($sliderIds as $element)
                    /*if(isset($instance['ads_id']) && $instance['ads_id'] == $element['value'])
                        echo "<option value=" . $element['value'] . " selected>". $element['name'] . "</option>";
                    else*/
                        echo "<option value=" . $element['value'] . ">". $element['name'] . "</option>";
                ?>
            </select>
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
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['slider_id'] = ( ! empty( $new_instance['slider_id'] ) ) ? strip_tags( $new_instance['slider_id'] ) : '';

        return $instance;
    }
}