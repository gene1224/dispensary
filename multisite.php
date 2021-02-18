<?php

function redirect_non_admin_user(){
    if(current_user_can('administrator')){
		
	}elseif(current_user_can('qrxds_new_basic')){
		wp_redirect( site_url() . "/frontend-manager" );  exit;
	}elseif(current_user_can('qrxds_pro')){
		wp_redirect( site_url() . "/frontend-manager" );  exit;
	}elseif(current_user_can('qrxds_premium')){
		wp_redirect( site_url() . "/frontend-manager" );  exit;
	}else{
		wp_redirect( site_url() );  exit;
	}
}

add_action( 'admin_init', 'redirect_non_admin_user' );

add_action( 'wp_head', 'multisite_style' );
add_action( 'wp_footer', 'multisite_js', 100);
function multisite_style(){
?>
    <style>
        #yith_wcfm-header{
            background: #2f73ba;
        }
        #yith_wcfm-header .yith_wcfm-header-content{
            padding: 25px 0px;
        }
        #yith_wcfm-header .yith_wcfm-header-content .yith_wcfm-site-name a{
            color: #ffffff;
            font-weight: 700;
        }
        #yith_wcfm-header .yith_wcfm-widget-area{
            color: #ffffff;
            font-weight: 700;
        }
        #yith_wcfm-header .yith_wcfm-widget-area .dispensaryDateTime{
            color: #ffffff;
            font-size: 18px;
            text-align: right;
        }
        #yith_wcfm-main-content .yith-wcfm-navigation{
            background: #06ac76;
            width: 260px;
        }
        #yith-wcfm-navigation-menu li a{
            padding: 20px 15px;
        }
        #yith-wcfm-navigation-menu > li{
            border: 1px solid #06ac76;
        }
        #yith-wcfm-navigation-menu > li a{
            color: #ffffff;
        }
        #yith-wcfm-navigation-menu li:hover > a{
            background: rgba(4,138,162, 0.5);
            font-weight: bold;
        }
        #yith-wcfm-navigation-menu li.is-active > a{
            background: rgba(4,138,162, 0.5);
        }
        #yith_wcfm-main-content .yith-wcfm-content{
            background: #ffffff;
        }
        #yith_wcfm-footer .yith_wcfm-widget-area{
            padding: 0px;
        }
        #yith_wcfm-main-content h1{
            text-transform: capitalize;
        }
        #yith_wcfm-main-content h1 .website_link{
            text-transform: lowercase;
        }
        #yith-wcfm-dashboard > ul{
            width: 100%;
            box-shadow: 2px 2px 5px grey;
        }
        #yith-wcfm-dashboard > ul li{
            height: 150px;
            justify-content: center;
            text-align: center;
        }
    </style>
<?php
}

function multisite_js(){
    $blog_id = get_current_blog_id();
    $blog_list = get_blog_list( 0, 'all' );
    $array = json_encode($blog_list);
    $website_link = "";
    foreach($blog_list as $blog){
        if($blog['blog_id'] == $blog_id){
            $website_link = $blog['domain'];
        }
    }
?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script>
        jQuery(document).ready(function($){
            var domain = '<?php echo $website_link; ?>';
            var interval = setInterval(function() {
                var momentNow = moment();
                $('#yith_wcfm-header .yith_wcfm-widget-area').html('<div class="dispensaryDateTime">' + momentNow.format('MMMM DD, YYYY') + ' - ' + momentNow.format('hh:mm:ss A') + '</div>');
            }, 100);
            $("#yith-wcfm-dashboard h1").html("<span class='website_link'><?php echo $website_link; ?></span> Statistics");
        });
    </script>
<?php
}