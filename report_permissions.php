<?php
/*
 * Determines the permissions of the current user in the current report
 * by looking at the users roles in the current context  
 *
 * @copyright &copy; 2011 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package ILP
 * @version 2.0
 */

global	$CFG,$USER;

//get the user id if it is not set then we will pass the global $USER->id 
$user_id   = $PARSER->optional_param('user_id',$USER->id,PARAM_INT);

//get the id of the report 
$report_id   = $PARSER->required_param('report_id',PARAM_INT);

if (!isset($context)) {
	print_error('contextnotset');
} 

//get all of the users roles in the current context and save the id of the roles into
//an array 
$role_ids	=	 array();
if ($roles = get_user_roles($context, $USER->id)) {
 	foreach ($roles as $role) {
 		$role_ids[]	= $role->roleid;
 	}
}

//REPORT CAPABILITIES

$access_report_createreports	=	0;
$access_report_editreports		=	0;
$access_report_deletereports	=	0;
$access_report_viewreports		=	0;	
$access_report_viewilp			=	0;
$access_report_viewotherilp		=	0;

$dbc	=	new ilp_db();

//we only need to check if a report permission has been assigned 
//if the user has the capability in the current context 


if ($access_createreports) { 
	
	//moodle 2.0 throws an error whena comparison is carried out for the context name in
    //pure sql. This could have something to do with the /: in the context name. So I am
    //having to get the capability record id first and then pass it to the 
    $capability	=	$dbc->get_capability_by_name('block/ilp:addreport');
	
	$access_report_createreports	=	$dbc->has_report_permission($report_id,$role_ids,$capability->id);
}	


if ($access_editreports) { 
	
	$capability	=	$dbc->get_capability_by_name('block/ilp:editreport');
	if (!empty($capability)) $access_report_editreports		=	$dbc->has_report_permission($report_id,$role_ids,$capability->id);
}	

if ($access_deletereports) { 
	
	$capability	=	$dbc->get_capability_by_name('block/ilp:deletereport');
	if (!empty($capability))	$access_report_deletereports	=	$dbc->has_report_permission($report_id,$role_ids,$capability->id);
}

if ($access_viewreports) { 
	
	$capability	=	$dbc->get_capability_by_name('block/ilp:viewreport');
	if (!empty($capability))	$access_report_viewreports		=	$dbc->has_report_permission($report_id,$role_ids,$capability->id);
}

if ($access_viewilp) { 
	
	$capability	=	$dbc->get_capability_by_name('block/ilp:viewilp');
	if (!empty($capability))	$access_report_viewilp			=	$dbc->has_report_permission($report_id,$role_ids,$capability->id);
} 

if ($access_viewotherilp) {

	$capability	=	$dbc->get_capability_by_name('block/ilp:viewotherilp');
	if (!empty($capability))	$access_report_viewotherilp		=	$dbc->has_report_permission($report_id,$role_ids,$capability->id);
} 


