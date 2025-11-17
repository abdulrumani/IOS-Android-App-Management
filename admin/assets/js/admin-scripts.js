jQuery(document).ready(function($) {
    'use strict';
    // console.log('IAAM Admin Scripts Loaded (v8)');

    function getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    }

    function showActivePaneForGroup($linksContainer, $panesContainer, activeKey, linkDataAttr, paneDataAttr) {
        var $allLinks = $linksContainer.find('a.nav-tab');
        var $allPanes = $panesContainer.children(); 

        $allLinks.removeClass('nav-tab-active');
        $allPanes.removeClass('active-pane').hide();

        var $activeLink = $allLinks.filter('[' + linkDataAttr + '="' + activeKey + '"]');
        var $activePane = $allPanes.filter('[' + paneDataAttr + '="' + activeKey + '"]');

        if ($activeLink.length) {
            $activeLink.addClass('nav-tab-active');
        }
        if ($activePane.length) {
            $activePane.addClass('active-pane').show();
        } else {
            var fallbackPaneId = '';
            if ($linksContainer.parent().hasClass('iaam-admin-wrap')) { // Main tabs
                fallbackPaneId = '#iaam-main-content-' + activeKey.replace(/_/g, '-');
            } else if ($linksContainer.hasClass('ads-management-sub-tabs')) {
                fallbackPaneId = '#iaam-sub-content-ads-management-' + activeKey.replace(/_/g, '-');
            } else if ($linksContainer.hasClass('settings-sub-tabs')) {
                 fallbackPaneId = '#iaam-sub-content-settings-' + activeKey.replace(/_/g, '-');
            } else if ($linksContainer.hasClass('ads-settings-sub-tabs')) { // Sub-sub tabs for Ads Settings
                 fallbackPaneId = '#iaam-ads-settings-pane-' + activeKey.replace(/_/g, '-');
            } else if ($linksContainer.hasClass('iaam-vertical-tabs-nav')) { // Vertical tabs (Ad Networks)
                var platformPrefix = '';
                if ($linksContainer.closest('.android-vertical-tabs').length) platformPrefix = 'android-';
                else if ($linksContainer.closest('.ios-vertical-tabs').length) platformPrefix = 'ios-';
                fallbackPaneId = '#vertical-pane-' + platformPrefix + activeKey.replace(/_/g, '-');
            }

            if (fallbackPaneId && $(fallbackPaneId).length) {
                $(fallbackPaneId).addClass('active-pane').show();
            } else {
                // console.warn('Pane not found for key (via data or fallback ID):', activeKey, 'in container:', $panesContainer.attr('id'));
            }
        }
    }

    function initializeTabGroup(config) {
        var $linksContainer = $(config.linksContainerSelector);
        var $panesContainer = $(config.panesContainerSelector);
        if (!$linksContainer.length || !$panesContainer.length) return;

        var activeKey = getUrlParameter(config.urlParamName) || config.defaultKey;

        $linksContainer.find('a.nav-tab').each(function() {
            var key = new URLSearchParams($(this).attr('href').split('?')[1]).get(config.urlParamName);
            if (key) $(this).attr('data-tab-key', key);
        });
        $panesContainer.children().each(function() {
            var paneId = $(this).attr('id');
            var keyFromId = '';
            if (paneId && config.paneIdToKeyFn) {
                keyFromId = config.paneIdToKeyFn(paneId);
            } else if (paneId && config.paneIdPrefix) { // Fallback if paneIdToKeyFn not provided
                 if (paneId.startsWith(config.paneIdPrefix)) {
                    keyFromId = paneId.substring(config.paneIdPrefix.length).replace(/-/g, '_');
                 }
            }
             // For vertical tabs, data-pane-key is set in HTML. For others, we derive it.
            if (!$(this).attr('data-pane-key') && keyFromId) {
                 $(this).attr('data-pane-key', keyFromId);
            }
        });

        showActivePaneForGroup($linksContainer, $panesContainer, activeKey, 'data-tab-key', 'data-pane-key');

        if (config.childGroupInitFunction) {
            config.childGroupInitFunction(activeKey);
        }

        $linksContainer.off('click.iaamTabs').on('click.iaamTabs', 'a.nav-tab', function(e) {
            e.preventDefault();
            var clickedKey = $(this).data('tab-key');
            var newUrl = $(this).attr('href');

            showActivePaneForGroup($linksContainer, $panesContainer, clickedKey, 'data-tab-key', 'data-pane-key');
            window.history.pushState({ path: newUrl }, '', newUrl);

            if (config.childGroupInitFunction) {
                config.childGroupInitFunction(clickedKey);
            }
            updateHiddenFieldsFromUrl(newUrl);
        });
    }

    // --- Main Tabs ---
    initializeTabGroup({
        linksContainerSelector: '.iaam-admin-wrap > h2.nav-tab-wrapper',
        panesContainerSelector: '.iaam-main-tab-content-wrapper',
        urlParamName: 'tab',
        defaultKey: 'ads_management',
        paneIdToKeyFn: function(paneId) { return paneId.startsWith('iaam-main-content-') ? paneId.substring('iaam-main-content-'.length).replace(/-/g, '_') : null; },
        childGroupInitFunction: handleMainTabActivation
    });

    function handleMainTabActivation(mainTabKey) {
        if (mainTabKey === 'ads_management') {
            initializeTabGroup({
                linksContainerSelector: '#iaam-main-content-ads-management .ads-management-sub-tabs',
                panesContainerSelector: '#iaam-main-content-ads-management .iaam-sub-tab-content-wrapper',
                urlParamName: 'sub_tab',
                defaultKey: 'android',
                paneIdToKeyFn: function(paneId) { return paneId.startsWith('iaam-sub-content-ads-management-') ? paneId.substring('iaam-sub-content-ads-management-'.length).replace(/-/g, '_') : null; },
                childGroupInitFunction: handleAdsSubTabActivation
            });
        } else if (mainTabKey === 'settings') {
            initializeTabGroup({
                linksContainerSelector: '#iaam-main-content-settings .settings-sub-tabs',
                panesContainerSelector: '#iaam-main-content-settings .iaam-sub-tab-content-wrapper',
                urlParamName: 'sub_tab',
                defaultKey: 'firebase_analytics',
                paneIdToKeyFn: function(paneId) { return paneId.startsWith('iaam-sub-content-settings-') ? paneId.substring('iaam-sub-content-settings-'.length).replace(/-/g, '_') : null; }
            });
        }
    }

    function handleAdsSubTabActivation(adsSubTabKey) {
        var platform = (adsSubTabKey === 'android' || adsSubTabKey === 'ios') ? adsSubTabKey : null;
        if (platform) {
            // Vertical tabs: data-tab-key and data-pane-key are set in HTML view file
            initializeTabGroup({
                linksContainerSelector: '#iaam-sub-content-ads-management-' + platform + ' .iaam-vertical-tabs-nav',
                panesContainerSelector: '#iaam-sub-content-ads-management-' + platform + ' .iaam-vertical-tab-content-wrapper',
                urlParamName: 'ad_type_tab',
                defaultKey: 'admob'
                // No paneIdToKeyFn, relies on data-pane-key="[network_key]" from HTML
            });
        } else if (adsSubTabKey === 'ads_settings') {
            initializeTabGroup({
                linksContainerSelector: '#iaam-sub-content-ads-management-ads-settings .ads-settings-sub-tabs',
                panesContainerSelector: '#iaam-sub-content-ads-management-ads-settings .iaam-ads-settings-content-wrapper',
                urlParamName: 'ads_set_sub_tab',
                defaultKey: 'global_ads_control',
                paneIdToKeyFn: function(paneId) { return paneId.startsWith('iaam-ads-settings-pane-') ? paneId.substring('iaam-ads-settings-pane-'.length).replace(/-/g, '_') : null; }
            });
        }
    }
    
    handleMainTabActivation(getUrlParameter('tab') || 'ads_management');

    function updateHiddenFieldsFromUrl(urlString) {
        var currentUrl = new URL(urlString, window.location.origin);
        var params = currentUrl.searchParams;
        $('form input[name="iaam_active_tab"]').val(params.get("tab") || 'ads_management');
        $('form input[name="iaam_active_sub_tab"]').val(params.get("sub_tab") || '');
        $('form input[name="iaam_active_platform_ad_type_tab"]').val(params.get("ad_type_tab") || '');
        $('form input[name="iaam_active_ads_settings_sub_tab"]').val(params.get("ads_set_sub_tab") || '');
    }
    updateHiddenFieldsFromUrl(window.location.href);
    $('form[action="admin-post.php"]').on('submit', function() {
        updateHiddenFieldsFromUrl(window.location.href);
    });

    $('.iaam-rest-api-table').on('click', '.copy-url-button', function(e) {
        e.preventDefault();
        var urlToCopy = $(this).data('url');
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val(urlToCopy).select();
        try {
            document.execCommand("copy");
            $(this).text($(this).data('copied-text') || 'Copied!');
            var originalText = $(this).data('original-text') || 'Copy URL';
            var $button = $(this);
            setTimeout(function() { $button.text(originalText); }, 2000);
        } catch (err) { alert('Oops, unable to copy. Please copy it manually.'); }
        $temp.remove();
    });
    $('.copy-url-button').each(function(){ $(this).data('original-text', $(this).text()); });
    
    // --- Send Notification Button on Post List ---
    if (typeof iaamPostList !== 'undefined') {
        $('.iaam-send-notification-btn').on('click', function(e) {
            e.preventDefault();

            var $button = $(this);
            var postId = $button.data('postid');
            var nonce = $button.data('nonce');
            var $spinner = $('#iaam-send-spinner-' + postId);
            var $status = $('#iaam-send-status-' + postId);

            // FCM Key کی موجودگی چیک کریں (JS کی طرف سے)
            if (!iaamPostList.fcm_key_is_set) {
                alert(iaamPostList.fcm_key_missing_message); // ایک سادہ الرٹ
                // یا آپ $status میں پیغام دکھا سکتے ہیں
                // $status.text(iaamPostList.fcm_key_missing_message).css('color', 'red');
                // setTimeout(function() { $status.text(''); }, 5000);
                return; // اگر FCM Key نہیں تو آگے نہ بڑھیں
            }

            if (!confirm(iaamPostList.confirm_message)) {
                return;
            }

            $spinner.css('visibility', 'visible').addClass('is-active'); // اسپنر کو دکھائیں
            $status.text(iaamPostList.sending_message).css('color', 'orange');
            $button.attr('disabled', 'disabled');

            $.ajax({
                url: iaamPostList.ajax_url,
                type: 'POST',
                data: {
                    action: 'iaam_send_post_notification',
                    post_id: postId,
                    nonce: nonce,
                },
                success: function(response) {
                    $spinner.removeClass('is-active').css('visibility', 'hidden');
                    $button.removeAttr('disabled');
                    if (response.success) {
                        $status.text(response.data.message || iaamPostList.success_message).css('color', 'green');
                    } else {
                        $status.text(response.data.message || iaamPostList.error_message).css('color', 'red');
                    }
                    setTimeout(function() { $status.text(''); }, 7000); // 7 سیکنڈ بعد اسٹیٹس صاف کریں
                },
                error: function(xhr, status, error) {
                    $spinner.removeClass('is-active').css('visibility', 'hidden');
                    $button.removeAttr('disabled');
                    $status.text(iaamPostList.error_message + ' (AJAX Error)').css('color', 'red');
                    // console.error('IAAM Send Notification AJAX Error:', status, error, xhr.responseText);
                    setTimeout(function() { $status.text(''); }, 7000);
                }
            });
        });
    }
    
    // ... (باقی تمام ٹیبز والا کوڈ اور کاپی URL والا کوڈ ویسا ہی رہے گا) ...
});