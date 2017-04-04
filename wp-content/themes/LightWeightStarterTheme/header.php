<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
	<head>
		<meta charset="<?php bloginfo('charset'); ?>">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?php bloginfo('name'); ?></title>
		<script type="text/javascript">
			window.burtApi = window.burtApi || [];
			window.burtApi.push(function() {
				window.burtApi.startTracking(function(api) {
					api.startUnitTracking();
					api.setTrackingKey('PROQ78NN5NBP', 'produkter.dn.se');
					api.setDomain('dn.se');
					api.addCloudKey('INS8GFMRVMI1');
					api.setCategory('dnasikt.se', 'debatt');
				});
			});
			window.byburt_onServicePlusIdSync = function(id) {
			    window.burtApi.push(function() {
			        id && window.burtApi.connect('burt.beacon', 'serviceplus-id', id);
			        burtApi.annotate('burt.content', 'loggedinbeacon', true);
			    });
			};
			<?php if(is_user_logged_in()) : $serviceplus_id = user_meta( get_current_user_id(), 'serviceplus_id' , null) ?>
				<?php if($serviceplus_id): ?>
					window.byburt_onServicePlusIdSync('<?php echo $serviceplus_id ?>');
				<?php endif; ?>
			<?php endif; ?>
		</script>

		<script type="text/javascript" src="http://fusion.dn.se/script.js?ads=dn"></script>
		<script type="text/javascript" src="http://www.dn.se/oas/fusion_utils.js?v=11"></script>
		<script>
		    window.Fusion.adServer = "fusion.dn.se";

		    window.Fusion.usepostscribe=true;
		    window.Fusion.usecheckads=false;
		    window.Fusion.parameters["byburt_segments"] = (!!window.byburt_segments.length) ? window.byburt_segments : 'none';
		    window.Fusion.parameters["cookiesDomain"] = "fusion.dn.se";

		    if( window.innerWidth < 767 ) {
	    		window.Fusion.mediaZone = "dn.mobil_dn_se.debatt.asikt";
	        	window.Fusion.layout = "m_asikt";
	    	}
	    	else {
	    		window.Fusion.mediaZone = "dn.dn_se.debatt.asikt";
	        	window.Fusion.layout = "asikt";
	    	}

    		/**
		    if( window.innerWidth < 767 ) {
		    	<?php if( is_front_page() ) : ?>
		    		window.Fusion.mediaZone = "dnse.smallscreens.asikt.sektion.undersektion.etta";
		        	window.Fusion.layout = "Small_asikt_etta";
		        <?php else : ?>
		    		window.Fusion.mediaZone = "dnse.smallscreens.asikt.sektion.undersektion.artikel";
		        	window.Fusion.layout = "Small_asikt_artikel";
		        <?php endif; ?>
		    } else {
		    	<?php if( is_front_page() ) : ?>
		    		window.Fusion.mediaZone = "dnse.largescreens.asikt.sektion.undersektion.etta";
		        	window.Fusion.layout = "Large_asikt_etta";
		        <?php else : ?>
		    		window.Fusion.mediaZone = "dnse.largescreens.asikt.sektion.undersektion.artikel";
		        	window.Fusion.layout = "Large_asikt_artikel";
		        <?php endif; ?>
		    }*/
		    window.Fusion.loadAds();
		</script>

		<?php wp_head(); ?>

		<!-- FAVICONS -->
		<link rel="apple-touch-icon" sizes="57x57" href="<?php echo get_stylesheet_directory_uri(); ?>/favicons/apple-touch-icon-57x57.png">
		<link rel="apple-touch-icon" sizes="60x60" href="<?php echo get_stylesheet_directory_uri(); ?>/favicons/apple-touch-icon-60x60.png">
		<link rel="apple-touch-icon" sizes="72x72" href="<?php echo get_stylesheet_directory_uri(); ?>/favicons/apple-touch-icon-72x72.png">
		<link rel="apple-touch-icon" sizes="76x76" href="<?php echo get_stylesheet_directory_uri(); ?>/favicons/apple-touch-icon-76x76.png">
		<link rel="apple-touch-icon" sizes="114x114" href="<?php echo get_stylesheet_directory_uri(); ?>/favicons/apple-touch-icon-114x114.png">
		<link rel="apple-touch-icon" sizes="120x120" href="<?php echo get_stylesheet_directory_uri(); ?>/favicons/apple-touch-icon-120x120.png">
		<link rel="apple-touch-icon" sizes="144x144" href="<?php echo get_stylesheet_directory_uri(); ?>/favicons/apple-touch-icon-144x144.png">
		<link rel="apple-touch-icon" sizes="152x152" href="<?php echo get_stylesheet_directory_uri(); ?>/favicons/apple-touch-icon-152x152.png">
		<link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_stylesheet_directory_uri(); ?>/favicons/apple-touch-icon-180x180.png">
		<link rel="icon" type="image/png" href="<?php echo get_stylesheet_directory_uri(); ?>/favicons/favicon-32x32.png" sizes="32x32">
		<link rel="icon" type="image/png" href="<?php echo get_stylesheet_directory_uri(); ?>/favicons/android-chrome-192x192.png" sizes="192x192">
		<link rel="icon" type="image/png" href="<?php echo get_stylesheet_directory_uri(); ?>/favicons/favicon-96x96.png" sizes="96x96">
		<link rel="icon" type="image/png" href="<?php echo get_stylesheet_directory_uri(); ?>/favicons/favicon-16x16.png" sizes="16x16">
		<link rel="manifest" href="<?php echo get_stylesheet_directory_uri(); ?>/favicons/manifest.json">
		<link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/favicons/favicon.ico">
		<meta name="msapplication-TileColor" content="#ffffff">
		<meta name="msapplication-TileImage" content="<?php echo get_stylesheet_directory_uri(); ?>/favicons/mstile-144x144.png">
		<meta name="msapplication-config" content="<?php echo get_stylesheet_directory_uri(); ?>/favicons/browserconfig.xml">
		<meta name="theme-color" content="#ffffff">
		<!-- // FAVICONS -->

	</head>
	<body <?php body_class(); ?>>

<nav class="navbar navbar-fixed-top">
	<div class="container-fluid container-nav">
		<div class="navbar-alerts">
			<div id="alerts"></div>
		</div>
		<div class="navbar-header">
			<a class="navbar-brand" href="<?php echo home_url( '/' ); ?>"></a>
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
				<i class="<?php echo (is_user_logged_in()) ? 'dnicon-dn_user-red' : 'dnicon-dn_user' ; ?> header-icon"></i>
				<!--<img src="<?php echo get_bloginfo('template_directory');?>/assets/images/<?php echo (is_user_logged_in()) ? 'icon_user-red.svg' : 'icon_user.svg' ; ?>" class="open" /> -->
				<i class="dnicon-close"></i>
			</button>
			<button type="button" class="navbar-toggle">
				<a href="<?php echo if_empty(get_option("mob_nav_pencil"),"#"); ?>"><i class="fa fa-pencil-square-o header-icon"></i></a>
			</button>
			<button type="button" class="navbar-toggle">
				<a href="<?php echo if_empty(get_option("mob_nav_question"),"#"); ?>"><i class="dnicon-question header-icon"></i></a>
			</button>
		</div>
		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
			<div class="navbar-right">
				<form class="navbar-form searchform" role="search" method="get" id="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<div class="form-group">
						<input type="search" name="s" id="search" class="form-control" value="<?php echo get_search_query(); ?>" placeholder="Sök"/>
					    <button class="js--toggle-search-input btn btn-naked"><span>Sök</span><i class="dnicon-search header-icon"></i></button>
					</div>
	  			</form>
	  			<ul class="nav navbar-nav">
	  			<?php if( is_user_logged_in() ) : ?>
	  			<?php $notifications = get_notifications(); ?>
					<li class="dropdown dropdown-notifications">
						<a href="#" class="dropdown-toggle js--toggle_notification_bar" data-toggle="dropdown" role="button" aria-expanded="false" onclick="dataLayer.push({event: 'customEvent',eventCategory:'Navbar',eventAction:'Notifications'});">
							<span class="text">Notifieringar </span><span class="badge"><?php echo count( get_notifications( array( 'read' => 0 ) ) ); ?></span>
						</a>
						<ul class="dropdown-menu" role="menu">
							<?php if( count( $notifications ) == 0) : ?>
								<li><a href="#">Du har inga notifieringar</a></li>
							<?php endif; ?>
							<?php foreach( $notifications as $notification ) : ?>
								<li class="dropdown-header <?php echo ($notification->is_read == 0) ? 'new' : '' ; ?>"><?php echo $notification->subject; ?></li>
								<li class="<?php echo ($notification->is_read == 0) ? 'new' : '' ; ?>"><a href="<?php echo $notification->permalink; ?>" onclick="dataLayer.push({event: 'customEvent',eventCategory:'Navbar',eventAction:'Visit notification link', eventLabel:'<?php echo ($notification->is_read == 0) ? 'new' : 'old' ; ?>'});"><?php echo $notification->body; ?></a></li>
							<?php endforeach; ?>
						</ul>
					</li>
				<?php endif; ?>

				<?php if(is_user_logged_in()): ?>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
							<?php echo wp_get_current_user()->user_firstname; ?> <i class="dnicon-dn_user-red header-icon"></i>
							<!-- <img src="<?php echo get_bloginfo('template_directory');?>/assets/images/icon_user-red.svg" class="icon-user" /></i> -->
						</a>
						<ul class="dropdown-menu" role="menu">
							<li><a href="<?php echo get_custom_page( array( 'template' => 'page-profile.php' ) ); ?>">Profil</a></li>
							<li><a href="https://kund.dn.se/mitt-konto/" target="_blank" onclick="dataLayer.push({event: 'customEvent',eventCategory:'Navbar',eventAction:'Customerservice'});">Kundservice</a></li>
							<li class="divider"></li>
							<li class="dropdown-header visible-xs"><?php echo wp_get_current_user()->display_name; ?></li>
							<li><a href="<?php echo serviceplus_logout_url( wp_current_url() ); ?>" onclick="dataLayer.push({event: 'customEvent',eventCategory:'Navbar',eventAction:'Logout'});">Logga ut</a></li>
						</ul>
					</li>
				<?php else: ?>
					<li><a href="<?php echo serviceplus_login_url( wp_current_url() ); ?>" onclick="dataLayer.push({event: 'customEvent',eventCategory:'Navbar',eventAction:'Login'});">Logga in <i class="dnicon-dn_user header-icon"></i><!-- <img src="<?php echo theme_root(); ?>/assets/images/icon_user.svg" class="icon-user" />--></a></li>
				<?php endif; ?>
				</ul>
			</div>
		</div>
	</div>
</nav>

<div class="container-fluid container-main">
	<div class="row">
		<section class="col-md-10 section-content">