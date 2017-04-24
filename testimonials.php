<?php
/*
Plugin Name: GW Testimonials
Plugin URI: http://www.gippslandweb.com
Description: Front end submission for Testimonials and shortcodes for displaying.
Version: 0.1
Author: GippslandWeb
Author URI: http://www.gippslandweb.com.au
GitHub Plugin URI: Gippsland-Web/gw-testimonials
*/

class GW_Testimonials {

function __construct() {
   add_action("init",array($this,"Init"),10);
   add_action('wp_ajax_nopriv_testimonial_submission',array($this,'AJAXSubmitTestimonial'));
   add_action('wp_ajax_testimonial_submission',array($this,'AJAXSubmitTestimonial'));

   add_shortcode('testimonial-entry',array($this,'DisplayTestimonialEntryForm'));
   add_shortcode('testimonials',array($this,'DisplayTestimonials'));
   add_shortcode('testimonial-single',array($this,"DisplaySingleTestimonial"));
   add_shortcode('testimonial-slider',array($this,"DisplaySliderTestimonial"));
   add_action('wp_enqueue_scripts',array($this,'QueueScripts'));

   add_action('admin_init', array($this,'InitSettings'));
   add_action('admin_menu', array($this,'InitAdminMenu'));
}

function Init() {
    $args = array(
        'public' => true,
        'labels' => array('name' => 'Testimonials', 'singular_name' => 'Testimonial'),

    );
    register_post_type('gw-testimonial',$args);
}
function InitAdminMenu() {
    add_options_page("GW Testimonials", "GW Testimonials","manage_options","gw-test-settings",array($this,"DisplaySettings"));
}
function DisplaySettings() {
    if(current_user_can('manage_options'))
        $this->IncludeTemplate("testimonial-settings.php");
}

function InitSettings() {


    
}

function QueueScripts() {
    wp_enqueue_style("gw-testimonial-styles",plugin_dir_url(__FILE__).'assets/style.css');
    wp_enqueue_script('jquery');
    wp_enqueue_script('gw-testimonial',plugin_dir_url(__FILE__).'assets/gw-testimonials.js',array(),null,true);
    wp_enqueue_script('gw-testimonial-slider',plugin_dir_url(__FILE__).'assets/jquery.sudoSlider.min.js');

    wp_localize_script("gw-testimonial","gwTestimonialOptions",array('ajaxUrl' => admin_url('admin-ajax.php'), "nonce" => wp_create_nonce('gw-tesimonials')));

}
function AJAXSubmitTestimonial() {
    $content = sanitize_text_field($_POST['content']);
    $name = sanitize_text_field($_POST['name']);
    if(strlen($name) < 3 || strlen($content) < 10)
        return;
    $postArgs = array("post_content" => $content, 'post_title' => $name, 'post_status' => 'pending', 'post_type' =>'gw-testimonial');
    $res = wp_insert_post($postArgs);
    echo($postArgs);
    echo($res);
}
function DisplaySliderTestimonial($atts = [], $content = null, $tag = '') {
    $atts = array_change_key_case((array)$atts,CASE_LOWER);
    $atts = shortcode_atts(["template" => 'testimonial-slider.php', 'pause'=> 3000, 'numposts' => 5, 'effect' => 'pushInRight', 'auto' => true, 'prevnext' => false],$atts,$tag);
    $perpage = intval($atts['numposts']);
    $query = new WP_query(array('post_type' =>'gw-testimonial', 'orderby' => 'date' , 'posts_per_page' => $perpage));
    ob_start();
    while($query->have_posts()){
        $query->the_post();
        $this->IncludeTemplate($atts['template']);
    }
        wp_localize_script("gw-testimonial","gwTestimonialOptions",array('sliderPause' => $atts['pause'], 'sliderEffect' => $atts['effect'],'sliderAuto' => $atts['auto'], 'sliderPrevNext' => $atts['prevnext'], 'sliderPause' => 5000, 'ajaxUrl' => admin_url('admin-ajax.php'), "nonce" => wp_create_nonce('gw-tesimonials')));

    return '<div id="testimonial-slider"><ul>'.ob_get_clean().'</ul></div>';   
}

function DisplayTestimonials($atts = [], $content = null, $tag = '') {
    $atts = array_change_key_case((array)$atts,CASE_LOWER);
    $atts = shortcode_atts(["template" => 'testimonial-archive.php', 'numposts' => 5],$atts,$tag);
    $perpage = intval($atts['numposts']);
    $query = new WP_query(array('post_type' =>'gw-testimonial', 'orderby' => 'date' , 'posts_per_page' => $perpage));

    ob_start();
    while($query->have_posts()){
        $query->the_post();
        $this->IncludeTemplate('testimonial-single.php');
    }
    return ob_get_clean();   

    /*ob_start();
    $this->IncludeTemplate($atts['template']);
    return ob_get_clean();*/
}
function DisplaySingleTestimonial($atts = [], $content = null, $tag = '') {
    $atts = array_change_key_case((array)$atts,CASE_LOWER);
    $atts = shortcode_atts(["template" => 'testimonial-single.php', "id" => 0],$atts,$tag);
    if($atts['id'] != 0) {
        $query = new WP_query(array('p' => $atts['id'], 'post_type' =>'gw-testimonial'));
    }
    else {
        $query = new WP_query(array('post_type' =>'gw-testimonial', 'orderby' => 'date'));
    }
    if(!$query->have_posts())
        return;
    $query->the_post();
    ob_start();
    $this->IncludeTemplate($atts['template']);
    return ob_get_clean();
}

function DisplayTestimonialEntryForm($atts = [], $content = null, $tag = ''){
    $atts = array_change_key_case((array)$atts,CASE_LOWER);
    $atts = shortcode_atts(["template" => 'testimonial-entry.php'],$atts,$tag);
    ob_start();
    $this->IncludeTemplate($atts['template']);
    return ob_get_clean();
}

function IncludeTemplate($name) {
    $file = plugin_dir_path(__FILE__).'templates/'.$name;
    if($theme_file = locate_template(array('templates/'.$name))){
        $file = $theme_file;
    }
    include($file);
}

}
new \GW_Testimonials();