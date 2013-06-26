<?php
/*
 * This class will handle the scheduler
 * */
class InterspireScheduler{

	const hook = "interspire_schdeuler";
	const interval = "everytwohour";

	static function init(){

		register_activation_hook(USERGROUPMANAGMENT_FILE, array(get_class(), 'activate_scheduler'));
		register_deactivation_hook(USERGROUPMANAGMENT_FILE, array(get_class(), 'deactivate_scheduler'));

		add_filter('cron_schedules', array(get_class(), 'add_new_interval'));

		add_action(self::hook, array(get_class(), 'process_scheduler'));
	
		if(is_multisite()){
			add_action('init', array(get_class(), 'activate_scheduler'));
		}
		
			

	}


	static function activate_scheduler(){
		if(!wp_next_scheduled(self::hook)) {
			wp_schedule_event( current_time( 'timestamp' ), self::interval, self::hook);
		}
	}


	static function deactivate_scheduler(){
		wp_clear_scheduled_hook(self::hook);
	}


	static function add_new_interval($schedules){
		$schedules['everytwohour'] = array(
			'interval' => 2 * HOUR_IN_SECONDS,
			'display' => 'Every Two Hour'
		);

		$schedules['everythreehour'] = array(
			'interval' => 3 * HOUR_IN_SECONDS,
			'display' => 'Every Three Hour'
		);

		return $schedules;
	}

	
	/*get offset value*/
	static function get_offset(){
		return get_option('interspire_sync_offset');
	}
	


	/*
	 * This function does the whole thing
	 * */
	static function process_scheduler(){
		$offset = self::get_offset();

		$offset = empty($offset) ? 0 : (int) $offset;
		$limit = 300;
		
		$user_query = self::get_unsynchronized_users($offset, $limit);
		
		$users = $user_query->get_results();		
		$count_user = $user_query->get_total();

		//var_dump($users);
		
		//resetting the offset if it reaches the final point of db table
		if($limit > $count_user){
			$offset = 0;
		}
		else{
			$offset += $limit;
		}
		
		//saving the offset in database for further query
		update_option('interspire_sync_offset', $offset);

		
		if($users){

			$sync = UgManagement::get_synchronizer();
	
			foreach($users as $key => $user){
	
				$usermeta = self::get_user_specific_data($user->ID);
				$usermeta['email'] = $user->user_email;
				
				//var_dump($usermeta);
				
				$response = $sync->add_subsciber($usermeta);
			}
		}

	
		
	}


	//unsynchronized users  fetching with user query class
	static function get_unsynchronized_users($offset = 0, $number = 300){
		
		$query = array(
			'meta_key'      =>  'interspire_list',
			'meta_value'    =>  '0',
			'meta_compare'  =>  '>',
			'offset'        => $offset,
			'number'        => $number,
			'fields'        => array('ID', 'user_email'),
			'orderby'       => 'ID'
		);
		
		if(is_multisite()){
			$query['blog_id'] = get_current_blog_id();
		}

		
		$user_query = new WP_User_Query($query);
		
		return $user_query;
			
	}



	static function get_user_specific_data($user_id){
		global $wpdb;
		$sql = "select meta_key, meta_value from $wpdb->usermeta where user_id = '$user_id' and meta_key in ( 'nickname', 'interspire_list' ) ";
		$results = $wpdb->get_results($sql);

		$sanitized = array();

		foreach($results as $result){
			$sanitized[$result->meta_key] = $result->meta_value;
		}

		return $sanitized;

	}


	static function scheduler_check(){
		/*
		$sch = wp_get_schedule(self::hook);
		var_dump($sch);
		*/

		self::process_scheduler();

		die();
	}
	
	
	/*
	 * calls on a delte a user to unsubscribe
	 * */
	static function unsubscribe_user($id){
		$user = new WP_User( $id );
		if($user){
			$meta_data = self::get_user_specific_data($user->ID);
			$meta_data['email'] = $user->user_email;
			$sync = UgManagement::get_synchronizer();
			$sync->unsubscribe_user($meta_data);
		}
	}
	
}