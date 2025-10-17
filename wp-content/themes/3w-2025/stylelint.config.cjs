/**
 * Stylelint configuration aligned with 3W CSS authoring rules.
 * - Extends the standard rule set.
 * - Keeps selectors scoped (no IDs/universal selectors).
 * - Delegates file-length enforcement to the custom size check script.
 */
module.exports = {
  extends: ['stylelint-config-standard'],
  ignoreFiles: ['build/**/*.css', 'node_modules/**/*.css'],
  rules: {
    'selector-max-id': 0,
    'selector-max-universal': 0,
    'max-nesting-depth': 3,
    'no-duplicate-selectors': true,
    'no-descending-specificity': null,
    'declaration-no-important': true,
    'color-function-notation': 'legacy',
    'alpha-value-notation': 'number',
    'custom-property-pattern': null,
    'selector-class-pattern': [
      '^[a-z0-9]+(?:-[a-z0-9]+)*(?:__(?:[a-z0-9]+(?:-[a-z0-9]+)*))?(?:--(?:[a-z0-9]+(?:-[a-z0-9]+)*))?$',
      {
        message:
          'Use BEM-style lowercase kebab-case (`block__element--modifier`) for class names to keep component styles predictable.',
      },
    ],
    'media-feature-range-notation': null,
  },
};
