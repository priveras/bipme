<html>
<head>
	<title>Bipme | Admin</title>
    <link rel="shortcut icon" href="<?php echo base_url()?>assets/img/favicon.ico" type="image/icon">
    <link rel="icon" href="<?php echo base_url()?>assets/img/favicon.ico" type="image/icon">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>/assets/styles/style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    <script src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
    <script type="text/javascript">
    var tId;
    var tId2;

    $("#messages").hide().slideDown();
        clearTimeout(tId);
        tId=setTimeout(function(){
            $("#messages").hide();        
        }, 3000);

    $("#messages2").hide().slideDown();
        clearTimeout(tId2);
        tId2=setTimeout(function(){  
            $("#messages2").hide();        
        }, 3000);

    function hideSubmit(i){
        document.getElementById('form_submit' +  i).style.cssText = 
        'background: url(http://bipme.co/bipme_members/assets/img/loading.gif) no-repeat;background-size: 50%;background-position: center;'
    }

    function hideName(i){
        document.getElementById('info' + i).style.cssText = 
        'display:none';
        document.getElementById('form_edit' + i).style.cssText = 
        'display:block';
    }
    </script>
</head>
<body>
    <header>
    	<div id="logo">
    		<div id="logo_img">
    			<img src="<?php echo base_url()?>assets/img/bipme_logo.png">
    		</div>
    		<div id="logo_text">
    			<p>Bipme</p>
    		</div>
    	</div>
        <?php 

        if($this->session->userdata('message')){?>

        <div id="messages">
            <?php 
            echo "<p>" . $this->session->userdata('message') . "</p>";
            $this->session->unset_userdata('message');
            $this->session->unset_userdata('user_username');
            ?>
        </div>

        <?php    
        }elseif($this->session->userdata('message2')){?>
        <div id="messages2">
            <?php 
            echo "<p>" . $this->session->userdata('message2') . "</p>";
            $this->session->unset_userdata('message2');
            $this->session->unset_userdata('user_username');
            ?>
        </div>

        <?php }
        ?>
        <div id="logout">
            <a href="<?php echo base_url()?>main/logout"><div id="logout_img">
                <img src="<?php echo base_url()?>assets/img/logout2.png">
            </div></a>
            <div id="logout_text">
                <p>Exit</p>
            </div>
        </div>
    </header>
    <?php 

    $method = $this->router->method;

    $class1 =  "menu_element";
    $class2 =  "menu_element";
    $class3 =  "menu_element";
    $class4 =  "menu_element";


    switch ($method) {

        case "account":
        $class1 = "menu_element_on";
        break;
        
        case "members":
        $class2 = "menu_element_on";
        break;

        case "push_b":
        $class2 = "menu_element_on";
        break;

        case "lists_a":
        $class3 = "menu_element_on";
        break;

        case "lists_a1":
        $class3 = "menu_element_on";
        break;

        case "lists_b":
        $class3 = "menu_element_on";
        break;

        case "lists_c":
        $class3 = "menu_element_on";
        break;

        case "notifications_a":
        $class4 = "menu_element_on";
        break;

    }
    ?>
    <div id="main_menu">
        <div id="menu">
            <a href="<?php echo base_url()?>main/account">
            <div class="<?php echo $class1 ?>">
                <div class="menu_admin_img"><img src="<?php echo $this->session->userdata('client_image')?>"></div>
                <div class="menu_admin_text"><p>Admin</p></div>
            </div>
            </a>
            <a href="<?php echo base_url()?>main/members">
            <div class="<?php echo $class2 ?>">
                <p>Notificaciones</p>
            </div>
            </a>
            <a href="<?php echo base_url()?>main/lists_a">
            <div class="<?php echo $class3 ?>">
                <p>Listas</p>
            </div>
            </a>
            <a href="<?php echo base_url()?>main/notifications_a">
            <div class="<?php echo $class4 ?>">
                <p>Historial</p>
            </div>
            </a>
            <!--<a href="<?php echo base_url()?>main/users_active">
            <div class="<?php echo $class5 ?>">
                <p>Datos</p>
            </div>
            </a>
            <div class="<?php echo $class6 ?>">
                <p>Ayuda</p>
            </div>-->
        </div>
    </div>