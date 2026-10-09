<?php
namespace Alezux_Members\Modules\Marketing\Includes;

use Alezux_Members\Modules\Marketing\Marketing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cron_Handler {

	public function __construct() {
		add_action( 'alezux_daily_event', [ $this, 'run_daily_checks' ] );
		
		// Ensure event is scheduled
		if ( ! wp_next_scheduled( 'alezux_daily_event' ) ) {
			wp_schedule_event( time(), 'daily', 'alezux_daily_event' );
		}
	}

	public function run_daily_checks() {
		$this->check_inactivity();
	}

	/**
	 * Check for inactive users (5 days)
	 */
	private function check_inactivity() {
		// Log start
		// error_log('Alezux Marketing: Checking inactivity...');

		// Criteria: 
		// 1. Last activity < 5 days ago
		// 2. Has NOT received inactivity email already (meta key empty)
		
		$five_days_ago = date( 'Y-m-d H:i:s', strtotime( '-5 days' ) );

		$args = [
			'role__in'    => [ 'subscriber', 'student', 'customer' ],
			'number'      => 50, // Batch limit to avoid timeouts
			'meta_query'  => [
				'relation' => 'AND',
				[
					'key'     => 'alezux_last_activity',
					'value'   => $five_days_ago,
					'compare' => '<=',
					'type'    => 'DATETIME'
				],
				[
					'key'     => 'alezux_inactivity_email_sent',
					'compare' => 'NOT EXISTS' // Only if NOT sent yet
				]
			],
			'fields' => 'all_with_meta'
		];

		$user_query = new \WP_User_Query( $args );
		$users = $user_query->get_results();

		if ( empty( $users ) ) return;

		$engine = Marketing::get_instance()->get_engine();

		foreach ( $users as $user ) {
			// Send Email
			$sent = $engine->send_email( 'inactivity_alert', $user->user_email, [ 'user' => $user ] );

			if ( $sent ) {
				// Mark as sent
				update_user_meta( $user->ID, 'alezux_inactivity_email_sent', true );
				// error_log("Alezux Inactivity Email sent to: " . $user->user_email);
			}
		}
	}
}
