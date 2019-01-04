<?php
/*
Plugin Name: Very Simple Google Analytics Tracking 
Description: Adds a Google analytics tracking code to the <head> of the theme, by hooking to wp_head. GA code can be added in Settings->Very Simple Google Analytics Tracking
Author: Vlad Duma
Tags: Simple, very simple, Google Analytics, Tracking, GA for WP
Version: 2.0
Licence: GPL2 or later
Licence URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Very Simple Google Analytics Tracking Tracking is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or any later version. 

Very Simple Google Analytics Tracking is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Very Simple Google Analytics Tracking. If not, see https://www.gnu.org/licenses/old-licenses/gpl-2.0.htm.
*/

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This plugin requires WordPress' );
}

class vsgat_settings // Creates a settings page
{
    public function __construct()
    {
        // Hook into the admin menu
        add_action('admin_menu', array(
            $this,
            'vsgat_create_plugin_settings_page'
        ));
    }
    
    public function vsgat_create_plugin_settings_page() //Page description
    {
        // Add the menu item and page
        $page_title = 'Very Simple Google Analytics Tracking Settings Page';
        $menu_title = 'Very Simple Google Analytics';
        $capability = 'manage_options';
        $slug       = 'vgsat_settings_slug';
        $callback   = array(
            $this,
            'vsgat_plugin_settings_page_content'
        );
        $icon       = 'dashicons-admin-plugins';
        $position   = 100;
        
        add_submenu_page('options-general.php', $page_title, $menu_title, $capability, $slug, $callback); 
        add_action('admin_init', array(
            $this,
            'setup_sections'
        ));
        add_action('admin_init', array(
            $this,
            'vsgat_setup_fields'
        ));
    }
    
    public function vsgat_plugin_settings_page_content() //HTML for the heading
    { ?>
    <div class="wrap">
        <h2>Very Simple Google Analytics Tracking Settings Page</h2>
        <form method="post" action="options.php">
            <?php
        settings_fields('vgsat_settings_slug');
        do_settings_sections('vgsat_settings_slug');
        submit_button();?>
        </form>
    </div> 
   <?php    }
    
    public function setup_sections()
    {
        add_settings_section('first_heading', 'Set up your very simple Google Analytics plugin here', array(
            $this,
            'section_callback'
        ), 'vgsat_settings_slug');
    }
    
    
    public function vsgat_setup_fields() //Creates a field for GA tracking ID
    {
        $fields = array(
            array(
                'uid' => 'ga_code_field',
                'label' => 'Enter your GA tracking ID here:',
                'section' => 'first_heading',
                'type' => 'text',
                'options' => false,
                'placeholder' => 'UA-XXXXXXXX-X',
                'helper' => '',
                'supplemental' => 'Google Analytics->Admin->Property->Tracking Info'
                
            )
        );
        foreach ($fields as $field) {
            add_settings_field($field['uid'], $field['label'], array(
                $this,
                'vsgat_field_callback'
            ), 'vgsat_settings_slug', $field['section'], $field);
            register_setting('vgsat_settings_slug', $field['uid']);
        }
    }
    
    public function vsgat_field_callback($arguments) //Adds the field
    {
        $value = get_option($arguments['uid']); // Get the current value, if there is one
        if (!$value) { // If no value exists
            $value = $arguments['default']; // Set to our default
        }
        
        // Check which type of field we want
        switch ($arguments['type']) {
            case 'text': // If it is a text field
                printf('<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value);
                break;
        }
        
        // If there is help text
        if ($helper = $arguments['helper']) {
            printf('<span class="helper"> %s</span>', $helper); // Show it
        }
        
        // If there is supplemental text
        if ($supplimental = $arguments['supplemental']) {
            printf('<p class="description">%s</p>', $supplimental); // Show it
        }
    }
}
new vsgat_settings();

function vsgat_code(){ // GA tracking code
?>
	<script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
		
		ga('create', '<?php echo get_option('ga_code_field');?>', 'auto');
		ga('send', 'pageview');
		
		</script>
<?php
}
add_action('wp_head', 'vsgat_code', 10); //Hooks code to wp_head 