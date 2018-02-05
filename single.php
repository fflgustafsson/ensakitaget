<?php get_header(); ?>
	
	<div class="container single">

		<?php if (have_posts()): while (have_posts()) : the_post(); ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<div class="single__img">
					<?php if ( has_post_thumbnail()) : // Check if Thumbnail exists ?>
						<?php the_post_thumbnail('full'); ?>
					<?php endif; ?>
				</div>

				<div class="single__txt">

					<h1>
						<?php the_title(); ?>
					</h1>
						<!-- /post title -->

					<div class="single__txt__meta">
						<!-- <span class="date"><?php the_time('H:i'); ?></span> — -->
						<span class="cat"><?php the_category( ', '); ?></span>
					</div>

					<?php the_content(); // Dynamic Content ?>
				</div>

			</article>
				<!-- /article -->
		<?php endwhile; ?>

		<?php else: ?>

			<article>
				<h1>
					<?php _e( 'Sorry, nothing to display.', 'html5blank' ); ?>		
				</h1>
			</article>
				<!-- /article -->

		<?php endif; ?>

	</div>

<?php get_footer(); ?>
