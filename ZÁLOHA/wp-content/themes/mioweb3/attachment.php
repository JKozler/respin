<?php if (have_posts()) {
	while (have_posts()) :
		the_post(); ?>
	<html>
	<head>
		<title></title>
	</head>
	<body style="background: #000; text-align: center; color: #fff;">

	<div id="container" class="single-attachment">


		<h2 class="entry-title"><?php the_title(); ?></h2>
		<?php if (wp_attachment_is_image()): ?>
			<p class="attachment"><?php
			$attachment_width = apply_filters('twentyten_attachment_size', 900);
			$attachment_height = apply_filters('twentyten_attachment_height', 900);
			echo wp_get_attachment_image($post->ID, [$attachment_width, $attachment_height]); // filterable image width with, essentially, no limit for image height.
			?></p>
		<?php endif; ?>


	</div><!-- #container -->
	</body>
	</html>
	<?php endwhile;

}; // end of the loop. ?>
