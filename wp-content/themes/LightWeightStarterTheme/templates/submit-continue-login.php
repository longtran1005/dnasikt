<?php global $pid; ?>
<div class="row page-controls">
	<div class="col-xs-12 clearfix">
		<?php if(is_user_logged_in()): ?>
			<!-- Inloggad -->
			<a class="btn btn-large btn-dn icon-right dnicon-chevron-right pull-right" href="<?php echo ($pid) ? "?pid=$pid&state=working" : "?state=working"; ?>" onclick="dataLayer.push({event: 'customEvent',eventCategory:'Submitpage', eventAction:'Continue'});">Jag vill börja skriva</a>
		<?php else: ?>
			<!-- Inte inloggad -->
			<div class="login-button clearfix"><a class="btn btn-large btn-dn icon-right dnicon-chevron-right pull-right" href="<?php echo serviceplus_login_url( wp_current_url() ); ?>"  onclick="dataLayer.push({event: 'customEvent',eventCategory:'Submitpage', eventAction:'Login'});">Logga in för att börja skriva</a></div>
			<div class="inline-link pull-right clearfix">Om du inte har något konto kan du <a href="<?php echo serviceplus_create_account_url( wp_current_url() ); ?>"  onclick="dataLayer.push({event: 'customEvent',eventCategory:'Submitpage', eventAction:'Create account'});">skapa ett konto</a></div>
		<?php endif; ?>
	</div>
</div>