<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Add extends CI_Controller {

	public function add_to_temp_list($username, $user_id, $client_id, $list_id, $privacy){

		$this->load->model('model_feed');
		$this->load->model('model_add');
		$user_id_checked = $this->model_feed->get_user_id($username, $user_id);

		if($user_id_checked){

			if($data = $this->model_add->get_user_info($user_id_checked)){

				if($this->model_feed->insert_temp_clients_users($user_id_checked, $client_id, $list_id)){

					/*$data2 = $this->model_feed->get_list_text($list_id);
					$message = "Estás esperando aprobación para entrar a la lista " . $data2['list'];
					$this->model_add->insert_welcome_notification($data, $message);
					$this->model_add->send_push($data, $message);*/
					redirect('main/suscribe2/'  . $client_id . '/' . $username . '/' . $user_id . '/' . $privacy);
				}else{
					redirect('main/suscribe2/'  . $client_id . '/' . $username . '/' . $user_id . '/' . $privacy);
				}
			}else{
				redirect('main/suscribe2/'  . $client_id . '/' . $username . '/' . $user_id . '/' . $privacy);
			}
		}
	}

	public function add_to_list($username, $user_id, $client_id, $list_id, $privacy){

		$this->load->model('model_feed');
		$this->load->model('model_add');
		$user_id_checked = $this->model_feed->get_user_id($username, $user_id);

		if($user_id_checked){

			if($data = $this->model_add->get_user_info($user_id_checked)){

				if($this->model_feed->insert_clients_users($user_id_checked, $client_id, $list_id)){

					$data2 = $this->model_feed->get_list_text($list_id);
					$message = "Ya eres parte de la lista " . $data2['list'];
					$this->model_add->insert_welcome_notification($data, $message, $client_id);
					redirect('main/suscribe2/'  . $client_id . '/' . $username . '/' . $user_id . '/' . $privacy);
				}else{
					redirect('main/suscribe2/'  . $client_id . '/' . $username . '/' . $user_id  . '/' . $privacy);
				}
			}else{
				redirect('main/suscribe2/'  . $client_id . '/' . $username . '/' . $user_id  . '/' . $privacy);
			}
		}
	}
}

