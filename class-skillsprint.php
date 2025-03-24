<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/includes
 */

class SkillSprint {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      SkillSprint_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->version = SKILLSPRINT_VERSION;
        $this->plugin_name = 'skillsprint';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_post_types();
        $this->define_quiz_hooks();
        $this->define_progress_hooks();
        $this->define_gamification_hooks();
        $this->define_access_hooks();
        $this->define_dashboard_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        // Load only essential classes in lite mode
        require_once SKILLSPRINT_PLUGIN_DIR . 'includes/class-skillsprint-loader.php';
        require_once SKILLSPRINT_PLUGIN_DIR . 'includes/class-skillsprint-i18n.php';
        require_once SKILLSPRINT_PLUGIN_DIR . 'includes/class-skillsprint-post-types.php';
        
        // Optional components based on lite mode
        if (!defined('SKILLSPRINT_LITE_MODE') || !SKILLSPRINT_LITE_MODE) {
            require_once SKILLSPRINT_PLUGIN_DIR . 'includes/class-skillsprint-db.php';
            require_once SKILLSPRINT_PLUGIN_DIR . 'includes/class-skillsprint-blueprint.php';
            require_once SKILLSPRINT_PLUGIN_DIR . 'includes/class-skillsprint-quiz.php';
            require_once SKILLSPRINT_PLUGIN_DIR . 'includes/class-skillsprint-progress.php';
            
            if (!defined('SKILLSPRINT_DISABLE_GAMIFICATION') || !SKILLSPRINT_DISABLE_GAMIFICATION) {
                require_once SKILLSPRINT_PLUGIN_DIR . 'includes/class-skillsprint-gamification.php';
            }
            
            require_once SKILLSPRINT_PLUGIN_DIR . 'includes/class-skillsprint-access.php';
            require_once SKILLSPRINT_PLUGIN_DIR . 'includes/class-skillsprint-dashboard.php';
            require_once SKILLSPRINT_PLUGIN_DIR . 'admin/class-skillsprint-admin.php';
        }
        
        require_once SKILLSPRINT_PLUGIN_DIR . 'public/class-skillsprint-public.php';
    
        $this->loader = new SkillSprint_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new SkillSprint_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new SkillSprint_Admin($this->get_plugin_name(), $this->get_version());

        // Admin scripts and styles
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        
        // Admin menu
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        
        // Admin notices
        $this->loader->add_action('admin_notices', $plugin_admin, 'display_admin_notices');
        
        // Admin ajax actions
        $this->loader->add_action('wp_ajax_skillsprint_save_blueprint_data', $plugin_admin, 'ajax_save_blueprint_data');
        $this->loader->add_action('wp_ajax_skillsprint_save_quiz_data', $plugin_admin, 'ajax_save_quiz_data');
        $this->loader->add_action('wp_ajax_skillsprint_get_user_progress', $plugin_admin, 'ajax_get_user_progress');
        
        // Plugin settings
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
        
        // Dashboard widgets
        $this->loader->add_action('wp_dashboard_setup', $plugin_admin, 'add_dashboard_widgets');
        
        // Custom admin columns for blueprints
        $this->loader->add_filter('manage_blueprint_posts_columns', $plugin_admin, 'add_blueprint_columns');
        $this->loader->add_action('manage_blueprint_posts_custom_column', $plugin_admin, 'display_blueprint_column_content', 10, 2);
        
        // Metaboxes
        $this->loader->add_action('add_meta_boxes', $plugin_admin, 'add_meta_boxes');
        $this->loader->add_action('save_post', $plugin_admin, 'save_metabox_data');
        
        // Welcome page redirect after activation
        $this->loader->add_action('admin_init', $plugin_admin, 'welcome_page_redirect');
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new SkillSprint_Public($this->get_plugin_name(), $this->get_version());

        // Public scripts and styles
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        
        // Template overrides
        $this->loader->add_filter('single_template', $plugin_public, 'blueprint_single_template');
        $this->loader->add_filter('archive_template', $plugin_public, 'blueprint_archive_template');
        
        // Shortcodes
        $this->loader->add_action('init', $plugin_public, 'register_shortcodes');
        
        // Blueprint content filtering
        $this->loader->add_filter('the_content', $plugin_public, 'filter_blueprint_content');
        
        // User auth and registration
        $this->loader->add_action('wp_ajax_nopriv_skillsprint_register_user', $plugin_public, 'ajax_register_user');
        $this->loader->add_action('wp_ajax_nopriv_skillsprint_login_user', $plugin_public, 'ajax_login_user');
        
        // User dashboard endpoint
        $this->loader->add_action('init', $plugin_public, 'add_rewrite_endpoints');
        $this->loader->add_filter('query_vars', $plugin_public, 'add_query_vars');
        $this->loader->add_action('template_redirect', $plugin_public, 'dashboard_template_redirect');
    }

    /**
     * Register all of the hooks related to custom post types.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_post_types() {
        $post_types = new SkillSprint_Post_Types();
        
        // Register post types and taxonomies
        $this->loader->add_action('init', $post_types, 'register_post_types');
        $this->loader->add_action('init', $post_types, 'register_taxonomies');
    }

    /**
     * Register all of the hooks related to quiz functionality.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_quiz_hooks() {
        $quiz = new SkillSprint_Quiz();
        
        // AJAX quiz submission handling
        $this->loader->add_action('wp_ajax_skillsprint_submit_quiz', $quiz, 'ajax_submit_quiz');
        $this->loader->add_action('wp_ajax_skillsprint_get_quiz_results', $quiz, 'ajax_get_quiz_results');
    }

    /**
     * Register all of the hooks related to progress tracking.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_progress_hooks() {
        $progress = new SkillSprint_Progress();
        
        // Record user progress
        $this->loader->add_action('wp_ajax_skillsprint_mark_day_started', $progress, 'ajax_mark_day_started');
        $this->loader->add_action('wp_ajax_skillsprint_mark_day_completed', $progress, 'ajax_mark_day_completed');
        $this->loader->add_action('wp_ajax_skillsprint_get_user_blueprint_progress', $progress, 'ajax_get_user_blueprint_progress');
    }

    /**
     * Register all of the hooks related to gamification.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_gamification_hooks() {
        $gamification = new SkillSprint_Gamification();
        
        // Handle achievements and points
        $this->loader->add_action('skillsprint_day_completed', $gamification, 'award_day_completion_points', 10, 3);
        $this->loader->add_action('skillsprint_quiz_completed', $gamification, 'award_quiz_points', 10, 4);
        $this->loader->add_action('skillsprint_blueprint_completed', $gamification, 'award_blueprint_completion_points', 10, 2);
        
        // Streaks tracking
        $this->loader->add_action('skillsprint_day_completed', $gamification, 'update_user_streak', 10, 2);
        
        // Achievement checks
        $this->loader->add_action('skillsprint_day_completed', $gamification, 'check_day_achievements', 10, 3);
        $this->loader->add_action('skillsprint_blueprint_completed', $gamification, 'check_blueprint_achievements', 10, 2);
        
        // AJAX leaderboard
        $this->loader->add_action('wp_ajax_skillsprint_get_leaderboard', $gamification, 'ajax_get_leaderboard');
        $this->loader->add_action('wp_ajax_nopriv_skillsprint_get_leaderboard', $gamification, 'ajax_get_leaderboard');
    }

    /**
     * Register all of the hooks related to access control.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_access_hooks() {
        $access = new SkillSprint_Access();
        
        // Control content access
        $this->loader->add_filter('skillsprint_can_access_day', $access, 'can_user_access_day', 10, 3);
        $this->loader->add_filter('skillsprint_can_access_blueprint', $access, 'can_user_access_blueprint', 10, 2);
        
        // Login/registration modals
        $this->loader->add_action('wp_footer', $access, 'add_login_registration_modals');
    }

    /**
     * Register all of the hooks related to user dashboard.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_dashboard_hooks() {
        $dashboard = new SkillSprint_Dashboard();
        
        // Dashboard data
        $this->loader->add_action('wp_ajax_skillsprint_get_dashboard_data', $dashboard, 'ajax_get_dashboard_data');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    SkillSprint_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

}