<?php

/**
 * Plugin Name: Duplicate Post
 * Plugin URI:  https://plusnarrative.com/
 * Description: Duplicate any page, post, or custom post type with a single click.
 * Version:     1.0.0
 * Author:      PLusNarrative
 * License:     GPL-2.0+
 */

if (! defined('ABSPATH')) {
    exit;
}

class Duplicate_Post
{

    public function __construct()
    {
        // Add "Duplicate" row action to all post type list tables
        add_filter('post_row_actions',    [$this, 'add_row_action'], 10, 2);
        add_filter('page_row_actions',    [$this, 'add_row_action'], 10, 2);

        // Handle the duplicate action
        add_action('admin_action_duplicate_post', [$this, 'handle_duplicate']);

        // Admin notice on success
        add_action('admin_notices', [$this, 'admin_notice']);
    }

    /**
     * Add "Duplicate" link to the row actions in the post list table.
     */
    public function add_row_action(array $actions, WP_Post $post): array
    {
        if (! current_user_can('edit_posts')) {
            return $actions;
        }

        $url = wp_nonce_url(
            add_query_arg(
                [
                    'action'  => 'duplicate_post',
                    'post_id' => $post->ID,
                ],
                admin_url('admin.php')
            ),
            'duplicate_post_' . $post->ID
        );

        $actions['duplicate'] = '<a href="' . esc_url($url) . '">Duplicate</a>';

        return $actions;
    }

    /**
     * Handle the duplication request.
     */
    public function handle_duplicate(): void
    {
        $post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;

        if (! $post_id) {
            wp_die('No post ID provided.');
        }

        check_admin_referer('duplicate_post_' . $post_id);

        if (! current_user_can('edit_posts')) {
            wp_die('You do not have permission to duplicate posts.');
        }

        $post = get_post($post_id);

        if (! $post) {
            wp_die('Post not found.');
        }

        $new_post_id = $this->duplicate($post);

        // Redirect back to the list table with a success flag
        $redirect = add_query_arg(
            [
                'post_type'      => $post->post_type === 'post' ? null : $post->post_type,
                'duplicated'     => 1,
                'new_post_id'    => $new_post_id,
            ],
            admin_url($post->post_type === 'post' ? 'edit.php' : 'edit.php')
        );

        wp_safe_redirect($redirect);
        exit;
    }

    /**
     * Duplicate a post and all its associated data.
     */
    public function duplicate(WP_Post $post): int
    {
        $current_user = wp_get_current_user();

        // Build the duplicate post args
        $new_post = [
            'post_title'     => $post->post_title . ' (Copy)',
            'post_content'   => $post->post_content,
            'post_excerpt'   => $post->post_excerpt,
            'post_status'    => 'draft',
            'post_type'      => $post->post_type,
            'post_author'    => $current_user->ID,
            'post_parent'    => $post->post_parent,
            'menu_order'     => $post->menu_order,
            'comment_status' => $post->comment_status,
            'ping_status'    => $post->ping_status,
        ];

        $new_post_id = wp_insert_post($new_post);

        if (is_wp_error($new_post_id)) {
            wp_die($new_post_id->get_error_message());
        }

        // Copy taxonomies
        $this->copy_taxonomies($post->ID, $new_post_id, $post->post_type);

        // Copy post meta
        $this->copy_meta($post->ID, $new_post_id);

        // Copy featured image
        $thumbnail_id = get_post_thumbnail_id($post->ID);
        if ($thumbnail_id) {
            set_post_thumbnail($new_post_id, $thumbnail_id);
        }

        return $new_post_id;
    }

    /**
     * Copy all taxonomies from one post to another.
     */
    private function copy_taxonomies(int $source_id, int $target_id, string $post_type): void
    {
        $taxonomies = get_object_taxonomies($post_type);

        foreach ($taxonomies as $taxonomy) {
            $terms = wp_get_object_terms($source_id, $taxonomy, ['fields' => 'ids']);

            if (! empty($terms) && ! is_wp_error($terms)) {
                wp_set_object_terms($target_id, $terms, $taxonomy);
            }
        }
    }

    /**
     * Copy all post meta from one post to another.
     */
    private function copy_meta(int $source_id, int $target_id): void
    {
        $meta = get_post_meta($source_id);

        if (empty($meta)) {
            return;
        }

        // Meta keys to skip
        $skip = ['_wp_old_slug', '_edit_lock', '_edit_last'];

        foreach ($meta as $key => $values) {
            if (in_array($key, $skip, true)) {
                continue;
            }

            foreach ($values as $value) {
                add_post_meta($target_id, $key, maybe_unserialize($value));
            }
        }
    }

    /**
     * Show a success notice after duplication.
     */
    public function admin_notice(): void
    {
        if (! isset($_GET['duplicated'], $_GET['new_post_id'])) {
            return;
        }

        $new_post_id = absint($_GET['new_post_id']);
        $edit_url    = get_edit_post_link($new_post_id);

        printf(
            '<div class="notice notice-success is-dismissible"><p>Post duplicated. <a href="%s">Edit the duplicate &rarr;</a></p></div>',
            esc_url($edit_url)
        );
    }
}

new Duplicate_Post();
