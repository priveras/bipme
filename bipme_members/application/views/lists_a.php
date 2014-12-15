<div class="wrapper">
<?php 

$privacy = $this->session->userdata('client_privacy');

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
	<div class="title">
		<h1>Listas Activas</h1>
	</div>
	<div class="subtitle">
		<p><?php if (empty($lists)){
            echo "Aún no tienes listas activas";
        }else{
            echo "Has click en el nombre para ver usuarios de la lista";
        } ?>
        </p>
	</div>
	<table>
       <?php

       if($lists !== FALSE){
            $i = 0;
            foreach($lists as $row){

                $client_id = $row['client_id'];
                $list_id = $row['id'];

                ?>
            <tr>
            	<td id="text" style="width:10%;"><a onMouseOver="this.style.cssText='color: #ff9500'" onMouseOut="this.style.cssText='color: #black'" href="<?php echo site_url('main/lists_a1/' . $list_id) ?>"><?php echo $row['list']?></a></td>
                <td id="info<?php echo $i ?>" style="width:70%;"><?php echo $row['description']?></td>
                <td id="td_info" style="width:11%;"><p>
                    <?php 

                    $j = 0;
                    foreach ($count_lists_users as $row) {

                        if($row['list_id'] == $list_id){
                            $j++;
                        }
                    }

                    if($j == 1){
                        echo $j . " Usuario";
                    }else{
                        echo $j . " Usuarios";
                    }

                    ?>

                </p></td>

                <?php
                $i++;
            } 
        }else{
            echo "No news to see here!";
        }
    ?>
    </table>
</div>