#!/bin/bash

echo "🚀 Larafactu - Pre-Push Checklist"
echo "=================================="
echo ""

# Step 1: Pint
echo "📝 Step 1: Running Pint..."
vendor/bin/pint
if [ $? -ne 0 ]; then
    echo "❌ Pint failed. Fix style issues and try again."
    exit 1
fi
echo "✅ Pint passed"
echo ""

# Step 2: Tests
echo "🧪 Step 2: Running tests..."
php artisan test
if [ $? -ne 0 ]; then
    echo "❌ Tests failed. Fix tests and try again."
    exit 1
fi
echo "✅ Tests passed"
echo ""

# Step 3: Git status
echo "📊 Step 3: Git status..."
git status
echo ""

# Ask for confirmation
read -p "👉 Ready to commit? Continue? (y/n) " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]
then
    echo "❌ Aborted by user"
    exit 1
fi

echo ""
echo "✅ Pre-push checks passed!"
echo ""
echo "📦 Next steps:"
echo "  1. git add ."
echo "  2. git commit -m \"your message\""
echo "  3. git push origin main"
echo "  4. gh run list --limit 1 (wait 3-5 min)"
echo ""

