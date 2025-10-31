# reCAPTCHA Investigation Report - shop.3wdistributing.com
**Date**: October 31, 2025
**Investigator**: Claude Code

## Executive Summary

Google reCAPTCHA v3 is being loaded on every page of shop.3wdistributing.com by the **Contact Form 7** WordPress plugin (version 6.1.3). This is causing potential user experience issues as reCAPTCHA v3 runs invisible scoring in the background on all page loads, which can occasionally trigger challenges for legitimate users.

## Root Cause Identification

### Primary Cause
- **Plugin**: Contact Form 7 (version 6.1.3)
- **reCAPTCHA Version**: Google reCAPTCHA v3
- **Site Key**: `6LcNGNkrAAAAAA8pRUFXtj44RV90bt0gyXXq-Wxg`
- **Implementation**: Loaded globally on all pages, not just contact form pages

### Evidence
1. **Script Loading**:
   ```html
   <script src="https://www.google.com/recaptcha/api.js?render=6LcNGNkrAAAAAA8pRUFXtj44RV90bt0gyXXq-Wxg&ver=3.0"></script>
   <script src="https://shop.3wdistributing.com/wp-content/plugins/contact-form-7/modules/recaptcha/index.js?ver=6.1.3"></script>
   ```

2. **Configuration**:
   ```javascript
   var wpcf7_recaptcha = {
       "sitekey": "6LcNGNkrAAAAAA8pRUFXtj44RV90bt0gyXXq-Wxg",
       "actions": {
           "homepage": "homepage",
           "contactform": "contactform"
       }
   };
   ```

3. **Server Environment**:
   - Hosting: Hostinger (hpanel)
   - Web Server: LiteSpeed
   - Cache: LiteSpeed Cache (active)
   - PHP Version: 8.4.7

## Why This Is a Problem

1. **Performance Impact**: reCAPTCHA v3 loads JavaScript on every page, adding ~120KB of scripts
2. **User Experience**: Can occasionally trigger challenges for legitimate users, especially those with:
   - VPN connections
   - Privacy-focused browsers
   - Ad blockers or script blockers
   - Slow connections

3. **Privacy Concerns**: Google tracks all page visits for scoring purposes
4. **Unnecessary Coverage**: reCAPTCHA is loaded site-wide when only needed on contact forms

## Recommended Workarounds

### Option 1: Disable reCAPTCHA Completely (Quick Fix)
**Impact**: Removes reCAPTCHA but may increase spam on contact forms

1. Access WordPress Admin Dashboard
2. Navigate to: **Contact → Integration**
3. Remove the reCAPTCHA integration keys
4. Save changes

### Option 2: Load reCAPTCHA Only on Contact Pages (Recommended)
**Impact**: Maintains spam protection while improving site performance

This requires adding code to the theme's functions.php or creating a custom plugin:

```php
// Add to wp-content/themes/porto-child/functions.php or create a mu-plugin

add_action('wp_enqueue_scripts', function() {
    // Only load reCAPTCHA on pages with contact forms
    if (!is_page(array('contact', 'contact-us')) && !has_shortcode(get_post()->post_content, 'contact-form-7')) {
        wp_dequeue_script('google-recaptcha');
        wp_dequeue_script('wpcf7-recaptcha');

        // Remove the recaptcha actions
        add_filter('wpcf7_form_elements', function($form) {
            $form = preg_replace('/<input[^>]*name="g-recaptcha-response"[^>]*>/', '', $form);
            return $form;
        });
    }
}, 100);
```

### Option 3: Switch to Alternative Spam Protection
**Impact**: Better user experience with effective spam protection

Consider alternatives like:
1. **Honeypot fields** (invisible to users, catches bots)
2. **Simple math CAPTCHA** (2+2=?)
3. **Cloudflare Turnstile** (privacy-focused alternative)
4. **WPForms with built-in anti-spam** (no CAPTCHA needed)

### Option 4: Configure reCAPTCHA v3 Threshold
**Impact**: Reduce false positives while maintaining protection

Access Contact Form 7 settings and adjust the score threshold (default is 0.5):
- Lower threshold (0.3): More permissive, fewer challenges
- Higher threshold (0.7): More strict, more challenges

## Security Implications of Removal

### Risks
1. **Increased Spam**: Contact forms may receive more spam submissions
2. **Bot Attacks**: Automated form submissions could increase
3. **Resource Usage**: Processing spam can consume server resources

### Mitigations
1. **Implement Honeypot Fields**: Add hidden fields that bots fill but humans don't
2. **Use WordPress Anti-Spam Plugins**: Akismet is already installed and can help
3. **Add Form Validation**: Require specific formats for email, phone numbers
4. **Rate Limiting**: Limit form submissions per IP address
5. **Server-Side Filtering**: Add custom validation rules

## Recommended Action Plan

1. **Immediate**: Implement Option 2 (load reCAPTCHA only on contact pages)
2. **Short-term**: Monitor spam levels for 1 week
3. **Long-term**: If spam remains low, consider removing reCAPTCHA entirely
4. **Alternative**: If spam increases, implement honeypot fields or Cloudflare Turnstile

## Technical Notes

- Contact Form 7 plugin is not present in the local development environment
- Changes need to be made directly on the staging/production server
- The Porto theme (version 7.7.0) is being used with a child theme
- WooCommerce (10.3.3) is active but not causing the reCAPTCHA issue

## Monitoring Recommendations

After implementing changes:
1. Monitor form submission logs for spam increase
2. Check Google PageSpeed scores for improvement
3. Test with various browsers and VPN connections
4. Monitor user feedback about form accessibility