<?php
/*
*
* Template Name: Tjänster
*
*/
use SB\Media;
get_header();
the_post();
?>
		
	<div class="container service">

		<?php the_content(); ?>

		<div class="service__postwrapper">
			<?php
			$i = 1;
			$recent = new WP_Query(); 
			$recent->query('post_type=services'); 
			
			while($recent->have_posts()) : $recent->the_post(); ?>

			<div class="service__post">
				<a href="<?php the_permalink(); ?>">
					<div class="service__post__img">
						<?php the_post_thumbnail('full'); ?>
						<div class="service__post__txt">
							<h2><?php the_title();?></h2>
							<?php if( get_field('service_desc') ): ?>
								<p>
									<?php the_field('service_desc'); ?>
								</p>
							<?php endif ?>
						</div>
					</div>
				</a>
			</div>

			<?php
			$i++;
			endwhile;?>
		</div>

	</div>

<?php get_footer(); ?>