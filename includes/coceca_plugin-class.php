<?php
if(!class_exists('Coceca_Plugin')){
    class Coceca_Plugin
    {

        var $menu = '';
        /**
         * Holds arrays of plugin details.
         */
        public $plugins = array();
        /**
         * Holds configurable array of strings.
         * @var array
         */
        public $strings = array();
        /**
         * Default absolute path to folder containing pre-packaged plugin zip files.
         *
         * @since 2.0.0
         *
         * @var string Absolute path prefix to packaged zip file location. Default is empty string.
         */
        public $default_path = '';

        /**
         * Flag to show admin notices or not.
         *
         * @since 2.1.0
         *
         * @var boolean
         */
        public $has_notices = true;

        /**
         * Flag to determine if the user can dismiss the notice nag.
         *
         * @since 2.4.0
         *
         * @var boolean
         */
        public $dismissable = true;

        /**
         * Message to be output above nag notice if dismissable is false.
         *
         * @since 2.4.0
         *
         * @var string
         */
        public $dismiss_msg = '';

        /**
         * Flag to set automatic activation of plugins. Off by default.
         *
         * @since 2.2.0
         *
         * @var boolean
         */
        public $is_automatic = false;

        /**
         * Optional message to display before the plugins table.
         *
         * @since 2.2.0
         *
         * @var string Message filtered by wp_kses_post(). Default is empty string.
         */
        public $message = '';

        var $pages = '';

        public function __construct(){

            $this->menu = 'add_exentions';
            $this->page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : '';

            add_action('admin_menu',array($this,'add_admin_menu') , 9);

            add_action( 'coceca_admin_notice', array($this,'coceca_welcome_panel') , 2 );
            add_action( 'coceca_list_exentions', array($this,'coceca_exentions_list') , 2 );

            add_action('admin_enqueue_scripts', array($this,'coceca_admin_enqueue_scripts') );

            add_action('wp_ajax_coceca_gopro',array($this,'coceca_upgradeMembership'));
            add_action('wp_ajax_checkEmail',array($this,'checkEmailExists'));
            add_action('wp_ajax_sendEmail',array($this,'sendEmailRegister'));

            /* Using Include Template Files*/
            add_filter('template_include', array($this,'coceca_template_chooser'));

            $this->strings = array(
                'page_title'                     => __( 'Install Required Plugins', 'coceca-plugin' ),
                'menu_title'                     => __( 'Install Plugins', 'coceca-plugin' ),
                'installing'                     => __( 'Installing Plugin: %s', 'coceca-plugin' ),
                'oops'                           => __( 'Something went wrong.', 'coceca-plugin' ),
                'notice_can_install_required'    => _n_noop( 'This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.' ),
                'notice_can_install_recommended' => _n_noop( 'This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.' ),
                'notice_cannot_install'          => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.' ),
                'notice_can_activate_required'   => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.' ),
                'notice_can_activate_recommended'=> _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.' ),
                'notice_cannot_activate'         => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.' ),
                'notice_ask_to_update'           => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.' ),
                'notice_cannot_update'           => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.' ),
                'install_link'                   => _n_noop( 'Begin installing plugin', 'Begin installing plugins' ),
                'activate_link'                  => _n_noop( 'Begin activating plugin', 'Begin activating plugins' ),
                'return'                         => __( 'Return to Required Plugins Installer', 'coceca-plugin' ),
                'dashboard'                      => __( 'Return to the dashboard', 'coceca-plugin' ),
                'plugin_activated'               => __( 'Plugin activated successfully.', 'coceca-plugin' ),
                'activated_successfully'         => __( 'The following plugin was activated successfully:', 'coceca-plugin' ),
                'complete'                       => __( 'All plugins installed and activated successfully. %1$s', 'coceca-plugin' ),
                'dismiss'                        => __( 'Dismiss this notice', 'coceca-plugin' ),
            );

            add_action('admin_menu', array($this,'remove_submenus_please'), 10);
        }

        function remove_submenus_please() {
            if ( !is_admin())
                return;
            global $submenu;
            unset($submenu['coceca'][0]);
        }

        public static function activate(){
            /***Insert Update Activate and Download***/
            if ( ! function_exists( 'get_plugins' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $installed_plugins = get_plugins();
            if(array_key_exists('coceca/coceca.php',$installed_plugins)){
                InsertActivateDownload(1,'');
                $is_plugin_exists = checkDomainExists();
                coceca_active_deactive($is_plugin_exists['id'],$is_plugin_exists['user_id'],true);
                update_option('EXT_SITE_URL', 'https://coceca.com/members_area/');
            }
        }

        public static function deactivate(){
            /***Update Activate and Download***/
            if ( ! function_exists( 'get_plugins' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $installed_plugins = get_plugins();
            if(array_key_exists('coceca/coceca.php',$installed_plugins)){
                UpdateActivateDownload(1,'');
                $is_plugin_exists = checkDomainExists();
                coceca_active_deactive($is_plugin_exists['id'],$is_plugin_exists['user_id'],false);
                update_option('EXT_SITE_URL', '');
            }

        }


        public function add_admin_menu(){
            add_object_page( __( 'CoCeCa', 'coceca-plugin' ),
                __( 'CoCeCa', 'coceca-plugin' ),
                'manage_options', 'coceca',
                array($this,'coceca_list_extentions') );

            add_submenu_page( 'coceca',
                __( 'CoCeCa | Home', 'coceca-plugin' ),
                __( 'Home', 'coceca-plugin' ),
                'manage_options', 'coceca',
                array($this,'coceca_get_started') );

            add_submenu_page( 'coceca',
                __( 'CoCeCa | Home', 'coceca-plugin' ),
                __( 'Home', 'coceca-plugin' ),
                'manage_options','add_exentions',
                array($this,'coceca_list_extentions'));
        }

        function coceca_get_started(){
            if(!current_user_can('manage_options')){
                wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
            } ?>
            <div class="wrap">
                <h2><?php echo esc_html( __( 'CoCeCa', 'coceca-plugin' ) ); ?>
                    <?php do_action('coceca_admin_notice'); ?>
            </div>
        <?php }

        function coceca_list_extentions(){
            if(!current_user_can('manage_options')){
                wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
            }
            if(isset($_GET['msg']) && $_GET['msg'] == 'email'){
                echo '<div id="message" class="updated fade"><p>We have emailed you, click on the link to be able to access your CoCeCa member area. (Check the spam/junk folder if you can\'t locate the email).</p></div>';
                $_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
            }

            // Return early if processing a plugin installation action.
            if ($this->do_plugin_install() ) {
                return;
            }
            ?>
            <div class="wrap">
                <h2><?php echo esc_html( __( 'CoCeCa Plugin Details', 'coceca-plugin' ) ); ?></h2>
                    <?php
                        do_action('coceca_list_exentions');
                    ?>
            </div>
        <?php }


        function coceca_welcome_panel() {
            $this->coceca_template_chooser('get_started');
        }

        function coceca_exentions_list(){
            $this->coceca_template_chooser('list_extensions');
        }

        /*
        * Upgrade Membership
        * */
        function coceca_upgradeMembership(){
            global $wpdb;

            if(!check_admin_referer( 'gopro-CoCeCa_'.absint($_GET['plugin_id']), 'com_nonce' ) ) {
                wp_die('You have taken too long. Please go back and retry.', '', array( 'response' => 403 ) );
            }

            $is_paypal = false;
            $admin_redirect_uri = $_POST['admin_redirect_uri'];
            $response = '';
            $coupon_code = $paypal_payment = $coupon_data= $coupon_data_sr = '';
            $coupon_code = $_POST['coupon_code'];
            if(isset($coupon_code) && $coupon_code!=''){
                $coupon_sepcial_chr = preg_replace('/[^a-zA-Z0-9 \[\]\.\-]/s', '', $coupon_code);
                $coupon_data = checkValidCoupon($coupon_code);

                if($coupon_data!='' && count($coupon_data) > 0){
                    $response['flag'] = 'success';
                    $response['msg'] = 'Valid coupon code.';
                    $coupon_data_sr = toPublicId($coupon_data['id']);
                }
                else{
                    $response['flag'] = 'error';
                    $response['msg'] = 'Coupon code is not valid. Please try again.';
                }
            }
            else{
                if(isset($_POST['paypal_payment']) && $_POST['paypal_payment'] == 'paypal_payment'){
                    $is_paypal = true;
                    $paypal_payment = 'paypal_payment';
                    $response['flag'] = 'success';
                    $response['msg'] = 'Redirecting to paypal...';
                }
            }

            $encrpted_string = syonencryptor('encrypt',getHost().':'.absint($_GET['plugin_id']));
            $redirect_url = EXT_SITE_URL.'wpapi/purchase_plugins/?check_host='.getHost().'&plugin_id='.absint(toPublicId($_GET['plugin_id'])).'&pass_code='.$encrpted_string.'&coupon_data='.$coupon_data_sr.'&paypal_payment='.$paypal_payment.'&redirect_url='.$admin_redirect_uri;

            if(!empty($coupon_data)){
                $response['redirect_url'] = $redirect_url;
            }
            else if(isset($is_paypal) && $is_paypal==true){
                $response['redirect_url'] = $redirect_url;
            }

            echo json_encode($response); die;
            exit();
        }

        /*
         * Method : checkEmailExists();
         * Note : This Method Check Email Exists Or Not
         * */
        public function checkEmailExists(){
            $api_data = array(
                'is_json'=>'1',
                'token'=>'VXJ6dpIpZELStgGoxXqtYh34lIpF1sQn',
                'user_host'=>getHost(),
            );
            $result = CallAPI('GET',EXT_SITE_URL.'checkEmail/',$api_data);
            echo $result; die;
        }

        /*
         * Method sendEmailRegister();
         * Note : This Method will send email for being register user
         * */
         public function sendEmailRegister(){
             if(isset($_POST['email_address']) && !empty($_POST['email_address'])){
                 if (!filter_var(trim($_POST['email_address']), FILTER_VALIDATE_EMAIL))
                 {
                     echo json_encode(array('flag'=>'error','msg'=>'Please enter a valid email address')); die;
                 }
                 else{
                     $api_data = array(
                         'is_json'=>'1',
                         'token'=>'VXJ6dpIpZELStgGoxXqtYh34lIpF1sQn',
                         'user_host'=>getHost(),
                         'email_address'=>$_POST['email_address']
                     );
                     $result = CallAPI('GET',EXT_SITE_URL.'index/send_email/',$api_data);
                     echo $result; die;
                 }
             }
             else{
                 echo json_encode(array('flag'=>'error','msg'=>'Please enter email address.')); die;
             }
         }


        function coceca_admin_enqueue_scripts() {
           wp_enqueue_style('fancybox_CSS', EXT_SITE_URL.'coceca/js/fancybox/jquery.fancybox.css', array(), COCECA_PLUGIN_VERSION, 'all' );
           wp_enqueue_script('fancybox_Js', EXT_SITE_URL.'coceca/js/fancybox/jquery.fancybox.js', array('jquery'), COCECA_PLUGIN_VERSION, 'all' );
           wp_enqueue_style('coceca-plugin-admin', EXT_SITE_URL.'coceca/css/style.css', array(), COCECA_PLUGIN_VERSION, 'all' );
           wp_enqueue_script('script_js', EXT_SITE_URL.'coceca/js/script.js', array('jquery'), COCECA_PLUGIN_VERSION, 'all' );

            //if($this->page == 'add_exentions'){
                wp_enqueue_script('check_host_email_js', EXT_SITE_URL.'coceca/js/check_host_email.js', array('jquery'), COCECA_PLUGIN_VERSION, 'all' );
          //  }

            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $main_uri =  $protocol.$_SERVER['HTTP_HOST'].'/';

            $third_party = EXT_SITE_URL;

            wp_localize_script('script_js', "user_host", array(
                'domain_name' => $main_uri,
                'third_party' => $third_party,
                'admin_url'   => admin_url(),
                'admin_email' => get_option('admin_email'),
                'is_json' => '1',
                'token' => 'VXJ6dpIpZELStgGoxXqtYh34lIpF1sQn',
                'plugin_url'  => coceca_plugin_url(),
            ));
        }

        /*
         * CTA Template Chooser
         * */
        function coceca_template_chooser($template) {
            if($template == 'get_started'){
                return $this->coceca_get_template_hierarchy('get_started');
            }
            else if($template == 'list_extensions'){
                return $this->coceca_get_template_hierarchy('list_extensions');
            }
        }

        /*
         * CTA Get Template Hierarchy
         * */
        function coceca_get_template_hierarchy($template) {
            // Get the template slug
            $template_slug = rtrim( $template, '.php' );
            $template = $template_slug . '.php';
            // Check if a custom template exists in the theme folder, if not, load the plugin template file
            if($theme_file = locate_template( array( 'plugin_template/' . $template ))) {
                $file = $theme_file;
            }
            else{
                $file = COCECA_PLUGIN_DIR . '/includes/templates/' . $template;
            }
            require_once($file);
            return apply_filters( 'cta_repl_template_' . $template, $file );
        }

        /**
         * Installs a plugin or activates a plugin depending on the hover
         * link clicked by the user.
         *
         * Checks the $_GET variable to see which actions have been
         * passed and responds with the appropriate method.
         *
         * Uses WP_Filesystem to process and handle the plugin installation
         * method.
         *
         * @since 1.0.0
         *
         * @uses WP_Filesystem
         * @uses WP_Error
         * @uses WP_Upgrader
         * @uses Plugin_Upgrader
         * @uses Plugin_Installer_Skin
         *
         * @return boolean True on success, false on failure
         */
        protected function do_plugin_install() {

            // All plugin information will be stored in an array for processing.
            $plugin = array(); global $current_user;

            // Checks for actions from hover links to process the installation.
            if ( isset( $_GET['plugin'] ) && ( isset( $_GET['mtb-install'] ) && $_GET['mtb-install'] = 'install-plugin' ) ) {

                check_admin_referer( 'mtb-install' );

                $plugin['name']   = $_GET['plugin_name']; // Plugin name.
                $plugin['slug']   = $_GET['plugin']; // Plugin slug.
                $plugin['source'] = $_GET['plugin_source']; // Plugin source.

                /*** Start Update Plugin Table ***/
                if(isset($_GET['plugin_id']) && !empty($_GET['plugin_id'])){
                    $domain_data = checkDomainExists();
                    $isAlreadyActivate = isActivatePlugin($domain_data['id'],$_GET['plugin_id'],$domain_data['user_id']);
                    if(empty($isAlreadyActivate)){
                        $insertData['m_p_id'] = $domain_data['id'];
                        $insertData['plugin_id'] = $_GET['plugin_id'];
                        $insertData['user_id'] = $domain_data['user_id'];
                        insertPluginData($insertData);
                    }
                }
                /*** End Update Plugin Table ***/

                // Pass all necessary information via URL if WP_Filesystem is needed.
                $url = wp_nonce_url(
                    add_query_arg(
                        array(
                            'page'          => $this->menu,
                            'plugin'        => $plugin['slug'],
                            'plugin_name'   => $plugin['name'],
                            'plugin_source' => $plugin['source'],
                            'mtb-install' => 'install-plugin',
                        ),
                        network_admin_url( 'admin.php' )
                    ),
                    'mtb-install'
                );
                $method = ''; // Leave blank so WP_Filesystem can populate it as necessary.
                $fields = array( 'mtb-install' ); // Extra fields to pass to WP_Filesystem.

                if ( false === ( $creds = request_filesystem_credentials( $url, $method, false, false, $fields ) ) ) {
                    return true;
                }

                if ( ! WP_Filesystem( $creds ) ) {
                    request_filesystem_credentials( $url, $method, true, false, $fields ); // Setup WP_Filesystem.
                    return true;
                }

                require_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // Need for plugins_api.
                require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php'; // Need for upgrade classes.

                // Set plugin source to WordPress API link if available.
                if ( isset( $plugin['source'] ) && 'repo' == $plugin['source'] ) {
                    $api = plugins_api( 'plugin_information', array( 'slug' => $plugin['slug'], 'fields' => array( 'sections' => false ) ) );

                    if ( is_wp_error( $api ) ) {
                        wp_die( $this->strings['oops'] . var_dump( $api ) );
                    }

                    if ( isset( $api->download_link ) ) {
                        $plugin['source'] = $api->download_link;
                    }
                }

                // Set type, based on whether the source starts with http:// or https://.
                $type = preg_match( '|^http(s)?://|', $plugin['source'] ) ? 'web' : 'upload';

                // Prep variables for Plugin_Installer_Skin class.
                $title = sprintf( $this->strings['installing'], $plugin['name'] );
                $url   = add_query_arg( array( 'action' => 'install-plugin', 'plugin' => $plugin['slug'] ), 'update.php' );
                if ( isset( $_GET['from'] ) ) {
                    $url .= add_query_arg( 'from', urlencode( stripslashes( $_GET['from'] ) ), $url );
                }

                $nonce = 'install-plugin_' . $plugin['slug'];

                // Prefix a default path to pre-packaged plugins.
                $source = ( 'upload' == $type ) ? $this->default_path . $plugin['source'] : $plugin['source'];

                // Create a new instance of Plugin_Upgrader.
                $upgrader = new Plugin_Upgrader( $skin = new Plugin_Installer_Skin( compact( 'type', 'title', 'url', 'nonce', 'plugin', 'api' ) ) );

                // Perform the action and install the plugin from the $source urldecode().
                $upgrader->install( $source );

                // Flush plugins cache so we can make sure that the installed plugins list is always up to date.
                wp_cache_flush();

                // Only activate plugins if the config option is set to true.
                if ( $this->is_automatic ) {
                    $plugin_activate = $upgrader->plugin_info(); // Grab the plugin info from the Plugin_Upgrader method.
                    $activate        = activate_plugin( $plugin_activate ); // Activate the plugin.
                    $this->populate_file_path(); // Re-populate the file path now that the plugin has been installed and activated.

                    if ( is_wp_error( $activate ) ) {
                        echo '<div id="message" class="error"><p>' . $activate->get_error_message() . '</p></div>';
                        echo '<p><a href="' . add_query_arg( 'page', $this->menu, network_admin_url( 'admin.php' ) ) . '" title="' . esc_attr( $this->strings['return'] ) . '" target="_parent">' . $this->strings['return'] . '</a></p>';
                        return true; // End it here if there is an error with automatic activation
                    }
                    else {
                        echo '<p>' . $this->strings['plugin_activated'] . '</p>';
                    }
                }

                // Display message based on if all plugins are now active or not.
                $complete = array();
                foreach ( $this->plugins as $plugin ) {
                    if ( ! is_plugin_active( $plugin['file_path'] ) ) {
                        echo '<p><a href="' . add_query_arg( 'page', $this->menu, network_admin_url( 'admin.php' ) ) . '" title="' . esc_attr( $this->strings['return'] ) . '" target="_parent">' . $this->strings['return'] . '</a></p>';
                        $complete[] = $plugin;
                        break;
                    }
                    // Nothing to store.
                    else {
                        $complete[] = '';
                    }
                }

                // Filter out any empty entries.
                $complete = array_filter( $complete );

                // All plugins are active, so we display the complete string and hide the plugin menu.
                if ( empty( $complete ) ) {
                   // echo '<p>' .  sprintf( $this->strings['complete'], '<a href="' . network_admin_url() . '" title="' . __( 'Return to the Dashboard', 'tgmpa' ) . '">' . __( 'Return to the Dashboard', 'tgmpa' ) . '</a>' ) . '</p>';
                   // echo '<style type="text/css">#adminmenu .wp-submenu li.current { display: none !important; }</style>';
                }

                return true;
            }
            // Checks for actions from hover links to process the activation.
            elseif ( isset( $_GET['plugin'] ) && ( isset( $_GET['mtb-activate'] ) && 'activate-plugin' == $_GET['mtb-activate'] ) ) {
                //check_admin_referer( 'mtb-activate', 'mtb-activate-nonce' );
                check_admin_referer( 'mtb-activate' );

                // Populate $plugin array with necessary information.
                $plugin['name']   = $_GET['plugin_name'];
                $plugin['slug']   = $_GET['plugin'];
                $plugin['source'] = isset($_GET['plugin_source']) ? $_GET['plugin_source'] : '';

                $plugin_data = get_plugins( '/' . $plugin['slug'] ); // Retrieve all plugins.
                $plugin_file = array_keys( $plugin_data ); // Retrieve all plugin files from installed plugins.

                $plugin_to_activate = $plugin['slug'] . '/' . $plugin_file[0]; // Match plugin slug with appropriate plugin file.
                $activate = activate_plugin( $plugin_to_activate ); // Activate the plugin.

                if ( is_wp_error( $activate ) ) {
                    echo '<div id="message" class="error"><p>' . $activate->get_error_message() . '</p></div>';
                    echo '<p><a href="' . add_query_arg( 'page', $this->menu, network_admin_url( 'admin.php' ) ) . '" title="' . esc_attr( $this->strings['return'] ) . '" target="_parent">' . $this->strings['return'] . '</a></p>';
                    return true; // End it here if there is an error with activation.
                }
                else {
                    // Make sure message doesn't display again if bulk activation is performed immediately after a single activation.
                    if ( ! isset( $_POST['action'] ) ) {
                        $msg = $this->strings['activated_successfully'] . ' <strong>' . $plugin['name'] . '</strong>';
                        echo '<div id="message" class="updated"><p>' . $msg . '</p></div>';
                    }
                }
            }

            return false;

        }

        /**
         * Set file_path key for each installed plugin.
         *
         * @since 2.1.0
         */
        public function populate_file_path() {
            // Add file_path key for all plugins.
            foreach ( $this->plugins as $plugin => $values ) {
                $this->plugins[$plugin]['file_path'] = $this->_get_plugin_basename_from_slug( $values['slug'] );
            }

        }
        /**
         * Helper function to extract the file path of the plugin file from the
         * plugin slug, if the plugin is installed.
         *
         * @since 2.0.0
         *
         * @param string $slug Plugin slug (typically folder name) as provided by the developer.
         * @return string      Either file path for plugin if installed, or just the plugin slug.
         */
        protected function _get_plugin_basename_from_slug( $slug ) {
            $keys = array_keys( get_plugins() );
            foreach ( $keys as $key ) {
                if ( preg_match( '|^' . $slug .'/|', $key ) ) {
                    return $key;
                }
            }
            return $slug;
        }

    }

    $coceca_plugin = new Coceca_Plugin();
}