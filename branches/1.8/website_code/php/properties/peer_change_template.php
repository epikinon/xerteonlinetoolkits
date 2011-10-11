<?PHP     /**
* 
* peer change template, alters the peer review status of a template
*
* @author Patrick Lockley
* @version 1.0
* @copyright Copyright (c) 2008,2009 University of Nottingham
* @package
*/

	require("../../../config.php");
	require("../../../session.php");

	include "../database_library.php";

	include "../template_status.php";

	include "../url_library.php";

	include "../user_library.php";
	
	include "properties_library.php";

	$database_id = database_connect("peer template database connect success","peer template change database connect failed");

	if(is_numeric($_POST['template_id'])){

		if(is_user_creator(mysql_real_escape_string($_POST['template_id']))||is_user_admin()){
	
			if($_POST['peer_status']=="off"){
	
				$query = "delete from " . $xerte_toolkits_site->database_table_prefix . "additional_sharing where template_id=\"" . mysql_real_escape_string($_POST['template_id']) . "\" AND sharing_type=\"peer\"";
	
				mysql_query($query);
	
			}else{
	
				$query = "INSERT INTO " . $xerte_toolkits_site->database_table_prefix . "additional_sharing (template_id, sharing_type, extra) VALUES (" . mysql_real_escape_string($_POST['template_id']) . ", \"peer\",\"" .  mysql_real_escape_string($_POST['password']) . "\")";
	
				mysql_query($query);
	
			}		
	
			/**
			* Update the screen
			*/
	
			peer_display($xerte_toolkits_site,true);
			
		}else{
	
			peer_display_fail();
	
		}
	
		mysql_close($database_id);
	
	}

?>