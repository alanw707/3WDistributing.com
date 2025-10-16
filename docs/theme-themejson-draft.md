# theme.json Draft — 3w-2025 Theme

```json
{
  "$schema": "https://schemas.wp.org/wp/6.6/theme.json",
  "version": 3,
  "settings": {
    "color": {
      "palette": [
        { "slug": "primary", "name": "Primary Blue", "color": "#1D6BCD" },
        { "slug": "primary-dark", "name": "Deep Navy", "color": "#0C1E33" },
        { "slug": "secondary", "name": "Electric Cyan", "color": "#18B8D7" },
        { "slug": "accent", "name": "Performance Red", "color": "#D84432" },
        { "slug": "surface", "name": "Light Smoke", "color": "#F3F6FA" },
        { "slug": "surface-alt", "name": "Pure White", "color": "#FFFFFF" },
        { "slug": "ink", "name": "Text Primary", "color": "#14181F" },
        { "slug": "ink-inverse", "name": "Text Inverse", "color": "#EEF3FF" }
      ],
      "gradients": [
        {
          "slug": "hero",
          "name": "Hero Gradient",
          "gradient": "linear-gradient(135deg, #0C1E33 0%, #1D6BCD 60%, #18B8D7 100%)"
        },
        {
          "slug": "card-hover",
          "name": "Card Hover",
          "gradient": "linear-gradient(180deg, rgba(12,30,51,0) 0%, rgba(29,107,205,0.85) 100%)"
        }
      ]
    },
    "spacing": {
      "units": ["px", "rem", "%"],
      "scale": {
        "0": "0",
        "05": "0.125rem",
        "1": "0.25rem",
        "2": "0.5rem",
        "3": "0.75rem",
        "4": "1rem",
        "5": "1.5rem",
        "6": "2rem",
        "7": "3rem",
        "8": "4rem"
      }
    },
    "typography": {
      "fontFamilies": [
        {
          "fontFamily": "Rajdhani, 'Segoe UI', sans-serif",
          "name": "Rajdhani",
          "slug": "display"
        },
        {
          "fontFamily": "Inter, 'Helvetica Neue', sans-serif",
          "name": "Inter",
          "slug": "body"
        },
        {
          "fontFamily": "Orbitron, 'Segoe UI', sans-serif",
          "name": "Orbitron",
          "slug": "numeric"
        }
      ],
      "fontSizes": [
        { "slug": "display-xl", "size": "3.5rem", "name": "Display XL" },
        { "slug": "display-lg", "size": "2.75rem", "name": "Display L" },
        { "slug": "h1", "size": "2.25rem", "name": "Heading 1" },
        { "slug": "h2", "size": "1.75rem", "name": "Heading 2" },
        { "slug": "h3", "size": "1.5rem", "name": "Heading 3" },
        { "slug": "body-lg", "size": "1.125rem", "name": "Body Large" },
        { "slug": "body-md", "size": "1rem", "name": "Body Medium" },
        { "slug": "body-sm", "size": "0.875rem", "name": "Body Small" }
      ],
      "lineHeights": [
        { "slug": "tight", "name": "Tight", "size": "1.1" },
        { "slug": "snug", "name": "Snug", "size": "1.3" },
        { "slug": "normal", "name": "Normal", "size": "1.5" },
        { "slug": "relaxed", "name": "Relaxed", "size": "1.65" }
      ],
      "letterSpacing": [
        { "slug": "caps", "name": "Caps", "size": "0.08em" }
      ]
    },
    "layout": {
      "contentSize": "1200px",
      "wideSize": "1400px"
    }
  },
  "styles": {
    "elements": {
      "heading": {
        "typography": {
          "fontFamily": "var(--wp--preset--font-family--display)"
        }
      },
      "button": {
        "border": {
          "radius": "999px"
        },
        "typography": {
          "textTransform": "uppercase",
          "letterSpacing": "var(--wp--preset--letter-spacing--caps)"
        }
      }
    }
  }
}
```

> Use this as a seed for the final `wp-content/themes/3w-2025/theme.json` once the theme scaffold is in place.
