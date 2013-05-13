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
		
	//	add_action('init', array(get_class(), 'scheduler_check'));
		
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
	
	
	
	/*
	 * This function does the whole thing
	 * */
	static function process_scheduler(){
		$users = self::get_unsynchronized_users();
				
		if($users){

			$sync = UgManagement::get_synchronizer();
			
			foreach($users as $key => $user){
			
				$usermeta = self::get_user_specific_data($user->ID);
				$usermeta['email'] = $user->user_email;
				//var_dump($usermeta);
				$response = $sync->add_subsciber($usermeta);

				if($response){
					update_user_meta($user->ID, 'interspire_status', 1);
					
				}
				
			}
		}
		
	}
		
	
	//unsynchronized users
	static function get_unsynchronized_users(){
		global $wpdb;
		
		$sql = "select ID, user_email  from $wpdb->users where ID in (
						select c.user_id from(
							select count(*) as num, user_id  from $wpdb->usermeta where meta_key like 'interspire_list' or meta_key like 'interspire_status'  group by user_id 
						) c where  c.num = 1
		) limit 100" ;
		
		$results = $wpdb->get_results($sql);

		return $results;
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
		$sch = wp_get_schedule(self::hook);
		var_dump($sch);
		die();
	}
	
}