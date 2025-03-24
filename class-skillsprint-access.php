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
    public function can_user_access_day( $can_access, $user_id, $blueprint_id, $day_number ) {
        // Get settings
        $settings = get_option( 'skillsprint_settings' );
        $days_free_access = isset( $settings['days_free_access'] ) ? intval( $settings['days_free_access'] ) : 2;
        $strict_progression = isset( $settings['strict_progression'] ) ? (bool) $settings['strict_progression'] : true;
        
        // Check if day is within free access range
        if ( $day_number <= $days_free_access ) {
            return true;
        }
        
        // If user is not logged in, deny access to days beyond free access
        if ( ! $user_id ) {
            return false;
        }
        
        // Check if user has appropriate role/capability
        $user = get_user_by( 'id', $user_id );
        
        if ( ! $user ) {
            return false;
        }
        
        // Admins and editors can always access all content
        if ( $user->has_cap( 'edit_others_blueprints' ) ) {
            return true;
        }
        
        // If strict progression is enabled, check if previous days are completed
        if ( $strict_progression && $day_number > 1 ) {
            for ( $i = 1; $i < $day_number; $i++ ) {
                $day_progress = SkillSprint_DB::get_user_day_progress( $user_id, $blueprint_id, $i );
                
                if ( ! $day_progress || $day_progress['progress_status'] !== 'completed' ) {
                    return false;
                }
            }
        }
        
        return true;
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
    public function can_user_access_blueprint( $can_access, $user_id, $blueprint_id ) {
        // Everyone can access the blueprint index page
        // Individual day access is controlled by can_user_access_day
        return true;
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
        
        // Check if template files exist before including
        $login_modal = SKILLSPRINT_PLUGIN_DIR . 'public/partials/login-modal.php';
        $register_modal = SKILLSPRINT_PLUGIN_DIR . 'public/partials/register-modal.php';
        
        if (file_exists($login_modal)) {
            include $login_modal;
        }
        
        if (file_exists($register_modal)) {
            include $register_modal;
        }
    }
}}