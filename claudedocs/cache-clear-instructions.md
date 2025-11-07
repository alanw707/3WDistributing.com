# Clear LiteSpeed Cache - Production Site

**Issue**: reCAPTCHA fix deployed but production site serving cached HTML

**Evidence**:
- Deployment successful: "Deployment complete"
- Cache header shows: `x-litespeed-cache: hit`
- Old page still being served without reCAPTCHA script

---

## Cache Clearing Options

### Option 1: LiteSpeed Cache Plugin (WordPress Admin - EASIEST)

1. Log into WordPress admin: https://www.3wdistributing.com/wp-admin/
2. Find **LiteSpeed Cache** in the left menu
3. Click **Toolbox** or **Purge**
4. Click **Purge All**
5. Wait 30 seconds
6. Visit https://www.3wdistributing.com/about-us/
7. Hard refresh (Ctrl+Shift+R or Cmd+Shift+R)

### Option 2: LiteSpeed Cache via .htaccess Purge

Add to `.htaccess` temporarily (if you have FTP access):
```apache
# Force cache purge
RewriteRule .* - [E=Cache-Control:no-cache]
```

Then remove after testing.

### Option 3: Hostinger Control Panel

1. Log into Hostinger control panel
2. Navigate to **Website** section
3. Find **Cache Management** or **Performance**
4. Click **Clear All Cache** or **Purge Cache**

### Option 4: Contact Hostinger Support

If above doesn't work:
1. Contact Hostinger support
2. Request: "Please clear all LiteSpeed cache for www.3wdistributing.com"
3. Mention: "Need fresh deployment to take effect"

---

## Verification After Clearing Cache

Run these checks:

### 1. Check for reCAPTCHA Script
```bash
curl -s https://www.3wdistributing.com/about-us/ | grep -i "google.com/recaptcha"
```

**Expected**: Should see script tag like:
```html
<script src="https://www.google.com/recaptcha/api.js"
```

### 2. Check Cache Headers
```bash
curl -sI https://www.3wdistributing.com/about-us/ | grep -i "cache"
```

**Expected**:
- `x-litespeed-cache: miss` (first load after purge)
- OR no cache header at all

### 3. Visual Check
1. Visit: https://www.3wdistributing.com/about-us/
2. Scroll to contact form
3. **Should see**: Google reCAPTCHA checkbox widget above "Send Message" button

---

## Why This is Necessary

**The Problem**:
- LiteSpeed Cache stores **entire HTML pages** in cache
- Your deployment updated PHP files but cache still serves **old HTML**
- Old HTML = no reCAPTCHA script loaded

**The Solution**:
- Purge cache → Forces server to regenerate pages from new PHP
- New PHP includes reCAPTCHA script
- Fresh HTML served to visitors

---

## Alternative: Wait it Out

If you can't access cache controls:
- LiteSpeed cache typically expires after 1-24 hours
- New visitors will start seeing fresh pages gradually
- But this delays the fix working

**Recommendation**: Clear cache immediately for instant fix.
