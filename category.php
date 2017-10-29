<?php
use SB\Media;
get_header();
?>
	
	<div class="container inspirationWrap">

		<div class="inspirationWrap__menu">
			<?php wp_nav_menu(array('menu' => 'inspiration-menu')); ?>
		</div>

		<h1 class="inspirationWrap__categoryTitle">
			<?php _e( 'Kategorin ', 'html5blank' ); single_cat_title(); ?>
		</h1>

		<div class="inspiration">
			<?php get_template_part('loop'); ?>
		</div>

		<?php get_template_part('pagination'); ?>

	</div>

<?php get_footer(); ?>
