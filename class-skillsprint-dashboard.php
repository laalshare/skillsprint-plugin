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



    /**
     * Render dashboard statistics widget
     *
     * @param int $user_id User ID
     */
    public function render_statistics_widget($user_id) {
        // Get user statistics
        $stats = array(
            'days_completed' => $this->get_days_completed_count($user_id),
            'blueprints_completed' => $this->get_blueprints_completed_count($user_id),
            'total_points' => SkillSprint_DB::get_user_total_points($user_id),
            'current_streak' => $this->get_user_streak($user_id),
            'quiz_accuracy' => $this->get_quiz_accuracy($user_id),
            'total_time' => $this->get_total_learning_time($user_id)
        );
        
        // Get user rank
        $rank = get_user_meta($user_id, '_skillsprint_rank', true);
        
        ?>
        <div class="skillsprint-dashboard-widget skillsprint-stats-widget">
            <h3 class="skillsprint-dashboard-widget-title"><?php _e('Your Learning Statistics', 'skillsprint'); ?></h3>
            
            <div class="skillsprint-stats-grid">
                <div class="skillsprint-stat-item">
                    <div class="skillsprint-stat-value" data-stat="days_completed"><?php echo esc_html($stats['days_completed']); ?></div>
                    <div class="skillsprint-stat-label"><?php _e('Days Completed', 'skillsprint'); ?></div>
                </div>
                
                <div class="skillsprint-stat-item">
                    <div class="skillsprint-stat-value" data-stat="blueprints_completed"><?php echo esc_html($stats['blueprints_completed']); ?></div>
                    <div class="skillsprint-stat-label"><?php _e('Blueprints Completed', 'skillsprint'); ?></div>
                </div>
                
                <div class="skillsprint-stat-item">
                    <div class="skillsprint-stat-value" data-stat="total_points"><?php echo esc_html($stats['total_points']); ?></div>
                    <div class="skillsprint-stat-label"><?php _e('Total Points', 'skillsprint'); ?></div>
                </div>
                
                <div class="skillsprint-stat-item">
                    <div class="skillsprint-stat-value" data-stat="current_streak"><?php echo esc_html($stats['current_streak']); ?></div>
                    <div class="skillsprint-stat-label"><?php _e('Day Streak', 'skillsprint'); ?></div>
                </div>
                
                <div class="skillsprint-stat-item">
                    <div class="skillsprint-stat-value" data-stat="quiz_accuracy"><?php echo esc_html($stats['quiz_accuracy']); ?>%</div>
                    <div class="skillsprint-stat-label"><?php _e('Quiz Accuracy', 'skillsprint'); ?></div>
                </div>
                
                <div class="skillsprint-stat-item">
                    <div class="skillsprint-stat-value" data-stat="rank"><?php echo $rank ? esc_html('#' . $rank) : '-'; ?></div>
                    <div class="skillsprint-stat-label"><?php _e('Leaderboard Rank', 'skillsprint'); ?></div>
                </div>
            </div>
            
            <?php if ($stats['total_time'] > 0) : ?>
                <div class="skillsprint-total-time">
                    <p><?php printf(
                        __('You have spent <strong>%s</strong> learning with us!', 'skillsprint'),
                        $this->format_time_spent($stats['total_time'])
                    ); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render weekly progress widget
     *
     * @param int $user_id User ID
     */
    public function render_weekly_progress_widget($user_id) {
        // Get progress data
        $progress = new SkillSprint_Progress();
        $weekly_data = $progress->get_weekly_progress($user_id);
        
        ?>
        <div class="skillsprint-dashboard-widget skillsprint-weekly-widget">
            <h3 class="skillsprint-dashboard-widget-title"><?php _e('This Week\'s Progress', 'skillsprint'); ?></h3>
            
            <div class="skillsprint-weekly-chart-container">
                <div class="skillsprint-weekly-chart">
                    <?php foreach ($weekly_data as $day) : 
                        $height = $day['completed_days'] * 20; // 20px per completed day
                        $active = date('Y-m-d') === $day['date'] ? 'active' : '';
                        ?>
                        <div class="skillsprint-day-column <?php echo $active; ?>" title="<?php printf(__('%s: %d days completed', 'skillsprint'), $day['day'], $day['completed_days']); ?>">
                            <div class="skillsprint-day-bar" style="height: <?php echo $height; ?>px;"></div>
                            <div class="skillsprint-day-label"><?php echo esc_html($day['day']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <?php
            // Calculate total for the week
            $total_completed = array_sum(array_column($weekly_data, 'completed_days'));
            $total_quizzes = array_sum(array_column($weekly_data, 'quiz_attempts'));
            ?>
            
            <div class="skillsprint-weekly-summary">
                <div class="skillsprint-weekly-total">
                    <span><?php _e('This week:', 'skillsprint'); ?></span>
                    <span><?php printf(_n('%d day completed', '%d days completed', $total_completed, 'skillsprint'), $total_completed); ?></span>
                    <span><?php printf(_n('%d quiz taken', '%d quizzes taken', $total_quizzes, 'skillsprint'), $total_quizzes); ?></span>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render achievements widget
     *
     * @param int $user_id User ID
     */
    public function render_achievements_widget($user_id) {
        // Get user achievements
        $achievements = SkillSprint_DB::get_user_achievements($user_id);
        $gamification = new SkillSprint_Gamification();
        
        ?>
        <div class="skillsprint-dashboard-widget skillsprint-achievements-widget">
            <h3 class="skillsprint-dashboard-widget-title"><?php _e('Your Achievements', 'skillsprint'); ?></h3>
            
            <?php if (!empty($achievements)) : ?>
                <div class="skillsprint-achievements-list">
                    <?php foreach (array_slice($achievements, 0, 5) as $achievement) : 
                        $badge = $gamification->get_badge_info($achievement['achievement_id']);
                        ?>
                        <div class="skillsprint-achievement">
                            <div class="skillsprint-achievement-icon">
                                <img src="<?php echo SKILLSPRINT_PLUGIN_URL; ?>public/images/badges/<?php echo esc_attr($badge['icon']); ?>.png" alt="<?php echo esc_attr($badge['name']); ?>">
                            </div>
                            <div class="skillsprint-achievement-content">
                                <div class="skillsprint-achievement-title"><?php echo esc_html($badge['name']); ?></div>
                                <div class="skillsprint-achievement-description"><?php echo esc_html($badge['description']); ?></div>
                                <div class="skillsprint-achievement-date"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($achievement['date_earned']))); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (count($achievements) > 5) : ?>
                    <div class="skillsprint-view-all">
                        <a href="<?php echo esc_url(add_query_arg('view', 'achievements', get_permalink())); ?>"><?php printf(__('View all %d achievements', 'skillsprint'), count($achievements)); ?></a>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="skillsprint-no-achievements">
                    <p><?php _e('You haven\'t earned any achievements yet. Complete days and blueprints to earn achievements!', 'skillsprint'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Get days completed count
     *
     * @param int $user_id User ID
     * @return int Days completed count
     */
    private function get_days_completed_count($user_id) {
        global $wpdb;
        
        $progress_table = $wpdb->prefix . 'skillsprint_progress';
        
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $progress_table WHERE user_id = %d AND progress_status = 'completed'",
                $user_id
            )
        );
        
        return intval($count);
    }

    /**
     * Get blueprints completed count
     *
     * @param int $user_id User ID
     * @return int Blueprints completed count
     */
    private function get_blueprints_completed_count($user_id) {
        global $wpdb;
        
        $progress_table = $wpdb->prefix . 'skillsprint_progress';
        
        // Get all blueprints with progress
        $blueprint_progress = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                    blueprint_id,
                    COUNT(*) as completed_days
                FROM 
                    $progress_table 
                WHERE 
                    user_id = %d AND 
                    progress_status = 'completed'
                GROUP BY 
                    blueprint_id",
                $user_id
            ),
            ARRAY_A
        );
        
        $completed_count = 0;
        
        foreach ($blueprint_progress as $progress) {
            // Get total days in blueprint
            $days_data = SkillSprint_DB::get_blueprint_days_data($progress['blueprint_id']);
            $total_days = count($days_data);
            
            // Check if all days are completed
            if ($progress['completed_days'] >= $total_days) {
                $completed_count++;
            }
        }
        
        return $completed_count;
    }

    /**
     * Get user streak
     *
     * @param int $user_id User ID
     * @return int Current streak
     */
    private function get_user_streak($user_id) {
        $streak_info = SkillSprint_DB::get_user_streak($user_id);
        
        return isset($streak_info['current_streak']) ? intval($streak_info['current_streak']) : 0;
    }

    /**
     * Get quiz accuracy
     *
     * @param int $user_id User ID
     * @return int Quiz accuracy percentage
     */
    private function get_quiz_accuracy($user_id) {
        global $wpdb;
        
        $quiz_table = $wpdb->prefix . 'skillsprint_quiz_responses';
        
        $total = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $quiz_table WHERE user_id = %d",
                $user_id
            )
        );
        
        $correct = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $quiz_table WHERE user_id = %d AND is_correct = 1",
                $user_id
            )
        );
        
        if ($total > 0) {
            return round(($correct / $total) * 100);
        }
        
        return 0;
    }

    /**
     * Get total learning time
     *
     * @param int $user_id User ID
     * @return int Total time in minutes
     */
    private function get_total_learning_time($user_id) {
        global $wpdb;
        
        $progress_table = $wpdb->prefix . 'skillsprint_progress';
        
        $total_time = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(time_spent) FROM $progress_table WHERE user_id = %d",
                $user_id
            )
        );
        
        return intval($total_time);
    }

    /**
     * Format time spent
     *
     * @param int $minutes Time in minutes
     * @return string Formatted time
     */
    private function format_time_spent($minutes) {
        if ($minutes < 60) {
            return sprintf(_n('%d minute', '%d minutes', $minutes, 'skillsprint'), $minutes);
        }
        
        $hours = floor($minutes / 60);
        $remaining_minutes = $minutes % 60;
        
        if ($remaining_minutes == 0) {
            return sprintf(_n('%d hour', '%d hours', $hours, 'skillsprint'), $hours);
        }
        
        return sprintf(
            __('%d hour %d minute', '%d hours %d minutes', $hours, 'skillsprint'), 
            $hours, 
            $remaining_minutes
        );
    }



}