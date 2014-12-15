<?php

class Model_add extends CI_Model{


	public function get_user_info($user_id_checked){

		if(isset($user_id_checked)) {

			$this->db->where('id', $user_id_checked);
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

	public function insert_welcome_notification($data, $message, $client_id){

		unset ($data['parse_token']);
		unset ($data['user_username']);
		unset ($data['device']);
		$data['client_id'] = $client_id;
		$data['message'] = $message;
			
		$query1 = $this->db->insert('posts_users', $data);
		$query2 = $this->db->insert('posts_users2', $data);

		if($query1 && $query2){
			return true;
		}else{
			return false;
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
			return true;
		}else{
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
} 