<?php 

if($lists[0]['client_id'] == $this->session->userdata('client_id')){

?>



<div class="wrapper">
	
<?php $privacy = $this->session->userdata('client_privacy');

if($privacy == 0 || $privacy == 2){?>

    <div class="wrapper_menu">
        <div class="submenu_element_1_on">
            <p class="wrapper_menu_text">Activas</p>
        </div>
        <a href="<?php echo base_url()?>main/lists_c">
        <div class="submenu_element">
            <p class="wrapper_menu_text">Administrar</p>
        </div>
        </a>
    </div>

<?php
}elseif($privacy == 1){ ?>

    <div class="wrapper_menu">
        <div class="submenu_element_1_on">
            <p class="wrapper_menu_text">Activas</p>
        </div>
        <a href="<?php echo base_url()?>main/lists_b">
        <div class="submenu_element">
            <p class="wrapper_menu_text">Temp</p>
        </div>
        </a>
        <a href="<?php echo base_url()?>main/lists_c">
        <div class="submenu_element">
            <p class="wrapper_menu_text">Administrar</p>
        </div>
        </a>
    </div>
<?php
}

?>
    <div class="list">
        <a href="<?php echo base_url()?>main/lists_a"><div id="close"><p>x</p></div></a>
        <div>
        <?php


            echo "<p>" . $lists[0]['list'] . "</p>";

            $list_id = $lists[0]['id'];

            $client_id = $this->session->userdata('client_id');


        ?>
        </div>
    </div>
	<div class="title" style="margin-top:120px;">
		<h1>Administrar lista</h1>
	</div>
	<div class="subtitle">
		<p>Aquí puedes agregar usuarios a esta lista</p>
	</div>
    <div id="add_temp_post">
        <?php 

        $i = 0;

        echo form_open('add/insert_clients_users');

        $add_user = array(
            'name' => 'add',
            'id' => 'form_text',
            'placeholder' => 'Inserta un usuario o email a la lista'
            );

        echo form_input($add_user);

        $text2 = array(
            'name' => 'list_id',
            'type' => 'hidden',
            'value' => $list_id
            );

        echo form_input($text2);

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
        <div id="submit_bulk"></div>
    </div>
	<table>
       <?php

       if($lists_users !== FALSE){
            $i = 0;
            foreach($lists_users as $row){

                $id = $row['cuid'];

                ?>
            <tr>
                <?php 

                if($row['username'] == 'dummie'){
                    echo "<td id='text'>" . $row['email'] . "</td>";
                    echo "<td id='text'>No name</td>";
                }else{
                    echo "<td id='text'>" . $row['username'] . "</td>";
                    echo "<td id='text'>" . $row['name'] . "</td>";
                }

                ?>
                <td id="delete">
                    <a href="<?php echo site_url('add/delete_user_from_list/' . $id . '/' . $list_id) ?>">
                    <div><p>X</p></div>
                    </a>
                </td>

                <?php
                $i++;
            } 
        }else{
            echo "No news to see here!";
        }
    ?>
    </table>
</div>

<?php

}else{
    echo "<div class=wrapper'><div class='wrapper_menu'><div class='title'><h1>You do not have access</h1></div></div></div>";
}