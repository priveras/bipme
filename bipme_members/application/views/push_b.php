<div class="wrapper">
	<div class="wrapper_menu">
        <a href="<?php echo base_url()?>main/members">
		<div class="submenu_element">
			<p class="wrapper_menu_text">Individual</p>
		</div>
        </a>
		<div class="submenu_element_on">
			<p class="wrapper_menu_text">En Bulto</p>
		</div>
		<!--<div class="submenu_element">
			<p class="wrapper_menu_text">Hold</p>
		</div>-->
	</div>	
	<div class="title">
		<h1>Mandar notificación a todos los usuarios de una lista</h1>
	</div>
	<div class="subtitle">
		<p><?php 
        if(empty($posts)){
            echo "Aún no tienes textos activos";
        }else{
            echo "Selecciona la lista para enviar la notificación";
        }?>
            </p>
	</div>
	<table>
            <tr>
               	<td id="form">
            		<?php

                    $i = 0;

            		echo form_open('add/insert_bulk_notifications');

            		//echo validation_errors();

                    $text = array(
                        'name' => 'message',
                        'id' => 'form_text',
                        'placeholder' => 'Mensaje. Máximo 120 caracteres',
                        'maxlength'=> '120'
                        );

                    echo form_input($text);

            		$options = array(
            			'error' => 'Select list...',
            			);

            		foreach ($lists as $row) {
            			$list_id = $row['id'];
            			$list = $row['list'];
            			$options[$list_id] = $list;
            		}


            		echo form_dropdown('list_id', $options, '', 'class="dropdown"');

            		$submit = array(
            			'name' => 'add',
            			'id' => 'form_submit' . $i,
                        'class' => 'form_submit',
            			'value' => '+',
                        'onClick' => 'hideSubmit(' . $i .')'
            			);

            		echo form_submit($submit);

                    echo "<div id='loading" . $i ."' class='loading'><img src='http://bipme.co/bipme_members/assets/img/loading.gif'/></div>";

            		echo form_close();

            		?>
            	</td>

            </tr>
    </table>
</div>