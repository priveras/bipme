<div class="wrapper">
	<table>
       <?php

       if($posts !== FALSE){
            $i = 0;
            foreach($posts as $row){

                $client = $row['name'];
                $message = $row['message'];

                ?>
            <tr>
            	<td id="text" style="width:10%;"><?php echo $row['name']?></td>
                <td id="info<?php echo $i ?>" style="width:70%;"><?php echo $row['message']?></td>
            </tr>
            <tr>
                <td id="text" style="width:10%;"><?php echo $row['name']?></td>
                <td id="info<?php echo $i ?>" style="width:70%;"><?php echo $row['message_email']?></td>
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