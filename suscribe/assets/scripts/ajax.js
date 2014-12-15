var base_url = '<?php echo base_url(); ?>';

var page = 1;


$(window).scroll(function () {
    $('#more').hide();
    $('#no-more').hide();

    if($(window).scrollTop() + $(window).height() > $(document).height()) {
        $('#more').show();
    }
    if($(window).scrollTop() + $(window).height() == $(document).height()) {

        getProducts();
    }
});

function getProducts()
{
    $('#more').hide();
    $('#no-more').hide();

    page++;

    /*
    var data = {
        page_num: page
    };
    */
    var data = 'page_num='+page;
    //alert(data);
    
    var the_url = base_url + "json/my_products"
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
                
                str_HTML += '<div id="wrapper">';
                
                for (var key in products) {
                      var obj = products[key];
                      var the_row = obj.row;
                      var the_recommendations = obj.recommendations;
                      
                      //alert(the_row.image);
                      
                      var str_HTML2 = '';
                      
                      str_HTML += '<div class="posts">';

                      str_HTML2 += '<a href="<?php echo site_url("add/delete/' + the_row.id + '") ?>"><div class="delete"><img src="<?php echo base_url(); ?>assets/img/trash.png" /></div></a>';

                      str_HTML2 += '<div class="text"><a target="_blank" href="' + the_row.url + '"><h1>' + the_row.title + '</h1></a></div>';

                      str_HTML2 += '<a target="_blank" href="' + the_row.url + '"><div class="images" style="background-image:url(' + the_row.image + ')"></div></a>';

                      str_HTML2 += '<div class="text"><p>' + the_row.description + '</p></div>';

                      str_HTML2 += '<div class="posts_footer">';

                      str_HTML2 += '<div class="posts_footer_img"><img src="' + the_row.favicon + '"></div>';

                      str_HTML2 += '<div class="posts_footer_site"><p>' + the_row.site_name + '</p></div>';

                      str_HTML2 += '<div class="posts_footer_created"><p>' + the_row.created_at.substr(0,8) + '</p></div>';

                      str_HTML2 += '</div>';

                      str_HTML2 += '</div>';
                      
                      str_HTML += str_HTML2;
                      
                      //alert(str_HTML);
                      
                    i++;
                }
                
                str_HTML += '</div>';
                str_HTML += '</div>';
                
                //alert(str_HTML);
                
                $("#result").append(str_HTML);
                //console.log(res);
                }
            

            }
        });
    //}
}