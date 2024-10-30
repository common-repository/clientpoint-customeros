<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/*
    Plugin Name: ClientPoint CustomerOS
    Description: Transform your site with ClientPoint CustomerOS: Chat, schedule meetings, share docs, and engage visitors seamlessly.
    Version: 1.0.1
    Author: ClientPoint
    Author URI: https://www.clientpoint.net/
    License: GPL-2.0+
*/

/**
 * This function integrates the ClientPoint CustomerOS Profile & Appearance Settings into the WordPress Dashboard, offering a platform to tailor settings specific to CustomerOS preferences.
 * 
 * @author Haris Isani
*/
function clientpoint_inject_settings_page() {
    ?>
    <div class="wrap">
        <h1>ClientPoint CustomerOS</h1>
        <form method="post" action="options.php">
            <?php
                settings_fields('clientpoint_settings_group');
                do_settings_sections('clientpoint-settings');

                $current_url = get_home_url();
                $parsed_url = wp_parse_url($current_url);
                $current_domain = $parsed_url['host'];

                // Fetch all field names used in the plugin settings
                $apiUrl = 'https://customeros.clientpointsolutions.com/wordpress/get-plugin.php?action=clientpoint_form&domain='.$current_domain;
                $response = wp_remote_get($apiUrl);
                if ( is_array( $response ) && ! is_wp_error( $response ) ) {
                $formData = json_decode( $response['body'], true );
                ?>
                <table class="form-table">
                <?php foreach($formData as $key => $value){ ?> 
                    <tr valign="top">
                        <td nowrap scope="row"><?php echo esc_js ($value['label']) ?></td>
                        <td>
                            <?php if($value['type']=="input") { ?> 
                                <input <?php echo esc_js ($value['required']) ?> style="width:300px" type="text" id="<?php echo esc_js ($value['name']) ?>" name="<?php echo esc_js ($value['name']) ?>" value="<?php echo !empty(get_option ($value['name']))? esc_js(get_option ($value['name'])) : esc_js ($value['defaultValue'])  ?>">
                            <?php } ?> 
                            <?php if($value['type']=="textarea") { ?> 
                                <textarea <?php echo esc_js ($value['required']) ?> style="width:300px;height: 100px;" name="<?php echo esc_js ($value['name']) ?>" id="<?php echo esc_js ($value['name']) ?>" cols="30" rows="10"><?php echo !empty(get_option ($value['name']))? esc_js(get_option ($value['name'])) : esc_js ($value['defaultValue'])  ?></textarea>
                            <?php } ?> 
                            <?php if($value['type']=="select") { ?> 
                                <select <?php echo esc_js ($value['required']) ?> id="<?php echo esc_js ($value['name']) ?>" name="<?php echo esc_js ($value['name']) ?>">
                                    <?php
                                        $selectedValue = !empty(get_option ($value['name']))? get_option ($value['name']) : esc_js ($value['defaultValue']);
                                        $allSelectValues=explode(',', $value['values']);
                                        $allSelectLabels=explode(',', $value['valueLabels']);
                                    ?>
                                     <?php foreach($allSelectValues as $key1 => $value1){ 
                                        $selectedIndex = ($selectedValue==$value1)? "selected" : "";
                                        ?> 
                                        <option <?php echo esc_js ($selectedIndex) ?> value="<?php echo esc_js ($value1) ?>" ><?php echo esc_js ($allSelectLabels[$key1]) ?></option>
                                    <?php } ?> 
                                </select>
                            <?php } ?> 
                            <?php if($value['type']=="color") { ?> 
                                <input <?php echo esc_js ($value['required']) ?> style="width:300px" type="color" id="<?php echo esc_js ($value['name']) ?>" name="<?php echo esc_js ($value['name']) ?>" value="<?php echo !empty(get_option ($value['name']))? esc_js(get_option ($value['name'])) : esc_js ($value['defaultValue'])  ?>">
                            <?php } ?> 
                        </td>
                        <td>
                            <?php echo esc_html($value['description']) ?>
                        </td>
                    </tr>
                <?php }} ?>
                    <tr>
                        <td colspan=3><ul style="list-style-type: none; margin: 0; padding: 0;"><li style="margin-bottom: 10px;">Notes:<ol style="list-style-type: decimal; margin: 0; padding-left: 20px;"><li style="margin-bottom: 5px;"><p style="margin: 0;">Read More at <a target="_blank" href="https://support.clientpoint.net/hc/en-us/categories/13354525569687-ClientPoint-CustomerOS" style="color: #712C63; text-decoration: none;font-weight:bold;">ClientPoint CustomerOS Support</a></p></li><li style="margin-bottom: 5px;"><p style="margin: 0;">If you encounter any issues during the CustomerOS setup process, please <a href="https://support.clientpoint.net/hc/en-us/requests/new" target="_blank" style="font-weight:bold; color: #712C63; text-decoration: none;">submit a request to support</a> for prompt assistance.</p></li></ol></li></ul></td>
                    </tr>
                </table>
                <?php submit_button(); 
            ?>
        </form>
    </div>
    <?php
}

/**
 * This function inserts the ClientPoint CustomerOS main Script into the WordPress Head, ensuring its presence on all web pages.
 * 
 * @author Haris Isani
*/
function clientpoint_enqueue_main_scripts() {
    wp_enqueue_script('cp-wm-sidebar-js', 'https://cdn.clientpoint.me/freemium-js/cp-sidebar.js', array(), null, true); // 'true' for deferred loading
}
add_action('wp_head', 'clientpoint_enqueue_main_scripts');

/**
 * This function inserts the ClientPoint CustomerOS Custom Script into the WordPress Head, ensuring its presence on all web pages.
 * 
 * @author Haris Isani
*/
function clientpoint_inject_code() {
    $current_url = get_home_url();
    $parsed_url = wp_parse_url($current_url);
    $current_domain = $parsed_url['host'];
    
    // Fetch all field names used in the plugin settings
    $apiUrl = 'https://customeros.clientpointsolutions.com/wordpress/get-plugin.php?action=variables&domain='.$current_domain;
    $response = wp_remote_get($apiUrl);
    if ( is_array( $response ) && ! is_wp_error( $response ) ) {
        $jsonData = json_decode( $response['body'], true );
        $allVariables = explode(',', $jsonData['all_variables']);
        $output = array();
        ?>
        <script>
            function isDesktop() {
                const userAgent = navigator.userAgent.toLowerCase();
                
                // Check for mobile devices by user agent string
                const isMobile = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini|mobile|tablet/.test(userAgent);
                
                // Additional check for screen width (this is arbitrary, you can adjust as needed)
                const isSmallScreen = window.innerWidth <= 1024;

                return !isMobile && !isSmallScreen;
            }

            const clientpointSettings = {
                <?php foreach ($allVariables as $value) { ?>
                    <?php 
                        $optionValue = get_option($value);
                        $escapedValue = str_replace("#", "", $optionValue);
                    ?>
                    <?php echo esc_js($value); ?>: <?php echo($escapedValue=="true" || $escapedValue=="false") ?  esc_js($escapedValue)  : '"' . esc_js($escapedValue) . '"'; ?>,
                <?php } ?>
            };
            window.addEventListener("load", async (event) => {
                // Define the isDesktop function
                function isDesktop() {
                    const userAgent = navigator.userAgent.toLowerCase();

                    // Check for mobile devices by user agent string
                    const isMobile = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini|mobile|tablet/.test(userAgent);
                    
                    // Additional check for screen width (this is arbitrary, you can adjust as needed)
                    const isSmallScreen = window.innerWidth <= 1024;

                    return !isMobile && !isSmallScreen;
                }

                async function executeAfterSeconds(seconds) {
                    seconds = (seconds=="")? 0 : seconds;
                    setTimeout(async function() {
                        await ClientPoint.init(clientpointSettings);
                        <?php if(get_option('openDefaultDesktop')=="true" && get_option('showCPIcon')=="true"  ){ ?>
                        if (isDesktop()) {
                            var element = document.getElementById('cp-wm-sb-iconInner-block');
                            element.click();
                        }
                        <?php } ?>
                    }, seconds * 1000); // Convert seconds to milliseconds
                }
                executeAfterSeconds(<?php echo esc_js(get_option('delayDuration')); ?>);      
            });
        </script>
        <?php
    }
}
add_action('wp_head', 'clientpoint_inject_code');

/**
 * This function adds the ClientPoint CustomerOS menu option to the WordPress Admin Dashboard, facilitating easy access to CustomerOS features.
 * 
 * @author Haris Isani
*/
function clientpoint_inject_add_menu_page() {
    // Add menu page with icon
    $icon_url = 'https://plugins.svn.wordpress.org/clientpoint-customeros/assets/ClientPoint_Mark_white_registered.png';
    add_menu_page(
        'ClientPoint Settings',
        'ClientPoint',
        'manage_options',
        'clientpoint-settings',
        'clientpoint_inject_settings_page',
        $icon_url
    );
}
add_action('admin_menu', 'clientpoint_inject_add_menu_page');

function clientpoint_inject_custom_js() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#toplevel_page_clientpoint-settings img').attr('alt', 'ClientPoint Icon');
        });
    </script>
    <?php
}
add_action('admin_footer', 'clientpoint_inject_custom_js');

/**
 * This function seamlessly integrates the ClientPoint CustomerOS Setting page into the Admin Dashboard's navigation bar, ensuring convenient access to CustomerOS settings for administrators.
 * 
 * @author Haris Isani
*/
function clientpoint_register_settings() {
    $current_url = get_home_url();
    $parsed_url = wp_parse_url($current_url);
    $current_domain = $parsed_url['host'];
    
    // Fetch all field names used in the plugin settings
    $apiUrl = 'https://customeros.clientpointsolutions.com/wordpress/get-plugin.php?action=variables&domain='.$current_domain;
    $response = wp_remote_get($apiUrl);
    if ( is_array( $response ) && ! is_wp_error( $response ) ) {
        $jsonData = json_decode( $response['body'], true );
        $allVariables = explode(',', $jsonData['all_variables']);
        
        // Register settings
        foreach($allVariables as $value){
            register_setting('clientpoint_settings_group', $value);
        }
    }
}
add_action('admin_init', 'clientpoint_register_settings');

/**
 * This function seamlessly incorporates the ClientPoint CustomerOS custom style into the ClientPoint icon on the Admin Dashboard's Navbar, enhancing its appearance and aligning it with the CustomerOS branding.
 * 
 * @author Haris Isani
*/
function clientpoint_inject_custom_styles() {
    ?>
    <style>
        #adminmenu #toplevel_page_clientpoint-settings .wp-menu-image img {
            max-width: 16px;
            max-height: 16px;
        }
    </style>
    <?php
}
add_action('admin_head', 'clientpoint_inject_custom_styles');
?>
