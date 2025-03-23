<?php
/**
 * Fired during plugin activation.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/includes
 */
class SkillSprint_Activator {

    /**
     * Activate the plugin.
     *
     * Create necessary database tables and initialize settings
     *
     * @since    1.0.0
     */
    public static function activate() {
        self::create_tables();
        self::add_roles_and_capabilities();
        self::create_default_settings();
        
        // Set the activation flag to redirect to welcome page
        set_transient('skillsprint_activation_redirect', true, 30);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Create database tables
     */
    private static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table names
        $progress_table = $wpdb->prefix . 'skillsprint_progress';
        $quiz_responses_table = $wpdb->prefix . 'skillsprint_quiz_responses';
        $achievements_table = $wpdb->prefix . 'skillsprint_achievements';
        $user_points_table = $wpdb->prefix . 'skillsprint_user_points';
        $streaks_table = $wpdb->prefix . 'skillsprint_streaks';
        
        // Create tables
        $progress_sql = "CREATE TABLE $progress_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            blueprint_id bigint(20) NOT NULL,
            day_number int(11) NOT NULL,
            progress_status varchar(20) NOT NULL DEFAULT 'not_started',
            date_started datetime DEFAULT NULL,
            date_completed datetime DEFAULT NULL,
            time_spent int(11) DEFAULT 0,
            notes longtext DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY blueprint_id (blueprint_id),
            UNIQUE KEY user_blueprint_day (user_id, blueprint_id, day_number)
        ) $charset_collate;";
        
        // Quiz Responses Table
        $quiz_responses_sql = "CREATE TABLE $quiz_responses_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            blueprint_id bigint(20) NOT NULL,
            quiz_id varchar(50) NOT NULL,
            question_id varchar(50) NOT NULL,
            user_answer longtext NOT NULL,
            is_correct tinyint(1) DEFAULT 0,
            points_earned int(11) DEFAULT 0,
            attempt_number int(11) DEFAULT 1,
            date_submitted datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY blueprint_id (blueprint_id),
            KEY quiz_id (quiz_id)
        ) $charset_collate;";
        
        // Achievements Table
        $achievements_sql = "CREATE TABLE $achievements_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            achievement_id varchar(50) NOT NULL,
            date_earned datetime DEFAULT NULL,
            meta longtext DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            UNIQUE KEY user_achievement (user_id, achievement_id)
        ) $charset_collate;";
        
        // User Points Table
        $user_points_sql = "CREATE TABLE $user_points_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            blueprint_id bigint(20) DEFAULT NULL,
            points int(11) NOT NULL DEFAULT 0,
            source varchar(50) NOT NULL,
            description varchar(255) DEFAULT NULL,
            date_earned datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY blueprint_id (blueprint_id)
        ) $charset_collate;";
        
        // Streaks Table
        $streaks_sql = "CREATE TABLE $streaks_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            current_streak int(11) NOT NULL DEFAULT 0,
            longest_streak int(11) NOT NULL DEFAULT 0,
            last_activity_date date DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY user_id (user_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Create the tables
        dbDelta($progress_sql);
        dbDelta($quiz_responses_sql);
        dbDelta($achievements_sql);
        dbDelta($user_points_sql);
        dbDelta($streaks_sql);
        
        // Save database version
        add_option('skillsprint_db_version', SKILLSPRINT_DB_VERSION);
    }
    
    /**
     * Add custom roles and capabilities
     */
    private static function add_roles_and_capabilities() {
        // Blueprint Viewer role - can access but not create blueprints
        add_role(
            'blueprint_learner',
            __('Blueprint Learner', 'skillsprint'),
            array(
                'read' => true,
                'read_blueprint' => true,
            )
        );
        
        // Add blueprint capabilities to administrator role
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->add_cap('read_blueprint');
            $admin_role->add_cap('edit_blueprint');
            $admin_role->add_cap('edit_blueprints');
            $admin_role->add_cap('edit_others_blueprints');
            $admin_role->add_cap('publish_blueprints');
            $admin_role->add_cap('read_private_blueprints');
            $admin_role->add_cap('delete_blueprint');
            $admin_role->add_cap('manage_skillsprint');
        }
        
        // Add blueprint capabilities to editor role
        $editor_role = get_role('editor');
        if ($editor_role) {
            $editor_role->add_cap('read_blueprint');
            $editor_role->add_cap('edit_blueprint');
            $editor_role->add_cap('edit_blueprints');
            $editor_role->add_cap('edit_others_blueprints');
            $editor_role->add_cap('publish_blueprints');
            $editor_role->add_cap('read_private_blueprints');
        }
    }
    
    /**
     * Create default settings
     */
    private static function create_default_settings() {
        $default_settings = array(
            'points_per_day_completion' => 10,
            'points_per_quiz_correct' => 5,
            'points_per_blueprint_completion' => 50,
            'streak_bonus_multiplier' => 1.5,
            'default_quiz_pass_score' => 70,
            'max_quiz_attempts' => 3,
            'gamification_enabled' => true,
            'leaderboard_enabled' => true,
            'strict_progression' => true,
            'days_free_access' => 2,
        );
        
        add_option('skillsprint_settings', $default_settings);
    }
}