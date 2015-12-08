<?php

/**
 * Notifier component
 */
class BP_Site_Subscriber extends BP_Component {

	/**
	 * Site subscriber component setup. Creates component object
	 * and inserts it in buddpress.
	 */
	static public function __setup() {
		global $bp;
		$bp->site_subscriber = new BP_Site_Subscriber();
	}

	/**
	 * Start the site subscriber component creation process
	 */
	public function __construct() {
		parent::start(
			'site_subscriber',
			__( 'Site Subscriber', 'bp-site-subscriber' ),
			BP_PLUGIN_DIR
		);

		// Wordpress init
		add_action( 'init', array( $this, 'init' ) );

		/**
		 * Actions and filters for notification adding and deleting
		 */

		// Add new notification when a new post is published
		add_action( 'publish_post', array( $this, 'add_notification' ), 10, 2 );
		// Mark users notification connected to a post as read
		add_action( 'the_post', array( $this, 'mark_notification_as_read' ) );
		// Delete notifications if post is deleted
		add_action( 'delete_post', array( $this, 'delete_notifications' ) );
		// Delete notifications if post is trashed
		add_action( 'wp_trash_post', array( $this, 'delete_notifications' ) );

		/**
		 * Actions and filters for subscribing
		 */
		add_action( 'wp_ajax_bp_site_subscriber_subscribe', array( $this, 'change_subscription' ) );

		/**
		 * Actions and filters for settings
		 */

		// Action run when displaying notification settings (enable or disable emails)
		add_action( 'bp_notification_settings', array( $this, 'settings_screen' ) );

	}

	/**
	 *
	 */
	public function init() {
		load_plugin_textdomain( 'bp-site-subscriber', false, plugin_basename( BP_SITE_SUBSCRIBER_PLUGIN_DIR ) . "/languages/" );
	}

	/**
	 * Setting up buddypress component properties
	 * This is an override
	 * @return void
	 */
	public function setup_globals( $args = array() ) {
		if ( ! defined( 'BP_SITE_SUBSCRIBER_SLUG' ) ) {
			define( 'BP_SITE_SUBSCRIBER_SLUG', $this->id );
		}

		$args = array(
			'slug' => BP_SITE_SUBSCRIBER_SLUG,
			'has_directory' => false,
			'notification_callback' => 'bp_site_subscriber_notification_format'
		);

		parent::setup_globals( $args );
	}

	/**
	 * Locates and loads a template by using Wordpress locate_template.
	 * If no template is found, it loads a template from this plugins template
	 * directory.
	 * @see locate_template
	 * @param string $slug
	 * @param string $name
	 * @return void
	 */
	public static function get_template( $slug, $name = '' ) {
		$template_names = array(
			$slug . '-' . $name . '.php',
			$slug . '.php'
		);

		$located = locate_template( $template_names );

		if ( empty( $located ) ) {
			foreach( $template_names as $name ) {
				if ( file_exists( BP_SITE_SUBSCRIBER_TEMPLATE_DIR . '/' . $name ) ) {
					load_template( BP_SITE_SUBSCRIBER_TEMPLATE_DIR . '/' . $name, false );
					return;
				}
			}
		} else {
			load_template( $located, false );
		}
	}

	/**
	 * Adds a new post notification
	 * @uses publish_post action
	 * @param int $post_id
	 * @param obj $post
	 * @return void
	 */
	public function add_notification( $post_id, $post ) {
		// Bail early if post_type isn't post or if post has already been published
		if ( get_post_type( $post ) !== 'post' || $_POST['original_post_status'] == 'publish' ) {
			return;
		}

		// Get all subscribers
		$subscribers = get_option( 'bp_site_subscriber_subscribers', array() );

		// Bail early if no subscribers
		if ( empty( $subscribers ) ) {
			return;
		}
		$subscribers = array_values( $subscribers );
		$subscribers = array_unique( $subscribers );

		$link = get_permalink( $post_id );
		$title = get_the_title( $post_id );
		$site_name = get_option( 'blogname' );

		foreach ( $subscribers as $subscriber ) {

			if ( $subscriber != $post->post_author && ! in_array( $subscriber, $sent ) ) {
				// Notify
				bp_core_add_notification( $post_id, $subscriber, $this->id, 'new_post_' . $post_id, get_current_blog_id() );
				// Mail
				if ( 'no' != bp_get_user_meta( $subscriber, 'bp-site-subscriber-send-email', true ) ) {
					$user = get_userdata( $subscriber );
					$email = $user->user_email;
					$profile_link = bp_core_get_user_domain( $subscriber );

					$subject = sprintf(
						__( 'New post on the site %s', 'bp-site-subscriber' ),
						$site_name
					);

					$message = sprintf(
						__( "New post %s (%s) on the site %s \n\n--------------------\n\n Go to your profile to disable these emails: %s", 'bp-site-subscriber' ),
						$title,
						$link,
						$site_name,
						$profile_link
					);
					wp_mail( $email, $subject, $message );
				}
			}
		}
	}

	/**
	 * Marks notification connected to post as read
	 * @param array|int $post
	 * @return void
	 */
	public function mark_notification_as_read( $post ) {
		if ( ! is_single( $post ) ) :
			return;
		endif;

		if( is_object( $post ) ) {
			$post_id = $post->ID;
		} elseif( is_array( $post ) ) {
			$post_id = $post[ 'ID' ];
		} elseif( is_numeric( $post ) ) {
			$post_id = $post;
		}

		if( isset( $post_id ) ) {
			BP_Notifications_Notification::update(
				array(
					'is_new' => false,
				),
				array(
					'user_id' => get_current_user_id(),
					'item_id' => $post_id,
					'secondary_item_id' => get_current_blog_id(),
					'component_name' => $this->id,
					'component_action' => 'new_post_' . $post_id,
				)
			);
		}
	}

	/**
	 * Deletes all notifications connected to post if it is deleted.
	 * @param array|int $post
	 * @return void
	 */
	public function delete_notifications( $post ) {
		if( is_object( $post ) ) {
			$post_id = $post->ID;
		} elseif( is_array( $post ) ) {
			$post_id = $post[ 'ID' ];
		} elseif( is_numeric( $post ) ) {
			$post_id = $post;
		}

		if( isset( $post_id ) ) {
			BP_Notifications_Notification::delete(
				array(
					'item_id' => $post_id,
					'secondary_item_id' => get_current_blog_id(),
					'component_name' => $this->id,
					'component_action' => 'new_post_' . $post_id,
				)
			);
		}
	}

	/**
	 * Displays an edit screen for notifications inside the buddypress notification settings form
	 * @return void
	 */
	public function settings_screen() {
		self::get_template( 'bp-site-subscriber-settings' );
	}

	/**
	 * Return a subscribe button
	 */
	public static function subscribe_button() {
		$user_id = get_current_user_id();
		$site_subscribers = get_option( 'bp_site_subscriber_subscribers', array() );
		$nonce = wp_create_nonce( 'bp_site_subscriber_subscribe_nonce' );
		if ( in_array( $user_id, $site_subscribers ) ) {
			$text = __( 'Stop subscription', 'bp-site-subscriber' );
			return '<button id="bp-site-subscriber-button" class="button btn" data-nonce="' . $nonce . '">' . $text . '</button>';
		} else {
			$text = __( 'Subscribe', 'bp-site-subscriber' );
			return '<button id="bp-site-subscriber-button" class="button btn" data-nonce="' . $nonce . '">' . $text . '</button>';
		}
	}

	/**
	 * Change subscription status
	 */
	public function change_subscription() {
		check_ajax_referer( 'bp_site_subscriber_subscribe_nonce', 'bp_site_subscriber_subscribe' );
		$user_id = get_current_user_id();
		$site_subscribers = get_option( 'bp_site_subscriber_subscribers', array() );
		if ( ! in_array( $user_id, $site_subscribers ) ) {
			$site_subscribers[] = $user_id;
			update_option( 'bp_site_subscriber_subscribers', $site_subscribers );
		} else {
			foreach ( $site_subscribers as $key => $site_subscriber ) {
				if ( $user_id == $site_subscriber ) :
					unset( $site_subscribers[$key] );
				endif;
			}
			update_option( 'bp_site_subscriber_subscribers', $site_subscribers );
		}
		echo BP_Site_Subscriber::subscribe_button();
		die;
	}

}

/**
 * Formats notification messages. Used as a callback by buddypress
 * @param string $action usually new_[topic|reply|quote]_[ID]
 * @param int $item_id the post id usually
 * @param int $secondary_item_id the parent post id usually
 * @param int $total_items total item count of how many notifications there are with the same $action
 * @param string $format string, array or object
 * @return array formatted messages
 */
function bp_site_subscriber_notification_format( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {
	if ( get_current_blog_id() == $secondary_item_id ) {
		$link = get_permalink( $item_id );
		$title = get_the_title( $item_id );
		$site_name = get_option( 'blogname' );
	} else {
		// This condition shouldn't be able to happen if multisite isn't active,
		// sinice $secondary_item_id should always be 1, but better double check than fatal error. :)
		if ( is_multisite() ) {
			switch_to_blog( $secondary_item_id );
				$link = get_permalink( $item_id );
				$title = get_the_title( $item_id );
				$site_name = get_option( 'blogname' );
			restore_current_blog();
		}
	}

	$text = sprintf(
		__( 'New post "%s" on the site %s', 'bp-site-subscriber' ),
		$title,
		$site_name
	);

	$notification = sprintf(
		'<a href="%s">%s</a>',
		$link,
		esc_html( $text )
	);
	if ( $format === 'string' ) {
		return $notification;
	} else {
		return array(
			'text' => $text,
			'link' => $link
		);
	}
}