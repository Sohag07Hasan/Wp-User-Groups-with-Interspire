<?php
/*
 * Createa a new custom posttype to store the group informations
 * 
 * */

class UgManagement{
	
	//custom posttype management
	const posttype = 'usergroup';
	
	
	static function init(){
			
		//admin menu
		add_action('admin_menu', array(get_class(), 'admin_menu'));		
		register_activation_hook(USERGROUPMANAGMENT_FILE, array(get_class(), 'manage_db'));
	//	register_deactivation_hook(USERGROUPMANAGMENT_FILE, array(get_class(), 'deactivated_plugin'));

		
		//for submissions
		add_action('init', array(get_class(), 'save_group_info'));
		
		//add_action('init', array(get_class(), 'test'));
		
		
		//prevent password reset
		add_filter('allow_password_reset', array(get_class(), 'prevent_password_reset'), 10, 2);
					
	}
	
	
	
	
	
	static function test(){
		$sync = self::get_synchronizer();
		$lists = $sync->get_lists();
		
		var_dump($lists);
		exit;
	}
	
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
		add_menu_page('user group management', 'User Groups', 'manage_options', 'user-group-management', array(get_class(), 'menu_group_management'), '', 68);
		add_submenu_page('user-group-management', 'new or edit user group', 'Add New', 'manage_options', 'addnew-user-group', array(get_class(), 'submenu_add_usergourp'));
		add_submenu_page('user-group-management', 'inter sipre default options', 'InterSpire', 'manage_options', 'interspire-default-options', array(get_class(), 'submenu_interspire'));
		add_submenu_page('user-group-management', 'inter sipre default options', 'Site Settings', 'manage_options', 'registration-default-options', array(get_class(), 'submenu_registration_options'));
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
    			
    		//	var_dump($group_meta); die();
    			
    			if($user){
    				$user->set_role($group_meta['role']);
    				update_user_meta($user->ID, 'gm_group_id', $group['ID']);
    				update_user_meta($user->ID, 'interspire_list', $group_meta['group_interspire_list']);
    				
    				return true;
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
    					update_user_meta($user_id, 'interspire_list', $group_meta['group_interspire_list']);
    					
    					return true;
    				}
    			}
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
    	return false;
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