<?php

Class Viddyoze{

    private static $api_host = '';
    private static $api_key = '';
    private static $templates_uri = '';
    private static $template_uri = '';
    private static $customization_uri = '';
    private static $font_uri = '';
    private static $categories_uri = '';
    private static $filters_uri = '';
    private static $render_preview_uri = '';
    private static $render_uri = '';
    private static $renders_uri = '';
    private static $subscriptions_uri = '';
    private static $template_can_be_rendered_uri = '';

    private static $pages = array(
        'welcome'   => 'viddyoze-welcome-screen',
        'options'   => 'viddyoze-options',
        'templates' => 'viddyoze-templates',
        'template'  => 'viddyoze-template-single',
        'videos'    => 'viddyoze-videos',
        'support'    => 'viddyoze-support'
    );

    private static $upgrade_urls = array(
        'template_club_marketing' => 'https://viddyoze.com/club/',
        'template-club_plus_marketing' => 'https://viddyoze.com/upgrade/plus/',
        'whitelabel_marketing' => 'https://viddyoze.com/white-label/',
    );
	
    public static function viddyozeInit()
    {
        if ( is_admin() ) {
            $page = sanitize_text_field($_GET['page']);
            $template = self::viddyozeSanitizeInt(sanitize_text_field($_GET['t']), false);

            if ($page == self::$pages['template'] && !$template) {
                wp_redirect( admin_url( '/admin.php?page=' . self::$pages['options'] ) );
                exit;
            }

            add_action('admin_enqueue_scripts',array('Viddyoze','viddyozeGlobalStyles'));

            if ( strpos($page, 'viddyoze') !== false ) {
                add_action('admin_enqueue_scripts', array('Viddyoze', 'viddyozeCustomAdmin'));
            }
            
            if(empty(get_option('viddyoze_api_key'))) {
                /*
                * set admin notice if options Is Not Here
                */
                add_action('admin_notices',array('Viddyoze','viddyozeNotice'));
            }
            add_action('admin_post_nopriv_viddyoze_render',array('Viddyoze','viddyozeHandleRender'));
            add_action('admin_post_viddyoze_render',array('Viddyoze','viddyozeHandleRender'));
            add_action('admin_post_viddyoze_render_delete',array('Viddyoze','viddyozeHandleRenderDelete'));
            add_action("wp_ajax_get_percentage", array('Viddyoze','viddyozeGetPercentage'));
            add_action("wp_ajax_nopriv_get_percentage",  array('Viddyoze','viddyozeCheckLogin'));
            add_action("wp_ajax_get_preview", array('Viddyoze','viddyozeGetPreview'));
            add_action("wp_ajax_nopriv_get_preview",  array('Viddyoze','viddyozeCheckLogin'));
            add_action("wp_ajax_get_render", array('Viddyoze','viddyozeHandleRender'));
            add_action("wp_ajax_nopriv_get_preview",  array('Viddyoze','viddyozeCheckLogin'));
            self::$api_host = get_option('viddyoze_api_host', 'https://api.viddyoze.com');
            self::$api_key = get_option('viddyoze_api_key') ;
            self::$templates_uri = '/templates';
            self::$template_uri = '/template';
            self::$font_uri = '/font';
            self::$categories_uri = '/categories';
            self::$filters_uri = '/filters';
            self::$render_preview_uri = '/render/preview';
            self::$render_uri = '/render';
            self::$renders_uri = '/renders';
            self::$customization_uri = '/template_customisation';
            self::$subscriptions_uri = '/user/subscriptions';
            self::$template_can_be_rendered_uri = '/template/{TEMPLATE_ID}/can-be-rendered';

            /**
             * Init Welcome Page
             */
            add_action( 'admin_menu', array('Viddyoze', 'viddyozeWelcomePage') );
            add_action( 'admin_init', array('Viddyoze', 'viddyozeWelcomePageRedirect') );
            add_action( 'admin_head', array('Viddyoze', 'viddyozeWelcomePageRemoveMenu') );

            /**
             * Notices
             */
            if ($page && in_array($page, self::$pages)) {
                remove_all_actions( 'admin_notices' );
                remove_all_actions('all_admin_notices');
                if ($page === self::$pages['options']) {
                    add_action('admin_notices', array('Viddyoze', 'viddyozeShowYouTubeNotice'));
                }
                else if ($page === self::$pages['templates']) {
                    add_action('admin_notices', array('Viddyoze', 'viddyozeShowDiscoverNotice'));
                }
                else if (in_array($page, array(self::$pages['videos'], self::$pages['support']))) {
                    add_action('admin_notices', array('Viddyoze', 'viddyozeShowTutorialsNotice'));
                }
                else if ($page !== self::$pages['welcome']) {
                    add_action('admin_notices', array('Viddyoze', 'viddyozeShowUpgradeNotice'));
                }
            }
        }        
    }

    public static function viddyozeShowYouTubeNotice() {
        ?>
        <div class="notice notice-info is-dismissible">
            <p style="font-weight:700">Subscribe To Our YouTube Channel!</p>
            <p>The #1 Resource For Everyone Who Wants To Create High Impact Video Content</p>
            <p><a target="_blank" href="https://www.youtube.com/channel/UCUNTFlTtU2pC6bAXoGACO1g" class="button-primary">SUBSCRIBE HERE</a></p>
        </div>
        <?php
    }

    public static function viddyozeShowDiscoverNotice() {
        ?>
        <div class="notice notice-info is-dismissible">
            <p style="font-weight:700">Have You Watched This Video?</p>
            <p>Discover How To Create Your First Animation In Just 3 Clicks Directly Inside Your WordPress Account.</p>
            <p><a target="_blank" href="https://viddyoze.com/wordpress/tutorials/steps/" class="button-primary">WATCH VIDEO HERE</a></p>
        </div>
        <?php
    }

    public static function viddyozeShowTutorialsNotice() {
        ?>
        <div class="notice notice-info is-dismissible">
            <p style="font-weight:700">Check Out Our Tutorials! </p>
            <p>Get started with our training videos so that you can learn how to create better animations and drive more clicks and customers to your business.</p>
            <p><a target="_blank" href="https://app.viddyoze.com/tutorials" class="button-primary">LEARN MORE HERE</a></p>
        </div>
        <?php
    }

    public static function viddyozeShowUpgradeNotice() {
        if (self::isFreeMember() || self::isLicenceOnly()) {
            ?>
            <div class="notice notice-info is-dismissible">
                <p style="font-weight:700">Want more choice?</p>
                <p>Unlock over <strong>1500+</strong> templates instantly, plus at least 15 new templates added every month</p>
                <p><a target="_blank" href="<?php echo self::$upgrade_urls['template_club_marketing']; ?>" class="button-primary">Upgrade To Template Club</a></p>
            </div>
            <?php
        } else if (self::isTemplateClub()) {
            ?>
            <div class="notice notice-info is-dismissible">
                <p style="font-weight:700">Want more?</p>
                <p>Access additional audio tracks, animated GIF renders, and multiple concurrent renders</p>
                <p><a target="_blank" href="<?php echo self::$upgrade_urls['template-club_plus_marketing']; ?>" class="button-primary">Upgrade To Template Club Plus</a></p>
            </div>
            <?php
        } else if (self::isTemplateClubPlus()) {
            ?>
            <div class="notice notice-info is-dismissible">
                <p style="font-weight:700">Ready to go all the way with Viddyoze?</p>
                <p>Run Viddyoze on your own domain, and use your own branding.</p>
                <p><a target="_blank" href="<?php echo self::$upgrade_urls['whitelabel_marketing']; ?>" class="button-primary">Upgrade To White Label</a></p>
            </div>
            <?php
        }
    }

    /**
     * Configure welcome page
     */
    public static function viddyozeWelcomePage() {
        add_dashboard_page(
            'Welcome To Viddyoze',
            'Welcome To Viddyoze',
            'read',
            self::$pages['welcome'],
            array('Viddyoze', 'viddyozeWelcomePageContent')
        );
    }

    /**
     * Redirect to welcome page
     */
    public static function viddyozeWelcomePageRedirect() {
        // Bail if no activation redirect
        if ( ! get_transient( '_viddyoze_welcome_screen_activation_redirect' ) ) {
            return;
        }

        // Delete the redirect transient
        delete_transient( '_viddyoze_welcome_screen_activation_redirect' );

        // Bail if activating from network, or bulk
        if ( is_network_admin() || (isset($_GET['activate-multi']) && $_GET['activate-multi'] !== false) ) {
            return;
        }

        // Redirect to bbPress about page
        wp_safe_redirect( add_query_arg( array( 'page' => self::$pages['welcome'] ), admin_url( 'index.php' ) ) );
    }

    public static function viddyozeWelcomePageRemoveMenu() {
        remove_submenu_page( 'index.php', self::$pages['welcome'] );
    }

    /**
     *
     */
    public static function viddyozeGlobalStyles() {
        wp_enqueue_style('viddyoze-global-css', VIDDYOZE_ADMIN_URL .'css/viddyoze_global.css', array(), '1.0.0', 'all');
    }

    /**
     *
     */
    public static function viddyozeCustomAdmin() {
        //enqueue styles and scripts
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker');
        wp_enqueue_script( 'jquery-ui-dialog' );
        wp_enqueue_style( 'wp-jquery-ui-dialog' );
        wp_enqueue_style('viddyoze-css', VIDDYOZE_ADMIN_URL .'css/viddyoze.css', array(), '1.0.0', 'all');
        wp_enqueue_style('slick-css', VIDDYOZE_ADMIN_URL .'css/slick.css', array(), '1.0.0', 'all');

        wp_enqueue_script( 'clipboard-lib', VIDDYOZE_ADMIN_URL.'js/clipboard.min.js', array( 'jquery' ), '1.0.0', true );
        wp_enqueue_script( 'slick.min', VIDDYOZE_ADMIN_URL.'js/slick.min.js', array( 'jquery' ), '1.0.0', true );
        wp_register_script( "viddyoze-js", VIDDYOZE_ADMIN_URL.'js/viddyoze.js', array('jquery'),'1.0.2',true);
        wp_localize_script( 'viddyoze-js', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));  
        wp_enqueue_script('viddyoze-js');
    }

    /**
     *
     */
    public static function viddyozeGetPercentage(){
        global $wp;
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $result = array();

            $id = self::viddyozeSanitizeInt(sanitize_text_field($_REQUEST['id']), false);
            if($id === false) {
                $result['type'] = "error";
                $result['percentage'] = '1';
            }
            else {
                $video = self::getVideoStatus($id);
                if ($video->status == "finished") {
                    $download_content = '';
                    foreach ($video->videoUrls as $videoUrl) {
                        if (!in_array($videoUrl->type, array('MOV', 'MP4')) && (self::isLicenceOnly() || self::isFreeMember())) {
                            continue;
                        }
                        $download_content .= '<div class="py-2 px-2 inline-block w-full whitespace-no-wrap">
                                <a href="' . $videoUrl->url . '" class="block">' . $videoUrl->type . '</a>
                            </div>';
                    }
                    $content = '<p class="text-gray-400 py-3">Rendered ' . self::time_elapsed_string($video->processing_finished_at) . '</p>
                    <div class="dropdown inline-block relative float-left my-10">
                        <button class="text-xs font-bold uppercase px-5 py-3 shadow-lg rounded-full mx-auto leading-normal text-white bg-blue-600 inline-flex items-center">
                        <span class="mr-1">Download</span>
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/> </svg>
                        </button>
                        <div class="dropdown-menu absolute hidden text-black-700 pt-1 shadow-lg w-full bg-white rounded">
                            '.$download_content.'
                        </div>
                    </div>
                    <a href="'. admin_url('admin.php' . add_query_arg(array(
                        'page'=> self::$pages['template'],
                        't' => $video->templateId,
                        'c' => $video->customizationId
                        ), $wp->request)) .'" class=" float-left inline-block text-xs font-bold uppercase px-5 py-3 shadow-lg rounded-full mx-auto my-10 block leading-normal text-black ml-2">Edit</a>
                    
                    <a href="#" data-embed="'.$video->videoUrls[0]->url . '" class=" float-left inline-block text-xs font-bold uppercase px-5 py-3 shadow-lg rounded-full bg-green-600 my-10 block leading-normal text-white ml-2 open-my-dialog2 copy-embed">Embed</a>
                    <a href="#" data-id="' . $video->id . '" class=" float-right inline-block text-xs font-bold uppercase px-5 py-3 shadow-lg rounded-full mx-auto my-10 block leading-normal text-white bg-red-600 open-my-dialog delete-render">Delete</a>';
                    $result['content'] = $content;
                    $result['type'] = "finished";
                }
                elseif ($video->status == "rendering") {
                    $content = 'Rendering ' . (int)$video->percentageComplete . '%';
                    $result['content'] = $content;
                    $result['type'] = "rendering";
                }
                elseif ($video->status == "queuing") {
                    $content = 'Queuing...';
                    $result['content'] = $content;
                    $result['type'] = "queuing";
                }
                $result['percentage'] = $video;
            }
            $result = json_encode($result);
            echo $result;
        }
        exit;
    }

    /**
     *
     */
    public static function viddyozeGetPreview() {
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            if(!isset($_POST['data'])) {
                $response = array(
                        'type' => 'error'
                );
            } else {
                $parsed_data = array();
                // $_POST['data'] includes encoded arrays and base64 data so can't be sanitized here
                parse_str($_POST['data'], $parsed_data);
                $response = self::viddyozeHandlePreview($parsed_data);
            }
            echo json_encode($response);
        }
        exit;
    }

    /**
     * @param $parsed_data
     * @return bool|mixed
     */
    public static function viddyozeHandlePreview($parsed_data){
        if (
            isset($parsed_data)
            && !empty($parsed_data)
            && isset($parsed_data['template_id'])
            && self::viddyozeValidateNumber($parsed_data['template_id'])
        ) {
            $params = array(
                'template_id' => $parsed_data['template_id']
            );

            if (isset($parsed_data['images_data'])) {
                $params['images'] = self::viddyozeSanitizeArray(
                        $parsed_data['images_data'],
                        array('/^data:image\/(?:gif|png|jpeg|bmp|webp)(?:;charset=utf-8)?;base64,(?:[^\"=]*)={0,2}/'),
                        true
                );
            }

            if (isset($parsed_data['colors'])) {
                $params['colors'] = self::viddyozeSanitizeArray(
                        $parsed_data['colors'],
                        array('/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'),
                        true
                );
            }

            if (isset($parsed_data['texts'])) {
                $params['texts'] = $parsed_data['texts'];
            }

            if (isset($parsed_data['template_audio'])) {
                $params['template_audio'] = self::viddyozeSanitizeInt(sanitize_text_field($parsed_data['template_audio']));
            }

            if (isset($parsed_data['toogle_fonts']) && $parsed_data['toogle_fonts'] === 'true' && isset($parsed_data['viddyoze_font_family'])) {
                $params['fonts'] = self::viddyozeSanitizeArray(
                    $parsed_data['viddyoze_font_family'],
                    array('/[0-9]+/'),
                    true
                );
            }

            return self::postApi($params, self::$render_preview_uri . '?imagesAsFiles=0');
        }
    }

    /**
     *
     */
    public static function viddyozeCheckLogin(){
        echo "You must log in to vote";
        exit;
    }

    /**
     *
     */
    public static function viddyozeHandleRender(){
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $p = array();

            $data = sanitize_text_field($_REQUEST['data']);

            parse_str($data, $p);
            if (isset($p["templateCustomisationId"]) && isset($p['template_id'])) {
                $params = array(
                    'template_id' => $p['template_id'],
                    'template_customisation_id' => $p["templateCustomisationId"],
                    'audio' => true,
                    'client_id' => 0,
                );

                $response = self::postApi($params, self::$render_uri);
                if (!isset($response->id)) {
                    $result["error"] = 1;
                    $result["msg"] = "Whoa, hold it there! You can only render one video at a time with your account. Please wait for your current render to finish before creating a new one.";
                }
                else {
                    $result["error"] = 0;
                    $result["url"] = admin_url( '/admin.php?page=' . self::$pages['videos'] );
                }
            } else {
                $result["error"] = 1;
                $result["msg"] = "We're sorry. There was a problem creating your video, please try again.";
            }
            $result = json_encode($result);
            echo $result;
            exit;
        }
    }

    /**
     *
     */
    public static function viddyozeHandleRenderDelete(){
        $render_id = self::viddyozeSanitizeInt(sanitize_text_field($_GET['viddyoze_renderid']), false);
        if (!empty($render_id)) {
            $response = self::deleteApi([], self::$render_uri . '/' . $render_id);
            wp_redirect( admin_url( '/admin.php?page=' . self::$pages['videos'] ) );
            exit;
        }
    }

    /**
     *
     */
    public static function viddyozeNotice(){
        /*
         * set admin notice also make it dismissable
         */
        $class = 'notice notice-warning is-dismissible';
        $message = 'You are using Viddyoze plugin without API keys, please refer to the documentation.';

        printf( '<div class="%1$s"><p>%2$s <a href="#">here</a></p></div>', esc_attr( $class ), esc_html__( $message, VIDDYOZE_TEXT_DOMAIN) );
    }

    /**
     *
     */
    public static function time_elapsed_string($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);
    
        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;
    
        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }
    
        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

    
	
	/**
	 * viddyozeOptionsPage
	 * Create Options page
	 */
	public static function viddyozeOptionsPage() {
	    $image = file_get_contents(VIDDYOZE_ADMIN_URL . 'images/viddyoze-plugin-icon.png');
	    $image_data = base64_encode($image);
		add_menu_page(
			'Viddyoze Plugin Settings', // page_title
			'Viddyoze', // menu_title
			'manage_options', // capability
            self::$pages['options'], // menu_slug
			array( 'Viddyoze', 'viddyozeOptionsPageContent' ), // function
            'data:image/png;base64,'.$image_data, // icon_url
			100 // position
        );
        add_submenu_page(
            self::$pages['options'], // parent_slug
            'Viddyoze Templates', // page_title
            'Settings', // submenu_title
            'manage_options', // capability
            self::$pages['options'], // submenu_slug
            array( 'Viddyoze', 'viddyozeOptionsPageContent' ), // function
            100 // position
        );
        add_action( 'admin_init', array( 'Viddyoze', 'viddyozeOptionsSave' ) );
    }

    /**
     *
     */
    public static function viddyozeWelcomePageContent() {
        ?>
        <div class="wrap viddyoze_welcome_wrapper">
            <div class="my-2">

                <div class="viddyoze_welcome_cols">
                    <div class="viddyoze_welcome_column about_viddyoze">
                        <svg width="120px" height="36px" viewBox="0 0 120 36" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <g id="Symbols" stroke="none" stroke-width="1" fill-rule="evenodd">
                                <g id="Viddyoze-Main" transform="translate(-660.000000, -31.000000)">
                                    <g id="Group-8">
                                        <g transform="translate(153.000000, 31.000000)">
                                            <path d="M511.913769,0 L622.086513,0 C624.789072,0 627,2.20800977 627,4.90637525 L627,24.1519915 C627,26.8506388 624.789072,29.0583667 622.086513,29.0583667 L580.771452,29.0583667 L567.739563,36 L579.577934,10.2191258 L576.444422,10.2191258 L572.943457,17.8500752 L569.783133,10.2191258 L566.642001,10.2191258 L571.292457,21.5068888 L567.812659,29.0583667 L511.913769,29.0583667 C509.21121,29.0583667 507,26.8506388 507,24.1519915 L507,4.90637525 C507,2.20800977 509.21121,0 511.913769,0 L511.913769,0 Z M618.838136,17.172877 L609.012573,17.172877 C609.154531,18.0414266 609.533555,18.7335609 610.146541,19.2501253 C610.759528,19.7627442 611.54467,20.0189128 612.49463,20.0189128 C613.636218,20.0189128 614.612989,19.6173284 615.432844,18.8138778 L617.999372,20.0265218 C617.359575,20.9370616 616.589673,21.6103144 615.697004,22.0499436 C614.800949,22.4861911 613.739794,22.7040331 612.50987,22.7040331 C610.60233,22.7040331 609.047004,22.1034882 607.848125,20.898735 C606.649245,19.6976453 606.04783,18.190506 606.04783,16.3812625 C606.04783,14.5260834 606.649245,12.9845629 607.848125,11.7606463 C609.047004,10.5325025 610.548708,9.92068512 612.360574,9.92068512 C614.279684,9.92068512 615.842631,10.5325025 617.045461,11.7527555 C618.248292,12.9769539 618.849707,14.587237 618.849707,16.5954409 L618.838136,17.172877 L618.838136,17.172877 Z M615.792959,14.7861974 C615.58976,14.1205536 615.191544,13.5774987 614.597749,13.1604146 C614.000002,12.7436122 613.310534,12.5370428 612.521441,12.5370428 C611.667154,12.5370428 610.920395,12.7703845 610.272977,13.2410133 C609.87081,13.5315631 609.495455,14.0478457 609.15058,14.7861974 L615.792959,14.7861974 L615.792959,14.7861974 Z M595.544087,10.2191258 L604.898621,10.2191258 L599.401499,19.8123434 L604.576606,19.8123434 L604.576606,22.4058742 L594.693751,22.4058742 L600.190592,12.8123747 L595.544087,12.8123747 L595.544087,10.2191258 L595.544087,10.2191258 Z M587.078552,9.92068512 C588.231429,9.92068512 589.315444,10.2075714 590.330597,10.7813439 C591.349419,11.3551165 592.138794,12.131513 592.709448,13.114479 C593.280101,14.1013903 593.563734,15.1610095 593.563734,16.3009456 C593.563734,17.4484907 593.276432,18.5193825 592.705497,19.5178482 C592.130892,20.5160321 591.349419,21.296374 590.36136,21.858874 C589.369067,22.4210922 588.2811,22.7040331 587.089841,22.7040331 C585.335548,22.7040331 583.837795,22.0843249 582.600533,20.8451904 C581.359321,19.6018287 580.738714,18.0949712 580.738714,16.3237725 C580.738714,14.4226578 581.439754,12.839147 582.841833,11.5729584 C584.071475,10.4713489 585.480892,9.92068512 587.078552,9.92068512 L587.078552,9.92068512 Z M587.124272,12.7895478 C586.170643,12.7895478 585.377599,13.1184243 584.745422,13.7764592 C584.113526,14.4381576 583.795462,15.2796531 583.795462,16.3085546 C583.795462,17.3642285 584.109575,18.2212237 584.734133,18.8753131 C585.358408,19.5294026 586.151452,19.8582791 587.112701,19.8582791 C588.070563,19.8582791 588.867276,19.5254572 589.507073,18.8637588 C590.14292,18.2020604 590.460702,17.3490105 590.460702,16.3085546 C590.460702,15.2641533 590.146871,14.4189942 589.522313,13.7649048 C588.898038,13.114479 588.097374,12.7895478 587.124272,12.7895478 L587.124272,12.7895478 Z M561.86511,5.51424724 L564.921857,5.51424724 L564.921857,22.4058742 L561.86511,22.4058742 L561.86511,21.1089679 C561.267645,21.6751315 560.66623,22.0806613 560.064815,22.3292209 C559.463399,22.5777806 558.808362,22.7040331 558.107322,22.7040331 C556.525185,22.7040331 555.161487,22.0958793 554.00861,20.8756263 C552.855451,19.6593186 552.280846,18.1445704 552.280846,16.3353269 C552.280846,14.4573209 552.840211,12.9194639 553.954988,11.7183742 C555.069765,10.52123 556.421891,9.92068512 558.015318,9.92068512 C558.750788,9.92068512 559.436588,10.058492 560.080055,10.3298785 C560.723521,10.6054922 561.317316,11.018631 561.86511,11.5653494 L561.86511,5.51424724 L561.86511,5.51424724 Z M558.643544,12.7207853 C557.693584,12.7207853 556.904491,13.0533254 556.279934,13.7229146 C555.65199,14.3882766 555.337876,15.2452718 555.337876,16.2893913 C555.337876,17.3414016 555.655658,18.2057239 556.295456,18.8865857 C556.935254,19.5637838 557.720395,19.9042147 558.655115,19.9042147 C559.616646,19.9042147 560.417028,19.5713928 561.049205,18.9020854 C561.685052,18.2364416 562.003116,17.3602831 562.003116,16.2778369 C562.003116,15.2184995 561.685052,14.3615043 561.049205,13.7037513 C560.417028,13.0496618 559.612695,12.7207853 558.643544,12.7207853 L558.643544,12.7207853 Z M546.692002,5.51424724 L549.748749,5.51424724 L549.748749,22.4058742 L546.692002,22.4058742 L546.692002,21.1089679 C546.094537,21.6751315 545.493122,22.0806613 544.891424,22.3292209 C544.290291,22.5777806 543.635254,22.7040331 542.934214,22.7040331 C541.352077,22.7040331 539.98838,22.0958793 538.835502,20.8756263 C537.682343,19.6593186 537.107738,18.1445704 537.107738,16.3353269 C537.107738,14.4573209 537.667103,12.9194639 538.781598,11.7183742 C539.896375,10.52123 541.248784,9.92068512 542.84221,9.92068512 C543.57768,9.92068512 544.263198,10.058492 544.906947,10.3298785 C545.550413,10.6054922 546.144208,11.018631 546.692002,11.5653494 L546.692002,5.51424724 L546.692002,5.51424724 Z M543.470436,12.7207853 C542.520476,12.7207853 541.731383,13.0533254 541.106826,13.7229146 C540.478882,14.3882766 540.164486,15.2452718 540.164486,16.2893913 C540.164486,17.3414016 540.48255,18.2057239 541.122348,18.8865857 C541.761863,19.5637838 542.547287,19.9042147 543.482007,19.9042147 C544.443538,19.9042147 545.24392,19.5713928 545.876097,18.9020854 C546.511944,18.2364416 546.830008,17.3602831 546.830008,16.2778369 C546.830008,15.2184995 546.511944,14.3615043 545.876097,13.7037513 C545.24392,13.0496618 544.439587,12.7207853 543.470436,12.7207853 L543.470436,12.7207853 Z M533.365191,5.21580661 C533.901695,5.21580661 534.361153,5.41082164 534.74441,5.79718813 C535.131055,6.18355461 535.322683,6.65023798 535.322683,7.2009018 C535.322683,7.74423848 535.131055,8.20697645 534.75203,8.58939755 C534.372724,8.97210045 533.916935,9.16317009 533.384382,9.16317009 C532.84054,9.16317009 532.376849,8.96815506 531.993873,8.578125 C531.606947,8.18781313 531.4156,7.71352079 531.4156,7.155248 C531.4156,6.61952029 531.606947,6.16439128 531.986253,5.78563377 C532.365278,5.40715807 532.825018,5.21580661 533.365191,5.21580661 L533.365191,5.21580661 Z M531.829056,10.2191258 L534.885804,10.2191258 L534.885804,22.4058742 L531.829056,22.4058742 L531.829056,10.2191258 Z M515.150575,5.92738602 L518.37976,5.92738602 L522.528426,17.6319514 L526.757524,5.92738602 L529.975139,5.92738602 L524.018558,22.4058742 L520.98834,22.4058742 L515.150575,5.92738602 L515.150575,5.92738602 Z" id="Viddyoze"></path>
                                        </g>
                                    </g>
                                </g>
                            </g>
                        </svg>
                        <h1>Welcome to Viddyoze</h1>
                        <h2>Make Scroll-Stopping Videos In Minutes</h2>
                        <p>Bring your ideas to life with just a few clicks - even if you’ve never made videos before!</p>
                        <p class="mb-10">To get started, do the following:</p>
                        <ul>
                            <li>
                                Either log in to your existing Viddyoze account or create a new <strong>free</strong> account.
                                <br>
                                <br>
                                <p class="text-center">
                                    <a class="viddyoze_button" target="_blank" href="https://app.viddyoze.com">Log In</a>
                                    <a class="viddyoze_button" target="_blank" href="https://checkout.viddyoze.com/purchase/checkout/wordpress">Create An Account</a>
                                </p>
                                <br>
                            </li>
                            <li>Grab your API key from your profile page in the <a class="viddyoze_link" target="_blank" href="https://app.viddyoze.com/accounts/profile">Viddyoze App</a></li>
                            <li>Enter your API key below, or in the Viddyoze plugin <a class="viddyoze_link" href="<?php menu_page_url(self::$pages['options']) ?>">Settings page</a></li>
                        </ul>
                        <p>
                        <form method="post" action="options.php">
                            <?php settings_fields( 'viddyoze-options' ); ?>
                            <?php do_settings_sections( 'viddyoze-options' ); ?>
                            <table class="form-table">
                                <tr valign="top">
                                    <th scope="row">
                                        <label for="viddyoze_api_host">Viddyoze API Host *</label>
                                    </th>
                                    <td>
                                        <input type="text" name="viddyoze_api_host" id="viddyoze_api_host" readonly required value="<?php echo esc_attr( get_option('viddyoze_api_host', 'https://api.viddyoze.com') ); ?>" class="regular-text"/>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">
                                        <label for="viddyoze_api_key">Viddyoze API Key *</label>
                                    </th>
                                    <td>
                                        <input type="text" name="viddyoze_api_key" id="viddyoze_api_key" value="<?php echo esc_attr( get_option('viddyoze_api_key') ); ?>" class="regular-text"/>
                                    </td>
                                </tr>
                            </table>

                            <?php submit_button('Save', 'viddyoze_button'); ?>

                        </form>
                        </p>
                    </div>
                    <div class="viddyoze_welcome_column about_viddyoze_image">
                        <iframe src="https://player.vimeo.com/video/597304407" width="640" height="564" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
                    </div>
                </div>

            </div>
        </div>
        <?php
    }
	
	/**
	 * viddyozeTemplatesPage
	 * Create Templates page
	 */
	public static function viddyozeTemplatesPage() {
        add_submenu_page(
            self::$pages['options'], // parent_slug
            'Viddyoze Templates', // page_title
			'Templates', // submenu_title
			'manage_options', // capability
            self::$pages['templates'], // submenu_slug
			array( 'Viddyoze', 'viddyozeTemplatesPageContent' ), // function
			100 // position
        );
    }
    
	
	/**
	 * viddyozeVideosPage
	 * Create Videos page
	 */
	public static function viddyozeVideosPage() {
        add_submenu_page(
            self::$pages['options'], // parent_slug
            'Viddyoze Videos', // page_title
			'My Videos', // submenu_title
			'manage_options', // capability
            self::$pages['videos'], // submenu_slug
			array( 'Viddyoze', 'viddyozeVideosPageContent' ), // function
			100 // position
        );
    }


    /**
     * viddyozeSupportPage
     * Support page
     */
    public static function viddyozeSupportPage() {
        add_submenu_page(
            self::$pages['options'], // parent_slug
            'Viddyoze Support', // page_title
            'Support', // submenu_title
            'manage_options', // capability
            self::$pages['support'], // submenu_slug
            array( 'Viddyoze', 'viddyozeSupportPageContent' ), // function
            100 // position
        );
    }
    
	
	/**
	 * viddyozeTemplateSingle
	 * Create Templates page
	 */
	public static function viddyozeTemplateSingle() {
        add_submenu_page(
            self::$pages['templates'], // parent_slug
            'Template Single', // page_title
			'Template', // submenu_title
			'manage_options', // capability
            self::$pages['template'], // submenu_slug
			array( 'Viddyoze', 'viddyozeTemplateSingleContent' ), // function
			100 // position
        );
    }

    
	/**
	 * viddyozeOptionsSave
	 * create form admin page
	 */
	public static function viddyozeOptionsSave() {
        register_setting( 'viddyoze-options', 'viddyoze_api_host' );
        register_setting( 'viddyoze-options', 'viddyoze_api_key' );
	}

    
	/**
	 * viddyozeOptionsPageContent
	 * create form admin page
	 */
	public static function viddyozeOptionsPageContent() {
        ?>
        <div class="wrap viddyoze-settings">
        <h1>Viddyoze Plugin Settings</h1>

        <p>To get started, do the following:</p>

        <ul>
            <li>
                Either <a target="_blank" href="https://app.viddyoze.com">log in</a> to your existing Viddyoze account or
                <a target="_blank" href="https://checkout.viddyoze.com/purchase/checkout/wordpress">create a new free account</a>.
            </li>
            <li>Grab your API key from your <a target="_blank" href="https://app.viddyoze.com/accounts/profile">profile page in the Viddyoze App</a></li>
            <li>Enter your API key below</li>
        </ul>

        <form method="post" action="options.php">
            <?php settings_fields( 'viddyoze-options' ); ?>
            <?php do_settings_sections( 'viddyoze-options' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="viddyoze_api_host">Viddyoze API Host *</label>
                    </th>
                    <td>
                        <input type="text" name="viddyoze_api_host" id="viddyoze_api_host" readonly required value="<?php echo esc_attr( get_option('viddyoze_api_host', 'https://api.viddyoze.com') ); ?>" class="regular-text"/>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="viddyoze_api_key">Viddyoze API Key *</label>
                    </th>
                    <td>
                        <input type="text" name="viddyoze_api_key" id="viddyoze_api_key" value="<?php echo esc_attr( get_option('viddyoze_api_key') ); ?>" class="regular-text"/>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>

        </form>
        </div>
    <?php 		
    }    

    public static function callApi($params, $apiUrl, $return_on_failure = false) {
        global $wp_version;
        $headers = array(
            'X-API-KEY' => self::$api_key,
            'Content-Type' => 'application/json'
        );
        $args = array(
            'headers' => $headers,
            'method' => 'GET',
            'timeout' => 60,
            'redirection' => 5,
            'httpversion' => '1.0',
            'user-agent'  => 'WordPress/' . $wp_version . '; ' . home_url(),
            'blocking' => true,
            'sslverify' => false,
		    'cookies' => array()
        );

        $result = wp_remote_get( self::$api_host . $apiUrl . '?' . http_build_query($params), $args );

        if (isset($result->errors)) {
            return false;
        }
        if (isset($result['response']['code']) && isset($result['body'])) {
            if ($result['response']['code'] == 200 || $return_on_failure)
            return json_decode($result['body']);
        }
        return false;
    }       

    public static function deleteApi($params, $apiUrl) { 
        global $wp_version;
        $headers = array(
            'X-API-KEY' => self::$api_key,
            'Content-Type' => 'application/json'
        );
        $args = array(
            'headers' => $headers,
            'method' => 'DELETE',
            'timeout' => 60,
            'redirection' => 5,
            'httpversion' => '1.0',
            'user-agent'  => 'WordPress/' . $wp_version . '; ' . home_url(),
            'blocking' => true,
            'sslverify' => false,
		    'cookies' => array()
        );
        
        $result = wp_remote_request( self::$api_host . $apiUrl . '?' . http_build_query($params), $args );
        if (isset($result->errors)) {
            return false;
        }
        if (isset($result['response']['code']) && $result['response']['code'] == 204) {
            return true;
        }
        return false;
    }    

    public static function postApi($params, $apiUrl) { 
        global $wp_version;
        $headers = array(
            'X-API-KEY' => self::$api_key,
            'Content-Type' => 'application/x-www-form-urlencoded'
        );
        $args = array(
            'headers' => $headers,
            'method' => 'POST',
            'body' => $params,
            'timeout' => 300,
            'redirection' => 5,
            'httpversion' => '1.0',
            'user-agent'  => 'WordPress/' . $wp_version . '; ' . home_url(),
            'blocking' => true,
            'sslverify' => false,
		    'cookies' => array()
        );
        
        $result = wp_remote_post( self::$api_host . $apiUrl, $args );
        if (isset($result->errors)) {
            return false;
        }
        if (isset($result['response']['code']) && $result['response']['code'] == 201 && isset($result['body'])) {
            return json_decode($result['body']);
        }
        return false;
    }

    public static function isBusiness() {
        return self::isLevel('isBusinessMember');
    }

    public static function isAgencyUser() {
        return self::isLevel('isAgencyResellerClient');
    }

    public static function isTemplateClubPlus() {
        return self::isLevel('isTemplateClubPlusMember');
    }

    public static function isTemplateClub() {
        return self::isLevel('isTemplateClubMember');
    }

    public static function isTemplateClubLite() {
        return self::isLevel('isTemplateClubLiteMember');
    }

    public static function isTemplateCommercial() {
        return self::isLevel('isCommercialMember');
    }

    public static function isReseller() {
        return self::isLevel('isAgencyResellerClient');
    }

    public static function isLicenceOnly() {
        return !self::isLevel('isBusinessMember')
            && !self::isLevel('isTemplateClubMember')
            && !self::isLevel('isTemplateClubPlusMember')
            && !self::isLevel('isAgencyResellerClient')
            && (self::isLevel('isPersonalMember') || self::isLevel('isCommercialMember'));
    }

    public static function isFreeMember() {
        return !self::isLevel('isCommercialMember')
            && !self::isLevel('isTemplateClubLiteMember')
            && !self::isLevel('isBusinessMember')
            && !self::isLevel('isTemplateClubMember')
            && !self::isLevel('isTemplateClubPlusMember')
            && !self::isLevel('isAgencyResellerClient');
    }

    public static function canToggleTemplates() {
	    if (self::isLevel('isBusinessMember') || self::isLevel('isTemplateClubPlusMember') || self::isLevel('isAgencyResellerClient')) {
	        return false;
        }
	    if (self::isLevel('isTemplateClubMember')) {
	        return self::isLevel('isTemplateClubLiteMember');
        }
	    return true;
    }

    public static function isLevel($level) {
        $subscriptions = self::getAccountSubscriptions();

        if (isset($subscriptions->{$level}) && $subscriptions->{$level}) {
            return true;
        }

        return false;
    }
    
    public static function getTemplates() {
        $filter_values = self::getFilterValues();

        $params = array(
            'page' => self::viddyozeSanitizeInt(sanitize_text_field($_GET['paged']), 1),
            'filters[name]' => sanitize_text_field($_GET['viddyoze_search']),
            'filters[category]' => self::viddyozeSanitizeInt(sanitize_text_field($_GET['viddyoze_category']), ""),
            'filters[duration]' => self::viddyozeFilterValues(sanitize_text_field($_GET['viddyoze_duration']), $filter_values['durations']),
            'filters[ratio]' => self::viddyozeFilterValues(sanitize_text_field($_GET['viddyoze_ratio']), $filter_values['ratios']),
            'filters[customization]' => self::viddyozeSanitizeArray($_GET['viddyoze_customizations'], $filter_values['customisations']),
            'filters[images]' => self::viddyozeFilterValues(sanitize_text_field($_GET['viddyoze_nb_image']), $filter_values['images']),
        );

        $library = self::viddyozeSanitizeInt(sanitize_text_field($_GET['viddyoze_library']), 0);
        if ($library == 1) {
            $params['filters[tc]'] = 'true';
        }
        $response = self::callApi($params, self::$templates_uri);
        return $response != null ? $response : [];
    }

    public static function getAccountSubscriptions() {
	    $subscriptions = get_transient('viddyoze_subscriptions');
	    if (!$subscriptions) {
            $subscriptions = self::callApi([], self::$subscriptions_uri);
            set_transient('viddyoze_subscriptions', $subscriptions, 86400);
        }
	    return $subscriptions;
    }

    /**
     * @param $template_id
     * @return object
     */
    public static function checkCanBeRendered($template_id) {
        $uri = str_replace('{TEMPLATE_ID}', $template_id, self::$template_can_be_rendered_uri);
        $result = self::callApi([], $uri, true);
        return (object)array(
            'can_render' => $result && isset($result->code) && $result->code === 200,
            'code' => isset($result->code) ? $result->code : null,
            'message' => isset($result->message) ? $result->message : null
        );
    }

    public static function getVideoStatus($id) { 
        return self::callApi([], self::$render_uri. '/' . $id);
    }
    
    public static function getVideos() { 
        $params = array();
        if (isset($_GET['paged'])) {
            $params['page'] = sanitize_text_field($_GET['paged']);
        }
        if (isset($_GET['viddyoze_search'])) {
            $params['filters[name]'] = sanitize_text_field($_GET['viddyoze_search']);
        }
        if (isset($_GET['viddyoze_ratio'])) {
            $params['filters[ratio]'] = sanitize_text_field($_GET['viddyoze_ratio']);
        }
        $response = self::callApi($params, self::$renders_uri);
        return $response != null ? $response : [];
    }
    
    public static function getTemplate() {
	    $template_id = self::viddyozeSanitizeInt(sanitize_text_field($_GET['t']), null);
        $params = array(
            'id' => $template_id,
        );
        $template = wp_cache_get('viddyoze_template_'.$template_id);
        if (!$template) {
            $template = self::callApi($params, self::$template_uri . '/' . $template_id);
            wp_cache_set('viddyoze_template_'.$template_id, $template, 'viddyoze', 86400);
        }
        return $template;
    }
    
    public static function getCustomization() {
        $customization_id = self::viddyozeSanitizeInt(sanitize_text_field($_GET['c']), null);
        if (!$customization_id) {
            return null;
        }
        $template_id = self::viddyozeSanitizeInt(sanitize_text_field($_GET['t']), null);
        $params = array(
            'id' => $customization_id,
        );
        $customization = wp_cache_get('viddyoze_customization_'.$template_id);
        if (!$customization) {
            $customization = self::callApi($params, self::$customization_uri . '/' . $customization_id);
            wp_cache_set('viddyoze_customization_'.$template_id, $customization, 'viddyoze', 86400);
        }
        return $customization;
    }
    
    public static function getFonts() { 
        $fonts = wp_cache_get('viddyoze_fonts');
        if (!$fonts) {
            $fonts = self::callApi([], self::$font_uri);
            wp_cache_set('viddyoze_fonts', $fonts, 'viddyoze', 86400);
        }
        return $fonts;
    }
    
    public static function getCategories() {   
        $categories = get_transient('viddyoze_categories');
        if (!$categories) {
            $categories = self::callApi([], self::$categories_uri);
            set_transient('viddyoze_categories', $categories, 86400);
        }
        return $categories;
    }
    
    public static function getFilters() { 
        $filters = get_transient('viddyoze_filters', 'viddyoze');
        if (!$filters) {
            $filters = self::callApi([], self::$filters_uri);
            set_transient('viddyoze_filters', $filters, 86400);
        }
        return $filters;
    }

    private static function getFilterTypes() {
        $filter_types = wp_cache_get('viddyoze_filter_types');
        if (!$filter_types) {
            $filters = self::getFilters();
            $filter_types = [];
            foreach ($filters as $filter => $values) {
                $filter_types[] = $filter;
            }
            wp_cache_set('viddyoze_filter_types', $filter_types, 'viddyoze', 86400);
        }
        return $filter_types;
    }

    private static function getFilterValues() {
        $filter_values = get_transient('viddyoze_filter_values');
        if (!$filter_values) {
            $filters = self::getFilters();
            $filter_values = [];
            foreach ($filters as $filter => $values) {
                $filter_values[$filter] = array_map(function ($value) {
                    return isset($value->code) ? $value->code : null;
                }, $values);
            }
            $filter_values = array_filter($filter_values);
            set_transient('viddyoze_filter_values', $filter_values, 86400);
        }
        return $filter_values;
    }
    
    public static function renderImagesForm($images, $customization) { 
        ?>
            <div class="flex flex-wrap text-center">
        <?php
        $i = 0;
        foreach ($images as $image) {
            // echo "<pre>";var_dump($customization);echo "</pre>";
            ?>  
            <div>
                <label class="w-40 h-40 flex flex-col items-center px-4 py-6 m-3 bg-white text-blue rounded-lg shadow-lg tracking-wide uppercase border border-blue cursor-pointer hover:bg-blue hover:text-blue-600 bg-contain bg-center bg-no-repeat relative" <?php if(isset($customization->payload->images[$i]->value) && !empty($customization->payload->images[$i]->value)) {
                    echo "style='background-image: url(\"" . $customization->payload->images[$i]->value . "\")'";
                } ?>>
                    <svg class="w-8 h-8 text-blue-600 addable <?php if(isset($customization->payload->images[$i]->value) && !empty($customization->payload->images[$i]->value)) {
                    echo "hidden";
                } ?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                    </svg>
                    <svg class="w-8 h-8 text-gray-500 editable <?php if(!(isset($customization->payload->images[$i]->value) && !empty($customization->payload->images[$i]->value))) {
                    echo "hidden";
                } ?> absolute top-0 right-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <input type='file' name="images[]" id="viddyoze_<?php echo sanitize_title( $image->name ) ?>" class="hidden viddyoze-file" onchange="readURL(this);" accept=".jpg,.jpeg,.png"/>
                    <input type='hidden' name="images_data[]" class="datavalue" value='<?php if(isset($customization->payload->images[$i]->value) && !empty($customization->payload->images[$i]->value)) { echo $customization->payload->images[$i]->value; } ?>'>
                </label>
                <span class="mt-2 uppercase tracking-wide text-grey-darker text-xs font-bold mb-2"><?php echo $image->name; ?></span>
            </div>
            <?php   
            $i++;         
        }
        ?>
            </div>
        <?php
    }

    public static function renderTextsForm($texts, $fontGroups, $customization) {
        if (!empty($fontGroups)) {
            $textGroups = [];
            foreach ($fontGroups as $key => $fontGroup) {
                foreach ($texts as $text ) {
                    if (in_array($text->name, $fontGroup)) {
                        $textGroups[$key][] = $text;
                    }
                }
            }
            $fonts = self::getFonts();
            $default_font_id = null;
            $default_font_name = null;
            ?>
            <div class="bg-gray-200 rounded-lg mb-6 py-3">
                <!-- Toggle Button -->
                <label for="toogle_fonts" class="flex items-center cursor-pointer">
                    <h3 class="p-3 relative mr-3 text-lg font-bold uppercase">Customise your fonts</h3>
                    <!-- toggle -->
                    <div class="relative">
                    <!-- input -->
                    <input id="toogle_fonts" name="toogle_fonts" type="checkbox" class="hidden" style="display: none;" onclick="toggleFont()"/>
                    <!-- line -->
                    <div class="toggle__line w-10 h-4 bg-gray-400 rounded-full shadow-inner"></div>
                    <!-- dot -->
                    <div class="toggle__dot absolute w-6 h-6 bg-white rounded-full shadow inset-y-0 left-0"></div>
                    </div>
                    <!-- label -->
                </label>
                <div class="flex flex-col font-things hidden">
                    <div class="md:w-1/2 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-bold mb-2" for="viddyoze_<?php echo $key ?>_font_type">
                        Font type
                        </label>
                        <select name="viddyoze_font_type" id="viddyoze_font_type" class="appearance-none block w-full bg-grey-lighter text-grey-darker border rounded py-3 px-4 mb-3">
                        <?php
                        foreach ($fonts as $font) {
                            $selected = "";
                            if ($font->default) {
                                $default_font_id = $font->id;
                                $default_font_name = $font->fontFamilyList[0]->name;
                                $selected = "selected";
                            }
                            ?>
                            <option value="<?php echo $font->id ?>" <?php echo $selected ?>><?php echo $font->name ?></option>
                            <?php
                        }
                        ?>
                        </select>
                    </div>
                </div>
            </div>

            <?php
            foreach ($textGroups as $key => $textGroup) {
            ?>
            <div class="bg-gray-200 rounded-lg mb-6 font-things hidden">
                <h3 class="p-3 mb-3 text-lg font-bold uppercase">font group : <?php echo $key ?></h3>
                <div class="flex flex-col">
                    <div class="md:w-1/2 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-bold mb-2" for="viddyoze_<?php echo $key ?>_font_family">
                        Font family: <span class="viddyoze-font-family"><?php echo $default_font_name; ?></span>
                        </label>
                        <select name="viddyoze_font_family[]" id="viddyoze_<?php echo $key ?>_font_family" class="viddyoze_font_family appearance-none block w-full bg-grey-lighter text-grey-darker border rounded py-3 px-4 mb-3">
                        <?php
                        foreach ($fonts as $font) {
                            foreach ($font->fontFamilyList as $fontFamily) {
                            ?>
                            <optgroup label="<?php echo $fontFamily->name ?>" class="viddyoze-font-group viddyoze-font-<?php echo $font->id ?> <?php echo $font->id === $default_font_id ? 'viddyoze-font-selected' : 'viddyoze-font-unselected' ?>">
                                <?php
                                $i = 0;
                                foreach ($fontFamily->fontList as $item) {
                                    ?><option value="<?php echo $item->id; ?>"<?php echo $i === 0 ? ' selected' : ''; ?>><?php echo $item->name; ?></option><?php
                                    $i++;
                                }
                                ?>
                            </optgroup>
                            <?php
                            }
                        }
                        ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap">
            <?php
                $i = 0;
                foreach ($textGroup as $text) {
                ?>  
                <div class="md:w-1/2 px-3 mb-6 md:mb-0">
                    <label class="block uppercase tracking-wide text-grey-darker text-xs font-bold mb-2" for="viddyoze_<?php echo sanitize_title( $text->name ) ?>">
                        <?php echo $text->name; ?>
                    </label>
                    <input class="appearance-none block w-full bg-grey-lighter text-grey-darker border rounded py-3 px-4 mb-3" id="viddyoze_<?php echo sanitize_title( $text->name ) ?>" name="texts[]" type="text" placeholder="<?php echo $text->default; ?>" value="<?php if(isset($customization->payload->texts[$i]->value) && !empty($customization->payload->texts[$i]->value)) { echo $customization->payload->texts[$i]->value; } else {echo $text->default;} ?>" required>
                </div>
                <?php            
                    $i++;         
                }
            ?>
            </div>
            <?php
            }
        }
        else {
        ?>
        <div class="flex flex-wrap">
        <?php
            $i = 0;
            foreach ($texts as $text) {
            ?>  
            <div class="md:w-1/2 px-3 mb-6 md:mb-0">
                <label class="block uppercase tracking-wide text-grey-darker text-xs font-bold mb-2" for="viddyoze_<?php echo sanitize_title( $text->name ) ?>">
                    <?php echo $text->name; ?>
                </label>
                <input class="appearance-none block w-full bg-grey-lighter text-grey-darker border rounded py-3 px-4 mb-3" id="viddyoze_<?php echo sanitize_title( $text->name ) ?>" name="texts[]" type="text" placeholder="<?php echo $text->default; ?>" value="<?php if(isset($customization->payload->texts[$i]->value) && !empty($customization->payload->texts[$i]->value)) { echo $customization->payload->texts[$i]->value; } else {echo $text->default;} ?>" required>
            </div>
            <?php            
                $i++;         
            }
        ?>
        </div>
        <?php
        }
    }

    public static function renderColorsForm($colors, $customization) { 
        $i = 0;
        foreach ($colors as $color) {
            ?>  
        <div class="flex flex-wrap">
            <div class="px-3 mb-6 md:mb-0">
                <label class="block uppercase tracking-wide text-grey-darker text-xs font-bold mb-2" for="viddyoze_<?php echo sanitize_title( $color->name ) ?>">
                    <?php echo $color->name; ?>
                </label>
                <input class="appearance-none block w-full bg-grey-lighter text-grey-darker border rounded py-3 px-4 mb-3 color-field" id="viddyoze_<?php echo sanitize_title( $color->name ) ?>" name="colors[]" type="text" value="<?php if(isset($customization->payload->colors[$i]->value) && !empty($customization->payload->colors[$i]->value)) { echo $customization->payload->colors[$i]->value; } else {echo "#" . $color->default;} ?>" required>
            </div>
        </div>
            <?php      
            $i++;      
        }
    }

    public static function renderAudioForm($audios, $customization) {

        /**
         * isTemplateClubPlusMember || isBusinessMember || isAgencyResellerClient
        ? true : (!!(isTemplateClubMember && index === 0))
         */

        $i=0;
        foreach ($audios as $audio) {
            $checked = ((isset($customization->payload->template_audio) && $audio->id == $customization->payload->template_audio) or (!isset($customization->payload->template_audio) && $i == 0))? "checked": '';
            $selectable = ($i === 0 || self::isTemplateClubPlus() || self::isBusiness() || self::isReseller()) ? true : !!(self::isLicenceOnly() && $i === 0);
            $i++;
            ?>  
            <label class="flex items-center py-4">
                <input type="radio" name="template_audio" class="h-5 w-5 text-blue-600" <?php echo $checked; ?> value="<?php echo $audio->id; ?>"<?php echo !$selectable ? ' disabled' : ''; ?>>
                <audio controls loop src="https://api.viddyoze.com/templates/audio/<?php echo $audio->id; ?>/file">
                </audio>
                <span class="ml-2 my-2 uppercase tracking-wide text-grey-darker text-xs font-bold "><?php echo $audio->name; ?></span>
            </label>
            <?php            
        }
    }


    public static function pagination($pages, $href) {
        $output = '';

        $paged = self::viddyozeSanitizeInt(sanitize_text_field($_REQUEST['paged']), 1);

        //if pages exists after loop's lower limit
        if($pages >= 1) {

            if (($paged - 2) > 0) {
                $output = $output . '<a href="' . $href . 'paged=1" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">1</a>';
            }
            if (($paged - 2) > 1) {
                $output = $output . '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
            }

            //Loop for provides links for 2 pages before and after current page
            for ($i = ($paged - 1); $i <= ($paged + 1); $i++) {
                if ($i < 1) continue;

                if ($i > $pages) break;

                if ($paged == $i) {
                    $output = $output . '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">' . $i . '</span>';
                } else {
                    $output = $output . '<a href="' . $href . "paged=" . $i . '" class="md:inline-flex relative items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">' . $i . '</a>';
                }
            }

            //if pages exists after loop's upper limit
            if (($pages - ($paged + 1)) > 1) {
                $output = $output . '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
            }

            if (($pages - ($paged + 1)) > 0) {
                if ($paged == $pages) {
                    $output = $output . '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">' . $pages . '</span>';
                } else {
                    $output = $output . '<a href="' . $href . "paged=" . $pages . '" class="md:inline-flex relative items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">' . $pages . '</a>';
                }
            }
        }
        return $output;
    }

    
	/**
	 * viddyozeTemplatesPageContent
	 * create form admin page
	 */
	public static function viddyozeTemplatesPageContent() {
        global $wp;

        $categories_data = self::getCategories();
        $template_data = self::getTemplates();
        $filters = self::getFilters();
        $filter_values = self::getFilterValues();

        $categories = (isset($categories_data->categories))? $categories_data->categories: [];
        $ratios = (isset($filters->ratios) && $filters->ratios)? $filters->ratios: [];
        $durations = (isset($filters->durations) && $filters->durations)? $filters->durations: [];
        $customizations = (isset($filters->customisations) && $filters->customisations)? $filters->customisations: [];
        $nb_images = (isset($filters->images) && $filters->images)? $filters->images: [];
        $total_pages = (isset($template_data->numPages))? $template_data->numPages: 0;
        $templates = (isset($template_data->templates))? $template_data->templates: [];

        $selected_page = self::viddyozeFilterValues(sanitize_text_field($_GET['page']), [self::$pages['templates']]);
        $selected_category = self::viddyozeSanitizeInt(sanitize_text_field($_GET['viddyoze_category']));
        $selected_ratio = self::viddyozeFilterValues(sanitize_text_field($_GET['viddyoze_ratio']), $filter_values['ratios']);
        $selected_duration = self::viddyozeFilterValues(sanitize_text_field($_GET['viddyoze_duration']), $filter_values['durations']);
        $selected_nb_image = self::viddyozeFilterValues(sanitize_text_field($_GET['viddyoze_nb_image']), $filter_values['images']);
        $selected_search = sanitize_text_field($_GET['viddyoze_search']);
        $selected_customization = self::viddyozeSanitizeArray($_GET['viddyoze_customizations'], $filter_values['customisations']);
        $selected_library = self::viddyozeSanitizeInt(sanitize_text_field($_GET['viddyoze_library']), 0);

        $args = array(
            'page' => $selected_page,
            'viddyoze_category' => $selected_category,
            'viddyoze_ratio' => $selected_ratio,
            'viddyoze_duration' => $selected_duration,
            'viddyoze_nb_image' => $selected_nb_image,
            'viddyoze_search' => $selected_search,
            'viddyoze_customizations' => $selected_customization,
            'viddyoze_library' => $selected_library,
        );
        $current_url = admin_url('admin.php' . add_query_arg(array($args), $wp->request));
        ?>
            <div class="wrap">
                <div class="font-sans">
                    <div class="container mx-auto">
                        <div class="py-8">
                            <div class="mb-10">
                                <h2 class="text-2xl font-semibold leading-tight">Templates</h2>
                            </div>
                            <div class="shadow rounded-lg overflow-hidden">
                                <form class="inline-block bg-white border-b min-w-full" method="GET" action="">
                                    <input type="hidden" name="page" value="<?php echo esc_attr($selected_page); ?>" />
                                    <div class="my-2">
                                        <div class="flex sm:flex-row flex-col mb-1 sm:mb-0 justify-center">
                                            <div class="relative m-2">
                                                <select name="viddyoze_category" id="viddyoze_category"
                                                    class="appearance-none h-full rounded-r border-t border-r border-b block appearance-none w-full bg-white border-gray-400 text-gray-700 py-2 px-4 pr-8 leading-tight focus:outline-none focus:border-l focus:border-r focus:bg-white focus:border-gray-500">
                                                    <option value="">All Templates</option>
                                                    <?php foreach ($categories as $category) { $selected = ( $selected_category == $category->id )? "selected": ""; ?>
                                                    <option <?php echo $selected ?> value="<?php echo $category->id ?>"><?php echo $category->name ?></option>                                                
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="relative m-2">
                                                <select name="viddyoze_ratio" id="viddyoze_ratio"
                                                    class="appearance-none h-full rounded-r border-t border-r border-b block appearance-none w-full bg-white border-gray-400 text-gray-700 py-2 px-4 pr-8 leading-tight focus:outline-none focus:border-l focus:border-r focus:bg-white focus:border-gray-500">
                                                    <?php foreach ($ratios as $ratio) { $selected = ( $selected_ratio == $ratio->code )? "selected": ""; ?>
                                                    <option <?php echo $selected ?> value="<?php echo $ratio->code ?>"><?php echo str_replace("All", "All Ratios", $ratio->name) ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="relative m-2">
                                                <select name="viddyoze_duration" id="viddyoze_duration"
                                                    class="appearance-none h-full rounded-r border-t border-r border-b block appearance-none w-full bg-white border-gray-400 text-gray-700 py-2 px-4 pr-8 leading-tight focus:outline-none focus:border-l focus:border-r focus:bg-white focus:border-gray-500">
                                                    <?php foreach ($durations as $duration) { $selected = ( $selected_duration == $duration->code )? "selected": ""; ?>
                                                    <option <?php echo $selected ?> value="<?php echo $duration->code ?>"><?php echo str_replace("All", "All Durations", $duration->name) ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="relative m-2">
                                                <select name="viddyoze_nb_image" id="viddyoze_nb_image"
                                                    class="appearance-none h-full rounded-r border-t border-r border-b block appearance-none w-full bg-white border-gray-400 text-gray-700 py-2 px-4 pr-8 leading-tight focus:outline-none focus:border-l focus:border-r focus:bg-white focus:border-gray-500">
                                                    <option value="">Numbers of Images/Logos</option>
                                                    <?php foreach ($nb_images as $nb_image) { $selected = ( $selected_nb_image == $nb_image->code )? "selected": ""; ?>
                                                    <option <?php echo $selected ?> value="<?php echo $nb_image->code ?>"><?php echo $nb_image->name ?></option>                                                
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="relative m-2">
                                                <span class="h-full absolute inset-y-0 left-0 flex items-center pl-2">
                                                    <svg viewBox="0 0 24 24" class="h-4 w-4 fill-current text-gray-500">
                                                        <path
                                                            d="M10 4a6 6 0 100 12 6 6 0 000-12zm-8 6a8 8 0 1114.32 4.906l5.387 5.387a1 1 0 01-1.414 1.414l-5.387-5.387A8 8 0 012 10z">
                                                        </path>
                                                    </svg>
                                                </span>
                                                <input placeholder="Search" name="viddyoze_search" id="viddyoze_search" value="<?php echo esc_attr($selected_search); ?>"
                                                    class="appearance-none rounded-r rounded-l border border-gray-400 border-b block pl-8 pr-6 py-2 w-full bg-white text-sm placeholder-gray-400 text-gray-700 focus:bg-white focus:placeholder-gray-600 focus:text-gray-700 focus:outline-none" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="my-2">
                                        <div class="flex sm:flex-row flex-col mb-1 sm:mb-0 justify-center">
                                            <div class="relative m-2">
                                                <div class="flex sm:flex-row flex-col">
                                                    <?php
                                                    foreach ($customizations as $customization) {
                                                        $checked = ( in_array($customization->code, $selected_customization) )? "checked": "";
                                                        ?>
                                                        <label class="items-center my-2 mr-8">
                                                            <input type="checkbox" value="<?php echo $customization->code ?>" <?php echo $checked ?> name="viddyoze_customizations[]" class="form-checkbox h-5 w-5"><span class="ml-2 text-gray-700"><?php echo $customization->name ?></span>
                                                        </label>
                                                        <?php
                                                    }
                                                    ?>
                                                    <?php if (self::canToggleTemplates()) { ?>
                                                        <label class="items-center my-2 mr-8">
                                                            <input type="checkbox" value="1" <?php echo $selected_library ? "checked" : "" ?> name="viddyoze_library" class="form-checkbox h-5 w-5"><span class="ml-2 text-gray-700">View All</span>
                                                        </label>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <div class="relative m-2">
                                                <div class="flex sm:flex-row flex-col">
                                                    <button 
                                                    class="button-primary" type="submit" name="viddyoze_apply" value="Apply">Apply</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <div class="inline-block min-w-full">
                                    <?php
                                    if (count($templates) === 0) {
                                        ?>
                                        <div class="p-4">No templates found</div>
                                        <?php
                                    }
                                    ?>
                                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 mb-10">
                                        <?php foreach ($templates as $template) { ?>
                                            <a href="<?php echo admin_url('admin.php' . add_query_arg(array(
                                                'page'=> self::$pages['template'],
                                                't' => $template->id
                                                ), $wp->request)); ?>" class="bg-white shadow-md rounded-md overflow-hidden my-6 mx-3 video">
                                                <video class="thevideo bg-cover bg-center h-56 min-w-full" style="background-image: url(<?php echo $template->previewImageUrl ?>)" loop preload="none" muted>
                                                <source src="<?php echo $template->mp4PreviewUrl ?>" type="video/mp4">
                                                <source src="<?php echo $template->webmPreviewUrl ?>" type="video/webm">
                                                </video>
                                                <div class="py-2 px-4">
                                                    <p class="uppercase tracking-wide text-sm font-bold text-gray-700"><?php echo $template->name ?></p>
                                                </div>
                                                <div class="py-2 px-4 text-right">
                                                    <span class="button-primary">CUSTOMIZE &rightarrow;</span>
                                                </div>
                                            </a>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="inline-block min-w-full">
                                    <div class="bg-white px-4 py-3 flex items-center justify-center border-t border-gray-200 sm:px-6">

                                        <div class="flex justify-center">
                                            <div>
                                            <nav class="relative z-0 inline-flex shadow-sm" aria-label="Pagination">
                                                <?php echo self::pagination($total_pages, $current_url."&"); ?>

                                                <a href="<?php menu_page_url(self::$pages['welcome']) ?>" class="md:inline-flex relative items-center px-4 py-2 border border-gray-300 bg-blue-200 ml-2 text-sm font-medium text-gray-700 hover:bg-gray-50">HOW TO CREATE YOUR RENDER</a>
                                            </nav>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php
	}

    /**
     * viddyozeSupportPageContent
     * support page
     */
    public static function viddyozeSupportPageContent() {
        ?>
        <div class="wrap">
            <h1>Support</h1>

            <h2>Do You Need Help?</h2>

            <p>We're here to help, provide support and answer any questions you may have.</p>

            <p>There are a few ways you can get in touch with us fast.</p>

            <ol>
                <li><a target="_blank" href="http://app.viddyoze.com/">Log in to your existing Viddyoze account</a> and use the help icon at the bottom of the screen to chat with us live.</li>
                <li>Send a quick email to <a target="_blank" href="mailto:support@viddyoze.com">support@viddyoze.com</a> and our team will reply in no time!</li>
            </ol>

            <h2>What About Getting Help From Our Community?</h2>

            <p>Why not join our Facebook group with a community of over 60,000 business owners, entrepreneurs and video creators that can help you get the most out of your Viddyoze account.</p>

            <a href="https://www.facebook.com/groups/viddyoze/permalink/1361896450812713">JOIN OUR GROUP HERE</a>
        </div>
        <?php
    }

	/**
	 * viddyozeVideosPageContent
	 * create form admin page
	 */
	public static function viddyozeVideosPageContent() {
        global $wp;

        $videos_data = self::getVideos();
        $filters = self::getFilters();
        $filter_values = self::getFilterValues();

        $ratios = (isset($filters->ratios) && $filters->ratios)? $filters->ratios: [];

        $total_pages = (isset($videos_data->numPages))? $videos_data->numPages: 0;
        $videos = (isset($videos_data->renders))? $videos_data->renders: [];

        $selected_page = self::viddyozeFilterValues(sanitize_text_field($_GET['page']), [self::$pages['videos']]);
        $selected_ratio = self::viddyozeFilterValues(sanitize_text_field($_GET['viddyoze_ratio']), $filter_values['ratios']);
        $selected_search = sanitize_text_field($_GET['viddyoze_search']);

        $args = array(
            'page' => $selected_page,
            'viddyoze_ratio' => $selected_ratio,
            'viddyoze_search' => $selected_search,
        );
        $current_url = admin_url('admin.php' . add_query_arg(array($args), $wp->request));
        ?>
        <div class="wrap">
            <div class="font-sans">
                <div class="container mx-auto">
                    <div class="py-8">
                        <div class="mb-10">
                            <h2 class="text-2xl font-semibold leading-tight">My Videos</h2>
                        </div>
                        <div class="shadow rounded-lg overflow-hidden">
                            <form class="inline-block bg-white border-b min-w-full" method="GET" action="">
                                <input type="hidden" name="page" value="<?php echo $selected_page; ?>" />
                                <div class="my-2">
                                    <div class="flex sm:flex-row flex-col mb-1 sm:mb-0 justify-center">
                                        <div class="relative m-2">
                                            <select name="viddyoze_ratio" id="viddyoze_ratio"
                                                    class="appearance-none rounded-r border-t border-r border-b block appearance-none w-full bg-white border-gray-400 text-gray-700 py-2 px-4 pr-8 leading-tight focus:outline-none focus:border-l focus:border-r focus:bg-white focus:border-gray-500">
                                                <?php foreach ($ratios as $ratio) { $selected = ( $selected_ratio == $ratio->code )? "selected": ""; ?>
                                                    <option <?php echo $selected ?> value="<?php echo $ratio->code ?>"><?php echo str_replace("All", "Ratios", $ratio->name) ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="relative m-2">
                                                <span class="absolute inset-y-0 left-0 flex items-center pl-2">
                                                    <svg viewBox="0 0 24 24" class="h-4 w-4 fill-current text-gray-500">
                                                        <path
                                                                d="M10 4a6 6 0 100 12 6 6 0 000-12zm-8 6a8 8 0 1114.32 4.906l5.387 5.387a1 1 0 01-1.414 1.414l-5.387-5.387A8 8 0 012 10z">
                                                        </path>
                                                    </svg>
                                                </span>
                                            <input placeholder="Search" name="viddyoze_search" id="viddyoze_search" value="<?php echo $selected_search; ?>"
                                                   class="appearance-none rounded-r rounded-l border border-gray-400 border-b block pl-8 pr-6 py-2 w-full bg-white text-sm placeholder-gray-400 text-gray-700 focus:bg-white focus:placeholder-gray-600 focus:text-gray-700 focus:outline-none" />
                                        </div>
                                        <div class="relative m-2">
                                            <div class="flex sm:flex-row flex-col">
                                                <button
                                                        class="button-primary" type="submit" name="viddyoze_apply" value="Apply">Apply</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="inline-block min-w-full">
                                <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 mb-10 min-h-screen">
                                    <?php
                                    foreach ($videos as $video) {
                                        $embed_url = null;
                                        ?>
                                        <div class="bg-white shadow-md rounded-md my-6 mx-3">
                                            <div class="bg-cover bg-center h-56 min-w-full" style="background-image: url(<?php echo $video->previewImageUrl ?>)" >
                                            </div>
                                            <div class="p-4">
                                                <p class="uppercase tracking-wide text-sm font-bold text-gray-700"><?php echo $video->name ?></p>
                                                <?php if ($video->status == "finished") { ?>
                                                    <div class="card-content" data-id="<?php echo $video->id ?>">
                                                        <p class="text-gray-400 py-3">Rendered <?php echo self::time_elapsed_string($video->processing_finished_at); ?></p>
                                                        <div class="dropdown inline-block relative float-left my-10">
                                                            <button class="text-xs font-bold uppercase px-5 py-3 shadow-lg rounded-full mx-auto leading-normal text-white bg-blue-600 inline-flex items-center">
                                                                <span class="mr-1">Download</span>
                                                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/> </svg>
                                                            </button>
                                                            <div class="dropdown-menu absolute hidden text-black-700 pt-1 shadow-lg w-full bg-white rounded">
                                                                <?php
                                                                $video_types = array();
                                                                foreach ($video->videoUrls as $videoUrl) {
                                                                    $video_types[] = $videoUrl->type;
                                                                    if ($videoUrl->type === 'MP4') {
                                                                        $embed_url = $videoUrl->url;
                                                                    }
                                                                    if (!in_array($videoUrl->type, array('MOV', 'MP4')) && (self::isLicenceOnly() || self::isFreeMember())) {
                                                                        continue;
                                                                    }
                                                                    ?>
                                                                    <div class="py-2 px-2 inline-block w-full whitespace-no-wrap">
                                                                        <a href="<?php echo $videoUrl->url ?>" class="block"><?php echo $videoUrl->type; ?></a>
                                                                    </div>
                                                                    <?php
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                        <a href="<?php echo admin_url('admin.php' . add_query_arg(array(
                                                                'page'=> self::$pages['template'],
                                                                't' => $video->templateId,
                                                                'c' => $video->customizationId
                                                            ), $wp->request)); ?>" class=" float-left inline-block text-xs font-bold uppercase px-5 py-3 shadow-lg rounded-full mx-auto my-10 block leading-normal text-black ml-2">Edit</a>
                                                        <?php if ($embed_url) { ?>
                                                            <a href="#" data-embed="<?php echo $embed_url ?>" class=" float-left inline-block text-xs font-bold uppercase px-5 py-3 shadow-lg rounded-full bg-green-600 my-10 block leading-normal text-white ml-2 open-my-dialog2 copy-embed">Embed</a>
                                                        <?php } ?>
                                                        <a href="#" data-id="<?php echo $video->id ?>" class=" float-right inline-block text-xs font-bold uppercase px-5 py-3 shadow-lg rounded-full mx-auto my-10 block leading-normal text-white bg-red-600 open-my-dialog delete-render">Delete</a>
                                                    </div>
                                                <?php } elseif ($video->status == "rendering") { ?>
                                                    <div class="card-content">
                                                        <span class="text-gray-400 should-get-percentage" data-id="<?php echo $video->id ?>">Rendering <?php echo (int) $video->percentageComplete ?>%</span>
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="card-content">
                                                        <span class="text-gray-400 should-get-percentage" data-id="<?php echo $video->id ?>">Queuing...</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="inline-block min-w-full">
                                <div class="bg-white px-4 py-3 flex items-center justify-center border-t border-gray-200 sm:px-6">

                                    <div class="flex justify-center">
                                        <div>
                                            <nav class="relative z-0 inline-flex shadow-sm -space-x-px" aria-label="Pagination">
                                                <?php echo self::pagination($total_pages, $current_url."&"); ?>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- The modal / dialog box, hidden somewhere near the footer -->
        <div id="my-dialog" class="hidden w-80">
            <h3>Delete video?</h3>
            <p>This action cannot be undone</p>
            <form  action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
                <input type="hidden" name="viddyoze_renderid" id="viddyoze_renderid" value="0">
                <input type="hidden" name="action" value="viddyoze_render_delete">
                <span class="float-left inline-block text-xs font-bold uppercase px-5 py-3 m-6 shadow-lg rounded-full block leading-normal text-white bg-blue-600 close-ui-modal">Cancel</span>
                <button type="submit" class="float-right inline-block text-xs font-bold uppercase px-5 py-3 m-6 shadow-lg rounded-full block leading-normal text-white bg-red-600">Delete video</button>
            </form>
        </div>

        <!-- The modal / dialog box, hidden somewhere near the footer -->
        <div id="my-dialog2" class="hidden w-80">
            <h3>Embed this video</h3>
            <p>Copy the code below and paste it in your page</p>
            <p>
                <input type="checkbox" id="viddyoze_embed_autoplay_toggle" checked /> Autoplay
            </p>
            <code class="my-5 block max-w-md overflow-x-scroll">
                &lt;video width="560" height="315"<span id="viddyoze_embed_autoplay"> autoplay</span> controls&gt;
                &lt;source src="<span class="viddyoze_embed"></span>" type="video/mp4"&gt;
                &lt;/video&gt;
            </code>
            <p>Or embed the following URL</p>
            <code class="my-5 block max-w-md overflow-x-scroll">
                <span class="viddyoze_embed"></span>
            </code>
        </div>
        <?php
    }
    
    /**
	 * viddyozeOptionsPageContent
	 * create form admin page
	 */
	public static function viddyozeTemplateSingleContent() {
	    $template = self::getTemplate();
        $customization = self::getCustomization();
        if (!$template) {
            echo "Wordpress can not call the Viddyoze API !!"; exit;
        }

        $can_render = self::checkCanBeRendered($template->id);

        if (!$can_render->can_render) {
        ?>
        <div class="notice notice-warning">
            <p><?php echo $can_render->message ?? 'You\'re currently unable to render this template'; ?></p>
        </div>
        <?php
        }
        ?>
        <div class="wrap viddyoze_template_generator">
            <div class="font-sans">
                <div class="container mx-auto">
                    <div class="py-8">
                        <div class="mb-10">
                            <h2 class="text-2xl font-semibold leading-tight">#<?php echo $template->id ?> : <?php echo $template->name; ?></h2>
                        </div>
                        <div class="overflow-hidden">
                            <div class="inline-block min-w-full">
                                <div class="grid sm:grid-cols-1 lg:grid-cols-2 xl:grid-cols-2 mb-10 gap-4">
                                    <div class="">
                                        <div class="flex flex-wrap" id="tabs-id">
                                            <div class="w-full">
                                                <ul class="flex mb-0 list-none flex-wrap pb-4 flex-row">
                                                    <?php 
                                                        $i=0;
                                                        foreach ($template->assets as $key => $asset) {
                                                            if ($key == "fontGroups") continue;
                                                            if (count($asset) == 0) continue;
                                                            $classes = "text-xs font-bold uppercase px-5 py-3 shadow-lg rounded block leading-normal text-blue-600 bg-white";
                                                            if ($i == 0) {
                                                                $classes = "text-xs font-bold uppercase px-5 py-3 shadow-lg rounded block leading-normal text-white bg-blue-600";
                                                            }
                                                        $i++;
                                                    ?>
                                                    <li class="-mb-px mr-2 last:mr-0 flex-auto text-center">
                                                        <a class="<?php echo $classes; ?>" onclick="changeAtiveTab(event,'tab-<?php echo $key; ?>')">
                                                        <i class="fas fa-space-shuttle text-base mr-1"></i>  <?php echo $key; ?>
                                                        </a>
                                                    </li>
                                                    <?php
                                                        }
                                                    ?>
                                                </ul>
                                                <div class="relative flex flex-col min-w-0 break-words bg-white w-full mb-6 shadow rounded-lg p-6 bg-white">
                                                    <div class="px-4 py-5 flex-auto">
                                                        <form id="viddyoze_form" class="tab-content tab-space" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" enctype="multipart/form-data">
                                                        <input type="hidden" value="<?php echo $template->id; ?>" name="template_id">
                                                        <input type="hidden" name="action" value="viddyoze_render">
                                                        <input type="hidden" name="templateCustomisationId" id="templateCustomisationId" value="">
                                                            <?php 
                                                                $i=0;
                                                                foreach ($template->assets as $key => $asset) {
                                                                    if ($key == "fontGroups") continue;
                                                                    if (count($asset) == 0) continue;
                                                                    $visibility = "hidden";
                                                                    if ($i == 0) {
                                                                        $visibility = "block";
                                                                    }
                                                                $i++;
                                                            ?>
                                                            <div class="<?php echo $visibility; ?>" id="tab-<?php echo $key; ?>">
                                                                <?php
                                                                    switch ($key) {
                                                                        case 'images':
                                                                            self::renderImagesForm($asset, $customization);
                                                                            break;
                                                                        case 'texts':
                                                                            self::renderTextsForm($asset, $template->assets->fontGroups, $customization);
                                                                            break;
                                                                        case 'colors':
                                                                            self::renderColorsForm($asset, $customization);
                                                                            break;
                                                                        case 'audio':
                                                                            $disabled = "";
                                                                            self::renderAudioForm($asset, $customization);
                                                                            ?>
                                                                            <div class="flex items-center justify-center py-4">
                                                                            <?php
                                                                            if (count($template->assets->images) > 0) { 
                                                                                ?>
                                                                                <a href="javascript:void(0)" class="text-xs font-bold uppercase px-5 py-3 shadow-lg rounded-full mx-2 my-10 block leading-normal text-white bg-blue-200 hidden" id="viddyoze_submit_btn" disabled>Create</a>
                                                                                <button type="submit" class="text-xs font-bold uppercase px-5 py-3 shadow-lg rounded-full mx-2 my-10 block leading-normal text-white bg-blue-200" id="viddyoze_preview_btn" disabled>Preview</button>
                                                                                <?php
                                                                            }
                                                                            else {
                                                                                ?>
                                                                                <a href="javascript:void(0)" class="text-xs font-bold uppercase px-5 py-3 shadow-lg rounded-full mx-2 my-10 block leading-normal text-white bg-blue-600 hidden" id="viddyoze_submit_btn"<?php echo !$can_render ? ' disabled' : ''; ?>>Create</a>
                                                                                <button type="submit" class="text-xs font-bold uppercase px-5 py-3 shadow-lg rounded-full mx-2 my-10 block leading-normal text-white bg-blue-600" id="viddyoze_preview_btn">Preview</button>
                                                                                <?php
                                                                            }
                                                                            ?>
                                                                              <a href="javascript:history.back();" class="text-xs font-bold uppercase px-5 py-3 shadow-lg rounded-full mx-2 my-10 block leading-normal text-white bg-black" id="viddyoze_cancel_btn">Cancel</a>
                                                                            </div>
                                                                            <?php
                                                                            break;
                                                                        
                                                                        default:
                                                                            # code...
                                                                            break;
                                                                    }
                                                                ?>
                                                            </div>
                                                            <?php
                                                                }
                                                            ?>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="shadow rounded-lg p-6 bg-white viddyoze-player">
                                        <video class="bg-cover bg-center min-w-full h-96" controls autoplay loop muted>
                                            <source src="<?php echo $template->previews->mp4->big ?>" type="video/mp4">
                                            <source src="<?php echo $template->previews->webm->medium ?>" type="video/webm">
                                        </video>
                                    </div>                                    
                                    <div class="shadow rounded-lg p-6 bg-white viddyoze-preview hidden">
                                        <div class="text-center">Hang Tight! Creating a preview can take a few moments.</div>
                                        <div class="slider-for">
                                            <div class="slide slide1"><img src="<?php echo VIDDYOZE_ADMIN_URL .'images/load_canvas.gif'; ?>" alt=""></div>
                                            <div class="slide slide2"><img src="<?php echo VIDDYOZE_ADMIN_URL .'images/load_canvas.gif'; ?>" alt=""></div>
                                            <div class="slide slide3"><img src="<?php echo VIDDYOZE_ADMIN_URL .'images/load_canvas.gif'; ?>" alt=""></div>
                                            <div class="slide slide4"><img src="<?php echo VIDDYOZE_ADMIN_URL .'images/load_canvas.gif'; ?>" alt=""></div>
                                        </div>
                                        <div class="slider-nav" data-loading-image="<?php echo VIDDYOZE_ADMIN_URL .'images/load_canvas.gif'; ?>">
                                            <div class="slide slide1 p-2"><img src="<?php echo VIDDYOZE_ADMIN_URL .'images/load_canvas.gif'; ?>" alt=""></div>
                                            <div class="slide slide2 p-2"><img src="<?php echo VIDDYOZE_ADMIN_URL .'images/load_canvas.gif'; ?>" alt=""></div>
                                            <div class="slide slide3 p-2"><img src="<?php echo VIDDYOZE_ADMIN_URL .'images/load_canvas.gif'; ?>" alt=""></div>
                                            <div class="slide slide4 p-2"><img src="<?php echo VIDDYOZE_ADMIN_URL .'images/load_canvas.gif'; ?>" alt=""></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php 		
    }

    /**
     * @param mixed $values
     * @param array $allowedValues
     * @return array|int|string
     */
    private static function viddyozeFilterValues($values, $allowedValues) {
        if (!empty($allowedValues)) {
            if (is_array($values)) {
                return array_filter($values, function ($value) use ($allowedValues) {
                    return in_array($value, $allowedValues);
                });
            } else if (is_string($values) || is_numeric($values)) {
                return in_array($values, $allowedValues) ? $values : null;
            }
        }
        return $values;
    }

    /**
     * @param $value
     * @param $allowedValues
     * @return bool
     */
    private static function viddyozeCheckIsAllowedValue($value, $allowedValues) {
        if (!empty($allowedValues)) {
            return in_array($value, $allowedValues);
        }
        return true;
    }

    /**
     * @param mixed $raw
     * @param array $allowedValues
     * @return bool
     */
    public static function viddyozeValidateNumber($raw, $allowedValues = []) {
        if (is_numeric($raw)) {
            return self::viddyozeCheckIsAllowedValue((int)$raw, $allowedValues)
                || self::viddyozeCheckIsAllowedValue((float)$raw, $allowedValues)
                || self::viddyozeCheckIsAllowedValue($raw, $allowedValues);
        }
        return false;
    }

    /**
     * @param mixed $raw
     * @param int $defaultValue
     * @param array $allowedValues
     * @return int
     */
    public static function viddyozeSanitizeInt($raw, $defaultValue = 0, $allowedValues = []) {
        if (self::viddyozeValidateNumber($raw, $allowedValues)) {
            return (int)$raw;
        }
        return $defaultValue;
    }

    /**
     * @param mixed $raw
     * @param array $allowedValues
     * @param bool $regex
     * @return array
     */
    public static function viddyozeSanitizeArray($raw, $allowedValues = [], $regex = false) {
        if (is_array($raw)) {
            return array_filter($raw, function ($value) use ($allowedValues, $regex) {
                if (!empty($allowedValues)) {
                    if ($regex) {
                        $matches = array_filter($allowedValues, function ($expression) use ($value) {
                            return (bool)preg_match($expression, $value);
                        });
                        return !empty($matches);
                    }
                    return in_array($value, $allowedValues);
                }
                return !is_null($value) && $value !== '';
            });
        }
        return array();
    }
}