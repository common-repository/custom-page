<!DOCTYPE html> 
<html>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php wp_title('&raquo;', true, 'right'); ?></title>
<?php wp_head(); ?>
</head>
<body>
<?php render_custom_page(); ?>
<?php wp_footer(); ?>
</body>
</html>
