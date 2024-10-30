<?php   
    /**
     * Plugin Name: Blast.my SMS Broadcast
     * Plugin URI: https://blast.my/
     * Description: Send or broadcast SMS from your Wordpress admin panel.
     * Author: Ifcon Technology
     * Author URI: https://ifcontech.com/
     * Version: 1.2.0
     *
     * Copyright: (c) 2022, Ifcon Technology Sdn. Bhd. (nurul@ifcontech.com)
     *
     * License: GNU General Public License v3.0
     * License URI: http://www.gnu.org/licenses/gpl-3.0.html
     *
     * @package   Blast-SMS-Broadcast
     * @author    Ifcon Technology
     * @category  Integration
     * @copyright Copyright (c) 2022, Ifcon Technology Sdn. Bhd.
     * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
     */

    namespace BlastSmsBroadcast { 

        if (!defined('ABSPATH')) exit; // Exit if accessed directly

        global $pagenow;

        class BlastSmsBroadcast_Main {  
            
            public $functions;

            private static $instance;

            public function __construct()
            {
                $this->functions = new BlastSmsBroadcast_Helper();
            }

            static function blastmy_main_get_instance()  
            {  
                if(!isset(self::$instance)) {  
                    self::$instance = new self();
                }  

                return self::$instance;  
            }  

            public function blastmy_main_init()  
            {  
                if ( is_admin() && isset($_GET['page']) && $_GET['page'] == 'blast-sms-broadcast' ) {
                    add_action( 'admin_enqueue_scripts', function () {
                        wp_enqueue_style( 'blast_style_css', plugins_url(basename(dirname(__FILE__))).'/css/style.css');
                        wp_enqueue_style( 'blast_bootstrap_css', plugins_url(basename(dirname(__FILE__))).'/vendor/bootstrap/css/bootstrap.min.css');
                        wp_enqueue_style( 'blast_select2_css', plugins_url(basename(dirname(__FILE__))).'/vendor/select2/css/select2.min.css');
                        wp_enqueue_style( 'blast_jqueryui_css', plugins_url(basename(dirname(__FILE__))).'/vendor/jquery-ui/jquery-ui.min.css');
                    
                        wp_enqueue_script( 'blast_script_js', plugins_url(basename(dirname(__FILE__))).'/js/script.js', ['jquery'] );
                        wp_enqueue_script( 'blast_bootstrap_js', plugins_url(basename(dirname(__FILE__))).'/vendor/bootstrap/js/bootstrap.bundle.min.js', ['jquery'] );
                        wp_enqueue_script( 'blast_select2_js', plugins_url(basename(dirname(__FILE__))).'/vendor/select2/js/select2.full.min.js', ['jquery'] );
                    });
                }

                add_action( 'admin_menu', array($this, 'blastmy_main_plugin_menu') );
        
                add_action('admin_init', function () {
                    register_setting('blast_options', 'blast_options');
                    register_setting('blast_messages', 'blast_messages');
                });
            }

            public function blastmy_main_plugin_menu()  
            {  
                add_menu_page(
                    'Blast SMS',
                    'Blast SMS',
                    'manage_options',
                    'blast-sms-broadcast',
                    array($this, 'blastmy_main_settings'),
                    'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><style type="text/css">.st0{fill:#9CA1A7;}</style><path class="st0" d="M256 32C114.6 32 .0137 125.1 .0137 240c0 49.59 21.39 95 56.99 130.7c-12.5 50.39-54.31 95.3-54.81 95.8C0 468.8-.5938 472.2 .6875 475.2C1.1 478.2 4.813 480 8 480c66.31 0 116-31.8 140.6-51.41C181.3 440.9 217.6 448 256 448C397.4 448 512 354.9 512 240S397.4 32 256 32zM167.3 271.9C163.9 291.1 146.3 304 121.1 304c-4.031 0-8.25-.3125-12.59-1C101.1 301.8 92.81 298.8 85.5 296.1c-8.312-3-14.06-12.66-11.09-20.97S85 261.1 93.38 264.9c6.979 2.498 14.53 5.449 20.88 6.438C125.7 273.1 135 271 135.8 266.4c1.053-5.912-10.84-8.396-24.56-12.34c-12.12-3.531-44.28-12.97-38.63-46c4.062-23.38 27.31-35.91 58-31.09c5.906 .9062 12.44 2.844 18.59 4.969c8.344 2.875 12.78 12 9.906 20.34C156.3 210.7 147.2 215.1 138.8 212.2c-4.344-1.5-8.938-2.938-13.09-3.594c-11.22-1.656-20.72 .4062-21.5 4.906C103.2 219.2 113.6 221.5 124.4 224.6C141.4 229.5 173.1 238.5 167.3 271.9zM320 288c0 8.844-7.156 16-16 16S288 296.8 288 288V240l-19.19 25.59c-6.062 8.062-19.55 8.062-25.62 0L224 240V288c0 8.844-7.156 16-16 16S192 296.8 192 288V192c0-6.875 4.406-12.1 10.94-15.18c6.5-2.094 13.71 .0586 17.87 5.59L256 229.3l35.19-46.93c4.156-5.531 11.4-7.652 17.87-5.59C315.6 179 320 185.1 320 192V288zM439.3 271.9C435.9 291.1 418.3 304 393.1 304c-4.031 0-8.25-.3125-12.59-1c-8.25-1.25-16.56-4.25-23.88-6.906c-8.312-3-14.06-12.66-11.09-20.97s10.59-13.16 18.97-10.19c6.979 2.498 14.53 5.449 20.88 6.438c11.44 1.719 20.78-.375 21.56-4.938c1.053-5.912-10.84-8.396-24.56-12.34c-12.12-3.531-44.28-12.97-38.63-46c4.031-23.38 27.25-35.91 58-31.09c5.906 .9062 12.44 2.844 18.59 4.969c8.344 2.875 12.78 12 9.906 20.34c-2.875 8.344-11.94 12.81-20.34 9.906c-4.344-1.5-8.938-2.938-13.09-3.594c-11.19-1.656-20.72 .4062-21.5 4.906C375.2 219.2 385.6 221.5 396.4 224.6C413.4 229.5 445.1 238.5 439.3 271.9z"/></svg>'),
                    60
                );
            }  
                
            public function blastmy_main_settings(){
            ?>
                <div class="wrap">
                    <h1 class="mb-3"><?php echo get_admin_page_title(); ?></h1>
                    <h1 class="d-block">API Token</h1>
                    <p>Grab your API token from <a href="https://blast.my/" target="_blank">Blast.my</a> and enter it below to enable broadcast SMS from your dashboard.</p>
                    <form class="row mb-5" action="options.php" method="post">
                        <?php settings_fields('blast_options'); ?>
                        <div class="col-12">
                            <div class="py-3">
                                <div class="form-group row mb-4">
                                    <div class="col-sm-3 col-form-label">
                                        Token
                                    </div>
                                    <div class="col-sm-9">
                                        <input class="form-control " id="token" name="blast_options[token]" type="password" value="<?php echo esc_attr( get_option('blast_options')['token'] ?? ''); ?>" />
                                    </div>
                                </div>
                                <div class="form-group row mb-4">
                                    <div class="col-sm-3 col-form-label">
                                        Balance
                                    </div>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <input min="0" oninput="validity.valid||(value='');" class="form-control" id="balance" name="blast_options[balance]" type="number" value="<?php echo esc_attr($this->functions->blastmy_helper_get_balance() ?? 0); ?>" readonly />
                                            <div class="input-group-append">
                                                <span class="input-group-text" style="border-top-left-radius: 0; border-bottom-left-radius: 0;border: 1px solid #8c8f94;">credits</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e('Save'); ?>" />
                            </div>
                        </div>
                    </form>

                    <?php
                        $blast_options = get_option( 'blast_options' );
                        if(isset($blast_options['token']) && $blast_options['token'] != '' && BlastSmsBroadcast_Helper::blastmy_helper_get_balance() ): ?>
                            <h1>Broadcast SMS</h1>
                            <p>Send SMS broadcast to one or more phone numbers. Available for Malaysia (+60) phone numbers only. 1 SMS = 95 credits.</p>
                            <form class="row mb-5" action="#" method="post">
                                <div class="col-12">
                                    <div class="py-3">
                                        <div class="form-group row mb-4">
                                            <div class="col-sm-3 col-form-label">
                                                Group
                                            </div>
                                            <div class="col-sm-9">
                                                <select class="form-control select-groups">
                                                    <option></option>
                                                </select>
                                                <div id="blast-groups" class="mt-2">
                                                    <i class="fa fa-exclamation-circle text-danger"></i> <small>You don't have any contact group yet. Create one at <a href="https://blast.my/" target="_blank">Blast.my</a> dashboard</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-4">
                                            <div class="col-sm-3 col-form-label">
                                                Recipient Phone(s)
                                                <small class="text-muted d-block">Including country code (+60). E.g. +60123456789.</small>
                                            </div>
                                            <div class="col-sm-9">
                                                <select name="blast_messages[phone][]" class="form-control select-phones" multiple="multiple"></select>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-4">
                                            <div class="col-sm-3 col-form-label">
                                                Message
                                                <small class="text-muted d-block">
                                                    Each message limits to 156 characters only. Some words will be filtered due to carrier restriction.
                                                </small>
                                            </div>
                                            <div class="col-sm-9">
                                                <textarea id="text-source" rows="3" name="blast_messages[message]" style="margin:5px 0 5px 0;" class="form-control" maxlength="156"></textarea>
                                                <div id="blast-connect" class="mt-2" style="display: none;">
                                                    <i class="fa fa-check-circle text-success"></i> <small>Connected to Blast.my word filter</small><br>
                                                </div>
                                                <div id="blast-disconnect" class="mt-2">
                                                    <i class="fa fa-times-circle text-danger"></i> <small>Unable to connect to Blast.my word filter</small>
                                                </div>
                                                <small id="text-output" class="mt-2 text-muted" style="width: 25rem; display: block; line-height: 1; display: none;">
                                                    Output: <i>RM0 <span id="text-messages"></span></i>
                                                </small>
                                            </div>
                                        </div>
                                        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e('Broadcast Now'); ?>" />
                                    </div>
                                </div>
                            </form>
                        <?php endif;
                    ?>
                </div>
            <?php  
            }  
        }

        if (is_admin()) {
            include plugin_dir_path( __FILE__ ) . 'functions.php';

            $blast_api = BlastSmsBroadcast_Main::blastmy_main_get_instance();
            $blast_api->blastmy_main_init();
        }
    }
?>