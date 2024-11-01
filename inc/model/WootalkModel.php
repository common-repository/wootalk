<?php
/*
* ============== Wootalk Model ==============
*/

defined( 'ABSPATH' ) || exit;
/**
 * Main WootalkModel Class.
 *
 * @class WootalkModel
 */	
class WootalkModel {
	
	var $qry; 
	
	function get_wootalk_single_data($select, $where_data, $switch="AND", $debug = false)
	{
		global $wpdb;
		$requested_col = '';
		foreach ($select as $table => $col){
			$requested_col = $col;
			$this->qry .= 'SELECT '.$col.' FROM
			'.$wpdb -> prefix . $table;

		}
		$where = ' WHERE ';
		$format = array();
		foreach ($where_data as $type => $pair){
			if ($type == 's'){
				foreach ($pair as $f => $d){
					$where .= " $f = %s";
					$format[] = $d;
				}
			}else if ($type == 'd'){
				foreach ($pair as $f => $d){
					$where .= " $f = %d";
					$format[] = $d;
				}
			}


			$where .= " $switch ";
		}
		$where = substr_replace($where,"",-4);
		$this ->qry .= $where;

		$res = $wpdb - get_results($wpdb -> prepare($this -> qry, $format));
		$this -> qry = '';
		$wpdb -> flush();
		return $res[0] -> $requested_col;
	}


	function get_wootalk_row_data($select, $where_data, $switch="AND", $debug = false)
	{
		global $wpdb;
		$requested_col = '';
		foreach ($select as $table => $cols){

			if (is_array($cols))
				$cols = implode(',', $cols);

			$this -> qry .= 'SELECT '.$cols.' FROM
			'.$wpdb -> prefix . $table;

		}
		
		$where = ' WHERE ';
		$format = array();
		foreach ($where_data as $type => $pair){
			if ($type == 's'){
				foreach ($pair as $f => $d){
					$where .= " $f = %s";
					$format[] = $d;
				}
			}else if ($type == 'd'){
				foreach ($pair as $f => $d){
					$where .= " $f = %d";
					$format[] = $d;
				}
			}
			$where .= " $switch ";
		}
		$where_data = implode(',', $format);
		$where = substr_replace($where,"",-4);
		$this -> qry .= $where;
		$res = $wpdb -> get_results($wpdb -> prepare($this -> qry, $where_data));
		if( ! empty($res) ) {
			$res = $res[0];
		} else {
			$res = null;
		}
		$this -> qry = '';
		
		//clearing the cache
		$wpdb -> flush();
		return $res;
	}

	function get_wootalk_rows_data($select, $where_data = NULL, $switch="AND", $debug = false)
	{

		global $wpdb;
		$requested_col = '';
		foreach ($select as $table => $cols){
			if (is_array($cols))
				$cols = implode(',', $cols);
			$this -> qry .= 'SELECT '.$cols.' FROM
			'.$wpdb -> prefix . $table;
		}
		
		$format = array();
		if($where_data != NULL){
			$where = ' WHERE ';
			
			foreach ($where_data as $type => $pair){
				if ($type == 's'){
					foreach ($pair as $f => $d){
						$where .= " $f = %s";
						$format[] = $d;
					}
				}else if ($type == 'd'){
					foreach ($pair as $f => $d){
						$where .= " $f = %d";
						$format[] = $d;
					}
				}else if ($type == 'd2'){
					foreach ($pair as $f => $d){
						$where .= " $f = %d";
						$format[] = $d;
					}
				}
				$where .= " $switch ";
			}
			
			$where = substr_replace($where,"",-4);
			$this -> qry .= $where;
		}else{
			$where = ' WHERE 1 = %d';
			$format[] = 1;
			$this -> qry .= $where;
		}
		
		$res = $wpdb -> get_results($wpdb -> prepare($this -> qry, $format));
		$this -> qry = '';
		//clearing the cache
		$wpdb -> flush();
		return $res;
	}

	public function wootalk_insert_msg($table, $data, $format, $debug = false)
	{
		
		global $wpdb;
		$wpdb->insert($wpdb->prefix.$table, $data, $format);
		return $wpdb -> insert_id;
	}
	public function wootalk_update_msg($table, $data, $where, $format, $where_format,$debug = false)
	{
		global $wpdb;
		$rows_effected = $wpdb->update($wpdb ->prefix.$table, $data, $where, $format = null, $where_format = null);
		if($rows_effected)
			return true;
		else
			return false;
	}

    public function wootalk_update_msg_notification($table, $data, $where, $format, $where_format,$debug = false)
	{
		global $wpdb;
		$rows_effected=$wpdb->update($wpdb ->prefix.$table, $data, $where);
		if($rows_effected)
			return true;
		else
			return false;
	}
	
	function wootalk_delete_msg($table,$msg_id){
		global $wpdb;
		$wpdb->delete($wpdb->prefix.$table, array( 'wootalk_id' => $msg_id) );
		$wpdb -> flush();
	}

	/*
	 * print query and error 
	*/

	public function wootalk_print_query($caller_function)
	{
		global $wpdb;
		echo '<br> The query is being called from '.$caller_function.'<br>';
		$wpdb->show_errors();
		$wpdb->print_error();
	}
	
	
	
}

?>