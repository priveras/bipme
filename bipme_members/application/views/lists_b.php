<div class="wrapper">
    <div class="wrapper_menu">
        <a href="<?php echo base_url()?>main/lists_a">
        <div class="submenu_element">
            <p class="wrapper_menu_text">Activas</p>
        </div>
        </a>
        <div class="submenu_element_on">
            <p class="wrapper_menu_text">Temp</p>
        </div>
        <a href="<?php echo base_url()?>main/lists_c">
        <div class="submenu_element">
            <p class="wrapper_menu_text">Administrar</p>
        </div>
        </a>
    </div>
    <div class="title">
        <h1>Usuarios Temporales</h1>
    </div>
    <div class="subtitle">
        <p>Estos son los usuarios esperando aprobación para ser incluidos en listas</p>
    </div>
    <?php
    if($temp_clients_users !== FALSE){

        if(empty($temp_clients_users)){

        }else{ ?>
            <table>
                <tr>
                    <th>Nombre</th>
                    <th>Lista</th>   
                <th>Nombre de usuario</th>
                <th style="color:#e84c3d; text-align:center; padding-left:0px;">X</th>
                <th style="color:#50bca8; text-align:center; padding-left:0px;">✓</th>
            </tr>
        <?}
        $i = 1;
        foreach($temp_clients_users as $row){

            $tcu_id = $row['id'];
            $user_id = $row['user_id'];
            $list_id = $row['list_id'];
            $username = $row['username'];
            ?>
            <tr>
                <td id="id"><?php echo $row['name']?></td>
                <td><?php echo $row['list']?></td>
                <td><?php echo $row['username']?></td>
                <td id="delete"><a href="<?php echo site_url('add/delete_temp_clients_users/' .  $user_id . '/' . $list_id) ?>"><div><p>X</p></div></td>
                <td id="approve"><a href="<?php echo site_url('add/add_temp_clients_users/' .  $list_id . '/' . $username) ?>"><div><p>✓</p></div></a></td>
            </tr>
        <?php 
        $i++;
        } 
    }else{
        echo "No news to see here!";
    }
    ?>
</table>
</div>