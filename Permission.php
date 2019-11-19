<?php
/*
 * Class Permission 
 * Admin assigns permission and capabilities
 *
 */
class Permission extends CI_Controller {
	public function __construct(){
        parent::__construct();		
		$this->load->library(array('ion_auth','form_validation'));		
		$this->load->model('Model_common');			
		$this->load->library('form_validation');		
		$this->load->helper('form','url');		
		$this->load->helper('common_helper');		
		$this->my_setting->setSetting();
		$this->load->library(array('ion_auth','form_validation'));
    }
	
	/*
	 * Assign Permission By Admin to other Members
	 * @Param permissionId
	 */
	public function add_permission($editId=''){	
	
		$this->form_validation->set_rules('user_role','User Role', 'required');
		$this->form_validation->set_rules('capability[]','User Capability', 'required');
		if($this->form_validation->run()== false){
			
		}
	
		if((checkUserCapability('all_capability',getUserRole())==false) && (checkUserCapability('create_permission',getUserRole())==false)){
			$this->session->set_flashdata('flashmessage_error', 'you can not perform this action');
			redirect('', 'refresh');
			
		}
		$userRole = $this->Model_common->getdata($table=TABLE_PREFIX .'user_role', $columns = '*', $join = array(), $where_array = array(), $order_by = '', $offset = '', $limit = '');	
		
		if($userRole){			
			foreach($userRole->result() as $userrole){				
				$data['userrole'][] = $userrole;			
			}		
		}
		
		$capability = $this->Model_common->getdata($table=TABLE_PREFIX .'capabilities', $columns = '*', $join = array(), $where_array = array(), $order_by = '', $offset = '', $limit = '');		
		if($capability){			
			foreach($capability->result() as $capability){
				$data['capability'][] = $capability;							
			}		
		}
		
		
		$where_array = array(
			'id' => $editId
		);
		$getPermission = $this->Model_common->getdata($table=TABLE_PREFIX .'assign_permission', $columns='id,role_id,user_capabilities', $join, $where_array, $order_by, $offset, $limit);
		foreach($getPermission->result() as $getPermission){
			$data['permissions'][] = $getPermission;
		}
		
		if($this->input->post('user')){
			$datavalue = array(	
			'role_id'=> $this->input->post('user_role'),
			'user_capabilities'=> json_encode($this->input->post('capability[]')));
			$saveSapability = $this->Model_common->saveData($table='wp_assign_permission',$datavalue);
			if($saveSapability){
				$this->session->flashdata('flashmessage_success','Save Successfully');
				
			}
		} else if($this->input->post('userupdate')) {
			
			if((checkUserCapability('all_capability',getUserRole())==false) && (checkUserCapability('update_permission',getUserRole())==false)){
			$this->session->set_flashdata('flashmessage_error', 'you can not perform this action');
			redirect('', 'refresh');
			}
			
			$where_array = array(
				'id' => $editId
			);
			
			$data_array = array(	
				'role_id'=> $this->input->post('user_role'),
				'user_capabilities'=> json_encode($this->input->post('capability[]'))
			);
			$saveSapability = $this->Model_common->updateData($table=TABLE_PREFIX .'assign_permission', $where_array, $data_array);
			if($saveSapability){
				$this->session->flashdata('flashmessage_success','Update Successfully');
				redirect('users/permission/view');
			}
		}
		$data['page_title'] = ($editId!="")?"Edit Permission":"Add Permission";
		$this->load->view('layout/addpermission',$data);
	}
	
	
	public function delete_permission(){
		if((checkUserCapability('all_capability',getUserRole())==false) && (checkUserCapability('delete_permission',getUserRole())==false)){
			$this->session->set_flashdata('flashmessage_error', 'you can not perform this action');
			redirect('', 'refresh');
		}
		
		$wherearray = array(
				'id'=> $this->input->post('id')
			);
		$result = $this->Model_common->deleteData($table=TABLE_PREFIX ."assign_permission",$wherearray);
			if($result){
				$this->session->flashdata('flashmessage_success','Delete Successfully');
				redirect('view-permission');
			}
	}
	

	public function view_permission(){
		
		if((checkUserCapability('all_capability',getUserRole())==false) && (checkUserCapability('view_permission',getUserRole())==false)){
			$this->session->set_flashdata('flashmessage_error', 'you can not perform this action');
			redirect('', 'refresh');
		}
		
		$table = TABLE_PREFIX .'assign_permission';
		$columns = TABLE_PREFIX .'user_role.user_role, '.TABLE_PREFIX .'assign_permission.id, '.TABLE_PREFIX .'assign_permission.user_capabilities';
		$join = array(
					array(TABLE_PREFIX .'user_role', TABLE_PREFIX .'user_role.id = '.TABLE_PREFIX .'assign_permission.role_id', 'inner')
				);
		$where_array = array();
		$order_by = array(); 
		$offset = '';
		$limit = '';
		$getSapability = $this->Model_common->getdata($table, $columns, $join, $where_array, $order_by, $offset, $limit);

		$data = array();		
		foreach($getSapability->result() as $getSapability){
			$data['permission'][] = $getSapability;		
		}	

		$data['page_title'] = "List Permission";
		$this->load->view('layout/view_permission',$data);	
		
	}
	
	
	public function list_capability(){
		
		if (!$this->ion_auth->logged_in()){
			redirect('auth', 'refresh');
		}
		
		if((checkUserCapability('all_capability',getUserRole())==false) && (checkUserCapability('list_capability',getUserRole())==false)){
			$this->session->set_flashdata('flashmessage_error', 'you can not perform this action');
			redirect('', 'refresh');
		}
		
		
		$data = array();
		$getcapability = $this->Model_common->getData($table=TABLE_PREFIX ."capabilities", $columns='id,capabilities', $join=array(), $where_array=array(), $order_by=array(), $offset='', $limit='');
		foreach($getcapability->result() as $capability){
			$data['capability'][] = $capability;
		}
		$this->form_validation->set_rules('capability', 'Capability', 'required');
		if ($this->form_validation->run() == FALSE){
				
			} else {
				if($this->input->post('submit')){
					
					$capability = $this->input->post('capability');
					$value = array(
						'capabilities' => $capability
					);
					
					$result = $this->Model_common->saveData($table=TABLE_PREFIX ."capabilities",$value);
					if($result){
						$this->session->flashdata('flashmessage_success','Save Successfully');
						redirect('add-capability');
					}
				}
			}
		$data['page_title'] = "List Capability";
		$this->load->view('layout/list_capability',$data); 
	}
	
	public function update_capability(){
		if((checkUserCapability('update_capability',getUserRole())==false) && (checkUserCapability('update_setting',getUserRole())==false)){
			$this->session->set_flashdata('flashmessage_error', 'you can not perform this action');
			redirect('', 'refresh');
		}		
		
		
		if($this->input->post('val')){
			$id = $this->input->post('id');
			$data = array(
				'capabilities'=> $this->input->post('val')
			);
			$wherearray = array(
				'id'=> $id
			);
			$result = $this->Model_common->updateData($table=TABLE_PREFIX ."capabilities",$wherearray,$data);
			if($result){
				
				$this->session->flashdata('flashmessage_success','Update Successfully');
				redirect('add-capability');
			}
		}
	}
	
	
	public function delete_capability(){
		if((checkUserCapability('delete_capability',getUserRole())==false) && (checkUserCapability('update_setting',getUserRole())==false)){
			$this->session->set_flashdata('flashmessage_error', 'you can not perform this action');
			redirect('', 'refresh');
		}
		
		
		if($this->input->post('id')){
			$wherearray = array(
				'id'=> $this->input->post('id')
			);
			$result = $this->Model_common->deleteData($table=TABLE_PREFIX ."capabilities",$wherearray);
			if($result){
				$this->session->flashdata('flashmessage_success','Delete Successfully');
				redirect('add-capability');
			}
		}
	}
		
	

}
