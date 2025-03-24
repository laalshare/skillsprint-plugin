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


    /**
 * Get weekly progress for a user
 *
 * @param int $user_id The user ID
 * @return array Weekly progress stats
 */
public function get_weekly_progress($user_id) {
    global $wpdb;
    
    $progress_table = $wpdb->prefix . 'skillsprint_progress';
    $quiz_table = $wpdb->prefix . 'skillsprint_quiz_responses';
    
    // Get dates for this week (starts on Monday)
    $week_start = date('Y-m-d', strtotime('monday this week'));
    $week_end = date('Y-m-d', strtotime('sunday this week'));
    
    // Daily completed days count
    $days_completed = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT 
                DATE(date_completed) as completion_date, 
                COUNT(*) as completed_count 
            FROM 
                $progress_table 
            WHERE 
                user_id = %d AND 
                progress_status = 'completed' AND 
                date_completed BETWEEN %s AND %s 
            GROUP BY 
                DATE(date_completed)
            ORDER BY 
                completion_date ASC",
            $user_id,
            $week_start . ' 00:00:00',
            $week_end . ' 23:59:59'
        ),
        ARRAY_A
    );
    
    // Daily quiz attempts count
    $quiz_attempts = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT 
                DATE(date_submitted) as attempt_date, 
                COUNT(DISTINCT CONCAT(blueprint_id, quiz_id, attempt_number)) as attempt_count 
            FROM 
                $quiz_table 
            WHERE 
                user_id = %d AND 
                date_submitted BETWEEN %s AND %s 
            GROUP BY 
                DATE(date_submitted)
            ORDER BY 
                attempt_date ASC",
            $user_id,
            $week_start . ' 00:00:00',
            $week_end . ' 23:59:59'
        ),
        ARRAY_A
    );
    
    // Format data for a full week
    $days_of_week = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
    $week_data = array();
    
    for ($i = 0; $i < 7; $i++) {
        $date = date('Y-m-d', strtotime($week_start . " +$i days"));
        $day_label = $days_of_week[$i];
        
        $completed_count = 0;
        foreach ($days_completed as $day) {
            if ($day['completion_date'] === $date) {
                $completed_count = $day['completed_count'];
                break;
            }
        }
        
        $attempts_count = 0;
        foreach ($quiz_attempts as $attempt) {
            if ($attempt['attempt_date'] === $date) {
                $attempts_count = $attempt['attempt_count'];
                break;
            }
        }
        
        $week_data[] = array(
            'date' => $date,
            'day' => $day_label,
            'completed_days' => $completed_count,
            'quiz_attempts' => $attempts_count
        );
    }
    
    return $week_data;
}

/**
 * Get learning time statistics for a user
 *
 * @param int $user_id The user ID
 * @return array Learning time stats
 */
public function get_learning_time_stats($user_id) {
    global $wpdb;
    
    $progress_table = $wpdb->prefix . 'skillsprint_progress';
    
    // Total time spent (in minutes)
    $total_time = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT SUM(time_spent) FROM $progress_table WHERE user_id = %d",
            $user_id
        )
    );
    
    // Average time per day
    $avg_time = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT AVG(time_spent) FROM $progress_table WHERE user_id = %d AND time_spent > 0",
            $user_id
        )
    );
    
    // Total days with activity
    $total_days = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(DISTINCT DATE(date_started)) FROM $progress_table WHERE user_id = %d",
            $user_id
        )
    );
    
    // Format data
    return array(
        'total_time' => $total_time ? intval($total_time) : 0,
        'avg_time_per_day' => $avg_time ? round($avg_time) : 0,
        'total_active_days' => $total_days ? intval($total_days) : 0
    );
}

/**
 * Track time spent on a day
 *
 * @param int $user_id The user ID
 * @param int $blueprint_id The blueprint ID
 * @param int $day_number The day number
 * @param int $time_seconds Time spent in seconds
 * @return bool Whether the operation was successful
 */
public function track_time_spent($user_id, $blueprint_id, $day_number, $time_seconds) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'skillsprint_progress';
    
    // Convert seconds to minutes
    $time_minutes = round($time_seconds / 60);
    
    // Check if entry exists
    $existing = SkillSprint_DB::get_user_day_progress($user_id, $blueprint_id, $day_number);
    
    if ($existing) {
        // Update existing entry
        $result = $wpdb->update(
            $table_name,
            array(
                'time_spent' => $time_minutes + intval($existing['time_spent'])
            ),
            array(
                'user_id' => $user_id,
                'blueprint_id' => $blueprint_id,
                'day_number' => $day_number
            ),
            array('%d'),
            array('%d', '%d', '%d')
        );
    } else {
        // Mark as started and set initial time
        $result = $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'blueprint_id' => $blueprint_id,
                'day_number' => $day_number,
                'progress_status' => 'in_progress',
                'date_started' => current_time('mysql'),
                'time_spent' => $time_minutes
            ),
            array('%d', '%d', '%d', '%s', '%s', '%d')
        );
    }
    
    return $result !== false;
}

/**
 * AJAX handler for tracking time spent
 */
public function ajax_track_time_spent() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'skillsprint_nonce')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'skillsprint')));
    }
    
    // Check user login
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('You must be logged in to track progress.', 'skillsprint')));
    }
    
    // Get parameters
    $user_id = get_current_user_id();
    $blueprint_id = isset($_POST['blueprint_id']) ? intval($_POST['blueprint_id']) : 0;
    $day_number = isset($_POST['day_number']) ? intval($_POST['day_number']) : 0;
    $time_seconds = isset($_POST['time_seconds']) ? intval($_POST['time_seconds']) : 0;
    
    if (!$blueprint_id || !$day_number || $time_seconds <= 0) {
        wp_send_json_error(array('message' => __('Invalid parameters.', 'skillsprint')));
    }
    
    // Track time spent
    $result = $this->track_time_spent($user_id, $blueprint_id, $day_number, $time_seconds);
    
    if ($result) {
        wp_send_json_success(array('message' => __('Time tracked successfully.', 'skillsprint')));
    } else {
        wp_send_json_error(array('message' => __('Error tracking time.', 'skillsprint')));
    }
}
}