<?php

class Model_feed extends CI_Model{

	public function get_clients(){

		$this->db->order_by("name", "asc");
		$query = $this->db->get('clients');

		$query = $query->result_array();

		$i = 0;
		foreach($query as $row){

			$client_category = $query[$i]['category'];

			switch ($client_category) {

        		case "1":
        		$query[$i]['privacy'] = 0;
        		break;

        		case "2":
        		$query[$i]['privacy'] = 0;
        		break;

        		case "3":
        		$query[$i]['privacy'] = 0;
        		break;

	        	case "4":
	        	$query[$i]['privacy'] = 1;
	        	break;

        		case "5":
        		$query[$i]['privacy'] = 1;
        		break;

        		case "6":
        		$query[$i]['privacy'] = 1;
        		break;

        		case "7":
        		$query[$i]['privacy'] = 2;
        		break;

        		case "8":
        		$query[$i]['privacy'] = 2;
        		break;

        		case "9":
        		$query[$i]['privacy'] = 2;
        		break;

        		default:
        		$query[$i]['privacy'] = 2;
        	}		
        	$i++;
		}

		return $query;
	}

	public function get_lists($user_id, $client_id){

		$this->db->where('client_id', $client_id);
		$query = $this->db->get('lists');

		$query = $query->result_array();

		$i = 0;
		foreach ($query as $row) {

			$list_id = $row['id'];

			$this->db->where('user_id', $user_id);
			$this->db->where('client_id', $client_id);
			$this->db->where('list_id', $list_id);

			$query2 = $this->db->get('temp_clients_users');

			if($query2->num_rows() == 1){
				$query[$i]['style'] = 'subscribe_temp';
			}else{
				$this->db->where('user_id', $user_id);
				$this->db->where('client_id', $client_id);
				$this->db->where('list_id', $list_id);

				$query3 = $this->db->get('clients_users');	

				if($query3->num_rows() == 1){
					$query[$i]['style'] = 'subscribe_active';
				}else{
					$query[$i]['style'] = 'subscribe_inactive';
				}
			}

			$i++;
		}

		return $query;
	}

	public function get_client($client_id){

		$this->db->where('id', $client_id);
		$query = $this->db->get('clients');

		$query = $query->result_array();

		return $query;
	}

	public function get_user_id($username, $user_id){

		$this->db->where('id', $user_id);
		$query = $this->db->get('users');

		$row = $query->row();

		$username2 = $row->username;
		$user_id = $row->id;

		if(md5($username2) == $username){
			return $user_id;
		}else{
			return false;
		}
	}

	public function insert_temp_clients_users($user_id, $client_id, $list_id){

		$data = array(
			'user_id' => $user_id,
			'client_id' =>$client_id,
			'list_id' => $list_id
			);

		$this->db->where('user_id', $user_id);
		$this->db->where('client_id', $client_id);
		$this->db->where('list_id', $list_id);

		$query = $this->db->get('temp_clients_users');

		if($query->num_rows() == 0){
			$this->db->where('user_id', $user_id);
			$this->db->where('client_id', $client_id);
			$this->db->where('list_id', $list_id);

			$query2 = $this->db->get('clients_users');

			if($query2->num_rows() == 0){
				if($this->db->insert('temp_clients_users', $data)){
					return true;
				}else{
					return false;
				}
			}else{

				$this->db->where('user_id', $user_id);
				$this->db->where('client_id', $client_id);
				$this->db->where('list_id', $list_id);

				$this->db->delete('clients_users');

				return false;
			}
		}else{
			$this->db->where('user_id', $user_id);
			$this->db->where('client_id', $client_id);
			$this->db->where('list_id', $list_id);

			$this->db->delete('temp_clients_users');

			return false;
		}
	}

	public function get_list_text($list_id){

		$this->db->where('id', $list_id);
		$query = $this->db->get('lists');

		if($query){

			$row = $query->row();

			$data = array(
				'list' => $row->list
				);

			return $data;
		}
	}

	public function insert_clients_users($user_id, $client_id, $list_id){

		$data = array(
			'user_id' => $user_id,
			'client_id' =>$client_id,
			'list_id' => $list_id
			);

		$this->db->where('user_id', $user_id);
		$this->db->where('client_id', $client_id);
		$this->db->where('list_id', $list_id);

		$query = $this->db->get('clients_users');

		if($query->num_rows() == 0){

			if($this->db->insert('clients_users', $data)){
				return true;
			}else{
				return false;
			}

		}else{
			$this->db->where('user_id', $user_id);
			$this->db->where('client_id', $client_id);
			$this->db->where('list_id', $list_id);

			$this->db->delete('clients_users');

			return false;
		}
	}
} 