<div class="wrapper">
    <div class="wrapper_menu">
        <div class="submenu_element_1_on">
            <p class="wrapper_menu_text">Database</p>
        </div>
    </div>
	<div class="title">
		<h1>Listas Activas</h1>
	</div>
	<div class="subtitle">
		<p><?php if (empty($lists)){
            echo "Aún no tienes regsitros en tu base de datos";
        }else{
            echo "Has click en el nombre para ver usuarios de la lista";
        } ?>
        </p>
	</div>
    <div id="add_temp_post">
        <?php 

        $i = 0;

        echo form_open('add/insert_db');

        $add_user = array(
            'name' => 'user_db',
            'id' => 'form_text',
            'placeholder' => 'Inserta un usuario a tu base de datos'
            );

        echo form_input($add_user);


        $submit_user = array(
            'name' => 'add_user',
            'id' => 'form_submit' . $i,
            'class' => 'form_submit',
            'value' => '+',
            'onClick' => 'hideSubmit(' . $i .')'
            );

        echo form_submit($submit_user);

        echo form_close();
        ?>

    </div>
	<table>
      
    </table>
</div>