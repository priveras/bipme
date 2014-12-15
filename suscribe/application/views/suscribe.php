

<?php

foreach ($clients as $row) { 

	$client_id = $row['id'];
    $client_category = $row['category'];
    $privacy = $row['privacy'];

    if($privacy == 2){

    }else{

        ?>
    <a href="<?php echo site_url('main/suscribe2/' .  $client_id . '/' . $username . '/' . $user_id . '/' . $privacy) ?>">
    <div id="row">
    	<div id="image" style="background-image:url(<?php echo $row['image']?>);"></div>
    	<div id="name">
    		<p><?php echo $row['name'];?></p>
    	</div>
        <div id="go">
    		<p>→</p>
    	</div>
    </div></a>
<?php 
}
}

?>
</div>
<a href="http://bipme.co/bipme_members/"><div id="members"></div></a>
</body>
</html>