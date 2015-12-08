<?php
if ( ! $bp_site_subscriber_send_email = bp_get_user_meta( bp_displayed_user_id(), 'bp-site-subscriber-send-email', true ) ) {
	$bp_site_subscriber_send_email = 'yes';
}
?>
<table class="notification-settings" id="site-subscriber-notification-settings">
	<thead>
		<tr>
			<th class="icon"></th>
			<th class="title"><?php _e( 'Site subscriptions', 'bp-site-subscriber' ) ?></th>
			<th class="yes"><?php _e( 'Yes', 'buddypress' ) ?></th>
			<th class="no"><?php _e( 'No', 'buddypress' )?></th>
		</tr>
	</thead>

	<tbody>
		<tr id="site-subscriber-notification-settings-send-email">
			<td></td>
			<td><?php _e( 'Someone posts on a site that you are subscribed to.', 'bp-site-subscriber' ); ?></td>
			<td class="yes"><input type="radio" name="notifications[bp-site-subscriber-send-email]" value="yes" <?php checked( $bp_site_subscriber_send_email, 'yes', true ) ?>/></td>
			<td class="no"><input type="radio" name="notifications[bp-site-subscriber-send-email]" value="no" <?php checked( $bp_site_subscriber_send_email, 'no', true ) ?>/></td>
		</tr>
	</tbody>
</table>