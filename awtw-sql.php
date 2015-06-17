<?php 
/************************************************************************************/
/*	All Awtw Related SQL functions	*/		
/************************************************************************************/		
Class Awtw_DB {	

	public static function createTable() {
		global $wpdb;
		$tablename = $wpdb->prefix. 'awtw_plugin';

		$sql = "CREATE TABLE $tablename (
		  id int(11) unsigned NOT NULL AUTO_INCREMENT,
		  ip_address varchar(40) NOT NULL,
		  source_url varchar(2083) NOT NULL,
		  agent varchar(300) NOT NULL,
		  created_at datetime NOT NULL,
		  status tinyint(1) unsigned NOT NULL,
		  feedback varchar(2000) NOT NULL,
		  PRIMARY KEY (id)
			);";

		// Required to run Query using dbDelta()
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		// Run the Query
		dbDelta( $sql );

	}

	public static function get($id) {
		global $wpdb;
		$tablename = $wpdb->prefix. 'awtw_plugin';
		$output = $wpdb->get_results("SELECT * FROM $tablename WHERE id = $id");
		return $output[0];
	}

	public static function getAll() {
		global $wpdb;
		$tablename = $wpdb->prefix. 'awtw_plugin';
		$output = $wpdb->get_results("SELECT * FROM $tablename");
		return $output;
	}

	public static function statusUpdate($id, $status) {
		global $wpdb;
		$tablename = $wpdb->prefix. 'awtw_plugin';
		$output = $wpdb->query("UPDATE $tablename SET status = $status WHERE id = $id");
		return $output;
	}

	public static function getSpam() {
		global $wpdb;
		$tablename = $wpdb->prefix. 'awtw_plugin';
		$output = $wpdb->get_results("SELECT * FROM $tablename WHERE status = 1");
		return $output;
	}

	public static function getPending() {
		global $wpdb;
		$tablename = $wpdb->prefix. 'awtw_plugin';
		$output = $wpdb->get_results("SELECT * FROM $tablename WHERE status = 0");
		return $output;
	}
	public static function getApproved() {
		global $wpdb;
		$tablename = $wpdb->prefix. 'awtw_plugin';
		$output = $wpdb->get_results("SELECT * FROM $tablename WHERE status = 2");
		return $output;
	}
	public static function delete($id) {
		global $wpdb;
		$tablename = $wpdb->prefix. 'awtw_plugin';
		$output = $wpdb->query("DELETE FROM $tablename WHERE id = $id");
		return $output;
	}
	public static function count($status) {
		global $wpdb;
		$tablename = $wpdb->prefix. 'awtw_plugin';
		$output = $wpdb->get_results("SELECT COUNT(*) as c FROM $tablename WHERE status = $status");
		return $output[0]->c;
	}


} ?>