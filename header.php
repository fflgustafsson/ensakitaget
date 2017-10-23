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

		<!-- wrapper -->
		<div class="wrapper">

			<!-- header -->
			<header class="header clear" role="banner">

				<div class="logo">
					<a href="<?php echo home_url(); ?>">
						<h1>
							<img src="<?php echo get_template_directory_uri(); ?>/images/ensakitaget-logo.svg" alt="ensakitaget.se">
						</h1>
					</a>
				</div>
				
				<div id="mobile-menu" class="mobile-menu"></div>

				<!-- nav -->
				<nav id="navigation" class="nav" role="navigation">

					<?php
			             wp_nav_menu(array(
			              'theme_location' => 'main_menu',
			              'menu_class'     => 'main-menu',
			              'menu_id'        => 'main-menu',
			              'container'      => false,
			              'fallback_cb'    => false,
			            ));
			        ?>
					
				</nav>
				<!-- /nav -->
				
			</header>
			<!-- /header -->
