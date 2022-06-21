<?php

namespace Uncanny_Automator;

/**
 * Class WPLMS_COURSESTARTED
 *
 * @package Uncanny_Automator
 */
class WPLMS_COURSESTARTED {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'WPLMS';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'WPLMSCOURSESTARTED';
		$this->trigger_meta = 'WPLMS_COURSESTART';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/wp-lms/' ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - WP LMS */
			'sentence'            => sprintf( esc_attr__( 'A user starts {{a course:%1$s}} {{a number of:%2$s}} time(s)', 'uncanny-automator' ), $this->trigger_meta, 'NUMTIMES' ),
			/* translators: Logged-in trigger - WP LMS */
			'select_option_name'  => esc_attr__( 'A user starts {{a course}}', 'uncanny-automator' ),
			'action'              => 'wplms_start_course',
			'priority'            => 10,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'wplms_course_started' ),
			'options'             => array(
				Automator()->helpers->recipe->wplms->options->all_wplms_courses( esc_attr__( 'Course', 'uncanny-automator' ), $this->trigger_meta ),
				Automator()->helpers->recipe->options->number_of_times(),
			),
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param integer $course_id
	 * @param integer $user_id
	 * @param null $marks
	 */
	public function wplms_course_started( $course_id, $user_id ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( empty( $user_id ) ) {
			return;
		}
		$recipes            = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$required_course    = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$matched_recipe_ids = array();

		if ( empty( $recipes ) ) {
			return;
		}

		if ( empty( $required_course ) ) {
			return;
		}

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$recipe_id  = absint( $recipe_id );
				$trigger_id = absint( $trigger['ID'] );
				if ( ! isset( $required_course[ $recipe_id ] ) ) {
					continue;
				}
				if ( ! isset( $required_course[ $recipe_id ][ $trigger_id ] ) ) {
					continue;
				}
				if (
					( intval( '-1' ) === intval( $required_course[ $recipe_id ][ $trigger_id ] ) )
					||
					( absint( $course_id ) === absint( $required_course[ $recipe_id ][ $trigger_id ] ) )
				) {
					$matched_recipe_ids[ $recipe_id ] = array(
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					);
				}
			}
		}

		if ( empty( $matched_recipe_ids ) ) {
			return;
		}
		foreach ( $matched_recipe_ids as $matched_recipe_id ) {
			$pass_args = array(
				'code'             => $this->trigger_code,
				'meta'             => $this->trigger_meta,
				'user_id'          => $user_id,
				'recipe_to_match'  => $matched_recipe_id['recipe_id'],
				'trigger_to_match' => $matched_recipe_id['trigger_id'],
				'ignore_post_id'   => true,
				'is_signed_in'     => true,
			);

			$arr        = Automator()->maybe_add_trigger_entry( $pass_args, false );
			if ( $arr ) {
				foreach ( $arr as $result ) {
					if ( true === $result['result'] ) {
						$token_args = array(
							'course_id' => $course_id,
							'user_id'   => $user_id,
							'action'    => 'course_started',
						);
						do_action( 'automator_wplms_save_tokens', $token_args, $result['args']  );
						Automator()->maybe_trigger_complete( $result['args'] );
					}
				}
			}
		}
	}
}
