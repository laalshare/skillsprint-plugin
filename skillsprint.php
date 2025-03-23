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

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once SKILLSPRINT_PLUGIN_DIR . 'includes/class-skillsprint.php';

/**
 * Begins execution of the plugin.
 */
function run_skillsprint() {
    $plugin = new SkillSprint();
    $plugin->run();
}

// Let's get started!
run_skillsprint();