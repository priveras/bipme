		<?php

		if($info !== FALSE){

			$client_id = $this->session->userdata('client_id') - 1;
         
            ?>
            <div class="wrapper">
            	<div id="account_image">
            		<img src="<?php echo $info[$client_id]['image']?>"/>
            	</div>
            	<div id="account_title">
            		<h1><?php echo $info[$client_id]['name']?></h1>
            	</div>
            	<table id="account_table">

            <tr>
			<th id="account_heading">Nombre:</th>
			<?php $i = 1 ?>
			<td class="info" id="info<?php echo $i ?>"><?php echo $info[$client_id]['name']?></td>
			<td class="form_edit" id="form_edit<?php echo $i ?>"><?php

            		echo form_open('add/update_account');

            		//echo validation_errors();

            		$text = array(
            			'name' => 'text',
            			'id' => 'edit_text',
            			'value' => $info[$client_id]['name'],
            			'autofocus'   => 'autofocus',
            			);

            		echo form_input($text);

            		$text2 = array(
            			'name' => 'i',
            			'type' => 'hidden',
            			'value' => $i
            			);

            		echo form_input($text2);

            		$submit = array(
            			'name' => 'add_submit',
            			//'id' => 'form_submit' . $i,
                        'class' => 'edit_submit',
            			'value' => '+',
            			'onClick' => 'hideSubmit(' . $i .')'
            			);  

            		echo form_submit($submit);

            		echo form_close();

            		?>
            	</td>
            	<td id="id"><a onclick="hideName('<?php echo $i ?>')"><div><img src="<?php echo base_url()?>/assets/img/edit.png"/></div></a></td>
		</tr>

		<tr>
			<th id="account_heading">Usuario:</th>
			<td><?php echo $this->session->userdata('client_username')?></td>
		</tr>
		<tr>
			<th id="account_heading">Email:</th>
			<?php $i = 2 ?>
			<td id="info<?php echo $i ?>"><?php echo $info[$client_id]['email']?></td>
			<td class="form_edit" id="form_edit<?php echo $i ?>"><?php


            		echo form_open('add/update_account');

            		//echo validation_errors();

            		$text = array(
            			'name' => 'text',
            			'id' => 'edit_text',
            			'value' => $info[$client_id]['email'],
            			'autofocus'   => 'autofocus',
            			);

            		echo form_input($text);

            		$text2 = array(
            			'name' => 'i',
            			'type' => 'hidden',
            			'value' => $i
            			);

            		echo form_input($text2);

            		$submit = array(
            			'name' => 'add_submit',
            			//'id' => 'form_submit' . $i,
                        'class' => 'edit_submit',
            			'value' => '+',
            			'onClick' => 'hideSubmit(' . $i .')'
            			);  

            		echo form_submit($submit);

            		echo form_close();

            		?>
            	</td>
            	<td id="id"><a onclick="hideName('<?php echo $i ?>')"><div><img src="<?php echo base_url()?>/assets/img/edit.png"/></div></a></td>
		</tr>
		<tr>
			<th id="account_heading">Teléfono:</th>
			<?php $i = 3 ?>
			<td id="info<?php echo $i ?>"><?php echo $info[$client_id]['tel']?></td>
			<td class="form_edit" id="form_edit<?php echo $i ?>"><?php


            		echo form_open('add/update_account');

            		//echo validation_errors();

            		$text = array(
            			'name' => 'text',
            			'id' => 'edit_text',
            			'value' => $info[$client_id]['tel'],
            			'autofocus'   => 'autofocus',
            			);

            		echo form_input($text);

            		$text2 = array(
            			'name' => 'i',
            			'type' => 'hidden',
            			'value' => $i
            			);

            		echo form_input($text2);

            		$submit = array(
            			'name' => 'add_submit',
            			//'id' => 'form_submit' . $i,
                        'class' => 'edit_submit',
            			'value' => '+',
            			'onClick' => 'hideSubmit(' . $i .')'
            			);  

            		echo form_submit($submit);

            		echo form_close();

            		?>
            	</td>
            	<td id="id"><a onclick="hideName('<?php echo $i ?>')"><div><img src="<?php echo base_url()?>/assets/img/edit.png"/></div></a></td>
		</tr>
		<tr>
			<th id="account_heading">Cuenta:</th>
			<td><?php echo $this->session->userdata('client_suscription')?>: <?php echo $this->session->userdata('client_lists')?> listas y <?php echo $this->session->userdata('client_notifications')?> notificaciones al mes</td>
		</tr>	
		<tr>
			<th id="account_heading">Imagen:</th>
			<?php $i = 4 ?>
			<td id="info<?php echo $i ?>"><?php echo $info[$client_id]['image']?></td>
			<td class="form_edit" id="form_edit<?php echo $i ?>"><?php


            		echo form_open('add/update_account');

            		//echo validation_errors();

            		$text = array(
            			'name' => 'text',
            			'id' => 'edit_text',
            			'value' => $info[$client_id]['image'],
            			'autofocus'   => 'autofocus',
            			);

            		echo form_input($text);

            		$text2 = array(
            			'name' => 'i',
            			'type' => 'hidden',
            			'value' => $i
            			);

            		echo form_input($text2);

            		$submit = array(
            			'name' => 'add_submit',
            			//'id' => 'form_submit' . $i,
                        'class' => 'edit_submit',
            			'value' => '+',
            			'onClick' => 'hideSubmit(' . $i .')'
            			);  

            		echo form_submit($submit);

            		echo form_close();

            		?>
            	</td>
            	<td id="id"><a onclick="hideName('<?php echo $i ?>')"><div><img src="<?php echo base_url()?>/assets/img/edit.png"/></div></a></td>
		</tr>


		<?php
        }else{
            echo "No news to see here!";
        }

		?>
	</table>
      <!--<a href="<?php echo site_url('add/update_table/'); ?>"><h1>GO!</h1></a>-->
</div>
</div>