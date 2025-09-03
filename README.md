# LovePress

**Contributors:** jivi  
**Stable tag:** 2.0.0  
**Requires at least:** 5.6  
**Tested up to:** 6.6  
**License:** GPLv2 or later  

---

## Preview

![Preview](assets/view.png)
![Preview](assets/view2.png)

## Description

LovePress is a WordPress plugin designed to create beautiful wedding invitation pages with ease.  
It comes with ready-to-use templates, animation options, and a simple wizard to help you build your page step by step.  

### Key Features
- **Custom Post Type:** `lovepress_page` dedicated for invitations.  
- **Built-in Templates:** Minimalist, Elegant, Classic, Animated.  
- **Animation Options:** Fade, Slide, Zoom, Flip (configurable per section).  
- **Easy Page Wizard:** Accessible via **WP Admin → LovePress → Create Page**.  
- **Centralized Settings:** Manage defaults in **WP Admin → LovePress → Settings**.  
- **Shortcode Support:** Embed invitations anywhere using `[lovepress id="123"]`.  
- **Front-end Rendering:** Only single pages of CPT `lovepress_page` are rendered with LovePress template, keeping other site pages untouched.  

---

## Installation

1. Upload the plugin ZIP file via **Plugins → Add New → Upload Plugin**, or extract it into the `/wp-content/plugins/` directory.  
2. Activate the plugin through the **Plugins** menu in WordPress.  
3. Go to **LovePress → Settings** to configure default appearance.  
4. Use **LovePress → Create Page** wizard to generate your first invitation.  

---

## Usage

- Each LovePress page is stored as a **CPT entry** (`lovepress_page`).  
- Edit your page and customize the appearance, template, and animations from the meta boxes.  
- Optional: Add background video or custom RSVP link.  
- If no RSVP URL is provided, LovePress will display an internal RSVP form (submissions are stored as a private CPT).  
- Use `[lovepress id="123"]` shortcode to embed invitations in other posts or pages.  

---

## Frequently Asked Questions

**Q: Will LovePress override my existing pages or posts?**  
A: No. LovePress only takes over rendering for `lovepress_page` single entries.  

**Q: Can I use my own CSS or JS?**  
A: Yes. You can enqueue additional assets via your theme or child theme.  

---

## Changelog

### 2.0.0
- Initial stable release.  
- Added CPT `lovepress_page`.  
- Added ready-to-use templates (Minimalist, Elegant, Classic, Animated).  
- Added animation options (Fade, Slide, Zoom, Flip).  
- Added wizard for quick page creation.  
- Added shortcode support `[lovepress id="123"]`.  
- Added internal RSVP form with AJAX saving.  

---

## License

This plugin is licensed under the GPLv2 or later.  
You are free to modify and redistribute it under the same license.  