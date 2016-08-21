<?php
/*  
Plugin Name: WP Trello
Plugin URI: http://www.polevaultweb.co.uk/plugins/wp-trello/  
Description: A plugin to display data from Trello in your WordPress site.
Author: polevaultweb 
Version: 1.0.6
Author URI: http://www.polevaultweb.com/

Copyright 2013  polevaultweb  (email : info@polevaultweb.com)

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
if ( !session_id() ) session_start();
require_once ( plugin_dir_path( __FILE__ ) .'includes/wpt-widget.php' );

new wp_trello();
class wp_trello {

    private $plugin_path;
    private $plugin_url;
	private $plugin_version;
	private $plugin_l10n;
	
	private $wpsf;
	
	private $api_base = 'https://api.trello.com/1/';
	private $request_token_url;
	private $authorize_url;
	private $access_token_url;
		
	private $app_name = 'WP Trello';
	private $callback_url;
 	
 	private $consumer;
 	private $token;
 	private $consumer_key = '21b4a1e4f755637319c979849147076e';
 	private $consumer_secret = '7d5ab8655e15582041cab97459d1c6d9fca51e690247df4fb5214f2691ba786c';
 	
 	public $timeout = 30;
	public $connecttimeout = 30; 
	public $ssl_verifypeer = FALSE;
	public $format;
	public $decode_json = TRUE;
	public $http_info;
	public $useragent = 'Provider Oauth';

    function __construct() {	

        $this->plugin_path = plugin_dir_path( __FILE__ );
        $this->plugin_url = plugin_dir_url( __FILE__ );
		$this->plugin_version = '1.0.6';
		$this->plugin_l10n = 'wp-trello';
		
		$this->request_token_url = $this->api_base .'OAuthGetRequestToken';
		$this->authorize_url = $this->api_base .'OAuthAuthorizeToken';
		$this->access_token_url = $this->api_base .'OAuthGetAccessToken';
		
		$this->callback_url = get_admin_url() . 'options-general.php?page=wp-trello';
		
		// Set up l10n
        load_plugin_textdomain( $this->plugin_l10n, false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
        
        // Hooks and Filters
        add_action(	'admin_enqueue_scripts', array( $this, 'add_admin_scripts' ));
        add_action( 'admin_menu', array( $this, 'add_settings_menu' ));
        add_action( 'admin_init', array( $this, 'trello_connect' ));
        add_action( 'wp_ajax_wpt_get_objects', array($this,'wpt_get_objects'));
        add_action( 'wp_ajax_wpt_disconnect', array($this, 'wpt_disconnect'));
        add_filter( 'plugin_action_links', array($this, 'plugin_settings_link'), 10, 2 );
        add_action(	'wp_enqueue_scripts', array($this, 'custom_css'));

        add_shortcode('wp-trello', array($this, 'trello_data') );
        if ($this->is_connected()) {
        	add_action( 'widgets_init', create_function( '', 'register_widget( "wpt_widget" );' ) );
        }
                
        // oAuth
        require_once( $this->plugin_path .'includes/trello.php' );
       	   
        // Settings
		require_once( $this->plugin_path .'includes/wp-settings-framework.php' );
        $this->wpsf = new wpt_WordPressSettingsFramework( $this->plugin_path .'includes/wpt-settings.php' );
        add_filter( $this->wpsf->get_option_group() .'_settings_validate', array(&$this, 'validate_settings') );
        $this->settings = wpsf_get_settings( $this->plugin_path .'includes/wpt-settings.php' );
		
	}
	
	function add_admin_scripts() {
		if (isset($_GET['page']) && $_GET['page'] == $this->plugin_l10n){	
			// js
            wp_register_script( 'wpt-admin-js', plugins_url('assets/js/wpt-admin.js' , __FILE__ ), array('jquery'), $this->plugin_version );
            wp_enqueue_script( 'wpt-admin-js' );
            wp_localize_script( 'wpt-admin-js', 'wp_trello', array(  'nonce' => wp_create_nonce('wp_trello') ));

			// css
			wp_register_style( 'wpt-admin-css', plugins_url('assets/css/wpt-admin.css' , __FILE__ ), array(), $this->plugin_version);
			wp_enqueue_style('wpt-admin-css');
		}
	}
	
	function custom_css() {
		$custom_css = $this->default_val($this->settings, 'wptsettings_general_output-css', '');
		if ($custom_css != '') {
			$output = "<style type=\"text/css\">\n" . $custom_css . "\n</style>\n";
			echo $output;
		}
	}
	
	function add_settings_menu() {
		add_options_page( __('WP Trello', $this->plugin_l10n), __('WP Trello', $this->plugin_l10n), 'manage_options', $this->plugin_l10n, array( $this, 'settings_page' ) );
	}
	
	function plugin_settings_link($links, $file) {  
		if ($file == plugin_basename(__FILE__)){
			$settings_link = '<a href="'. $this->callback_url .'">' . __('Settings', $this->plugin_l10n) . '</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;	
	}
	
	function settings_page() {
		if (!current_user_can('manage_options')) {
		    wp_die('You do not have sufficient permissions to access this page.');
		} 
		global $wptsf_settings;
		$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general'; 
		$connected = $this->is_connected();
		$trello = new trello_oauth();
		$source = 'trello';
		$callback = $this->callback_url;
		$url = $trello->get_authorise_url($callback, $source);
	?>
		<div class="wrap">
		  <div id="icon-options-general" class="icon32"></div>
		  <h2><?php _e('WP Trello Settings', $this->plugin_l10n) ?></h2>
		  <p>
		  <?php if(!$connected) { ?>
		  	<a class="button button-primary" href=<?php echo $url; ?>>Connect with Trello</a></p>
		  <?php } ?>
		  <h2 class="nav-tab-wrapper">
			  <?php foreach( $wptsf_settings as $tab ){ ?>
				<a href="?page=<?php echo $_GET['page']; ?>&tab=<?php echo $tab['section_id']; ?>" class="nav-tab<?php echo $active_tab == $tab['section_id'] ? ' nav-tab-active' : ''; ?>"><?php echo $tab['section_title']; ?></a>
				<?php } ?>
				<?php if($connected) { 
					$member = $this->get_connected_member();
					$fullname = ($member != '') ? $member->username : '';
				?>
					<p class="nav-tab"><strong><?php echo $fullname; ?></strong> <a id="wpt-disconnect" href="#" class="button">Disconnect</a></p>
				<?php } ?>
			  </h2>
		  <form action="options.php" method="post">
				<?php settings_fields( $this->wpsf->get_option_group() ); ?>
				<?php $this->do_settings_sections( $this->wpsf->get_option_group() ); ?>
				<p class="submit"><input type="submit" class="button-primary" value="<?php _e( 'Save Changes', $this->plugin_l10n ); ?>" /></p>
		  </form>
		</div>
		<?php
	}
	
	function do_settings_sections($page) {
        global $wp_settings_sections, $wp_settings_fields;
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general'; 
        if ( !isset($wp_settings_sections) || !isset($wp_settings_sections[$page]) )
            return;
        foreach ( (array) $wp_settings_sections[$page] as $section ) {
            echo '<div id="section-'. $section['id'] .'"class="wpt-section'. ($active_tab == $section['id'] ? ' wpt-section-active' : '') .'">';
            call_user_func($section['callback'], $section);
            if ( !isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section['id']]) )
                    continue;
            echo '<table class="form-table">';
            do_settings_fields($page, $section['id']);
            echo '</table>
            </div>';
        }
    }
    
    function validate_settings( $input ) { return $input; }
    
    function default_val( $options, $value, $default = '' ){
        if( !isset($options[$value]) ) return $default;
        else return $options[$value];
    }
 	
	function trello_connect() {
		if(isset($_GET['page']) && $_GET['page'] == 'wp-trello' && isset($_GET['oauth_token']) && isset($_GET['oauth_verifier'])) {
			$auth_token = $_SESSION['trello_oauth_token'];
			$auth_token_secret = $_SESSION['trello_oauth_token_secret'];			
			$callback = $this->callback_url;
			$request_code = $_GET['oauth_verifier'];
			
			$trello = new trello_oauth($auth_token, $auth_token_secret);
			$token = $trello->getAccessToken($request_code, $callback);
			$member = $trello->getMember();
			$trello_data['token'] = $token;
			$trello_data['member'] = $member;
			
			update_option('wptsettings_trello', $trello_data);
			header( 'Location: '. $this->callback_url );
		}
	}
	
	function is_connected() {		
		return (get_option('wptsettings_trello')) ? true : false;
	}
	
	function get_connected_member() {		
		$trello_data = get_option('wptsettings_trello');
		return isset($trello_data['member']) ? $trello_data['member'] : '';
	}
	
	function get_access_token() {		
		$trello_data = get_option('wptsettings_trello');
		return isset($trello_data['token']) ? $trello_data['token'] : '';
	}
	
	function trello_data($atts, $content = null) {
		extract(shortcode_atts(array(	'type' => 'cards',
										'id' => '',
										'link' => false,
										), $atts));
		
		if (!$this->is_connected()) return '';						
		return $this->trello_output($type, $id, $link);		
	}
	
	function trello_output($type, $id, $link) {
		$wp_trello = new wp_trello();
		
		$data = $wp_trello->get_data($type, $id);
		$parent = $wp_trello->get_parent($type, $id);
		$singular = substr($type, 0, -1);
		$target_blank = $wp_trello->default_val($wp_trello->settings, 'wptsettings_general_target-blank', '');
		$target = '';
		if($target_blank == 1) $target = ' target="_blank"';
		if (is_array($data)) {
			$html = '<ul class="wpt-'. $singular .'-wrapper">';
			foreach ($data as $item) {
				$html .= '<li class="wpt-'. $singular .'">';
				if ($link && strtolower($link) == 'yes') {
					$url = (isset($item->url)) ? $item->url : '#';
					$html .= '<a class="wpt-'. $singular .'-link" href="'. $url .'"'. $target .'>'. $item->name .'</a>';
				} else $html .= make_clickable($item->name);
				$html .= '</li>';		
			}
			$html .= '</ul>';
		} else {
			$html = '<div class="wpt-'. $singular .'-wrapper">';
			$html .= '<div class="wpt-'. $singular .'">';
			if ($link && strtolower($link) == 'yes') {
				$url = (isset($data->url)) ? $data->url : '#';
				$html .= '<a class="wpt-'. $singular .'-link" href="'. $url .'"'. $target .'>'. $data->name .'</a>';
			} else $html .= (isset($data->name)) ? make_clickable($data->name) : '';
			$html .= '</div>';
			$html .= '</div>';	
		}
		$link_love = $wp_trello->default_val($wp_trello->settings, 'wptsettings_general_link-love', '');
		if($link_love == 1) $html .= 'Trello data served by <a href="http://wordpress.org/extend/plugins/wp-trello/" target="_blank">WP Trello</a>';
		return $html;
	}
	
	function get_data($object, $id) {
		$trello_data = get_option('wptsettings_trello');
		$access_token = isset($trello_data['token']) ? $trello_data['token'] : '';
		$trello = new trello_oauth($access_token['oauth_token'], $access_token['oauth_token_secret']);
		$method = 'get'. ucfirst($object);
		$data = call_user_func(array($trello, $method), $id);
		return $data;		 
	}

	function get_dropdown_data($object, $id) {
		$trello_data = get_option('wptsettings_trello');
		$access_token = isset($trello_data['token']) ? $trello_data['token'] : '';
		$trello = new trello_oauth($access_token['oauth_token'], $access_token['oauth_token_secret']);
		$method = 'get'. ucfirst($object);
		$data = call_user_func(array($trello, $method), $id);
		$data = $trello->getDropdown($data, substr($object, 0, -1));
		return $data;		 
	}
	
	function get_parent($object, $id) {
	    $trello_objects = array( 	0 => 'organization',
									1 => 'board',
									2 => 'list',
									3 => 'card'
								);				
		$singular = substr($object, 0, -1);
		$child_object = array_search($singular, $trello_objects); 
		if ($child_object == 0) return;
		$parent_object = $trello_objects[$child_object - 1];
		$parent = wp_trello::get_data($parent_object, $id);
		return $parent;
	}
	
	function wpt_get_objects() {
		if ( !isset($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], 'wp_trello' ))
            return 0;
		if ( !isset($_POST['type']) || !isset($_POST['id']))
            return 0;
        $response['error'] = false;
        $response['message'] = '';
        $objects = $this->get_dropdown_data($_POST['type'], $_POST['id']); 
        $response['objects'] = $objects;
        $response['message'] = 'success';
        echo json_encode($response);
        die;
	}
	
	function wpt_disconnect() {
		if ( !isset($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], 'wp_trello' ))
            return 0;
		$response['error'] = false;
        $response['message'] = '';
        $response['redirect'] = '';
        $delete = delete_option('wptsettings_trello');
		$response['redirect'] = $this->callback_url; 
        $response['message'] = 'success';
        $response['error'] = $delete;
        echo json_encode($response);
        die;
	}
}
?>