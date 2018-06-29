<?php
/*
   Public balance shortcode
   
 */
 
 /* Designed for informational purposes to see total supply of points in system.
  * And own soft leaderboard that isn't ranked.
  *
  */

/* Main Public Log shortcode function */

function pb_func() {
	
	/* Technically users don't have to be logged in
	* Should litterally be the log the admin sees 
	* I don't care. Tell users to not put personal identificable 
	* information in their user name (referred to PID in the health care industry)
	*/
	
	global $wpdb;
	$table_name_points = $wpdb->prefix . 'vyps_points';
	$table_name_log = $wpdb->prefix . 'vyps_points_log';
	$table_name_users = $wpdb->prefix . 'users';
	
	//BTW the number of IDs should always match the number of rows, NO EXCEPTIONS. If it doesn't it means the admin deleted a row
	//And that is against the psuedo-blockchain philosophy. //Also it dawned on me I can rewrite the public log here.
	
	$number_of_log_rows = $wpdb->get_var( "SELECT max( id ) FROM $table_name_log" ); //No where needed. All rows. No exceptions
	$number_of_point_rows = $wpdb->get_var( "SELECT max( id ) FROM $table_name_points" ); //No where needed. All rows. No exceptions
	
	//echo '<br>'. $number_of_log_rows; //Some debugging
	//echo '<br>'. $number_of_point_rows; //More debugging
	
	$begin_row = 1;
	$end_row = ''; //Eventually will have admin ability to filter how many rows they see as after 1000 may be intensive
	
	/* Although normally against totally going programatic. Since I know I'm going to reuse this for the public log I'm going to put the headers into variables */
	
	$date_label = "Date";
	$user_name_label = "User Name";
	$user_id_label = "UID";
	$point_type_label = "Point Type";
	$point_id_label = "PID";
	$amount_label = "Amount";
	$reason_label = "Adjustment Reason";


	//Header output is also footer output if you have not noticed.
	//Also isn't it nice you can edit the format directly instead it all in the array?
	$header_output = "
			<tr>
				<th>$date_label</th>
				<th>$user_name_label</th>
				<th>$user_id_label</th>
				<th>$point_type_label</th>
				<th>$point_id_label</th>
				<th>$amount_label</th>
				<th>$reason_label</th>
			</tr>	
	";


	
	
	//Because the shorcode version won't have this
	$page_header_text = "
		<h1 class=\"wp-heading-inline\">All Point Adjustments</h1>        
		<h2>Point Log</h2>
	";
	
	//this is what it's goint to be called
	$table_output = "";
	
	for ($x_for_count = $number_of_log_rows; $x_for_count > 0; $x_for_count = $x_for_count -1 ) { //I'm counting backwards. Also look what I did. Also also, there should never be a 0 id or less than 1
	
		$date_data = $wpdb->get_var( "SELECT time FROM $table_name_log WHERE id= '$x_for_count'" ); //Straight up going to brute force this un-programatically not via entire row
		$user_id_data = $wpdb->get_var( "SELECT user_id FROM $table_name_log WHERE id= '$x_for_count'" );
		$user_name_data = $wpdb->get_var( "SELECT user_login FROM $table_name_users WHERE id= '$user_id_data'" ); //And this is why I didn't call it the entire row by arrow. We are in 4d with multiple tables
		$point_id_data = $wpdb->get_var( "SELECT points FROM $table_name_log WHERE id= '$x_for_count'" ); //Yeah this is why I want to call points something else in this table, but its the PID if you can't tell
		$point_type_data = $wpdb->get_var( "SELECT name FROM $table_name_points WHERE id= '$point_id_data'" ); //And now we are calling a total of 3 tables in this operation
		$amount_data = $wpdb->get_var( "SELECT points_amount FROM $table_name_log WHERE id= '$x_for_count'" );
		$reason_data = $wpdb->get_var( "SELECT reason FROM $table_name_log WHERE id= '$x_for_count'" );
		
		$current_row_output = "
			<tr>
				<td>$date_data</td>
				<td>$user_name_data</td>
				<td>$user_id_data</td>
				<td>$point_type_data</td>
				<td>$point_id_data</td>
				<td>$amount_data</td>
				<td>$reason_data</td>
			</tr>
				";
		
		//Compile into row output.
		$table_output = $table_output . $current_row_output; //I like my way that is more reasonable instead of .=
		
	} 
	
	//The page output
	echo "
		<div class=\"wrap\">
			$page_header_text
			<table class=\"wp-list-table widefat fixed striped users\">
				$header_output
				$table_output
				$header_output
			</table>			
		</div>
	";
	
}
	
/* 
* Shortcode for the log.
*/

add_shortcode( 'vyps-pb', 'pb_func');
