    <?php

    if($this->session->userdata('message')){
    	echo "<p>" . $this->session->userdata('message') . "</p>";
    	$this->session->unset_userdata('message');
    }else{?>

	<p>Enter your username or email<br/> to reset your password</p>
	<?php

	    echo form_open('main/forgot');

	    $username = array(
	    	'name' => 'username_email',
	    	'id' => 'form_text',
	    	'value' => $this->input->post('username_email'),
	    	'placeholder' => 'Username or email'
	    	);

	    echo "<div id='username'>";
	    echo form_input($username);
	    echo "</div>";


        $submit = array(
        	'name' => 'login_submit',
        	'id' => 'form_submit',
        	'value' => 'Reset Password',
        	);

	    echo "<div id='submit'><p>";
	    echo form_submit($submit);
	    echo "</p></div>";

	    echo validation_errors();

	    echo form_close();

	    }?>
</div>
</body>
</html>