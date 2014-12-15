	<p>Please type in your new password</p>
	<?

	    echo form_open('main/new_password');

	    $password = array(
	    	'name' => 'password',
	    	'id' => 'form_text',
	    	'placeholder' => 'New Password',
	    	'type' => 'password'
	    	);

	    echo "<div id='username'>";
	    echo form_input($password);
	    echo "</div>";

	    $password2 = array(
	    	'name' => 'password',
	    	'id' => 'form_text',
	    	'placeholder' => 'Repeat Password',
	    	'type' => 'password'
	    	);

	    echo "<div id='username'>";
	    echo form_input($password2);
	    echo "</div>";

	    $keyarray = array(
	    	'name' => 'key',
	    	'type' => 'hidden',
	    	'value' => $key
	    	);	

	    echo form_input($keyarray);

	    $usernamearray = array(
	    	'name' => 'username_email',
	    	'type' => 'hidden',
	    	'value' => $username_email
	    	);	

	    echo form_input($usernamearray);

        $submit = array(
        	'name' => 'login_submit',
        	'id' => 'form_submit',
        	'value' => 'Submit',
        	);

	    echo "<div id='submit'><p>";
	    echo form_submit($submit);
	    echo "</p></div>";

	    echo validation_errors();

	    echo form_close();

	    ?>
</div>
</body>
</html>