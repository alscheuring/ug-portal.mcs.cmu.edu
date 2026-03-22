# Fabricator to Layup Migration Strategy

## Current State Analysis

### Fabricator Pages in Database
- **7 total pages** in the system
- **4 pages with block content** (pages 4, 5, 6, 7)
- **3 pages without blocks** (pages 1, 2, 3) - likely using simple content field

### Current Block Types & Usage

#### 1. Hero Block (`hero`)
**Usage:** Page 5 - Welcome page
**Current Structure:**
```json
{
  "title": "Welcome to Biological Sciences",
  "subtitle": "Discover cutting-edge research...",
  "button_link": "/about",
  "button_text": "Learn More"
}
```
**Layup Mapping:** → `HeroWidget`
**Migration Complexity:** ⭐ Simple - Direct mapping

#### 2. Text Block (`text`)
**Usage:** Page 5 - Two instances for content sections
**Current Structure:**
```json
{
  "content": "<p>HTML content...</p>",
  "heading": "About Our Department"
}
```
**Layup Mapping:** → `RichTextWidget` + `HeadingWidget`
**Migration Complexity:** ⭐⭐ Moderate - May need to split heading/content

#### 3. Team Members Block (`team-members`)
**Usage:** Page 6 - Team showcase
**Current Structure:**
```json
{
  "heading": "Meet Our Amazing Team",
  "members": [
    {
      "bio": "Sarah leads our cutting-edge research...",
      "name": "Dr. Sarah Johnson",
      "image": 2,
      "title": "Lead Research Scientist",
      "twitter_url": "https://twitter.com/sarahjohnson",
      "linkedin_url": "https://linkedin.com/in/sarah-johnson-phd"
    }
  ],
  "description": "Our diverse team of experts..."
}
```
**Layup Mapping:** → `TeamGridWidget`
**Migration Complexity:** ⭐⭐⭐ Complex - Need to map member structure

#### 4. Leadership Grid Block (`leadership-grid`)
**Usage:** Pages 4 & 7 - Leadership showcases
**Current Structure:** Same as team-members but with `leaders` array
**Layup Mapping:** → `TeamGridWidget`
**Migration Complexity:** ⭐⭐⭐ Complex - Similar to team-members

### Current Layouts
- **default** (4 pages)
- **leadership** (2 pages)
- **team** (1 page)

## Migration Strategy

### Phase 1: Database Migration
1. **Run Layup Migration:** Create `layup_pages` table
2. **Create Migration Command:** `php artisan make:command MigrateFabricatorToLayup`
3. **Data Transformation:** Map existing page data to Layup format

### Phase 2: Block Mapping & Widget Creation

#### Custom Widget Creation (if needed)
Layup has extensive widgets, but we may need custom ones:

1. **Check TeamGridWidget compatibility** - May work as-is
2. **Create custom widgets if needed:**
   - `App\Layup\Widgets\CMUTeamGridWidget`
   - `App\Layup\Widgets\CMULeadershipGridWidget`

#### Widget Configuration Mapping

**Hero Block → HeroWidget**
```php
// Fabricator format
["title" => "...", "subtitle" => "...", "button_link" => "...", "button_text" => "..."]

// Layup HeroWidget format (need to verify exact schema)
["heading" => "...", "subheading" => "...", "cta_url" => "...", "cta_text" => "..."]
```

**Text Block → RichTextWidget + HeadingWidget**
```php
// Fabricator format
["heading" => "About Us", "content" => "<p>...</p>"]

// Layup format (split into two widgets)
[
  ["type" => "heading", "data" => ["text" => "About Us"]],
  ["type" => "rich-text", "data" => ["content" => "<p>...</p>"]]
]
```

### Phase 3: Migration Command Implementation

**Command Structure:**
```php
php artisan fabricator:migrate-to-layup [--dry-run] [--team=1]
```

**Migration Logic:**
1. **Backup existing data** - Export to JSON
2. **Create Layup pages** - Copy basic page data
3. **Transform blocks** - Convert each block type
4. **Handle images** - Ensure Curator images work with Layup
5. **Preserve layouts** - Map to Layup's layout system
6. **Maintain URLs** - Keep existing slug structure

### Phase 4: Template & Route Updates

#### Update Routes
- Current: Using Fabricator's page routing
- Target: Switch to Layup's frontend routing (`/pages/{slug}`)
- **Impact:** May need URL structure changes

#### Template Updates
- Current: Custom Fabricator layouts (default, leadership, team)
- Target: Layup's layout system
- **Action:** Create custom Layup templates if needed

### Phase 5: Admin Interface Migration

#### Filament Integration
1. **Remove Fabricator resources** from Filament
2. **Verify Layup integration** with existing Filament admin
3. **Test team-scoped access** - Ensure TeamAdmins only see their pages
4. **Update navigation** - Replace Fabricator menu items

### Phase 6: Testing & Validation

#### Automated Tests
1. **Migration command tests** - Verify data transformation
2. **Page rendering tests** - Ensure pages display correctly
3. **Admin interface tests** - Verify Filament integration

#### Manual Testing Checklist
- [ ] All pages migrate successfully
- [ ] Block content displays correctly
- [ ] Images load properly (Curator integration)
- [ ] Team scoping works in admin
- [ ] Public pages render correctly
- [ ] SEO metadata preserved

## Risk Assessment & Mitigation

### High Risk Areas

1. **Image References**
   - **Risk:** Curator images may not map correctly
   - **Mitigation:** Test image field mapping early

2. **Custom Styling**
   - **Risk:** Current block styling may not match Layup widgets
   - **Mitigation:** Create custom widget templates

3. **URL Structure Changes**
   - **Risk:** Breaking existing links
   - **Mitigation:** Implement redirects, keep same slug structure

4. **Team Scoping**
   - **Risk:** Layup may not support team-scoped pages
   - **Mitigation:** Extend Layup models with team relationship

### Rollback Plan
1. **Keep Fabricator installed** during migration
2. **Database backup** before migration
3. **Feature flag** to switch between systems
4. **Quick rollback command** if issues arise

## Implementation Timeline

### Week 1: Setup & Analysis
- [x] Install Layup package
- [x] Analyze current data structure
- [ ] Create migration command skeleton
- [ ] Test Layup widget compatibility

### Week 2: Core Migration
- [ ] Implement block transformation logic
- [ ] Create custom widgets if needed
- [ ] Build comprehensive migration command
- [ ] Test on sample pages

### Week 3: Integration & Testing
- [ ] Integrate with Filament admin
- [ ] Update public page routes/templates
- [ ] Comprehensive testing
- [ ] Performance optimization

### Week 4: Deployment & Cleanup
- [ ] Production migration
- [ ] Monitor for issues
- [ ] Remove Fabricator dependencies
- [ ] Documentation updates

## Success Criteria

✅ **Functionality:** All existing pages work identically
✅ **Performance:** No degradation in load times
✅ **Admin UX:** Team admins can edit pages seamlessly
✅ **SEO:** No loss of search rankings
✅ **Maintenance:** Easier content management with Layup's modern interface

## Next Steps

1. **Create migration command structure**
2. **Test widget compatibility** with sample data
3. **Implement block transformation logic**
4. **Verify team scoping requirements**