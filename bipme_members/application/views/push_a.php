<div class="wrapper">
	<div class="wrapper_menu">
		<div class="submenu_element_1_on">
			<p class="wrapper_menu_text">Enviar</p>
		</div>
	</div>	
	<div class="title">
		<h1>Mandar notificación</h1>
	</div>
	<div class="subtitle">
		<p>Para mandar notificación individual ingresa el nombre del usuario</p>
	</div>
	<table >
        <tr>
            <td id="form">
                <?php

                $i = 0;

                echo form_open('add/add_validation');
                //echo validation_errors();

                $text = array(
                    'name' => 'message',
                    'id' => 'form_text',
                    'placeholder' => 'Mensaje. Máximo 120 caracteres',
                    'maxlength'=> '120'
                    );

                echo form_input($text);

                $text2 = array(
                    'name' => 'username',
                    'id' => 'form_text2',
                    'placeholder' => 'Nombre de usuario o email'
                    );

                echo form_input($text2);

                $submit = array(
                    'name' => 'add',
                    'id' => 'form_submit' . $i,
                    'class' => 'form_submit',
                    'value' => '+',
                    'onClick' => 'hideSubmit(' . $i .')'
                    );

                echo form_submit($submit);

                echo form_close();
                ?>
            </td>
        </tr>
         <tr>
                <td id="form">
                    <?php

                    $i = 1;

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