<?php
use SB\Media;
get_header();
?>

	<div class="container">

		<div class="newsfeed">
			
			<?php get_template_part('loop'); ?>
			
		</div>

		<?php get_sidebar(); ?>

		<?php get_template_part('pagination'); ?>

	</div>

<?php get_footer(); ?>
