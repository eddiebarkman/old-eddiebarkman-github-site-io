<?php get_header(); ?>
	<div class="row">
		<div class="col-md-8 content-area">
			<main id="main" class="blog-main post-wrap" role="main">
				<?php 
				if ( have_posts() ) : while ( have_posts() ) : the_post();
					get_template_part( 'content', get_post_format() );
				endwhile; 
				?>
				<?php else : ?>
					<?php get_template_part( 'content', 'none' ); ?>
				<?php endif; ?>
			</main> <!-- /main -->
		</div> <!-- /.blog-main -->
		<?php get_sidebar(); ?>
	</div> <!-- /.row -->
<?php get_footer(); ?>