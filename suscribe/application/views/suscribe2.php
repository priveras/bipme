<?php

$key = md5('bipme');

foreach ($clients as $row) {?>
	<div id="client_header">
    	<div id="image_header" style="background-image:url(<?php echo $row['image']?>);"></div>
    	<div id="name_header">
    		<p><?php echo $row['name']; ?></p>
    	</div>
    </div>

<?php

}?>

<div id="space"></div>

<?php 
foreach ($lists as $row) { 
    $list_id = $row['id'];

    $style = $row['style'];

    $x = 0;

    switch ($style) {
        case 'subscribe_active':
            $symbol = '✓';
            break;

        case 'subscribe_inactive':
            $symbol = '+';
            $x = 1;
            break;

        case 'subscribe_temp':
            $symbol = '-';
            break;
    }


    ?>
    <div id="row2">
    	<div id="list">
    		<h1><?php echo $row['list']; ?></h1>
            <p><?php echo $row['description']?></p>
    	</div>

        <?php 

        if($privacy == 0){
            $message = "¡Ya eres parte de la lista!";
            $method = "add_to_list";
        }else{
            $message = "Ya estás esperando aprobación para ser parte de la lista.";
            $method = "add_to_temp_list";
        }?>

    	<a 
        <?php 
        if($x == 1){
            echo('onClick="alert(\'' . $message .  '\')"');
        }?> 
        href="<?php echo site_url('add/' . $method . '/' . $username . '/' . $user_id . '/' . $client_id . '/' .  $list_id . '/' . $privacy ) ?>">
        <div id="<?php echo $style ?>">
    		<p><?php echo $symbol ?></p>
    	</div>
        </a>
    </div>
<?php }


?>
</div>
</body>
</html>