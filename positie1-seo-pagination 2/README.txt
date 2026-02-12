Positie1 SEO Pagination (v1.5.4)
=============================

New in 1.5.4
---------------
- Frontend CSS wordt conditioneel geladen (alleen bij widget/shortcode)
- Rank Math ondersteuning voor canonical + robots (noindex op paginering)

New in 1.5.1
---------------
- Elementor widget Content: Query ID (adds data-query-id on wrapper)

Widget
------
Name: "SEO Pagination" (General)

Content:
- Query ID (optional)
- Aria label, Mid size, End size, Prev/Next text

Styling in Elementor:
- Items (Normal)
- Hover (links only)
- Current/Active
- Prev/Next (separate)
- Dots (separate)
- Focus/Pressed (A11y)

Shortcode (fallback)
-------------------
[seo_pagination query_id="..."]
Alias (optional): [pagination]
