<?php
/**
 * Provides helper functions.
 *
 * @since	  1.0.0
 *
 * @package	GSCR_CPT_Underwriters
 * @subpackage GSCR_CPT_Underwriters/core
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Returns the main plugin object
 *
 * @since		1.0.0
 *
 * @return		GSCR_CPT_Underwriters
 */
function GSCRCPTUNDERWRITERS() {
	return GSCR_CPT_Underwriters::instance();
}