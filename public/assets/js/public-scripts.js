(function($) {
    'use strict';

    $(function() { 
        if (typeof iaamPublicData === 'undefined') {
            // console.warn('IAAM Public Data not found. Deep linking script will not run.');
            return;
        }

        function getOS() {
            var userAgent = navigator.userAgent || navigator.vendor || window.opera;
            if (/android/i.test(userAgent)) { return "android"; }
            if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) { return "ios"; }
            return "desktop"; 
        }

        var currentOS = getOS();
        var appScheme = iaamPublicData.appScheme; 
        var fallbackTimeout = parseInt(iaamPublicData.fallbackTimeout, 10) || 2500;
        
        var universalRedirectUrl = iaamPublicData.redirectUrls.desktop; // ڈیفالٹ ری ڈائریکٹ URL (ڈیسک ٹاپ والا)
        var storeUrl = ''; // یہ اب صرف اس صورت میں استعمال ہوگا اگر universalRedirectUrl خالی ہو

        // console.log('IAAM Public Data:', iaamPublicData);
        // console.log('Current OS:', currentOS);
        // console.log('App Scheme to try:', appScheme);
        // console.log('Universal Redirect URL for non-app users:', universalRedirectUrl);

        // 1. Handle Desktop Redirect (Entire Site)
        if (currentOS === "desktop" && iaamPublicData.deepLinkEnabled.desktop) {
            if (universalRedirectUrl) {
                // console.log('Desktop user & desktop deep linking enabled. Redirecting to:', universalRedirectUrl);
                window.location.replace(universalRedirectUrl);
            } else {
                // console.warn('Desktop deep linking enabled, but no Desktop Redirect URL is set.');
            }
            return; 
        }

        // 2. Handle Mobile Deep Linking / Redirect (Entire Site)
        var attemptDeepLinkOrRedirectOnMobile = false;

        if (currentOS === "android" && iaamPublicData.deepLinkEnabled.android) {
            storeUrl = iaamPublicData.redirectUrls.playStore; // اسٹور URL اب بھی فال بیک کے لیے رکھ لیں
            if (/huawei/i.test(navigator.userAgent) && iaamPublicData.redirectUrls.appGallery) {
                storeUrl = iaamPublicData.redirectUrls.appGallery;
            }
            attemptDeepLinkOrRedirectOnMobile = true;
        } else if (currentOS === "ios" && iaamPublicData.deepLinkEnabled.ios) {
            storeUrl = iaamPublicData.redirectUrls.appStore; // اسٹور URL اب بھی فال بیک کے لیے رکھ لیں
            attemptDeepLinkOrRedirectOnMobile = true;
        }

        if (attemptDeepLinkOrRedirectOnMobile && appScheme) {
            // console.log('Mobile user. Attempting to open app with scheme:', appScheme);
            
            var appOpened = false;
            var timeoutHandle;

            function onPageVisibilityChange() {
                if (document.hidden || document.webkitHidden || document.msHiddenPageVisibility) {
                    appOpened = true;
                    clearTimeout(timeoutHandle); 
                    removeVisibilityListeners();
                }
            }
            
            function onBlur() {
                appOpened = true;
                clearTimeout(timeoutHandle);
                removeVisibilityListeners(); 
                window.removeEventListener('blur', onBlur);
            }

            function addVisibilityListeners() {
                document.addEventListener("visibilitychange", onPageVisibilityChange);
                document.addEventListener("webkitvisibilitychange", onPageVisibilityChange);
            }
            function removeVisibilityListeners() {
                document.removeEventListener("visibilitychange", onPageVisibilityChange);
                document.removeEventListener("webkitvisibilitychange", onPageVisibilityChange);
            }
            
            addVisibilityListeners();
            window.addEventListener('blur', onBlur);

            timeoutHandle = setTimeout(function() {
                removeVisibilityListeners();
                window.removeEventListener('blur', onBlur);

                if (!appOpened) {
                    // اگر ایپ نہیں کھلی، تو universalRedirectUrl پر بھیجیں
                    if (universalRedirectUrl) {
                        // console.log('Timeout reached, app did not open. Redirecting to universal redirect URL:', universalRedirectUrl);
                        window.location.href = universalRedirectUrl;
                    } else if (storeUrl) { // اگر universalRedirectUrl سیٹ نہیں ہے، تو پرانے طریقے سے اسٹور پر بھیجیں
                        // console.log('Timeout reached, app did not open, universal redirect URL not set. Redirecting to store:', storeUrl);
                        window.location.href = storeUrl;
                    } else {
                        // console.warn('App not opened, and no redirect URL or store URL found.');
                    }
                } else {
                    // console.log('App likely opened, fallback redirect cancelled.');
                }
            }, fallbackTimeout);

            // ایپ کھولنے کی کوشش
            var iframe = document.createElement("iframe");
            iframe.style.cssText = "border:none;width:1px;height:1px;position:absolute;top:-9999px;left:-9999px;";
            iframe.src = appScheme; 
            document.body.appendChild(iframe);

        } else if (attemptDeepLinkOrRedirectOnMobile && universalRedirectUrl) {
            // اگر ایپ اسکیم نہیں ہے (مثلاً ایپ انسٹال نہیں) لیکن موبائل ری ڈائریکشن فعال ہے اور URL موجود ہے
            // تو براہ راست universalRedirectUrl پر بھیج دیں (یہ صورتحال کم آئے گی اگر appScheme ہمیشہ موجود ہو)
            // console.log('Mobile user, deep linking enabled, but no app scheme. Redirecting to universal URL:', universalRedirectUrl);
            // window.location.href = universalRedirectUrl;
            // نوٹ: اوپر والا if بلاک (attemptDeepLinkOnMobile && appScheme && storeUrl) زیادہ تر موبائل کیسز کو ہینڈل کرے گا۔
            // یہ else if بلاک ایک اضافی فال بیک ہو سکتا ہے اگر appScheme خالی ہو لیکن آپ پھر بھی ری ڈائریکٹ کرنا چاہتے ہیں۔
        } else {
            // console.log('Deep linking/redirect not attempted for this mobile OS or required settings/URLs are missing.');
        }
    });

})(jQuery);
