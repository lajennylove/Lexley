<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WCM_USERACCESSCANCELLED
 * @package Uncanny_Automator_Pro
 */
class WCM_USERACCESSCANCELLED {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WCMEMBERSHIPS';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'WCMUSERACCESSCANCELLED';
		$this->trigger_meta = 'WCMMEMBERSHIPPLAN';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/woocommerce-memberships/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - WooCommerce Memberships */
			'sentence'            => sprintf( esc_attr__( "A user's access to {{a membership plan:%1\$s}} cancelled", 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - WooCommerce Memberships */
			'select_option_name'  => esc_attr__( "A user's access to {{a membership plan}} cancelled", 'uncanny-automator-pro' ),
			'action'              => 'wc_memberships_user_membership_status_changed',
			'priority'            => 99,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'wc_user_membership_cancelled' ),
			'options'             => [
				$uncanny_automator->helpers->recipe->wc_memberships->options->wcm_get_all_membership_plans( null, $this->trigger_meta,
					[ 'is_any' => true ] ),
			],
		);

		$uncanny_automator->register->trigger( $trigger );

		return;

	}

	/**
	 * @param $membership_plan
	 * @param $args
	 */
	public function wc_user_membership_cancelled( $user_membership_id, $old_status, $new_status  ) {
		global $uncanny_automator;
		$membership_plan = wc_memberships_get_user_membership( $user_membership_id );

		if ( 0 === $membership_plan->user_id ) {
			// Its a logged in recipe and
			// user ID is 0. Skip process
			return;
		}

		if( 'cancelled' !== $new_status ) {
			return;
		}

		$recipes            = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$required_plan      = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$matched_recipe_ids = [];
		$order_id           = '';

		//Add where option is set to Any product
		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];//return early for all products
				if ( isset( $required_plan[ $recipe_id ] ) && isset( $required_plan[ $recipe_id ][ $trigger_id ] ) ) {
					if ( - 1 === intval( $required_plan[ $recipe_id ][ $trigger_id ] )
					     || $membership_plan->plan_id == $required_plan[ $recipe_id ][ $trigger_id ] ) {
						$matched_recipe_ids[] = [
							'recipe_id'  => $recipe_id,
							'trigger_id' => $trigger_id,
						];
					}
				}
			}
		}

		$membership_plan_type = get_post_meta( $membership_plan->plan_id, '_access_method', true );

		if ( ! empty( $matched_recipe_ids ) ) {
			foreach ( $matched_recipe_ids as $matched_recipe_id ) {
				$pass_args = [
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'user_id'          => $membership_plan->user_id,
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
					'ignore_post_id'   => true,
					'is_signed_in'     => true,
					'post_id'          => $membership_plan->plan_id,
				];

				$args = $uncanny_automator->maybe_add_trigger_entry( $pass_args, false );
				if ( $args ) {
					foreach ( $args as $result ) {
						if ( true === $result['result'] ) {

							if ( $membership_plan_type === 'purchase' ) {
								$order_id = get_post_meta( $membership_plan->post->ID, '_order_id', true );
							}

							$trigger_meta = [
								'user_id'        => $membership_plan->user_id,
								'trigger_id'     => $result['args']['trigger_id'],
								'trigger_log_id' => $result['args']['get_trigger_id'],
								'run_number'     => $result['args']['run_number'],
							];

							$trigger_meta['meta_key']   = 'WCMPLANORDERID';
							$trigger_meta['meta_value'] = maybe_serialize( $order_id );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$uncanny_automator->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}

		return;
	}

}
