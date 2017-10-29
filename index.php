<?php
use SB\Media;
get_header();
?>

	<div class="container inspirationWrap">

		<div class="inspirationWrap__menu">
			<?php wp_nav_menu(array('menu' => 'inspiration-menu')); ?>
		</div>

		<div class="inspiration">
			<?php get_template_part('loop'); ?>
		</div>

		<?php get_template_part('pagination'); ?>

	</div>

<?php get_footer(); ?>
