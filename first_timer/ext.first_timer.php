<?php

/*
================================================================
	First Timer
	for EllisLab ExpressionEngine - by Ryan Irelan
----------------------------------------------------------------
	Copyright (c) 2008 Airbag Industries, LLC
================================================================
	THIS IS COPYRIGHTED SOFTWARE. PLEASE
	READ THE LICENSE AGREEMENT.
----------------------------------------------------------------
	This software is based upon and derived from
	EllisLab ExpressionEngine software protected under
	copyright dated 2005 - 2008. Please see
	http://expressionengine.com/docs/license.html
----------------------------------------------------------------
	USE THIS SOFTWARE AT YOUR OWN RISK. WE ASSUME
	NO WARRANTY OR LIABILITY FOR THIS SOFTWARE AS DETAILED
	IN THE LICENSE AGREEMENT.
================================================================
	File:			ext.first_timer.php
----------------------------------------------------------------
	Version:		1.0.1
----------------------------------------------------------------
	Purpose:		Lets you redirect a user to a specific page the first time they log in.
----------------------------------------------------------------
	Compatibility:	EE 1.6.3
----------------------------------------------------------------
	Created:		2008-06-13
================================================================
*/
// PLEASE NOTE THAT THIS EXTENSION DOES NOT CURRENTLY WORK WITH THE MULTIPLE SITE MANAGER

// -----------------------------------------
//	Begin class
// -----------------------------------------

class First_timer
{
    var $settings        = array();
    
    var $name            = 'First Timer';
    var $version         = '1.0.1';
    var $description     = 'Lets you redirect a user to a specific page the first time they log in.';
    var $settings_exist  = 'y';
    var $docs_url        = 'https://github.com/ryanirelan/ri.first_timer.ee_addon';
    
	 	// ------------------------------
		// Settings Pour mon Extension
		// ------------------------------
		
		function settings()
		{
			global $FNS;

			// set the base url so we can use it as the default for both fields
			$r = $FNS->create_url('');
			
			$settings = array();
			
			$settings['first_redirect'] = $r;
			$settings['normal_redirect'] = $r;
			
			return $settings;

		}

    // -------------------------------
    // Constructor
    // -------------------------------
    
    function First_timer ( $settings='' )
    {
        $this->settings = $settings;
    }
    // END
  


	// --------------------------------
	//  Activate Extension
	// --------------------------------

	function activate_extension()
	{
	    global $DB;

	    $DB->query($DB->insert_string('exp_extensions',
	                                  array(
	                                        'extension_id' => '',
	                                        'class'        => get_class($this),
	                                        'method'       => "redirect_user",
	                                        'hook'         => "member_member_login_single",
	                                        'settings'     => "",
	                                        'priority'     => 10,
	                                        'version'      => $this->version,
	                                        'enabled'      => "y"
	                                      )
	                                 )
	              );
	
	// create new column in exp_members so we can track first timers
	
	// check that the column doesn't already exist because we don't remove it when uninstalling the extension (on purpose, so as to protect existing data in that column)
	
	$column_check = $DB->query("SHOW COLUMNS FROM exp_members");
	
	$first_timer_exists = FALSE;  
	
	foreach($column_check->result as $column)
	{
		
		if ($column['Field'] == "first_time") 
		{
			$first_timer_exists = TRUE;
		}
		                            
	} 
	
	// if the column isn't already there, go ahead and create it
		
		if (! $first_timer_exists)
		{
			$DB->query("ALTER TABLE exp_members 
									ADD COLUMN first_time INT(1) DEFAULT 0");
		}
		
	}
	// END
	
	// --------------------------------
	//  Do the redirect
	// --------------------------------

	function redirect_user()
	{
			global $FNS, $SESS, $DB, $LOC;
			
			// get the current user
			$this_user = $SESS->userdata['member_id'];
		 	
			// check whether the user has logged in before
			$last_visit = $DB->query("SELECT first_time 
																FROM exp_members 
																WHERE member_id = $this_user");
			
			foreach($last_visit->result as $visit)
			{
				$last_visit = $visit['first_time'];
			}
			
			if ($last_visit == 0)
			{
				$this_user = $SESS->userdata['member_id'];
				
				// update the member row and mark them as having at least one login
				$DB->query("UPDATE exp_members 
										SET first_time = 1 
										WHERE member_id = $this_user");
				
				//redirect based on control panel setting
				$redirect = $this->settings['first_redirect'];
				$FNS->redirect($redirect);
				
			}
			
			// if they're not first timers, just redirect them to normal redirect
			
			else
			{
			 
				$redirect = $this->settings['normal_redirect'];
				$FNS->redirect($redirect);
			}  
	}
	// END
	
	// --------------------------------
	//  Update Extension
	// --------------------------------  

	function update_extension ( $current='' )
	{
	    global $DB;

	    if ($current == '' OR $current == $this->version)
	    {
	        return FALSE;
	    }

	    if ($current < '1.0.1')
	    {
	        // Update to next version
	    }

	    $DB->query("UPDATE exp_extensions 
	                SET version = '".$DB->escape_str($this->version)."' 
	                WHERE class = '".get_class($this)."'");
	}
	// END
	
	// --------------------------------
	//  Disable Extension
	// --------------------------------

	function disable_extension()
	{
	    global $DB;

	    $DB->query("DELETE FROM exp_extensions WHERE class = '".get_class($this)."'");
	}
	// END
}
// END CLASS   

?>