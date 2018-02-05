<?php if (have_posts()): while (have_posts()) : the_post(); ?>

	<div class="inspiration__article">
		<!-- article -->
		
			<article id="post-<?php the_ID(); ?>">
				
				<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
					<div class="inspiration__article__img">
						<?php the_post_thumbnail('full'); ?>
					</div>
				</a>

				<div class="inspiration__article__meta">
					<span class="date"><?php the_time('H:i'); ?></span> —
					<span class="cat"><?php the_category( ', '); ?></span>
				</div>
				
				<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
					<h2><?php the_title(); ?></h2>
				</a>
			</article>

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
