<?php if (have_posts()): while (have_posts()) : the_post(); ?>

	<div class="newspost">
		<!-- article -->
		<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<!-- post thumbnail -->
				<?php if ( has_post_thumbnail()) : // Check if thumbnail exists ?>
					<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
						<?php the_post_thumbnail(); (array( 245, 245, true) ); ?>
					</a>
				<?php endif; ?>

				<!-- post details -->
				<div class="date">
					<?php the_time('H:i'); ?> 
				</div>
				<!-- /post details -->

				<!-- /post thumbnail -->

				<!-- post title -->
				<h2>
					<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
						<?php the_title(); ?>
					</a>
				</h2>
				<!-- /post title -->

			</article>
		</a>
		<!-- /article -->
	</div>

<?php endwhile; ?>

<?php else: ?>

	<!-- article -->
	<article>
		<h2>
			<?php _e( 'Sorry, nothing to display.', 'html5blank' ); ?>
		</h2>
	</article>
	<!-- /article -->

<?php endif; ?>
