<?php
use SB\Media;
get_header();
?>
	
	<div class="container">

		<div class="newsfeed">

			<h1 class="title">

				<?php _e( 'Kategorin ', 'html5blank' ); single_cat_title(); ?>
				
			</h1>
			
			<?php get_template_part('loop'); ?>

			<?php get_template_part('pagination'); ?>

		</div>

		<?php get_sidebar(); ?>

	</div>

<?php get_footer(); ?>
