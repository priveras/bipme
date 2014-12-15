<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Json extends CI_Controller {

	public function my_products()
	{
		session_start();
		
		$page_num = (strlen($_REQUEST['page_num'])==0) ? 1 : $_REQUEST['page_num'];
		$page_num = ($page_num - 1)*12;
		
		$this->load->model("model_feed");

		$client_id = $this->session->userdata('client_id');

		$products = $this->model_feed->get_my_notifications_paginated($client_id, $page_num, 12);
		
		$data = array();
		$i = 0;
		foreach ($products as $row) {
			$data['products'][$i]['row'] = $row;
			
			$i++;
		}
		
		echo json_encode($data);
	}

	public function login(){

		$the_last_id = 0;

		if ($this->input->server('REQUEST_METHOD') === 'POST'){

			$this->load->model("json_model");

			if($this->input->post('parse_token')){

				$info_user = $this->json_model->get_info_user_by_user_pwd2(strtolower($this->input->post('username')), $this->input->post('password'), $this->input->post('parse_token'));
			}else{

				$info_user = $this->json_model->get_info_user_by_user_pwd(strtolower($this->input->post('username')), $this->input->post('password'));
			}

			
			if ($info_user){
				
				$info_user = $info_user[0];

				$the_info = array(
					"user_id" => $info_user["id"],
					"name" => $info_user["name"],
					"username" => $info_user["username"],
					"email" => $info_user["email"],
					);
			}else{

				$the_info = array(
					"user_id" => "0",
					"name" => "",
					"username" => "",
					"email" => "",
					);
			}			
		}
		
		$data = array("response" => $the_info);
		
		//echo json_encode($data);
		
		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}
	
	public function saveUser(){

		$the_last_id = 0;
		
		if ($this->input->server('REQUEST_METHOD') === 'POST'){
			
			$this->load->model("json_model");
			
			$info_user = $this->json_model->get_info_user($this->input->post('email'));
			$info_user2 = $this->json_model->get_info_user2($this->input->post('username'));
			
			
			if (!$info_user && !$info_user2){
				
				$the_data = array(	
					"username" => $this->input->post('username'), 
					"email" => $this->input->post('email'),
					"password" => $this->input->post('password'),
					"name" => $this->input->post('name'),
					"parse_token" => $this->input->post('parse_token'),
					"device" => $this->input->post('device'),
					);
				
				$the_last_id = $this->json_model->save_user($the_data);
				
				$info_user = $this->json_model->get_info_user_by_id($the_last_id);
				$info_user = $info_user[0];
				
				$the_info = array(
					"user_id" => $info_user["id"],
					"name" => $info_user["name"],
					"username" => $info_user["username"],
					"email" => $info_user["email"],
					);
			}else{
				
				$the_info = array(
					"user_id" => "0",
					"name" => "",
					"username" => "",
					"email" => "",
					);
			}
		}
		
		$data = array("response" => $the_info);
		
		//echo json_encode($data);
		
		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}
	
	/*public function getClients()
	{
		
		$this->load->model("json_model");
        
		$rows = $this->json_model->get_clients();
		
		$i = 0;
		foreach ($rows as $row) {
			$data['rows'][$i] = $row;
			
			$i++;
		}
		
		//echo json_encode($data);
		
		$this->output
    		->set_content_type('application/json')
    		->set_output(json_encode(array("response" => $data)));
	}*/
	
	/*public function getUsers()
	{
		
		$this->load->model("json_model");
        
		$rows = $this->json_model->get_users();
		
		$i = 0;
		foreach ($rows as $row) {
			$data['rows'][$i] = $row;
			
			$i++;
		}
		
		//echo json_encode($data);
		
		$this->output
    		->set_content_type('application/json')
    		->set_output(json_encode(array("response" => $data)));
	}*/
	
	public function getPostsUser(){
		
		$data = array();
		
		if ($this->input->server('REQUEST_METHOD') === 'POST'){
			
			$this->load->model("json_model");
	        
			$rows = $this->json_model->get_posts_user($this->input->post('user_id'));
			
			$i = 0;
			foreach ($rows as $row) {
				$row["time"] = $this->humanTiming(strtotime($row["date"])) . " ago.";

				$row["text"] = $row['message'];
				
				$data['rows'][$i] = $row;
				
				$i++;
			}
		}
		
		//echo json_encode($data);
		
		$this->output->set_content_type('application/json')->set_output(json_encode(array("response" => $data)));
		
	}
	
	private function humanTiming ($time){
	    
	    $time = time() - $time; // to get the time since that moment
	
	    $tokens = array (
	        31536000 => 'y',
	        2592000 => 'mo',
	        604800 => 'wk',
	        86400 => 'd',
	        3600 => 'h',
	        60 => 'm',
	        1 => 's'
	    );
	
	    foreach ($tokens as $unit => $text) {
	        if ($time < $unit) continue;
	        $numberOfUnits = floor($time / $unit);
	        return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'':'');
	    }
	}

	public function deletePostUser()
	{
		$bolFlag = false;
		
		if ($this->input->server('REQUEST_METHOD') === 'POST')
		{
			$this->load->model("json_model");
			$this->json_model->delete_post_user($this->input->post('post_user_id'));
			
			$bolFlag = true;
		}
	
		$data = array("response" => $bolFlag);
		
		//echo json_encode($data);
		
		$this->output
    		->set_content_type('application/json')
    		->set_output(json_encode($data));
	}
}