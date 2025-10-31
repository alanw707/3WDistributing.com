#!/usr/bin/env node
/**
 * Scrape products from shop.3wdistributing.com using Playwright
 *
 * This script uses the Chrome DevTools MCP server to scrape product data
 * from the shop site. The scraped data is saved to a JSON file that can
 * be consumed by the PHP fitment import system.
 *
 * Usage:
 *   node scripts/scrape-shop-products.js [--limit=N] [--output=FILE]
 *
 * Options:
 *   --limit=N       Limit to N pages (default: all pages)
 *   --output=FILE   Output JSON file path (default: scraped-products.json)
 *   --category=SLUG Category slug to scrape (default: akrapovic)
 */

const fs = require('fs');
const path = require('path');

// Parse command line arguments
const args = process.argv.slice(2);
const options = {
  limit: null,
  output: 'scraped-products.json',
  category: 'akrapovic',
  baseUrl: 'https://shop.3wdistributing.com'
};

args.forEach(arg => {
  const [key, value] = arg.split('=');
  if (key === '--limit') options.limit = parseInt(value, 10);
  if (key === '--output') options.output = value;
  if (key === '--category') options.category = value;
});

console.log('🚀 Starting shop product scraper');
console.log('📦 Category:', options.category);
console.log('📄 Page limit:', options.limit || 'all pages');
console.log('💾 Output file:', options.output);
console.log('');

/**
 * Extract product data from the current page
 * This is the working extraction code discovered through testing
 */
function extractProducts() {
  const products = [];

  // Find all product headings (h3 elements)
  const headings = Array.from(document.querySelectorAll('h3'));

  headings.forEach((heading, index) => {
    // Get the product link that contains the heading
    const productLink = heading.closest('a');
    if (!productLink || !productLink.href.includes('/product/')) {
      return;
    }

    const name = heading.textContent.trim();
    const url = productLink.href;

    // The product link's parent is the details container
    // The category container is the first child of the details container
    const detailsContainer = productLink.parentElement;
    const categoryContainer = detailsContainer?.firstElementChild;

    // Extract category links
    const categories = [];
    if (categoryContainer) {
      const categoryLinks = categoryContainer.querySelectorAll('a');
      categoryLinks.forEach(link => {
        const category = link.textContent.trim();
        if (category && category !== ',') {
          categories.push(category);
        }
      });
    }

    products.push({
      id: `product_${Date.now()}_${index}`,
      name: name,
      categories: categories,
      tags: [],
      url: url
    });
  });

  return products;
}

/**
 * Check if there's a next page link
 */
function hasNextPage() {
  const nextLink = document.querySelector('.next.page-numbers');
  return nextLink !== null;
}

/**
 * Get the next page URL
 */
function getNextPageUrl() {
  const nextLink = document.querySelector('.next.page-numbers');
  return nextLink ? nextLink.href : null;
}

/**
 * Main scraping logic
 * Note: This script outputs instructions for manual execution via MCP
 */
async function main() {
  console.log('⚠️  MANUAL SCRAPING INSTRUCTIONS');
  console.log('');
  console.log('This script requires manual execution using the Chrome DevTools MCP server.');
  console.log('Follow these steps:');
  console.log('');
  console.log('1. Navigate to the category page:');
  console.log(`   URL: ${options.baseUrl}/product-category/${options.category}/`);
  console.log('');
  console.log('2. Use the extractProducts() function to get products from each page');
  console.log('');
  console.log('3. Check for next page with hasNextPage() and navigate with getNextPageUrl()');
  console.log('');
  console.log('4. Repeat until all pages are scraped');
  console.log('');
  console.log('5. Save all products to JSON file');
  console.log('');
  console.log('📋 EXTRACTION FUNCTIONS:');
  console.log('');
  console.log('// Extract products from current page');
  console.log('extractProducts()');
  console.log('');
  console.log('// Check for next page');
  console.log('hasNextPage()');
  console.log('');
  console.log('// Get next page URL');
  console.log('getNextPageUrl()');
  console.log('');
  console.log('✅ COMPLETE AUTOMATED SCRAPING:');
  console.log('');
  console.log('Use the automated scraping approach with Chrome DevTools MCP:');
  console.log('1. Navigate to category page');
  console.log('2. Execute extraction loop');
  console.log('3. Handle pagination automatically');
  console.log('4. Save results to JSON');
  console.log('');

  // Create a sample output structure
  const sampleOutput = {
    metadata: {
      scrapedAt: new Date().toISOString(),
      category: options.category,
      baseUrl: options.baseUrl,
      totalProducts: 0,
      totalPages: 0
    },
    products: []
  };

  // Save sample structure
  fs.writeFileSync(
    options.output,
    JSON.stringify(sampleOutput, null, 2),
    'utf8'
  );

  console.log(`📝 Sample JSON structure created at: ${options.output}`);
  console.log('');
  console.log('Next steps:');
  console.log('1. Use Chrome DevTools MCP to scrape products');
  console.log('2. Update the JSON file with real product data');
  console.log('3. Run: wp fitment import --source=' + options.output);
}

// Run main function
main().catch(console.error);
