<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CI_Controller {

	public function index(){
		$this->suscribe();
	}

	public function suscribe($key, $user_id, $username){

		$key2 = md5('bipme');

		if($key == $key2){

			$this->load->model('model_feed');

			$data = array();
			$data['clients'] = $this->model_feed->get_clients();
			$data['user_id'] = $user_id;
			$data['username'] = md5($username);
			$data['key'] = $key;

			$this->load->view('header.php');
			$this->load->view('suscribe.php', $data);
		}else{
			echo "You don't have access";
		}
	} 

	public function suscribe2($client_id, $username, $user_id, $privacy){

		$this->load->model('model_feed');

		$data = array();
		$data['username'] = $username;
		$data['user_id'] = $user_id;
		$data['client_id'] = $client_id;
		$data['privacy'] = $privacy;

		
		$data['clients'] = $this->model_feed->get_client($client_id);
		$data['lists'] = $this->model_feed->get_lists($user_id, $client_id);

		$this->load->view('header.php');
		$this->load->view('suscribe2.php', $data);
	}
}



