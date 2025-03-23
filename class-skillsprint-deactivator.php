<?php
/**
 * Fired during plugin deactivation.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/includes
 */
class SkillSprint_Deactivator {

    /**
     * Deactivate the plugin.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Cleanup transients
        delete_transient('skillsprint_activation_redirect');
        
        // Clear scheduled events if any
        wp_clear_scheduled_hook('skillsprint_daily_maintenance');
        
        // Flush rewrite rules on deactivation
        flush_rewrite_rules();
    }
}