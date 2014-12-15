<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CI_Controller {

	public function index(){
		$this->reset_password();
	}

	public function reset_password(){
		$this->load->view('header.php');
		$this->load->view('reset_password.php');
	} 

	public function forgot(){

		$this->load->library('form_validation');

		$this->form_validation->set_rules('username_email', 'Username', 'required|trim');

		if($this->form_validation->run()){

			$username_email = strtolower($this->input->post('username_email'));

			$this->load->model('model_users');

			$data = $this->model_users->get_user_info($username_email);

			if($data){

				$username_email = str_replace("@","-",$username_email);

				$key = md5($data['email']);

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
				$this->email->to($data['email']);
				$this->email->subject("Bipme - Reset password");

				$message = "<p>Please disregard this email if you didn't request to change your password</p>";
				$message .= "<p><a href='" . base_url() . "main/key/$key/$username_email '>Click here</a> to reset your passs</p>";

				$this->email->message($message);

				if($this->email->send()){
					$this->session->set_userdata('message', "We just sent you an email, <br/>please check your inbox or <br/>your spam email");
					redirect('main/reset_password');

				}else{
					$this->session->set_userdata('message', "We couldn't send you an email, please try again later");
					redirect('main/reset_password');
				}
			}else{
				redirect('main/reset_password');
			}
		}
	}

	public function key($key, $username_email){

		$data['key'] = $key;
		$username_email = $username_email;
		$data['username_email'] = $username_email;

		$this->load->view('header.php');
		$this->load->view('new_password', $data);
	}

	public function new_password(){

		$this->load->library('form_validation');

		$this->form_validation->set_rules('password', 'Password', 'required|trim');
		$this->form_validation->set_rules('password2', 'Confirm Password', 'required|trim|matches[password]');

		$password = $this->input->post('password');
		$key = $this->input->post('key');
		$username_email = str_replace("-","@",$this->input->post('username_email'));

		$this->load->model('model_users');

		if($this->model_users->reset_password($password, $key, $username_email)){
			$this->session->set_userdata('message', "Your password has been<br/>succesfully updated");
			redirect('main/reset_password');
		}else{
			$this->session->set_userdata('message', "We couldn't update your password,<br/>please try again later");
			redirect('main/reset_password');
		}

	}
}



