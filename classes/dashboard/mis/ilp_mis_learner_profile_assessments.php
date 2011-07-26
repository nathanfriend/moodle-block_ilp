<?php 
require_once($CFG->dirroot.'/blocks/ilp/classes/dashboard/ilp_mis_plugin.php');

class ilp_mis_learner_profile_assessments extends ilp_mis_plugin	{

	protected 	$fields;
	protected 	$mis_user_id;
	
	/**
	 * 
	 * Constructor for the class
	 * @param array $params should hold any vars that are needed by plugin. can also hold the 
	 * 						the connection string vars if they are different from those specified 
	 * 						in the mis connection
	 */
	
 	function	__construct($params=array())	{
 		parent::__construct($params);
 		
 		$this->tabletype	=	get_config('block_ilp','mis_learner_assessments_tabletype');
 		$this->fields		=	array();
 	}
 	
 	/**
 	 * 
 	 * @see ilp_mis_plugin::display()
 	 */
 	function display()	{
 		global $CFG;
 		
 		if (!empty($this->data)) {
 			//buffer output   
			ob_start();
 			
 			require_once($CFG->dirroot.'/blocks/ilp/classes/dashboard/mis/ilp_mis_learner_profile_assessments.html');
 			
 			$pluginoutput = ob_get_contents();
 			
	        ob_end_clean();
 			
 			return $pluginoutput;
 			
 		} else {
 			//print configuration needed message 
 		}
 		
 	} 
 	
 	/**
 	 * Retrieves data from the mis 
 	 * 
 	 * @param	$mis_user_id	the id of the user in the mis used to retrieve the data of the user
 	 * @param	$user_id		the id of the user in moodle
 	 *
 	 * @return	null
 	 */
 	
 	
    public function set_data( $mis_user_id ){
    		
    		$this->mis_user_id	=	$mis_user_id;
    		
    		$table	=	get_config('block_ilp','mis_learner_assessments_table');
    		
			if (!empty($table)) {

 				$sidfield	=	get_config('block_ilp','mis_learner_assessments_studentid');
 			
 				$keyfields	=	array($sidfield	=> array('=' => $mis_user_id));
 				
 				$this->fields		=	array();
 				
 				if 	(get_config('block_ilp','mis_learner_assessments_studentid')) 	$this->fields['studentid']	=	get_config('block_ilp','mis_learner_assessments_studentid');
 				if 	(get_config('block_ilp','mis_learner_assessments_maths')) 	$this->fields['maths']	=	get_config('block_ilp','mis_learner_assessments_maths');
 				if 	(get_config('block_ilp','mis_learner_assessments_english')) 		$this->fields['english']	=	get_config('block_ilp','mis_learner_assessments_english');
 				if 	(get_config('block_ilp','mis_learner_assessments_ict')) 		$this->fields['ict']	=	get_config('block_ilp','mis_learner_assessments_ict');
 				if 	(get_config('block_ilp','mis_learner_assessments_study')) 		$this->fields['study']	=	get_config('block_ilp','mis_learner_assessments_study');
 				
 				$this->data	=	$this->dbquery( $table, $keyfields, $this->fields);
 				
 				//we only need the first record so pass it back 
 				$this->data	= (!empty($this->data)) ?  array_shift($this->data)	:	$this->data; 	
 				
 			} else {
 				var_dump('table not set');
 			}
    }
 	
	/**
     * Adds settings for this plugin to the admin settings
     * @see ilp_mis_plugin::config_settings()
     */
    public function config_settings(&$settings)	{
    	global $CFG;
    	
    	$link ='<a href="'.$CFG->wwwroot.'/blocks/ilp/actions/edit_plugin_config.php?pluginname=ilp_mis_learner_assessments&plugintype=mis">'.get_string('ilp_mis_learner_assessments_pluginnamesettings', 'block_ilp').'</a>';
		$settings->add(new admin_setting_heading('block_ilp_mis_learner_assessments', '', $link));
 	 }
    
 	  	 /**
 	  * Adds config settings for the plugin to the given mform
 	  * @see ilp_plugin::config_form()
 	  */
 	 function config_form(&$mform)	{
 	 	
 	 	$this->config_text_element($mform,'mis_learner_assessments_table',get_string('ilp_mis_learner_assessments_table', 'block_ilp'),get_string('ilp_mis_learner_assessments_tabledesc', 'block_ilp'),'');
 	 	
 	 	$this->config_text_element($mform,'mis_learner_assessments_studentid',get_string('ilp_mis_learner_assessments_studentid', 'block_ilp'),get_string('ilp_mis_learner_assessments_studentiddesc', 'block_ilp'),'studentID');
 	 	
 	 	$this->config_text_element($mform,'mis_learner_assessments_maths',get_string('ilp_mis_learner_assessments_maths', 'block_ilp'),get_string('ilp_mis_learner_assessments_mathsdesc', 'block_ilp'),'mathsResult');

 	 	$this->config_text_element($mform,'mis_learner_assessments_english',get_string('ilp_mis_learner_assessments_english', 'block_ilp'),get_string('ilp_mis_learner_assessments_englishdesc', 'block_ilp'),'englishResult');
 	 	
 	 	$this->config_text_element($mform,'mis_learner_assessments_ict',get_string('ilp_mis_learner_assessments_ict', 'block_ilp'),get_string('ilp_mis_learner_assessments_ictdesc', 'block_ilp'),'ictResult');
 	 	
 	 	$this->config_text_element($mform,'mis_learner_assessments_study',get_string('ilp_mis_learner_assessments_study', 'block_ilp'),get_string('ilp_mis_learner_assessments_studydesc', 'block_ilp'),'studySupport');
 	 	
 	 	$options = array(
    		 ILP_MIS_TABLE => get_string('table','block_ilp'),
    		 ILP_MIS_STOREDPROCEDURE	=> get_string('storedprocedure','block_ilp') 
    	);
 	 	
 	 	$this->config_select_element($mform,'mis_learner_assessments_tabletype',$options,get_string('ilp_mis_learner_assessments_tabletype', 'block_ilp'),get_string('ilp_mis_learner_assessments_tabletypedesc', 'block_ilp'),1);
 	 	
 	 	$options = array(
    		ILP_ENABLED => get_string('enabled','block_ilp'),
    		ILP_DISABLED => get_string('disabled','block_ilp')
    	);
 	
 	 	$this->config_select_element($mform,'ilp_mis_learner_profile_assessments_pluginstatus',$options,get_string('ilp_mis_learner_profile_assessments_pluginstatus', 'block_ilp'),get_string('ilp_mis_learner_profile_assessments_pluginstatusdesc', 'block_ilp'),0);
 	 	
 	 }
    
	/**
	 * Adds the string values from the tab to the language file
	 *
	 * @param	array &$string the language strings array passed by reference so we  
	 * just need to simply add the plugins entries on to it
	 */
	 function language_strings(&$string) {

        $string['ilp_mis_learner_assessments_pluginname']						= 'Learner Profile Iniital Assessment';
        $string['ilp_mis_learner_assessments_pluginnamesettings']				= 'Iniital Assessment Configuration';
        
        $string['ilp_mis_learner_assessments_table']							= 'MIS table';
        $string['ilp_mis_learner_assessments_tabledesc']						= 'The table in the MIS where the data for this plugin will be retrieved from';
        
        $string['ilp_mis_learner_assessments_studentid']						= 'Student ID field';
        $string['ilp_mis_learner_assessments_studentiddesc']					= 'The field that will be used to find the student';
        
        $string['ilp_mis_learner_assessments_maths']							= 'Maths data field';
        $string['ilp_mis_learner_assessments_mathsdesc']						= 'The field that holds maths data';
        
        $string['ilp_mis_learner_assessments_english']							= 'English data field';
        $string['ilp_mis_learner_assessments_englishdesc']						= 'English data';
        
        
        
        $string['ilp_mis_learner_assessments_ict']								= 'ICT data field';
        $string['ilp_mis_learner_assessments_ictdesc']							= 'The field that holds ICT data';
        
        $string['ilp_mis_learner_assessments_study']							= 'Study support field';
        $string['ilp_mis_learner_assessments_studydesc']						= 'The field that holds study support data';
                
        $string['ilp_mis_learner_assessments_tabletype']						= 'Table type';
        $string['ilp_mis_learner_assessments_tabletypedesc']					= 'Does this plugin connect to a table or stored procedure';        
        
        $string['ilp_mis_learner_profile_assessments_pluginstatus']				= 'Status';
        $string['ilp_mis_learner_profile_assessments_pluginstatusdesc']			= 'Is the block enabled or disabled';
        
        
        $string['ilp_mis_learner_profile_assessments_disp_assessments']				= 'Initial Assessments';
        $string['ilp_mis_learner_profile_assessments_disp_maths']					= 'Maths';
        $string['ilp_mis_learner_profile_assessments_disp_english']					= 'English';
        $string['ilp_mis_learner_profile_assessments_disp_ict']						= 'ICT';
        $string['ilp_mis_learner_profile_assessments_disp_study']					= 'Study Support';
        
        
        return $string;
    }

    
    function plugin_type()	{
    	return 'learnerprofile';
    }
 	



}

?>