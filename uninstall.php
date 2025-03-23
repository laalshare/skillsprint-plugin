<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package    SkillSprint
 */

// If uninstall not called from WordPress, exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check for plugin settings
$settings = get_option('skillsprint_settings');
$preserve_data = isset($settings['preserve_data_on_uninstall']) ? (bool) $settings['preserve_data_on_uninstall'] : true;

if (!$preserve_data) {
    // Remove custom post type posts and meta data
    global $wpdb;

    // Define custom post type
    $post_type = 'blueprint';
    
    // Get all post IDs of the custom post type
    $post_ids = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type = %s", $post_type));
    
    // Delete all posts and related meta
    foreach ($post_ids as $post_id) {
        wp_delete_post($post_id, true);
    }
    
    // Delete custom taxonomies' terms
    $taxonomies = array('blueprint_category', 'blueprint_tag', 'blueprint_difficulty');
    foreach ($taxonomies as $taxonomy) {
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ));
        
        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                wp_delete_term($term->term_id, $taxonomy);
            }
        }
    }
    
    // Drop custom tables
    $tables = array(
        $wpdb->prefix . 'skillsprint_progress',
        $wpdb->prefix . 'skillsprint_quiz_responses',
        $wpdb->prefix . 'skillsprint_achievements',
        $wpdb->prefix . 'skillsprint_user_points',
        $wpdb->prefix . 'skillsprint_streaks',
    );
    
    foreach ($tables as $table) {
        $wpdb->query("DROP TABLE IF EXISTS $table");
    }
    
    // Delete plugin options
    delete_option('skillsprint_settings');
    delete_option('skillsprint_db_version');
    
    // Clear any cached data that has been cached
    wp_cache_flush();
}

// Flush rewrite rules to remove custom post type rules
flush_rewrite_rules();