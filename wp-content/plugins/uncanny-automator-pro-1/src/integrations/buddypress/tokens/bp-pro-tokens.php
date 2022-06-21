<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Bp_Tokens;

/**
 * Class Bp_Tokens
 *
 * @package Uncanny_Automator_Pro
 */
class Bp_Pro_Tokens extends Bp_Tokens {


	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'BP';

	public function __construct() {
		add_filter( 'automator_maybe_trigger_bp_tokens', [ $this, 'bp_possible_tokens_pro' ], 20, 2 );
		add_filter( 'automator_maybe_parse_token', [ $this, 'parse_bp_pro_token' ], 20, 6 );

	}

	/**
	 * Only load this integration and its triggers and actions if the related
	 * plugin is active
	 *
	 * @param $status
	 * @param $code
	 *
	 * @return bool
	 */
	public function plugin_active( $status, $code ) {

		if ( self::$integration === $code ) {
			if ( class_exists( 'BuddyPress' ) ) {
				$status = true;
			} else {
				$status = false;
			}
		}

		return $status;
	}

	/**
	 * @param $value
	 * @param $pieces
	 * @param $recipe_id
	 * @param $trigger_data
	 *
	 * @return mixed
	 */
	public function parse_bp_pro_token( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		if ( $pieces ) {
			if ( in_array( 'BPGROUPS', $pieces ) ) {
				// Get Group id from meta log
				$group_id = $this->get_meta_data_from_trigger_meta( $user_id, 'BPGROUPS', $replace_args['trigger_id'], $replace_args['trigger_log_id'] );
				if ( $group_id ) {
					$group = groups_get_group( $group_id );
					if ( isset( $group->name ) ) {
						$value = $group->name;
					}
				}
			} elseif ( in_array( 'BPGROUPS_ID', $pieces ) ) {
				// Get Group id from meta log
				$group_id = $this->get_meta_data_from_trigger_meta( $user_id, 'BPGROUPS', $replace_args['trigger_id'], $replace_args['trigger_log_id'] );
				if ( $group_id ) {
					$value = $group_id;
				}
			}
			if ( in_array( 'USER_PROFILE_URL', $pieces ) || in_array( 'MANAGE_GROUP_REQUESTS_URL', $pieces ) ) {
				// Get Group id from meta log
				$value = $this->get_meta_data_from_trigger_meta( $user_id, $pieces[2], $replace_args['trigger_id'], $replace_args['trigger_log_id'] );
				if ( $value ) {
					$value = maybe_unserialize( $value );
				}
			}
		}

		return $value;
	}

	/**
	 * @param $user_id
	 * @param $meta_key
	 * @param $trigger_id
	 * @param $trigger_log_id
	 *
	 * @return mixed|string
	 */
	public function get_meta_data_from_trigger_meta( $user_id, $meta_key, $trigger_id, $trigger_log_id ) {
		global $wpdb;
		if ( empty( $meta_key ) || empty( $trigger_id ) || empty( $trigger_log_id ) ) {
			return '';
		}

		$meta_value = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->prefix}uap_trigger_log_meta WHERE user_id = %d AND meta_key = %s AND automator_trigger_id = %d AND automator_trigger_log_id = %d ORDER BY ID DESC LIMIT 0,1", $user_id, $meta_key, $trigger_id, $trigger_log_id ) );
		if ( ! empty( $meta_value ) ) {
			return maybe_unserialize( $meta_value );
		}

		return '';
	}

	/**
	 * @param array $tokens
	 * @param array $args
	 *
	 * @return array
	 */
	public function bp_possible_tokens_pro( $tokens = [], $args = [] ) {
		$trigger_integration = $args['integration'];
		$trigger_meta        = $args['meta'];
		$fields              = [];
		if ( isset( $args['triggers_meta']['code'] ) && 'BPPOSTGROUPACTIVITY' === $args['triggers_meta']['code'] ) {

			$fields[] = [
				'tokenId'         => 'BPGROUPS_ID',
				'tokenName'       => __( 'Group ID', 'uncanny-automator' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => 'BPUSERACTIVITY',
			];
			$fields[] = [
				'tokenId'         => 'ACTIVITY_ID',
				'tokenName'       => __( 'Activity ID', 'uncanny-automator' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => 'BPUSERACTIVITY',
			];
			$fields[] = [
				'tokenId'         => 'ACTIVITY_CONTENT',
				'tokenName'       => __( 'Activity content', 'uncanny-automator' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => 'BPUSERACTIVITY',
			];
			$fields[] = [
				'tokenId'         => 'ACTIVITY_URL',
				'tokenName'       => __( 'Activity URL', 'uncanny-automator' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => 'BPUSERACTIVITY',
			];
			$fields[] = [
				'tokenId'         => 'ACTIVITY_STREAM_URL',
				'tokenName'       => __( 'Activity stream URL', 'uncanny-automator' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => 'BPUSERACTIVITY',
			];
		}
		$tokens = array_merge( $tokens, $fields );

		return $tokens;
	}
}
