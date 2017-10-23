<?php get_header(); ?>
	
	<div class="container">

		<?php if (have_posts()): while (have_posts()) : the_post(); ?>

			<div class="textbox">
					<!-- article -->
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

					<!-- post title -->
					<h1 class="title">
						<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
					</h1>
					<!-- /post title -->

					<div class="postdetails">
						<!-- post details -->
						<span class="category">
							<i class="fa fa-folder-open-o"></i> 
							<?php the_category(','); // Separated by commas ?>
						</span>
						
						<span class="date">
							<?php the_time('H:i d/m'); ?> 
						</span>
						<!-- /post details -->
					</div>

					<?php if ( has_post_thumbnail()) : // Check if Thumbnail exists ?>
						<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
							<?php the_post_thumbnail('full'); ?>
						</a>
					<?php endif; ?>

					<?php the_content(); // Dynamic Content ?>

				</article>
				<!-- /article -->
			</div>

			<?php endwhile; ?>

			<?php else: ?>

				<!-- article -->
				<article>
					<h1>
						<?php _e( 'Sorry, nothing to display.', 'html5blank' ); ?>
					</h1>
				</article>
				<!-- /article -->

			<?php endif; ?>

		<?php get_sidebar(); ?>

	</div>

<?php get_footer(); ?>
