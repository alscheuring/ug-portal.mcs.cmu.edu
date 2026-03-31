# Layup Page Import/Export

This document explains how to import and export layup pages using JSON files.

## Commands

### Import a Layup Page

Import a layup page from a JSON file:

```bash
php artisan layup:import path/to/page.json
```

#### Options

- `--team=ID|slug` - Specify the team to assign the page to
- `--author=ID|email` - Specify the author of the page
- `--status=draft|published` - Set the page status (default: draft)
- `--overwrite` - Overwrite existing page with the same slug

#### Examples

```bash
# Import with team and author
php artisan layup:import sample-page.json --team=engineering --author=admin@example.com

# Import and publish immediately
php artisan layup:import sample-page.json --status=published

# Overwrite existing page
php artisan layup:import sample-page.json --overwrite
```

### Export a Layup Page

Export an existing layup page to a JSON file:

```bash
php artisan layup:export PAGE_ID_OR_SLUG
```

#### Options

- `--output=path` - Specify output file path (default: storage/app/layup-exports/[slug].json)
- `--include-sidebars` - Include sidebar assignments in the export
- `--team=ID|slug` - Search within a specific team
- `--format=json` - Export format (currently only JSON supported)

#### Examples

```bash
# Export by ID
php artisan layup:export 123

# Export by slug with sidebars
php artisan layup:export "sample-page" --include-sidebars

# Export to specific file
php artisan layup:export "home-page" --output=/tmp/home-page-backup.json

# Export from specific team
php artisan layup:export "about" --team=marketing
```

## JSON Structure

A layup page JSON file has the following structure:

### Required Fields

- `title` - Page title
- `content` - Page content structure (see below)

### Optional Fields

- `slug` - URL slug (auto-generated from title if not provided)
- `status` - Page status: `draft` or `published`
- `meta` - Meta information (object)
- `is_department_home` - Whether this is a department home page (boolean)
- `sidebars` - Array of sidebar assignments

### Content Structure

The `content` field contains a complex nested structure:

```json
{
  "content": {
    "rows": [
      {
        "id": "unique-row-id",
        "order": 0,
        "columns": [
          {
            "id": "unique-column-id",
            "span": {
              "xl": 12,
              "lg": 12,
              "md": 12,
              "sm": 12
            },
            "widgets": [
              {
                "id": "unique-widget-id",
                "type": "text",
                "data": {
                  "content": "<p>Your HTML content here</p>",
                  // ... other widget-specific properties
                }
              }
            ],
            "settings": {
              "padding": "p-4",
              "background": "transparent"
            }
          }
        ],
        "settings": {
          "wrap": "wrap",
          "align": "stretch",
          "justify": "start",
          "direction": "row",
          "full_width": false
        }
      }
    ]
  }
}
```

### Widget Types

The system supports various widget types:
- `text` - Rich text content
- `image` - Image widgets
- `button` - Button components
- And many more (see layup documentation)

### Sidebar Assignments

If you want to include sidebar assignments in your import:

```json
{
  "sidebars": [
    {
      "id": 1,
      "sort_order": 0
    },
    {
      "id": 3,
      "sort_order": 1
    }
  ]
}
```

Or use simple ID array:
```json
{
  "sidebars": [1, 3, 5]
}
```

## Example Files

A complete example file is available at:
`storage/app/layup-examples/sample-page.json`

This file demonstrates:
- Multi-column layout
- Text widgets with styling
- Responsive breakpoints
- Sidebar assignments

## Notes

- IDs are auto-generated if not provided
- Slugs are auto-generated from titles if not provided
- Import validates JSON structure and required fields
- Export includes metadata for context
- All files are stored in `storage/app/layup-exports/` by default
- Sidebar assignments are validated to ensure they belong to the correct team

## Troubleshooting

### File Not Found
Ensure the JSON file path is correct and accessible.

### Validation Errors
Check that your JSON contains required fields (`title`, `content` with `rows` array).

### Team/Author Not Found
Verify the team slug/ID and author email/ID exist in the system.

### Permission Issues
Ensure you have write permissions to the output directory.

### Page Already Exists
Use the `--overwrite` flag to replace existing pages with the same slug.