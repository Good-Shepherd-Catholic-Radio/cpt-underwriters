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
		'has_archive' => false,
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
		
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		
		add_filter( 'manage_' . $this->post_type . '_posts_columns', array( $this, 'admin_column_add' ) );
		
		add_action( 'manage_' . $this->post_type . '_posts_custom_column', array( $this, 'admin_column_display' ), 10, 2 );
		
		add_filter( 'manage_edit-' . $this->post_type . '_sortable_columns', array( $this, 'admin_columns_sortable' ) );
		
		add_action( 'pre_get_posts', array( $this, 'admin_columns_sorting' ) );
		
		add_filter( 'get_sample_permalink_html', array( $this, 'alter_permalink_html' ), 10, 5 );
		
		add_filter( 'the_permalink', array( $this, 'the_permalink' ) );
		
		add_filter( 'post_type_link', array( $this, 'get_permalink' ), 10, 4 );
		
		add_filter( 'template_include', array( $this, 'redirect_to_website' ) );
		
		add_action( 'init', array( $this, 'register_taxonomy' ) );
		
	}
	
	/**
	 * Add Meta Box
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function add_meta_boxes() {
		
		add_meta_box(
			'underwriter-website-url',
			sprintf( _x( '%s Meta', 'Metabox Title', 'gscr-cpt-underwriters' ), $this->label_singular ),
			array( $this, 'metabox_content' ),
			$this->post_type,
			'side'
		);
		
	}
	
	/**
	 * Add Meta Field
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function metabox_content() {
		
		rbm_do_field_text(
			'underwriter_website_url',
			_x( 'Underwriter Website URL', 'Underwriter Website URL Label', 'gscr-cpt-underwriters' ),
			false,
			array(
				'description' => __( 'If a Website URL is placed here, this Underwriter will link to their Website directly.', 'gscr-cpt-underwriters' ),
			)
		);
		
	}
	
	/**
	 * Adds an Admin Column
	 * 
	 * @param		array $columns Array of Admin Columns
	 *                                       
	 * @access		public
	 * @since		1.0.0
	 * @return		array Modified Admin Column Array
	 */
	public function admin_column_add( $columns ) {
		
		$columns['underwriter_website_url'] = _x( 'Website Entered?', 'Website Entered Admin Column Label', 'gscr-cpt-underwriters' );
		
		return $columns;
		
	}
	
	/**
	 * Displays data within Admin Columns
	 * 
	 * @param		string  $column  Admin Column ID
	 * @param		integer $post_id Post ID
	 *                               
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function admin_column_display( $column, $post_id ) {
		
		switch ( $column ) {
				
			case 'underwriter_website_url' :
				if ( rbm_get_field( $column, $post_id ) ) {
					echo '<span class="dashicons dashicons-yes"></span>';
				}
				break;
			default : 
				echo rbm_field( $column, $post_id );
				break;
				
		}
		
	}
	
	/**
	 * Modify the Sortable Admin Columns
	 * 
	 * @param		array $sortable_columns Sortable Admin Columns
	 *                                                
	 * @access		public
	 * @since		1.0.0
	 * @return		array Sortable Admin Columns
	 */
	public function admin_columns_sortable( $sortable_columns ) {
		
		$sortable_columns[ 'underwriter_website_url' ] = '_rbm_underwriter_website_url';
		
		return $sortable_columns;
		
	}
	
	/**
	 * Allow Website Attached Underwriters to be sorted by whether the Website exists or not
	 * This technically also runs on the Frontend, but it isn't important. The condition should never be true anyway in normal use.
	 * 
	 * @param		object $query WP_Query
	 *                       
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function admin_columns_sorting( $query ) {
		
		$orderby = $query->get( 'orderby' );
		
		if ( $orderby == '_rbm_underwriter_website_url' ) {
			
			$query->set( 'meta_query', array(
				array(
					'key' => '_rbm_underwriter_website_url',
					'compare' => 'EXISTS'
				),
			) );
			
			$query->set( 'orderby', 'meta_value_num' );
			
		}
		
	}
	
	/**
	 * Show the Website URL as the Permalink Sample if this Underwriter has one set
	 * 
	 * @param		string  $return    Sample HTML Markup
	 * @param		integer $post_id   Post ID
	 * @param		string  $new_title New Sample Permalink Title
	 * @param		string  $new_slug  New Sample Permalnk Slug
	 * @param		object  $post      WP Post Object
	 *                   
	 * @access		public
	 * @since		1.0.0
	 * @return		string  Modified HTML Markup
	 */
	public function alter_permalink_html( $return, $post_id, $new_title, $new_slug, $post ) {
		
		// No sense in a database query if it isn't the correct Post Type
		if ( $post->post_type == $this->post_type ) {
			
			if ( $website_url = rbm_get_field( 'underwriter_website_url', $post_id ) ) {
				
				$return = preg_replace( '/<a.*<\/a>/', '<a href="' . $website_url . '">' . $website_url . '</a>', $return );
				$return = str_replace( '<span id="edit-slug-buttons"><button type="button" class="edit-slug button button-small hide-if-no-js" aria-label="Edit permalink">Edit</button></span>', '', $return );
				
			}
			
		}
		
		return $return;
		
	}
	
	/**
	 * Replace the_permalink() calls on the Frontend with the Website URL
	 * 
	 * @param		string $url The Post URL
	 *                
	 * @access		public
	 * @since		1.0.0
	 * @return		string Modified URL
	 */
	public function the_permalink( $url ) {
		
		if ( get_post_type() == $this->post_type ) {
			
			if ( $website_url = rbm_get_field( 'underwriter_website_url', get_the_ID() ) ) {
				
				$url = $website_url;
				
			}
			
		}
		
		return $url;
		
	}
	
	/**
	 * Replace get_peramlink() calls on the Frontend with the Website URL
	 * 
	 * @param		string  $url       The Post URL
	 * @param		object  $post      WP Post Object
	 * @param		boolean $leavename Whether to leave the Post Name
	 * @param		boolean $sample    Is it a sample permalink?
	 *     
	 * @access		public
	 * @since		1.0.0
	 * @return		string  Modified URL
	 */
	public function get_permalink( $url, $post, $leavename = false, $sample = false ) {
		
		if ( $post->post_type == $this->post_type ) {
			
			if ( $website_url = rbm_get_field( 'underwriter_website_url', get_the_ID() ) ) {
				
				$url = $website_url;
				
			}
			
		}
		
		return $url;
		
	}
	
	/**
	 * Force a redirect to the Website if one exists
	 * 
	 * @param       string $template Path to Template File
	 *                                                
	 * @since       1.0.0
	 * @return      string Modified Template File Path
 	 */
	public function redirect_to_website( $template ) {
		
		global $wp_query;
		global $post;
		
		if ( is_single() && get_post_type() == $this->post_type ) {
			
			if ( $website_url = rbm_get_field( 'underwriter_website_url', $post->ID ) ) {
				
				header( "Location: $website_url", true, 301 );
			
			}
			
		}
		
		return $template;
	
	}
	
	/**
	 * Underwriter Category Taxonomy
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function register_taxonomy() {
		
		register_taxonomy( $this->post_type . '-category', array( $this->post_type ), array(
			'labels' => array(
				'name' => sprintf( __( '%s Categories', 'gscr-cpt-underwriters' ), $this->label_singular ),
				'singular_name' => sprintf( __( '%s Category', 'gscr-cpt-underwriters' ), $this->label_singular ),
				'menu_name' => sprintf( __( '%s Categories', 'gscr-cpt-underwriters' ), $this->label_singular ),
			),
			'hierarchical' => true,
			'show_admin_column' => true,
		) );
		
	}
	
}