<!-- sidebar -->

	<aside class="sidebar" role="complementary">

		<form class="search" method="get" action="<?php echo home_url(); ?>" role="search">
			<input class="search" type="search" placeholder="Sök...">
		</form>

		<?php if ( is_active_sidebar( 'sidebar' )  ) : ?>
			<aside id="secondary" class="sidebar_widget" role="complementary">
				<?php dynamic_sidebar( 'sidebar' ); ?>
			</aside>
		<?php endif; ?>
		
	</aside>

<!-- /sidebar -->
