<?php

class Model_add extends CI_Model{

	public function get_user_info($username){

		if(isset($username)) {

			$this->db->where('username', $username);
			$query_user = $this->db->get('users');

			if($query_user->num_rows() == 1){

				if($query_user){

					$row_user = $query_user->row();

					$data = array(
						'user_id' => $row_user->id,
						'parse_token' => $row_user->parse_token,
						'user_username' => $row_user->username,
						'device' => $row_user->device
						);

					$this->session->set_userdata('user_username', $data['user_username']);

					return $data;
				}
			}else{
				$this->session->set_userdata('message2', 'Ese usuario no existe');
				return false;
			}
		}
	}

	public function insert_notification($data, $message){

		unset ($data['parse_token']);
		unset ($data['user_username']);
		unset ($data['device']);
		$data['client_id'] = $this->session->userdata['client_id'];
		$data['message'] = $message;

		$query = $this->db->insert('posts_users', $data);
		$query2 = $this->db->insert('posts_users2', $data);

		if($query && $query2){
			return true;
		}else{
			return false;
		}
	}

	public function insert_welcome_notification($data, $message, $identifier){

		unset ($data['parse_token']);
		unset ($data['user_username']);
		unset ($data['device']);
		$data['client_id'] = $this->session->userdata['client_id'];
		$data['message'] = $message;

		if($identifer == 0){

			$this->db->where('client_id', $data['client_id']);
			$this->db->where('user_id', $data['user_id']);
			$this->db->where('message', $data['message']);

			$query = $this->db->get('posts_users2');

			if($query->num_rows() == 1){
				return false;
			}else{
				$query1 = $this->db->insert('posts_users', $data);
				$query2 = $this->db->insert('posts_users2', $data);

				if($query1 && $query2){
					return true;
				}else{
					return false;
				}		
			}
		}else{
			$query1 = $this->db->insert('posts_users', $data);
			$query2 = $this->db->insert('posts_users2', $data);

			if($query1 && $query2){
				return true;
			}else{
				return false;
			}
		}
	}

	public function insert_bulk_notifications($message, $list_id){

		$client_id = $this->session->userdata['client_id'];

		$sql = sprintf("SELECT cu.id, cu.user_id, cu.client_id, cu.list_id, cu.email, u.name, u.parse_token, u.device, u.id
			FROM clients_users cu
			JOIN users u ON u.id = cu.user_id
			WHERE cu.client_id = %d AND cu.list_id = %d",$client_id, $list_id);

		$query = $this->db->query($sql);
		$lists = $query->result_array();

		if($query->num_rows() > 0){
			foreach ($lists as $row) {

				$this->load->helper('email');

				if (valid_email($row['email'])){

					$this->db->where('email', $row['email']);
					$query = $this->db->get('users');

					if($query->num_rows() == 1){

						$query_row = $query->row();

						$data = array(
							'user_id' => $query_row->id,
							'email' => ''
							);

						$this->db->where('email', $row['email']);
						$this->db->update('clients_users', $data);

						$data2 = array(
							'client_id' => $row['client_id'],
							'user_id' => $data['user_id'],
							'message' => $message,
							);

						$query1 = $this->db->insert('posts_users', $data2);
						$query2 = $this->db->insert('posts_users2', $data2);

						if($query1 && $query2){
							$data = array(
								'parse_token' => $row['parse_token'],
								'device' => $row['device'],
								);

							$this->send_push($data);
						}else{
							echo "The query to insert and send push didn't work";
						}
					}else{
						$this->send_email($message, $row['email']);
					}
				}else{

					$data = array(
						'client_id' => $row['client_id'],
						'user_id' => $row['user_id'],
						'message' => $message,
						);

					$query1 = $this->db->insert('posts_users', $data);
					$query2 = $this->db->insert('posts_users2', $data);

					if($query1 && $query2){
						$data = array(
							'parse_token' => $row['parse_token'],
							'device' => $row['device'],
							);

						$this->send_push($data, $message);
					}else{
						echo "The query to insert and send push didn't work";
					}
				}
			}
			return true;
		}else{
			$this->session->set_userdata('message2', 'No hay usuarios registrados en esta lista');
			return true;
		}
	}

	public function send_push($data, $message){

		$deviceToken = $data['parse_token'];

		if($data['device'] == 'iPhone'){
			$deviceType = 'ios';			
		}else{
			$deviceType = 'android';
		}

		$client_name = $this->session->userdata('client_name');

		$texto = $client_name . ": " . $message;


		if($this->httpPost($deviceToken, $deviceType, $texto)){
			echo "The push was sent";
			return true;
		}else{
			echo "We couldn't comunicate with Parse to send the push notification";
			return false;
		}
	}

	private function httpPost($deviceToken, $deviceType, $texto){
		
		$data = '{ "where": { "deviceType": "'.$deviceType.'", "deviceToken": "'.$deviceToken.'" }, "data": { "alert": "'.$texto.'", "badge": "Increment", "sound": "cheering.caf" } }';

		$ch = curl_init();  

		curl_setopt($ch,CURLOPT_URL,"https://api.parse.com/1/push");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'X-Parse-Application-Id: EeIVQ8rDUWYemKav8ACOcWWNRZesS6qEkmngwXKT',
			'X-Parse-REST-API-Key: zEqA9yBDlOEDLedTSxuYR34X51BN3LhkFndhvSDY',
			'Content-Type: application/json',
			'Content-Length:'.strlen($data),
			)); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);    

		$output=curl_exec($ch);

		curl_close($ch);
		return $output;
	}

	public function insert_clients_users($data, $list_id, $client_id){

		unset ($data['parse_token']);
		unset ($data['user_username']);
		unset ($data['device']);
		$data['client_id'] = $client_id;
		$data['list_id'] = $list_id;

		$this->db->where('client_id', $data['client_id']);
		$this->db->where('user_id', $data['user_id']);
		$this->db->where('list_id', $list_id);

		$query = $this->db->get('clients_users');

		if($query->num_rows() == 0){

			if($this->db->insert('clients_users', $data)){
				echo "The user was inserted to the list";
				return true;
			}else{
				echo "Could not insert to client_users";
			}
		}else{
			return false;
		}
	}

	public function delete_user($id){

		$this->db->where('id', $id);
		$delete = $this->db->delete('clients_users'); 

		if($delete){
			return true;
		}else{
			return false;
		}
	}

	public function delete_post($id){

		$this->db->where('id', $id);
		$delete = $this->db->delete('posts_clients'); 

		if($delete){
			return true;
		}else{
			return false;
		}
	}

	public function delete_temp_post($id){

		$this->db->where('id', $id);
		$delete = $this->db->delete('temp_posts'); 

		if($delete){
			return true;
		}else{
			return false;
		}
	}

	public function insert_temp_posts($text){

		$client_id = $this->session->userdata('client_id');
		$data = array(
			'text' => $text,
			'client_id' => $client_id
			);

		$this->db->where('text', $text);
		$this->db->where('client_id', $client_id);
		$query = $this->db->get('temp_posts');

		if($query->num_rows() == 0){

			$this->db->where('text', $text);
			$query2 = $this->db->get('posts');

			if($query2->num_rows() == 1){

				$row = $query2->row();

				$data2 = array(
					'post_id' => $row->id
					);

				$this->db->where('post_id', $data2['post_id']);
				$this->db->where('client_id', $client_id);
				$query3 = $this->db->get('posts_clients');

				if($query3->num_rows() == 0){

					if($this->db->insert('temp_posts', $data)){
						echo "The temp_post was inserted";
						$i = 5;
						$this->send_admin_notification($i);
						return true;
					}else{
						return false;
					}
				}else{
					return false;
				}
			}else{
				if($this->db->insert('temp_posts', $data)){
					echo "The temp_post was inserted";
					$i = 5;
					$this->send_admin_notification($i);
					return true;
				}else{
					return false;
				}
			}
		}else{
			return false;
		}
	}

	public function get_lists_info($list_id){

		$this->db->where('id', $list_id);
		$query = $this->db->get('lists');
		$lists = $query->result_array();
    
		return $lists;
	}


	public function insert_list($text){

		$list_key = md5(uniqid());

		$client_id = $this->session->userdata('client_id');

		$data = array(
			'list' => $text,
			'client_id' => $client_id,
			);

		$this->db->where('list', $text);
		$this->db->where('client_id', $client_id);
		$query = $this->db->get('lists');

		if($query->num_rows() == 0){
			if($this->db->insert('lists', $data)){
				echo "The list was inserted";
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	public function insert_list_users($text, $id){

		$client_id = $this->session->userdata('client_id');

		$data = array(
			'client_id' => $client_id,
			'user_id' => 1,
			'list_id' => $id
			);

		if($this->db->insert('temp_lists', $data)){
			echo "The temp_post was inserted";
			return true;
		}else{
			return false;
		}
	}

	public function delete_list($list_id, $client_id){

		$this->db->where('id', $list_id);
		$delete = $this->db->delete('lists');

		$this->db->where('list_id', $list_id);
		$this->db->where('client_id', $client_id);
		$delete2 = $this->db->delete('clients_users');

		$this->db->where('list_id', $list_id);
		$this->db->where('client_id', $client_id);
		$delete3 = $this->db->delete('temp_clients_users');

		if($delete && $delete2 && $delete3){
			return true;
		}else{
			return false;
		}
	}

	public function delete_user_from_list($id){

		$this->db->where('id', $id);
		$delete = $this->db->delete('clients_users');

		if($delete){
			return true;
		}else{
			return false;
		}
	}

	public function send_admin_notification($i){

		$admin1 = array(
			'client_id' => 1,
			'user_id' => 1,
			'post_id' => $i
			);

		$admin2 = array(
			'client_id' => 1,
			'user_id' => 2,
			'post_id' => $i
			);

		$deviceToken1 = 'de78c7553bc0db4d13996d606e6b6ebd04c5dc293b49c57daa040e81a5888a55';
		$deviceToken2 = 'cee6dd4c5ee6e81a87eef541a74d9cad117facf2d2cdfb4e787b2e3376a6ec13';

		if($i){

			$this->db->where('id', $i);
			$query = $this->db->get('posts');

			if($query){

				$row = $query->row();

				$data = array(
					'text' => $row->text
					);
			}

			$deviceType = 'ios';
			$texto = "Bipme: " . $data['text'];

			if($this->db->insert('posts_users', $admin1) && $this->db->insert('posts_users2', $admin1)){
				$deviceToken = $deviceToken1;
				$this->httpPost($deviceToken, $deviceType, $texto);
			}
			
			if($this->db->insert('posts_users', $admin2) && $this->db->insert('posts_users2', $admin2)){
				$deviceToken = $deviceToken2;
				$this->httpPost($deviceToken, $deviceType, $texto);	
			}
		}
	}

	public function check_category(){

		$client_lists = $this->session->userdata('client_lists');

		$client_id = $this->session->userdata('client_id');

		echo $client_lists;

		$this->db->where('client_id', $client_id);
		$query = $this->db->get('lists');

		if($query->num_rows() >= $client_lists ){
			echo "Too many lists";
			return false;
		}else{
			echo "You are good to go";
			return true;
		}
	}

	public function update_account($post, $column){

		$client_id = $this->session->userdata('client_id');		

		$data = array(
			$column => $post
			);

		$this->db->where('id', $client_id);
		$query = $this->db->update('clients', $data);

		if($query){
			return true;
		}else{
			return false;
		}

	}

	public function update_list_description($post, $list_id){

		$data = array(
			'description' => $post
			);

		$this->db->where('id', $list_id);
		$query = $this->db->update('lists', $data);

		if($query){
			return true;
		}else{
			return false;
		}
	}

	public function update_list_name($post, $list_id){

		$data = array(
			'list' => $post
			);

		$this->db->where('id', $list_id);
		$query = $this->db->update('lists', $data);

		if($query){
			return true;
		}else{
			return false;
		}
	}

	public function delete_temp_clients_users($user_id, $client_id, $list_id){

		$this->db->where('user_id', $user_id);
		$this->db->where('client_id', $client_id);
		$this->db->where('list_id', $list_id);
		$delete = $this->db->delete('temp_clients_users');

		if($delete){
			return true;
		}else{
			return false;
		}
	}

	public function add_temp_clients_users($data, $list_id, $client_id){

		unset ($data['parse_token']);
		unset ($data['user_username']);
		unset ($data['device']);
		$data['client_id'] = $client_id;
		$data['list_id'] = $list_id;

		$this->db->where('id', $list_id);
		$querya = $this->db->get('lists');

		if($querya){
			
			$row = $querya->row();

			$data2 = array(
					'list' => $row->list
					);
		}

		$this->db->where('user_id', $data['user_id']);
		$this->db->where('client_id', $data['client_id']);
		$this->db->where('list_id', $list_id);

		$query = $this->db->get('clients_users');

		if($query->num_rows() == 0){

			if($this->db->insert('clients_users', $data)){

				$this->db->where('user_id', $data['user_id']);
				$this->db->where('client_id', $data['client_id']);
				$this->db->where('list_id', $list_id);

				$this->db->delete('temp_clients_users');

				echo "The user was inserted to the list";
				return $data2;
			}else{
				echo "Could not insert to client_users";
			}
		}else{
			return false;
		}
	}

	public function get_list_text($list_id){

		$this->db->where('id', $list_id);
		$query = $this->db->get('lists');

		if($query){

			$row = $query->row();

			$list_text = array(
				'list' => $row->list,
				);

			return $list_text;
		}
	}

	public function get_posts_users_update(){

		$query = $this->db->get('posts_users2');

		$query = $query->result_array();

		return $query;
	}

	public function update_posts($id, $post_id){

		$this->db->where('id', $post_id);
		$query = $this->db->get('posts');

		if($query){

			$row = $query->row();

			$data = array(
				'post_id' => $row->text
				);

			$this->db->where('id', $id);
			$this->db->update('posts_users2', $data);

			return true;

		}
	}

	public function insert_notification_email($username, $message){

		$client_id = $this->session->userdata('client_id');

		$data = array(
			'client_id' => $client_id,
			'email' => $username,
			'message' => $message
			);

		$query = $this->db->insert('posts_emails', $data);

		if($query){
			$this->session->set_userdata('user_email', $data['email']);
			return true;
		}else{
			return false;
		}

	}

	public function insert_notification_api($data, $message, $client_id){

		unset ($data['parse_token']);
		unset ($data['user_username']);
		unset ($data['device']);
		$data['client_id'] = $client_id;
		$data['message'] = $message;

		$query = $this->db->insert('posts_users', $data);
		$query2 = $this->db->insert('posts_users2', $data);

		if($query && $query2){
			return true;
		}else{
			return false;
		}
	}

	public function check_email($username){

		$this->db->where('email', $username);
		$query = $this->db->get('users');

		if($query->num_rows() == 1){

			$row_user = $query->row();

			$data = array(
				'username' => $row_user->username,
				);

			return $data;
		}else{
			return false;
		}
	}

	public function unsubscribe($email){

		$data = array(
			'email' => $email,
			'reason' => 'unsubscribe'
			);

		$this->db->insert('unsubscribe', $data);
	}

	public function add_clients_users_email($variables){

		$data = array(
			'client_id' => $variables['client_id'],
			'user_id' => 12,
			'list_id' => $variables['list_id'],
			'email' => $variables['username'],
			);

		$this->db->where('email', $data['email']);
		$this->db->where('client_id', $data['client_id']);
		$this->db->where('list_id', $data['list_id']);
		$query = $this->db->get('clients_users');

		if($query->num_rows() == 0){
			if($this->db->insert('clients_users', $data)){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	public function check_client_key($client_id, $client_password){

		$this->db->where('id', $client_id);
		$this->db->where('password', $client_password);

		$query = $this->db->get('clients');

		if($query->num_rows() == 1){
			return true;
		}else{
			return false;
		}

	}

	public function send_email($message, $username){

		$client_name = $this->session->userdata('client_name');
		$client_image = $this->session->userdata('client_image');

		$config = Array(
			'protocol' => 'smtp',
			'smtp_host' => 'ssl://smtp.googlemail.com',
			'smtp_port' => 465,
			'smtp_user' => 'lisa@bipme.co',
			'smtp_pass' => 'Empib567b',
			'mailtype'  => 'html', 
			'charset'   => 'utf-8'
		);
		
		$this->load->library('email', $config);
		$this->email->set_newline("\r\n");

		$this->email->from('lisa@bipme.co', "Lisa de Bipme");
		$this->email->to($username);
		$this->email->subject($client_name . " te ha enviado un mensaje.");

		$message2 = "
			<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
			<html xmlns='http://www.w3.org/1999/xhtml'>
			<head>
			  <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
			  <meta name='viewport' content='width=device-width'>
			  <style>
			/**********************************************
			* Ink v1.0.5 - Copyright 2013 ZURB Inc        *
			**********************************************/

			/* Client-specific Styles & Reset */

			#outlook a { 
			  padding:0; 
			} 

			body{ 
			  width:100% !important; 
			  min-width: 100%;
			  -webkit-text-size-adjust:100%; 
			  -ms-text-size-adjust:100%; 
			  margin:0; 
			  padding:0;
			}

			.ExternalClass { 
			  width:100%;
			} 

			.ExternalClass, 
			.ExternalClass p, 
			.ExternalClass span, 
			.ExternalClass font, 
			.ExternalClass td, 
			.ExternalClass div { 
			  line-height: 100%; 
			} 

			#backgroundTable { 
			  margin:0; 
			  padding:0; 
			  width:100% !important; 
			  line-height: 100% !important; 
			}

			img { 
			  outline:none; 
			  text-decoration:none; 
			  -ms-interpolation-mode: bicubic;
			  width: auto;
			  max-width: 50px; 
			  float: left; 
			  clear: both; 
			  display: block;
			}

			center {
			  width: 100%;
			  min-width: 580px;
			}

			a img { 
			  border: none;
			}

			p {
			  margin: 0 0 0 10px;
			}

			table {
			  border-spacing: 0;
			  border-collapse: collapse;
			}

			td { 
			  word-break: break-word;
			  -webkit-hyphens: auto;
			  -moz-hyphens: auto;
			  hyphens: auto;
			  border-collapse: collapse !important; 
			}

			table, tr, td {
			  padding: 0;
			  vertical-align: top;
			  text-align: left;
			}

			hr {
			  color: #d9d9d9; 
			  background-color: #d9d9d9; 
			  height: 1px; 
			  border: none;
			}

			/* Responsive Grid */

			table.body {
			  height: 100%;
			  width: 100%;
			}

			table.container {
			  width: 580px;
			  margin: 0 auto;
			  text-align: inherit;
			}

			table.row { 
			  padding: 0px; 
			  width: 100%;
			  position: relative;
			}

			table.container table.row {
			  display: block;
			}

			td.wrapper {
			  padding: 10px 20px 0px 0px;
			  position: relative;
			}

			table.columns,
			table.column {
			  margin: 0 auto;
			}

			table.columns td,
			table.column td {
			  padding: 0px 0px 10px; 
			}

			table.columns td.sub-columns,
			table.column td.sub-columns,
			table.columns td.sub-column,
			table.column td.sub-column {
			  padding-right: 10px;
			}

			td.sub-column, td.sub-columns {
			  min-width: 0px;
			}

			table.row td.last,
			table.container td.last {
			  padding-right: 0px;
			}

			table.one { width: 30px; }
			table.two { width: 80px; }
			table.three { width: 130px; }
			table.four { width: 180px; }
			table.five { width: 230px; }
			table.six { width: 280px; }
			table.seven { width: 330px; }
			table.eight { width: 380px; }
			table.nine { width: 430px; }
			table.ten { width: 480px; }
			table.eleven { width: 530px; }
			table.twelve { width: 580px; }

			table.one center { min-width: 30px; }
			table.two center { min-width: 80px; }
			table.three center { min-width: 130px; }
			table.four center { min-width: 180px; }
			table.five center { min-width: 230px; }
			table.six center { min-width: 280px; }
			table.seven center { min-width: 330px; }
			table.eight center { min-width: 380px; }
			table.nine center { min-width: 430px; }
			table.ten center { min-width: 480px; }
			table.eleven center { min-width: 530px; }
			table.twelve center { min-width: 580px; }

			table.one .panel center { min-width: 10px; }
			table.two .panel center { min-width: 60px; }
			table.three .panel center { min-width: 110px; }
			table.four .panel center { min-width: 160px; }
			table.five .panel center { min-width: 210px; }
			table.six .panel center { min-width: 260px; }
			table.seven .panel center { min-width: 310px; }
			table.eight .panel center { min-width: 360px; }
			table.nine .panel center { min-width: 410px; }
			table.ten .panel center { min-width: 460px; }
			table.eleven .panel center { min-width: 510px; }
			table.twelve .panel center { min-width: 560px; }

			.body .columns td.one,
			.body .column td.one { width: 8.333333%; }
			.body .columns td.two,
			.body .column td.two { width: 16.666666%; }
			.body .columns td.three,
			.body .column td.three { width: 25%; }
			.body .columns td.four,
			.body .column td.four { width: 33.333333%; }
			.body .columns td.five,
			.body .column td.five { width: 41.666666%; }
			.body .columns td.six,
			.body .column td.six { width: 50%; }
			.body .columns td.seven,
			.body .column td.seven { width: 58.333333%; }
			.body .columns td.eight,
			.body .column td.eight { width: 66.666666%; }
			.body .columns td.nine,
			.body .column td.nine { width: 75%; }
			.body .columns td.ten,
			.body .column td.ten { width: 83.333333%; }
			.body .columns td.eleven,
			.body .column td.eleven { width: 91.666666%; }
			.body .columns td.twelve,
			.body .column td.twelve { width: 100%; }

			td.offset-by-one { padding-left: 50px; }
			td.offset-by-two { padding-left: 100px; }
			td.offset-by-three { padding-left: 150px; }
			td.offset-by-four { padding-left: 200px; }
			td.offset-by-five { padding-left: 250px; }
			td.offset-by-six { padding-left: 300px; }
			td.offset-by-seven { padding-left: 350px; }
			td.offset-by-eight { padding-left: 400px; }
			td.offset-by-nine { padding-left: 450px; }
			td.offset-by-ten { padding-left: 500px; }
			td.offset-by-eleven { padding-left: 550px; }

			td.expander {
			  visibility: hidden;
			  width: 0px;
			  padding: 0 !important;
			}

			table.columns .text-pad,
			table.column .text-pad {
			  padding-left: 10px;
			  padding-right: 10px;
			}

			table.columns .left-text-pad,
			table.columns .text-pad-left,
			table.column .left-text-pad,
			table.column .text-pad-left {
			  padding-left: 10px;
			}

			table.columns .right-text-pad,
			table.columns .text-pad-right,
			table.column .right-text-pad,
			table.column .text-pad-right {
			  padding-right: 10px;
			}

			/* Block Grid */

			.block-grid {
			  width: 100%;
			  max-width: 580px;
			}

			.block-grid td {
			  display: inline-block;
			  padding:10px;
			}

			.two-up td {
			  width:270px;
			}

			.three-up td {
			  width:173px;
			}

			.four-up td {
			  width:125px;
			}

			.five-up td {
			  width:96px;
			}

			.six-up td {
			  width:76px;
			}

			.seven-up td {
			  width:62px;
			}

			.eight-up td {
			  width:52px;
			}

			/* Alignment & Visibility Classes */

			table.center, td.center {
			  text-align: center;
			}

			h1.center,
			h2.center,
			h3.center,
			h4.center,
			h5.center,
			h6.center {
			  text-align: center;
			}

			span.center {
			  display: block;
			  width: 100%;
			  text-align: center;
			}

			img.center {
			  margin: 0 auto;
			  float: none;
			}

			.show-for-small,
			.hide-for-desktop {
			  display: none;
			}

			/* Typography */

			body, table.body, h1, h2, h3, h4, h5, h6, p, td { 
			  color: #222222;
			  font-family: 'Helvetica', 'Arial', sans-serif; 
			  font-weight: normal; 
			  padding:0; 
			  margin: 0;
			  text-align: left; 
			  line-height: 1.3;
			}

			h1, h2, h3, h4, h5, h6 {
			  word-break: normal;
			}

			h1 {font-size: 40px;}
			h2 {font-size: 36px;}
			h3 {font-size: 32px;}
			h4 {font-size: 28px;}
			h5 {font-size: 24px;}
			h6 {font-size: 20px;}
			body, table.body, p, td {font-size: 14px;line-height:19px;}

			p.lead, p.lede, p.leed {
			  font-size: 18px;
			  line-height:21px;
			}

			p { 
			  margin-bottom: 10px;
			}

			small {
			  font-size: 10px;
			}

			a {
			  color: #50bca8; 
			  text-decoration: none;
			}

			h1 a, 
			h2 a, 
			h3 a, 
			h4 a, 
			h5 a, 
			h6 a {
			  
			}

			h1 a:active, 
			h2 a:active,  
			h3 a:active, 
			h4 a:active, 
			h5 a:active, 
			h6 a:active { 
			  
			} 

			h1 a:visited, 
			h2 a:visited,  
			h3 a:visited, 
			h4 a:visited, 
			h5 a:visited, 
			h6 a:visited { 
			  
			} 

			/* Panels */

			.panel {
			  background: #f2f2f2;
			  border: 1px solid #d9d9d9;
			  padding: 10px !important;
			}

			.sub-grid table {
			  width: 100%;
			}

			.sub-grid td.sub-columns {
			  padding-bottom: 0;
			}

			/* Buttons */

			table.button,
			table.tiny-button,
			table.small-button,
			table.medium-button,
			table.large-button {
			  width: 100%;
			  overflow: hidden;
			}

			table.button td,
			table.tiny-button td,
			table.small-button td,
			table.medium-button td,
			table.large-button td {
			  display: block;
			  width: auto !important;
			  text-align: center;
			  background: #2ba6cb;
			  border: 1px solid #2284a1;
			  color: #ffffff;
			  padding: 8px 0;
			}

			table.tiny-button td {
			  padding: 5px 0 4px;
			}

			table.small-button td {
			  padding: 8px 0 7px;
			}

			table.medium-button td {
			  padding: 12px 0 10px;
			}

			table.large-button td {
			  padding: 21px 0 18px;
			}

			table.button td a,
			table.tiny-button td a,
			table.small-button td a,
			table.medium-button td a,
			table.large-button td a {
			  font-weight: bold;
			  text-decoration: none;
			  font-family: Helvetica, Arial, sans-serif;
			  color: #ffffff;
			  font-size: 16px;
			}

			table.tiny-button td a {
			  font-size: 12px;
			  font-weight: normal;
			}

			table.small-button td a {
			  font-size: 16px;
			}

			table.medium-button td a {
			  font-size: 20px;
			}

			table.large-button td a {
			  font-size: 24px;
			}

			table.button:hover td,
			table.button:visited td,
			table.button:active td {
			  background: #50bca8 !important;
			}

			table.button:hover td a,
			table.button:visited td a,
			table.button:active td a {
			  color: #fff !important;
			}

			table.button:hover td,
			table.tiny-button:hover td,
			table.small-button:hover td,
			table.medium-button:hover td,
			table.large-button:hover td {
			  background: #50bca8 !important;
			}

			table.button:hover td a,
			table.button:active td a,
			table.button td a:visited,
			table.tiny-button:hover td a,
			table.tiny-button:active td a,
			table.tiny-button td a:visited,
			table.small-button:hover td a,
			table.small-button:active td a,
			table.small-button td a:visited,
			table.medium-button:hover td a,
			table.medium-button:active td a,
			table.medium-button td a:visited,
			table.large-button:hover td a,
			table.large-button:active td a,
			table.large-button td a:visited {
			  color: #ffffff !important; 
			}

			table.secondary td {
			  background: #e9e9e9;
			  border-color: #d0d0d0;
			  color: #555;
			}

			table.secondary td a {
			  color: #555;
			}

			table.secondary:hover td {
			  background: #d0d0d0 !important;
			  color: #555;
			}

			table.secondary:hover td a,
			table.secondary td a:visited,
			table.secondary:active td a {
			  color: #555 !important;
			}

			table.success td {
			  background: #5da423;
			  border-color: #457a1a;
			}

			table.success:hover td {
			  background: #457a1a !important;
			}

			table.alert td {
			  background: #c60f13;
			  border-color: #970b0e;
			}

			table.alert:hover td {
			  background: #970b0e !important;
			}

			table.radius td {
			  -webkit-border-radius: 3px;
			  -moz-border-radius: 3px;
			  border-radius: 3px;
			}

			table.round td {
			  -webkit-border-radius: 500px;
			  -moz-border-radius: 500px;
			  border-radius: 500px;
			}

			/* Outlook First */

			body.outlook p {
			  display: inline !important;
			}

			/*  Media Queries */

			@media only screen and (max-width: 600px) {

			  table[class='body'] img {
			    width: auto !important;
			    height: auto !important;
			  }

			  table[class='body'] center {
			    min-width: 0 !important;
			  }

			  table[class='body'] .container {
			    width: 95% !important;
			  }

			  table[class='body'] .row {
			    width: 100% !important;
			    display: block !important;
			  }

			  table[class='body'] .wrapper {
			    display: block !important;
			    padding-right: 0 !important;
			  }

			  table[class='body'] .columns,
			  table[class='body'] .column {
			    table-layout: fixed !important;
			    float: none !important;
			    width: 100% !important;
			    padding-right: 0px !important;
			    padding-left: 0px !important;
			    display: block !important;
			  }

			  table[class='body'] .wrapper.first .columns,
			  table[class='body'] .wrapper.first .column {
			    display: table !important;
			  }

			  table[class='body'] table.columns td,
			  table[class='body'] table.column td {
			    width: 100% !important;
			  }

			  table[class='body'] .columns td.one,
			  table[class='body'] .column td.one { width: 8.333333% !important; }
			  table[class='body'] .columns td.two,
			  table[class='body'] .column td.two { width: 16.666666% !important; }
			  table[class='body'] .columns td.three,
			  table[class='body'] .column td.three { width: 25% !important; }
			  table[class='body'] .columns td.four,
			  table[class='body'] .column td.four { width: 33.333333% !important; }
			  table[class='body'] .columns td.five,
			  table[class='body'] .column td.five { width: 41.666666% !important; }
			  table[class='body'] .columns td.six,
			  table[class='body'] .column td.six { width: 50% !important; }
			  table[class='body'] .columns td.seven,
			  table[class='body'] .column td.seven { width: 58.333333% !important; }
			  table[class='body'] .columns td.eight,
			  table[class='body'] .column td.eight { width: 66.666666% !important; }
			  table[class='body'] .columns td.nine,
			  table[class='body'] .column td.nine { width: 75% !important; }
			  table[class='body'] .columns td.ten,
			  table[class='body'] .column td.ten { width: 83.333333% !important; }
			  table[class='body'] .columns td.eleven,
			  table[class='body'] .column td.eleven { width: 91.666666% !important; }
			  table[class='body'] .columns td.twelve,
			  table[class='body'] .column td.twelve { width: 100% !important; }

			  table[class='body'] td.offset-by-one,
			  table[class='body'] td.offset-by-two,
			  table[class='body'] td.offset-by-three,
			  table[class='body'] td.offset-by-four,
			  table[class='body'] td.offset-by-five,
			  table[class='body'] td.offset-by-six,
			  table[class='body'] td.offset-by-seven,
			  table[class='body'] td.offset-by-eight,
			  table[class='body'] td.offset-by-nine,
			  table[class='body'] td.offset-by-ten,
			  table[class='body'] td.offset-by-eleven {
			    padding-left: 0 !important;
			  }

			  table[class='body'] table.columns td.expander {
			    width: 1px !important;
			  }

			  table[class='body'] .right-text-pad,
			  table[class='body'] .text-pad-right {
			    padding-left: 10px !important;
			  }

			  table[class='body'] .left-text-pad,
			  table[class='body'] .text-pad-left {
			    padding-right: 10px !important;
			  }

			  table[class='body'] .hide-for-small,
			  table[class='body'] .show-for-desktop {
			    display: none !important;
			  }

			  table[class='body'] .show-for-small,
			  table[class='body'] .hide-for-desktop {
			    display: inherit !important;
			  }
			}

			  </style>
			  <style>

			    table.facebook td {
			      background: #3b5998;
			      border-color: #2d4473;
			    }

			    table.facebook:hover td {
			      background: #2d4473 !important;
			    }

			    table.twitter td {
			      background: #00acee;
			      border-color: #0087bb;
			    }

			    table.twitter:hover td {
			      background: #0087bb !important;
			    }

			    table.google-plus td {
			      background-color: #DB4A39;
			      border-color: #CC0000;
			    }

			    table.google-plus:hover td {
			      background: #CC0000 !important;
			    }

			    .template-label {
			      color: #ffffff;
			      font-weight: bold;
			      font-size: 11px;
			    }

			    .callout .panel {
			      background: white;
			      border-color: #ff9500;
			    }

			    .header {
			      background: #ff9500;
			    }

			    .footer .wrapper {
			      background: #eaedf1;
			    }

			    .footer h5 {
			      padding-bottom: 10px;
			    }

			    table.columns .text-pad {
			      padding-left: 10px;
			      padding-right: 10px;
			    }

			    table.columns .left-text-pad {
			      padding-left: 10px;
			    }

			    table.columns .right-text-pad {
			      padding-right: 10px;
			    }

			    @media only screen and (max-width: 600px) {

			      table[class='body'] .right-text-pad {
			        padding-left: 10px !important;
			      }

			      table[class='body'] .left-text-pad {
			        padding-right: 10px !important;
			      }
			    }

			  </style>
			</head>
			<body style='min-width: 100%;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;margin: 0;padding: 0;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;text-align: left;line-height: 19px;font-size: 14px;width: 100% !important;'>
			  <table class='body' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;height: 100%;width: 100%;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;'>
			    <tr style='padding: 0;vertical-align: top;text-align: left;'>
			      <td class='center' align='center' valign='top' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0;vertical-align: top;text-align: center;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;'>
			        <center style='width: 100%;min-width: 580px;'>


			          <table class='row header' style='border-spacing: 0;border-collapse: collapse;padding: 0px;vertical-align: top;text-align: left;background: #ff9500;width: 100%;position: relative;'>
			            <tr style='padding: 0;vertical-align: top;text-align: left;'>
			              <td class='center' align='center' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0;vertical-align: top;text-align: center;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;'>
			                <center style='width: 100%;min-width: 580px;'>

			                  <table class='container' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: inherit;width: 580px;margin: 0 auto;'>
			                    <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                      <td class='wrapper last' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;position: relative;padding-right: 0px;border-collapse: collapse !important;'>

			                        <table class='twelve columns' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;margin: 0 auto;width: 580px;'>
			                          <tr style='padding: 0;vertical-align: top;text-align: left;'>

			                            <td class='six sub-columns' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;min-width: 0px;padding-right: 10px;width: 50%;border-collapse: collapse !important;'>
			                              <img src='http://bipme.co/app/img/bipme_logo_white.png' style='outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;width: auto;max-width: 50px;float: left;clear: both;display: block;'>
			                            </td>
			                            <td class='six sub-columns last' align='right' style='text-align: right;vertical-align: middle;word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;min-width: 0px;padding-right: 0px;width: 50%;border-collapse: collapse !important;'>
			                              <span class='template-label' style='color: #ffffff;font-weight: bold;font-size: 11px;'>Bipme</span>
			                            </td>
			                            <td class='expander' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;'></td>

			                          </tr>
			                        </table>

			                      </td>
			                    </tr>
			                  </table>

			                </center>
			              </td>
			            </tr>
			          </table>
			          <br>

			          <table class='container' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: inherit;width: 580px;margin: 0 auto;'>
			            <tr style='padding: 0;vertical-align: top;text-align: left;'>
			              <td style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;'>

			                <!-- content start -->
			                <table class='row' style='border-spacing: 0;border-collapse: collapse;padding: 0px;vertical-align: top;text-align: left;width: 100%;position: relative;display: block;'>
			                  <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                    <td class='wrapper last' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;position: relative;padding-right: 0px;border-collapse: collapse !important;'>

			                      <table class='twelve columns' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;margin: 0 auto;width: 580px;'>
			                        <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                          <td style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;'>

			                            <h1 style='color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;padding: 0;margin: 0;text-align: left;line-height: 1.3;word-break: normal;font-size: 40px;'>¡Hola!</h1>
			                            <br>
			                            <p class='lead' style='margin: 0;margin-bottom: 10px;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;padding: 0;text-align: left;line-height: 21px;font-size: 18px;'>" . $client_name ." te ha enviado un mensaje importante a través de <a style='color:#ff9500; text-decoration:none'href='http://bipme.co/app/'>Bipme</a>...</p>

			                          </td>
			                          <td class='expander' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;'></td>
			                        </tr>
			                      </table>

			                    </td>
			                  </tr>
			                </table>

			                <table class='row callout' style='border-spacing: 0;border-collapse: collapse;padding: 0px;vertical-align: top;text-align: left;width: 100%;position: relative;display: block;'>
			                  <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                    <td class='wrapper last' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;position: relative;padding-right: 0px;border-collapse: collapse !important;'>

			                      <table class='twelve columns' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;margin: 0 auto;width: 580px;'>
			                        <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                          <td class='panel' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px !important;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;background: white;border: 1px solid #d9d9d9;border-color: #ff9500;border-collapse: collapse !important;'>
			                            <img src='" . $client_image . "' style='outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;width: auto;max-width: 50px; border-radius:25px;float: left;clear: both;display: block;'>
			                            <p style='text-align: justify;margin-top: 8px;margin-left: 60px;margin-bottom: 10px;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;padding: 0;line-height: 19px;font-size: 14px;'><span style='font-weight: bold;'>" . $client_name ."</span>: " . $message . "<a href='http://bipme.co/app/' style='color: #50bca8;text-decoration: none;'> Descarga Bipme para ver el mensaje »</a></p>

			                          </td>
			                          <td class='expander' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;'></td>
			                        </tr>
			                      </table>

			                    </td>
			                  </tr>
			                </table>

			                <table class='row' style='border-spacing: 0;border-collapse: collapse;padding: 0px;vertical-align: top;text-align: left;width: 100%;position: relative;display: block;'>
			                  <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                    <td class='wrapper last' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;position: relative;padding-right: 0px;border-collapse: collapse !important;'>

			                      <table class='twelve columns' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;margin: 0 auto;width: 580px;'>
			                        <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                          <td style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;'>

			                            <h3 style='color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;padding: 0;margin: 0;text-align: left;line-height: 1.3;word-break: normal;font-size: 32px;'>¿Que es <a style='color:#ff9500; text-decoration:none'href='http://bipme.co/app/'>Bipme</a>?</h3>
			                            <br>
			                            <p style='margin: 0;margin-bottom: 10px;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;padding: 0;text-align: left;line-height: 19px;font-size: 14px;'>Con Bipme te olvidas de los correos infinitos y los mensajes sin sentido. Usando nuestra aplicación recibirás mensajes cortos a través de notificaciones con la información que te realmente importa.</p>

			                          </td>
			                          <td class='expander' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;'></td>
			                        </tr>
			                      </table>

			                    </td>
			                  </tr>
			                </table>


			                <table class='row' style='border-spacing: 0;border-collapse: collapse;padding: 0px;vertical-align: top;text-align: left;width: 100%;position: relative;display: block;'>
			                  <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                    <td class='wrapper last' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;position: relative;padding-right: 0px;border-collapse: collapse !important;'>

			                      <table class='three columns' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;margin: 0 auto;width: 130px;'>
			                        <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                          <td style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;'>

			                            <a href='http://bipme.co/app/' style='color: #ffffff;text-decoration: none;font-weight: bold;font-family: Helvetica, Arial, sans-serif;font-size: 16px;'><table class='button' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;width: 100%;overflow: hidden;'>
			                              <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                                <td style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 8px 0;vertical-align: top;text-align: center;color: #ffffff;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;display: block;background: #ff9500;border: 1px solid;border-collapse: collapse !important;width: auto !important;'>
			                                  Download
			                                </td>
			                              </tr>
			                            </table></a>
			                            <br/>
			                            <br/>

			                          </td>
			                          <td class='expander' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;'></td>
			                        </tr>
			                      </table>

			                    </td>
			                  </tr>
			                </table>


			                <table class='row footer' style='border-spacing: 0;border-collapse: collapse;padding: 0px;vertical-align: top;text-align: left;width: 100%;position: relative;display: block;'>
			                  <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                    <td class='wrapper' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;position: relative;background: #eaedf1;border-collapse: collapse !important;'>

			                      <table class='six columns' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;margin: 0 auto;width: 280px;'>
			                        <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                          <td class='left-text-pad' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;padding-left: 10px;border-collapse: collapse !important;'>

			                            <h5 style='color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;padding: 0;margin: 0;text-align: left;line-height: 1.3;word-break: normal;font-size: 24px;padding-bottom: 10px;'>Somos sociales:</h5>

			                             <a href='https://www.facebook.com/bipmeapp' style='color: #ffffff;text-decoration: none;font-weight: normal;font-family: Helvetica, Arial, sans-serif;font-size: 12px;'><table class='tiny-button facebook' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;width: 100%;overflow: hidden;'>
			                              <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                                <td style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 5px 0 4px;vertical-align: top;text-align: center;color: #ffffff;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;display: block;background: #3b5998;border: 1px solid #2284a1;border-color: #2d4473;border-collapse: collapse !important;width: auto !important;'>
			                                 Facebook
			                                </td>
			                              </tr>
			                            </table></a>

			                            <br>

			                            <a href='https://twitter.com/bipmeapp' style='color: #ffffff;text-decoration: none;font-weight: normal;font-family: Helvetica, Arial, sans-serif;font-size: 12px;'><table class='tiny-button twitter' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;width: 100%;overflow: hidden;'>
			                              <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                                <td style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 5px 0 4px;vertical-align: top;text-align: center;color: #ffffff;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;display: block;background: #00acee;border: 1px solid #2284a1;border-color: #0087bb;border-collapse: collapse !important;width: auto !important;'>
			                                  Twitter
			                                </td>
			                              </tr>
			                            </table></a>

			                            <br>

			                          </td>
			                          <td class='expander' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;'></td>
			                        </tr>
			                      </table>

			                    </td>
			                    <td class='wrapper last' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;position: relative;background: #eaedf1;padding-right: 0px;border-collapse: collapse !important;'>

			                      <table class='six columns' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;margin: 0 auto;width: 280px;'>
			                        <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                          <td class='last right-text-pad' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;padding-right: 0px;border-collapse: collapse !important;'>
			                            <p style='margin: 0;margin-bottom: 10px;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;padding: 0;text-align: left;line-height: 19px;font-size: 14px;'>¿Quieres ponerte en contacto?</p>
			                            <p style='margin: 0;margin-bottom: 10px;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;padding: 0;text-align: left;line-height: 19px;font-size: 14px;'>Habla con <a href='mailto:lisa@bipme.co' style='color: #50bca8;text-decoration: none;'>lisa@bipme.co</a></p>
			                          </td>
			                          <td class='expander' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;'></td>
			                        </tr>
			                      </table>

			                    </td>
			                  </tr>
			                </table>

			                <br/>
			                <table class='row' style='border-spacing: 0;border-collapse: collapse;padding: 0px;vertical-align: top;text-align: left;width: 100%;position: relative;display: block;'>
			                  <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                    <td class='wrapper last' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;position: relative;padding-right: 0px;border-collapse: collapse !important;'>

			                      <table class='twelve columns' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;margin: 0 auto;width: 580px;'>
			                        <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                          <td align='center' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;'>
			                            <center style='width: 100%;min-width: 580px;'>
			                              <p style='text-align: center;margin: 0;margin-bottom: 10px;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;padding: 0;line-height: 19px;font-size: 14px;'><a href='http://bipme.co/app/terms.php' style='color: #50bca8;text-decoration: none;'>Terms</a> | <a href='http://bipme.co/app/privacy.php' style='color: #50bca8;text-decoration: none;'>Privacy</a> | <a href='" . base_url() . "add/unsusbcribe/$username' style='color: #50bca8;text-decoration: none;'>Unsubscribe</a></p>
			                            </center>
			                          </td>
			                          <td class='expander' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;'></td>
			                        </tr>
			                      </table>

			                    </td>
			                  </tr>
			                </table>

			                <!-- container end below -->
			              </td>
			            </tr>
			          </table>

			        </center>
			      </td>
			    </tr>
			  </table>
			</body>
			</html>";

		$this->email->message($message2);

		//send and email to the user
		if($this->model_add->insert_notification_email($username, $message)){
			if($this->email->send()){
				//redirect('main/members');
			}else{
				echo "Could not send email";
			}
		}else{
			echo "Problem adding to database.";
		}
	}

	public function get_user_info_api($username){

		if(isset($username)) {

			$this->db->where('username', $username);
			$query_user = $this->db->get('users');

			if($query_user->num_rows() == 1){

				if($query_user){

					$row_user = $query_user->row();

					$data = array(
						'user_id' => $row_user->id,
						'parse_token' => $row_user->parse_token,
						'user_username' => $row_user->username,
						'device' => $row_user->device
						);

					return $data;
				}
			}else{
				return true;
			}
		}
	}

	public function send_push_api($data, $message, $client_id){

		$deviceToken = $data['parse_token'];

		if($data['device'] == 'iPhone'){
			$deviceType = 'ios';			
		}else{
			$deviceType = 'android';
		}

		$this->db->where('id', $client_id);
		$query = $this->db->get('clients');

		if($query->num_rows() == 1){

			$row_client = $query->row();

			$data = array(
				'client_name' => $row_client->name,
				);

			$client_name = $data['client_name'];
		}


		$texto = $client_name . ": " . $message;


		if($this->httpPost($deviceToken, $deviceType, $texto)){
			return true;
		}else{
			return false;
		}
	}

	public function send_email_api($message, $username, $client_id){

		$this->db->where('id', $client_id);
		$query = $this->db->get('clients');

		if($query->num_rows() == 1){

			$query_rows = $query->row();

			$data = array(
				'client_name' => $query_rows->name,
				'client_image' => $query_rows->image,
				);

			$client_name = $data['client_name'];
			$client_image = $data['client_image'];
		}

		$config = Array(
			'protocol' => 'smtp',
			'smtp_host' => 'ssl://smtp.googlemail.com',
			'smtp_port' => 465,
			'smtp_user' => 'lisa@bipme.co',
			'smtp_pass' => 'Empib567b',
			'mailtype'  => 'html', 
			'charset'   => 'utf-8'
		);
		
		$this->load->library('email', $config);
		$this->email->set_newline("\r\n");

		$this->email->from('lisa@bipme.co', "Lisa de Bipme");
		$this->email->to($username);
		$this->email->subject($client_name . " te ha enviado un mensaje.");

		$message2 = "
			<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
			<html xmlns='http://www.w3.org/1999/xhtml'>
			<head>
			  <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
			  <meta name='viewport' content='width=device-width'>
			  <style>
			/**********************************************
			* Ink v1.0.5 - Copyright 2013 ZURB Inc        *
			**********************************************/

			/* Client-specific Styles & Reset */

			#outlook a { 
			  padding:0; 
			} 

			body{ 
			  width:100% !important; 
			  min-width: 100%;
			  -webkit-text-size-adjust:100%; 
			  -ms-text-size-adjust:100%; 
			  margin:0; 
			  padding:0;
			}

			.ExternalClass { 
			  width:100%;
			} 

			.ExternalClass, 
			.ExternalClass p, 
			.ExternalClass span, 
			.ExternalClass font, 
			.ExternalClass td, 
			.ExternalClass div { 
			  line-height: 100%; 
			} 

			#backgroundTable { 
			  margin:0; 
			  padding:0; 
			  width:100% !important; 
			  line-height: 100% !important; 
			}

			img { 
			  outline:none; 
			  text-decoration:none; 
			  -ms-interpolation-mode: bicubic;
			  width: auto;
			  max-width: 50px; 
			  float: left; 
			  clear: both; 
			  display: block;
			}

			center {
			  width: 100%;
			  min-width: 580px;
			}

			a img { 
			  border: none;
			}

			p {
			  margin: 0 0 0 10px;
			}

			table {
			  border-spacing: 0;
			  border-collapse: collapse;
			}

			td { 
			  word-break: break-word;
			  -webkit-hyphens: auto;
			  -moz-hyphens: auto;
			  hyphens: auto;
			  border-collapse: collapse !important; 
			}

			table, tr, td {
			  padding: 0;
			  vertical-align: top;
			  text-align: left;
			}

			hr {
			  color: #d9d9d9; 
			  background-color: #d9d9d9; 
			  height: 1px; 
			  border: none;
			}

			/* Responsive Grid */

			table.body {
			  height: 100%;
			  width: 100%;
			}

			table.container {
			  width: 580px;
			  margin: 0 auto;
			  text-align: inherit;
			}

			table.row { 
			  padding: 0px; 
			  width: 100%;
			  position: relative;
			}

			table.container table.row {
			  display: block;
			}

			td.wrapper {
			  padding: 10px 20px 0px 0px;
			  position: relative;
			}

			table.columns,
			table.column {
			  margin: 0 auto;
			}

			table.columns td,
			table.column td {
			  padding: 0px 0px 10px; 
			}

			table.columns td.sub-columns,
			table.column td.sub-columns,
			table.columns td.sub-column,
			table.column td.sub-column {
			  padding-right: 10px;
			}

			td.sub-column, td.sub-columns {
			  min-width: 0px;
			}

			table.row td.last,
			table.container td.last {
			  padding-right: 0px;
			}

			table.one { width: 30px; }
			table.two { width: 80px; }
			table.three { width: 130px; }
			table.four { width: 180px; }
			table.five { width: 230px; }
			table.six { width: 280px; }
			table.seven { width: 330px; }
			table.eight { width: 380px; }
			table.nine { width: 430px; }
			table.ten { width: 480px; }
			table.eleven { width: 530px; }
			table.twelve { width: 580px; }

			table.one center { min-width: 30px; }
			table.two center { min-width: 80px; }
			table.three center { min-width: 130px; }
			table.four center { min-width: 180px; }
			table.five center { min-width: 230px; }
			table.six center { min-width: 280px; }
			table.seven center { min-width: 330px; }
			table.eight center { min-width: 380px; }
			table.nine center { min-width: 430px; }
			table.ten center { min-width: 480px; }
			table.eleven center { min-width: 530px; }
			table.twelve center { min-width: 580px; }

			table.one .panel center { min-width: 10px; }
			table.two .panel center { min-width: 60px; }
			table.three .panel center { min-width: 110px; }
			table.four .panel center { min-width: 160px; }
			table.five .panel center { min-width: 210px; }
			table.six .panel center { min-width: 260px; }
			table.seven .panel center { min-width: 310px; }
			table.eight .panel center { min-width: 360px; }
			table.nine .panel center { min-width: 410px; }
			table.ten .panel center { min-width: 460px; }
			table.eleven .panel center { min-width: 510px; }
			table.twelve .panel center { min-width: 560px; }

			.body .columns td.one,
			.body .column td.one { width: 8.333333%; }
			.body .columns td.two,
			.body .column td.two { width: 16.666666%; }
			.body .columns td.three,
			.body .column td.three { width: 25%; }
			.body .columns td.four,
			.body .column td.four { width: 33.333333%; }
			.body .columns td.five,
			.body .column td.five { width: 41.666666%; }
			.body .columns td.six,
			.body .column td.six { width: 50%; }
			.body .columns td.seven,
			.body .column td.seven { width: 58.333333%; }
			.body .columns td.eight,
			.body .column td.eight { width: 66.666666%; }
			.body .columns td.nine,
			.body .column td.nine { width: 75%; }
			.body .columns td.ten,
			.body .column td.ten { width: 83.333333%; }
			.body .columns td.eleven,
			.body .column td.eleven { width: 91.666666%; }
			.body .columns td.twelve,
			.body .column td.twelve { width: 100%; }

			td.offset-by-one { padding-left: 50px; }
			td.offset-by-two { padding-left: 100px; }
			td.offset-by-three { padding-left: 150px; }
			td.offset-by-four { padding-left: 200px; }
			td.offset-by-five { padding-left: 250px; }
			td.offset-by-six { padding-left: 300px; }
			td.offset-by-seven { padding-left: 350px; }
			td.offset-by-eight { padding-left: 400px; }
			td.offset-by-nine { padding-left: 450px; }
			td.offset-by-ten { padding-left: 500px; }
			td.offset-by-eleven { padding-left: 550px; }

			td.expander {
			  visibility: hidden;
			  width: 0px;
			  padding: 0 !important;
			}

			table.columns .text-pad,
			table.column .text-pad {
			  padding-left: 10px;
			  padding-right: 10px;
			}

			table.columns .left-text-pad,
			table.columns .text-pad-left,
			table.column .left-text-pad,
			table.column .text-pad-left {
			  padding-left: 10px;
			}

			table.columns .right-text-pad,
			table.columns .text-pad-right,
			table.column .right-text-pad,
			table.column .text-pad-right {
			  padding-right: 10px;
			}

			/* Block Grid */

			.block-grid {
			  width: 100%;
			  max-width: 580px;
			}

			.block-grid td {
			  display: inline-block;
			  padding:10px;
			}

			.two-up td {
			  width:270px;
			}

			.three-up td {
			  width:173px;
			}

			.four-up td {
			  width:125px;
			}

			.five-up td {
			  width:96px;
			}

			.six-up td {
			  width:76px;
			}

			.seven-up td {
			  width:62px;
			}

			.eight-up td {
			  width:52px;
			}

			/* Alignment & Visibility Classes */

			table.center, td.center {
			  text-align: center;
			}

			h1.center,
			h2.center,
			h3.center,
			h4.center,
			h5.center,
			h6.center {
			  text-align: center;
			}

			span.center {
			  display: block;
			  width: 100%;
			  text-align: center;
			}

			img.center {
			  margin: 0 auto;
			  float: none;
			}

			.show-for-small,
			.hide-for-desktop {
			  display: none;
			}

			/* Typography */

			body, table.body, h1, h2, h3, h4, h5, h6, p, td { 
			  color: #222222;
			  font-family: 'Helvetica', 'Arial', sans-serif; 
			  font-weight: normal; 
			  padding:0; 
			  margin: 0;
			  text-align: left; 
			  line-height: 1.3;
			}

			h1, h2, h3, h4, h5, h6 {
			  word-break: normal;
			}

			h1 {font-size: 40px;}
			h2 {font-size: 36px;}
			h3 {font-size: 32px;}
			h4 {font-size: 28px;}
			h5 {font-size: 24px;}
			h6 {font-size: 20px;}
			body, table.body, p, td {font-size: 14px;line-height:19px;}

			p.lead, p.lede, p.leed {
			  font-size: 18px;
			  line-height:21px;
			}

			p { 
			  margin-bottom: 10px;
			}

			small {
			  font-size: 10px;
			}

			a {
			  color: #50bca8; 
			  text-decoration: none;
			}

			h1 a, 
			h2 a, 
			h3 a, 
			h4 a, 
			h5 a, 
			h6 a {
			  
			}

			h1 a:active, 
			h2 a:active,  
			h3 a:active, 
			h4 a:active, 
			h5 a:active, 
			h6 a:active { 
			  
			} 

			h1 a:visited, 
			h2 a:visited,  
			h3 a:visited, 
			h4 a:visited, 
			h5 a:visited, 
			h6 a:visited { 
			  
			} 

			/* Panels */

			.panel {
			  background: #f2f2f2;
			  border: 1px solid #d9d9d9;
			  padding: 10px !important;
			}

			.sub-grid table {
			  width: 100%;
			}

			.sub-grid td.sub-columns {
			  padding-bottom: 0;
			}

			/* Buttons */

			table.button,
			table.tiny-button,
			table.small-button,
			table.medium-button,
			table.large-button {
			  width: 100%;
			  overflow: hidden;
			}

			table.button td,
			table.tiny-button td,
			table.small-button td,
			table.medium-button td,
			table.large-button td {
			  display: block;
			  width: auto !important;
			  text-align: center;
			  background: #2ba6cb;
			  border: 1px solid #2284a1;
			  color: #ffffff;
			  padding: 8px 0;
			}

			table.tiny-button td {
			  padding: 5px 0 4px;
			}

			table.small-button td {
			  padding: 8px 0 7px;
			}

			table.medium-button td {
			  padding: 12px 0 10px;
			}

			table.large-button td {
			  padding: 21px 0 18px;
			}

			table.button td a,
			table.tiny-button td a,
			table.small-button td a,
			table.medium-button td a,
			table.large-button td a {
			  font-weight: bold;
			  text-decoration: none;
			  font-family: Helvetica, Arial, sans-serif;
			  color: #ffffff;
			  font-size: 16px;
			}

			table.tiny-button td a {
			  font-size: 12px;
			  font-weight: normal;
			}

			table.small-button td a {
			  font-size: 16px;
			}

			table.medium-button td a {
			  font-size: 20px;
			}

			table.large-button td a {
			  font-size: 24px;
			}

			table.button:hover td,
			table.button:visited td,
			table.button:active td {
			  background: #50bca8 !important;
			}

			table.button:hover td a,
			table.button:visited td a,
			table.button:active td a {
			  color: #fff !important;
			}

			table.button:hover td,
			table.tiny-button:hover td,
			table.small-button:hover td,
			table.medium-button:hover td,
			table.large-button:hover td {
			  background: #50bca8 !important;
			}

			table.button:hover td a,
			table.button:active td a,
			table.button td a:visited,
			table.tiny-button:hover td a,
			table.tiny-button:active td a,
			table.tiny-button td a:visited,
			table.small-button:hover td a,
			table.small-button:active td a,
			table.small-button td a:visited,
			table.medium-button:hover td a,
			table.medium-button:active td a,
			table.medium-button td a:visited,
			table.large-button:hover td a,
			table.large-button:active td a,
			table.large-button td a:visited {
			  color: #ffffff !important; 
			}

			table.secondary td {
			  background: #e9e9e9;
			  border-color: #d0d0d0;
			  color: #555;
			}

			table.secondary td a {
			  color: #555;
			}

			table.secondary:hover td {
			  background: #d0d0d0 !important;
			  color: #555;
			}

			table.secondary:hover td a,
			table.secondary td a:visited,
			table.secondary:active td a {
			  color: #555 !important;
			}

			table.success td {
			  background: #5da423;
			  border-color: #457a1a;
			}

			table.success:hover td {
			  background: #457a1a !important;
			}

			table.alert td {
			  background: #c60f13;
			  border-color: #970b0e;
			}

			table.alert:hover td {
			  background: #970b0e !important;
			}

			table.radius td {
			  -webkit-border-radius: 3px;
			  -moz-border-radius: 3px;
			  border-radius: 3px;
			}

			table.round td {
			  -webkit-border-radius: 500px;
			  -moz-border-radius: 500px;
			  border-radius: 500px;
			}

			/* Outlook First */

			body.outlook p {
			  display: inline !important;
			}

			/*  Media Queries */

			@media only screen and (max-width: 600px) {

			  table[class='body'] img {
			    width: auto !important;
			    height: auto !important;
			  }

			  table[class='body'] center {
			    min-width: 0 !important;
			  }

			  table[class='body'] .container {
			    width: 95% !important;
			  }

			  table[class='body'] .row {
			    width: 100% !important;
			    display: block !important;
			  }

			  table[class='body'] .wrapper {
			    display: block !important;
			    padding-right: 0 !important;
			  }

			  table[class='body'] .columns,
			  table[class='body'] .column {
			    table-layout: fixed !important;
			    float: none !important;
			    width: 100% !important;
			    padding-right: 0px !important;
			    padding-left: 0px !important;
			    display: block !important;
			  }

			  table[class='body'] .wrapper.first .columns,
			  table[class='body'] .wrapper.first .column {
			    display: table !important;
			  }

			  table[class='body'] table.columns td,
			  table[class='body'] table.column td {
			    width: 100% !important;
			  }

			  table[class='body'] .columns td.one,
			  table[class='body'] .column td.one { width: 8.333333% !important; }
			  table[class='body'] .columns td.two,
			  table[class='body'] .column td.two { width: 16.666666% !important; }
			  table[class='body'] .columns td.three,
			  table[class='body'] .column td.three { width: 25% !important; }
			  table[class='body'] .columns td.four,
			  table[class='body'] .column td.four { width: 33.333333% !important; }
			  table[class='body'] .columns td.five,
			  table[class='body'] .column td.five { width: 41.666666% !important; }
			  table[class='body'] .columns td.six,
			  table[class='body'] .column td.six { width: 50% !important; }
			  table[class='body'] .columns td.seven,
			  table[class='body'] .column td.seven { width: 58.333333% !important; }
			  table[class='body'] .columns td.eight,
			  table[class='body'] .column td.eight { width: 66.666666% !important; }
			  table[class='body'] .columns td.nine,
			  table[class='body'] .column td.nine { width: 75% !important; }
			  table[class='body'] .columns td.ten,
			  table[class='body'] .column td.ten { width: 83.333333% !important; }
			  table[class='body'] .columns td.eleven,
			  table[class='body'] .column td.eleven { width: 91.666666% !important; }
			  table[class='body'] .columns td.twelve,
			  table[class='body'] .column td.twelve { width: 100% !important; }

			  table[class='body'] td.offset-by-one,
			  table[class='body'] td.offset-by-two,
			  table[class='body'] td.offset-by-three,
			  table[class='body'] td.offset-by-four,
			  table[class='body'] td.offset-by-five,
			  table[class='body'] td.offset-by-six,
			  table[class='body'] td.offset-by-seven,
			  table[class='body'] td.offset-by-eight,
			  table[class='body'] td.offset-by-nine,
			  table[class='body'] td.offset-by-ten,
			  table[class='body'] td.offset-by-eleven {
			    padding-left: 0 !important;
			  }

			  table[class='body'] table.columns td.expander {
			    width: 1px !important;
			  }

			  table[class='body'] .right-text-pad,
			  table[class='body'] .text-pad-right {
			    padding-left: 10px !important;
			  }

			  table[class='body'] .left-text-pad,
			  table[class='body'] .text-pad-left {
			    padding-right: 10px !important;
			  }

			  table[class='body'] .hide-for-small,
			  table[class='body'] .show-for-desktop {
			    display: none !important;
			  }

			  table[class='body'] .show-for-small,
			  table[class='body'] .hide-for-desktop {
			    display: inherit !important;
			  }
			}

			  </style>
			  <style>

			    table.facebook td {
			      background: #3b5998;
			      border-color: #2d4473;
			    }

			    table.facebook:hover td {
			      background: #2d4473 !important;
			    }

			    table.twitter td {
			      background: #00acee;
			      border-color: #0087bb;
			    }

			    table.twitter:hover td {
			      background: #0087bb !important;
			    }

			    table.google-plus td {
			      background-color: #DB4A39;
			      border-color: #CC0000;
			    }

			    table.google-plus:hover td {
			      background: #CC0000 !important;
			    }

			    .template-label {
			      color: #ffffff;
			      font-weight: bold;
			      font-size: 11px;
			    }

			    .callout .panel {
			      background: white;
			      border-color: #ff9500;
			    }

			    .header {
			      background: #ff9500;
			    }

			    .footer .wrapper {
			      background: #eaedf1;
			    }

			    .footer h5 {
			      padding-bottom: 10px;
			    }

			    table.columns .text-pad {
			      padding-left: 10px;
			      padding-right: 10px;
			    }

			    table.columns .left-text-pad {
			      padding-left: 10px;
			    }

			    table.columns .right-text-pad {
			      padding-right: 10px;
			    }

			    @media only screen and (max-width: 600px) {

			      table[class='body'] .right-text-pad {
			        padding-left: 10px !important;
			      }

			      table[class='body'] .left-text-pad {
			        padding-right: 10px !important;
			      }
			    }

			  </style>
			</head>
			<body style='min-width: 100%;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;margin: 0;padding: 0;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;text-align: left;line-height: 19px;font-size: 14px;width: 100% !important;'>
			  <table class='body' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;height: 100%;width: 100%;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;'>
			    <tr style='padding: 0;vertical-align: top;text-align: left;'>
			      <td class='center' align='center' valign='top' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0;vertical-align: top;text-align: center;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;'>
			        <center style='width: 100%;min-width: 580px;'>


			          <table class='row header' style='border-spacing: 0;border-collapse: collapse;padding: 0px;vertical-align: top;text-align: left;background: #ff9500;width: 100%;position: relative;'>
			            <tr style='padding: 0;vertical-align: top;text-align: left;'>
			              <td class='center' align='center' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0;vertical-align: top;text-align: center;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;'>
			                <center style='width: 100%;min-width: 580px;'>

			                  <table class='container' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: inherit;width: 580px;margin: 0 auto;'>
			                    <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                      <td class='wrapper last' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;position: relative;padding-right: 0px;border-collapse: collapse !important;'>

			                        <table class='twelve columns' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;margin: 0 auto;width: 580px;'>
			                          <tr style='padding: 0;vertical-align: top;text-align: left;'>

			                            <td class='six sub-columns' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;min-width: 0px;padding-right: 10px;width: 50%;border-collapse: collapse !important;'>
			                              <img src='http://bipme.co/app/img/bipme_logo_white.png' style='outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;width: auto;max-width: 50px;float: left;clear: both;display: block;'>
			                            </td>
			                            <td class='six sub-columns last' align='right' style='text-align: right;vertical-align: middle;word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;min-width: 0px;padding-right: 0px;width: 50%;border-collapse: collapse !important;'>
			                              <span class='template-label' style='color: #ffffff;font-weight: bold;font-size: 11px;'>Bipme</span>
			                            </td>
			                            <td class='expander' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;'></td>

			                          </tr>
			                        </table>

			                      </td>
			                    </tr>
			                  </table>

			                </center>
			              </td>
			            </tr>
			          </table>
			          <br>

			          <table class='container' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: inherit;width: 580px;margin: 0 auto;'>
			            <tr style='padding: 0;vertical-align: top;text-align: left;'>
			              <td style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;'>

			                <!-- content start -->
			                <table class='row' style='border-spacing: 0;border-collapse: collapse;padding: 0px;vertical-align: top;text-align: left;width: 100%;position: relative;display: block;'>
			                  <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                    <td class='wrapper last' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;position: relative;padding-right: 0px;border-collapse: collapse !important;'>

			                      <table class='twelve columns' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;margin: 0 auto;width: 580px;'>
			                        <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                          <td style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;'>

			                            <h1 style='color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;padding: 0;margin: 0;text-align: left;line-height: 1.3;word-break: normal;font-size: 40px;'>¡Hola!</h1>
			                            <br>
			                            <p class='lead' style='margin: 0;margin-bottom: 10px;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;padding: 0;text-align: left;line-height: 21px;font-size: 18px;'>" . $client_name ." te ha enviado un mensaje importante a través de <a style='color:#ff9500; text-decoration:none'href='http://bipme.co/app/'>Bipme</a>...</p>

			                          </td>
			                          <td class='expander' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;'></td>
			                        </tr>
			                      </table>

			                    </td>
			                  </tr>
			                </table>

			                <table class='row callout' style='border-spacing: 0;border-collapse: collapse;padding: 0px;vertical-align: top;text-align: left;width: 100%;position: relative;display: block;'>
			                  <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                    <td class='wrapper last' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;position: relative;padding-right: 0px;border-collapse: collapse !important;'>

			                      <table class='twelve columns' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;margin: 0 auto;width: 580px;'>
			                        <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                          <td class='panel' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px !important;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;background: white;border: 1px solid #d9d9d9;border-color: #ff9500;border-collapse: collapse !important;'>
			                            <img src='" . $client_image . "' style='outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;width: auto;max-width: 50px; border-radius:25px;float: left;clear: both;display: block;'>
			                            <p style='text-align: justify;margin-top: 8px;margin-left: 60px;margin-bottom: 10px;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;padding: 0;line-height: 19px;font-size: 14px;'><span style='font-weight: bold;'>" . $client_name ."</span>: " . $message . "<a href='http://bipme.co/app/' style='color: #50bca8;text-decoration: none;'> Descarga Bipme para ver el mensaje »</a></p>

			                          </td>
			                          <td class='expander' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;'></td>
			                        </tr>
			                      </table>

			                    </td>
			                  </tr>
			                </table>

			                <table class='row' style='border-spacing: 0;border-collapse: collapse;padding: 0px;vertical-align: top;text-align: left;width: 100%;position: relative;display: block;'>
			                  <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                    <td class='wrapper last' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;position: relative;padding-right: 0px;border-collapse: collapse !important;'>

			                      <table class='twelve columns' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;margin: 0 auto;width: 580px;'>
			                        <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                          <td style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;'>

			                            <h3 style='color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;padding: 0;margin: 0;text-align: left;line-height: 1.3;word-break: normal;font-size: 32px;'>¿Que es <a style='color:#ff9500; text-decoration:none'href='http://bipme.co/app/'>Bipme</a>?</h3>
			                            <br>
			                            <p style='margin: 0;margin-bottom: 10px;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;padding: 0;text-align: left;line-height: 19px;font-size: 14px;'>Con Bipme te olvidas de los correos infinitos y los mensajes sin sentido. Usando nuestra aplicación recibirás mensajes cortos a través de notificaciones con la información que realmente te importa.</p>

			                          </td>
			                          <td class='expander' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;'></td>
			                        </tr>
			                      </table>

			                    </td>
			                  </tr>
			                </table>


			                <table class='row' style='border-spacing: 0;border-collapse: collapse;padding: 0px;vertical-align: top;text-align: left;width: 100%;position: relative;display: block;'>
			                  <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                    <td class='wrapper last' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;position: relative;padding-right: 0px;border-collapse: collapse !important;'>

			                      <table class='three columns' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;margin: 0 auto;width: 130px;'>
			                        <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                          <td style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;'>

			                            <a href='http://bipme.co/app/' style='color: #ffffff;text-decoration: none;font-weight: bold;font-family: Helvetica, Arial, sans-serif;font-size: 16px;'><table class='button' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;width: 100%;overflow: hidden;'>
			                              <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                                <td style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 8px 0;vertical-align: top;text-align: center;color: #ffffff;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;display: block;background: #ff9500;border: 1px solid;border-collapse: collapse !important;width: auto !important;'>
			                                  Download
			                                </td>
			                              </tr>
			                            </table></a>
			                            <br/>
			                            <br/>

			                          </td>
			                          <td class='expander' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;'></td>
			                        </tr>
			                      </table>

			                    </td>
			                  </tr>
			                </table>


			                <table class='row footer' style='border-spacing: 0;border-collapse: collapse;padding: 0px;vertical-align: top;text-align: left;width: 100%;position: relative;display: block;'>
			                  <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                    <td class='wrapper' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;position: relative;background: #eaedf1;border-collapse: collapse !important;'>

			                      <table class='six columns' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;margin: 0 auto;width: 280px;'>
			                        <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                          <td class='left-text-pad' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;padding-left: 10px;border-collapse: collapse !important;'>

			                            <h5 style='color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;padding: 0;margin: 0;text-align: left;line-height: 1.3;word-break: normal;font-size: 24px;padding-bottom: 10px;'>Somos sociales:</h5>

			                             <a href='https://www.facebook.com/bipmeapp' style='color: #ffffff;text-decoration: none;font-weight: normal;font-family: Helvetica, Arial, sans-serif;font-size: 12px;'><table class='tiny-button facebook' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;width: 100%;overflow: hidden;'>
			                              <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                                <td style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 5px 0 4px;vertical-align: top;text-align: center;color: #ffffff;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;display: block;background: #3b5998;border: 1px solid #2284a1;border-color: #2d4473;border-collapse: collapse !important;width: auto !important;'>
			                                 Facebook
			                                </td>
			                              </tr>
			                            </table></a>

			                            <br>

			                            <a href='https://twitter.com/bipmeapp' style='color: #ffffff;text-decoration: none;font-weight: normal;font-family: Helvetica, Arial, sans-serif;font-size: 12px;'><table class='tiny-button twitter' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;width: 100%;overflow: hidden;'>
			                              <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                                <td style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 5px 0 4px;vertical-align: top;text-align: center;color: #ffffff;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;display: block;background: #00acee;border: 1px solid #2284a1;border-color: #0087bb;border-collapse: collapse !important;width: auto !important;'>
			                                  Twitter
			                                </td>
			                              </tr>
			                            </table></a>

			                            <br>

			                          </td>
			                          <td class='expander' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;'></td>
			                        </tr>
			                      </table>

			                    </td>
			                    <td class='wrapper last' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;position: relative;background: #eaedf1;padding-right: 0px;border-collapse: collapse !important;'>

			                      <table class='six columns' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;margin: 0 auto;width: 280px;'>
			                        <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                          <td class='last right-text-pad' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;padding-right: 0px;border-collapse: collapse !important;'>
			                            <p style='margin: 0;margin-bottom: 10px;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;padding: 0;text-align: left;line-height: 19px;font-size: 14px;'>¿Quieres ponerte en contacto?</p>
			                            <p style='margin: 0;margin-bottom: 10px;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;padding: 0;text-align: left;line-height: 19px;font-size: 14px;'>Habla con <a href='mailto:lisa@bipme.co' style='color: #50bca8;text-decoration: none;'>lisa@bipme.co</a></p>
			                          </td>
			                          <td class='expander' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;'></td>
			                        </tr>
			                      </table>

			                    </td>
			                  </tr>
			                </table>

			                <br/>
			                <table class='row' style='border-spacing: 0;border-collapse: collapse;padding: 0px;vertical-align: top;text-align: left;width: 100%;position: relative;display: block;'>
			                  <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                    <td class='wrapper last' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;position: relative;padding-right: 0px;border-collapse: collapse !important;'>

			                      <table class='twelve columns' style='border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;margin: 0 auto;width: 580px;'>
			                        <tr style='padding: 0;vertical-align: top;text-align: left;'>
			                          <td align='center' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;'>
			                            <center style='width: 100%;min-width: 580px;'>
			                              <p style='text-align: center;margin: 0;margin-bottom: 10px;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;padding: 0;line-height: 19px;font-size: 14px;'><a href='http://bipme.co/app//terms.php' style='color: #50bca8;text-decoration: none;'>Terms</a> | <a href='http://bipme.co/app//privacy.php' style='color: #50bca8;text-decoration: none;'>Privacy</a> | <a href='" . base_url() . "add/unsusbcribe/$username' style='color: #50bca8;text-decoration: none;'>Unsubscribe</a></p>
			                            </center>
			                          </td>
			                          <td class='expander' style='word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;'></td>
			                        </tr>
			                      </table>

			                    </td>
			                  </tr>
			                </table>

			                <!-- container end below -->
			              </td>
			            </tr>
			          </table>

			        </center>
			      </td>
			    </tr>
			  </table>
			</body>
			</html>";

		$this->email->message($message2);

		//send and email to the user
		if($this->model_add->insert_notification_email_api($username, $message, $client_id)){
			if($this->email->send()){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	public function insert_notification_email_api($username, $message, $client_id){

		$data = array(
			'client_id' => $client_id,
			'email' => $username,
			'message' => $message
			);

		$query = $this->db->insert('posts_emails', $data);

		if($query){
			$this->session->set_userdata('user_email', $data['email']);
			return true;
		}else{
			return false;
		}

	}

}





