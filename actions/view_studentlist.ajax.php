<?php
/**
 * Ajax file for view_students
 *
 * @copyright &copy; 2011 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package ILP
 * @version 2.0
 */


require_once('../configpath.php');

global $USER, $CFG, $SESSION, $PARSER, $PAGE, $OUTPUT;

//include the default class
require_once($CFG->dirroot.'/blocks/ilp/classes/tables/ilp_ajax_table.class.php');

//get the id of the course that is currently being used if set
$course_id 	= $PARSER->optional_param('course_id', 0, PARAM_INT);

//get the tutor flag
$tutor		=	$PARSER->optional_param('tutor', 0, PARAM_INT);

//get the status_id if set
$status_id		=	$PARSER->optional_param('status_id', 0, PARAM_INT);

// instantiate the db
$dbc = new ilp_db();




// set up the flexible table for displaying the portfolios
$flextable = new ilp_ajax_table('student_list');



$flextable->define_baseurl($CFG->wwwroot."/blocks/ilp/actions/view_studentlist.php?course_id={$course_id}&tutor={$tutor}&status_id={$status_id}");
$flextable->define_ajaxurl($CFG->wwwroot."/blocks/ilp/actions/view_studentlist.ajax.php?course_id={$course_id}&tutor={$tutor}&status_id={$status_id}");




// set the basic details to dispaly in the table
$headers = array(
	get_string('userpicture', 'block_ilp'),
    get_string('name', 'block_ilp'),
    get_string('status', 'block_ilp')
);

$columns = array(
	'picture',
  	'fullname',
	'u_status'
);


//we need to check if the mis plugin has been setup if it has we will get the attendance and punchuality figures

//include the attendance 
$misclassfile	=	$CFG->docroot."/blocks/ilp/classes/mis.class.php";

//we will assume the mis data is unavailable until proven otherwise
$misavailable = false;

//only proceed if a mis file has been created
if (file_exists($misclassfile)) {
	
	//create an instance of the MIS class
	$misclass	=	new mis();
	
	$punch_method1 = array($misclass, 'get_total_punchuality');
	$punch_method2 = array($misclass, 'get_student_punchuality');
	$attend_method1 = array($misclass, 'get_total_attendance');
	$attend_method2 = array($misclass, 'get_student_attendance');
        
	//check whether the necessary functions have been defined
	 if (is_callable($attend_method1,true) && is_callable($attend_method2,true)) {
	 	$headers[] = get_string('attendance','block_ilp');
	 	$columns[] = 'u_attendcance';
	 	$misattendavailable = true;
	 }	
	 
	 //check whether the necessary functions have been defined
	 if (is_callable($punch_method1,true) && is_callable($punch_method2,true)) {
	 	$headers[] = get_string('punctulaity','block_ilp');
		$columns[] = 'u_punctuality';
		$mispunchavailable = true;
	 }
	 
}

//get all enabled reports in this ilp
$reports		=	$dbc->get_reports(ILP_ENABLED);
			
//we are going to create headers and columns for all enabled reports 
foreach ($reports as $r) {
	$headers[]	=	$r->name;
	$columns[]	=	$r->id;
}

$headers[]	=	get_string('lastupdated','block_ilp');
$columns[]	=	'lastupdated';

$headers[]	=	'';
$columns[]	=	'view';

//define the columns and the headers in the flextable
$flextable->define_columns($columns);
$flextable->define_headers($headers);



$flextable->set_attribute('summary', get_string('studentslist', 'block_ilp'));
$flextable->set_attribute('cellspacing', '0');
$flextable->set_attribute('class', 'generaltable fit');
$flextable->define_fragment('studentlist');
$flextable->initialbars(true);
$flextable->setup();

if (!empty($course_id)) {
    $users	=	$dbc->get_course_users($course_id);
} else {
	$users	=	$dbc->get_user_tutees($USER->id);
}

$students	=	array();

foreach ($users	as $u) {
	$students[]	=	$u->id;
}


$studentslist	=	$dbc->get_students_matrix($flextable,$students,$status_id);

//get the default status item which will be used as the status for students who
//have not entered their ilp and have not had a status assigned
$default_status_item_id	=	get_config('block_ilp', 'defaultstatusitem');

//get the status item record
$default_status_item	=	$dbc->get_status_item_by_id($default_status_item_id);


$status_item	=	(!empty($default_status_item)) ? $default_status_item->name : get_string('unknown','block_ilp');

//this is needed if the current user has capabilities in the course context, it allows view_main page to view the user
//in the course context
$courseparam	=	(!empty($course_id)) ? "&course_id={$course_id}": '';

if(!empty($studentslist)) {
	foreach($studentslist as $stu) {
    	$data	=	array();
    	
    	$data['picture']	=	$OUTPUT->user_picture($stu,array('return'=>true,'size'=>50));
    	$data['fullname']	=	"<a href='{$CFG->wwwroot}/user/view.php?id={$stu->id}' class=\"userlink\">".fullname($stu)."</a>";
    	//if the student status has been set then show it else they have not had there ilp setup
    	//thus there status is the default
    	$data['u_status'] =   (!empty($stu->u_status)) ? $stu->u_status : $status_item; 

    	if (!empty($misattendavailable)) {
    		$total 		=	$misclass->get_total_attendance();
    		$actual 	=	$misclass->get_student_attendance();
    		//we only want to try to find the percentage if we can get the total possible
    		// attendance else set it to 0;
    		$data['u_attendcance'] =	(!empty($total)) ? $actual / $total	* 100 : 0 ;
    	}
    	
    	if (!empty($misattendavailable)) {
    		$total 		=	$misclass->get_total_attendance();
    		$actual 	=	$misclass->get_student_attendance();
    		//we only want to try to find the percentage if we can get the total possible
    		// attendance else set it to 0;
    		$data['u_attendcance'] =	(!empty($total)) ? $actual / $total	* 100 : 0 ;
    	}

      	foreach ($reports as $r) {

      		//get the number of this report that have been created
      		$createdentries	=	$dbc->count_report_entries($r->id,$stu->id);
      		
      		$reporttext	=	"{$createdentries} ".$r->name;
      		
      		//check if the report has a state field
      		if ($dbc->has_plugin_field($r->id,'ilp_element_plugin_state')) {
      			
      			//count the number of entries with a pass state
      			$achievedentries = $dbc->count_report_entries_with_state($r->id,$stu->id,ILP_PASSFAIL_PASS);
      			$reporttext	= $achievedentries. "/".$createdentries." ".get_string('achieved','block_ilp');	
      		}
      		
			$data[$r->id]	=	$reporttext;	
		}
		
    	$lastentry	=	$dbc->get_lastupdate($stu->id);
		$data['lastupdated']	=	(!empty($lastentry->timemodified)) ?userdate($lastentry->timemodified , get_string('strftimedate', 'langconfig')) : get_string('notapplicable','block_ilp');
		$data['view']	=	"<a href='{$CFG->wwwroot}/blocks/ilp/actions/view_main.php?user_id={$stu->id}{$courseparam}' >".get_string('viewplp','block_ilp')."</a>";
    	 $flextable->add_data_keyed($data);
    }
}    
 
$flextable->print_html();