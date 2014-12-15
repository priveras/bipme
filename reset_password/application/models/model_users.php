<?php

class Model_users extends CI_Model{

	public function get_user_info($username_email){

		$this->db->where('username', $username_email);
		$this->db->or_where('email', $username_email); 
		$query = $this->db->get('users');

		if($query->num_rows() == 1){

			$row = $query->row();

			$data = array(
				'id' => $row->id,
				'username' => $row->username,
				'name' => $row->name,
				'email' => $row->email,
				'password' => $row->password
				);

			$this->session->set_userdata('id', $data['id']);
			$this->session->set_userdata('username', $data['username']);
			$this->session->set_userdata('name', $data['name']);
			$this->session->set_userdata('email', $data['email']);
			$this->session->set_userdata('password', $data['password']);

			return $data;

		}else{
			$this->session->set_userdata('message', "We don't have that record, please try again");
			return false;
		}
	}

	public function reset_password($password, $key, $username_email){

		$this->db->where('username', $username_email);
		$this->db->or_where('email', $username_email); 
		$query = $this->db->get('users');

		if($query){

			$row = $query->row();

			$email = $row->email;

			if(md5($email) == $key){

				$data = array(
					'password' => md5($password),
					);

				$this->db->where('username', $username_email);
				$this->db->or_where('email', $username_email); 
				$this->db->update('users', $data); 

				return true;
			}else{
				return false;
			}
		}
	}
} 