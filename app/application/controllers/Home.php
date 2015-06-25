<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

	protected $params = array('model'=>'members');

	

	public function __construct()
	{
	    parent::__construct();

        $this->load->library('template');
        $this->load->library('session');
        $this->load->model('member_model');
	    $this->load->library('authlibrary',$this->params);
	    
	}//

	public function index()
	{	

		if($this->authlibrary->is_login()){
			// untuk mengecek login belum, jika belum di redirect ke login
			$this->authlibrary->check_login();
			$this->authlibrary->check_role('members');
			
			$this->template->load('template/template_main','dashboard');
		}else{


			$this->template->load('template/template_auth','home');
		}

	}
	
	
}