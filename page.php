<?php
/*
*
* Template Name: Undersida
*
*/
use SB\Media;
get_header();
the_post();
?>
		
	<div class="container page">

		<div class="page__img">
			<?php the_post_thumbnail('full'); ?>
		</div>

		<div class="page__txt">
			<h1>
				<?php the_title(); ?>
			</h1>
			<?php the_content(); ?>
		</div>

	</div>

<?php get_footer(); ?>