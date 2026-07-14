<?php
/**
 * Server-side validation rules for Uno WP Form reCAPTCHA.
 *
 * Loaded on `init` only when uno-wp-form is active
 * (Uno_WP_Form_Abstract_Validation_Rule exists).
 *
 * @package uno-wp-form-recaptcha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * reCAPTCHA server-side verification rule.
 *
 * - input -> confirm: verifies g-recaptcha-response via siteverify and
 *   stores a verified flag in the uno-wp-form session.
 * - confirm -> complete: requires the session flag (the confirm page has
 *   no widget, and bots POSTing directly to complete never obtain the flag).
 *   Falls back to verifying a POSTed token for forms without a confirm step.
 */
class Uno_WP_Form_Recaptcha_Validation_Rule extends Uno_WP_Form_Abstract_Validation_Rule {

	/**
	 * Validation rule name.
	 *
	 * @var string
	 */
	protected $name = 'uno_recaptcha_check';

	const SESSION_NAME = 'recaptcha';
	const SESSION_KEY  = 'verified';

	/**
	 * Validation process.
	 *
	 * @param string $name    Field name (virtual key).
	 * @param array  $options Validation options.
	 * @return string|null Error message on failure.
	 */
	public function rule( $name, array $options = array() ) {
		$secret = (string) get_option( UNO_WP_FORM_RECAPTCHA_OPTION_SECRETKEY, '' );
		if ( '' === $secret ) {
			return null;
		}

		$defaults = array(
			'message' => __( 'reCAPTCHA verification failed. Please try again.', UNO_WP_FORM_RECAPTCHA_TEXTDOMAIN ),
		);
		$options  = array_merge( $defaults, $options );

		$condition = $this->Data->get_post_condition();
		$session   = new Uno_WP_Form_Session( self::SESSION_NAME );

		if ( 'confirm' === $condition ) {
			$verified = $this->_verify_posted_token( $secret );
			if ( false === $verified ) {
				return $options['message'];
			}
			if ( true === $verified ) {
				$session->set( self::SESSION_KEY, time() );
			}
			return null;
		}

		if ( 'complete' === $condition ) {
			$flag = $session->get( self::SESSION_KEY );
			if ( $flag && ( time() - (int) $flag ) < HOUR_IN_SECONDS ) {
				$session->clear_value( self::SESSION_KEY );
				return null;
			}

			// Forms without a confirm step submit the widget token directly.
			$verified = $this->_verify_posted_token( $secret );
			if ( true === $verified || null === $verified ) {
				return null;
			}
			return $options['message'];
		}

		return null;
	}

	/**
	 * Verify the g-recaptcha-response in the current POST against Google.
	 *
	 * @param string $secret Secret key.
	 * @return bool|null True on success, false on failure,
	 *                   null when Google is unreachable (fail-open, logged).
	 */
	private function _verify_posted_token( $secret ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- uno-wp-form validates its own CSRF token.
		$token = isset( $_POST['g-recaptcha-response'] )
			? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) )
			: '';

		if ( '' === $token ) {
			return false;
		}

		$response = wp_remote_post(
			'https://www.google.com/recaptcha/api/siteverify',
			array(
				'timeout' => 10,
				'body'    => array(
					'secret'   => $secret,
					'response' => $token,
					'remoteip' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( 'uno-wp-form-recaptcha: siteverify request failed: ' . $response->get_error_message() );
			return null;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		return is_array( $body ) && ! empty( $body['success'] );
	}

	/**
	 * Add setting field to validation rule setting panel.
	 *
	 * @param int   $key   ID of validation rule.
	 * @param array $value Content of validation rule.
	 */
	public function admin(
		// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$key,
		$value
		// phpcs:enable
	) {
	}
}

/**
 * Honeypot rule: rejects the submission when the visually hidden
 * field injected via `unoform_form_end_html` has been filled in.
 */
class Uno_WP_Form_Recaptcha_Honeypot_Rule extends Uno_WP_Form_Abstract_Validation_Rule {

	/**
	 * Validation rule name.
	 *
	 * @var string
	 */
	protected $name = 'uno_recaptcha_honeypot';

	/**
	 * Validation process.
	 *
	 * @param string $name    Field name (the honeypot input).
	 * @param array  $options Validation options.
	 * @return string|null Error message on failure.
	 */
	public function rule( $name, array $options = array() ) {
		$defaults = array(
			'message' => __( 'The contents which you input were judged with spam.', UNO_WP_FORM_RECAPTCHA_TEXTDOMAIN ),
		);
		$options  = array_merge( $defaults, $options );

		$value = $this->Data->get_post_value_by_key( $name );
		if ( ! is_null( $value ) && '' !== (string) $value ) {
			return $options['message'];
		}
		return null;
	}

	/**
	 * Add setting field to validation rule setting panel.
	 *
	 * @param int   $key   ID of validation rule.
	 * @param array $value Content of validation rule.
	 */
	public function admin(
		// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$key,
		$value
		// phpcs:enable
	) {
	}
}
