<?php
/**
 * Class CPT_GSCR_Underwriters
 *
 * Creates the post type.
 *
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class CPT_GSCR_Underwriters extends RBM_CPT {

	public $post_type = 'underwriter';
	public $label_singular = null;
	public $label_plural = null;
	public $labels = array();
	public $icon = 'store';
	public $post_args = array(
		'hierarchical' => true,
		'supports' => array( 'title', 'editor', 'author', 'thumbnail' ),
		'has_archive' => true,
		'rewrite' => array(
			'slug' => 'underwriter',
			'with_front' => false,
			'feeds' => false,
			'pages' => true
		),
		'menu_position' => 11,
		//'capability_type' => 'underwriter',
	);

	/**
	 * CPT_GSCR_Underwriters constructor.
	 *
	 * @since 1.0.0
	 */
	function __construct() {

		// This allows us to Localize the Labels
		$this->label_singular = __( 'Underwriter', 'gscr-cpt-underwriters' );
		$this->label_plural = __( 'Underwriters', 'gscr-cpt-underwriters' );

		$this->labels = array(
			'menu_name' => __( 'Underwriters', 'gscr-cpt-underwriters' ),
			'all_items' => __( 'All Underwriters', 'gscr-cpt-underwriters' ),
		);

		parent::__construct();
		
	}
	
}