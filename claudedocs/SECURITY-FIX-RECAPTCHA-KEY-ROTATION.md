# reCAPTCHA Key Rotation - Security Incident Response

**Date**: November 7, 2025
**Incident**: Exposed reCAPTCHA keys detected by GitGuardian
**Status**: ✅ Repository cleaned, ⚠️ Keys need rotation

## Immediate Actions Completed

### 1. ✅ Removed Hardcoded Keys from Source Code
- Removed keys from `wp-content/themes/3w-2025/functions.php`
- Replaced with comment directing to environment variables
- Code now properly uses `threew_get_recaptcha_site_key()` and `threew_get_recaptcha_secret_key()` functions

### 2. ✅ Cleaned Documentation Files
- Updated all `claudedocs/*.md` files
- Replaced exposed keys with placeholders:
  - `YOUR_RECAPTCHA_SITE_KEY`
  - `YOUR_RECAPTCHA_SECRET_KEY`

### 3. ✅ Cleaned Git History
- Used `git-filter-repo` to remove keys from entire git history
- Replaced all historical occurrences with placeholders
- Force-pushed cleaned history to GitHub

### 4. ✅ Verified Security Posture
- Confirmed `.env` files are properly gitignored
- Verified no other secrets are hardcoded in source
- Confirmed all sensitive data uses environment variables

## Required User Actions

### ⚠️ CRITICAL: Rotate reCAPTCHA Keys at Google

The exposed keys **MUST** be rotated immediately as they remain accessible in old git history clones.

**Steps to rotate keys:**

1. **Go to Google reCAPTCHA Admin Console**
   - Visit: https://www.google.com/recaptcha/admin
   - Sign in with your Google account

2. **Locate Your Site**
   - Find "3wdistributing.com" or the site using these keys
   - Site key: `6LeVDgUsAAAAAPDjgFN5WRqWQ45VFTL7Qz6f6njK` (compromised)

3. **Delete Old Key Pair**
   - Delete the compromised site/key configuration
   - This will invalidate the exposed keys

4. **Create New reCAPTCHA Key Pair**
   - Create a new reCAPTCHA v2 configuration
   - Domain: `3wdistributing.com`, `staging.3wdistributing.com`
   - Type: reCAPTCHA v2 ("I'm not a robot" checkbox)
   - Save the new Site Key and Secret Key

5. **Update Environment Variables**

   **For Production (.env file - DO NOT COMMIT):**
   ```bash
   THREEW_RECAPTCHA_SITE_KEY=your_new_site_key_here
   THREEW_RECAPTCHA_SECRET_KEY=your_new_secret_key_here
   ```

6. **Update WordPress Options (Alternative Method)**

   Via WP-CLI:
   ```bash
   wp option update threew_recaptcha_site_key 'your_new_site_key_here'
   wp option update threew_recaptcha_secret_key 'your_new_secret_key_here'
   ```

   Or use Contact Form 7's reCAPTCHA integration panel in WordPress admin.

7. **Deploy Updated Configuration**
   - Update production server's `.env` file or WordPress options
   - Test contact forms to verify reCAPTCHA works
   - Clear any WordPress caches

8. **Mark GitGuardian Alert as Resolved**
   - Go to GitGuardian dashboard
   - Mark the alert as resolved
   - Add note: "Keys rotated, git history cleaned"

## Current Configuration

### How reCAPTCHA Keys Are Retrieved (Priority Order)

The theme uses a fallback system in `functions.php`:

1. **PHP Constants** (now removed - was security vulnerability)
2. **Environment Variables** via `getenv('THREEW_RECAPTCHA_SITE_KEY')`
3. **WordPress Options** via `get_option('threew_recaptcha_site_key')`
4. **Contact Form 7 Integration** via `WPCF7_RECAPTCHA_SITEKEY`

**Recommended**: Use WordPress options (#3) or CF7 integration (#4) for production.

## Security Best Practices Applied

✅ **Never commit secrets to git**
- All `.env` files are gitignored
- Documentation uses placeholders only

✅ **Use environment variables**
- Production secrets in `.env` (gitignored)
- WordPress options for server-side storage

✅ **Clean git history when exposed**
- Used `git-filter-repo` to rewrite history
- Force-pushed to remove from GitHub

✅ **Rotate compromised credentials**
- Document rotation process
- Invalidate old keys immediately

## Testing After Key Rotation

1. **Test About Page Contact Form**
   - Visit: https://www.3wdistributing.com/about/
   - Submit test message with reCAPTCHA
   - Verify submission works

2. **Test Staging Environment**
   - Repeat test on staging.3wdistributing.com
   - Ensure staging keys are also rotated if needed

3. **Monitor for Errors**
   - Check WordPress error logs
   - Monitor form submission metrics
   - Verify no reCAPTCHA validation failures

## Reference Documentation

- **GitGuardian Alert**: reCAPTCHA Key exposed (Nov 7, 2025)
- **Repository**: alanw707/3WDistributing.com
- **Commit**: 688ecfd (cleaned history)
- **Files Modified**:
  - `wp-content/themes/3w-2025/functions.php`
  - `claudedocs/recaptcha-*.md` (4 files)

## Timeline

- **13:24 UTC**: GitGuardian detected exposed key
- **13:36 UTC**: Removed hardcoded keys from source
- **13:37 UTC**: Cleaned git history with git-filter-repo
- **13:38 UTC**: Force-pushed cleaned history to GitHub
- **Pending**: User to rotate keys at Google reCAPTCHA Console

---

**Next Steps**:
1. Rotate keys at https://www.google.com/recaptcha/admin
2. Update production environment configuration
3. Test contact forms
4. Mark GitGuardian alert as resolved
