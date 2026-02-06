(function ($) {
	$(document).ready(function () {
		// Ensure analytify_addons object exists with fallback values
		if (typeof analytify_addons === 'undefined') {
			// Get ajaxurl from WordPress global (available in admin)
			var fallbackAjaxurl = (typeof ajaxurl !== 'undefined') ? ajaxurl : '/wp-admin/admin-ajax.php';
			window.analytify_addons = {
				ajaxurl: fallbackAjaxurl,
				nonce: '',
				allowed_slugs: []
			};
		}

		// Ensure ajaxurl and nonce have values (in case object exists but properties are missing)
		if (! analytify_addons.ajaxurl) {
			analytify_addons.ajaxurl = (typeof ajaxurl !== 'undefined') ? ajaxurl : '/wp-admin/admin-ajax.php';
		}
		if (! analytify_addons.nonce) {
			analytify_addons.nonce = '';
		}

		// Allowed slugs from server (dynamically generated in PHP)
		const allowedSlugs = (typeof analytify_addons !== 'undefined' && Array.isArray(analytify_addons.allowed_slugs) && analytify_addons.allowed_slugs.length > 0)
			? analytify_addons.allowed_slugs
			: ['wp-analytify-edd', 'wp-analytify-authors',
				'wp-analytify-campaigns',
				'wp-analytify-woocommerce',
				'wp-analytify-goals',
				'wp-analytify-email',
				'wp-analytify-forms',
				'analytify-analytics-dashboard-widget/wp-analytify-dashboard.php',
				'events-tracking',
				'custom-dimensions',
				'amp',
				'google-ads-tracking',
				'wp-analytify-forms/wp-analytify-forms.php',
				'wp-analytify-authors/wp-analytify-authors.php',
				'wp-analytify-campaigns/wp-analytify-campaigns.php',
				'wp-analytify-woocommerce/wp-analytify-woocommerce.php',
				'wp-analytify-goals/wp-analytify-goals.php',
				'wp-analytify-email/wp-analytify-email.php',
				'wp-analytify-edd/wp-analytify-edd.php'
			];

		function isValidSlug(slug) {
			if (! slug) {
				return false;
			}
			return allowedSlugs.includes(slug);
		}

		// Ensure all loader states are hidden on page load
		$('.wp-analytify-addon-enable, .wp-analytify-addon-install, .wp-analytify-addon-uninstalling, .wp-analytify-addon-uninstall, .wp-analytify-addon-wrong').hide();

		$(document).on('click', '.analytify-module-state', function (e) {
			e.preventDefault();

			var thisElement = $(this);
			var thisContainer = thisElement.parent().parent();
			var moduleSlug = $(this).attr('data-slug');
			var setState = $(this).attr('data-set-state');
			var internalModule = $(this).attr('data-internal-module');

			// Security check: Ensure slug is valid
			// For modules and external plugins, allow if slug exists (server will validate)
			if (! moduleSlug || moduleSlug.trim() === '') {
				return;
			}

			// Don't block if slug validation fails - server will handle security
			if (! isValidSlug(moduleSlug)) {
				// Continue anyway - server-side validation will catch invalid slugs
			}

			$.ajax({
				url: analytify_addons.ajaxurl,
				type: 'POST',
				data: {
					action: 'set_module_state',
					nonce: analytify_addons.nonce,
					module_slug: moduleSlug,
					set_state: setState,
					internal_module: internalModule
				},
				beforeSend: function () {
					// Hide all loaders first
					thisContainer.find('.wp-analytify-addon-enable, .wp-analytify-addon-uninstalling, .wp-analytify-addon-wrong, .wp-analytify-addon-install, .wp-analytify-addon-uninstall').hide();

					// Show correct loader based on action
					if (setState === 'active') {
						thisContainer.find('.wp-analytify-addon-enable').show();
					} else if (setState === 'deactive') {
						thisContainer.find('.wp-analytify-addon-uninstalling').show();
					}
				},
				error: function () {
					thisContainer.find('.wp-analytify-addon-enable, .wp-analytify-addon-uninstalling').hide();
					thisContainer.find('.wp-analytify-addon-wrong').show();
				},
				success: function (res) {
					thisContainer.find('.wp-analytify-addon-enable, .wp-analytify-addon-uninstalling').hide();

					if (res === 'failed' || res === 'Failed' || (typeof res === 'string' && res.toLowerCase().includes('error'))) {
						thisContainer.find('.wp-analytify-addon-wrong').show();
					} else {
						if (setState === 'active') {
							thisContainer.find('.wp-analytify-addon-install').show();
						} else {
							thisContainer.find('.wp-analytify-addon-uninstall').show();
						}
					}
				}
			}).done(function () {
				if (setState === 'active') {
					thisElement.parent().html('<button type="button" class="button-primary analytify-module-state analytify-deactivate-module" data-internal-module="' + internalModule + '" data-slug="' + moduleSlug + '" data-set-state="deactive">Deactivate add-on</button>');
				} else {
					thisElement.parent().html('<button type="button" class="button-primary analytify-module-state analytify-activate-module" data-internal-module="' + internalModule + '" data-slug="' + moduleSlug + '" data-set-state="active">Activate add-on</button>');
				}

				setTimeout(function () {
					thisContainer.find('.wp-analytify-addon-install, .wp-analytify-addon-uninstall').hide();
				}, 1800);
			});
		});

		// Ajax request to activate/deactivate the addon
		$(document).on('click', '.analytify-addon-state', function (e) {
			e.preventDefault();

			const thisElement = $(this);
			const thisContainer = thisElement.parent().parent();
			const addonSlug = $(this).attr('data-slug');
			const setState = $(this).attr('data-set-state');

			// Security check: Ensure slug is valid
			// Note: For Pro addons, we allow the slug if it's provided (non-empty)
			// The server-side validation will handle security
			if (! addonSlug || addonSlug.trim() === '') {
				return;
			}

			// For Pro addons, if slug validation fails but slug exists, continue
			// The server will validate it properly
			if (! isValidSlug(addonSlug)) {
				// Don't return - let server validate for Pro addons
			}

			$.ajax({
				url: analytify_addons.ajaxurl,
				type: 'POST',
				data: {
					action: 'set_addon_state',
					nonce: analytify_addons.nonce,
					addon_slug: addonSlug,
					set_state: setState
				},
				beforeSend: function () {
					// Hide all loaders first
					thisContainer.find('.wp-analytify-addon-enable, .wp-analytify-addon-uninstalling, .wp-analytify-addon-wrong, .wp-analytify-addon-install, .wp-analytify-addon-uninstall').hide();

					// Show correct loader based on action
					if (setState === 'active') {
						thisContainer.find('.wp-analytify-addon-enable').show(); // Show "Activating..."
					} else if (setState === 'deactive') {
						thisContainer.find('.wp-analytify-addon-uninstalling').show(); // Show "Deactivating..."
					}
				},
				error: function () {
					thisContainer.find('.wp-analytify-addon-enable, .wp-analytify-addon-uninstalling').hide();
					thisContainer.find('.wp-analytify-addon-wrong').show();
				},
				success: function (res) {
					// Hide all loaders
					thisContainer.find('.wp-analytify-addon-enable, .wp-analytify-addon-uninstalling').hide();

					if (res === 'failed' || (typeof res === 'string' && res.toLowerCase().includes('error'))) {
						thisContainer.find('.wp-analytify-addon-wrong').show();
					} else {
						if (setState === 'active') {
							thisContainer.find('.wp-analytify-addon-install').show(); // Show "Activated"
						} else if (setState === 'deactive') {
							thisContainer.find('.wp-analytify-addon-uninstall').show(); // Show "Deactivated"
						}
					}
				}
			}).done(function () {
				if (setState === 'active') {
					thisElement.parent().html('<button type="button" class="button-primary analytify-addon-state analytify-deactivate-addon" data-slug="' + addonSlug + '" data-set-state="deactive" data-nonce="' + (thisElement.attr('data-nonce') || '') + '">Deactivate add-on</button>');
				} else if (setState === 'deactive') {
					thisElement.parent().html('<button type="button" class="button-primary analytify-addon-state analytify-activate-addon" data-slug="' + addonSlug + '" data-set-state="active" data-nonce="' + (thisElement.attr('data-nonce') || '') + '">Activate add-on</button>');
				}

				// Hide success messages after 1.8 seconds
				setTimeout(function () {
					thisContainer.find('.wp-analytify-addon-install, .wp-analytify-addon-uninstall').hide();
				}, 1800);
			});
		});
	});

})(jQuery);
