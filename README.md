# Duplicate-Posts
A lightweight WordPress plugin that adds a Duplicate action to any post, page, or custom post type directly from the admin list table.

## Features

-  Duplicate any post, page, or custom post type with a single click
-  Copies all post content, excerpt, parent, menu order, and comment/ping status
-  Copies all taxonomies (categories, tags, and custom taxonomies)
-  Copies all post meta, including ACF and other custom fields
-  Copies the featured image
-  Duplicate is always created as a draft so you can review before publishing
-  Permission-checked — only users with edit_posts capability can duplicate
-  Nonce-verified requests to prevent CSRF
-  Success notice with a direct link to edit the new duplicate

## Requirements

-  WordPress 5.0+
-  PHP 7.4+

## Installation

-  Clone or download this repository:
`git clone https://github.com/your-username/duplicate-post.git`

-  Copy the duplicate-post folder into your WordPress plugins directory:
`/wp-content/plugins/duplicate-post/`
-  In your WordPress admin, go to Plugins → Installed Plugins and activate Duplicate Post.
-  Alternatively, save the contents of the post folder into a `.zip` file. Which you can install in wordpress as a normal plugin.

## Usage

-  Navigate to any post, page, or custom post type list table in the WordPress admin (e.g. Posts, Pages).
-  Hover over the entry you want to duplicate.
-  Click the Duplicate row action that appears beneath the title.
-  You'll be redirected back to the list table and a success notice will appear with a link to edit the new draft.

  <img width="1338" height="526" alt="Screenshot 2026-02-24 at 15 59 30" src="https://github.com/user-attachments/assets/17dff932-a510-4910-a221-7cd85f099918" />

  <img width="1348" height="616" alt="Screenshot 2026-02-24 at 16 00 40" src="https://github.com/user-attachments/assets/0d76836f-f900-4c89-88b2-ca01036457d1" />

