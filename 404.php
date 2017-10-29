<?php get_header(); ?>

	<div class="container">
			
		<!-- article -->
		<article id="post-404">

			<h1>
				<?php _e( '404', 'html5blank' ); ?>
			</h1>
				
			<p>
				Ojdå, verkar som det inte finns något här...?
				<a href="<?php echo home_url(); ?>">
					<?php _e( 'Till start?', 'html5blank' ); ?>
				</a>
			</p>

		</article>
			<!-- /article -->

		<?php get_sidebar(); ?>

	</div>

<?php get_footer(); ?>
