<?php
/**
 * Gamification functionality of the plugin.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/includes
 */

/**
 * Gamification functionality of the plugin.
 *
 * Handles all gamification functionality like points, achievements, and streaks.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/includes
 */
class SkillSprint_Gamification {

    /**
     * Award points for completing a day.
     *
     * @since    1.0.0
     * @param    int    $user_id      The user ID.
     * @param    int    $blueprint_id The blueprint ID.
     * @param    int    $day_number   The day number.
     */
    public function award_day_completion_points($user_id, $blueprint_id, $day_number) {
        // Get settings
        $settings = get_option('skillsprint_settings');
        
        // Check if gamification is enabled
        if (!isset($settings['gamification_enabled']) || !$settings['gamification_enabled']) {
            return;
        }
        
        $points_per_day = isset($settings['points_per_day_completion']) ? intval($settings['points_per_day_completion']) : 10;
        
        if ($points_per_day <= 0) {
            return;
        }
        
        // Get streak info for potential bonus
        $streak_info = SkillSprint_DB::get_user_streak($user_id);
        $streak_bonus_multiplier = isset($settings['streak_bonus_multiplier']) ? floatval($settings['streak_bonus_multiplier']) : 1.5;
        $current_streak = isset($streak_info['current_streak']) ? intval($streak_info['current_streak']) : 0;
        
        // Calculate points
        $points = $points_per_day;
        $description = sprintf(__('Completed Day %d of blueprint: %s', 'skillsprint'), $day_number, get_the_title($blueprint_id));
        
        // Add streak bonus if streak is active
        if ($current_streak >= 3 && $streak_bonus_multiplier > 1) {
            $points = round($points * $streak_bonus_multiplier);
            $description .= sprintf(__(' (includes %dx streak bonus)', 'skillsprint'), $streak_bonus_multiplier);
        }
        
        // Award points
        SkillSprint_DB::add_user_points(
            $user_id,
            $points,
            'day_completion',
            $description,
            $blueprint_id
        );
    }
    
    /**
     * Award points for completing a quiz.
     *
     * @since    1.0.0
     * @param    int     $user_id      The user ID.
     * @param    int     $blueprint_id The blueprint ID.
     * @param    string  $quiz_id      The quiz ID.
     * @param    bool    $passed       Whether the user passed the quiz.
     */
    public function award_quiz_points( $user_id, $blueprint_id, $quiz_id, $passed ) {
        // Get settings
        $settings = get_option( 'skillsprint_settings' );
        
        // Check if gamification is enabled
        if ( ! isset( $settings['gamification_enabled'] ) || ! $settings['gamification_enabled'] ) {
            return;
        }
        
        // Only award points if passed
        if ( ! $passed ) {
            return;
        }
        
        // Get quiz responses
        $latest_attempt = SkillSprint_DB::get_latest_quiz_attempt( $user_id, $blueprint_id, $quiz_id );
        $responses = SkillSprint_DB::get_quiz_responses( $user_id, $blueprint_id, $quiz_id, $latest_attempt );
        
        // Calculate points
        $points_per_correct = isset( $settings['points_per_quiz_correct'] ) ? intval( $settings['points_per_quiz_correct'] ) : 5;
        $correct_count = 0;
        
        foreach ( $responses as $response ) {
            if ( $response['is_correct'] ) {
                $correct_count++;
            }
        }
        
        $points = $correct_count * $points_per_correct;
        
        // Get quiz data for description
        $quiz_data = get_post_meta( $blueprint_id, '_skillsprint_quiz_' . $quiz_id, true );
        $quiz_title = isset( $quiz_data['title'] ) ? $quiz_data['title'] : __( 'Quiz', 'skillsprint' );
        
        $description = sprintf( 
            __( 'Completed quiz: %s in blueprint: %s (%d correct answers)', 'skillsprint' ), 
            $quiz_title, 
            get_the_title( $blueprint_id ),
            $correct_count
        );
        
        // Award points
        SkillSprint_DB::add_user_points(
            $user_id,
            $points,
            'quiz_completion',
            $description,
            $blueprint_id
        );
    }
    
    /**
     * Award points for completing a blueprint.
     *
     * @since    1.0.0
     * @param    int    $user_id      The user ID.
     * @param    int    $blueprint_id The blueprint ID.
     */
    public function award_blueprint_completion_points( $user_id, $blueprint_id ) {
        // Get settings
        $settings = get_option( 'skillsprint_settings' );
        
        // Check if gamification is enabled
        if ( ! isset( $settings['gamification_enabled'] ) || ! $settings['gamification_enabled'] ) {
            return;
        }
        
        $points = isset( $settings['points_per_blueprint_completion'] ) ? intval( $settings['points_per_blueprint_completion'] ) : 50;
        
        if ( $points <= 0 ) {
            return;
        }
        
        $description = sprintf( __( 'Completed the entire blueprint: %s', 'skillsprint' ), get_the_title( $blueprint_id ) );
        
        // Award points
        SkillSprint_DB::add_user_points(
            $user_id,
            $points,
            'blueprint_completion',
            $description,
            $blueprint_id
        );
    }
    
    /**
     * Update user streak.
     *
     * @since    1.0.0
     * @param    int    $user_id      The user ID.
     * @param    int    $blueprint_id The blueprint ID.
     */
    public function update_user_streak( $user_id, $blueprint_id ) {
        // Get settings
        $settings = get_option( 'skillsprint_settings' );
        
        // Check if gamification is enabled
        if ( ! isset( $settings['gamification_enabled'] ) || ! $settings['gamification_enabled'] ) {
            return;
        }
        
        // Update streak
        SkillSprint_DB::update_user_streak( $user_id );
    }
    
    /**
     * Check for achievements after completing a day.
     *
     * @since    1.0.0
     * @param    int    $user_id      The user ID.
     * @param    int    $blueprint_id The blueprint ID.
     * @param    int    $day_number   The day number.
     */
    public function check_day_achievements( $user_id, $blueprint_id, $day_number ) {
        // Get settings
        $settings = get_option( 'skillsprint_settings' );
        
        // Check if gamification is enabled
        if ( ! isset( $settings['gamification_enabled'] ) || ! $settings['gamification_enabled'] ) {
            return;
        }
        
        global $wpdb;
        
        // First day completed achievement
        if ( $day_number == 1 ) {
            $progress_table = $wpdb->prefix . 'skillsprint_progress';
            $total_days_completed = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $progress_table WHERE user_id = %d AND progress_status = 'completed'",
                    $user_id
                )
            );
            
            if ( $total_days_completed == 1 ) {
                SkillSprint_DB::add_user_achievement(
                    $user_id,
                    'first_day_completed',
                    array(
                        'blueprint_id' => $blueprint_id,
                        'day_number' => $day_number
                    )
                );
            }
        }
        
        // Completed days milestones
        $progress_table = $wpdb->prefix . 'skillsprint_progress';
        $total_days_completed = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $progress_table WHERE user_id = %d AND progress_status = 'completed'",
                $user_id
            )
        );
        
        // Day milestones
        $day_milestones = array(5, 10, 25, 50, 100);
        
        foreach ( $day_milestones as $milestone ) {
            if ( $total_days_completed == $milestone ) {
                SkillSprint_DB::add_user_achievement(
                    $user_id,
                    'days_completed_' . $milestone,
                    array(
                        'days_completed' => $milestone
                    )
                );
            }
        }
        
        // Check streak milestones
        $streak_info = SkillSprint_DB::get_user_streak( $user_id );
        $current_streak = isset( $streak_info['current_streak'] ) ? intval( $streak_info['current_streak'] ) : 0;
        
        // Streak milestones
        $streak_milestones = array(3, 7, 14, 30, 60, 90);
        
        foreach ( $streak_milestones as $milestone ) {
            if ( $current_streak == $milestone ) {
                SkillSprint_DB::add_user_achievement(
                    $user_id,
                    'streak_' . $milestone,
                    array(
                        'streak_days' => $milestone
                    )
                );
            }
        }
    }
    
    /**
     * Check for achievements after completing a blueprint.
     *
     * @since    1.0.0
     * @param    int    $user_id      The user ID.
     * @param    int    $blueprint_id The blueprint ID.
     */
    public function check_blueprint_achievements( $user_id, $blueprint_id ) {
        // Get settings
        $settings = get_option( 'skillsprint_settings' );
        
        // Check if gamification is enabled
        if ( ! isset( $settings['gamification_enabled'] ) || ! $settings['gamification_enabled'] ) {
            return;
        }
        
        global $wpdb;
        
        // First blueprint completed achievement
        $progress_table = $wpdb->prefix . 'skillsprint_progress';
        
        // Count completed blueprints
        $completed_blueprints = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT blueprint_id, COUNT(*) as days_completed FROM $progress_table 
                WHERE user_id = %d AND progress_status = 'completed' 
                GROUP BY blueprint_id",
                $user_id
            )
        );
        
        $blueprint_completion_count = 0;
        
        foreach ( $completed_blueprints as $blueprint ) {
            $days_data = SkillSprint_DB::get_blueprint_days_data( $blueprint->blueprint_id );
            $total_days = count( $days_data );
            
            if ( $blueprint->days_completed >= $total_days ) {
                $blueprint_completion_count++;
            }
        }
        
        // First blueprint completed
        if ( $blueprint_completion_count == 1 ) {
            SkillSprint_DB::add_user_achievement(
                $user_id,
                'first_blueprint_completed',
                array(
                    'blueprint_id' => $blueprint_id
                )
            );
        }
        
        // Blueprint milestone achievements
        $blueprint_milestones = array(3, 5, 10, 25);
        
        foreach ( $blueprint_milestones as $milestone ) {
            if ( $blueprint_completion_count == $milestone ) {
                SkillSprint_DB::add_user_achievement(
                    $user_id,
                    'blueprints_completed_' . $milestone,
                    array(
                        'blueprints_completed' => $milestone
                    )
                );
            }
        }
        
        // Check if user has completed blueprints in different categories
        $blueprint_categories = wp_get_post_terms( $blueprint_id, 'blueprint_category', array( 'fields' => 'ids' ) );
        
        if ( ! empty( $blueprint_categories ) && ! is_wp_error( $blueprint_categories ) ) {
            foreach ( $blueprint_categories as $category_id ) {
                // Get all blueprints in this category
                $category_blueprints = get_posts( array(
                    'post_type' => 'blueprint',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'blueprint_category',
                            'field' => 'term_id',
                            'terms' => $category_id
                        )
                    ),
                    'fields' => 'ids'
                ) );
                
                // Count how many in this category the user has completed
                $completed_in_category = 0;
                
                foreach ( $category_blueprints as $cat_blueprint_id ) {
                    if ( SkillSprint_DB::check_blueprint_completion( $user_id, $cat_blueprint_id ) ) {
                        $completed_in_category++;
                    }
                }
                
                // Category master achievement (complete 3 in a category)
                if ( $completed_in_category == 3 ) {
                    $category = get_term( $category_id, 'blueprint_category' );
                    
                    if ( $category && ! is_wp_error( $category ) ) {
                        SkillSprint_DB::add_user_achievement(
                            $user_id,
                            'category_master_' . $category_id,
                            array(
                                'category_id' => $category_id,
                                'category_name' => $category->name
                            )
                        );
                    }
                }
            }
        }
    }
    
    /**
     * AJAX handler for getting leaderboard data.
     *
     * @since    1.0.0
     */
    public function ajax_get_leaderboard() {
        // Check nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skillsprint_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skillsprint' ) ) );
        }
        
        // Get settings
        $settings = get_option( 'skillsprint_settings' );
        
        // Check if leaderboard is enabled
        if ( ! isset( $settings['leaderboard_enabled'] ) || ! $settings['leaderboard_enabled'] ) {
            wp_send_json_error( array( 'message' => __( 'Leaderboard is currently disabled.', 'skillsprint' ) ) );
        }
        
        // Get parameters
        $limit = isset( $_POST['limit'] ) ? intval( $_POST['limit'] ) : 10;
        $offset = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;
        
        // Get leaderboard data
        $leaderboard = SkillSprint_DB::get_leaderboard( $limit, $offset );
        
        wp_send_json_success( array(
            'leaderboard' => $leaderboard
        ) );
    }


    /**
 * Get badge info for an achievement
 *
 * @param string $achievement_id Achievement ID
 * @return array Badge info
 */
public function get_badge_info($achievement_id) {
    $badges = $this->get_all_badges();
    
    if (isset($badges[$achievement_id])) {
        return $badges[$achievement_id];
    }
    
    // Check for category badges
    if (strpos($achievement_id, 'category_master_') === 0) {
        $category_id = str_replace('category_master_', '', $achievement_id);
        $category = get_term($category_id, 'blueprint_category');
        
        if ($category && !is_wp_error($category)) {
            return array(
                'name' => sprintf(__('%s Expert', 'skillsprint'), $category->name),
                'description' => sprintf(__('Completed 3 blueprints in the %s category', 'skillsprint'), $category->name),
                'icon' => 'badge-category',
                'points' => 50
            );
        }
    }
    
    return array(
        'name' => __('Achievement', 'skillsprint'),
        'description' => __('Unlocked achievement', 'skillsprint'),
        'icon' => 'badge-default',
        'points' => 10
    );
}

/**
 * Get all available badges
 *
 * @return array All badges
 */
public function get_all_badges() {
    return array(
        'first_day_completed' => array(
            'name' => __('First Step', 'skillsprint'),
            'description' => __('Completed your first day', 'skillsprint'),
            'icon' => 'badge-first-step',
            'points' => 10
        ),
        'days_completed_5' => array(
            'name' => __('Enthusiast', 'skillsprint'),
            'description' => __('Completed 5 days', 'skillsprint'),
            'icon' => 'badge-enthusiast',
            'points' => 25
        ),
        'days_completed_10' => array(
            'name' => __('Dedicated Learner', 'skillsprint'),
            'description' => __('Completed 10 days', 'skillsprint'),
            'icon' => 'badge-dedicated',
            'points' => 50
        ),
        'days_completed_25' => array(
            'name' => __('Learning Machine', 'skillsprint'),
            'description' => __('Completed 25 days', 'skillsprint'),
            'icon' => 'badge-machine',
            'points' => 100
        ),
        'days_completed_50' => array(
            'name' => __('Knowledge Seeker', 'skillsprint'),
            'description' => __('Completed 50 days', 'skillsprint'),
            'icon' => 'badge-seeker',
            'points' => 200
        ),
        'days_completed_100' => array(
            'name' => __('Mastermind', 'skillsprint'),
            'description' => __('Completed 100 days', 'skillsprint'),
            'icon' => 'badge-mastermind',
            'points' => 500
        ),
        'first_blueprint_completed' => array(
            'name' => __('Blueprint Finisher', 'skillsprint'),
            'description' => __('Completed your first blueprint', 'skillsprint'),
            'icon' => 'badge-finisher',
            'points' => 50
        ),
        'blueprints_completed_3' => array(
            'name' => __('Blueprint Collector', 'skillsprint'),
            'description' => __('Completed 3 blueprints', 'skillsprint'),
            'icon' => 'badge-collector',
            'points' => 100
        ),
        'blueprints_completed_5' => array(
            'name' => __('Blueprint Expert', 'skillsprint'),
            'description' => __('Completed 5 blueprints', 'skillsprint'),
            'icon' => 'badge-expert',
            'points' => 200
        ),
        'blueprints_completed_10' => array(
            'name' => __('Blueprint Master', 'skillsprint'),
            'description' => __('Completed 10 blueprints', 'skillsprint'),
            'icon' => 'badge-master',
            'points' => 500
        ),
        'streak_3' => array(
            'name' => __('Consistent', 'skillsprint'),
            'description' => __('3-day learning streak', 'skillsprint'),
            'icon' => 'badge-consistent',
            'points' => 15
        ),
        'streak_7' => array(
            'name' => __('Weekly Champion', 'skillsprint'),
            'description' => __('7-day learning streak', 'skillsprint'),
            'icon' => 'badge-weekly',
            'points' => 50
        ),
        'streak_14' => array(
            'name' => __('Biweekly Hero', 'skillsprint'),
            'description' => __('14-day learning streak', 'skillsprint'),
            'icon' => 'badge-biweekly',
            'points' => 100
        ),
        'streak_30' => array(
            'name' => __('Monthly Devotee', 'skillsprint'),
            'description' => __('30-day learning streak', 'skillsprint'),
            'icon' => 'badge-monthly',
            'points' => 300
        ),
        'perfect_quiz' => array(
            'name' => __('Perfect Score', 'skillsprint'),
            'description' => __('Got 100% on a quiz', 'skillsprint'),
            'icon' => 'badge-perfect',
            'points' => 25
        ),
        'quiz_master' => array(
            'name' => __('Quiz Master', 'skillsprint'),
            'description' => __('Completed 10 quizzes', 'skillsprint'),
            'icon' => 'badge-quiz',
            'points' => 100
        )
    );
}

/**
 * Award achievement
 *
 * @param int $user_id User ID
 * @param string $achievement_id Achievement ID
 * @param array $meta Additional metadata
 * @return bool Success status
 */
public function award_achievement($user_id, $achievement_id, $meta = array()) {
    // Check if user already has this achievement
    $existing = SkillSprint_DB::get_user_achievement($user_id, $achievement_id);
    
    if ($existing) {
        return false; // Already awarded
    }
    
    // Get badge info
    $badge = $this->get_badge_info($achievement_id);
    
    // Award achievement
    $added = SkillSprint_DB::add_user_achievement($user_id, $achievement_id, $meta);
    
    if ($added) {
        // Award points for achievement
        if (!empty($badge['points'])) {
            SkillSprint_DB::add_user_points(
                $user_id,
                $badge['points'],
                'achievement',
                sprintf(__('Earned achievement: %s', 'skillsprint'), $badge['name'])
            );
        }
        
        // Trigger action
        do_action('skillsprint_achievement_awarded', $user_id, $achievement_id, $badge, $meta);
    }
    
    return $added;
}

/**
 * Check for perfect quiz achievement
 *
 * @param int $user_id User ID
 * @param int $blueprint_id Blueprint ID
 * @param string $quiz_id Quiz ID
 * @param array $quiz_result Quiz result data
 */
public function check_perfect_quiz_achievement($user_id, $blueprint_id, $quiz_id, $quiz_result) {
    // Award achievement for perfect score
    if ($quiz_result['score_percentage'] == 100) {
        $this->award_achievement($user_id, 'perfect_quiz', array(
            'blueprint_id' => $blueprint_id,
            'quiz_id' => $quiz_id,
            'score' => 100
        ));
    }
    
    // Count completed quizzes
    global $wpdb;
    $quiz_table = $wpdb->prefix . 'skillsprint_quiz_responses';
    
    $completed_quizzes = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(DISTINCT CONCAT(blueprint_id, '_', quiz_id)) 
            FROM $quiz_table 
            WHERE user_id = %d 
            GROUP BY user_id",
            $user_id
        )
    );
    
    // Award quiz master achievement
    if ($completed_quizzes == 10) {
        $this->award_achievement($user_id, 'quiz_master', array(
            'quizzes_completed' => 10
        ));
    }
}

/**
 * Display achievement notification
 *
 * @param int $user_id User ID
 * @param string $achievement_id Achievement ID
 * @param array $badge Badge info
 * @param array $meta Achievement metadata
 */
public function display_achievement_notification($user_id, $achievement_id, $badge, $meta) {
    // Only show for current user
    if ($user_id != get_current_user_id()) {
        return;
    }
    
    // Build notification HTML
    $notification = '<div class="skillsprint-achievement-notification">';
    $notification .= '<div class="skillsprint-achievement-icon"><img src="' . SKILLSPRINT_PLUGIN_URL . 'public/images/badges/' . $badge['icon'] . '.png" alt="' . esc_attr($badge['name']) . '"></div>';
    $notification .= '<div class="skillsprint-achievement-content">';
    $notification .= '<h3>' . __('Achievement Unlocked!', 'skillsprint') . '</h3>';
    $notification .= '<div class="skillsprint-achievement-name">' . esc_html($badge['name']) . '</div>';
    $notification .= '<div class="skillsprint-achievement-description">' . esc_html($badge['description']) . '</div>';
    $notification .= '<div class="skillsprint-achievement-points">+' . intval($badge['points']) . ' ' . __('points', 'skillsprint') . '</div>';
    $notification .= '</div>';
    $notification .= '<button class="skillsprint-achievement-close">&times;</button>';
    $notification .= '</div>';
    
    // Add to footer with JavaScript
    add_action('wp_footer', function() use ($notification) {
        echo '<script>
            jQuery(document).ready(function($) {
                $("body").append(\'' . str_replace("'", "\'", $notification) . '\');
                
                // Show notification with animation
                setTimeout(function() {
                    $(".skillsprint-achievement-notification").addClass("active");
                }, 500);
                
                // Auto-hide after 5 seconds
                setTimeout(function() {
                    $(".skillsprint-achievement-notification").removeClass("active");
                    setTimeout(function() {
                        $(".skillsprint-achievement-notification").remove();
                    }, 500);
                }, 5000);
                
                // Close button
                $(".skillsprint-achievement-close").on("click", function() {
                    $(".skillsprint-achievement-notification").removeClass("active");
                    setTimeout(function() {
                        $(".skillsprint-achievement-notification").remove();
                    }, 500);
                });
            });
        </script>';
    });
}
}