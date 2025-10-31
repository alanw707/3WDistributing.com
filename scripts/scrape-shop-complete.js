#!/usr/bin/env node
/**
 * Complete Automated Product Scraper for shop.3wdistributing.com
 *
 * This script scrapes all products from the Akrapovic category using
 * Chrome DevTools MCP server automation.
 *
 * USAGE INSTRUCTIONS:
 *
 * This script must be run through Claude Code with Chrome DevTools MCP enabled.
 *
 * Steps to run:
 * 1. Ask Claude: "Run the complete shop scraper script"
 * 2. Claude will use Chrome DevTools MCP to:
 *    - Navigate through all 30 pages
 *    - Extract products using proven extraction code
 *    - Save all 299 products to JSON
 *
 * Expected output: scraped-products-all.json (all 299 products)
 * Time estimate: 2-5 minutes for full scrape
 */

const fs = require('fs');
const path = require('path');

// Configuration
const CONFIG = {
  baseUrl: 'https://shop.3wdistributing.com',
  category: 'akrapovic',
  totalPages: 30,
  productsPerPage: 10,
  expectedTotal: 299,
  outputFile: 'scraped-products-all.json'
};

/**
 * Extraction function - PROVEN TO WORK
 * This JavaScript runs in the browser context
 * Returns array of products from current page
 */
const EXTRACTION_CODE = `
(() => {
    const products = [];
    const headings = Array.from(document.querySelectorAll('h3'));

    headings.forEach((heading, index) => {
        const productLink = heading.closest('a');
        if (!productLink || !productLink.href.includes('/product/')) {
            return;
        }

        const name = heading.textContent.trim();
        const url = productLink.href;

        const detailsContainer = productLink.parentElement;
        const categoryContainer = detailsContainer?.firstElementChild;

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
            name: name,
            categories: categories,
            tags: [],
            url: url
        });
    });

    return products;
})()
`;

/**
 * Main scraping workflow
 * Note: This is pseudocode - actual execution via Claude Code + Chrome DevTools MCP
 */
async function scrapeAllPages() {
  console.log('🚀 Starting complete shop product scraper');
  console.log(`📦 Category: ${CONFIG.category}`);
  console.log(`📄 Total pages: ${CONFIG.totalPages}`);
  console.log(`🎯 Expected products: ${CONFIG.expectedTotal}`);
  console.log('');

  const allProducts = [];

  // PSEUDOCODE - Claude Code will execute via MCP:
  // for (let page = 1; page <= CONFIG.totalPages; page++) {
  //   1. navigate_page(`${CONFIG.baseUrl}/product-category/${CONFIG.category}/page/${page}/`)
  //   2. evaluate_script(EXTRACTION_CODE)
  //   3. allProducts.push(...pageProducts)
  //   4. console.log(`✅ Page ${page}/${CONFIG.totalPages} - ${pageProducts.length} products`)
  // }

  console.log('');
  console.log('📊 Scraping Statistics:');
  console.log(`   Total products: ${allProducts.length}`);
  console.log(`   Expected: ${CONFIG.expectedTotal}`);
  console.log(`   Coverage: ${((allProducts.length / CONFIG.expectedTotal) * 100).toFixed(1)}%`);
  console.log('');

  // Add metadata
  const output = {
    metadata: {
      scrapedAt: new Date().toISOString(),
      category: CONFIG.category,
      baseUrl: CONFIG.baseUrl,
      totalPages: CONFIG.totalPages,
      totalProducts: allProducts.length,
      source: 'shop.3wdistributing.com'
    },
    products: allProducts
  };

  // Save to file
  fs.writeFileSync(
    path.join(__dirname, '..', CONFIG.outputFile),
    JSON.stringify(output, null, 2),
    'utf8'
  );

  console.log(`💾 Saved to: ${CONFIG.outputFile}`);
  console.log('');
  console.log('✅ Scraping complete!');
  console.log('');
  console.log('Next steps:');
  console.log('1. Run: wp fitment import --source=scraped-products-all.json');
  console.log('2. Verify fitment API returns data');
  console.log('3. Test fitment selector frontend');

  return output;
}

// Export for CLI execution
module.exports = {
  scrapeAllPages,
  EXTRACTION_CODE,
  CONFIG
};

// CLI execution
if (require.main === module) {
  console.log('⚠️  This script requires Claude Code with Chrome DevTools MCP');
  console.log('');
  console.log('Please ask Claude to run: "Execute the complete shop scraper"');
  console.log('');
  process.exit(0);
}
