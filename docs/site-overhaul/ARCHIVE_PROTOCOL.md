# Velmora Predecessor Archive Protocol

When a reviewed alternate page is approved for promotion, the predecessor must be archived as a complete reproducible package, not just copied as HTML or PHP.

## Archive Location

Use: docs/archive/<route-or-module>-predecessor-<YYYY-MM-DD>/

For large authenticated applications such as the dashboard or control panel, use a module-level archive rather than one folder per screen.

## Required Archive Contents

### 1. Page source
Preserve the exact predecessor PHP/HTML templates and any route-specific include files.

### 2. Page-specific styles
Preserve every stylesheet responsible for the predecessor’s visual appearance, including desktop, tablet, mobile, route-specific responsive overrides and material inline style blocks.
Shared stylesheets should be documented by path when other live pages still use them.

### 3. Page-specific scripts
Preserve JavaScript that controls menus, drawers, forms, filters, calculators, dynamic components, modals, API calls and other material interactions.

### 4. Visual assets
Record or copy hero images, backgrounds, icons, illustrations and page-specific graphic treatments. Shared brand assets may be referenced by repository path instead of duplicated.

### 5. Shared components
Document headers, footers, navigation, shared PHP bootstrap/application files and shared form handlers. If a shared component is being replaced and would otherwise be lost, preserve its predecessor copy.

### 6. Functional dependencies
The archive README must document relevant database tables/columns, form handlers, redirect behavior, authentication assumptions, email behavior, external libraries and important route dependencies.

### 7. Screenshot/reference note
Where practical, include a reference screenshot or identify an existing repository/library image showing the predecessor in use.

### 8. Archive README
Every archive must state the original canonical route, date retired, replacement version, files preserved, shared dependencies not copied, known limitations and reason for retirement.

## Promotion Checklist

- Alternate page explicitly approved by the user.
- Alternate page has passed PHP lint/deployment.
- Desktop and mobile layout reviewed.
- Links point to intended professional routes.
- Forms and state-changing actions use the intended backend.
- Predecessor archive created and checked.
- Canonical route changed only after the archive exists.
- Redirects added where duplicate legacy routes are retired.
- Site-overhaul status document updated.

## Rule

Archive first, promote second.

The only exception is an emergency security fix where keeping the predecessor active would create an immediate security or data-integrity risk.