<?php if (have_posts()): while (have_posts()) : the_post(); ?>

	<div class="inspiration__article">
		<!-- article -->
		<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<div class="inspiration__article__img">
					<?php if ( has_post_thumbnail()) : // Check if thumbnail exists ?>
						<?php the_post_thumbnail(); ?>
					<?php endif; ?>
				</div>

				<div class="inspiration__article__meta">
					<div class="cat"><?php the_category( ', '); ?></div>
					<div class="date"><?php the_date(); ?></div>
				</div>

				<h2><?php the_title(); ?></h2>
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
