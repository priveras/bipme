<div class="wrapper">
<?php 

$privacy = $this->session->userdata('client_privacy');

if($privacy == 0 || $privacy == 2){?>

    <div class="wrapper_menu">
        <a href="<?php echo base_url()?>main/lists_a">
        <div class="submenu_element">
            <p class="wrapper_menu_text">Activas</p>
        </div>
        </a>
        <div class="submenu_element_on">
            <p class="wrapper_menu_text">Administrar</p>
        </div>
    </div>

<?php
}elseif($privacy == 1){ ?>

    <div class="wrapper_menu">
        <a href="<?php echo base_url()?>main/lists_a">
        <div class="submenu_element">
            <p class="wrapper_menu_text">Activas</p>
        </div>
        </a>
        <a href="<?php echo base_url()?>main/lists_b">
        <div class="submenu_element">
            <p class="wrapper_menu_text">Temp</p>
        </div>
        </a>
        <a href="<?php echo base_url()?>main/lists_c">
        <div class="submenu_element_on">
            <p class="wrapper_menu_text">Administrar</p>
        </div>
        </a>
    </div>
<?php
}

?>
	<div class="title">
		<h1>Administrar listas</h1>
	</div>
	<div class="subtitle">
		<p><?php if (empty($lists)){
            echo "Aún no tienes listas";
        }else{
            echo "Aquí puedes agregar y editar listas";
        } ?>
        </p>
	</div>
    <div id="add_temp_post">
        <?php 

        $i = 0;
        echo form_open('add/add_list');

        $add_user = array(
            'name' => 'add',
            'id' => 'form_text',
            'placeholder' => 'Crea una nueva lista'
            );

        echo form_input($add_user);

        $submit_user = array(
            'name' => 'add_submit_user',
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

        <tr>
            <th>Nombre</th>
            <th>Editar nombre</th>
            <th>Descripción</th>
            <th>Editar descripción</th>
            <th>Eliminar lista</th>
        </tr>
       <?php

       if($lists !== FALSE){
            $i = 0;
            $k = 'a';
            foreach($lists as $row){

                $client_id = $row['client_id'];
                $list_id = $row['id'];

                ?>
            <tr>
            	<td class="text_list"  id="info<?php echo $k ?>" style="width:15%;"><a onMouseOver="this.style.cssText='color: #ff9500'" onMouseOut="this.style.cssText='color: #black'" href="<?php echo site_url('main/lists_a1/'. $client_id . "/" . $list_id) ?>"><?php echo $row['list']?></a></td>
                <td class="form_edit_list" id="form_edit<?php echo $k ?>"><?php

                    echo form_open('add/update_list_name');

                    //echo validation_errors();

                    $text = array(
                        'name' => 'text2',
                        'id' => 'edit_list',
                        'value' => $row['list'],
                        'autofocus'   => 'autofocus',
                        );

                    echo form_input($text);

                    $text2 = array(
                        'name' => 'list_id2',
                        'type' => 'hidden',
                        'value' => $list_id
                        );

                    echo form_input($text2);

                    $submit = array(
                        'name' => 'add_submit2',
                        //'id' => 'form_submit' . $i,
                        'class' => 'edit_submit',
                        'value' => '+',
                        'onClick' => 'hideSubmit(' . $k .')'
                        );  

                    echo form_submit($submit);

                    echo form_close();

                    ?>
                </td>
                <td id="id" style="width:3%;"> <a onclick="hideName('<?php echo $k ?>')"><div><img src="<?php echo base_url()?>/assets/img/edit.png"/></div></a></td>


                <td id="info<?php echo $i ?>" style="width:30%;"><?php echo $row['description']?></td>
                <td class="form_edit_description" id="form_edit<?php echo $i ?>"><?php

                    echo form_open('add/update_list_description');

                    //echo validation_errors();

                    $text = array(
                        'name' => 'text',
                        'id' => 'edit_list',
                        'value' => $row['description'],
                        'autofocus'   => 'autofocus',
                        );

                    echo form_input($text);

                    $text2 = array(
                        'name' => 'list_id',
                        'type' => 'hidden',
                        'value' => $list_id
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
                <td id="id" style="width:3%;"> <a onclick="hideName('<?php echo $i ?>')"><div><img src="<?php echo base_url()?>/assets/img/edit.png"/></div></a></td>

                <td id="delete" style="width:3%;">
                    <a href="<?php echo site_url('add/delete_list/' . $list_id . "/" . $client_id) ?>">
                    <div><p>X</p></div>
                	</a>
                </td>

                <?php
                $i++;
                $k++;
            } 
        }else{
            echo "No news to see here!";
        }
    ?>
    </table>
</div>