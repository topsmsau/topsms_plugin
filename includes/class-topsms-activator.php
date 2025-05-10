<?php

/**
 * Fired during plugin activation
 *
 * @link       https://eux.com.au
 * @since      1.0.0
 *
 * @package   Topsms
 * @subpackage Topsms/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Topsms
 * @subpackage Topsms/includes
 * @author     EUX <samee@eux.com.au>
 */
class Topsms_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        add_option('topsms_settings_processing', [
            'enabled' => true,
            'template' => ''
        ]);
        
        add_option('topsms_settings_completed', [
            'enabled' => true,
            'template' => ''
        ]);
        
        add_option('topsms_settings_failed', [
            'enabled' => true,
            'template' => ''
        ]);
        
        add_option('topsms_settings_refunded', [
            'enabled' => true,
            'template' => ''
        ]);
        
        add_option('topsms_settings_pending_payment', [
            'enabled' => true,
            'template' => ''
        ]);
        
        add_option('topsms_settings_cancelled', [
            'enabled' => true,
            'template' => ''
        ]);
        
        add_option('topsms_settings_onhold', [
            'enabled' => true,
            'template' => ''
        ]);
        
        add_option('topsms_settings_draft', [
            'enabled' => true,
            'template' => ''
        ]);
	}

}
