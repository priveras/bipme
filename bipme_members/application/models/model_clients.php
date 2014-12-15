<?php

class Model_clients extends CI_Model{

	public function add_temp_client($client_key){

		$data = array(
			'username' => strtolower($this->input->post('username')),
			'email' => $this->input->post('email'),
			'name' => $this->input->post('name'),
			'image' => $this->input->post('image'),
			'tel' => $this->input->post('tel'),
			'category' => $this->input->post('category'),
			'password' => md5($this->input->post('password')),
			'client_key' => $client_key
			);

		$query = $this->db->insert('temp_clients', $data);
		if($query){
			$i = 4;
			$this->load->model('model_add');
			$this->model_add->send_admin_notification($i);
			return true;
		}else{
			return false;
		}
	}

	public function is_key_valid($client_key){
		$this->db->where('client_key', $client_key);
		$query = $this->db->get('temp_clients');

		if($query->num_rows() == 1){
			return true;
		}else{
			return false;
		}
	}

	public function add_client($client_key){

		$this->db->where('client_key', $client_key);
		$temp_client = $this->db->get('temp_clients');

		if($temp_client){
			$row = $temp_client->row();

			$data = array(
				'username' => $row->username,
				'email' => $row->email,
				'password' => $row->password,
				'name' => $row->name,
				'image' => $row->image,
				'tel' => $this->input->post('tel'),
				'category' => $row->category
				);

			$did_add_client = $this->db->insert('clients', $data);
		}

		if($did_add_client){
			$this->db->where('client_key', $client_key);
			$this->db->delete('temp_clients');
			return true;
		}else{
			return false;
		}
	}

	public function can_log_in(){

		$this->db->where('username', $this->input->post('username'));
		$this->db->where('password', md5($this->input->post('password')));

		$query = $this->db->get('clients');

		if($query->num_rows() == 1){

			$row = $query->row();

			$data = array(
				'client_id' => $row->id,
				'client_username' => $row->username,
				'client_name' => $row->name,
				'client_email' => $row->email,
				'client_image' => $row->image,
				'client_category' => $row->category,
				'client_tel' => $row->tel
				);

			$this->session->set_userdata('client_id', $data['client_id']);
			$this->session->set_userdata('client_username', $data['client_username']);
			$this->session->set_userdata('client_name', $data['client_name']);
			$this->session->set_userdata('client_image', $data['client_image']);
			$this->session->set_userdata('client_category', $data['client_category']);
			$this->session->set_userdata('client_email', $data['client_email']);
			$this->session->set_userdata('client_tel', $data['client_tel']);

			$this->suscriptionType();
			
			return true;
		}else{
			return false;
		}
	}

	public function suscriptionType(){

		$client_category = $this->session->userdata('client_category');


		switch ($client_category) {

        	case "1":
        	$l = 3;
        	$n = 750;
        	$p = "Eric Clapton Stadium";
        	$privacy = 0;
        	break;

        	case "2":
        	$l = 5;
        	$n = 1500; 
        	$p = "Jimmy Page Stadium";
        	$privacy = 0;
        	break;

        	case "3":
        	$l = 100;
        	$n = 100000;
        	$p = "Jimmy Hendrix Stadium";
        	$privacy = 0;
        	break;

        	case "4":
        	$l = 3;
        	$n = 750;
        	$p = "Eric Clapton Unplugged";
        	$privacy = 1;
        	break;

        	case "5":
        	$l = 5;
        	$n = 1500;
        	$p = "Jimmy Page Unplugged";
        	$privacy = 1;
        	break;

        	case "6":
        	$l = 100;
        	$n = 100000;
        	$p = "Jimmy Hendrix Unplugged";
        	$privacy = 1;
        	break;

        	case "7":
        	$l = 3;
        	$n = 750;
        	$p = "Eric Clapton Backstage";
        	$privacy = 2;
        	break;

        	case "8":
        	$l = 5;
        	$n = 1500;
        	$p = "Jimmy Page Backstage";
        	$privacy = 2;
        	break;

        	case "9":
        	$l = 100;
        	$n = 100000;
        	$p = "Jimmy Hendrix Backstage";
        	$privacy = 2;
        	break;

        	default:
        	$l = 100;
        	$n = 100000;
        	$p = "Papo";
        	$privacy = 2;
        }

        $this->session->set_userdata('client_lists', $l);
        $this->session->set_userdata('client_notifications', $n);
        $this->session->set_userdata('client_suscription', $p);
        $this->session->set_userdata('client_privacy', $privacy);
	}
} 