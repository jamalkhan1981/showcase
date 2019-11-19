<?php
class Setting extends CI_Controller {
	
    public function __construct()
    {
         parent::__construct();
		 $this->load->library(array('ion_auth','form_validation'));
		 
		 $this->load->model('app_setting');
		 $this->load->library('form_validation');
		 $this->load->model('Model_common');
		 $this->load->helper(array('form','common_helper'));
		 $this->my_setting->setSetting();
    }
	
	
	
	function add_setting(){
		
		$values = array();
		$data = array();
		if (!$this->ion_auth->logged_in())
		{
			redirect('auth/login', 'refresh');
		}	
		
		if((checkUserCapability('all_capability',getUserRole())==false) && (checkUserCapability('create_setting',getUserRole())==false)){
			$this->session->set_flashdata('flashmessage_error', 'you can not perform this action');
			redirect('', 'refresh');
		}
	
		//call function getSetting for display all site setting
		$data['setting'] = $this->get_setting();
		
		if($this->input->post('save')){
			
				$key = $this->input->post('key[]');
				$slug = $this->input->post('slug[]');
				$value = $this->input->post('value[]');
				$count = count($key);
				
				for($i=0;$i<$count;$i++){
					if($this->my_setting->duplicateSlug($slug[$i])){
						$this->session->set_flashdata('flashmessage_error', 'Already Exist');
						redirect('settings/app-settings', 'refresh');
					} else {
						array_push($values,[$key[$i],$slug[$i],$value[$i]]);
					}
				}
				$this->my_setting->appSetting($values);
			} 
		$data['page_title'] = "App Settings";
		$this->load->view('layout/add_setting',$data);
	}
	
	function get_setting(){
		
		$data = array();
		if((checkUserCapability('all_capability',getUserRole())==false) && (checkUserCapability('view_setting',getUserRole())==false)){
			$this->session->set_flashdata('flashmessage_error', 'you can not perform this action');
			redirect('', 'refresh');
		}
		
		
		$table = TABLE_PREFIX .'options';
		$where = '';
		$allSetting = $this->app_setting->getValue($table, $where);
		
		foreach($allSetting->result() as $setting) {
			array_push($data,$setting);
		} 
		
		return $data;
	}
	
	function update_setting(){
		
		if((checkUserCapability('all_capability',getUserRole())==false) && (checkUserCapability('update_setting',getUserRole())==false)){
			$this->session->set_flashdata('flashmessage_error', 'you can not perform this action');
			redirect('', 'refresh');
		}
		
		$data = array();
		if($this->input->post('key')){
			$data = array(
				'option_name' => $this->input->post('key'),
				'option_slug' => $this->input->post('slug'),
				'option_value' => $this->input->post('val')
			);
		}
			$table = TABLE_PREFIX .'options';
			$where = array('option_id'=>$this->input->post('option_id'));
			
			if($this->my_setting->duplicateSlug($this->input->post('slug'))){
				$updateSetting = $this->app_setting->updateValue($table, $where, $data);
				if($updateSetting){
					echo "Update successfully";
				}
			} else {
				echo "Already Exist";
			}
		}
	

}
