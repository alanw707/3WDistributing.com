#!/usr/bin/env bash
################################################################################
# Setup Blog Page and Navigation
#
# Creates a Blog page with the page-blog.php template and adds it to the
# primary navigation menu on staging.
#
# Usage:
#   bash scripts/setup-blog-page.sh
#
# Required Environment Variables:
#   STAGE_WP_APP_USER     - WordPress application username
#   STAGE_WP_APP_PASSWORD - WordPress application password
#   STAGE_WP_BASE_URL     - Staging WordPress base URL
################################################################################

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

# Source REST API helpers
if [[ -f "$PROJECT_ROOT/scripts/blog-import/wp-cli/lib/helpers.sh" ]]; then
    # shellcheck source=blog-import/wp-cli/lib/helpers.sh
    source "$PROJECT_ROOT/scripts/blog-import/wp-cli/lib/helpers.sh"
else
    echo "❌ Error: Cannot find helpers.sh"
    exit 1
fi

################################################################################
# Validate environment
################################################################################

if [[ -z "${STAGE_WP_BASE_URL:-}" ]]; then
    echo "❌ Error: STAGE_WP_BASE_URL is not set"
    exit 1
fi

if [[ -z "${STAGE_WP_APP_USER:-}" ]] || [[ -z "${STAGE_WP_APP_PASSWORD:-}" ]]; then
    echo "❌ Error: STAGE_WP_APP_USER and STAGE_WP_APP_PASSWORD must be set"
    exit 1
fi

echo "🚀 Setting up Blog page on staging..."
echo "   Target: $STAGE_WP_BASE_URL"
echo ""

################################################################################
# Step 1: Check if Blog page already exists
################################################################################

echo "🔍 Checking for existing Blog page..."

EXISTING_PAGE=$(curl -sS \
    -H "Authorization: Basic $(echo -n "${STAGE_WP_APP_USER}:${STAGE_WP_APP_PASSWORD}" | base64)" \
    "${STAGE_WP_BASE_URL}/wp-json/wp/v2/pages?slug=blog&per_page=1" \
    | jq -r '.[0] // empty')

if [[ -n "$EXISTING_PAGE" ]]; then
    PAGE_ID=$(echo "$EXISTING_PAGE" | jq -r '.id')
    echo "✅ Blog page already exists (ID: $PAGE_ID)"
    echo "   URL: ${STAGE_WP_BASE_URL}/blog"
else
    ################################################################################
    # Step 2: Create Blog page with template
    ################################################################################

    echo "📝 Creating Blog page..."

    PAGE_DATA=$(jq -nc \
        --arg title "Blog" \
        --arg slug "blog" \
        --arg content "" \
        --arg status "publish" \
        '{
            title: $title,
            slug: $slug,
            content: $content,
            status: $status,
            comment_status: "closed"
        }')

    CREATE_RESPONSE=$(curl -sS -X POST \
        -H "Authorization: Basic $(echo -n "${STAGE_WP_APP_USER}:${STAGE_WP_APP_PASSWORD}" | base64)" \
        -H "Content-Type: application/json" \
        -d "$PAGE_DATA" \
        "${STAGE_WP_BASE_URL}/wp-json/wp/v2/pages")

    PAGE_ID=$(echo "$CREATE_RESPONSE" | jq -r '.id // empty')

    if [[ -z "$PAGE_ID" ]] || [[ "$PAGE_ID" == "null" ]]; then
        echo "❌ Failed to create Blog page"
        echo "Response: $CREATE_RESPONSE"
        exit 1
    fi

    echo "✅ Blog page created (ID: $PAGE_ID)"

    # Set the page template via post meta
    echo "📝 Assigning page-blog.php template..."

    META_RESPONSE=$(curl -sS -X POST \
        -H "Authorization: Basic $(echo -n "${STAGE_WP_APP_USER}:${STAGE_WP_APP_PASSWORD}" | base64)" \
        -H "Content-Type: application/json" \
        -d '{"meta": {"_wp_page_template": "page-blog.php"}}' \
        "${STAGE_WP_BASE_URL}/wp-json/wp/v2/pages/${PAGE_ID}")

    if [[ $? -eq 0 ]]; then
        echo "✅ Template assigned successfully"
    else
        echo "⚠️  Template assignment may have failed, but continuing..."
    fi

    echo "   URL: ${STAGE_WP_BASE_URL}/blog"
fi

################################################################################
# Step 3: Get primary menu
################################################################################

echo ""
echo "🔍 Finding primary navigation menu..."

# Get all menus
MENUS=$(curl -sS \
    -H "Authorization: Basic $(echo -n "${STAGE_WP_APP_USER}:${STAGE_WP_APP_PASSWORD}" | base64)" \
    "${STAGE_WP_BASE_URL}/wp-json/wp/v2/menus")

# Find the primary menu (assigned to 'primary' location)
PRIMARY_MENU_ID=$(echo "$MENUS" | jq -r '.[] | select(.locations[] == "primary") | .id // empty')

if [[ -z "$PRIMARY_MENU_ID" ]] || [[ "$PRIMARY_MENU_ID" == "null" ]]; then
    echo "⚠️  No primary menu found. Creating one..."

    # Create primary menu
    MENU_CREATE=$(curl -sS -X POST \
        -H "Authorization: Basic $(echo -n "${STAGE_WP_APP_USER}:${STAGE_WP_APP_PASSWORD}" | base64)" \
        -H "Content-Type: application/json" \
        -d '{"name": "Primary Menu", "slug": "primary-menu", "locations": ["primary"]}' \
        "${STAGE_WP_BASE_URL}/wp-json/wp/v2/menus")

    PRIMARY_MENU_ID=$(echo "$MENU_CREATE" | jq -r '.id // empty')

    if [[ -z "$PRIMARY_MENU_ID" ]] || [[ "$PRIMARY_MENU_ID" == "null" ]]; then
        echo "❌ Failed to create primary menu"
        exit 1
    fi

    echo "✅ Primary menu created (ID: $PRIMARY_MENU_ID)"
else
    echo "✅ Found primary menu (ID: $PRIMARY_MENU_ID)"
fi

################################################################################
# Step 4: Check if Blog is already in menu
################################################################################

echo ""
echo "🔍 Checking menu items..."

MENU_ITEMS=$(curl -sS \
    -H "Authorization: Basic $(echo -n "${STAGE_WP_APP_USER}:${STAGE_WP_APP_PASSWORD}" | base64)" \
    "${STAGE_WP_BASE_URL}/wp-json/wp/v2/menu-items?menus=${PRIMARY_MENU_ID}&per_page=100")

BLOG_MENU_ITEM=$(echo "$MENU_ITEMS" | jq -r --arg page_id "$PAGE_ID" \
    '.[] | select(.object_id == ($page_id | tonumber)) | .id // empty')

if [[ -n "$BLOG_MENU_ITEM" ]] && [[ "$BLOG_MENU_ITEM" != "null" ]]; then
    echo "✅ Blog is already in the menu (Menu Item ID: $BLOG_MENU_ITEM)"
else
    ################################################################################
    # Step 5: Add Blog page to menu
    ################################################################################

    echo "📝 Adding Blog to primary menu..."

    # Get the highest menu order to add at the end
    MAX_ORDER=$(echo "$MENU_ITEMS" | jq -r 'map(.menu_order) | max // 0')
    NEW_ORDER=$((MAX_ORDER + 1))

    MENU_ITEM_DATA=$(jq -nc \
        --arg title "Blog" \
        --arg page_id "$PAGE_ID" \
        --arg menu_id "$PRIMARY_MENU_ID" \
        --arg order "$NEW_ORDER" \
        '{
            title: $title,
            object: "page",
            object_id: ($page_id | tonumber),
            type: "post_type",
            menus: ($menu_id | tonumber),
            menu_order: ($order | tonumber),
            status: "publish"
        }')

    MENU_ITEM_RESPONSE=$(curl -sS -X POST \
        -H "Authorization: Basic $(echo -n "${STAGE_WP_APP_USER}:${STAGE_WP_APP_PASSWORD}" | base64)" \
        -H "Content-Type: application/json" \
        -d "$MENU_ITEM_DATA" \
        "${STAGE_WP_BASE_URL}/wp-json/wp/v2/menu-items")

    MENU_ITEM_ID=$(echo "$MENU_ITEM_RESPONSE" | jq -r '.id // empty')

    if [[ -z "$MENU_ITEM_ID" ]] || [[ "$MENU_ITEM_ID" == "null" ]]; then
        echo "❌ Failed to add Blog to menu"
        echo "Response: $MENU_ITEM_RESPONSE"
        exit 1
    fi

    echo "✅ Blog added to menu (Menu Item ID: $MENU_ITEM_ID)"
fi

################################################################################
# Summary
################################################################################

echo ""
echo "════════════════════════════════════════════════════════════════"
echo "✅ Blog page setup complete!"
echo "════════════════════════════════════════════════════════════════"
echo ""
echo "📄 Page ID: $PAGE_ID"
echo "🔗 Page URL: ${STAGE_WP_BASE_URL}/blog"
echo "📋 Menu ID: $PRIMARY_MENU_ID"
echo ""
echo "🎯 Next steps:"
echo "   1. Visit ${STAGE_WP_BASE_URL}/blog to see your blog listing"
echo "   2. Check the navigation menu to verify the Blog link"
echo "   3. Customize the page title/description if needed"
echo ""
