#!/usr/bin/env node

/**
 * WooCommerce Product Fetcher
 * 
 * Fetches all products from shop.3wdistributing.com via WooCommerce REST API
 * and saves them in format compatible with fitment import system.
 * 
 * Usage:
 *   node scripts/fetch-woocommerce-products.js
 *   node scripts/fetch-woocommerce-products.js --output custom-output.json
 *   node scripts/fetch-woocommerce-products.js --per-page 50 --max-pages 10
 */

const https = require('https');
const fs = require('fs');
const path = require('path');

// Load environment variables from .env.shop
const envPath = path.join(__dirname, '../.env.shop');
const envContent = fs.readFileSync(envPath, 'utf8');
const env = {};
envContent.split('\n').forEach(line => {
  if (line && !line.startsWith('#')) {
    const [key, ...valueParts] = line.split('=');
    env[key.trim()] = valueParts.join('=').trim();
  }
});

// Configuration
const config = {
  consumerKey: env.SHOP_WC_CONSUMER_KEY,
  consumerSecret: env.SHOP_WC_CONSUMER_SECRET,
  baseUrl: env.SHOP_BASE_URL,
  perPage: 100, // WooCommerce max is 100
  maxPages: null, // null = fetch all
  outputFile: 'woocommerce-products-all.json'
};

// Parse command line arguments
process.argv.slice(2).forEach((arg, index, arr) => {
  if (arg === '--output' && arr[index + 1]) {
    config.outputFile = arr[index + 1];
  }
  if (arg === '--per-page' && arr[index + 1]) {
    config.perPage = parseInt(arr[index + 1], 10);
  }
  if (arg === '--max-pages' && arr[index + 1]) {
    config.maxPages = parseInt(arr[index + 1], 10);
  }
});

// Validate credentials
if (!config.consumerKey || !config.consumerSecret) {
  console.error('❌ Error: WooCommerce API credentials not found in .env.shop');
  console.error('Make sure .env.shop contains:');
  console.error('  SHOP_WC_CONSUMER_KEY=...');
  console.error('  SHOP_WC_CONSUMER_SECRET=...');
  process.exit(1);
}

/**
 * Make authenticated request to WooCommerce API
 */
function fetchProducts(page = 1) {
  return new Promise((resolve, reject) => {
    const auth = Buffer.from(`${config.consumerKey}:${config.consumerSecret}`).toString('base64');
    const url = new URL(`${config.baseUrl}/wp-json/wc/v3/products`);
    url.searchParams.append('per_page', config.perPage);
    url.searchParams.append('page', page);
    url.searchParams.append('status', 'publish');
    url.searchParams.append('orderby', 'id');
    url.searchParams.append('order', 'asc');

    const options = {
      method: 'GET',
      headers: {
        'Authorization': `Basic ${auth}`,
        'User-Agent': '3W-Fitment-Importer/1.0'
      }
    };

    https.get(url.toString(), options, (res) => {
      let data = '';

      res.on('data', chunk => {
        data += chunk;
      });

      res.on('end', () => {
        if (res.statusCode === 200) {
          try {
            const products = JSON.parse(data);
            const totalPages = parseInt(res.headers['x-wp-totalpages'] || '1', 10);
            const totalProducts = parseInt(res.headers['x-wp-total'] || '0', 10);
            
            resolve({
              products,
              totalPages,
              totalProducts,
              currentPage: page
            });
          } catch (error) {
            reject(new Error(`Failed to parse JSON: ${error.message}`));
          }
        } else {
          reject(new Error(`HTTP ${res.statusCode}: ${data}`));
        }
      });
    }).on('error', reject);
  });
}

/**
 * Transform WooCommerce product to fitment import format
 */
function transformProduct(wcProduct) {
  return {
    name: wcProduct.name,
    categories: wcProduct.categories.map(cat => cat.name),
    tags: wcProduct.tags.map(tag => tag.name),
    url: wcProduct.permalink
  };
}

/**
 * Main execution
 */
async function main() {
  console.log('🚀 Starting WooCommerce product fetch...\n');
  console.log(`📍 Base URL: ${config.baseUrl}`);
  console.log(`📄 Per page: ${config.perPage}`);
  console.log(`📦 Output: ${config.outputFile}\n`);

  const allProducts = [];
  let currentPage = 1;
  let totalPages = 1;
  let totalProducts = 0;

  try {
    // Fetch first page to get totals
    console.log(`⏳ Fetching page ${currentPage}...`);
    const firstResult = await fetchProducts(currentPage);
    
    totalPages = firstResult.totalPages;
    totalProducts = firstResult.totalProducts;
    
    console.log(`📊 Total products: ${totalProducts}`);
    console.log(`📚 Total pages: ${totalPages}\n`);

    // Add first page products
    firstResult.products.forEach(product => {
      allProducts.push(transformProduct(product));
    });
    
    console.log(`✅ Page 1/${totalPages} - ${firstResult.products.length} products`);

    // Fetch remaining pages
    for (currentPage = 2; currentPage <= totalPages; currentPage++) {
      // Check if we've hit max pages limit
      if (config.maxPages && currentPage > config.maxPages) {
        console.log(`\n⏹️  Stopped at page ${currentPage - 1} (max-pages limit)`);
        break;
      }

      console.log(`⏳ Fetching page ${currentPage}/${totalPages}...`);
      const result = await fetchProducts(currentPage);
      
      result.products.forEach(product => {
        allProducts.push(transformProduct(product));
      });
      
      console.log(`✅ Page ${currentPage}/${totalPages} - ${result.products.length} products`);
      
      // Small delay to be respectful to the API
      await new Promise(resolve => setTimeout(resolve, 100));
    }

    // Create output object
    const output = {
      metadata: {
        fetchedAt: new Date().toISOString(),
        source: 'WooCommerce REST API',
        baseUrl: config.baseUrl,
        totalProducts: allProducts.length,
        totalPages: config.maxPages || totalPages,
        note: config.maxPages 
          ? `Fetched first ${config.maxPages} pages (partial dataset)`
          : 'Complete product catalog'
      },
      products: allProducts
    };

    // Save to file
    const outputPath = path.join(process.cwd(), config.outputFile);
    fs.writeFileSync(outputPath, JSON.stringify(output, null, 2));

    console.log(`\n✅ Success!`);
    console.log(`📦 Fetched ${allProducts.length} products`);
    console.log(`💾 Saved to: ${outputPath}`);
    console.log(`\n📝 Next step: Run import command:`);
    console.log(`   wp fitment import --source=${config.outputFile}`);

  } catch (error) {
    console.error(`\n❌ Error: ${error.message}`);
    process.exit(1);
  }
}

// Run
main();
