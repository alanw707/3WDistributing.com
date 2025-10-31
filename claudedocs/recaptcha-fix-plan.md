# reCAPTCHA Fix Plan for shop.3wdistributing.com

**Date**: 2025-10-31
**Issue**: Google reCAPTCHA v3 loading on all pages causing poor UX
**Status**: Investigation Complete - Ready for Implementation

---

## Investigation Summary

### Root Cause
- **Plugin**: Contact Form 7 (v6.1.3) with Google reCAPTCHA v3
- **Problem**: reCAPTCHA loads globally on ALL pages, not just contact forms
- **Impact**:
  - 120KB extra JavaScript per page
  - Google tracking all visitors
  - Potential false positives for VPN/privacy users
  - Performance degradation on shop pages

### Evidence
- Site Key: `6LcNGNkrAAAAAA8pRUFXtj44RV90bt0gyXXq-Wxg`
- Scripts loaded: `google-recaptcha`, `wpcf7-recaptcha`
- Full investigation: `claudedocs/recaptcha-investigation-2025-10-31.md`

---

## Implementation Options

### Option 1: Conditional Loading (RECOMMENDED)
**Approach**: Load reCAPTCHA only on pages with contact forms

**Implementation**:
```php
// Add to wp-content/themes/porto-child/functions.php

add_action('wp_enqueue_scripts', function() {
    // Only load reCAPTCHA on pages with contact forms
    if (!is_page(array('contact', 'contact-us')) &&
        !has_shortcode(get_post()->post_content, 'contact-form-7')) {
        wp_dequeue_script('google-recaptcha');
        wp_dequeue_script('wpcf7-recaptcha');
    }
}, 100);
```

**Pros**:
- ✅ Keeps contact form protected
- ✅ Removes from shop/product pages
- ✅ Improves performance
- ✅ Better UX

**Cons**:
- Requires code modification
- Need to maintain on theme updates

---

### Option 2: Complete Removal
**Approach**: Remove reCAPTCHA entirely from Contact Form 7

**Implementation**:
1. WordPress Admin → Contact → Integration
2. Remove reCAPTCHA site key and secret key
3. Save changes

**Pros**:
- ✅ No code changes needed
- ✅ Simplest solution
- ✅ Maximum performance improvement

**Cons**:
- ⚠️ Slightly higher spam risk
- Relies on Akismet only (already installed)

---

### Option 3: Alternative Protection
**Approach**: Replace with better spam protection

**Options**:
1. **Cloudflare Turnstile**: Privacy-focused, invisible, better UX
2. **Honeypot Fields**: Invisible to users, catches bots
3. **Really Simple CAPTCHA**: Local solution, no Google tracking

**Pros**:
- ✅ Modern alternatives
- ✅ Better privacy
- ✅ Potentially better UX

**Cons**:
- Requires plugin changes
- May need configuration

---

## Implementation Task Plan

1. **Review & Decide**
   - Review full investigation report
   - Choose implementation approach
   - Consider security vs UX trade-offs

2. **Backup**
   - Backup `wp-content/themes/porto-child/functions.php`
   - Create restore point

3. **Implement**
   - Add chosen fix to functions.php OR
   - Remove reCAPTCHA keys from Contact Form 7 OR
   - Install alternative protection plugin

4. **Test**
   - Verify reCAPTCHA removed from shop pages
   - Verify contact form still protected
   - Test with different browsers/scenarios
   - Check for JavaScript errors

5. **Monitor**
   - Page load performance improvements
   - Spam submission rates (if removed)
   - User experience feedback

---

## Testing Checklist

### Shop Pages (Should NOT show reCAPTCHA)
- [ ] Home page (https://shop.3wdistributing.com)
- [ ] Product listing pages
- [ ] Individual product pages
- [ ] Cart page
- [ ] Checkout page

### Contact Pages (Should show reCAPTCHA if Option 1)
- [ ] Contact form page
- [ ] Any other pages with Contact Form 7 shortcode

### Performance
- [ ] Check page load time before fix
- [ ] Check page load time after fix
- [ ] Verify JavaScript console has no errors
- [ ] Check Network tab for reCAPTCHA requests

---

## Rollback Plan

If issues occur:
1. Restore backup of functions.php OR
2. Re-add reCAPTCHA keys to Contact Form 7
3. Clear all WordPress caches
4. Test again

---

## Security Considerations

### With Conditional Loading (Option 1)
- Contact forms remain fully protected
- Shop pages unaffected
- No security degradation

### With Complete Removal (Option 2)
- Akismet still active for spam filtering
- Slight increase in spam risk (~5-10%)
- Consider monitoring submission logs

### With Alternative (Option 3)
- Security level depends on chosen alternative
- Cloudflare Turnstile: Similar to reCAPTCHA
- Honeypot: Good for basic bot protection

---

## Estimated Impact

### Performance Improvement
- **Page Load**: -120KB JavaScript (~0.5-1s faster on 3G)
- **Requests**: -2 external requests to Google
- **Privacy**: No Google tracking on shop pages

### User Experience
- **Friction**: Reduced false positives
- **Privacy**: Better for privacy-conscious users
- **Speed**: Faster page loads

---

## Next Steps

1. Review this plan with stakeholders
2. Decide on implementation approach
3. Schedule implementation window
4. Execute implementation
5. Test thoroughly
6. Monitor results

---

## Related Files
- Investigation Report: `claudedocs/recaptcha-investigation-2025-10-31.md`
- Theme Functions: `wp-content/themes/porto-child/functions.php`
- Contact Form Plugin: `wp-content/plugins/contact-form-7/`
