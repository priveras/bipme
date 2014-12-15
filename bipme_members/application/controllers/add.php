<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

ob_start();

class Add extends CI_Controller{

	public function __construct(){

		parent::__construct();
		$this->load->model('model_feed');
		$this->load->model('model_add');
	}

	public function add_validation(){

		$this->load->library('form_validation');	
		$this->form_validation->set_rules('add', 'Add', 'required|trim'); 
		$this->form_validation->set_rules('message', 'Message', 'required|trim'); 

		if ($this->form_validation->run()){

			$message = $this->input->post('message');
			$username = $this->input->post('username');

			$this->load->helper('email');

			if (valid_email($username)){

				if($data = $this->model_add->check_email($username)){

					$username = $data['username'];

					if($this->insert_notification($message, $username)){
						redirect('main/members');
					}else{
						echo "Something went wrong";
						redirect('main/members');
					}
				}else{
					$this->send_email($message, $username);
				}
			}else{

				if($this->insert_notification($message, $username)){
					redirect('main/members');
				}else{
					echo "Something went wrong";
					redirect('main/members');
				}
			}
		}else{
			$this->session->set_userdata('message', 'No escribiste ningún mensaje');
			redirect('main/members');
		}
	}

	public function insert_notification($message, $username){

		if($data = $this->model_add->get_user_info($username)){

			if($this->model_add->insert_notification($data, $message)){

				if($this->model_add->send_push($data, $message)){

					$this->session->set_userdata('message', 'Se ha enviado la notificación a ' . $this->session->userdata('user_username'));
					return true;

				}else{
					$this->session->set_userdata('message2', 'Hubo un error, no se pudo mandar la notificación');
					return true;
				}
			}else{
				echo "The notifications wasn't inserted";
			}
		}else{
			echo "Couldn't get the user's info</br>";
		}
	}


	public function add_user_validation(){

		$this->load->library('form_validation');	
		$this->form_validation->set_rules('add', 'Add', 'required|trim'); 

		if ($this->form_validation->run()){

			echo 'The form validation ran members<br/>';

			$this->insert_clients_users();
			redirect('main/userslist');
		}else{
			echo "The form validation didn't ran<br/>";
			redirect('main/members');
		}
	}

	public function insert_clients_users(){

		$this->load->library('form_validation');	
		$this->form_validation->set_rules('add_user', 'Add', 'required|trim'); 

		if ($this->form_validation->run()){

			
			$variables = array(
				'client_id' => $this->session->userdata('client_id'),
				'username' => $this->input->post('add'),
				'list_id' => $this->input->post('list_id'),
				);

			$this->load->helper('email');

			if (valid_email($variables['username'])){

				if($data = $this->model_add->check_email($variables['username'])){

					$variables = array(
						'client_id' => $this->session->userdata('client_id'),
						'username' => $data['username'],
						'list_id' => $this->input->post('list_id'),
						);

					$this->insert_clients_users2($variables);

				}else{
					if($this->model_add->add_clients_users_email($variables)){
						$this->session->set_userdata('message', 'Fue insertada la dirección de email ' . $variables['username']);
						redirect('main/lists_a1/' . $variables['list_id']);
					}else{
						$this->session->set_userdata('message2', 'Ya tienes esa dirección de email registrada ');
						redirect('main/lists_a1/' . $variables['list_id']);
					}
				}
			}else{
				$this->insert_clients_users2($variables);
			}
		}
	}

	public function insert_clients_users2($variables){


		$list_text = $this->model_add->get_list_text($variables['list_id']);

		$message = 'Ya formas parte de la lista ' . $list_text['list'];

		if($data = $this->model_add->get_user_info($variables['username'])){

			if($this->model_add->insert_clients_users($data, $variables['list_id'], $variables['client_id'])){
				echo "El usuario fue insertado";
				$this->session->set_userdata('message', 'Fue insertado el usuario ' . $this->session->userdata('user_username'));

				$identifier = 0;

				if($this->model_add->insert_welcome_notification($data, $message, $identifier)){

					$this->model_add->send_push($data, $message);
					$this->session->set_userdata('message', 'Fue insertado el usuario ' . $this->session->userdata('user_username') . '. Y recibió mensaje de bienvenida');
					redirect('main/lists_a1/' . $variables['list_id']);
				}else{
					redirect('main/lists_a1/' . $variables['list_id']);
				}
			}else{
				$this->session->set_userdata('message2', 'Ese usuario ya estaba registrado');
				redirect('main/lists_a1/' . $variables['list_id']);
			}
		}else{
			redirect('main/lists_a1/' . $variables['list_id']);
		}
	}

	public function delete_user($id){

		if($this->model_add->delete_user($id)){
			$this->session->set_userdata('message', 'Se ha quitado el usuario de la lista');
			redirect('main/userslist');
		}else{
			echo "The post wasn't deleted";
		}
	}

	public function delete_post($id){

		if($this->model_add->delete_post($id)){
			$this->session->set_userdata('message', 'Se ha quitado el texto de tu cuenta');
			redirect('main/posts_a');
		}else{
			echo "The post wasn't deleted";
		}
	}

	public function delete_temp_post($id){

		if($this->model_add->delete_temp_post($id)){
			$this->session->set_userdata('message', 'Se ha quitado el texto para revisión');
			redirect('main/posts_b');
		}else{
			echo "The post wasn't deleted";
		}
	}

	public function add_temp_post_validation(){

		$this->load->library('form_validation');	
		$this->form_validation->set_rules('add', 'Add', 'required|trim'); 

		if ($this->form_validation->run()){

			$text = $_POST['add'];

			if($this->model_add->insert_temp_posts($text)){
				$this->session->set_userdata('message', 'Se ha insertado el texto para revisión');
				redirect('main/posts_b');
			}else{
				$this->session->set_userdata('message2', 'Ya tienes un texto igual');
				redirect('main/posts_b');
			}
		}else{
			$this->session->set_userdata('message2', 'El campo esta vacío');
			redirect('main/posts_b');
		}
	}

	public function add_list(){

		$this->load->library('form_validation');	
		$this->form_validation->set_rules('add', 'Add', 'required|trim'); 

		if ($this->form_validation->run()){

			echo 'The form validation ran members<br/>';
			$text = $_POST['add'];

			if($this->model_add->check_category()){
				if($this->model_add->insert_list($text)){
					$this->session->set_userdata('message', 'Se ha insertado la lista');
					redirect('main/lists_c');
				}else{
					$this->session->set_userdata('message2', 'Ya tienes una lista con ese nombre');
					redirect('main/lists_c');
				}
			}else{
				$this->session->set_userdata('message2', 'No puedes crear más listas, llegaste al límite de tu paquete');
				redirect('main/lists_c');
			}
		}else{
			$this->session->set_userdata('message2', 'El campo esta vacío');
			redirect('main/lists_c');
		}
	}

	public function delete_list($list_id, $client_id){

		if($this->model_add->delete_list($list_id, $client_id)){
			$this->session->set_userdata('message', 'Se ha eliminado la lista');
			redirect('main/lists_c');
		}else{
			echo "The post wasn't deleted";
		}
	}

	public function delete_user_from_list($id, $list_id){

		if($this->model_add->delete_user_from_list($id, $list_id)){
			$this->session->set_userdata('message', 'Se ha eliminado al usuario de la lista');
			redirect('main/lists_a1/'. $list_id);
		}else{
			echo "The post wasn't deleted";
		}
	}

	public function insert_bulk_notifications(){

		$this->load->library('form_validation');	
		$this->form_validation->set_rules('add', 'Add', 'required|trim'); 
		$this->form_validation->set_rules('message', 'Message', 'required|trim'); 

		if ($this->form_validation->run()){

			$message = $this->input->post('message');
			$list_id = $this->input->post('list_id');

			if (isset($message) && isset($list_id)) {
				if($list_id == "error"){
					$this->session->set_userdata('message', 'Debes de seleccionar una lista');
					redirect('main/members');
				}else{

					if($this->model_add->insert_bulk_notifications($message, $list_id)){
						$this->session->set_userdata('message', 'Se ha mandado la notificación a toda la lista');
						redirect('main/members');
					}else{
						$this->session->set_userdata('message', 'Se ha mandado la notificación a toda la lista incluyendo emails');
						redirect('main/members	');
					}
				}
			}

		}else{
			$this->session->set_userdata('message', 'No escribiste ningún mensaje');
			redirect('main/members');
		}
	}

	public function update_account(){

		$this->load->library('form_validation');	

		$post = $this->input->post('text');
		$i = $this->input->post('i');

		switch ($i) {
			case 1:
				$column = 'name';
				break;
			case 2:
				$column = 'email';
				break;
			case 3:
				$column = 'tel';
				break;
			case 4:
				$column = 'image';
				break;
		}

		if($this->model_add->update_account($post, $column)){
			$this->session->set_userdata('message', 'The ' . $column . ' was updated');
			redirect('main/account');
		}else{
			$this->session->set_userdata('message2', 'The post was not updated');
			redirect('main/account');
		}
	}

	public function update_list_description(){

		$this->load->library('form_validation');

		$post = $this->input->post('text');
		$list_id = $this->input->post('list_id');

		if($this->model_add->update_list_description($post, $list_id)){
			$this->session->set_userdata('message', 'La descripción de la lista se ha actualizado');
			redirect('main/lists_c');
		}else{
			$this->session->set_userdata('message2', 'No se pudo actualizar la descripción');
			redirect('main/lists_c');
		}
	}

	public function update_list_name(){

		$this->load->library('form_validation');

		$post = $this->input->post('text2');
		$list_id = $this->input->post('list_id2');

		if($this->model_add->update_list_name($post, $list_id)){
			$this->session->set_userdata('message', 'El nombre de la lista se ha actualizado');
			redirect('main/lists_c');
		}else{
			$this->session->set_userdata('message2', 'No se pudo actualizar el nombre');
			redirect('main/lists_c');
		}
	}

	public function delete_temp_clients_users($user_id, $list_id){

		$client_id = $this->session->userdata('client_id');

		if($this->model_add->delete_temp_clients_users($user_id, $client_id, $list_id)){
			$this->session->set_userdata('message', 'Se ha negado el acceso a la lista');
			redirect('main/lists_b');
		}else{
			echo "The post wasn't deleted";
		}
	}

	public function add_temp_clients_users($list_id, $username){

		$client_id = $this->session->userdata('client_id');

		if($data = $this->model_add->get_user_info($username)){

			if($data2 = $this->model_add->add_temp_clients_users($data, $list_id, $client_id)){
				echo "El usuario fue insertado";
				$this->session->set_userdata('message', 'Fue insertado el usuario ' . $this->session->userdata('user_username'));

				$message = "Ya eres parte de la lista " . $data2['list'];

				$identifier = 1;

				if($this->model_add->insert_welcome_notification($data, $message, $identifier)){

					$this->model_add->send_push($data, $message);
					$this->session->set_userdata('message', 'Fue insertado el usuario ' . $this->session->userdata('user_username') . '. Y recibió mensaje de bienvenida');
					redirect('main/lists_b');
				}else{
					redirect('main/lists_b');
				}
			}else{
				$this->session->set_userdata('message2', 'Ese usuario ya estaba registrado');
				redirect('main/lists_b');
			}
		}else{
			redirect('main/lists_b');
		}
	}

	public function update_table(){

		if($query = $this->model_add->get_posts_users_update()){
			foreach ($query as $row) {

				$id = $row['id'];
				$post_id = $row['post_id'];

				if($this->model_add->update_posts($id, $post_id)){
					echo "succes!";
				}else{
					echo "something didn't work on the update";
				}

			}
		}else{
			echo "something went wrong";
		}
	}

	public function unsubscribe($email){

		if($this->model_add->unsubscribe($email)){
			echo "You have been unsuscribed";
		}else{
			echo "Please try again something went wrong";
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
			                            <p style='text-align: justify;margin-top: 8px;margin-left: 60px;margin-bottom: 10px;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;padding: 0;line-height: 19px;font-size: 14px;'><span style='font-weight: bold;'>" . $client_name ."</span>: " . $message . "<a href='http://bipme.co/app/' style='color: #50bca8;text-decoration: none;'> Descarga Bipme y registrate con este correo para ver el mensaje »</a></p>

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
			                              <p style='text-align: center;margin: 0;margin-bottom: 10px;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;padding: 0;line-height: 19px;font-size: 14px;'><a href='http://bipme.co/terms.php' style='color: #50bca8;text-decoration: none;'>Terms</a> | <a href='http://bipme.co/privacy.php' style='color: #50bca8;text-decoration: none;'>Privacy</a> | <a href='" . base_url() . "add/unsubscribe/$username' style='color: #50bca8;text-decoration: none;'>Unsubscribe</a></p>
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
				$this->session->set_userdata('message', 'Se ha mandado un email con el mensaje a ' . $this->session->userdata('user_email'));
				redirect('main/members');
			}else{
				echo "Could not send email";
			}
		}else{
			echo "Problem adding to database.";
		}
	}
}

