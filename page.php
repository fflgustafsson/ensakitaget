<?php
/*
*
* Template Name: About
*
*/
use SB\Media;
get_header();
the_post();
?>
		
	<div class="container">
		<div class="textbox">
			<h1 class="title">
				<?php the_title(); ?>
			</h1>
			<?php the_post_thumbnail('full'); ?>
			<?php the_content(); ?>
		</div>
		<?php get_sidebar(); ?>
	</div>

<?php get_footer(); ?>