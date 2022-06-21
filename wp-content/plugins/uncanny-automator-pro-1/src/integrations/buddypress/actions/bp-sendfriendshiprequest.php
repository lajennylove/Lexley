<?php

namespace Uncanny_Automator_Pro;
/**
 * Class BP_SENDFRIENDSHIPREQUEST
 * @package Uncanny_Automator_Pro
 */
class BP_SENDFRIENDSHIPREQUEST {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'BP';

	private $action_code;
	private $action_meta;

	/**
	 * SetAutomatorTriggers constructor.
	 */
	public function __construct() {
		$this->action_code = 'BPSENDFRIENDSHIPREQUEST';
		$this->action_meta = 'BPUSERS';

		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object.
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = [
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/buddyboss/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - BuddyPress */
			'sentence'           => sprintf( esc_attr__( 'Send a friendship request to {{a user:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - BuddyPress */
			'select_option_name' => esc_attr__( 'Send a friendship request to {{a user}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => [ $this, 'bp_send_friendship_request' ],
			'options'            => array(
				$uncanny_automator->helpers->recipe->buddypress->options->all_buddypress_users(),
			),
		];

		$uncanny_automator->register->action( $action );
	}

	/**
	 * Send a private message
	 *
	 * @param string $user_id
	 * @param array $action_data
	 * @param string $recipe_id
	 *
	 * @return void
	 *
	 * @since 1.1
	 */
	public function bp_send_friendship_request( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;
		$friend_userid = $action_data['meta'][ $this->action_meta ];

		if ( function_exists( 'friends_add_friend' ) ) {
			$send = friends_add_friend( $user_id, $friend_userid );
			if ( $send === false ) {
				$action_data['complete_with_errors'] = true;
				$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, __( 'We are unable to send friendship request to selected user.', 'uncanny-automator-pro' ) );
			} else {
				$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
			}
		} else {
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, __( 'BuddyBoss connection module is not active.', 'uncanny-automator-pro' ) );
		}
	}

}
