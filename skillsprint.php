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


add_action('skillsprint_day_completed', array($this, 'day_completed_hook'), 10, 3);
add_action('skillsprint_blueprint_completed', array($this, 'blueprint_completed_hook'), 10, 2);
add_action('skillsprint_achievement_awarded', array($this, 'achievement_awarded_hook'), 10, 4);
add_action('skillsprint_quiz_completed', array($this, 'quiz_completed_hook'), 10, 4);
add_action('skillsprint_points_added', array($this, 'points_added_hook'), 10, 4);

// Custom filters
add_filter('skillsprint_can_access_day', array($this, 'can_access_day_filter'), 10, 4);
add_filter('skillsprint_quiz_result', array($this, 'filter_quiz_result'), 10, 3);
add_filter('skillsprint_badge_info', array($this, 'filter_badge_info'), 10, 2);

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


    /**
 * Hook callback for day completion
 * 
 * @param int $user_id User ID
 * @param int $blueprint_id Blueprint ID
 * @param int $day_number Day number
 */
public function day_completed_hook($user_id, $blueprint_id, $day_number) {
    // This hook is called when a user completes a day
    // Useful for integration with other systems, notifications, etc.
    
    // Get day information
    $day_data = SkillSprint_DB::get_blueprint_day_data($blueprint_id, $day_number);
    $blueprint_title = get_the_title($blueprint_id);
    
    // Log completion
    do_action('skillsprint_log', 
        sprintf(
            __('User %d completed Day %d of %s', 'skillsprint'),
            $user_id,
            $day_number,
            $blueprint_title
        )
    );
    
    // Allow other plugins to hook into this event
    do_action('skillsprint_day_completed_extra', $user_id, $blueprint_id, $day_number, $day_data);
}

/**
 * Hook callback for blueprint completion
 * 
 * @param int $user_id User ID
 * @param int $blueprint_id Blueprint ID
 */
public function blueprint_completed_hook($user_id, $blueprint_id) {
    // This hook is called when a user completes an entire blueprint
    
    // Get blueprint information
    $blueprint_title = get_the_title($blueprint_id);
    $blueprint_author = get_post_field('post_author', $blueprint_id);
    
    // Log completion
    do_action('skillsprint_log', 
        sprintf(
            __('User %d completed blueprint %s', 'skillsprint'),
            $user_id,
            $blueprint_title
        )
    );
    
    // Send notification to blueprint author
    if (apply_filters('skillsprint_notify_author_on_completion', true, $blueprint_id, $user_id)) {
        $author_email = get_the_author_meta('user_email', $blueprint_author);
        $user_data = get_userdata($user_id);
        
        if ($author_email && $user_data) {
            $subject = sprintf(
                __('[%s] %s completed your blueprint %s', 'skillsprint'),
                get_bloginfo('name'),
                $user_data->display_name,
                $blueprint_title
            );
            
            $message = sprintf(
                __("Hello,\n\n%s has completed your blueprint \"%s\".\n\nYou can view their progress in the admin dashboard.\n\nBest regards,\n%s", 'skillsprint'),
                $user_data->display_name,
                $blueprint_title,
                get_bloginfo('name')
            );
            
            wp_mail($author_email, $subject, $message);
        }
    }
    
    // Allow other plugins to hook into this event
    do_action('skillsprint_blueprint_completed_extra', $user_id, $blueprint_id);
}

/**
 * Hook callback for achievement awarded
 * 
 * @param int $user_id User ID
 * @param string $achievement_id Achievement ID
 * @param array $badge Badge info
 * @param array $meta Achievement metadata
 */
public function achievement_awarded_hook($user_id, $achievement_id, $badge, $meta) {
    // This hook is called when a user earns an achievement
    
    // Log achievement
    do_action('skillsprint_log', 
        sprintf(
            __('User %d earned achievement %s', 'skillsprint'),
            $user_id,
            $badge['name']
        )
    );
    
    // Display achievement notification
    if (apply_filters('skillsprint_show_achievement_notification', true, $user_id, $achievement_id)) {
        $gamification = new SkillSprint_Gamification();
        $gamification->display_achievement_notification($user_id, $achievement_id, $badge, $meta);
    }
}

/**
 * Hook callback for quiz completion
 * 
 * @param int $user_id User ID
 * @param int $blueprint_id Blueprint ID
 * @param string $quiz_id Quiz ID
 * @param bool $passed Whether the quiz was passed
 */
public function quiz_completed_hook($user_id, $blueprint_id, $quiz_id, $passed) {
    // Get quiz information
    $quiz_data = get_post_meta($blueprint_id, '_skillsprint_quiz_' . $quiz_id, true);
    $quiz_title = isset($quiz_data['title']) ? $quiz_data['title'] : $quiz_id;
    $blueprint_title = get_the_title($blueprint_id);
    
    // Log quiz completion
    do_action('skillsprint_log', 
        sprintf(
            __('User %d %s quiz %s in blueprint %s', 'skillsprint'),
            $user_id,
            $passed ? __('passed', 'skillsprint') : __('failed', 'skillsprint'),
            $quiz_title,
            $blueprint_title
        )
    );
    
    // Check for achievements
    if ($passed) {
        $gamification = new SkillSprint_Gamification();
        
        // Get quiz score
        $quiz_result = SkillSprint_DB::calculate_quiz_score($user_id, $blueprint_id, $quiz_id);
        
        // Check for perfect quiz achievement
        $gamification->check_perfect_quiz_achievement($user_id, $blueprint_id, $quiz_id, $quiz_result);
    }
    
    // Allow other plugins to hook into this event
    do_action('skillsprint_quiz_completed_extra', $user_id, $blueprint_id, $quiz_id, $passed, $quiz_data);
}

/**
 * Hook callback for points added
 * 
 * @param int $user_id User ID
 * @param int $points Points amount
 * @param string $source Points source
 * @param int $blueprint_id Blueprint ID (optional)
 */
public function points_added_hook($user_id, $points, $source, $blueprint_id) {
    // This hook is called when points are added to a user
    
    // Log points
    do_action('skillsprint_log', 
        sprintf(
            __('User %d earned %d points from %s', 'skillsprint'),
            $user_id,
            $points,
            $source
        )
    );
    
    // Update user ranking
    $this->update_user_ranking($user_id);
    
    // Allow other plugins to hook into this event
    do_action('skillsprint_points_added_extra', $user_id, $points, $source, $blueprint_id);
}

/**
 * Filter for day access control
 * 
 * @param bool $can_access Default access status
 * @param int $user_id User ID
 * @param int $blueprint_id Blueprint ID
 * @param int $day_number Day number
 * @return bool Updated access status
 */
public function can_access_day_filter($can_access, $user_id, $blueprint_id, $day_number) {
    // Allow plugins to override the default access control
    
    // Example: Check if blueprint belongs to a premium membership
    if (function_exists('is_premium_blueprint') && is_premium_blueprint($blueprint_id)) {
        // Check if user has premium membership
        if (function_exists('user_has_premium_membership') && !user_has_premium_membership($user_id)) {
            return false;
        }
    }
    
    return $can_access;
}

/**
 * Filter for quiz results
 * 
 * @param array $result Quiz result data
 * @param int $user_id User ID
 * @param int $blueprint_id Blueprint ID
 * @return array Updated result data
 */
public function filter_quiz_result($result, $user_id, $blueprint_id) {
    // Allow plugins to modify quiz results
    
    // Example: Add bonus points for speed
    if (isset($result['time_taken']) && $result['time_taken'] < 60) { // Completed in less than 60 seconds
        $result['points'] += 5; // Add 5 bonus points
        $result['bonus_points'] = 5;
        $result['bonus_reason'] = __('Speed bonus', 'skillsprint');
    }
    
    return $result;
}

/**
 * Filter for badge information
 * 
 * @param array $badge Badge info
 * @param string $achievement_id Achievement ID
 * @return array Updated badge info
 */
public function filter_badge_info($badge, $achievement_id) {
    // Allow plugins to modify badge information
    
    // Example: Enhance certain badges for special events
    if (function_exists('is_special_event_active') && is_special_event_active()) {
        // Double points for badges during special events
        $badge['points'] *= 2;
        $badge['name'] = '⭐ ' . $badge['name']; // Add star to badge name
    }
    
    return $badge;
}

/**
 * Update user ranking
 * 
 * @param int $user_id User ID
 */
private function update_user_ranking() {
    // Calculate rankings based on points
    global $wpdb;
    
    $points_table = $wpdb->prefix . 'skillsprint_user_points';
    
    // Get all users with points
    $users_with_points = $wpdb->get_results(
        "SELECT user_id, SUM(points) as total_points
        FROM $points_table
        GROUP BY user_id
        ORDER BY total_points DESC",
        ARRAY_A
    );
    
    // Update ranking metadata for each user
    foreach ($users_with_points as $index => $user) {
        $rank = $index + 1;
        update_user_meta($user['user_id'], '_skillsprint_rank', $rank);
        update_user_meta($user['user_id'], '_skillsprint_total_points', $user['total_points']);
    }
}




}

// Initialize plugin
$skillsprint = new SkillSprint();