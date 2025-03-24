<?php
/**
 * Plugin Name: SkillSprint 7-Day Learning Platform
 * Plugin URI: https://example.com/skillsprint
 * Description: Transform regular blog posts into interactive 7-day learning courses with progress tracking, quizzes, and gamification.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: skillsprint
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 */
define('SKILLSPRINT_VERSION', '1.0.0');
define('SKILLSPRINT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SKILLSPRINT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SKILLSPRINT_DB_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 */
function activate_skillsprint() {
    // Simple activation for now
    update_option('skillsprint_flush_needed', true);
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_skillsprint() {
    flush_rewrite_rules();
}

register_activation_hook(__FILE__, 'activate_skillsprint');
register_deactivation_hook(__FILE__, 'deactivate_skillsprint');

/**
 * Flush rewrite rules when needed
 */
function skillsprint_flush_rewrite_rules() {
    $flush_flag = get_option('skillsprint_flush_needed', false);
    
    if ($flush_flag) {
        flush_rewrite_rules();
        update_option('skillsprint_flush_needed', false);
    }
}
add_action('init', 'skillsprint_flush_rewrite_rules', 20);

/**
 * Main SkillSprint class
 */
class SkillSprint {
    /**
     * Initialize the plugin
     */
    public function __construct() {
        // Register post type
        add_action('init', array($this, 'register_post_types'));
        add_action('init', array($this, 'register_taxonomies'));
        
        // Register scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Template filters
        add_filter('single_template', array($this, 'blueprint_single_template'));
        add_filter('archive_template', array($this, 'blueprint_archive_template'));
    }
    
    /**
     * Register post types.
     */
    public function register_post_types() {
        // 7-Day Blueprint Post Type
        $labels = array(
            'name'                  => _x('7-Day Blueprints', 'Post Type General Name', 'skillsprint'),
            'singular_name'         => _x('Blueprint', 'Post Type Singular Name', 'skillsprint'),
            'menu_name'             => __('7-Day Blueprints', 'skillsprint'),
            'name_admin_bar'        => __('Blueprint', 'skillsprint'),
            'all_items'             => __('All Blueprints', 'skillsprint'),
            'add_new_item'          => __('Add New Blueprint', 'skillsprint'),
            'add_new'               => __('Add New', 'skillsprint'),
            'new_item'              => __('New Blueprint', 'skillsprint'),
            'edit_item'             => __('Edit Blueprint', 'skillsprint'),
            'update_item'           => __('Update Blueprint', 'skillsprint'),
            'view_item'             => __('View Blueprint', 'skillsprint'),
            'search_items'          => __('Search Blueprint', 'skillsprint')
        );
        
        $args = array(
            'label'                 => __('Blueprint', 'skillsprint'),
            'description'           => __('7-Day Learning Blueprint', 'skillsprint'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'author'),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-welcome-learn-more',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'show_in_rest'          => true,
            'rewrite'               => array(
                'slug'              => 'blueprint',
                'with_front'        => false,
            ),
        );
        
        register_post_type('blueprint', $args);
    }
    
    /**
     * Register taxonomies.
     */
    public function register_taxonomies() {
        // Blueprint Category Taxonomy
        $labels = array(
            'name'                       => _x('Blueprint Categories', 'Taxonomy General Name', 'skillsprint'),
            'singular_name'              => _x('Blueprint Category', 'Taxonomy Singular Name', 'skillsprint'),
            'menu_name'                  => __('Categories', 'skillsprint'),
            'all_items'                  => __('All Categories', 'skillsprint'),
            'parent_item'                => __('Parent Category', 'skillsprint'),
            'parent_item_colon'          => __('Parent Category:', 'skillsprint'),
            'new_item_name'              => __('New Category Name', 'skillsprint'),
            'add_new_item'               => __('Add New Category', 'skillsprint'),
            'edit_item'                  => __('Edit Category', 'skillsprint'),
            'update_item'                => __('Update Category', 'skillsprint'),
            'view_item'                  => __('View Category', 'skillsprint'),
            'separate_items_with_commas' => __('Separate categories with commas', 'skillsprint'),
            'add_or_remove_items'        => __('Add or remove categories', 'skillsprint'),
            'choose_from_most_used'      => __('Choose from the most used', 'skillsprint'),
            'popular_items'              => __('Popular Categories', 'skillsprint'),
            'search_items'               => __('Search Categories', 'skillsprint'),
            'not_found'                  => __('Not Found', 'skillsprint'),
        );
        
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,
            'rewrite'                    => array(
                'slug'                   => 'blueprint-category',
                'with_front'             => false,
            ),
        );
        
        register_taxonomy('blueprint_category', array('blueprint'), $args);
        
        // Blueprint Tag Taxonomy
        $labels = array(
            'name'                       => _x('Blueprint Tags', 'Taxonomy General Name', 'skillsprint'),
            'singular_name'              => _x('Blueprint Tag', 'Taxonomy Singular Name', 'skillsprint'),
            'menu_name'                  => __('Tags', 'skillsprint'),
            'all_items'                  => __('All Tags', 'skillsprint'),
            'parent_item'                => __('Parent Tag', 'skillsprint'),
            'parent_item_colon'          => __('Parent Tag:', 'skillsprint'),
            'new_item_name'              => __('New Tag Name', 'skillsprint'),
            'add_new_item'               => __('Add New Tag', 'skillsprint'),
            'edit_item'                  => __('Edit Tag', 'skillsprint'),
            'update_item'                => __('Update Tag', 'skillsprint'),
            'view_item'                  => __('View Tag', 'skillsprint'),
            'separate_items_with_commas' => __('Separate tags with commas', 'skillsprint'),
            'add_or_remove_items'        => __('Add or remove tags', 'skillsprint'),
            'choose_from_most_used'      => __('Choose from the most used', 'skillsprint'),
            'popular_items'              => __('Popular Tags', 'skillsprint'),
            'search_items'               => __('Search Tags', 'skillsprint'),
            'not_found'                  => __('Not Found', 'skillsprint'),
        );
        
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => false,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,
            'rewrite'                    => array(
                'slug'                   => 'blueprint-tag',
                'with_front'             => false,
            ),
        );
        
        register_taxonomy('blueprint_tag', array('blueprint'), $args);
        
        // Blueprint Difficulty Taxonomy
        $labels = array(
            'name'                       => _x('Difficulty Levels', 'Taxonomy General Name', 'skillsprint'),
            'singular_name'              => _x('Difficulty Level', 'Taxonomy Singular Name', 'skillsprint'),
            'menu_name'                  => __('Difficulty', 'skillsprint'),
            'all_items'                  => __('All Difficulty Levels', 'skillsprint'),
            'parent_item'                => __('Parent Difficulty Level', 'skillsprint'),
            'parent_item_colon'          => __('Parent Difficulty Level:', 'skillsprint'),
            'new_item_name'              => __('New Difficulty Level Name', 'skillsprint'),
            'add_new_item'               => __('Add New Difficulty Level', 'skillsprint'),
            'edit_item'                  => __('Edit Difficulty Level', 'skillsprint'),
            'update_item'                => __('Update Difficulty Level', 'skillsprint'),
            'view_item'                  => __('View Difficulty Level', 'skillsprint'),
            'separate_items_with_commas' => __('Separate difficulty levels with commas', 'skillsprint'),
            'add_or_remove_items'        => __('Add or remove difficulty levels', 'skillsprint'),
            'choose_from_most_used'      => __('Choose from the most used', 'skillsprint'),
            'popular_items'              => __('Popular Difficulty Levels', 'skillsprint'),
            'search_items'               => __('Search Difficulty Levels', 'skillsprint'),
            'not_found'                  => __('Not Found', 'skillsprint'),
        );
        
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => false,
            'show_in_rest'               => true,
            'rewrite'                    => array(
                'slug'                   => 'blueprint-difficulty',
                'with_front'             => false,
            ),
        );
        
        register_taxonomy('blueprint_difficulty', array('blueprint'), $args);
        
        // Create default difficulty levels if they don't exist
        $difficulty_levels = array(
            'beginner' => array(
                'name' => __('Beginner', 'skillsprint'),
                'slug' => 'beginner',
                'description' => __('For those new to the subject', 'skillsprint')
            ),
            'intermediate' => array(
                'name' => __('Intermediate', 'skillsprint'),
                'slug' => 'intermediate',
                'description' => __('For those with some experience', 'skillsprint')
            ),
            'advanced' => array(
                'name' => __('Advanced', 'skillsprint'),
                'slug' => 'advanced',
                'description' => __('For those with significant experience', 'skillsprint')
            ),
            'expert' => array(
                'name' => __('Expert', 'skillsprint'),
                'slug' => 'expert',
                'description' => __('For those with deep expertise', 'skillsprint')
            ),
        );
        
        foreach ($difficulty_levels as $difficulty) {
            if (!term_exists($difficulty['name'], 'blueprint_difficulty')) {
                wp_insert_term(
                    $difficulty['name'],
                    'blueprint_difficulty',
                    array(
                        'description' => $difficulty['description'],
                        'slug' => $difficulty['slug']
                    )
                );
            }
        }
    }
    
    /**
     * Register styles.
     */
    public function enqueue_styles() {
        wp_enqueue_style('dashicons');
        wp_enqueue_style('skillsprint-public', SKILLSPRINT_PLUGIN_URL . 'public/css/skillsprint-public.css', array(), SKILLSPRINT_VERSION);
    }
    
    /**
     * Register scripts.
     */
    public function enqueue_scripts() {
        wp_enqueue_script('skillsprint-public', SKILLSPRINT_PLUGIN_URL . 'public/js/skillsprint-public.js', array('jquery'), SKILLSPRINT_VERSION, true);
        
        // Pass basic data to script
        wp_localize_script('skillsprint-public', 'skillsprint', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('skillsprint_nonce'),
            'is_user_logged_in' => is_user_logged_in(),
            'user_id' => get_current_user_id()
        ));
    }
    
    /**
     * Custom blueprint single template.
     */
    public function blueprint_single_template($template) {
        if (is_singular('blueprint')) {
            // See if theme has template
            $theme_file = locate_template('single-blueprint.php');
            if ($theme_file) {
                return $theme_file;
            }
            
            // Check plugin template
            $plugin_file = SKILLSPRINT_PLUGIN_DIR . 'public/templates/single-blueprint.php';
            if (file_exists($plugin_file)) {
                return $plugin_file;
            }
        }
        return $template;
    }
    
    /**
     * Custom blueprint archive template.
     */
    public function blueprint_archive_template($template) {
        if (is_post_type_archive('blueprint')) {
            // See if theme has template
            $theme_file = locate_template('archive-blueprint.php');
            if ($theme_file) {
                return $theme_file;
            }
            
            // Check plugin template
            $plugin_file = SKILLSPRINT_PLUGIN_DIR . 'public/templates/archive-blueprint.php';
            if (file_exists($plugin_file)) {
                return $plugin_file;
            }
        }
        return $template;
    }
}

// Initialize plugin
$skillsprint = new SkillSprint();