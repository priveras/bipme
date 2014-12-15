<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<link rel="shortcut icon" href="<?php echo base_url()?>assets/img/favicon.ico" type="image/icon">
    <link rel="icon" href="<?php echo base_url()?>assets/img/favicon.ico" type="image/icon">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url()?>/assets/styles/signup.css"/>
	<title>Signup page</title>
</head>
<body>
	<div id="wrapper">
		<div id="logo">
    		<div id="logo_img">
    			<img src="<?php echo base_url()?>assets/img/bipme_logo.png">
    		</div>
    		<div id="logo_text">
    			<p>Bipme</p>
    		</div>
    	</div>
	<?php

	echo form_open('main/signup_validation');

	echo validation_errors();

	$this->form_validation->set_error_delimiters('<p>', '</p>');

	$username = array(
		'name' => 'username',
		'id' => 'form_text',
		'value' => $this->input->post('username'),
		'placeholder' => 'Username'
		);

	echo form_input($username);

	$email = array(
		'name' => 'email',
		'id' => 'form_text',
		'value' => $this->input->post('email'),
		'placeholder' => 'Email'
		);

	echo form_input($email);

	$name = array(
		'name' => 'name',
		'id' => 'form_text',
		'value' => $this->input->post('name'),
		'placeholder' => 'Name'
		);

	echo form_input($name);

	$image = array(
		'name' => 'image',
		'id' => 'form_text',
		'value' => $this->input->post('image'),
		'placeholder' => 'Image'
		);

	echo form_input($image);

	$tel = array(
		'name' => 'tel',
		'id' => 'form_text',
		'value' => $this->input->post('tel'),
		'placeholder' => 'Telephone'
		);

	echo form_input($tel);

    $category = array(
		'name' => 'category',
		'id' => 'form_text',
		'value' => $this->input->post('category'),
		'placeholder' => 'Category'
		);

	echo form_input($category);

	$password1 = array(
		'name' => 'password',
		'id' => 'form_text',
		'placeholder' => 'Password'
		);

	echo form_password($password1);

	$password2 = array(
		'name' => 'cpassword',
		'id' => 'form_text',
		'placeholder' => 'Confirm Password'
		);

	echo form_password($password2);


	$submit = array(
		'name' => 'signup_submit',
		'id' => 'form_submit',
		'value' => 'Sign up!',
		);

	echo "<p>";
	echo form_submit($submit);
	echo "</p>";

	echo form_close();
	?>
</div>
</body>
</html>