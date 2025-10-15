
# Modified UI + Editor + PDF structure

This package adds:
- Card-style dashboard (`templates/list.php`) with search and Create button.
- Two-pane editor for New/Edit (`templates/new.php`, `templates/edit.php`) driven by `/assets/editor.js`.
- Support for Pages, Tables and Course Content blocks (serialized to a hidden `items` JSON).
- Global styles in `/public/assets/app.css`.
- DB migration to add `proposal_items.type` column in `/migrations/20251014_add_type_to_items.sql`.

## Backend wiring (you must adapt if your Controller/Model differ)
- `route_new_form()` should define `$default_items` and include `new.php`.
- `route_edit_form()` should load `$items` (arrays) and include `edit.php`.
- `route_create()` / `route_update()` should read `$_POST['items']` (JSON), validate, and save.

## Items JSON example
See the message body for an example array of items that the editor produces.

## PDF
`templates/proposal_pdf.php` already uses banner/strip images from `/public/assets`. It can be extended to render tables and content blocks by iterating over `$proposal_items` in order, grouping children under each page.
