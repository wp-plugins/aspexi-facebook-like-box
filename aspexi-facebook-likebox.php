<?php
/*
Plugin Name: Aspexi Facebook Like Box
Plugin URI:  http://aspexi.com/
Description: Plugin adds facebook like box slide on hover.
Author: Aspexi
Version: 1.0.0
Author URI: http://aspexi.com/
License: GPLv2 or later
*/
/*  © Copyright 2014 Aspexi
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
/*
TODO

[ ] Activate hook
[ ] Deactivate hook
[x] Uninstall hook
[x] Settings
[ ] Version check

*/

defined('ABSPATH') or exit();

if ( !class_exists( 'AspexiFBlikebox' ) ) {

    class AspexiFBlikebox {

        public $cf = array();   // config array

        public function __construct() {

            /* Configuration */
            $this->settings();

            add_action( 'admin_menu',   array( &$this, 'admin_menu'));
            add_action( 'init',         array( &$this, 'init' ), 10 );
            add_action( 'wp_footer',    array( &$this, 'get_html' ), 21 );

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
                'aspexifblikebox_version' => '1.0.0',
                'url' => '',
                'locale' => 'en_GB'
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
        }

        public function admin_menu() {
            add_submenu_page( 'themes.php', __( 'Aspexi Facebook Like Box', 'aspexifblikebox' ), __( 'Facebook Like Box', 'aspexifblikebox' ), 'manage_options', 'aspexi-facebook-likebox.php', array( &$this, 'admin_page') );
        }

        public function admin_page() {

            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }
            // prepare options

            // request action
            if ( isset( $_REQUEST['afblb_form_submit'] ) && check_admin_referer( plugin_basename(__FILE__), 'afblb_nonce_name' ) ) {
                $aspexifblikebox_request_options = array();

                $aspexifblikebox_request_options['url']     = isset( $_REQUEST['afblb_url'] ) ? trim( $_REQUEST['afblb_url'] ) : '';
                $aspexifblikebox_request_options['locale']  = isset( $_REQUEST['afblb_locale'] ) ? $_REQUEST['afblb_locale'] : '';

                $this->cf = array_merge( (array)$this->cf, $aspexifblikebox_request_options );

                update_option( 'aspexifblikebox_options',  $this->cf, '', 'yes' );
                $message = __( 'Settings saved.', 'aspexifblikebox' );          
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

                <div id="poststuff" class="metabox-holder has-right-sidebar">
                    <div id="post-body">
                        <div id="post-body-content">
                            <form method="post" action="themes.php?page=aspexi-facebook-likebox.php">

                                <input type="hidden" name="afblb_form_submit" value="submit" />

                                <div class="postbox">
                                    <h3><span><?php _e('Settings', 'aspexifblikebox'); ?></span></h3>
                                    <div class="inside">
                                        <p><?php _e('Facebook Page URL', 'aspexifblikebox'); ?></p>
                                        http://www.facebook.com/
                                        <input type="text" name="afblb_url" value="<?php echo $this->cf['url']; ?>" />
                                        <p><?php _e('Localization', 'aspexifblikebox'); ?><br /><span style="font-size: 10px"><?php _e('Change might not be visible immediately because of Facebook cache', 'aspexifblikebox'); ?></span></p>
                                        <?php echo $locales_input; ?>
                                    </div>
                                </div>

                                <p><input class="button-primary" type="submit" name="send" value="<?php _e('Save settings', 'aspexifblikebox'); ?>" id="submitbutton" /></p>
                                <?php wp_nonce_field( plugin_basename( __FILE__ ), 'afblb_nonce_name' ); ?>
                            </form>
                        </div>
                    </div>

                    <div id="side-info-column" class="inner-sidebar">

                        <div class="postbox">
                            <h3><span>Made by</span></h3>   
                            <div class="inside">
                                <div style="width: 170px; margin: 0 auto;">
                                    <a href="http://aspexi.com/" target="_blank"><img src="<?php echo plugins_url().'/'.basename( __DIR__ ).'/images/aspexi300.png'; ?>" alt="" border="0" width="150" /></a>
                                    <p align="center"><em><a href="http://aspexi.com/contact/" target="_blank">Contact</a></em> <em><a href="http://aspexi.com/contact/" target="_blank">Support</a></em></p>
                                </div>
                            </div>
                        </div>

                        <div class="postbox">
                            <h3><span>Donate</span></h3>    
                            <div class="inside">    
                                <p>If this plugin is useful for you, consider donate it or buy me a beer :) Thank you!</p>
                                <div style="width:100%; display: block; text-align:center;"><form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                                    <input type="hidden" name="cmd" value="_s-xclick">
                                    <input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
                                    ">
                                    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                                    <img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
                                    </form>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>


            </div>
            <?php
        }

        public function get_html() {

            // Options
            $locale = apply_filters( 'aspexifblikebox_locale', $this->cf['locale'] );
            $url    = apply_filters( 'aspexifblikebox_url', $this->cf['url'] );

            if( !strlen( $url ) )
                return;

            $output = '';

            $output .= $this->get_script();

            $output .= '<style type="text/css"> .aspexifblikebox{background: url("'.plugins_url().'/'.basename( __DIR__ ).'/images/fb1-right.png'.'") no-repeat scroll left center transparent !important; float: right;height: 270px;padding: 0 5px 0 46px;width: 245px;z-index:  99999;position:fixed;right:-250px;top:20%;} .aspexifblikebox div{ padding: 0; margin-right:-8px; border:2px solid  #3b5998; background:#fafafa;} .aspexifblikebox span{bottom: 4px;font: 8px "lucida grande",tahoma,verdana,arial,sans-serif;position: absolute;right: 6px;text-align: right;z-index: 99999;} .aspexifblikebox span a{color: gray;text-decoration:none;} .aspexifblikebox span a:hover{text-decoration:underline;} } </style><div class="aspexifblikebox" style=""><div><iframe src="http://www.facebook.com/plugins/likebox.php?locale='.$locale.'&href=http%3A%2F%2Ffacebook.com%2F'.$url.'&amp;width=245&amp;colorscheme=light&amp;show_faces=true&amp;border_color=white&amp;connections=12&amp;stream=false&amp;header=false&amp;height=270" scrolling="no" frameborder="0" scrolling="no" style="border: white; overflow: hidden; height: 270px; width: 245px;background:#fafafa;"></iframe></div> </div>';

            $output = apply_filters( 'aspexifblikebox_output', $output );

            echo $output;
        }

        public function get_script() {

            if( !wp_script_is( 'jquery' ) ) {
                wp_deregister_script('jquery');
                wp_register_script('jquery', ('http://code.jquery.com/jquery-latest.min.js'), false, '');
                wp_enqueue_script('jquery');
            }

            $script = '<script type="text/javascript"> /*<![CDATA[*/ jQuery(document).ready(function() {jQuery(".aspexifblikebox").hover(function() {jQuery(this).stop().animate({right: "0"}, "medium");}, function() {jQuery(this).stop().animate({right: "-250"}, "medium");}, 500);}); /*]]>*/ </script>';

            $script = apply_filters( 'aspexifblikebox_script', $script );

            return $script;
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
    }

    /* Let's start the show */
    global $aspexifblikebox;

    $aspexifblikebox = new AspexiFBlikebox();
}