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
	}
	
		
	// manages admin menu
	static function admin_menu(){
		add_menu_page('user group management', 'User Groups', 'manage_options', 'user-group-management', array(get_class(), 'menu_group_management'), '', 68);
		add_submenu_page('user-group-management', 'new or edit user group', 'Add New', 'manage_options', 'addnew-user-group', array(get_class(), 'submenu_add_usergourp'));
	}
	
	
	//menupage
	static function menu_group_management(){
		
		include USERGROUPMANAGMENT_DIR . '/includes/groups.php';
	}
	
	//sub menu page to add or edit an user group
	static function submenu_add_usergourp(){
		if($_POST['page'] == 'addnew-user-group'){
			$info = self::save_group_info();
		}
		include USERGROUPMANAGMENT_DIR . '/includes/add-neew-group.php';
	}
	
	
	//function to hanel group info
	static function save_group_info(){
		$Ugdb = new UgDbManagement();
		$group_id = 0;		
		if(isset($_POST['group_id'])) $group_id = $_POST['group_id'];
		
		$info = array(
			'name' => trim($_POST['group_name']),
			'domain' => trim($_POST['group_domain']) 
		);
		
		$group_id = $Ugdb->update_group($group_id, $info);

		if($group_id > 0){
			$Ugdb->update_group_meta($group_id, 'password', trim($_POST['group_password']));
			$Ugdb->update_group_meta($group_id, 'group_interspire_list', trim($_POST['group_interspire_list']));
		}
	}
	
	
	//activate the plguin and create tables
	static function manage_db(){
		$Ugdb = new UgDbManagement();
		return $Ugdb->manage_db();
	}
}