<?php
/**
 * PANG Blocksy Child functions.
 *
 * @package PANG_Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function pang_blocksy_child_enqueue_styles(): void {
	$parent_theme = wp_get_theme( 'blocksy' );
	$parent_version = $parent_theme->exists() ? $parent_theme->get( 'Version' ) : null;

	wp_enqueue_style(
		'blocksy-parent-style',
		get_template_directory_uri() . '/style.css',
		array(),
		$parent_version
	);

	wp_enqueue_style(
		'pang-blocksy-child-style',
		get_stylesheet_uri(),
		array( 'blocksy-parent-style' ),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'pang_blocksy_child_enqueue_styles', 20 );

function pang_blocksy_child_setup(): void {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
}
add_action( 'after_setup_theme', 'pang_blocksy_child_setup' );
