<script>
var base_url = '<?php echo base_url(); ?>';

var page = 1;


$(window).scroll(function () {

    if($(window).scrollTop() + $(window).height() > $(document).height()) {

    }
    if($(window).scrollTop() + $(window).height() == $(document).height()) {

        getProducts();
    }
});

function getProducts()
{

    page++;

    /*
    var data = {
        page_num: page
    };
    */
    var data = 'page_num='+page;
    //alert(data);
    
    var the_url = base_url + "json/my_products";

    //alert(the_url);

    //alert(actual_count);
    //if((page-1)* 12 > actual_count){
    //    $('#no-more').show();
    //}else{
        $.ajax({
            type: "POST",
            url: the_url,
            data:data,
            async: false,
            success: function(res) {
            //alert(res);
            if (res.length > 0)
                {
                var json = JSON.parse(res);
                var products = json.products;
                
                var i = (page -1 )*12;
                var str_HTML = '';
                
                //str_HTML += '<table>';
                
                for (var key in products) {
                      var obj = products[key];
                      var the_row = obj.row;
                      
                      //alert(the_row.id);
                      
                      var str_HTML2 = '';
                      
                      str_HTML += '<tr>';
                      str_HTML += '<td>' + the_row.message + '</td>';
                      str_HTML += '<td>' + the_row.name + '</td>';
                      str_HTML += '<td id="date">' + the_row.date + '</td>';
                      str_HTML += '<td id="approve"><div><p>✓</p></div></td>';
                      str_HTML += '</tr>';

                      
                      str_HTML += str_HTML2;
                      
                      //alert(str_HTML);
                      
                    i++;
                }
                
//                str_HTML += '</table>';
                
                //alert(str_HTML);
                
                $("table").append(str_HTML);
                //console.log(res);
                }
            

            }
        });
    //}
}

</script>
<div class="wrapper">
	<div class="wrapper_menu">
		<div class="submenu_element_1_on">
			<p class="wrapper_menu_text">Enviados</p>
		</div>
		<!--<div class="submenu_element">
			<p class="wrapper_menu_text">Hold</p>
		</div>-->
	</div>	
	<div class="title">
		<h1>Notificaciones Enviadas</h1>
	</div>
	<div class="subtitle">
		<p><?php

        if(empty($notifications)){
            echo "Aún no se a mandado ningún mensaje";
        }else{
            echo "Historial de mensajes enviados";
        }
        ?>
    </p>
	</div>
	<table>
       <?php

       if($notifications !== FALSE){
            $i = 0;
            foreach($notifications as $row){?>
            <tr>
                <td><?php echo $row['message']?></td>
                <td><?php echo $row['name']?></td>
                <td id="date"><?php echo $row['date']?></td>
                <td id="approve"><div><p>✓</p></div></td>

                <?php
                $i++;
            } 
        }else{
            echo "No news to see here!";
        }
    ?>
    </table>
</div>