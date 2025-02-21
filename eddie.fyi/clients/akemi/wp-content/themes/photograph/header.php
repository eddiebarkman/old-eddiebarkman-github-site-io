<?php
/**
 * Displays the header content
 *
 * @package Theme Freesia
 * @subpackage Photograph
 * @since Photograph 1.0
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<?php
$photograph_settings = photograph_get_theme_options(); ?>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<link rel="profile" href="http://gmpg.org/xfn/11" />
<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
<?php endif;
wp_head(); ?>
	<link href="https://use.fontawesome.com/releases/v5.0.1/css/all.css" rel="stylesheet">
</head>
<body <?php body_class(); ?>>
<div id="page" class="site">
	<!-- Masthead ============================================= -->
	<header id="masthead" class="site-header clearfix">
		<?php if ( get_header_image() ) : ?>
		<a href="<?php echo esc_url(home_url('/'));?>" rel="home"><img src="<?php header_image(); ?>" class="header-image" width="<?php echo esc_attr(get_custom_header()->width);?>" height="<?php echo esc_attr(get_custom_header()->height);?>" alt="<?php echo esc_attr(get_bloginfo('name', 'display'));?>"> </a>
	<?php endif; ?>
		<div class="header-wrap">
			
			<!-- Top Header============================================= -->
			<div class="top-header">

				<!-- Main Header============================================= -->
				<div id="sticky-header" class="clearfix">
					<div class="main-header clearfix">
						<?php do_action('photograph_site_branding'); ?>

							<!-- Main Nav ============================================= -->
							<?php
							if($photograph_settings['photograph_disable_main_menu']==0){ ?>
								<nav id="site-navigation" class="main-navigation clearfix" role="navigation">

								<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
									<span class="line-bar"></span>
							  	</button>
							  	<!-- end .menu-toggle -->
								<?php if (has_nav_menu('primary')) {
									$args = array(
									'theme_location' => 'primary',
									'container'      => '',
									'items_wrap'     => '<ul id="primary-menu" class="menu nav-menu">%3$s</ul>',
									); ?>

									<?php wp_nav_menu($args);//extract the content from apperance-> nav menu
									} else {// extract the content from page menu only
									wp_page_menu(array('menu_class' => 'menu', 'items_wrap'     => '<ul id="primary-menu" class="menu nav-menu">%3$s</ul>'));
									} ?>
								</nav> <!-- end #site-navigation -->
							<?php }
							$photograph_side_menu = $photograph_settings['photograph_side_menu'];
							$search_form = $photograph_settings['photograph_search_custom_header'];
							if( (1 != $photograph_settings['photograph_disable_main_menu']) || (1 != $search_form) ){  ?>
								<div class="right-toggle">
									<?php if($photograph_settings['photograph_disable_main_menu']==0){ ?>
									<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
										<span class="line-bar"></span>
								  	</button>
								  	<!-- end .menu-toggle -->
								  	<?php }
								  	if (1 != $search_form) { ?>
									<div id="search-toggle" class="header-search"></div>
									<?php } 
									if(1 != $photograph_side_menu){ ?>
									<div class="show-menu-toggle">
										<span class="sn-text"><?php _e('Menu Button','photograph'); ?></span>
										<span class="bars"></span>
								  	</div>
								  	<?php } ?>
								</div>
								<!-- end .right-toggle -->
								<?php
							} ?>
					</div> <!-- end .main-header -->
				</div> <!-- end #sticky-header -->
			</div> <!-- end .top-header -->
			<?php if (1 != $search_form) { ?>
				<div id="search-box" class="clearfix">
					<div class="search-x"></div>
						<?php get_search_form();?>
				</div>  <!-- end #search-box -->
			<?php }

			$photograph_side_menu = $photograph_settings['photograph_side_menu'];
			if(1 != $photograph_side_menu){ ?>
				<div class="side-menu-wrap">
					<div class="side-menu">
				  		<div class="hide-menu-toggle">			
							<span class="bars"></span>
					  	</div>

						<?php do_action ('photograph_new_site_branding');

						if (has_nav_menu('side-nav-menu') || (has_nav_menu( 'social-link' ) && $photograph_settings['photograph_side_menu_social_icons'] == 0 ) || is_active_sidebar( 'photograph_side_menu' ) ):
							
							if (has_nav_menu('side-nav-menu')) { 
								$args = array(
									'theme_location' => 'side-nav-menu',
									'container'      => '',
									'items_wrap'     => '<ul class="side-menu-list">%3$s</ul>',
									); ?>
							<nav class="side-nav-wrap">
								<?php wp_nav_menu($args); ?>
							</nav><!-- end .side-nav-wrap -->
							<?php }
							if($photograph_settings['photograph_side_menu_social_icons'] == 0):
								do_action('photograph_social_links');
							endif;

							if( is_active_sidebar( 'photograph_side_menu' )) {
								echo '<div class="side-widget-tray">';
									dynamic_sidebar( 'photograph_side_menu' );
								echo '</div> <!-- end .side-widget-tray -->';
							} 
						endif; ?>
					</div><!-- end .side-menu -->
				</div><!-- end .side-menu-wrap -->
				<?php 
			} ?>
		</div><!-- end .header-wrap -->
		<?php 
		if($photograph_settings['photograph_top_social_icons'] == 0):
			echo '<div class="header-social-block">';
				do_action('photograph_social_links');
			echo '</div>'.'<!-- end .header-social-block -->';
		endif;

		$photograph_enable_slider = $photograph_settings['photograph_enable_slider'];
		if (($photograph_settings['photograph_slider_video_display'] == 'video') && ($photograph_enable_slider=='frontpage'|| $photograph_enable_slider=='enitresite') ){
			if(is_front_page() && ($photograph_enable_slider=='frontpage') ) { ?>
				<!-- Video and image header ============================================= -->
				<?php photograph_video_category_sliders();

			} elseif($photograph_enable_slider=='enitresite'){

				photograph_video_category_sliders();

			}
		} else { ?>
		<!-- Main Slider ============================================= -->
		<?php
			
			if ($photograph_enable_slider=='frontpage'|| $photograph_enable_slider=='enitresite'){
				 if(is_front_page() && ($photograph_enable_slider=='frontpage') ) {

				 	if(is_active_sidebar( 'slider_section' )){

				 		dynamic_sidebar( 'slider_section' );

				 	} else {

				 		if($photograph_settings['photograph_slider_type'] == 'default_slider') {
							photograph_category_sliders();

						} else {

							if(class_exists('Photograph_Plus_Features')):
								do_action('photograph_image_sliders');
							endif;
						}

				 	}
					
				}
				if($photograph_enable_slider=='enitresite'){

					if(is_active_sidebar( 'slider_section' )){

				 		dynamic_sidebar( 'slider_section' );

				 	} else {

				 		if($photograph_settings['photograph_slider_type'] == 'default_slider') {

								photograph_category_sliders();

						} else {

							if(class_exists('Photograph_Plus_Features')):

								do_action('photograph_image_sliders');

							endif;
						}
				 	}

					
				}
			} ?>

		<?php } ?>
		<div class="scroll-down"><span></span><span></span><span></span></div><!-- Scroll Down Button -->
	</header> <!-- end #masthead -->
	<!-- Main Page Start ============================================= -->
	<div class="site-content-contain">
		<div id="content" class="site-content">
		