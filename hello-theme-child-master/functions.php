<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementorChild
 */

/**
 * Load child theme css and optional scripts
 *
 * @return void
 */
function hello_elementor_child_enqueue_scripts() {
	wp_enqueue_style(
		'hello-elementor-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		[
			'hello-elementor-theme-style',
		],
		'1.0.0'
	);
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_enqueue_scripts' );


function my_custom_sidebar() {
	register_sidebar(
		array (
			'name' => __( 'Custom Sidebar Area', 'hello-elementor-child' ),
			'id' => 'custom-side-bar',
			'description' => __( 'This is the custom sidebar that you registered using the code snippet. You can change this text by editing this section in the code.', 'your-theme-domain' ),
			'before_widget' => '<div class="widget-content">',
			'after_widget' => "</div>",
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>',
		)
	);
}
add_action( 'widgets_init', 'my_custom_sidebar' );


// Pagebuilder Locale
function sp_unload_textdomain_elementor() {
	if (is_admin()) {
		$user_locale = get_user_meta( get_current_user_id(), 'locale', true );
		if ( 'en_US' === $user_locale ) {
			unload_textdomain( 'elementor' );
			unload_textdomain( 'elementor-pro' );
		}
	}
}
add_action( 'init', 'sp_unload_textdomain_elementor', 100 );

/* Icon Widget Fix - Link now applies to the whole element (not only icon & title) */ 

function tdau_link_whole_icon_box ( $content, $widget ) {
	
    if ( 'icon-box' === $widget->get_name() ) {
        $settings = $widget->get_settings_for_display();

		$wrapper_tag = 'div';

		$has_icon = ! empty( $settings['icon'] );

		if ( ! empty( $settings['link']['url'] ) ) {
			$wrapper_tag = 'a';
		}

		$icon_attributes = $widget->get_render_attribute_string( 'icon' );
		$link_attributes = $widget->get_render_attribute_string( 'link' );

		if ( ! $has_icon && ! empty( $settings['selected_icon']['value'] ) ) {
			$has_icon = true;
		}
		$migrated = isset( $settings['__fa4_migrated']['selected_icon'] );
        $is_new = ! isset( $settings['icon'] ) && Elementor\Icons_Manager::is_migration_allowed();
		
		ob_start();

		?>
		<<?php echo implode( ' ', [ $wrapper_tag, $link_attributes ] ); ?> class="elementor-icon-box-wrapper elementor-icon-box-wrapper-tdau elementor-animation-<?php echo $settings['hover_animation']; ?>">
			<?php if ( $has_icon ) : ?>
			<div class="elementor-icon-box-icon">
				<<?php echo implode( ' ', [ 'span', $icon_attributes ] ); ?>>
				<?php
				if ( $is_new || $migrated ) {
					Elementor\Icons_Manager::render_icon( $settings['selected_icon'], [ 'aria-hidden' => 'true' ] );
				} elseif ( ! empty( $settings['icon'] ) ) {
					?><i <?php echo $widget->get_render_attribute_string( 'i' ); ?>></i><?php
				}
				?>
				</span>
			</div>
			<?php endif; ?>
			<div class="elementor-icon-box-content">
				<<?php echo $settings['title_size']; ?> class="elementor-icon-box-title">
					<?php echo $settings['title_text']; ?>
				</<?php echo $settings['title_size']; ?>>
				<?php if ( ! Elementor\Utils::is_empty( $settings['description_text'] ) ) : ?>
				<p <?php echo $widget->get_render_attribute_string( 'description_text' ); ?>><?php echo $settings['description_text']; ?></p>
				<?php endif; ?>
			</div>
		</<?php echo $wrapper_tag; ?>>
		<?php

		$content = ob_get_clean();

    }

    return $content;
}
add_filter( 'elementor/widget/render_content', 'tdau_link_whole_icon_box', 10, 2 );

add_filter( 'manage_employee_posts_columns', 'sp_employee_columns' );
function sp_employee_columns( $columns ) {
	//$first = array_shift($columns);
	$columns['picture'] = __( 'Picture', 'default' );
	$columns['location'] = __( 'Location', 'default' );
	$columns['team'] = __( 'Team', 'default' );
	unset($columns['date']);
	return $columns;
}

add_action( 'manage_employee_posts_custom_column', 'smashing_realestate_custom_column', 10, 2);
function smashing_realestate_custom_column( $column, $post_id ) {
	$pod = pods( 'employee', $post_id );
	if ( 'picture' === $column ) {
		echo '<img src="'.$pod->display('picture').'" width=50 height=50 />';
	}
	if ( 'location' === $column ) {
		echo $pod->display('employee_location');
	}
	if ( 'team' === $column ) {
		echo $pod->display('employee_team');
	}
}

add_shortcode( 'birthdays', 'sp_display_birthday_widget' );
function sp_display_birthday_widget($atts){
	$transient = get_transient('employee_birthdays_'.strtolower(date('M')));
	if ($transient!==false) { return $transient; }
	$employees = pods('employee', array('limit' => -1));
	$birthdays = array();
	
	$output = '<ul id="employee_birthdays_list">';

	$thismonthbirthdays = array();
	if ($employees->total() > 0)  {
		while($employees->fetch()) {
			$name = $employees->field('first_name').' '.$employees->field('last_name');
			$birthday = $employees->field('birthday');
			$link = $employees->field('permalink');
			if (empty($birthday)) { continue; }
			$date = explode("-",$birthday);
			$day = $date[2];
			$month = $date[1];
			if (intval($month) == intval(date('m'))) {
				array_push($thismonthbirthdays,array(
					'link' => $link,
					'name' => $name,
					'day' => $day
				));
			}
		}
	}
	usort($thismonthbirthdays, function ($item1, $item2) { return $item1['day'] <=> $item2['day'];});
	foreach($thismonthbirthdays as $employeedata){
		$output.= '<li><a href="'.$employeedata['link'].'"><span class="employee_name">'.$employeedata['name'].'</span> <span class="employee_birthday">'.$employeedata['day']."/".date('M').'</span></a></li>';
	}
	
	$output.= '</ul>';

	$end = strtotime('+1 month',strtotime(date('m').'/01/'.date('Y').' 00:00:00')) - 1;
	$now = time();
	$expiration = $end - $now;
	set_transient('employee_birthdays_'.strtolower(date('M')),$output,$expiration);
	return $output;
}

// Autoredirect if search only returns one result
add_action('template_redirect', 'sp_single_result');
function sp_single_result() {
    if (is_search()) {
        global $wp_query;
        if ($wp_query->post_count == 1) {
            wp_redirect( get_permalink( $wp_query->posts['0']->ID ) );
        }
    }
}

add_action('wp_footer', 'sp_add_script');
function sp_add_script() {
	?>
<script>
jQuery(document).ready(function(){
	jQuery(".elementor-element-f22bdfe a").on("click",function(e){
		e.preventDefault();
		toggleEmployeesLocation(jQuery(this));
		return false;
	});
});

function toggleEmployeesLocation(el) {
	let loc = jQuery(el).text();
	jQuery(".elementor-grid article").each(function(){
		if (loc=="Melbourne") {
			if (!jQuery(this).find('.elementor-widget-flip-box').hasClass('eloc_'+loc)) {
				jQuery(this).animate({width:0,marginLeft:"-100%"},300,function(){ jQuery(this).hide().addClass('isHidden'); });
			} else {
				if(jQuery(this).hasClass('isHidden')) {
					jQuery(this).show().animate({width:"100%",marginLeft:0},300).removeClass('isHidden');
				}
			}
		} else {
			if (jQuery(this).find('.elementor-widget-flip-box').hasClass('eloc_Melbourne')) {
				jQuery(this).animate({width:0,marginLeft:"-100%"},300,function(){ jQuery(this).hide().addClass('isHidden'); });
			} else {
				if(jQuery(this).hasClass('isHidden')) {
					jQuery(this).show().animate({width:"100%",marginLeft:0},300).removeClass('isHidden');
				}
			}
		}
	});
}
</script>
	<?php
}
//add_action('wp_footer','sp_playground');
function sp_playground(){
	if (!isset($_GET['play'])) { return; }
	$employees = pods('employee', array('limit' => 1));
	while($employees->fetch()) {
		echo '<pre>';
		var_dump($employees);
		echo '</pre>';
	}
}

/* INCLUDE CUSTOM FUNCTIONS */
include( 'inc/custom-functions.php' );
// DISABLE ELEMENTOR METADATA
add_filter( "hello_elementor_description_meta_tag", "__return_false" );
