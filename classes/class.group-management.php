<?php
/*
 * Createa a new custom posttype to store the group informations
 * 
 * */

class UgManagement{
	
	//custom posttype management
	const posttype = 'usergroup';
	
	
	
	static $registered_user = array();
	
	
	static function init(){
			
		//admin menu
		add_action('admin_menu', array(get_class(), 'admin_menu'));		
		
		
		
		register_activation_hook(USERGROUPMANAGMENT_FILE, array(get_class(), 'manage_db'));
		//register_deactivation_hook(USERGROUPMANAGMENT_FILE, array(get_class(), 'deactivated_plugin'));

		
		//for submissions
		add_action('init', array(get_class(), 'save_group_info'));
		
		//add_action('init', array(get_class(), 'test'));
		
		
		//prevent password reset
		add_filter('allow_password_reset', array(get_class(), 'prevent_password_reset'), 10, 2);
		
		//disalbe the password option from profile page
		add_filter('show_password_fields', array(get_class(), 'show_password_fields'), 10, 2);
		
		
		//authentication user by group password
		remove_filter('authenticate', 'wp_authenticate_username_password', 20);
		add_filter('authenticate', array(get_class(), 'wp_authenticate_username_password'), 1, 3);
		
		
		//filter registraion procedur with existing groups
		add_filter('registration_errors', array(get_class(), 'registration_errors'), 10, 3);
		
		
		//attahch some group info with user meta when registration
		add_action('user_register', array(get_class(), 'user_register'), 10, 1);
		
		
		//restrict registration page and password reset page
		add_action('login_init', array(get_class(), 'login_init'));
		
		//login page error message
		add_filter('login_message', array(get_class(), 'login_message'), 10, 1);	
		add_action('delete_user', array(get_class(), 'unsubscribe_the_user'), 10);
		
		
		//add_filter('login_url', array(get_class(), 'login_url'), 10, 2);

		
	//	add_action('init', array(get_class(), 'test'));
		
		
		//camel case
		
		
		//mulitisite verificatin
		add_filter('wpmu_validate_user_signup', array(get_class(), 'wpmu_validate_user_signup'), 10, 1);
		add_action('wpmu_activate_user', array(get_class(), 'set_default_user_meta'), 10, 3);
		add_action('activate_header', array(get_class(), 'replace_group_password'));
		add_action('remove_user_from_blog', array(get_class(), 'unsubscribe_the_user'));
		add_action('wpmu_delete_user', array(get_class(), 'unsubscribe_the_user'));
		
		//single installation
				
		
	}
	
	
	/*
	 * Interspire scheduler
	 * */
	static function interspire_scheduler(){
		include USERGROUPMANAGMENT_DIR . '/includes/parallel-scheduler.php';
	}
	
	
	
	static function test(){
		$Ugdb = new UgDbManagement();
		$status = $Ugdb->check_if_tables_installed();

		if(false === $status){
			var_dump("not exists");
		}
	}
		
	
	
	static function login_url($url, $redirect){
		
	}

	
	/*
	 * it will fire when a user is deleted from a blog, from the network (ms)
	 * it will fire also when a user is delted from single installation
	 * */
	static function unsubscribe_the_user($id){
		return InterspireScheduler::unsubscribe_user($id);
	}
	
	
	
	//return the interspire class object
	static function get_synchronizer(){
		if(!class_exists('InterSpireSync')){
			include USERGROUPMANAGMENT_DIR . '/classes/class.interspire.php';
		}
		
		$sync = new InterSpireSync(self::get_interspire_credentials());
		return $sync;
	}
	
	
	//get the interspire lists
	static function get_interspire_lists(){
		$sync = self::get_synchronizer();
		$lists = $sync->get_lists();
		return $lists;
	}
	
		
	// manages admin menu
	static function admin_menu(){
		add_menu_page(ucwords('user group management'), 'User Groups', 'manage_options', 'user-group-management', array(get_class(), 'menu_group_management'), '', 68);
		add_submenu_page('user-group-management', ucwords('new or edit a user group'), 'Add New', 'manage_options', 'addnew-user-group', array(get_class(), 'submenu_add_usergourp'));
		add_submenu_page('user-group-management', ucwords('inter sipre default options'), 'InterSpire', 'manage_options', 'interspire-default-options', array(get_class(), 'submenu_interspire'));
		add_submenu_page('user-group-management', ucwords('site default options'), 'Site Settings', 'manage_options', 'registration-default-options', array(get_class(), 'submenu_registration_options'));
		add_submenu_page('user-group-management', ucwords('default user bulk import'), 'CSV Import', 'manage_options', 'default-csv-user-import', array(get_class(), 'submenu_default_csv_import'));
		add_submenu_page('user-group-management', ucwords('Scheduler'), 'Scheduler', 'manage_options', 'scheduler', array(get_class(), 'interspire_scheduler'));
	}
	
	
	
	/*
	 * save the menu/submenu page
	 * */
	static function save_group_info(){
		if($_POST['page'] == 'addnew-user-group'){
			$info = self::_save();
			if(is_array($info)){
				$url = add_query_arg($info, admin_url('admin.php?page=addnew-user-group'));
				return self::do_redirect($url);
			}
		}
	}
	
	
	//menupage
	static function menu_group_management(){
		
		$UgList = self::get_list_table();
		
		if($UgList->current_action() == 'delete'){
			$group_ids = $_REQUEST['group_id'];
			
			if(!is_array($group_ids)){
				$group_ids = array($group_ids);
			}			
			
			$message = count($group_ids) . ' deleted';
			
			
			self::handle_actions($group_ids);
		}
		
		include USERGROUPMANAGMENT_DIR . '/includes/groups.php';
	}
	
	
	/*
	 * handle actions
	 * */
	static function handle_actions($group_ids){
		$Ugdb = new UgDbManagement();
		
		if(is_array($group_ids)){
			foreach($group_ids as $group_id){
				$role = $Ugdb->get_group_meta($group_id, 'role');
				remove_role($role);
				$Ugdb->delete_group($group_id);
			}
		}
		//var_dump($group_ids);
	}
	
	
	//get a list table
	static function get_list_table(){
		if(!class_exists('UgListTable')){
			include USERGROUPMANAGMENT_DIR . '/classes/list-table.php';
		}
		
		$UgList = new UgListTable();
		return $UgList;
	}
	
	
	//sub menu page to add or edit an user group
	static function submenu_add_usergourp(){
		$Ugdb = new UgDbManagement();
		
		if($_REQUEST['csv'] == 'csv'){
						
			include USERGROUPMANAGMENT_DIR . '/includes/csv-import-form.php';
		}
		else{
			include USERGROUPMANAGMENT_DIR . '/includes/add-neew-group.php';
		}
	}
	
	
	//function to hanel group info
	static function _save(){
		$Ugdb = new UgDbManagement();
		$group_id = 0;
				
		if(isset($_POST['group_id'])) $group_id = $_POST['group_id'];
		
		$info = array(
			'name' => trim($_POST['group_name']),
			'domain' => trim($_POST['group_domain']),
		);
		
		
		
		if(empty($info['name'])) return false;
		
		$cap = array('read' => true);
		
		if($group_id > 0){
			$existing_role = $_POST['group_role'];
			remove_role($existing_role);
			
			if(strlen($existing_role) > 0){
				$created_role = add_role($existing_role, $_POST['group_name'], $cap);
			}
			else{
				$role_name = strip_tags($_POST['group_name']);		
				$role_id = preg_replace('/[^A-Za-z0-9]/', '', $role_name);
				$role_id = strtolower($role_id);
				$created_role = add_role($role_id, $role_name, $cap);
			}
		}
		else{
			$role_name = strip_tags($_POST['group_name']);		
			$role_id = preg_replace('/[^A-Za-z0-9]/', '', $role_name);
			$role_id = strtolower($role_id);
			$created_role = add_role($role_id, $role_name, $cap);
		}

		if(!$created_role){
			return array(
				'message' => 2				
			);
		}		
				
		
		$group_id = $Ugdb->update_group($group_id, $info);

		if($group_id > 0){
			$Ugdb->update_group_meta($group_id, 'group_interspire_list', trim($_POST['group_interspire_list']));
			$Ugdb->update_group_meta($group_id, 'group_password', trim($_POST['group_password']));
			$Ugdb->update_group_meta($group_id, 'role', $created_role->name);
			
		}
		
		if($group_id > 0){
			return array(
				'group_id' => $group_id,
				'message' => 1
				
			);
		}
			
		
	}
	
	
	/*
	 * save the group name as a role
	 * */
	static function _add_role($info){
		$cap = array(
			'read' => true
		);
		
		if(strlen($info['role']) > 0){
			remove_role($info['role']);
			$created_role = add_role($info['role'], strip_tags($info['name']), $cap);
		}
		else{
		
			$role_name = strip_tags($info['name']);		
			$role_id = preg_replace('/[^A-Za-z0-9]/', '', $role_name);
			$role_id = strtolower($role_id);
			$created_role = add_role($role_id, $role_name, $cap);
		}
		
		
		return $created_role;
	}
	
	
	//activate the plguin and create tables
	static function manage_db(){
		
		self::activate_plugin();
			
		$Ugdb = new UgDbManagement();
		return $Ugdb->manage_db();
	}
	
	//activate the plugin and set the default options
	static function activate_plugin(){
		$options = array(
			'restrict-password-reset' => 1,
			'restrict-registration'  => 0,
			'default-interspire-list' => 0
		);
		
		return update_option('default_site_options', $options);
	}
	
		
	
	static function deactivated_plugin(){
		$Ugdb = new UgDbManagement();
		return $Ugdb->drop_tables();
	}
	
	
	//do a http redirect
	static function do_redirect($url){
		if(!function_exists('wp_redirect')){
			include ABSPATH . '/wp-includes/pluggable.php';
		}
		
		wp_redirect($url);
		die();
	}
	
	
	/*
	 * submenu for interspire
	 * */
	static function submenu_interspire(){
		include USERGROUPMANAGMENT_DIR . '/includes/interspire-credential-form.php';
	}
	
	
	/*submenu page for site default settings*/
	static function submenu_registration_options(){
		include USERGROUPMANAGMENT_DIR . '/includes/default-site-options.php';
	}
	
	
	/*
	 * submenu for default registration procedure
	 * */
	static function submenu_default_csv_import(){
		include USERGROUPMANAGMENT_DIR . '/includes/default-csv-import-form.php';
	}
	
	
	
	
	/*
	 * csv uploader object
	 * */
	static function _csv_uploader(){
		if(!class_exists('File_CSV_DataSource')){
			include USERGROUPMANAGMENT_DIR . '/classes/class.csv-parser.php';
		}
		
		$csv = new File_CSV_DataSource();
		return $csv;
	}
	
	
	// delete BOM from UTF-8 file
    function stripBOM($fname) {
        $res = fopen($fname, 'rb');
        if (false !== $res) {
            $bytes = fread($res, 3);
            if ($bytes == pack('CCC', 0xef, 0xbb, 0xbf)) {
                $this->log['notice'][] = 'Getting rid of byte order mark...';
                fclose($res);

                $contents = file_get_contents($fname);
                if (false === $contents) {
                    trigger_error('Failed to get file contents.', E_USER_WARNING);
                }
                $contents = substr($contents, 3);
                $success = file_put_contents($fname, $contents);
                if (false === $success) {
                    trigger_error('Failed to put file contents.', E_USER_WARNING);
                }
            } else {
                fclose($res);
            }
        } else {
            $this->log['error'][] = 'Failed to open file, aborting.';
        }
    }
	
    
    
    /*
     * create a new user if not exists
     * */
    static function create_user($info){
    	global $wpdb;
    	$Ugdb = new UgDbManagement();
    	
    	$group = $Ugdb->get_group($_POST['group_id']);
		$group_meta = $Ugdb->get_group_metas($group[ID]);
		
    	if(strlen($group[domain]) > 0){
    		if(self::is_matched($group['domain'], $info[0])){
    			$user = get_user_by( 'email', $info[0] );
    		    			
    			if($user){
    				return false;
    			}
    			else{
    				$user_id = wp_insert_user(array(
    					'user_login' => $info[0],
    					'first_name' => $info[1],
    					'user_nicename' => $info[2],
    					'nickname' => $info[2],
    					'user_email' => $info[0],
    					'display_name' =>$info[1],
    					'user_pass' => $group_meta['group_password'],
    					'role' => $group_meta['role']
    				));
    				
    				if($user_id){
    					update_user_meta($user_id, 'gm_group_id', $group['ID']);
    					if($group_meta['group_interspire_list'] > 0){
    						update_user_meta($user_id, 'interspire_list', $group_meta['group_interspire_list']);
    					}
    					
    					return true;
    				}
    			}
    		}
    	}
    	else{
    		
    			$user = get_user_by( 'email', $info[0] );
    			
    			if($user){
    				return false;	
    			}
    			else{
    				$user_id = wp_insert_user(array(
    					'user_login' => $info[0],
    					'first_name' => $info[1],
    					'user_nicename' => $info[2],
    					'nickname' => $info[2],
    					'user_email' => $info[0],
    					'display_name' =>$info[1],
    					'user_pass' => $group_meta['group_password'],
    					'role' => $group_meta['role']
    				));
    				
    				if($user_id){
    					update_user_meta($user_id, 'gm_group_id', $group['ID']);
    					
    					if($group_meta['group_interspire_list'] > 0){
    						update_user_meta($user_id, 'interspire_list', $group_meta['group_interspire_list']);
    					}
    					
    					return true;
    				}
    			}
    		}
    		
    	
    	
    	return false;
    	    	
    }
    
    
    
    /*
     * create user with default csv uploader
     * */
    static function create_default_user($info, $defaults){
    	
    	$user = get_user_by( 'email', $info[0] );
    		
    	if($user){
    		return false;
    	}
    	else{
    		$user_id = wp_insert_user(array(
    			'user_login' => $info[0],
    			'first_name' => $info[1],
    			'user_nicename' => $info[2],
    			'nickname' => $info[2],
    			'user_email' => $info[0],
    			'display_name' =>$info[1],
    			'user_pass' => wp_generate_password( 12, false),
    			'role' => 'subscriber'
    		));
    				
    		if($user_id){
    			
	    		if($defaults['default-interspire-list']){
	    			update_user_meta($user_id, 'interspire_list', $defaults['default-interspire-list']); 
	    		}
    			
    			return true;
    		}
    	}
    	
    	return false;
    }
      
    
    
    //if the domain is matched
    static function is_matched($domain, $email){
    	$em = explode('@', $email);

    	//var_dump($em);
    	//var_dump($domain);
    	
    	return ($em[count($em) - 1] == $domain) ? true : false;
    }
    
    
    //prevent password reset
    static function prevent_password_reset($allow, $user_id){
    	
    	//$default_options = self::get_site_default_options();
    	
    	//var_dump($allow); exit;
    	   	
		if(self::is_a_group_memeber($user_id)){
			$allow = false;
		}

		return $allow;
    }

    
    //prevent the password editing option
    static function show_password_fields($allow, $userdata){
    	
    	//skip for the admin
    	if(current_user_can('manage_options')) return $allow;

   		if(self::is_a_group_memeber($userdata->ID)){
			$allow = false;
		}
		
		return $allow;
    }
    
    
    /*
     * authentication users by group password
     * 
     * */
	function wp_authenticate_username_password($user, $username, $password) {
		if ( is_a($user, 'WP_User') ) { return $user; }
	
		if ( empty($username) || empty($password) ) {
			$error = new WP_Error();
	
			if ( empty($username) )
				$error->add('empty_username', __('<strong>ERROR</strong>: The username field is empty.'));
	
			if ( empty($password) )
				$error->add('empty_password', __('<strong>ERROR</strong>: The password field is empty.'));
	
			return $error;
		}
	
		$user = get_user_by('login', $username);
	
		if ( !$user )
			return new WP_Error('invalid_username', sprintf(__('<strong>ERROR</strong>: Invalid username. <a href="%s" title="Password Lost and Found">Lost your password</a>?'), wp_lostpassword_url()));
	
		if ( is_multisite() ) {
			// Is user marked as spam?
			if ( 1 == $user->spam)
				return new WP_Error('invalid_username', __('<strong>ERROR</strong>: Your account has been marked as a spammer.'));
	
			// Is a user's blog marked as spam?
			if ( !is_super_admin( $user->ID ) && isset($user->primary_blog) ) {
				$details = get_blog_details( $user->primary_blog );
				if ( is_object( $details ) && $details->spam == 1 )
					return new WP_Error('blog_suspended', __('Site Suspended.'));
			}
		}
		
		
		
		//user group management
		$group_id = self::is_a_group_memeber($user->ID);
		$Ugdb = new UgDbManagement();
		$group = $Ugdb->get_group($group_id);
		
		
		if($group){
			
			$group_password = $Ugdb->get_group_meta($group_id, 'group_password');
							
			if(strlen($group_password) > 1){
			
				if($group_password == $password){
					return $user;
				}
				else{
					return new WP_Error( 'incorrect_password', sprintf( __( '<strong>ERROR</strong>: The password you entered for the group name  <strong>%1$s</strong> is incorrect. Please contact with the site admin' ),
					$group['name'] ) );
				}
			}
		}
		
		//now checking if the user is from default group
		$default_value = get_user_meta($user->ID, 'default_group', true);
		
		if($default_value == 'y'){
			$default_options = self::get_site_default_options();
			
			if(strlen($default_options['default-group-password']) > 0){
				if($default_options['default-group-password'] == $password){
					return $user;
				}
			}
		}
		
		
		//filtering password to give the group password		
		$user = apply_filters('wp_authenticate_user', $user, $password);
		
		if ( is_wp_error($user) )
			return $user;
	
		if ( !wp_check_password($password, $user->user_pass, $user->ID) )
			return new WP_Error( 'incorrect_password', sprintf( __( '<strong>ERROR</strong>: The password you entered for the username <strong>%1$s</strong> is incorrect. <a href="%2$s" title="Password Lost and Found">Lost your password</a>?' ),
			$username, wp_lostpassword_url() ) );
	
		return $user;
	}
	
    
    
    //controlling registration procedure
    static function registration_errors($errors, $sanitized_user_login, $user_email){
    	
    //	var_dump($errors); exit;
    	
    	if(is_email($user_email)){
    		$em = explode('@', $user_email);
    		$domain = $em[count($em) - 1];
    		
    		$default_optons = self::get_site_default_options();
    		
    		$domains = $default_optons['default-group-domain'];
    		$domains = preg_replace('/[ ]/', '', $domains);
    		$domains = explode(',', $domains);
    		
    		if(in_array($domain, $domains)){
    			self::$registered_user['default'] = $default_optons;
    			add_filter('random_password', array(get_class(), 'set_group_password'), 10, 1);	
    		}
    		else{
    			$errors->add('domain_unavailable', sprintf('This domain <strong>%s</strong> is unavailable. Please choose another one ( <strong>%s</strong> ) ', $domain, implode(', ', $domains)));
    		}
    		    		
    	}
    	
    	return $errors;
    }
	
    
    //ms
	static function wpmu_validate_user_signup($result){
		
		$user_email = $result['user_email'];
		$errors = $result['errors'];
		
		if(is_email($user_email)){
			$em = explode('@', $user_email);
    		$domain = $em[count($em) - 1];
    		
    		$default_optons = self::get_site_default_options();
    		
    		$domains = $default_optons['default-group-domain'];

    		if(strlen($domains) > 3){
    		
	    		$domains = preg_replace('/[ ]/', '', $domains);
	    		$domains = explode(',', $domains);
	    		
	    		if(!in_array($domain, $domains)){
	    			$errors->add('user_email', sprintf('This domain <strong>%s</strong> is unavailable. Please choose another one ( <strong>%s</strong> ) ', $domain, implode(', ', $domains)));
	    			self::$registered_user['default'] = $default_optons;
	    			add_filter('random_password', array(get_class(), 'set_group_password'), 10, 1);	
	    		}
    		
    		}
    		
		}
		
		$result['errors'] = $errors;
				
		return $result;
		
	}
	
	
	/*
	 * it will use when someone activates his account from verification link (ms)
	 * */
	static function use_group_password($result){
		if(is_multisite()){			
			add_filter('random_password', array(get_class(), 'replace_with_default_password'), 10, 1);			
		}

		return $result;
	}
	
	
	//hook to filter the default password
	static function replace_group_password(){
		add_filter('random_password', array(get_class(), 'replace_with_default_password'), 10, 1);	
	}
	
	
	//replaces random password with default pass (ms)
	static function replace_with_default_password($password){
		$default_optons = self::get_site_default_options();
		if(isset($default_optons['default-group-password']) && !empty($default_optons['default-group-password'])){
			$password = $default_optons['default-group-password'];
		}
		
		return $password;
	}
    
	
	//ms attach default interspire and group list with a verified user
	static function set_default_user_meta($info){
		$user_id = $info['user_id'];
		$default_optons = self::get_site_default_options();
		
		if($default_optons['default-interspire-list'] > 0){
			update_user_meta($user->ID, 'interspire_list', $default_site_options['default-interspire-list']);
    		update_user_meta($user->ID, 'default_group', 'y');
		}
	}
	
	
	//ms showing error messages
	static function show_custom_signup_message($errors){
		if ( $errmsg = $errors->get_error_message('domain_unavailable') ) {
			echo '<p class="error">' . $errmsg . '</p>';
		}
	}
	
    
    
    //attach some meta data to the usermeta table using default settings and other settings
    static function user_register($user_id){
    	
    	$user = get_userdata($user_id);

    		if($user->caps['subscriber'] || in_array('subscriber', $user->roles)){
    			$default_site_options = self::get_site_default_options();
    			
    			if($default_site_options['default-interspire-list'] > 0){
    				update_user_meta($user->ID, 'interspire_list', $default_site_options['default-interspire-list']);
    				update_user_meta($user->ID, 'default_group', 'y');
    			}
    		}
    	
    }
    
      
    
    //set the group password as default
    static function set_group_password($password){
    	
    	//var_dump(self::$registered_user['group']);
    	
    	if(isset(self::$registered_user['default'])){
    		$default_pass = self::$registered_user['default']['default-group-password'];
    		if(strlen($default_pass) > 0){
    			$password = $default_pass;
    		}
    	}
    	    	 	
    	return $password;
    }
	

    
    /*
     * apply default site settings in login page
     * */
    static function login_init(){
    	$action = $_REQUEST['action'];    	
    	
    	$default_options = self::get_site_default_options();    	
    	
    	if($action == 'register'){
    	 	if ( $default_options['restrict-registration'] == 1 ) {
				wp_redirect( site_url('wp-login.php?registration=disabled') );
				exit();
			}
    	}   		
    	
    	if(in_array($action, array('retrievepassword', 'lostpassword'))){
    		if($default_options['restrict-password-reset'] == 1){
    			wp_redirect( site_url('wp-login.php?passwordreset=disabled') );
				exit();
    		}
    	}
    	
    }
    
    
    /*
     * giving login message
     * */
    static function login_message($message){
    	if($_REQUEST['passwordreset'] == 'disabled'){
    		$message = '<div id="login_error"><p> Password reset is not currently available </p></div>';
    	}
    	
    	return $message;
    }
    
    
    
    /*
     * bool if a memeber is a 
     * */
    static function is_a_group_memeber($user_id){
    	return get_user_meta($user_id, 'gm_group_id', true);
    }
    
    
    
    //get the interspire credentials
    static function get_interspire_credentials(){
    	return get_option('interspire_credentials');
    }
   
   	//get default site settings
	static function get_site_default_options(){
		return get_option('default_site_options');
	}
}
