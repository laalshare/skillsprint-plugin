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
 * 
 * @package SkillSprint
 */



 // Add these near the top of the file after plugin header
define('SKILLSPRINT_LITE_MODE', true); // Enable lite mode to reduce memory usage
define('SKILLSPRINT_DISABLE_GAMIFICATION', true); // Disable gamification features

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
    require_once SKILLSPRINT_PLUGIN_DIR . 'includes/class-skillsprint-activator.php';
    SkillSprint_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_skillsprint() {
    require_once SKILLSPRINT_PLUGIN_DIR . 'includes/class-skillsprint-deactivator.php';
    SkillSprint_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_skillsprint');

register_deactivation_hook(__FILE__, 'deactivate_skillsprint');


// Add a one-time flush rewrite rules on plugin activation
register_activation_hook(__FILE__, function() {
    flush_rewrite_rules();
});

// Also flush rules one time after this update
add_action('init', function() {
    if (get_option('skillsprint_flush_needed', false)) {
        flush_rewrite_rules();
        delete_option('skillsprint_flush_needed');
    }
});

// Set the option to flush - run once
add_action('plugins_loaded', function() {
    update_option('skillsprint_flush_needed', true);
});


/**
 * Increase memory limit for plugin operations.
 */
function skillsprint_increase_memory_limit() {
    // Try to increase memory limit for plugin operations
    $current_limit = ini_get('memory_limit');
    $current_limit_int = (int)$current_limit;
    
    // Only increase if current limit is less than 256M
    if ($current_limit_int < 256) {
        @ini_set('memory_limit', '256M');
    }
}
add_action('init', 'skillsprint_increase_memory_limit');





/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once SKILLSPRINT_PLUGIN_DIR . 'includes/class-skillsprint.php';

/**
 * Begins execution of the plugin.
 */
function run_skillsprint() {
    // Increase memory limit
    @ini_set('memory_limit', '256M');
    
    // Initialize plugin
    $plugin = new SkillSprint();
    
    // Only load necessary components in lite mode
    if (defined('SKILLSPRINT_LITE_MODE') && SKILLSPRINT_LITE_MODE) {
        $plugin->load_dependencies();
        $plugin->set_locale();
        $plugin->define_post_types();
        $plugin->define_public_hooks();
    } else {
        $plugin->run(); // Full initialization
    }
}

// Let's get started!
run_skillsprint();