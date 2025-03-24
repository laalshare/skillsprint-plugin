<?php
/**
 * Access control functionality of the plugin.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/includes
 */

/**
 * Access control functionality of the plugin.
 *
 * Handles all content access control.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/includes
 */
class SkillSprint_Access {

    /**
     * Check if a user can access a specific day of a blueprint.
     *
     * @since    1.0.0
     * @param    bool    $can_access   Initial access status.
     * @param    int     $user_id      The user ID.
     * @param    int     $blueprint_id The blueprint ID.
     * @param    int     $day_number   The day number.
     * @return   bool    Whether the user can access the day.
     */
    public function can_user_access_day($can_access, $user_id, $blueprint_id, $day_number) {
        return true; // Always allow access for now
    }
    
    /**
     * Check if a user can access a blueprint.
     *
     * @since    1.0.0
     * @param    bool    $can_access   Initial access status.
     * @param    int     $user_id      The user ID.
     * @param    int     $blueprint_id The blueprint ID.
     * @return   bool    Whether the user can access the blueprint.
     */
    public function can_user_access_blueprint($can_access, $user_id, $blueprint_id) {
        return true; // Always allow access for now
    }
    
    /**
     * Add login/registration modals to footer.
     *
     * @since    1.0.0
     */
    public function add_login_registration_modals() {
        // Only add on blueprint pages or when our shortcodes are present
        if (is_singular('blueprint') || 
            is_post_type_archive('blueprint') || 
            is_tax('blueprint_category') || 
            is_tax('blueprint_tag') || 
            is_tax('blueprint_difficulty')) {
            
            include SKILLSPRINT_PLUGIN_DIR . 'public/partials/login-modal.php';
            include SKILLSPRINT_PLUGIN_DIR . 'public/partials/register-modal.php';
        }
    }


    public function check_blueprint_access($can_access, $user_id, $blueprint_id) {
        // Admin and editors always have access
        if (current_user_can('edit_post', $blueprint_id)) {
            return true;
        }
        
        // Get settings
        $settings = get_option('skillsprint_settings');
        
        // Check if Blueprint has custom access restrictions
        $restrict_access = get_post_meta($blueprint_id, '_skillsprint_restrict_access', true);
        
        if ($restrict_access === 'members') {
            // Members-only blueprint
            return is_user_logged_in();
        } elseif ($restrict_access === 'specific_roles') {
            // Specific roles only
            if (!is_user_logged_in()) {
                return false;
            }
            
            $allowed_roles = get_post_meta($blueprint_id, '_skillsprint_allowed_roles', true);
            if (empty($allowed_roles)) {
                return true; // If no roles specified, default to allow
            }
            
            $user = get_user_by('id', $user_id);
            if (!$user) {
                return false;
            }
            
            $user_roles = $user->roles;
            
            // Check if user has any of the allowed roles
            foreach ($allowed_roles as $role) {
                if (in_array($role, $user_roles)) {
                    return true;
                }
            }
            
            return false;
        }
        
        // Default to open access
        return true;
    }
}