<?php

namespace Uncanny_Automator_Pro;

/**
 * Class AFFWP_REJECTREFERRAL
 * @package Uncanny_Automator_Pro
 */
class AFFWP_REJECTREFERRAL {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'AFFWP';

	/**
	 * @var string
	 */
	private $trigger_code;
	/**
	 * @var string
	 */
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'REJECTREFERRAL';
		$this->trigger_meta = 'AFFWPREJECTREFERRAL';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;
		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/affiliatewp/' ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'is_pro'              => true,
			/* translators: Logged-in trigger - Affiliate WP */
			'sentence'            => sprintf( __( "An affiliate's referral of {{a specific type:%1\$s}} is rejected", 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - Affiliate WP */
			'select_option_name'  => __( "An affiliate's referral of {{a specific type}} is rejected", 'uncanny-automator-pro' ),
			'action'              => 'affwp_set_referral_status',
			'priority'            => 99,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'affwp_reject_referral' ),
			'options'             => [
				$uncanny_automator->helpers->recipe->affiliate_wp->options->get_referral_types( null, $this->trigger_meta, [ 'any_option' => true ] ),
			],
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * @param $referral_id
	 * @param $new_status
	 * @param $old_status
	 *
	 * @return mixed
	 */
	public function affwp_reject_referral( $referral_id, $new_status, $old_status ) {
		global $uncanny_automator;

		if ( (string) $new_status === (string) $old_status || 'rejected' !== (string) $new_status ) {
			return $new_status;
		}

		$recipes       = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$required_type = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$referral      = affwp_get_referral( $referral_id );
		$type          = $referral->type;
		$user_id       = affwp_get_affiliate_user_id( $referral->affiliate_id );

		if ( 0 === absint( $user_id ) ) {
			// Its a logged in recipe and
			// user ID is 0. Skip process
			return;
		}

		$user               = get_user_by( 'id', $user_id );
		$affiliate          = affwp_get_affiliate( $referral->affiliate_id );
		$matched_recipe_ids = [];


		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				if ( $required_type[ $recipe_id ][ $trigger_id ] == $type || intval( '-1' ) === intval( $required_type[ $recipe_id ][ $trigger_id ] ) ) {
					$matched_recipe_ids[] = [
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					];
				}
			}
		}

		if ( ! empty( $matched_recipe_ids ) ) {
			foreach ( $matched_recipe_ids as $matched_recipe_id ) {
				$pass_args = [
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'user_id'          => $user_id,
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
					'ignore_post_id'   => true,
				];

				$args = $uncanny_automator->maybe_add_trigger_entry( $pass_args, false );

				if ( $args ) {
					foreach ( $args as $result ) {
						if ( true === $result['result'] ) {

							$trigger_meta = [
								'user_id'        => $user_id,
								'trigger_id'     => $result['args']['trigger_id'],
								'trigger_log_id' => $result['args']['get_trigger_id'],
								'run_number'     => $result['args']['run_number'],
							];

							$trigger_meta['meta_key']   = $this->trigger_meta;
							$trigger_meta['meta_value'] = maybe_serialize( $type );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'AFFILIATEWPID';
							$trigger_meta['meta_value'] = maybe_serialize( $referral->affiliate_id );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'AFFILIATEWPSTATUS';
							$trigger_meta['meta_value'] = maybe_serialize( $affiliate->status );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'AFFILIATEWPREGISTERDATE';
							$trigger_meta['meta_value'] = maybe_serialize( $affiliate->date_registered );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'AFFILIATEWPPAYMENTEMAIL';
							$trigger_meta['meta_value'] = maybe_serialize( $affiliate->payment_email );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'AFFILIATEWPACCEMAIL';
							$trigger_meta['meta_value'] = maybe_serialize( $user->user_email );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'AFFILIATEWPWEBSITE';
							$trigger_meta['meta_value'] = maybe_serialize( $user->user_url );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'AFFILIATEWPURL';
							$trigger_meta['meta_value'] = maybe_serialize( affwp_get_affiliate_referral_url( array( 'affiliate_id' => $referral->affiliate_id ) ) );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'AFFILIATEWPREFRATE';
							$trigger_meta['meta_value'] = ! empty( $affiliate->rate ) ? maybe_serialize( $affiliate->rate ) : maybe_serialize( '0' );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'AFFILIATEWPREFRATETYPE';
							$trigger_meta['meta_value'] = ! empty( $affiliate->rate_type ) ? maybe_serialize( $affiliate->rate_type ) : maybe_serialize( '0' );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'AFFILIATEWPPROMOMETHODS';
							$trigger_meta['meta_value'] = maybe_serialize( get_user_meta( $affiliate->user_id, 'affwp_promotion_method', true ) );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'AFFILIATEWPNOTES';
							$trigger_meta['meta_value'] = maybe_serialize( affwp_get_affiliate_meta( $affiliate->affiliate_id, 'notes', true ) );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'REFERRALAMOUNT';
							$trigger_meta['meta_value'] = maybe_serialize( $referral->amount );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'REFERRALDATE';
							$trigger_meta['meta_value'] = maybe_serialize( $referral->date );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'REFERRALDESCRIPTION';
							$trigger_meta['meta_value'] = maybe_serialize( $referral->description );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'REFERRALCONTEXT';
							$trigger_meta['meta_value'] = maybe_serialize( $referral->context );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'REFERRALREFERENCE';
							$trigger_meta['meta_value'] = maybe_serialize( $referral->reference );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'REFERRALCUSTOM';
							$trigger_meta['meta_value'] = maybe_serialize( $referral->custom );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'REFERRALSTATUS';
							$trigger_meta['meta_value'] = maybe_serialize( $referral->status );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$dynamic_coupons = affwp_get_dynamic_affiliate_coupons( $affiliate->ID, false );
							$coupons         = '';
							if ( isset( $dynamic_coupons ) && is_array( $dynamic_coupons ) ) {
								foreach ( $dynamic_coupons as $coupon ) {
									$coupons .= $coupon->coupon_code . '<br/>';
								}
							}

							$trigger_meta['meta_key']   = 'AFFILIATEWPCOUPON';
							$trigger_meta['meta_value'] = maybe_serialize( $coupons );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$uncanny_automator->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}
	}
}
