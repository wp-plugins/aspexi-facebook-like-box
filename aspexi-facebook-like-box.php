<?php
/*
Plugin Name: Aspexi Facebook Like Box
Plugin URI:  http://aspexi.com/downloads/aspexi-facebook-like-box-slider-hd/?src=free_plugin
Description: Plugin adds facebook like box slide on hover.
Author: Aspexi
Version: 1.1.0
Author URI: http://aspexi.com/
License: GPLv2 or later

    © Copyright 2014 Aspexi
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
defined('ABSPATH') or exit();

if ( !class_exists( 'AspexiFBlikebox' ) ) {

    define('ASPEXIFBLIKEBOX_VERSION', '1.1.0');

    class AspexiFBlikebox {

        public $cf          = array(); // config array
        private $messages   = array(); // admin messages
        private $errors     = array(); // admin errors

        public function __construct() {

            /* Configuration */
            $this->settings();

            add_action( 'admin_menu',           array( &$this, 'admin_menu'));
            add_action( 'init',                 array( &$this, 'init' ), 10 );
            add_action( 'wp_footer',            array( &$this, 'get_html' ), 21 );
            add_action( 'admin_enqueue_scripts',array( &$this, 'admin_scripts') );
            add_action( 'wp_enqueue_scripts',   array( &$this, 'init_scripts') );
            
            register_uninstall_hook( __FILE__, array( 'AspexiFBlikebox', 'uninstall' ) );
        }

        /* WP init action */
        public function init() {

            /* Internationalization */
            load_plugin_textdomain( 'aspexifblikebox', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
        }

        public function settings() {

            /* Defaults */
            $cf_default = array(
                'aspexifblikebox_version' => ASPEXIFBLIKEBOX_VERSION,
                'url' => '',
                'locale' => 'en_GB',
                'status' => 'enabled',
            );

            /* Install default options */
            if ( is_multisite() ) {
                if ( !get_site_option( 'aspexifblikebox_options' ) ) {
                    add_site_option( 'aspexifblikebox_options', $cf_default, '', 'yes' );
                }
            } else {
                if ( !get_option( 'aspexifblikebox_options' ) )
                    add_option( 'aspexifblikebox_options', $cf_default, '', 'yes' );
            }

            /* Get options from the database */
            if ( is_multisite() )
                $this->cf = get_site_option( 'aspexifblikebox_options' );
            else
                $this->cf = get_option( 'aspexifblikebox_options' );

            /* Upgrade */
            if( $this->cf['aspexifblikebox_version'] != ASPEXIFBLIKEBOX_VERSION ) {
                switch( $this->cf['aspexifblikebox_version'] ) {
                    case '1.0.0':
                        $this->cf['status'] = 'enabled';
                        $this->cf['aspexifblikebox_version'] = ASPEXIFBLIKEBOX_VERSION;
                        update_option( 'aspexifblikebox_options',  $this->cf, '', 'yes' );
                    break;
                }
            }
        }

        private function add_message( $message ) {
            $message = trim( $message );

            if( strlen( $message ) )
                $this->messages[] = $message;
        }

        private function add_error( $error ) {
            $error = trim( $error );

            if( strlen( $error ) )
                $this->errors[] = $error;
        }

        public function has_errors() {
            return count( $this->errors );
        }

        public function display_admin_notices( $echo = false ) {
            $ret = '';

            foreach( (array)$this->errors as $error ) {
                $ret .= '<div class="error fade"><p><strong>'.$error.'</strong></p></div>';
            }

            foreach( (array)$this->messages as $message ) {
                $ret .= '<div class="updated fade"><p><strong>'.$message.'</strong></p></div>';
            }

            if( $echo )
                echo $ret;
            else
                return $ret;
        }

        public function admin_menu() {
            add_submenu_page( 'themes.php', __( 'Aspexi Facebook Like Box', 'aspexifblikebox' ), __( 'Facebook Like Box', 'aspexifblikebox' ), 'manage_options', 'aspexi-facebook-like-box.php', array( &$this, 'admin_page') );
        }

        public function admin_page() {

            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }

            // request action
            if ( isset( $_REQUEST['afblb_form_submit'] ) && check_admin_referer( plugin_basename(__FILE__), 'afblb_nonce_name' ) ) {

                if( !in_array( $_REQUEST['afblb_status'], array('enabled','disabled') ) )
                    $this->add_error( __( 'Wrong or missing status. Available statuses: enabled and disabled. Settings not saved.', 'aspexifblikebox' ) );

                if( !$this->has_errors() ) {
                    $aspexifblikebox_request_options = array();

                    $aspexifblikebox_request_options['url']     = isset( $_REQUEST['afblb_url'] ) ? trim( $_REQUEST['afblb_url'] ) : '';
                    $aspexifblikebox_request_options['locale']  = isset( $_REQUEST['afblb_locale'] ) ? $_REQUEST['afblb_locale'] : '';
                    $aspexifblikebox_request_options['status']  = isset( $_REQUEST['afblb_status'] ) ? $_REQUEST['afblb_status'] : '';
                    $this->cf = array_merge( (array)$this->cf, $aspexifblikebox_request_options );

                    update_option( 'aspexifblikebox_options',  $this->cf, '', 'yes' );
                    $this->add_message( __( 'Settings saved.', 'aspexifblikebox' ) );

                    // Preview maybe
                    if( $_REQUEST['preview'] )
                        $preview = true;
                    else
                        $preview = false;  
                }   
            }

            // Locale
            $locales = array(
                'Afrikaans' => 'af_ZA',
                'Albanian' => 'sq_AL',
                'Arabic' => 'ar_AR',
                'Armenian' => 'hy_AM',
                'Aymara' => 'ay_BO',
                'Azeri' => 'az_AZ',
                'Basque' => 'eu_ES',
                'Belarusian' => 'be_BY',
                'Bengali' => 'bn_IN',
                'Bosnian' => 'bs_BA',
                'Bulgarian' => 'bg_BG',
                'Catalan' => 'ca_ES',
                'Cherokee' => 'ck_US',
                'Croatian' => 'hr_HR',
                'Czech' => 'cs_CZ',
                'Danish' => 'da_DK',
                'Dutch' => 'nl_NL',
                'Dutch (Belgium)' => 'nl_BE',
                'English (Pirate)' => 'en_PI',
                'English (UK)' => 'en_GB',
                'English (Upside Down)' => 'en_UD',
                'English (US)' => 'en_US',
                'Esperanto' => 'eo_EO',
                'Estonian' => 'et_EE',
                'Faroese' => 'fo_FO',
                'Filipino' => 'tl_PH',
                'Finnish' => 'fi_FI',
                'Finnish (test)' => 'fb_FI',
                'French (Canada)' => 'fr_CA',
                'French (France)' => 'fr_FR',
                'Galician' => 'gl_ES',
                'Georgian' => 'ka_GE',
                'German' => 'de_DE',
                'Greek' => 'el_GR',
                'Guaran' => 'gn_PY',
                'Gujarati' => 'gu_IN',
                'Hebrew' => 'he_IL',
                'Hindi' => 'hi_IN',
                'Hungarian' => 'hu_HU',
                'Icelandic' => 'is_IS',
                'Indonesian' => 'id_ID',
                'Irish' => 'ga_IE',
                'Italian' => 'it_IT',
                'Japanese' => 'ja_JP',
                'Javanese' => 'jv_ID',
                'Kannada' => 'kn_IN',
                'Kazakh' => 'kk_KZ',
                'Khmer' => 'km_KH',
                'Klingon' => 'tl_ST',
                'Korean' => 'ko_KR',
                'Kurdish' => 'ku_TR',
                'Latin' => 'la_VA',
                'Latvian' => 'lv_LV',
                'Leet Speak' => 'fb_LT',
                'Limburgish' => 'li_NL',
                'Lithuanian' => 'lt_LT',
                'Macedonian' => 'mk_MK',
                'Malagasy' => 'mg_MG',
                'Malay' => 'ms_MY',
                'Malayalam' => 'ml_IN',
                'Maltese' => 'mt_MT',
                'Marathi' => 'mr_IN',
                'Mongolian' => 'mn_MN',
                'Nepali' => 'ne_NP',
                'Northern Sami' => 'se_NO',
                'Norwegian (bokmal)' => 'nb_NO',
                'Norwegian (nynorsk)' => 'nn_NO',
                'Pashto' => 'ps_AF',
                'Persian' => 'fa_IR',
                'Polish' => 'pl_PL',
                'Portuguese (Brazil)' => 'pt_BR',
                'Portuguese (Portugal)' => 'pt_PT',
                'Punjabi' => 'pa_IN',
                'Quechua' => 'qu_PE',
                'Romanian' => 'ro_RO',
                'Romansh' => 'rm_CH',
                'Russian' => 'ru_RU',
                'Sanskrit' => 'sa_IN',
                'Serbian' => 'sr_RS',
                'Simplified Chinese (China)' => 'zh_CN',
                'Slovak' => 'sk_SK',
                'Slovenian' => 'sl_SI',
                'Somali' => 'so_SO',
                'Spanish' => 'es_LA',
                'Spanish (Chile)' => 'es_CL',
                'Spanish (Colombia)' => 'es_CO',
                'Spanish (Mexico)' => 'es_MX',
                'Spanish (Spain)' => 'es_ES',
                'Spanish (Venezuela)' => 'es_VE',
                'Swahili' => 'sw_KE',
                'Swedish' => 'sv_SE',
                'Syriac' => 'sy_SY',
                'Tajik' => 'tg_TJ',
                'Tamil' => 'ta_IN',
                'Tatar' => 'tt_RU',
                'Telugu' => 'te_IN',
                'Thai' => 'th_TH',
                'Traditional Chinese (Hong Kong)' => 'zh_HK',
                'Traditional Chinese (Taiwan)' => 'zh_TW',
                'Turkish' => 'tr_TR',
                'Ukrainian' => 'uk_UA',
                'Urdu' => 'ur_PK',
                'Uzbek' => 'uz_UZ',
                'Vietnamese' => 'vi_VN',
                'Welsh' => 'cy_GB',
                'Xhosa' => 'xh_ZA',
                'Yiddish' => 'yi_DE',
                'Zulu' => 'zu_ZA'
            );

            $locales_input = '<select name="afblb_locale">';

            foreach( $locales as $k => $v ) {
                $locales_input .= '<option value="'.$v.'"'.( ( $this->cf['locale'] == $v ) ? ' selected="selected"' : '' ).'>'.$k.'</option>';
            }

            $locales_input .= '</select>';

            // show form
            ?>
            <div class="wrap">
                <div id="icon-link" class="icon32"></div><h2><?php _e( 'Aspexi Facebook Like Box Settings', 'aspexifblikebox' ); ?></h2>
                <?php $this->display_admin_notices( true ); ?>
                <div id="poststuff" class="metabox-holder">
                    <div id="post-body">
                        <div id="post-body-content">
                            <form method="post" action="themes.php?page=aspexi-facebook-like-box.php">

                                <input type="hidden" name="afblb_form_submit" value="submit" />

                                <div class="postbox">
                                    <h3><span><?php _e('Settings', 'aspexifblikebox'); ?></span></h3>
                                    <div class="inside">
                                    <table class="form-table">
                                        <tbody>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Like Box', 'aspexifblikebox'); ?></th>
                                                <td><select name="afblb_status">
                                                    <option value="enabled"<?php if( 'enabled' == $this->cf['status'] ) echo ' selected="selected"'; ?>><?php _e('enabled', 'aspexifblikebox'); ?></option>
                                                    <option value="disabled"<?php if( 'disabled' == $this->cf['status'] ) echo ' selected="selected"'; ?>><?php _e('disabled', 'aspexifblikebox'); ?></option>
                                                    </select></td>
                                            </tr>                                        
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Facebook Page URL', 'aspexifblikebox'); ?></strong></th>
                                                <td>http://www.facebook.com/&nbsp;<input type="text" name="afblb_url" value="<?php echo $this->cf['url']; ?>" />
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Like Box Height', 'aspexifblikebox'); ?></th>
                                                <td><input type="text" name="afblb_height" value="285" size="3" disabled />&nbsp;px<?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Like Box Width', 'aspexifblikebox'); ?></th>
                                                <td><input type="text" name="afblb_width" value="245" size="3" disabled />&nbsp;px<?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Show Friends\' Faces', 'aspexifblikebox'); ?></th>
                                                <td><input type="checkbox" value="on" name="afblb_faces" checked disabled /><?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Number of Connections', 'aspexifblikebox'); ?><br /><span style="font-size: 10px"><?php _e('For auto generated number of connection set 0', 'aspexifblikebox'); ?></span></th>
                                                <td><input type="text" name="afblb_faces_count" value="0" size="3" disabled /><?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Show Posts (Stream)', 'aspexifblikebox'); ?></th>
                                                <td><input type="checkbox" value="on" name="afblb_stream" disabled /><?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Force Wall', 'aspexifblikebox'); ?><br /><span style="font-size: 10px"><?php _e('For "place" Pages (Pages that have a physical location that can be used with check-ins), this specifies whether the stream contains posts by the Page or just check-ins from friends.', 'aspexifblikebox'); ?></span></th>
                                                <td><input type="checkbox" value="on" name="afblb_wall" disabled /><?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Header', 'aspexifblikebox'); ?><br /><span style="font-size: 10px"><?php _e('Specifies whether to display the Facebook header at the top of the plugin.', 'aspexifblikebox'); ?></span></th>
                                                <td><input type="checkbox" value="on" name="afblb_header" disabled /><?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Localization', 'aspexifblikebox'); ?><br /><span style="font-size: 10px"><?php _e('Change might not be visible immediately due to Facebook / browser cache', 'aspexifblikebox'); ?></span></th>
                                                <td><?php echo $locales_input; ?></td>
                                            </tr>
                                                                                       
                                        </tbody>
                                    </table>
                                                                           
                                    </div>
                                </div>

                                <p><input class="button-primary" type="submit" name="send" value="<?php _e('Save settings', 'aspexifblikebox'); ?>" id="submitbutton" />
                                <input class="button-secondary" type="submit" name="preview" value="<?php _e('Save and preview', 'aspexifblikebox'); ?>" id="previewbutton" /></p>
                                <?php wp_nonce_field( plugin_basename( __FILE__ ), 'afblb_nonce_name' ); ?>

                                <div class="postbox">
                                    <h3><span><?php _e('Button Settings', 'aspexifblikebox'); ?></span></h3>
                                    <div class="inside">
                                    <table class="form-table">
                                        <tbody>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Button Space', 'aspexifblikebox'); ?><br /><span style="font-size: 10px"><?php _e('Space between button and page edge', 'aspexifblikebox'); ?></span></th>
                                                <td><input type="text" name="afblb_btspace" value="0" size="3" disabled />&nbsp;px<?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Button Placement', 'aspexifblikebox'); ?></th>
                                                <td><input type="radio" name="afblb_btvertical" value="top" disabled />&nbsp;<?php _e('top','aspexifblikebox'); ?><br />
                                                    <input type="radio" name="afblb_btvertical" value="middle" checked disabled />&nbsp;<?php _e('middle','aspexifblikebox'); ?><br />
                                                    <input type="radio" name="afblb_btvertical" value="bottom" disabled />&nbsp;<?php _e('bottom','aspexifblikebox'); ?><br />
                                                    <input type="radio" name="afblb_btvertical" value="fixed" disabled />&nbsp;<?php _e('fixed','aspexifblikebox'); ?>
                                                    <input type="text" name="afblb_btvertical_val" value="" size="3" disabled />&nbsp;px <?php _e('from slider top','aspexifblikebox'); ?><?php echo $this->get_pro_link(); ?>
                                                    </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Button Image', 'aspexifblikebox'); ?></th>
                                                <td><span><input type="radio" name="afblb_btimage" value="fb1-right" checked disabled />&nbsp;<img src="<?php echo plugins_url().'/'.basename( __DIR__ ).'/images/fb1-right.png'; ?>" alt="" style="cursor:pointer;" /></span>
                                                <span><input type="radio" name="afblb_btimage" value="" disabled />&nbsp;<img src="<?php echo plugins_url().'/'.basename( __DIR__ ).'/images/preview-buttons.jpg'; ?>" alt="" style="cursor:pointer;" /></span><?php echo $this->get_pro_link(); ?>
                                                </td>
                                            </tr>   
                                            <tr valign="top">
                                                <th scope="row"><?php _e('High Resolution', 'aspexifblikebox'); ?><br /><span style="font-size: 10px"><?php _e('Use SVG high quality images instead of PNG if possible. Recommended for Retina displays (iPhone, iPad, MacBook Pro).', 'aspexifblikebox'); ?></span></th>
                                                <td><input type="checkbox" value="on" name="afblb_bthq" disabled /><?php echo $this->get_pro_link(); ?></td>
                                            </tr>                              
                                        </tbody>
                                    </table>
                                    </div>
                                </div>

                                <p><input class="button-primary" type="submit" name="send" value="<?php _e('Save settings', 'aspexifblikebox'); ?>" id="submitbutton" />
                                <input class="button-secondary" type="submit" name="preview" value="<?php _e('Save and preview', 'aspexifblikebox'); ?>" id="previewbutton" /></p>

                                <div class="postbox">
                                    <h3><span><?php _e('Advanced Look and Feel', 'aspexifblikebox'); ?></span></h3>
                                    <div class="inside">
                                    <table class="form-table">
                                        <tbody>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Placement', 'aspexifblikebox'); ?></th>
                                                <td><select name="afblb_placement" disabled>
                                                    <option value="left"><?php _e('left', 'aspexifblikebox'); ?></option>
                                                    <option value="right" selected="selected"><?php _e('right', 'aspexifblikebox'); ?></option>
                                                    </select><?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Vertical placement', 'aspexifblikebox'); ?></th>
                                                <td><input type="radio" name="afblb_vertical" value="middle" checked disabled />&nbsp;<?php _e('center','aspexifblikebox'); ?><br />
                                                    <input type="radio" name="afblb_vertical" value="fixed" disabled />&nbsp;<?php _e('fixed','aspexifblikebox'); ?>
                                                    <input type="text" name="afblb_vertical_val" value="" size="3" disabled />&nbsp;px <?php _e('from page top','aspexifblikebox'); ?><?php echo $this->get_pro_link(); ?>
                                                </td>
                                            </tr>    
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Color Scheme', 'aspexifblikebox'); ?></th>
                                                <td><select name="afblb_colorscheme" disabled>
                                                    <option value="light" selected="selected"><?php _e('light', 'aspexifblikebox'); ?></option>
                                                    <option value="dark"><?php _e('dark', 'aspexifblikebox'); ?></option>
                                                    </select><?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Border Color', 'aspexifblikebox'); ?></th>
                                                <td><input type="text" name="afblb_bordercolor" class="bordercolor-field" value="#3B5998" size="6" disabled /><?php echo $this->get_pro_link(); ?></td>
                                            </tr>    
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Border Width', 'aspexifblikebox'); ?></th>
                                                <td><input type="text" name="afblb_borderwidth" value="2" size="3" disabled />&nbsp;px<?php echo $this->get_pro_link(); ?></td>
                                            </tr>   
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Background Color', 'aspexifblikebox'); ?></th>
                                                <td><input type="text" name="afblb_bgcolor" class="bgcolor-field" value="#FFFFFF" size="6" disabled /><?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Slide on Mouse', 'aspexifblikebox'); ?></th>
                                                <td><select name="afblb_slideon" disabled>
                                                    <option value="hover" selected="selected"><?php _e('hover', 'aspexifblikebox'); ?></option>
                                                    <option value="click"><?php _e('click', 'aspexifblikebox'); ?></option>
                                                    </select><?php echo $this->get_pro_link(); ?></td>
                                            </tr>   
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Slide Time', 'aspexifblikebox'); ?></th>
                                                <td><input type="text" name="afblb_slidetime" value="400" size="3" disabled />&nbsp;<?php _e('milliseconds', 'aspexifblikebox'); ?><?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Disable on GET', 'aspexifblikebox'); ?><br /><span style="font-size: 10px"><?php _e('Example: set Parameter=iframe and Value=true. Like Box will be disabled on all URLs like yourwebsite.com/?iframe=true.', 'aspexifblikebox'); ?></span></th>
                                                <td><?php _e('Parameter', 'aspexifblikebox'); ?>:&nbsp;<input type="text" name="afblb_disableparam" value="" size="6" disabled /><br />
                                                    <?php _e('Value', 'aspexifblikebox'); ?>:&nbsp;<input type="text" name="afblb_disableval" value="" size="6" disabled /><?php echo $this->get_pro_link(); ?>
                                                </td>
                                            </tr>                        
                                        </tbody>
                                    </table>
                                    </div>
                                </div>

                                <p><input class="button-primary" type="submit" name="send" value="<?php _e('Save settings', 'aspexifblikebox'); ?>" id="submitbutton" />
                                <input class="button-secondary" type="submit" name="preview" value="<?php _e('Save and preview', 'aspexifblikebox'); ?>" id="previewbutton" /></p>

                                <div class="postbox">
                                    <h3><span><?php _e('Enable on Mobile', 'aspexifblikebox'); ?></span></h3>
                                    <div class="inside">
                                    <table class="form-table">
                                        <tbody>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('iPad & iPod', 'aspexifblikebox'); ?></th>
                                                <td><input type="checkbox" value="on" name="afblb_edipad" checked disabled /><?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                             <tr valign="top">
                                                <th scope="row"><?php _e('iPhone', 'aspexifblikebox'); ?></th>
                                                <td><input type="checkbox" value="on" name="afblb_ediphone" checked disabled /><?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Android', 'aspexifblikebox'); ?></th>
                                                <td><input type="checkbox" value="on" name="afblb_edandroid" checked disabled /><?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Other Mobile Devices', 'aspexifblikebox'); ?></th>
                                                <td><input type="checkbox" value="on" name="afblb_edothers" checked disabled /><?php echo $this->get_pro_link(); ?></td>
                                            </tr>                         
                                        </tbody>
                                    </table>
                                    </div>
                                </div>

                                <p><input class="button-primary" type="submit" name="send" value="<?php _e('Save settings', 'aspexifblikebox'); ?>" id="submitbutton" />
                                <input class="button-secondary" type="submit" name="preview" value="<?php _e('Save and preview', 'aspexifblikebox'); ?>" id="previewbutton" /></p>
                            </form>
                            <div class="postbox">
                                <h3><span>Made by</span></h3>   
                                <div class="inside">
                                    <div style="width: 170px; margin: 0 auto;">
                                        <a href="http://aspexi.com/" target="_blank"><img src="<?php echo plugins_url().'/'.basename( __DIR__ ).'/images/aspexi300.png'; ?>" alt="" border="0" width="150" /></a>
                                    </div>
                                </div>
                            </div>   
                        </div>                                             
                    </div>
                </div>
            </div>
            <?php
            // Preview
            if( $preview ) {
                $this->init_scripts();
                echo $this->get_html($preview);
            }
        }

        public function get_pro_link() {
            $ret = '';

            $ret .= '&nbsp;&nbsp;&nbsp;<a href="http://aspexi.com/downloads/aspexi-facebook-like-box-slider-hd/?src=free_plugin" target="_blank">Get PRO version</a>';

            return $ret;
        }

        public function get_html( $preview = false ) {

            $url            = apply_filters( 'aspexifblikebox_url', $this->cf['url'] );
            $status         = apply_filters( 'aspexifblikebox_status', $this->cf['status'] );

            // Disable maybe
            if( ( !strlen( $url ) || 'enabled' != $status ) && !$preview )
                return;

            // Options
            $locale         = apply_filters( 'aspexifblikebox_locale', $this->cf['locale'] );
            $height         = 285;
            $width          = 245;
            $placement      = 'right';
            $btspace        = 0;
            $btimage        = 'fb1-right.png';
            $bordercolor    = '#3B5998';
            $borderwidth    = 2;
            $bgcolor        = '#ffffff';
  
            $css_placement = array();
            if( 'left' == $placement ) {
                $css_placement[0] = 'right';
                $css_placement[1] = '0 '.(48+$btspace).'px 0 5px';
            } else {
                $css_placement[0] = 'left';
                $css_placement[1] = '0 5px 0 '.(48+$btspace).'px';
            }

            $css_placement[2] = '50%;margin-top:-'.floor($height/2).'px';

            $stream     = 'false';
            $header     = 'false';

            // Facebook button image (check in THEME CHILD -> THEME PARENT -> PLUGIN DIR)
            // TODO: move this to admin page
            $users_button_custom    = '/plugins/aspexi-facebook-like-box/images/aspexi-fb-custom.png';
            $users_button_template  = get_template_directory() . $users_button_custom;
            $users_button_child     = get_stylesheet_directory() . $users_button_custom;
            $button_uri             = '';

            if( file_exists( $users_button_child ) )
                $button_uri = get_stylesheet_directory_uri() . $users_button_custom;
            elseif( file_exists( $users_button_template ) )
                $button_uri = get_template_directory_uri() . $users_button_custom;
            elseif( file_exists( plugin_dir_path( __FILE__ ).'images/'.$btimage ) )
                $button_uri = plugins_url().'/'.basename( __DIR__ ).'/images/'.$btimage;

            if( '' == $button_uri ) {
                $button_uri = plugins_url().'/'.basename( __DIR__ ).'/images/fb1-right.png';
            }

            $button_uri  = apply_filters( 'aspexifblikebox_button_uri', $button_uri );
            
            $output = '';

            $output .= '<style type="text/css"> .aspexifblikebox{box-sizing: content-box;-webkit-box-sizing: content-box;-moz-box-sizing: content-box;background: url("'.$button_uri.'") no-repeat scroll '.$css_placement[0].' '.$css_placement[3].' transparent;'.$css_placement[4].' float: '.$placement.';height: '.$height.'px;padding: '.$css_placement[1].';width: '.$width.'px;z-index:  99999;position:fixed;'.$placement.':-'.($width+5).'px;top:'.$css_placement[2].';cursor:pointer;} .aspexifblikebox div{ padding: 0; margin-'.$placement.':-8px; border:'.$borderwidth.'px solid '.$bordercolor.'; background:'.$bgcolor.';} .aspexifblikebox span{bottom: 4px;font: 8px "lucida grande",tahoma,verdana,arial,sans-serif;position: absolute;'.$placement.': 6px;text-align: '.$placement.';z-index: 99999;} .aspexifblikebox span a{color: gray;text-decoration:none;} .aspexifblikebox span a:hover{text-decoration:underline;} } </style><div class="aspexifblikebox"><div><iframe src="http://www.facebook.com/plugins/likebox.php?locale='.$locale.'&href=http%3A%2F%2Ffacebook.com%2F'.$url.'&amp;width='.$width.'&amp;colorscheme=light&amp;connections=0&amp;show_border=false&amp;border_color=white&amp;header='.$header.'&amp;height='.$height.'" scrolling="no" frameborder="0" scrolling="no" style="border: white; overflow: hidden; height: '.$height.'px; width: '.$width.'px;background:'.$bgcolor.';"></iframe></div> </div>';

            $output = apply_filters( 'aspexifblikebox_output', $output );

            echo $output;
        }

        public function init_scripts() {
            $width      = 245;
            $placement  = 'right';
            $slideon    = 'hover';

            wp_enqueue_script( 'aspexi-facebook-like-box', plugins_url( basename( __DIR__ ) . '/js/aflb.js' ), array( 'jquery' ), false, true );
            wp_localize_script( 'aspexi-facebook-like-box', 'aflb', array(
                    'slideon'   => $slideon,
                    'placement' => $placement,
                    'width'     => (int)$width
                ) );
        }

        public static function uninstall() {

            if ( !is_multisite() ) 
                delete_option( 'aspexifblikebox_options' );
            else {
                global $wpdb;
                $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
                $original_blog_id = get_current_blog_id();
                foreach ( $blog_ids as $blog_id ) {
                    switch_to_blog( $blog_id );
                    delete_option( 'aspexifblikebox_options' );  
                }
                switch_to_blog( $original_blog_id );

                delete_site_option( 'aspexifblikebox_options' );  
            }
        }

        public function admin_scripts() {
            // premium only
            return;
        }
    }

    /* Let's start the show */
    global $aspexifblikebox;

    $aspexifblikebox = new AspexiFBlikebox();
}