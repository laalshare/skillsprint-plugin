<?php
/**
 * User dashboard functionality of the plugin.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/includes
 */

/**
 * User dashboard functionality of the plugin.
 *
 * Handles all user dashboard functionality.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/includes
 */
class SkillSprint_Dashboard {

    /**
     * AJAX handler for getting dashboard data.
     *
     * @since    1.0.0
     */
    public function ajax_get_dashboard_data() {
        // Check nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skillsprint_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skillsprint' ) ) );
        }
        
        // Check user login
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in to view the dashboard.', 'skillsprint' ) ) );
        }
        
        $user_id = get_current_user_id();
        
        // Get dashboard data
        $in_progress = SkillSprint_Blueprint::get_user_in_progress_blueprints( $user_id, 5 );
        $completed = SkillSprint_Blueprint::get_user_completed_blueprints( $user_id, 5 );
        $recommended = SkillSprint_Blueprint::get_recommended_blueprints( $user_id, 3 );
        $achievements = SkillSprint_DB::get_user_achievements( $user_id );
        $total_points = SkillSprint_DB::get_user_total_points( $user_id );
        $points_history = SkillSprint_DB::get_user_points_history( $user_id, 10 );
        $streak_info = SkillSprint_DB::get_user_streak( $user_id );
        
        // Prepare achievement data for display
        $achievement_data = array();
        
        foreach ( $achievements as $achievement ) {
            $achievement_info = $this->get_achievement_info( $achievement['achievement_id'] );
            
            if ( $achievement_info ) {
                $achievement_data[] = array(
                    'id' => $achievement['achievement_id'],
                    'title' => $achievement_info['title'],
                    'description' => $achievement_info['description'],
                    'icon' => $achievement_info['icon'],
                    'date_earned' => $achievement['date_earned'],
                    'meta' => $achievement['meta']
                );
            }
        }
        
        // Get stats data
        global $wpdb;
        
        $progress_table = $wpdb->prefix . 'skillsprint_progress';
        
        // Days completed
        $days_completed = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $progress_table WHERE user_id = %d AND progress_status = 'completed'",
                $user_id
            )
        );
        
        // Blueprints started
        $blueprints_started = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(DISTINCT blueprint_id) FROM $progress_table WHERE user_id = %d",
                $user_id
            )
        );
        
        // Blueprints completed
        $blueprints_completed = count( $completed );
        
        // Quiz stats
        $quiz_table = $wpdb->prefix . 'skillsprint_quiz_responses';
        
        $quizzes_taken = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(DISTINCT CONCAT(blueprint_id, '_', quiz_id)) FROM $quiz_table WHERE user_id = %d",
                $user_id
            )
        );
        
        $correct_answers = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $quiz_table WHERE user_id = %d AND is_correct = 1",
                $user_id
            )
        );
        
        $total_answers = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $quiz_table WHERE user_id = %d",
                $user_id
            )
        );
        
        $accuracy = $total_answers > 0 ? round( ( $correct_answers / $total_answers ) * 100 ) : 0;
        
        // Recent activity
        $recent_activity = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT p.user_id, p.blueprint_id, p.day_number, p.progress_status, p.date_completed
                FROM {$progress_table} as p
                WHERE p.date_completed IS NOT NULL
                ORDER BY p.date_completed DESC
                LIMIT 5"
            )
        );
        
        $activity_data = array();
        
        foreach ( $recent_activity as $activity ) {
            $blueprint_title = get_the_title( $activity->blueprint_id );
            
            $activity_data[] = array(
                'blueprint_id' => $activity->blueprint_id,
                'blueprint_title' => $blueprint_title,
                'day_number' => $activity->day_number,
                'status' => $activity->progress_status,
                'date' => $activity->date_completed
            );
        }
        
        // Return dashboard data
        wp_send_json_success( array(
            'in_progress' => $in_progress,
            'completed' => $completed,
            'recommended' => $recommended,
            'achievements' => $achievement_data,
            'total_points' => $total_points,
            'points_history' => $points_history,
            'streak_info' => $streak_info,
            'stats' => array(
                'days_completed' => $days_completed,
                'blueprints_started' => $blueprints_started,
                'blueprints_completed' => $blueprints_completed,
                'quizzes_taken' => $quizzes_taken,
                'correct_answers' => $correct_answers,
                'total_answers' => $total_answers,
                'accuracy' => $accuracy
            ),
            'recent_activity' => $activity_data
        ) );
    }
    
    /**
     * Get achievement information based on achievement ID.
     *
     * @since    1.0.0
     * @param    string $achievement_id The achievement ID.
     * @return   array|false Achievement information or false if not found.
     */
    private function get_achievement_info( $achievement_id ) {
        // Define achievement information
        $achievements = array(
            'first_day_completed' => array(
                'title' => __( 'First Step', 'skillsprint' ),
                'description' => __( 'Completed your first day of learning.', 'skillsprint' ),
                'icon' => 'dashicons dashicons-yes-alt'
            ),
            'days_completed_5' => array(
                'title' => __( 'Getting Started', 'skillsprint' ),
                'description' => __( 'Completed 5 days of learning.', 'skillsprint' ),
                'icon' => 'dashicons dashicons-calendar'
            ),
            'days_completed_10' => array(
                'title' => __( 'Double Digits', 'skillsprint' ),
                'description' => __( 'Completed 10 days of learning.', 'skillsprint' ),
                'icon' => 'dashicons dashicons-calendar'
            ),
            'days_completed_25' => array(
                'title' => __( 'Quarter Century', 'skillsprint' ),
                'description' => __( 'Completed 25 days of learning.', 'skillsprint' ),
                'icon' => 'dashicons dashicons-calendar'
            ),
            'days_completed_50' => array(
                'title' => __( 'Half Century', 'skillsprint' ),
                'description' => __( 'Completed 50 days of learning.', 'skillsprint' ),
                'icon' => 'dashicons dashicons-calendar'
            ),
            'days_completed_100' => array(
                'title' => __( 'Century Milestone', 'skillsprint' ),
                'description' => __( 'Completed 100 days of learning.', 'skillsprint' ),
                'icon' => 'dashicons dashicons-awards'
            ),
            'streak_3' => array(
                'title' => __( 'Triple Streak', 'skillsprint' ),
                'description' => __( 'Learned for 3 consecutive days.', 'skillsprint' ),
                'icon' => 'dashicons dashicons-chart-line'
            ),
            'streak_7' => array(
                'title' => __( 'Week Warrior', 'skillsprint' ),
                'description' => __( 'Learned for 7 consecutive days.', 'skillsprint' ),
                'icon' => 'dashicons dashicons-chart-line'
            ),
            'streak_14' => array(
                'title' => __( 'Fortnight Focus', 'skillsprint' ),
                'description' => __( 'Learned for 14 consecutive days.', 'skillsprint' ),
                'icon' => 'dashicons dashicons-chart-line'
            ),
            'streak_30' => array(
                'title' => __( 'Monthly Master', 'skillsprint' ),
                'description' => __( 'Learned for 30 consecutive days.', 'skillsprint' ),
                'icon' => 'dashicons dashicons-awards'
            ),
            'streak_60' => array(
                'title' => __( 'Bimonthly Boss', 'skillsprint' ),
                'description' => __( 'Learned for 60 consecutive days.', 'skillsprint' ),
                'icon' => 'dashicons dashicons-awards'
            ),
            'streak_90' => array(
                'title' => __( 'Quarterly Quest', 'skillsprint' ),
                'description' => __( 'Learned for 90 consecutive days.', 'skillsprint' ),
                'icon' => 'dashicons dashicons-superhero'
            ),
            'first_blueprint_completed' => array(
                'title' => __( 'Blueprint Beginner', 'skillsprint' ),
                'description' => __( 'Completed your first 7-day blueprint.', 'skillsprint' ),
                'icon' => 'dashicons dashicons-welcome-learn-more'
            ),
            'blueprints_completed_3' => array(
                'title' => __( 'Blueprint Trio', 'skillsprint' ),
                'description' => __( 'Completed 3 blueprints.', 'skillsprint' ),
                'icon' => 'dashicons dashicons-welcome-learn-more'
            ),
            'blueprints_completed_5' => array(
                'title' => __( 'Blueprint Collector', 'skillsprint' ),
                'description' => __( 'Completed 5 blueprints.', 'skillsprint' ),
                'icon' => 'dashicons dashicons-welcome-learn-more'
            ),
            'blueprints_completed_10' => array(
                'title' => __( 'Blueprint Expert', 'skillsprint' ),
                'description' => __( 'Completed 10 blueprints.', 'skillsprint' ),
                'icon' => 'dashicons dashicons-awards'
            ),
            'blueprints_completed_25' => array(
                'title' => __( 'Blueprint Master', 'skillsprint' ),
                'description' => __( 'Completed 25 blueprints.', 'skillsprint' ),
                'icon' => 'dashicons dashicons-superhero'
            )
        );
        
        // Handle category master achievements
        if ( strpos( $achievement_id, 'category_master_' ) === 0 ) {
            $category_id = str_replace( 'category_master_', '', $achievement_id );
            $category = get_term( $category_id, 'blueprint_category' );
            
            if ( $category && ! is_wp_error( $category ) ) {
                return array(
                    'title' => sprintf( __( '%s Master', 'skillsprint' ), $category->name ),
                    'description' => sprintf( __( 'Completed 3 blueprints in the %s category.', 'skillsprint' ), $category->name ),
                    'icon' => 'dashicons dashicons-category'
                );
            }
        }
        
        return isset( $achievements[$achievement_id] ) ? $achievements[$achievement_id] : false;
    }
}