(function ($) {
    $(document).ready(function () {
        // Allowed slugs from server (this should be dynamically generated in PHP)
        const allowedSlugs = ['wp-analytify-edd', 'wp-analytify-authors',
            'wp-analytify-campaigns',
            'wp-analytify-woocommerce',
            'wp-analytify-goals/wp-analytify-goals.php',
            'wp-analytify-email/wp-analytify-email.php',
            'wp-analytify-forms/wp-analytify-forms.php',
            'analytify-analytics-dashboard-widget/wp-analytify-dashboard.php',
            'events-tracking',
            'custom-dimensions',
            'amp',
            'google-ads-tracking'
        ];

        function isValidSlug(slug) {
            return allowedSlugs.includes(slug); 
        }

        $(document).on('click', ".analytify-module-state", function (e) {
            e.preventDefault();

            var thisElement = $(this);
            var thisContainer = thisElement.parent().parent();
            var moduleSlug = $(this).attr('data-slug');
            var setState = $(this).attr('data-set-state');
            var internalModule = $(this).attr('data-internal-module');

            // Security check: Ensure slug is valid
            if (!isValidSlug(moduleSlug)) {
                console.log("Invalid addon selected!"); 
                return;
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
                    thisContainer.find('.wp-analytify-addon-enable').show();
                    thisContainer.find('.wp-analytify-addon-wrong').hide();
                },
                error: function () {
                    thisContainer.find('.wp-analytify-addon-enable').hide();
                    thisContainer.find('.wp-analytify-addon-wrong').show();
                },
                success: function (res) {
                    thisContainer.find('.wp-analytify-addon-enable').hide();
                    if (res === 'failed') {
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
                    thisElement.parent().html('<button type="button" class="button-primary analytify-module-state analytify-active-module" data-internal-module="' + internalModule + '" data-slug="' + moduleSlug + '" data-set-state="active">Activate add-on</button>');
                }

                setTimeout(function () {
                    thisContainer.find('.wp-analytify-addon-install, .wp-analytify-addon-uninstall').hide();
                }, 1800);
            });
        });

        // Ajax request to activate/deactivate the addon
        $(document).on('click', ".analytify-addon-state", function (e) {
            e.preventDefault();

            const thisElement = $(this);
            const thisContainer = thisElement.parent().parent();
            const addonSlug = $(this).attr('data-slug');
            const setState = $(this).attr('data-set-state');

            // Security check: Ensure slug is valid
            if (!isValidSlug(addonSlug)) {
                console.log("Invalid addon selected!"); 
                return;
            }

            $.ajax({
                url: analytify_addons.ajaxurl,
                type: 'POST',
                data: {
                    action: 'set_addon_state',
                    nonce: analytify_addons.nonce,
                    addon_slug: addonSlug,
                    set_state: setState,
                },
                beforeSend: function () {
            
                    // Hide all loaders first
                    thisContainer.find('.wp-analytify-addon-enable, .wp-analytify-addon-uninstalling, .wp-analytify-addon-wrong').hide();
            
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
            
                    if (res === 'failed') {
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
                    thisElement.parent().html('<button type="button" class="button-primary analytify-addon-state analytify-deactivate-addon" data-slug="' + addonSlug + '" data-set-state="deactive">Deactivate add-on</button>');
                } else if (setState === 'deactive') {
                    thisElement.parent().html('<button type="button" class="button-primary analytify-addon-state analytify-active-addon" data-slug="' + addonSlug + '" data-set-state="active">Activate add-on</button>');
                }
            
                // Hide success messages after 1.8 seconds
                setTimeout(function () {
                    thisContainer.find('.wp-analytify-addon-install, .wp-analytify-addon-uninstall').hide();
                }, 1800);
            });                      
        });
    });

})(jQuery);