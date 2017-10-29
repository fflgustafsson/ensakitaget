<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
	
	<head>
		<meta charset="<?php bloginfo('charset'); ?>">
		
		<title>
			<?php wp_title(''); ?>
			<?php if(wp_title('', false)) { echo ' :'; } ?> 
			<?php bloginfo('name'); ?>
		</title>

		<link href="//www.google-analytics.com" rel="dns-prefetch">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
		<link href="https://fonts.googleapis.com/css?family=Raleway:300,400,600" rel="stylesheet" type='text/css'>
        
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

		<?php wp_head(); ?>

	</head>

	<body <?php body_class(); ?>>

		<nav class="nav__mobile">
      	<div class="nav__mobile__close">
      		<i class="fa fa-times" aria-hidden="true"></i>
      	</div>
      	<div class="nav__mobile__main">
      		<?php wp_nav_menu(array('menu' => 'nav-menu')); ?>
      	</div>
	      <ul class="nav__mobile__social">
            <li>
              	<a href="https://www.facebook.com/Ensakitagetbymalin/" title="facebook">
              		<i class="fa fa-facebook-official" aria-hidden="true"></i>
              	</a>
            </li>
            <li>
            	<a href="https://www.instagram.com/ensakitaget_by_malin/" title="instagram" target="_blank">
            		<i class="fa fa-instagram" aria-hidden="true"></i>
            	</a>
            </li>
         </ul>
	   </nav>

    	<div class="nav__overlay js-nav-overlay"></div>

		<header class="header" role="banner">

			<div class="header__logo">
				<a href="<?php echo home_url(); ?>">
					<h1>
						<img src="<?php echo get_template_directory_uri(); ?>/img/ensakitaget_logo.png" alt="ensakitaget.se">
					</h1>
				</a>
			</div>

			<nav class="nav__desktop" role="navigation">
				<div class="nav__desktop__toggle" id="nav__toggle">
					<i class="fa fa-bars" aria-hidden="true"></i> Meny
				</div>
            <div class="nav__desktop__social">
              	<ul>
              		<li>
              			<a href="https://www.facebook.com/Ensakitagetbymalin/" title="facebook"><i class="fa fa-facebook-official" aria-hidden="true"></i></a>
              		</li>
              		<li>
              			<a href="https://www.instagram.com/ensakitaget_by_malin/" title="instagram" target="_blank"><i class="fa fa-instagram" aria-hidden="true"></i></a>
              		</li>
              	</ul>
            </div>
            <div class="nav__desktop__main">
              <?php wp_nav_menu(array('menu' => 'nav-menu')); ?>
            </div>
         </nav>
			
				
		</header>
