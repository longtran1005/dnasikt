
				</section>
				<!-- /.section-content -->
				<div class="col-md-2 visible-md visible-lg banner-content">
					<div id="not-fixed-ad">
						<?php get_template_part( 'templates/ad', 'not-fixed' ); ?>
					</div>
					<div id="fixed-ad" data-spy="affix" data-offset-top="360" data-offset-bottom="200">
						<?php get_template_part( 'templates/ad', 'fixed' ); ?>
					</div>
				</div>
				<!-- /.banner-content -->
			</div>
			<!-- /.row -->

			<div class="mobile-sidebar"></div>

		<?php // if (!function_exists('dynamic_sidebar') || !dynamic_sidebar('[WIDGET_ID]')) : endif; ?>

		</div> <!-- END CONTAINER MAIN -->
		<footer class="footer container-fluid container-footer-border"></footer>
		<footer class="footer container-fluid container-footer">
			<div class="row">
				<div class="col-md-10 col-sm-12">
					<div class="row last-footer-row">
						<div class="col-md-3 col-sm-3 footer-col">
							<?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar('footer-menu')) : ?>
							<?php endif; ?>
						</div>
						<div class="col-md-3 col-sm-3 footer-col">
							<?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar('footer-one')) : ?>
							<?php endif; ?>
						</div>
						<div class="col-md-3 col-sm-3 footer-col">
							<?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar('footer-two')) : ?>
							<?php endif; ?>
						</div>
						<div class="col-md-3 col-sm-3 footer-col">
							<?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar('footer-three')) : ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<div class="col-md-2 col-sm-12 padding-50 footer-text-dot">
					<?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar('footer-four')) : ?>
					<?php endif; ?>
				</div>
				<div class="col-xs-12 padding-50 footer-text-copy">
					<?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar('footer-five')) : ?>
					<?php endif; ?>
					<div class="footer-text-copy-logo">
						<img src="<?php echo get_bloginfo('template_directory');?>/assets/images/dagensnyheter.png"/>
					</div>
				</div>
			</div>
		</footer>
        
		<!-- Google Tag Manager -->
		<script>
		dataLayer=[{product:'DN.Asikt'}];
		dataLayer.push({event:'pageview',url:"<?php echo wp_current_url(false) ?>"});
		</script>
		<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-P42PCB" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		<script>
		(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','GTM-P42PCB');</script>
		<!-- End Google Tag Manager -->
		<script type="text/javascript" src="//m.burt.io/p/produkter-dn-se.js">
		</script>
		<?php wp_footer(); ?>
	</body>
</html>