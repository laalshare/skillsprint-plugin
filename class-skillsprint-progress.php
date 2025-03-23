<?php
/**
 * Progress tracking functionality of the plugin.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/includes
 */

/**
 * Progress tracking functionality of the plugin.
 *
 * Handles all user progress tracking.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/includes
 */
class SkillSprint_Progress {

    /**
     * AJAX handler for marking a day as started.
     *
     * @since    1.0.0
     */
    public function ajax_mark_day_started() {
        // Check nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skillsprint_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skillsprint' ) ) );
        }
        
        // Check user login
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in to track progress.', 'skillsprint' ) ) );
        }
        
        // Get parameters
        $user_id = get_current_user_id();
        $blueprint_id = isset( $_POST['blueprint_id'] ) ? intval( $_POST['blueprint_id'] ) : 0;
        $day_number = isset( $_POST['day_number'] ) ? intval( $_POST['day_number'] ) : 0;
        
        if ( ! $blueprint_id || ! $day_number ) {
            wp_send_json_error( array( 'message' => __( 'Invalid blueprint or day.', 'skillsprint' ) ) );
        }
        
        // Check if user can access this day
        $can_access = apply_filters( 'skillsprint_can_access_day', true, $user_id, $blueprint_id, $day_number );
        
        if ( ! $can_access ) {
            wp_send_json_error( array( 'message' => __( 'You do not have access to this day.', 'skillsprint' ) ) );
        }
        
        // Mark day as started
        $result = SkillSprint_DB::mark_day_started( $user_id, $blueprint_id, $day_number );
        
        if ( $result ) {
            wp_send_json_success( array( 
                'message' => __( 'Day started successfully!', 'skillsprint' ),
                'day_number' => $day_number,
                'status' => 'in_progress'
            ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Error marking day as started.', 'skillsprint' ) ) );
        }
    }
    
    /**
     * AJAX handler for marking a day as completed.
     *
     * @since    1.0.0
     */
    public function ajax_mark_day_completed() {
        // Check nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skillsprint_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skillsprint' ) ) );
        }
        
        // Check user login
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in to track progress.', 'skillsprint' ) ) );
        }
        
        // Get parameters
        $user_id = get_current_user_id();
        $blueprint_id = isset( $_POST['blueprint_id'] ) ? intval( $_POST['blueprint_id'] ) : 0;
        $day_number = isset( $_POST['day_number'] ) ? intval( $_POST['day_number'] ) : 0;
        $notes = isset( $_POST['notes'] ) ? sanitize_textarea_field( $_POST['notes'] ) : '';
        
        if ( ! $blueprint_id || ! $day_number ) {
            wp_send_json_error( array( 'message' => __( 'Invalid blueprint or day.', 'skillsprint' ) ) );
        }
        
        // Check if user can access this day
        $can_access = apply_filters( 'skillsprint_can_access_day', true, $user_id, $blueprint_id, $day_number );
        
        if ( ! $can_access ) {
            wp_send_json_error( array( 'message' => __( 'You do not have access to this day.', 'skillsprint' ) ) );
        }
        
        // Check if there's a quiz for this day that needs to be passed
        $day_data = SkillSprint_DB::get_blueprint_day_data( $blueprint_id, $day_number );
        
        if ( $day_data && isset( $day_data['quiz_id'] ) && ! empty( $day_data['quiz_id'] ) ) {
            // Get quiz data
            $quiz_id = $day_data['quiz_id'];
            $quiz_data = get_post_meta( $blueprint_id, '_skillsprint_quiz_' . $quiz_id, true );
            
            if ( $quiz_data ) {
                // Check if user has passed the quiz
                $passed = SkillSprint_DB::did_user_pass_quiz( $user_id, $blueprint_id, $quiz_id );
                
                if ( ! $passed ) {
                    wp_send_json_error( array( 'message' => __( 'You need to pass the quiz to complete this day.', 'skillsprint' ) ) );
                }
            }
        }
        
        // Mark day as completed
        $result = SkillSprint_DB::mark_day_completed( $user_id, $blueprint_id, $day_number, $notes );
        
        if ( $result ) {
            // Check if the entire blueprint is completed
            $completion_percentage = SkillSprint_DB::get_blueprint_completion_percentage( $user_id, $blueprint_id );
            $blueprint_completed = $completion_percentage == 100;
            
            // Get next day if available
            $days_data = SkillSprint_DB::get_blueprint_days_data( $blueprint_id );
            $next_day = null;
            
            if ( $day_number < count( $days_data ) ) {
                $next_day = $day_number + 1;
            }
            
            wp_send_json_success( array( 
                'message' => __( 'Day completed successfully!', 'skillsprint' ),
                'day_number' => $day_number,
                'status' => 'completed',
                'next_day' => $next_day,
                'blueprint_completed' => $blueprint_completed,
                'completion_percentage' => $completion_percentage
            ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Error marking day as completed.', 'skillsprint' ) ) );
        }
    }
    
    /**
     * AJAX handler for getting user blueprint progress.
     *
     * @since    1.0.0
     */
    public function ajax_get_user_blueprint_progress() {
        // Check nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skillsprint_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skillsprint' ) ) );
        }
        
        // Check user login
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in to view progress.', 'skillsprint' ) ) );
        }
        
        // Get parameters
        $user_id = get_current_user_id();
        $blueprint_id = isset( $_POST['blueprint_id'] ) ? intval( $_POST['blueprint_id'] ) : 0;
        
        if ( ! $blueprint_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid blueprint.', 'skillsprint' ) ) );
        }
        
        // Get progress data
        $progress = SkillSprint_DB::get_user_blueprint_progress( $user_id, $blueprint_id );
        $completion_percentage = SkillSprint_DB::get_blueprint_completion_percentage( $user_id, $blueprint_id );
        
        wp_send_json_success( array( 
            'progress' => $progress,
            'completion_percentage' => $completion_percentage
        ) );
    }
}