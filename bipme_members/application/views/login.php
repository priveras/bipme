<!DOCTYPE html>
<html lang="en" class="no-js">
	<head>
		<link rel="shortcut icon" href="<?php echo base_url()?>assets/img/favicon.ico" type="image/icon">
		<link rel="icon" href="<?php echo base_url()?>assets/img/favicon.ico" type="image/icon">
		<title>Bipme | Miembros</title>
		<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>/assets/styles/login.css">

		<!-- Mobile Specifi Metas -->
    	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	</head>
	<div id="wrapper">
		<div id="logo">
			<div id="logo_image">
				<img src="<?php echo base_url();?>/assets/img/bipme_logo.png"/>
			</div>
			<div id="logo_text">
				<h1>Bipme</h1>
			</div>
		</div>
		<div id="login">
			<div id="login-div">
				<?php

				echo form_open('main/login_validation');

				$username = array(
					'name' => 'username',
	    			'id' => 'username_input',
	    			'value' => $this->input->post('username'),
	    			'placeholder' => 'Username'
	    			);

	    		echo "<div id='username'>";
	    		echo form_input($username);
	    		echo "</div>";

	    		$password = array(
	    			'name' => 'password',
	    			'id' => 'password_input',
	    			'placeholder' => 'Password'
	    			);

	    		echo "<div id='password'>";
	    		echo form_password($password);
	    		echo "</div>";

        		$submit = array(
        			'name' => 'login_submit',
        			'id' => 'submit_type',
        			'value' => 'Log In',
        			);

	    		echo "<div id='submit'><p>";
	    		echo form_submit($submit);
	    		echo "</p></div>";

	    		echo validation_errors();

	    		echo form_close();

	    		?>
	    	</div>
    	</div>
    </div>
</body>
</html>
