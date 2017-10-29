<?php
/*
*
* Template Name: Startsida
*
*/
use SB\Media;
get_header();
the_post();
?>

<div class="start">

	<div class="start__welcome">
		<?php the_post_thumbnail('full'); ?>
		<div class="start__welcome__txt">
			<?php the_content(); ?>
		</div>
	</div>
		
	<div class="container">

		<div class="start__inputLink">

			<!-- Ingångslänkar -->
			<?php if( get_field('inputlink-inspiration-txt') ): ?>
			   <a href="<?php the_field('inputlink-inspiration-link'); ?>">
			      <img src="<?php the_field('inputlink-inspiration-icon'); ?>">
			      <h2><?php the_field('inputlink-inspiration-title'); ?></h2>
			      <?php the_field('inputlink-inspiration-txt'); ?>
			   </a>
			<?php endif; ?>

			<?php if( get_field('inputlink-services-txt') ): ?>
			   <a href="<?php the_field('inputlink-services-link'); ?>">
			   	<img src="<?php the_field('inputlink-services-icon'); ?>">
			      <h2><?php the_field('inputlink-services-title'); ?></h2>
			      <?php the_field('inputlink-services-txt'); ?>
			   </a>
			<?php endif; ?>

	<!-- 		<?php if( get_field('inputlink-shop-txt') ): ?>
			   <a href="<?php the_field('inputlink-shop-link'); ?>">
			      <img src="<?php the_field('inputlink-shop-icon'); ?>">
			      <h2><?php the_field('inputlink-shop-title'); ?></h2>
			      <?php the_field('inputlink-shop-txt'); ?>
			   </a>
			<?php endif; ?> -->

		</div>

	</div>

	<!-- Fält med ingång till tester -->
	<?php if( get_field('test-txt') ): ?>
		<div class="start__test">
			<div class="container">
				<img src="<?php the_field('test-icon'); ?>">
			   <h2><?php the_field('test-title'); ?></h2>
			   <?php the_field('test-txt'); ?>
			   <?php if( get_field('test-btn-true') ): ?>
			   	<a class="start__test__btn" href="<?php the_field('test-btn-link'); ?>">
			   		<?php the_field('test-btn'); ?>
			   	</a>
			   <?php endif; ?>
			</div>

		</div>
		
	<?php endif; ?>


	</div>

<?php get_footer(); ?>