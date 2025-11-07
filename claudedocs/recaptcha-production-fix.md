# reCAPTCHA Production Fix - About Page Contact Form

**Date**: 2025-11-06
**Issue**: reCAPTCHA not loading on production about page
**Status**: Solution Ready

---

## Root Cause

The reCAPTCHA script is not loading on production because the site key is not being retrieved properly. The `threew_get_recaptcha_site_key()` function checks multiple sources in order:

1. `THREEW_RECAPTCHA_SITE_KEY` constant (defined in code)
2. Environment variable via `threew_get_env_value()`
3. WordPress option `threew_recaptcha_site_key`

**Problem**: Production environment is not setting the environment variable or WordPress option.

---

## Solution Options

### Option 1: Use Hardcoded Constant (QUICKEST - RECOMMENDED)

**Current Implementation** (`functions.php:14-16`):
```php
if (!defined('THREEW_RECAPTCHA_SITE_KEY')) {
    define('THREEW_RECAPTCHA_SITE_KEY', 'YOUR_RECAPTCHA_SITE_KEY');
}

if (!defined('THREEW_RECAPTCHA_SECRET_KEY')) {
    define('THREEW_RECAPTCHA_SECRET_KEY', 'YOUR_RECAPTCHA_SECRET_KEY');
}
```

**Issue**: The constant definition is wrapped in `if (!defined())` which means it's NOT being set.

**Fix**: Remove the conditional check to force the constant:

```php
// Production reCAPTCHA keys - Always define
define('THREEW_RECAPTCHA_SITE_KEY', 'YOUR_RECAPTCHA_SITE_KEY');
define('THREEW_RECAPTCHA_SECRET_KEY', 'YOUR_RECAPTCHA_SECRET_KEY');
```

**Pros**:
- ✅ Immediate fix, no server configuration needed
- ✅ Works across all environments
- ✅ Simple and reliable

**Cons**:
- ⚠️ Keys visible in code (acceptable for reCAPTCHA v2 site keys)
- ⚠️ Requires code deployment to change keys

---

### Option 2: Set WordPress Options (FLEXIBLE)

Add keys to WordPress database via wp-cli or admin:

```bash
# Via wp-cli
wp option add threew_recaptcha_site_key 'YOUR_RECAPTCHA_SITE_KEY'
wp option add threew_recaptcha_secret_key 'YOUR_RECAPTCHA_SECRET_KEY'
```

Or create an admin settings page to manage these values.

**Pros**:
- ✅ Keys not in code
- ✅ Easy to update without deployment
- ✅ Environment-specific configuration

**Cons**:
- Requires database access or admin UI
- More complex implementation

---

### Option 3: Environment Variables (BEST PRACTICE)

Set environment variables on production server:

```bash
# In .env file or server configuration
THREEW_RECAPTCHA_SITE_KEY=YOUR_RECAPTCHA_SITE_KEY
THREEW_RECAPTCHA_SECRET_KEY=YOUR_RECAPTCHA_SECRET_KEY
```

**Pros**:
- ✅ Best security practice
- ✅ Environment-specific configuration
- ✅ Keys not in version control

**Cons**:
- Requires server access
- May need hosting provider support
- Deployment complexity

---

## Recommended Implementation: Option 1 (Hardcoded)

**File**: `/wp-content/themes/3w-2025/functions.php`
**Lines**: 14-20

**Change FROM**:
```php
if (!defined('THREEW_RECAPTCHA_SITE_KEY')) {
    define('THREEW_RECAPTCHA_SITE_KEY', 'YOUR_RECAPTCHA_SITE_KEY');
}

if (!defined('THREEW_RECAPTCHA_SECRET_KEY')) {
    define('THREEW_RECAPTCHA_SECRET_KEY', 'YOUR_RECAPTCHA_SECRET_KEY');
}
```

**Change TO**:
```php
// reCAPTCHA v2 keys for production
define('THREEW_RECAPTCHA_SITE_KEY', 'YOUR_RECAPTCHA_SITE_KEY');
define('THREEW_RECAPTCHA_SECRET_KEY', 'YOUR_RECAPTCHA_SECRET_KEY');
```

---

## Additional Verification

### Check Page Slug
Verify the about page slug matches the conditional check:

```php
// Current check (functions.php:332)
if ($recaptcha_site_key && (is_page_template('page-about.php') || is_page('about-us') || is_page('about'))) {
```

**Verify** the actual page slug:
1. Go to WordPress Admin → Pages → About Us
2. Check the permalink slug
3. Ensure it's either `about-us` or `about`

If slug is different, update the conditional to match.

---

## Testing Checklist

After deploying the fix:

- [ ] Clear all WordPress caches (object cache, page cache)
- [ ] Clear browser cache and cookies
- [ ] Visit https://www.3wdistributing.com/about-us/
- [ ] Verify reCAPTCHA widget appears above "Send Message" button
- [ ] Check browser console for JavaScript errors
- [ ] Test form submission with valid reCAPTCHA
- [ ] Test form submission without completing reCAPTCHA (should fail with "Please confirm you are not a robot")

---

## Rollback Plan

If issues occur:
1. Revert functions.php to previous version
2. Deploy immediately
3. Clear all caches
4. Re-investigate with detailed error logs

---

## Alternative: Debug Current Setup

If you want to understand WHY the constant isn't being defined, add debug logging:

```php
// Add temporarily to functions.php before line 89
error_log('THREEW_RECAPTCHA_SITE_KEY defined: ' . (defined('THREEW_RECAPTCHA_SITE_KEY') ? 'YES' : 'NO'));
error_log('THREEW_RECAPTCHA_SITE_KEY value: ' . (defined('THREEW_RECAPTCHA_SITE_KEY') ? THREEW_RECAPTCHA_SITE_KEY : 'NOT SET'));

function threew_get_recaptcha_site_key() {
    error_log('Getting reCAPTCHA site key...');
    // ... rest of function
```

Then check server error logs to see what's happening.

---

## Next Steps

1. **DECISION**: Choose implementation option (Recommended: Option 1)
2. **BACKUP**: Backup current functions.php
3. **IMPLEMENT**: Make the change
4. **DEPLOY**: Deploy to production via your normal process
5. **TEST**: Follow testing checklist above
6. **MONITOR**: Watch for successful form submissions
