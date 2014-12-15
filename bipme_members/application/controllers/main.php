<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CI_Controller {


	public function __construct(){

		parent::__construct();
		$this->load->model('model_clients');
		$this->load->model('model_feed');
		$this->load->model('model_add');
	}

	public function index(){
		$this->login();
	}

	public function restricted(){
		$this->load->view('restricted');
	}

	public function logout(){
		unset($this->session->userdata); 
		$this->session->sess_destroy();
		redirect('');
	}

	public function signup(){
		$this->load->view('signup');
	}

	public function signup_validation(){

		$this->load->library('form_validation');

		$this->form_validation->set_rules('username', 'Username', 'required|trim|is_unique[clients.username]|is_unique[temp_clients.username]');
		$this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|');
		$this->form_validation->set_rules('name', 'Name', 'required');
		$this->form_validation->set_rules('image', 'Image', 'required');
		$this->form_validation->set_rules('tel', 'Image', 'required');
		$this->form_validation->set_rules('category', 'Category', 'required');
		$this->form_validation->set_rules('password', 'Password', 'required|trim');
		$this->form_validation->set_rules('cpassword', 'Confirm Password', 'required|trim|matches[password]');

		$this->form_validation->set_message('is_unique', 'That %s address already exists.');

		if($this->form_validation->run()){

			$client_key = md5(uniqid());

			if($this->model_clients->add_temp_client($client_key)){
				//echo Success adding to temp_clients
				$this->load->view('email_sent');
			}else{
				echo "Problem adding to temp_clients.";
			}

		}else{
			//echo The form validation didn't run
			$this->load->view('signup');
		}
	}

	public function register_client($client_key){

		if($this->model_clients->is_key_valid($client_key)){
			if($newemail = $this->model_clients->add_client($client_key)){
				//echo "The client has been confirmed";
				$this->load->view('email_confirmation');
			}else{
				echo "Failed to add user, please try again.";
			}
		}else{
			echo "invalid key";
		}
	}

	public function login(){
		$this->load->view('login');
	}

	public function login_validation(){

		$this->load->library('form_validation');

		$this->form_validation->set_rules('username', 'Username', 'required|trim|xss_clean|callback_validate_credentials');
		$this->form_validation->set_rules('password', 'Password', 'required|md5|trim');

		if ($this->form_validation->run()){

				$is_logged_in = 1;
				
			$this->session->set_userdata('is_logged_in', $is_logged_in);
			redirect('main/members');
		}else{
			//echo The form validation didn't run
			$this->load->view('login');
		}
	}

	public function validate_credentials(){

		if($this->model_clients->can_log_in()){
			return true;
		}else{
			$this->form_validation->set_message('validate_credentials', 'Incorrect username/password.');
			return false;
		}
	}

	public function account(){
		if($this->session->userdata('is_logged_in') == 1){

			$data = array();
			$data['info'] = $this->model_feed->get_info();

			$this->load->view('header');
			$this->load->view('account', $data);
			$this->load->view('footer');

			
		}else{
			redirect('main/restricted');
		}
	}

	public function members(){
		if($this->session->userdata('is_logged_in') == 1){

			$data = array();
			$data['lists'] = $this->model_feed->get_lists();

			$this->load->view('header');
			$this->load->view('push_a', $data);
			$this->load->view('footer');
			
		}else{
			redirect('main/restricted');
		}
	}

	public function lists_a(){
		if($this->session->userdata('is_logged_in') == 1){

		    $data = array();
			$data['lists'] = $this->model_feed->get_lists();
			$data['count_lists_users'] = $this->model_feed->count_lists_users();

			$this->load->view('header');
			$this->load->view('lists_a', $data);
			$this->load->view('footer');
			
		}else{
			redirect('main/restricted');
		}
	}

	public function lists_a1($list_id){
		if($this->session->userdata('is_logged_in') == 1){

		    $data = array();
		    $data['lists'] = $this->model_add->get_lists_info($list_id);
		    $data['lists_users'] = $this->model_feed->get_lists_users($list_id);

			$this->load->view('header');
			$this->load->view('lists_a1', $data);
			$this->load->view('footer');
			
		}else{
			redirect('main/restricted');
		}
	}

	public function lists_b(){
		if($this->session->userdata('is_logged_in') == 1){

		    $data = array();
			$data['temp_clients_users'] = $this->model_feed->get_temp_clients_users();

			$this->load->view('header');
			$this->load->view('lists_b', $data);
			$this->load->view('footer');
			
		}else{
			redirect('main/restricted');
		}
	}

	public function lists_c(){
		if($this->session->userdata('is_logged_in') == 1){

		    $data = array();
			$data['lists'] = $this->model_feed->get_lists();
			$data['count_lists_users'] = $this->model_feed->count_lists_users();

			$this->load->view('header');
			$this->load->view('lists_c', $data);
			$this->load->view('footer');
			
		}else{
			redirect('main/restricted');
		}
	}

	public function notifications_a(){
		if($this->session->userdata('is_logged_in') == 1){

		    $data = array();
			$data['notifications'] = $this->model_feed->get_notifications();

			$this->load->view('header');
			$this->load->view('notifications_a', $data);
			$this->load->view('footer');
			
		}else{
			redirect('main/restricted');
		}
	}

	public function insert_bulk_notifications(){

		if ( isset($_GET['uid']) ) {

			$uid = $_GET['uid'];

			$this->model_add->insert_bulk_notifications($uid);
			$this->session->set_userdata('message', 'Se ha mandado la notificación a toda la lista');
			redirect('main/members');
		} else {
			echo "We didn't get the uid";
		}
	}

	public function feed(){
		if($this->session->userdata('is_logged_in') == 1){

			$this->load->model('json_model');

			$user_id = 1;

			$data = array();
			$data['posts'] = $this->json_model->get_posts_user($user_id);

			$this->load->view('header');
			$this->load->view('feed', $data);
			$this->load->view('footer');
			
		}else{
			redirect('main/restricted');
		}
	}
}