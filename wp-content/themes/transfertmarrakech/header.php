<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    
    <!-- Title - Yoast SEO will override this if "Force rewrite titles" is enabled -->
    <title><?php echo esc_html( wp_get_document_title() ); ?></title>
    
    <!-- Favicon -->
    <?php if ( has_site_icon() ) : ?>
        <link rel="icon" href="<?php echo esc_url( get_site_icon_url() ); ?>" />
        <link rel="apple-touch-icon" href="<?php echo esc_url( get_site_icon_url( 180 ) ); ?>" />
    <?php else : ?>
        <link rel="icon" href="<?php echo esc_url( get_template_directory_uri() . '/assets/images/favicon.ico' ); ?>" />
        <link rel="apple-touch-icon" href="<?php echo esc_url( get_template_directory_uri() . '/assets/images/apple-touch-icon.png' ); ?>" />
    <?php endif; ?>
    
    <!-- RSS Feed -->
    <link rel="alternate" type="application/rss+xml" title="<?php bloginfo( 'name' ); ?> RSS Feed" href="<?php bloginfo( 'rss2_url' ); ?>" />
    
    <!-- DNS Prefetch for Performance -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com" />
    <link rel="dns-prefetch" href="//www.google-analytics.com" />
    <link rel="dns-prefetch" href="//fonts.gstatic.com" />
    
    <!-- Preconnect for Critical Resources -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php
/**
 * The header for our theme
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package transfertmarrakech
 */

// Rend le header via la classe Header
\TM\Core\Header::get_instance()->render();
?>
    <div id="page">
        <div id="container">
