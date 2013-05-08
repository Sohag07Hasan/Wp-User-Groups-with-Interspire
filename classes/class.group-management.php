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

		
		//for submissions
		add_action('init', array(get_class(), 'save_group_info'));
	}
	
		
	// manages admin menu
	static function admin_menu(){
		add_menu_page('user group management', 'User Groups', 'manage_options', 'user-group-management', array(get_class(), 'menu_group_management'), '', 68);
		add_submenu_page('user-group-management', 'new or edit user group', 'Add New', 'manage_options', 'addnew-user-group', array(get_class(), 'submenu_add_usergourp'));
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
		
		include USERGROUPMANAGMENT_DIR . '/includes/groups.php';
	}
	
	
	//get a list table
	static function get_list_table(){
		if(!class_exists('UgListTable')){
			include USERGROUPMANAGMENT_DIR . 'classes/list-table.php';
		}
		
		$UgList = new UgListTable();
		return $UgList;
	}
	
	
	//sub menu page to add or edit an user group
	static function submenu_add_usergourp(){		
		include USERGROUPMANAGMENT_DIR . '/includes/add-neew-group.php';
	}
	
	
	//function to hanel group info
	static function _save(){
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
		
		if($group_id > 0){
			return array(
				'group_id' => $group_id,
				'message' => 1
				
			);
		}
	}
	
	
	//activate the plguin and create tables
	static function manage_db(){
		$Ugdb = new UgDbManagement();
		return $Ugdb->manage_db();
	}
	
	
	//do a http redirect
	static function do_redirect($url){
		if(!function_exists('wp_redirect')){
			include ABSPATH . '/wp-includes/pluggable.php';
		}
		
		wp_redirect($url);
		die();
	}
}