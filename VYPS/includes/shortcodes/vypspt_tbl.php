<?php
/*
	VIDYEN Points Transfer Table Shortcode
	
 */


function pt_tbl_func( $atts ) {
	
	/* Check to see if user is logged in and boot them out of function if they aren't. */
	
	if ( is_user_logged_in() ) {
		
	//I probaly don't have to have this part of the if
		
	} else {
		
		return;
		
	}
	
	/* The shortcode attributes need to come before the button as they determin the button value */
	
	$atts = shortcode_atts(
		array(
				'spid' => '0',
				'dpid' => '0',
				'samount' => '0',
				'damount' => '0',
		), $atts, 'vyps-pt' );
		
	$sourcePointID = $atts['spid'];
	$destinationPointID = $atts['dpid'];
	$pt_sAmount = $atts['samount'];
	$pt_dAmount = $atts['damount'];
	
	/* Not seeing comma number seperators annoys me */
	
	$format_pt_sAmount = number_format($pt_sAmount);
	$format_pt_dAmount = number_format($pt_dAmount);
	
	$btn_name = $sourcePointID . $destinationPointID . $pt_sAmount . $pt_dAmount;
	
	/*I don't know if this is some lazy coding but I am going to just return out if they haven't pressed the button
	* Side note: And this is important. The button value should be dynamic to not interfer with other buttons on page
	*/
	
	/* I tried avoiding calling this before the press, but had to get point names */
	
	global $wpdb;
	$table_name_points = $wpdb->prefix . 'vyps_points';
	$table_name_log = $wpdb->prefix . 'vyps_points_log';
	
	/* Just doing some table calls to get point names and icons. Can you put icons in buttons? Hrm... */
	
	$sourceName = $wpdb->get_var( "SELECT name FROM $table_name_points WHERE id= '$sourcePointID'" );
	$sourceIcon = $wpdb->get_var( "SELECT icon FROM $table_name_points WHERE id= '$sourcePointID'" );
	
	$destName = $wpdb->get_var( "SELECT name FROM $table_name_points WHERE id= '$destinationPointID'" );
	$destIcon = $wpdb->get_var( "SELECT icon FROM $table_name_points WHERE id= '$destinationPointID'" );
	
	if (isset($_POST[ $btn_name ])){ 
	
		/* Nothing should happen */
	
	} else {
		
		/* Ok. I'm creating a semi-unique name by just concatinating all the shortcode attributes.
		*  In theory one could have two buttons with the same shortcode attributes, but why would you do that?
		*  What should happen is that the function only runs when the unique name of the button is posted.
		*  What could go wrong?
		*/
		
		/* Just show them button if button has not been clicked. Its a requirement not a suggestion. */
		
		/* In future version I'm going to make the points say the numerical values that about to be transfered. Maybe. */
		/* I added ability to have point names but for now. Just have the button say transfer and the warning give how much */
		/* BTW it's only 1 column, 2 rows for each button. One for the output at top and one at bottom for button. */
		/* Reason I put button at bottom is that don't want mouse on the text bothering user */
		
		$results_message = "Press button to transfer points.";
		
		return "<table id=\"$btn_name\">
					<tr>
						<td><div align=\"center\">Spend</div></td>
						<td><div align=\"center\"><img src=\"$sourceIcon\" width=\"16\" hight=\"16\" title=\"$sourceName\"> $format_pt_sAmount</div></td>
						<td>
							<div align=\"center\">
								<b><form method=\"post\">
									<input type=\"hidden\" value=\"\" name=\"$btn_name\"/>
									<input type=\"submit\" class=\"button-secondary\" value=\"Transfer\" onclick=\"return confirm('You are about to transfer points $format_pt_sAmount $sourceName for $pt_dAmount $destName. Are you sure?');\" />
								</form></b>
							</div>
						</td>
						<td><div align=\"center\"><img src=\"$destIcon\" width=\"16\" hight=\"16\" title=\"$destName\"> $format_pt_dAmount</div></td>
						<td><div align=\"center\">Receive</div></td>
					</tr>
					<tr>
						<td colspan = 5><div align=\"center\"><b>$results_message</b></div></td>
					</tr>
				</table>";
					//<br><br>$btn_name";	//Debug: I'm curious what it looks like.	
	}
	
	
	/* These operations are below the post check as no need to wast server CPU if user didn't press button */
	
	
	$table_name_log = $wpdb->prefix . 'vyps_points_log';
	$current_user_id = get_current_user_id();
	
	/* if either earn or spend are 0 it means the admin messed up
	*  the shortcode atts and that you need to return out
	*  Shouldn't this all be set to elseifs? 
	*/
	
	if ( $pt_sAmount == 0 ) {
		
		return "Admin Error: Source amount was 0!";
		
	}
	
	if ( $pt_dAmount == 0 ) {
		
		return "Admin Error: Destination amount was 0!";
		
	}
	
	/* Oh yeah. Checking to see if source pid was set */
	
	if ( $sourcePointID == 0 ) {
		
		return "Admin Error: You did not set source pid!";
		
	}
	
	/* And the destination pid */
	
	if ( $destinationPointID == 0 ) {
		
		return "Admin Error: You did not set destination pid!";
		
	}

	
	//Ok. Now we get balance. If it is not enough for the spend variable, we tell them that and return out. NO EXCEPTIONS
	
	$balance_points = $wpdb->get_var( "SELECT sum(points_amount) FROM $table_name_log WHERE user_id = $current_user_id AND points = $sourcePointID");
	
	/* I do not ever see the need for a non-formatted need point */
	
	$need_points = number_format($pt_sAmount - $balance_points);
	
	if ( $pt_sAmount > $balance_points ) {
		
		$results_message = "Not enough " . $sourceName . " to transfer! You need " . $need_points . " more.";
		
		return "<table id=\"$btn_name\">
					<tr>
						<td><div align=\"center\">Spend</div></td>
						<td><div align=\"center\"><img src=\"$sourceIcon\" width=\"16\" hight=\"16\" title=\"$sourceName\"> $format_pt_sAmount</div></td>
						<td>
							<div align=\"center\">
								<b><form method=\"post\">
									<input type=\"hidden\" value=\"\" name=\"$btn_name\"/>
									<input type=\"submit\" class=\"button-secondary\" value=\"Transfer\" onclick=\"return confirm('You are about to transfer points $format_pt_sAmount $sourceName for $pt_dAmount $destName. Are you sure?');\" />
								</form></b>
							</div>
						</td>
						<td><div align=\"center\"><img src=\"$destIcon\" width=\"16\" hight=\"16\" title=\"$destName\"> $format_pt_dAmount</div></td>
						<td><div align=\"center\">Receive</div></td>
					</tr>
					<tr>
						<td colspan = 5><div align=\"center\"><b>$results_message</b></div></td>
					</tr>
				</table>";
		
	}
	

	
	
	/* All right. If user is still in the function, that means they are logged in and have enough points.
	*  It dawned on me an admin might put in a negative number but that's on them.
	*  Now the danergous part. Deduct points and then add the VYPS log to the WooWallet
	*  I'm just going to reuse the CH code for ads and ducts
	*/
	
	/* The CH add code to insert in the vyps log */
	
	$table_log = $wpdb->prefix . 'vyps_points_log';
	$reason = "Point Transfer";
	$amount = $pt_sAmount * -1; //Seems like this is a bad idea to just multiply it by a negative 1 but hey

	$PointType = $sourcePointID; //Originally this was a table call, but seems easier this way
	$user_id = $current_user_id;
	
	/* In my heads points out should happen first and then points destination. */
	
	$data = [
			'reason' => $reason,
			'points' => $PointType,
			'points_amount' => $amount,
			'user_id' => $user_id,
			'time' => date('Y-m-d H:i:s')
			];
	$wpdb->insert($table_log, $data);
	
	/* Ok. Now we put the destination points in. Reason should stay the same */
	
	$amount = $pt_dAmount; //Destination amount should be positive

	$PointType = $destinationPointID; //Originally this was a table call, but seems easier this way
	
	$data = [
			'reason' => $reason,
			'points' => $PointType,
			'points_amount' => $amount,
			'user_id' => $user_id,
			'time' => date('Y-m-d H:i:s')
			];
	$wpdb->insert($table_log, $data);
	
	$results_message = "Success. Points exchanged at: ". date('Y-m-d H:i:s');
	
	return "<table id=\"$btn_name\">
					<tr>
						<td><div align=\"center\">Spend</div></td>
						<td><div align=\"center\"><img src=\"$sourceIcon\" width=\"16\" hight=\"16\" title=\"$sourceName\"> $format_pt_sAmount</div></td>
						<td>
							<div align=\"center\">
								<b><form method=\"post\">
									<input type=\"hidden\" value=\"\" name=\"$btn_name\"/>
									<input type=\"submit\" class=\"button-secondary\" value=\"Transfer\" onclick=\"return confirm('You are about to transfer points $format_pt_sAmount $sourceName for $pt_dAmount $destName. Are you sure?');\" />
								</form></b>
							</div>
						</td>
						<td><div align=\"center\"><img src=\"$destIcon\" width=\"16\" hight=\"16\" title=\"$destName\"> $format_pt_dAmount</div></td>
						<td><div align=\"center\">Receive</div></td>
					</tr>
					<tr>
						<td colspan = 5><div align=\"center\"><b>$results_message</b></div></td>
					</tr>
				</table>";
			
			/* since I have the point names I might as well use them. Also I put it below because its annoying to have button move. */
			//<br><br>$btn_name"; //Debug stuff
	
	
}

/* Telling WP to use function for shortcode */

add_shortcode( 'vyps-pt-tbl', 'pt_tbl_func');

/* Ok after much deliberation, I decided I want the WW plugin to go into the pt since it has become the exchange */
/* If you don't have WW, it won't kill anything if you don't call it */

/* WW shortcode was here but moved it out */	