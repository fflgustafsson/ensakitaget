<?php
use SB\Media;
get_header();
?>

	<div class="container inspirationWrap">

		<div class="inspiration">
			<?php get_template_part('loop'); ?>
		</div>

		<?php get_template_part('pagination'); ?>

	</div>

<?php get_footer(); ?>
