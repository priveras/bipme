<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/*ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);*/


class Model_feed extends CI_Model {

	public function __construct(){

		$this->load->database();
	}

	public function get_lists(){

		$client_id = $this->session->userdata('client_id');

		$sql = sprintf("SELECT l.id, l.list, l.description, l.client_id, c.name, c.username
			FROM lists l
			JOIN clients c ON c.id = l.client_id
			WHERE l.client_id = %d ORDER BY l.id DESC", $client_id);

		$query = $this->db->query($sql);
		$lists = $query->result_array();
    
		return $lists;
	}

	public function count_lists_users(){

		$client_id = $this->session->userdata('client_id');

		$sql = sprintf("SELECT cu.client_id, cu.list_id
			FROM clients_users cu
			WHERE cu.client_id = %d", $client_id);

		$query = $this->db->query($sql);

		$query = $query->result_array();

		return $query;
	}

	public function get_lists_users($id){

		$client_id = $this->session->userdata('client_id');

		$sql = sprintf("SELECT l.id, l.list, l.client_id, cu.user_id, cu.email, u.name, u.username, cu.id as cuid
			FROM lists l
			JOIN clients_users cu ON cu.list_id = l.id
			JOIN users u ON u.id = cu.user_id
			JOIN clients c ON c.id = cu.client_id
			WHERE l.id = %d AND c.id = %d ORDER BY name ASC", $id, $client_id);

		$query = $this->db->query($sql);
		$lists_users = $query->result_array();
    
		return $lists_users;
	}

	public function get_temp_lists(){

		$client_id = $this->session->userdata('client_id');

		$sql = sprintf("SELECT tl.list, tl.client_id, tl.id, tl.list_key
			FROM temp_lists tl
			WHERE tl.client_id = %d ORDER BY id DESC", $client_id);

		$query = $this->db->query($sql);
		$lists = $query->result_array();
    
		return $lists;
	}

	public function get_userslist(){

		$client_id = $this->session->userdata('client_id');

		$sql = sprintf("SELECT cu.user_id, cu.id, u.name
			FROM clients_users cu
			JOIN users u ON u.id = cu.user_id
			WHERE cu.client_id = %d", $client_id);

		$query = $this->db->query($sql);
		$userslist = $query->result_array();
    
		return $userslist;
	}

	public function get_notifications(){

		$client_id = $this->session->userdata('client_id');

		$sql = sprintf("SELECT pu.id, pu.client_id, pu.user_id, pu.message, pu.date, u.username, u.name
			FROM posts_users2 pu
			JOIN users u ON u.id = pu.user_id
			WHERE pu.client_id = %d ORDER BY pu.id DESC LIMIT 12", $client_id);

		$query = $this->db->query($sql);
		$notifications = $query->result_array();
    
		return $notifications;
	}

	public	function get_my_notifications_paginated($client_id, $offset, $limit)
    {

    	$sql   = sprintf('SELECT pu.id, pu.client_id, pu.user_id, pu.message, pu.date, u.username, u.name
				FROM posts_users2 pu
				JOIN users u ON u.id = pu.user_id
				WHERE pu.client_id = %d ORDER BY pu.id DESC limit %d, %d', $client_id, $offset, $limit);
		
    	$query = $this->db->query($sql);
		$query = $query->result_array();
		return $query;
    }

    public function get_info(){

    	$sql   = sprintf('SELECT *
				FROM clients
				');
		
    	$query = $this->db->query($sql);
		$query = $query->result_array();
		return $query;    	
    }

    public function get_temp_clients_users(){

    	$client_id = $this->session->userdata('client_id');


		$sql = sprintf("SELECT tcu.id, tcu.user_id, tcu.client_id, tcu.list_id, u.id AS user_id, u.username, u.name, c.id AS client_id, c.name AS client, l.id AS list_id, l.list
			FROM temp_clients_users tcu
			JOIN users u ON u.id = tcu.user_id
			JOIN clients c ON c.id = tcu.client_id
			JOIN lists l ON l.id = tcu.list_id
			WHERE tcu.client_id = %d ORDER BY tcu.id DESC", $client_id);

		$query = $this->db->query($sql);
		$temp_clients_users = $query->result_array();
    
		return $temp_clients_users;
    }

    public function get_users($id){
    	
    	$this->db->where('id', $id);
    	$query = $this->db->get('users');

    	$query = $query->result_array();

    	return $query;

    }

    public function update($id, $data){

    	$this->db->where('id', $id);
    	$query = $this->db->update('users', $data);

    	if($query){
    		return true;
    	}else{
    		return false;
    	}

    }
}





