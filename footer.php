
			<footer class="footer" role="contentinfo">
				
				<div class="copy">
					<p class="copyright">
					&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>.
				</p>
				</div>
				<div class="socialmedia">
					<?php if ( is_active_sidebar( 'socialfooter' )  ) : ?>
						<?php dynamic_sidebar( 'socialfooter' ); ?>
					<?php endif; ?>
				</div>

			</footer>

		</div>

		<?php wp_footer(); ?>

	</body>
</html>
