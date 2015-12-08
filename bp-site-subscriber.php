<?php
/*
Plugin Name: BuddyPress Site Subscriber
Plugin URI: https://github.com/klandestino/
Description: Let's users subscribe to sites in a WordPress multisite network. Sends notifications and (optionally) emails to them when new posts are created.
Version: 0.1
Requires at least: 4.3
Tested up to: 4.4
License: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
Author: lakrisgubben
Author URI: https://github.com/lakrisgubben
Text Domain: bp-site-subscriber
Domain Path: /languages
Tags: BuddyPress, Multisite
*/

define( 'BP_SITE_SUBSCRIBER_PLUGIN_DIR', dirname( __FILE__ ) );

// Where to find plugin templates
define( 'BP_SITE_SUBSCRIBER_TEMPLATE_DIR', dirname( __FILE__ ) . '/templates' );

/**
 * Initiates this plugin by setting up the site subscriber component.
 * @return void
 */
function bp_site_subscriber_init() {
	if( version_compare( BP_VERSION, '2.3', '>' ) ) {
		// Buddypress component that handles the notifications
		require_once( dirname( __FILE__ ) . '/includes/notifier.php' );
		BP_Site_Subscriber::__setup();

		//Adds a widget with the subscribe to site button
		require_once( dirname( __FILE__ ) . '/includes/widget.php' );
	}
}

// Setup component with bp_setup_components action
add_action( 'bp_setup_components', 'bp_site_subscriber_init' );

/**
 * Adds site subscriber component to the active components list.
 * This is a must do if we want the notifications to work.
 * @param array $components alread activated components
 * @return array
 */
function bp_site_subscriber_add_active_component( $components ) {
	return array_merge( $components, array( 'site_subscriber' => true ) );
}

// Setup active components with bp_active_components filter
add_filter( 'bp_active_components', 'bp_site_subscriber_add_active_component' );