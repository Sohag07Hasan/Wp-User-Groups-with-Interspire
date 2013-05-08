<?php
class UgDbManagement{

	var $group;
	var $group_meta;
	
	function __construct(){
		$tables = $this->get_tables();
		
		$this->group = $tables['group'];
		$this->group_meta = $tables['group_meta'];
	}
	
	//manage db
	function manage_db(){
		global $wpdb;
				
		$sql = array();
		
		$sql[] = "create table if not exists $this->group(
			ID bigint not null auto_increment,
			name text not null,
			domain text,
			primary key(ID)			
		)";
		
		$sql[] = "create table if not exists $this->group_meta(
			group_id bigint not null,
			meta_key text not null,
			meta_value text not null
		)";
				
		
		foreach($sql as $s){			
			$wpdb->query($s);
		}
		
	}
	
	
	function get_tables(){
		global $wpdb;
		
		return array(
			'group' => $wpdb->prefix . 'user_groups',
			'group_meta' => $wpdb->prefix . 'user_group_meta'
		);
	}
	
	//utililty functions
	
	function update_group($ID, $info){
		global $wpdb;
		
		if($ID > 0){
			$wpdb->update($this->group, $info, array('ID' => $ID), array('%s', '%s'), array('%d'));
		}
		else{
			$wpdb->insert($this->group, $info, array('%s', '%s'));
			$ID = $wpdb->insert_id;
		}
		
		return $ID;
	}
	
	
	function update_group_meta($group_id, $meta_key, $meta_value){
		global $wpdb;
		$is_exist = $wpdb->get_var("select group_id from $this->group_meta where group_id = '$group_id' and meta_key = '$meta_key'");
		
		if($is_exist){
			$wpdb->update($this->group_meta, array('meta_key'=>$meta_key, 'meta_value'=>$meta_value), array('group_id'=>$group_id), array('%s', '%s'), array('%d'));	
		}
		else{
			$wpdb->insert($this->group_meta, array('group_id'=>$group_id, 'meta_key'=>$meta_key, 'meta_value'=>$meta_value), array('%d', '%s', '%s'));
		}
			
	}
		
	
	function get_group($ID = 0){
		
		$info = array();
		
		if($ID > 0){
			global $wpdb;
			$group = $wpdb->get_row("select * from $this->group where ID = '$ID'");
			if($group){
				$info = array(
					'ID' => $group->ID,
					'name' => $group->name,
					'domain' => $group->domain
				);
			}
		}
		
		return $info;
	}
	
	function get_group_meta($group_id, $meta_key){
		global $wpdb;
		if($group_id){
			return $wpdb->get_var("select meta_value from $this->group_meta where group_id = '$group_id' and meta_key = '$meta_key'");
		}
	}
	
	function get_group_metas($group_id){
		global $wpdb;
		$metas = array();
		
		if($group_id > 0){
			$results = $wpdb->get_results("select meta_key, meta_value FROM $this->group_meta where group_id = '$group_id'");
			
			if($results){
				foreach($results as $result){
					$metas[$result->meta_key] = $result->meta_value;
				}
			}			
		}
		
		return $metas;
	}
	
	
	//wrapper of get_results
	function get_results($sql, $table=0){
		global $wpdb;
				
		return $wpdb->get_results($sql);
	}
	
	
	function get_var($sql, $table = 0){
		global $wpdb;
			
		return $wpdb->get_var($sql);
	}
	
	
	/*
	 * delete a goup
	 * */
	function delete_group($ID){
		global $wpdb;
		$sql = array();
		$sql[] = "delete from $this->group where ID = '$ID'";
		$sql[] = "delete from $this->group_meta where group_id = '$ID'";
		
		foreach ($sql as $s) {
			$wpdb->query($s);
		}
	}
}
