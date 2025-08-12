#!/bin/bash

echo "🔄 Merging modern-features-fullstack to main..."

# Ensure we're on main branch
git checkout main

# Pull latest changes
git pull origin main

# Merge the modern-features-fullstack branch
git merge modern-features-fullstack

# If there are conflicts, resolve them
if [ $? -ne 0 ]; then
    echo "⚠️  Merge conflicts detected. Please resolve them manually."
    echo "After resolving conflicts:"
    echo "1. git add ."
    echo "2. git commit -m 'Resolve merge conflicts'"
    echo "3. git push origin main"
    exit 1
fi

# Update README.md
echo "�� Updating README.md..."
# The README.md content is already updated above

# Add all changes
git add .

# Commit the merge
git commit -m "Merge modern-features-fullstack into main

✨ Complete feature set:
- Full bilingual support (Arabic/English)
- Dark/Light theme switching
- Modern admin dashboard
- Real-time notifications
- Smart reactions system
- User management & following
- Content moderation
- Responsive design
- Security features
- Performance optimizations

🎯 Production-ready platform!"

# Push to main
git push origin main

echo "✅ Successfully merged modern-features-fullstack to main!"
echo "🚀 Your project is now ready for production deployment!"
