/* Production override for 3w-newsletter-popup/assets/newsletter-popup.js.
 * Base: deployed ?ver=1.0.1 asset (sha256 f3873bf2…). Adds Escape/backdrop
 * dismissal and modal focus management; existing timing and signup behavior stay intact.
 */
(function () {
  'use strict';

  var cfg = window.threewNewsletterPopup || {};
  var key = 'threewNewsletterPopupSuppressedAt';
  var dayMs = 24 * 60 * 60 * 1000;
  var suppressMs = (parseInt(cfg.suppressionDays || 30, 10) || 30) * dayMs;
  var showTimer = null;
  var retryTimer = null;
  var lastFocused = null;

  function suppressed() {
    var last = parseInt(localStorage.getItem(key) || '0', 10);
    return last && Date.now() - last < suppressMs;
  }

  function suppress() {
    localStorage.setItem(key, String(Date.now()));
  }

  function modal() {
    return document.getElementById('threew-newsletter-popup');
  }

  function isOpen() {
    var modalElement = modal();
    return !!(modalElement && !modalElement.hidden);
  }

  function show() {
    var modalElement = modal();
    if (!modalElement) return;
    if (hasBlockingConsentUi()) {
      scheduleRetry(1200);
      return;
    }
    lastFocused = document.activeElement;
    modalElement.hidden = false;
    document.documentElement.classList.add('threew-newsletter-open');
    suppress();
    focusFirstControl();
  }

  function hide(restoreFocus) {
    var modalElement = modal();
    if (modalElement) modalElement.hidden = true;
    document.documentElement.classList.remove('threew-newsletter-open');
    if (restoreFocus && lastFocused && typeof lastFocused.focus === 'function') {
      try { lastFocused.focus(); } catch (error) { /* detached node */ }
    }
    lastFocused = null;
  }

  function dismiss() {
    suppress();
    hide(true);
  }

  function focusable() {
    var modalElement = modal();
    if (!modalElement) return [];
    var selector = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
    return Array.prototype.slice.call(modalElement.querySelectorAll(selector)).filter(function (element) {
      return !!(element.offsetWidth || element.offsetHeight || element.getClientRects().length);
    });
  }

  function focusFirstControl() {
    var controls = focusable();
    if (controls.length) controls[0].focus();
  }

  function trapTab(event) {
    var modalElement = modal();
    var controls = focusable();
    if (!modalElement || !controls.length) return;
    var first = controls[0];
    var last = controls[controls.length - 1];
    var active = document.activeElement;
    if (event.shiftKey && (active === first || !modalElement.contains(active))) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && (active === last || !modalElement.contains(active))) {
      event.preventDefault();
      first.focus();
    }
  }

  function readCookie(name) {
    var match = document.cookie.match(
      new RegExp('(?:^|; )' + name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '=([^;]*)')
    );
    return match ? decodeURIComponent(match[1]) : '';
  }

  function hasConsentCookie() {
    var cookieName = cfg.consentCookie || 'threew_consent_v1';
    return !!readCookie(cookieName);
  }

  function hasBlockingConsentUi() {
    var banner = document.querySelector('[data-threew-banner]');
    var modalElement = document.querySelector('[data-threew-modal]');
    var bannerVisible = !!(banner && !banner.hidden);
    var modalVisible = !!(modalElement && !modalElement.hidden);
    return bannerVisible || modalVisible || !hasConsentCookie();
  }

  function scheduleRetry(delay) {
    if (retryTimer) window.clearTimeout(retryTimer);
    retryTimer = window.setTimeout(show, delay);
  }

  document.addEventListener('DOMContentLoaded', function () {
    if (suppressed()) return;

    var form = document.getElementById('threew-newsletter-popup-form');
    var close = document.getElementById('threew-newsletter-popup-close');
    var status = document.getElementById('threew-newsletter-popup-status');
    var modalElement = modal();

    showTimer = window.setTimeout(show, (parseInt(cfg.delaySeconds || 7, 10) || 0) * 1000);

    if (close) close.addEventListener('click', dismiss);

    if (modalElement) {
      // Clicking the scrim (anywhere outside the dialog) dismisses the popup.
      modalElement.addEventListener('click', function (event) {
        if (event.target === modalElement) dismiss();
      });
    }

    document.addEventListener('keydown', function (event) {
      if (!isOpen()) return;
      var keyName = event.key;
      if (keyName === 'Escape' || keyName === 'Esc') {
        event.preventDefault();
        dismiss();
        return;
      }
      if (keyName === 'Tab') trapTab(event);
    });

    document.addEventListener('threew:consent-updated', function () {
      if (suppressed()) return;
      if (showTimer) window.clearTimeout(showTimer);
      scheduleRetry(600);
    });
    if (!form) return;

    form.addEventListener('submit', function (event) {
      event.preventDefault();

      var email = form.querySelector('[name="email"]').value.trim();
      var consent = form.querySelector('[name="consent"]').checked;
      if (!email || !consent) {
        status.textContent = 'Enter your email and accept newsletter signup.';
        return;
      }

      var body = new URLSearchParams();
      body.set('action', 'threew_np_subscribe');
      body.set('nonce', cfg.nonce || '');
      body.set('email', email);
      body.set('consent', consent ? '1' : '0');

      form.querySelector('button[type="submit"]').disabled = true;
      status.textContent = 'Submitting...';

      fetch(cfg.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString()
      })
        .then(function (response) { return response.json(); })
        .then(function (data) {
          if (!data.success) throw new Error(data.data && data.data.message ? data.data.message : 'Signup failed.');
          suppress();
          form.innerHTML = '<p class="threew-np-success"></p>';
          form.querySelector('.threew-np-success').textContent = 'Success. Use code ' + data.data.couponCode + ' for ' + data.data.discountPercent + '% off.';
        })
        .catch(function (error) {
          status.textContent = error.message;
          form.querySelector('button[type="submit"]').disabled = false;
        });
    });
  });
})();
