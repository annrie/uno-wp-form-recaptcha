<?php
/**
 * Plugin Name: Uno WP Form reCAPTCHA
 * Plugin URI: https://github.com/annrie/uno-wp-form-recaptcha
 * Description: Adds a Google reCAPTCHA field with server-side verification and a honeypot field to Uno WP Form.
 * Version: 1.1.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: annrie
 * Author URI: https://phantomoon.com
 * Text Domain: uno-wp-form-recaptcha
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package uno-wp-form-recaptcha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const UNO_WP_FORM_RECAPTCHA_OPTION_SITEKEY   = 'uno-wp-form-recaptcha-sitekey';
const UNO_WP_FORM_RECAPTCHA_OPTION_SECRETKEY = 'uno-wp-form-recaptcha-secretkey';
const UNO_WP_FORM_RECAPTCHA_OPTION_CENTERING = 'uno-wp-form-recaptcha-centering';
const UNO_WP_FORM_RECAPTCHA_OPTION_MIGRATED  = 'uno-wp-form-recaptcha-migrated';
const UNO_WP_FORM_RECAPTCHA_TEXTDOMAIN       = 'uno-wp-form-recaptcha';
const UNO_WP_FORM_RECAPTCHA_HONEYPOT_FIELD   = 'uwf-recaptcha-hp';

/**
 * Load translations and migrate legacy settings once.
 */
function uno_wp_form_recaptcha_init() {
	load_plugin_textdomain(
		UNO_WP_FORM_RECAPTCHA_TEXTDOMAIN,
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);

	uno_wp_form_recaptcha_migrate_legacy_options();
}
add_action( 'plugins_loaded', 'uno_wp_form_recaptcha_init' );

/**
 * Copy settings from the old add-on when present.
 */
function uno_wp_form_recaptcha_migrate_legacy_options() {
	$migrated = (string) get_option( UNO_WP_FORM_RECAPTCHA_OPTION_MIGRATED, '' );
	if ( '2' === $migrated ) {
		return;
	}

	if ( '' === $migrated ) {
		$legacy_sitekey = get_option( 'mw-wp-form-recaptcha-sitekey', null );
		if ( null !== $legacy_sitekey && '' === (string) get_option( UNO_WP_FORM_RECAPTCHA_OPTION_SITEKEY, '' ) ) {
			update_option( UNO_WP_FORM_RECAPTCHA_OPTION_SITEKEY, sanitize_text_field( (string) $legacy_sitekey ) );
		}

		$legacy_centering = get_option( 'mw-wp-form-recaptcha-centering', null );
		if ( null !== $legacy_centering && '' === (string) get_option( UNO_WP_FORM_RECAPTCHA_OPTION_CENTERING, '' ) ) {
			update_option( UNO_WP_FORM_RECAPTCHA_OPTION_CENTERING, '1' === (string) $legacy_centering ? '1' : '' );
		}
	}

	// v1.1.0: secret key added for server-side verification.
	$legacy_secretkey = get_option( 'mw-wp-form-recaptcha-secretkey', null );
	if ( null !== $legacy_secretkey && '' === (string) get_option( UNO_WP_FORM_RECAPTCHA_OPTION_SECRETKEY, '' ) ) {
		update_option( UNO_WP_FORM_RECAPTCHA_OPTION_SECRETKEY, sanitize_text_field( (string) $legacy_secretkey ) );
	}

	update_option( UNO_WP_FORM_RECAPTCHA_OPTION_MIGRATED, '2' );
}

/**
 * Add settings page.
 */
function uno_wp_form_recaptcha_admin_menu() {
	add_menu_page(
		__( 'Uno WP Form reCAPTCHA', UNO_WP_FORM_RECAPTCHA_TEXTDOMAIN ),
		__( 'Uno WP Form reCAPTCHA', UNO_WP_FORM_RECAPTCHA_TEXTDOMAIN ),
		'manage_options',
		'uno-wp-form-recaptcha',
		'uno_wp_form_recaptcha_admin_page',
		'dashicons-shield-alt',
		58
	);
}
add_action( 'admin_menu', 'uno_wp_form_recaptcha_admin_menu' );

/**
 * Save settings.
 */
function uno_wp_form_recaptcha_save_settings() {
	if ( ! isset( $_POST['uno-wp-form-recaptcha-nonce'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', UNO_WP_FORM_RECAPTCHA_TEXTDOMAIN ) );
	}

	check_admin_referer( 'uno-wp-form-recaptcha-nonce-key', 'uno-wp-form-recaptcha-nonce' );

	$sitekey = isset( $_POST['uno-wp-form-recaptcha-sitekey'] )
		? sanitize_text_field( wp_unslash( $_POST['uno-wp-form-recaptcha-sitekey'] ) )
		: '';
	update_option( UNO_WP_FORM_RECAPTCHA_OPTION_SITEKEY, $sitekey );

	$secretkey = isset( $_POST['uno-wp-form-recaptcha-secretkey'] )
		? sanitize_text_field( wp_unslash( $_POST['uno-wp-form-recaptcha-secretkey'] ) )
		: '';
	update_option( UNO_WP_FORM_RECAPTCHA_OPTION_SECRETKEY, $secretkey );

	$centering = isset( $_POST['uno-wp-form-recaptcha-centering'] ) ? '1' : '';
	update_option( UNO_WP_FORM_RECAPTCHA_OPTION_CENTERING, $centering );

	add_settings_error(
		'uno-wp-form-recaptcha',
		'uno-wp-form-recaptcha-saved',
		__( 'Settings saved.', UNO_WP_FORM_RECAPTCHA_TEXTDOMAIN ),
		'updated'
	);
	set_transient( 'settings_errors', get_settings_errors(), 30 );

	wp_safe_redirect( menu_page_url( 'uno-wp-form-recaptcha', false ) );
	exit;
}
add_action( 'admin_init', 'uno_wp_form_recaptcha_save_settings' );

/**
 * Render settings page.
 */
function uno_wp_form_recaptcha_admin_page() {
	$sitekey   = (string) get_option( UNO_WP_FORM_RECAPTCHA_OPTION_SITEKEY, '' );
	$secretkey = (string) get_option( UNO_WP_FORM_RECAPTCHA_OPTION_SECRETKEY, '' );
	$centering = (string) get_option( UNO_WP_FORM_RECAPTCHA_OPTION_CENTERING, '' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Uno WP Form reCAPTCHA', UNO_WP_FORM_RECAPTCHA_TEXTDOMAIN ); ?></h1>
		<?php settings_errors(); ?>

		<form id="uno-wp-form-recaptcha-form" method="post" action="">
			<?php wp_nonce_field( 'uno-wp-form-recaptcha-nonce-key', 'uno-wp-form-recaptcha-nonce' ); ?>

			<h2><?php esc_html_e( 'Site key', UNO_WP_FORM_RECAPTCHA_TEXTDOMAIN ); ?></h2>
			<p>
				<input type="text" name="uno-wp-form-recaptcha-sitekey" class="regular-text" value="<?php echo esc_attr( $sitekey ); ?>">
				<br>
				<a href="https://www.google.com/recaptcha/admin#list" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Get the Site key of reCAPTCHA.', UNO_WP_FORM_RECAPTCHA_TEXTDOMAIN ); ?>
				</a>
			</p>

			<h2><?php esc_html_e( 'Secret key', UNO_WP_FORM_RECAPTCHA_TEXTDOMAIN ); ?></h2>
			<p>
				<input type="text" name="uno-wp-form-recaptcha-secretkey" class="regular-text" value="<?php echo esc_attr( $secretkey ); ?>">
				<br>
				<?php esc_html_e( 'Required for server-side verification. Without it, the reCAPTCHA can be bypassed by bots that do not run JavaScript.', UNO_WP_FORM_RECAPTCHA_TEXTDOMAIN ); ?>
			</p>

			<h2><?php esc_html_e( 'Display', UNO_WP_FORM_RECAPTCHA_TEXTDOMAIN ); ?></h2>
			<p>
				<label>
					<input type="checkbox" name="uno-wp-form-recaptcha-centering" value="1" <?php checked( $centering, '1' ); ?>>
					<?php esc_html_e( 'Centering reCAPTCHA', UNO_WP_FORM_RECAPTCHA_TEXTDOMAIN ); ?>
				</label>
			</p>

			<p class="submit">
				<input type="submit" value="<?php esc_attr_e( 'Save Changes', UNO_WP_FORM_RECAPTCHA_TEXTDOMAIN ); ?>" class="button button-primary button-large">
			</p>
		</form>
	</div>
	<?php
}

/**
 * Enqueue frontend assets.
 */
function uno_wp_form_recaptcha_enqueue_scripts() {
	// Honeypot CSS is needed even when reCAPTCHA keys are not configured.
	wp_register_style( 'uno-wp-form-recaptcha', false, array(), '1.1.0' );
	wp_enqueue_style( 'uno-wp-form-recaptcha' );
	wp_add_inline_style(
		'uno-wp-form-recaptcha',
		'.uwf-recaptcha-hp{position:absolute!important;left:-9999px!important;top:auto!important;width:1px;height:1px;overflow:hidden;}'
	);

	$sitekey = (string) get_option( UNO_WP_FORM_RECAPTCHA_OPTION_SITEKEY, '' );
	if ( '' === $sitekey ) {
		return;
	}

	wp_enqueue_script(
		'google-recaptcha',
		'https://www.google.com/recaptcha/api.js?onload=unoWpFormRecaptchaOnload&render=explicit',
		array(),
		null,
		true
	);

	$script = sprintf(
		"(function($){\n" .
		"  var apiReady = false;\n" .
		"  var domReady = false;\n" .
		"  function enableSubmit(){ $('.uno_wp_form_input button, .uno_wp_form_input input[type=\"submit\"]').prop('disabled', false); }\n" .
		"  function renderRecaptcha(){\n" .
		"    if (!apiReady || !domReady || !window.grecaptcha || !window.grecaptcha.render) return;\n" .
		"    var \$buttons = $('.uno_wp_form_input button, .uno_wp_form_input input[type=\"submit\"]');\n" .
		"    if (!\$buttons.length) return;\n" .
		"    var container = document.querySelector('.uno_wp_form_input .g-recaptcha');\n" .
		"    if (container && container.getAttribute('data-uno-wp-form-recaptcha-rendered') === '1') return;\n" .
		"    if (!container) {\n" .
		"      container = document.createElement('div');\n" .
		"      container.className = 'g-recaptcha';\n" .
		"      \$buttons.first().before(container);\n" .
		"    }\n" .
		"    container.setAttribute('data-uno-wp-form-recaptcha-rendered', '1');\n" .
		"    \$buttons.prop('disabled', true);\n" .
		"    $('.uno_wp_form_confirm input, .uno_wp_form_confirm select, .uno_wp_form_confirm textarea, .uno_wp_form_confirm button').prop('disabled', false);\n" .
		"    window.grecaptcha.render(container, { sitekey: container.getAttribute('data-sitekey') || '%s', callback: enableSubmit });\n" .
		"  }\n" .
		"  window.unoWpFormRecaptchaOnload = function(){ apiReady = true; renderRecaptcha(); };\n" .
		"  $(function(){ domReady = true; renderRecaptcha(); });\n" .
		"})(jQuery);",
		esc_js( $sitekey )
	);
	wp_add_inline_script( 'google-recaptcha', $script, 'before' );

	$css = '.g-recaptcha{margin:20px 0 15px;}';
	if ( '1' === (string) get_option( UNO_WP_FORM_RECAPTCHA_OPTION_CENTERING, '' ) ) {
		$css .= '.g-recaptcha>div{margin:0 auto;}';
	}
	wp_add_inline_style( 'uno-wp-form-recaptcha', $css );
}
add_action( 'wp_enqueue_scripts', 'uno_wp_form_recaptcha_enqueue_scripts' );

/**
 * Register server-side validation rules once uno-wp-form is available.
 */
function uno_wp_form_recaptcha_register_rules() {
	if ( ! class_exists( 'Uno_WP_Form_Abstract_Validation_Rule' ) ) {
		return;
	}

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-validation-rules.php';

	// Constructors self-register via the `unoform_validation_rules` filter.
	new Uno_WP_Form_Recaptcha_Validation_Rule();
	new Uno_WP_Form_Recaptcha_Honeypot_Rule();
}
add_action( 'init', 'uno_wp_form_recaptcha_register_rules' );

/**
 * Attach validation rules to the submitted form.
 *
 * Runs just before Uno_WP_Form_Main_Controller::_template_redirect()
 * (priority 10000) so the `unoform_validation_{form_key}` filter is
 * registered for whichever form was posted.
 */
function uno_wp_form_recaptcha_wire_validation() {
	if ( ! class_exists( 'UWF_Config' ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- uno-wp-form validates its own CSRF token.
	$form_id = isset( $_POST[ UWF_Config::NAME . '-form-id' ] ) ? (int) $_POST[ UWF_Config::NAME . '-form-id' ] : 0;
	if ( ! $form_id ) {
		return;
	}

	add_filter(
		'unoform_validation_' . UWF_Config::NAME . '-' . $form_id,
		'uno_wp_form_recaptcha_add_validation_rules',
		10,
		3
	);
}
add_action( 'template_redirect', 'uno_wp_form_recaptcha_wire_validation', 9999 );

/**
 * Add honeypot / reCAPTCHA rules on confirm and complete transitions.
 *
 * Note: uno-wp-form ignores the return value of this filter; rules must be
 * set directly on the passed Validation object.
 *
 * @param Uno_WP_Form_Validation $Validation Validation object.
 * @param array                  $data       Posted data.
 * @param Uno_WP_Form_Data       $Data       Data object (clone).
 * @return Uno_WP_Form_Validation
 */
function uno_wp_form_recaptcha_add_validation_rules(
	$Validation,
	// phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
	$data,
	$Data
) {
	$condition = $Data->get_post_condition();
	if ( ! in_array( $condition, array( 'confirm', 'complete' ), true ) ) {
		return $Validation;
	}

	$Validation->set_rule( UNO_WP_FORM_RECAPTCHA_HONEYPOT_FIELD, 'uno_recaptcha_honeypot' );

	$sitekey   = (string) get_option( UNO_WP_FORM_RECAPTCHA_OPTION_SITEKEY, '' );
	$secretkey = (string) get_option( UNO_WP_FORM_RECAPTCHA_OPTION_SECRETKEY, '' );
	if ( '' !== $sitekey && '' !== $secretkey ) {
		$Validation->set_rule( 'uno-wp-form-recaptcha-check', 'uno_recaptcha_check' );
	}

	return $Validation;
}

/**
 * Inject the honeypot field just before the closing form tag.
 *
 * @param string $html Form end HTML.
 * @return string
 */
function uno_wp_form_recaptcha_honeypot_field( $html ) {
	$html .= sprintf(
		'<div class="uwf-recaptcha-hp" aria-hidden="true"><label>%1$s<input type="text" name="%2$s" value="" tabindex="-1" autocomplete="off"></label></div>',
		esc_html__( 'Leave this field empty.', UNO_WP_FORM_RECAPTCHA_TEXTDOMAIN ),
		esc_attr( UNO_WP_FORM_RECAPTCHA_HONEYPOT_FIELD )
	);
	return $html;
}
add_filter( 'unoform_form_end_html', 'uno_wp_form_recaptcha_honeypot_field' );
