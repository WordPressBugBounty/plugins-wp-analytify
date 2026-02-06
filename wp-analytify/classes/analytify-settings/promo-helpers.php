<?php
/**
 * Analytify Settings Promo Helpers
 *
 * Contains helper functions for displaying promotional content
 * and upgrade prompts in the Analytify settings pages.
 *
 * @package WP_Analytify
 * @since 8.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Create activation anchor for modules.
 *
 * @param mixed $url   The URL for the upgrade link.
 * @param mixed $text  The text for the upgrade link.
 * @param mixed $addon The addon identifier.
 * @return string
 */
function activate_module_anchor( $url = '', $text = 'Upgrade to Analytify Pro', $addon = '' ) {
	if ( ! empty( $addon ) ) {
		if ( 'analytify-forms' === $addon && file_exists( ABSPATH . 'wp-content/plugins/wp-analytify-forms' ) ) {
			return '<a href=" ' . admin_url( 'admin.php?page=analytify-addons' ) . ' " class="analytify-promo-popup-btn">Activate Addon</a>';
		}
	} elseif ( class_exists( 'WP_Analytify_Pro_Base' ) ) {
		return '<a href=" ' . admin_url( 'admin.php?page=analytify-addons' ) . ' " class="analytify-promo-popup-btn">Activate Addon</a>';
	}
	return '<a href="' . $url . '" class="analytify-promo-popup-btn" target="_blank">' . $text . '</a>';
}

/**
 * Display tracking accordion promo.
 *
 * @param mixed $accordions The accordions array to modify.
 * @return void
 */
function wp_analytify_tracking_accordion_promo( $accordions ) {
	foreach ( $accordions as &$accordion ) {
		if ( 'wp-analytify-events-tracking' === $accordion['id'] ) {
			$accordion['is_active']  = false;
			$accordion['promo_text'] = '<div class="analytify-email-promo-contianer">
                <div class="analytify-email-premium-overlay">
                    <div class="analytify-email-premium-popup">
                        <h3 class="analytify-promo-popup-heading">Unlock Events Tracking</h3>
                        <p class="analytify-promo-popup-paragraph">Our Events tracking feature helps you to setup and track custom events on your WordPress website. Custom events will help you track and measure the performance of the most important Outbound links like Affiliate links. Setting up custom events is tricky for the beginners. But with the Analytify\'s Events Tracking you can easily acheive this on your WordPress websites.</p>
                        <ul class="analytify-promo-popup-list">
                            <li>Affiliate Tracking</li>
                            <li>Links & Clicks Tracking</li>
                            <li>Enhanced Link Attribution</li>
                            <li>Anchor Tracking</li>
                            <li>File downloads Tracking</li>
                            <li>Track outbound links</li>
                        </ul>
                        ' . activate_module_anchor( 'https://analytify.io/pricing?utm_source=analytify-lite&utm_medium=tracking-tab&utm_content=Events+Tracking&utm_campaign=pro-upgrade' ) . '
                    </div>
                </div>
            </div>';
		} elseif ( 'wp-analytify-custom-dimensions' === $accordion['id'] ) {
			$accordion['is_active']  = false;
			$accordion['promo_text'] = '<div class="analytify-email-promo-contianer">
                <div class="analytify-email-premium-overlay">
                    <div class="analytify-email-premium-popup">
                        <h3 class="analytify-promo-popup-heading">Unlock Google Custom Dimensions Tracking</h3>
                        <p class="analytify-promo-popup-paragraph">Custom Dimensions helps you to track Categories, Tags, Post Types, and logged in activities within Google Analytics. Remember, you have to setup Custom Dimensions in Google Analytics as well.</p>
                        <ul class="analytify-promo-popup-list">
                            <li>Custom Post Type Tracking</li>
                            <li>Category Tracking</li>
                            <li>Tags Tracking</li>
                            <li>Authors Tracking</li>
                            <li>User-ID Tracking</li>
                            <li>Track logged in activity</li>
                        </ul>
                        ' . activate_module_anchor( 'https://analytify.io/pricing?utm_source=analytify-lite&utm_medium=tracking-tab&utm_content=custom-dimensions&utm_campaign=pro-upgrade' ) . '
                    </div>
                </div>
            </div>';
		} elseif ( 'wp-analytify-forms' === $accordion['id'] ) {
			$accordion['is_active']  = false;
			$accordion['promo_text'] = '<div class="analytify-email-promo-contianer">
                    <div class="analytify-email-premium-overlay">
                        <div class="analytify-email-premium-popup">
                            <h3 class="analytify-promo-popup-heading">Unlock Forms Tracking Addon</h3>
                            <p class="analytify-promo-popup-paragraph">Would you like to track your WordPress website forms? Analytify Forms Tracking addon helps you to track the number of impressions and forms conversions/submissions. This Addon works with any popular WordPress form plugins like Gravity forms, Ninja Forms, Formidable forms and more, even including Custom WordPress forms.</p>
                            <ul class="analytify-promo-popup-list">
                                <li>Custom Forms Tracking</li>
                                <li>Track Gravity Forms</li>
                                <li>Track Contact Form 7</li>
                                <li>WPForms Tracking</li>
                                <li>Track Formidable Forms</li>
                                <li>Track submissions, impressions and conversions.</li>
                            </ul>
                            ' . activate_module_anchor( 'https://analytify.io/pricing?utm_source=analytify-lite&utm_medium=tracking-tab&utm_content=forms+tracking&utm_campaign=pro-upgrade', 'Explore Analytify Pro + Forms Tracking bundle', 'analytify-forms' ) . '
                        </div>
                    </div>
                </div>';
		} elseif ( 'analytify-google-ads-tracking' === $accordion['id'] ) {
			$accordion['is_active']  = false;
			$accordion['promo_text'] = '<div class="analytify-email-promo-contianer analytify-google-ads-container">
                    <div class="analytify-email-premium-overlay analytify-google-ads-overlay">
                        <div class="analytify-google-ads-popup">
                            <h3 class="analytify-promo-popup-heading">Unlock Google Ads Conversion Tracking.</h3>
                            <p class="analytify-promo-popup-paragraph">Track Your Woocommerce and EDD purchases as conversion in Google Ads using our Conversion Tracking addon.</p>
                            ' . activate_module_anchor( 'https://analytify.io/pricing?utm_source=analytify-lite&utm_medium=tracking-tab&utm_content=google-ads-tracking&utm_campaign=pro-upgrade' ) . '
                        </div>
                    </div>
                </div>';
		}
	}
	wp_analytify_tracking_accordion( $accordions, 'promo' );
}
add_action( 'wp_analytify_tracking_accordion_promo', 'wp_analytify_tracking_accordion_promo' );

/**
 * Display tracking accordion for pro users.
 *
 * @param mixed $accordions The accordions array to modify.
 * @return void
 */
function wp_analytify_tracking_accordion_pro( $accordions ) {
	$analytify_modules = get_option( 'wp_analytify_modules' );
	$analytify_modules = apply_filters( 'analytify_pro_modules', $analytify_modules );
	foreach ( $accordions as &$accordion ) {
		if ( 'wp-analytify-events-tracking' === $accordion['id'] ) {
			$accordion['is_active']  = ( 'active' === $analytify_modules['events-tracking']['status'] );
			$accordion['promo_text'] = '<div class="analytify-email-promo-contianer">
                <div class="analytify-email-premium-overlay">
                    <div class="analytify-email-premium-popup">
                    <h3 class="analytify-promo-popup-heading">Unlock Events Tracking</h3>
                        <p class="analytify-promo-popup-paragraph">Our Events tracking feature helps you to setup and track custom events on your WordPress website. Custom events will help you track and measure the performance of the most important Outbound links like Affiliate links. Setting up custom events is tricky for the beginners. But with the Analytify\'s Events Tracking you can easily acheive this on your WordPress websites.</p>
                        <ul class="analytify-promo-popup-list">
                            <li>Affiliate Tracking</li>
                            <li>Links & Clicks Tracking</li>
                            <li>Enhanced Link Attribution</li>
                            <li>Anchor Tracking</li>
                            <li>File downloads Tracking</li>
                            <li>Track outbound links</li>
                        </ul>
                        ' . activate_module_anchor( 'https://analytify.io/pricing?utm_source=analytify-pro&utm_medium=tracking-tab&utm_content=Events+Tracking&utm_campaign=pro-upgrade' ) . '
                    </div>
                </div>
            </div>';
		} elseif ( 'wp-analytify-custom-dimensions' === $accordion['id'] ) {
			$accordion['is_active']  = ( 'active' === $analytify_modules['custom-dimensions']['status'] );
			$accordion['promo_text'] = '<div class="analytify-email-promo-contianer">
                <div class="analytify-email-premium-overlay">
                    <div class="analytify-email-premium-popup">
                    <h3 class="analytify-promo-popup-heading">Unlock Google Custom Dimensions Tracking</h3>
                    <p class="analytify-promo-popup-paragraph">Custom Dimensions helps you to track Categories, Tags, Post Types, and logged in activities within Google Analytics. Remember, you have to setup Custom Dimensions in Google Analytics as well.</p>
                    <ul class="analytify-promo-popup-list">
                        <li>Custom Post Type Tracking</li>
                        <li>Category Tracking</li>
                        <li>Tags Tracking</li>
                        <li>Authors Tracking</li>
                        <li>User-ID Tracking</li>
                        <li>Track logged in activity</li>
                    </ul>
                    ' . activate_module_anchor( 'https://analytify.io/pricing?utm_source=analytify-pro&utm_medium=tracking-tab&utm_content=custom-dimensions&utm_campaign=pro-upgrade' ) . '
                    </div>
                </div>
            </div>';
		} elseif ( 'wp-analytify-forms' === $accordion['id'] ) {
			$accordion['is_active']  = class_exists( 'Analytify_Addon_Forms' ) || class_exists( 'Analytify_Forms' );
			$accordion['promo_text'] = '<div class="analytify-email-promo-contianer">
                <div class="analytify-email-premium-overlay">
                    <div class="analytify-email-premium-popup">
                    <h3 class="analytify-promo-popup-heading">Unlock Forms Tracking Addon</h3>
                    <p class="analytify-promo-popup-paragraph">Would you like to track your WordPress website forms? Analytify Forms Tracking addon helps you to track the number of impressions and forms conversions/submissions. This Addon works with any popular WordPress form plugins like Gravity forms, Ninja Forms, Formidable forms and more, even including Custom WordPress forms.</p>
                    <ul class="analytify-promo-popup-list">
                        <li>Custom Forms Tracking</li>
                        <li>Track Gravity Forms</li>
                        <li>Track Contact Form 7</li>
                        <li>WPForms Tracking</li>
                        <li>Track Formidable Forms</li>
                        <li>Track submissions, impressions and conversions.</li>
                    </ul>
                    ' . activate_module_anchor( 'https://analytify.io/pricing?utm_source=analytify-pro&utm_medium=tracking-tab&utm_content=forms+tracking&utm_campaign=pro-upgrade', 'Explore Analytify Pro + Forms Tracking bundle', 'analytify-forms' ) . '
                    </div>
                </div>
            </div>';
		} elseif ( 'analytify-google-ads-tracking' === $accordion['id'] ) {
			$accordion['is_active']  = isset( $analytify_modules['google-ads-tracking'] ) && 'active' === $analytify_modules['google-ads-tracking']['status'];
			$accordion['promo_text'] = '<div class="analytify-email-promo-contianer analytify-google-ads-container">
                <div class="analytify-google-ads-overlay">
                    <div class="analytify-google-ads-popup">
                        <h3 class="analytify-promo-popup-heading">Unlock Google Ads Conversion Tracking.</h3>
                        <p class="analytify-promo-popup-paragraph">Track Your Woocommerce and EDD purchases as conversion in Google Ads using our Conversion Tracking addon.</p>
                        ' . activate_module_anchor( 'https://analytify.io/pricing?utm_source=analytify-pro&utm_medium=tracking-tab&utm_content=google-ads-tracking&utm_campaign=pro-upgrade' ) . '
                    </div>
                </div>
            </div>';
		}
	}
	wp_analytify_tracking_accordion( $accordions, 'pro' );
}
add_action( 'wp_analytify_tracking_accordion_pro', 'wp_analytify_tracking_accordion_pro' );

/**
 * Display tracking accordion.
 *
 * @param mixed $accordions The accordions array to display.
 * @param mixed $type       The type of accordion (promo or pro).
 * @return void
 */
function wp_analytify_tracking_accordion( $accordions, $type = 'promo' ) {
	?>
	<div class="tracking-accordions-container">
		<div class="tracking-accordions-wrapper">
			<ul>
				<?php foreach ( $accordions as $accordion ) { ?>
					<li class="tracking-accordion event-tracking <?php echo esc_attr( $accordion['id'] ); ?>" data-id="<?php echo esc_attr( $accordion['id'] ); ?>">
						<div class="tracking-accordions-heading">
							<p><?php echo wp_kses_post( $accordion['title'] ); ?></p>
						</div>
						<div class="tracking-accordions-content">
							<?php
							if ( 'pro' === $type && $accordion['is_active'] ) {
								do_action( 'wp_analytify_tracking_accordion_options', $accordion['id'] );
							} else {
								echo wp_kses_post( $accordion['promo_text'] );
							}
							?>
						</div>
					</li>
				<?php } ?>
			</ul>
		</div>
	</div>
	<?php
}


