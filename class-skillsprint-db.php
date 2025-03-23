<?php
/**
 * Database operations for the plugin.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/includes
 */

/**
 * Database operations for the plugin.
 *
 * Handles all database operations for the plugin.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/includes
 */
class SkillSprint_DB {

    /**
     * Get user progress for a specific blueprint.
     *
     * @since    1.0.0
     * @param    int    $user_id      The user ID.
     * @param    int    $blueprint_id The blueprint ID.
     * @return   array  User progress data.
     */
    public static function get_user_blueprint_progress( $user_id, $blueprint_id ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'skillsprint_progress';
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d AND blueprint_id = %d ORDER BY day_number ASC",
                $user_id,
                $blueprint_id
            ),
            ARRAY_A
        );
        
        return $results;
    }
    
    /**
     * Get user progress for a specific day of a blueprint.
     *
     * @since    1.0.0
     * @param    int    $user_id      The user ID.
     * @param    int    $blueprint_id The blueprint ID.
     * @param    int    $day_number   The day number.
     * @return   array|false  User progress data or false if not found.
     */
    public static function get_user_day_progress( $user_id, $blueprint_id, $day_number ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'skillsprint_progress';
        
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d AND blueprint_id = %d AND day_number = %d",
                $user_id,
                $blueprint_id,
                $day_number
            ),
            ARRAY_A
        );
        
        return $result;
    }
    
    /**
     * Mark a day as started for a user.
     *
     * @since    1.0.0
     * @param    int    $user_id      The user ID.
     * @param    int    $blueprint_id The blueprint ID.
     * @param    int    $day_number   The day number.
     * @return   bool   Whether the operation was successful.
     */
    public static function mark_day_started( $user_id, $blueprint_id, $day_number ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'skillsprint_progress';
        
        // Check if entry already exists
        $existing = self::get_user_day_progress( $user_id, $blueprint_id, $day_number );
        
        if ( $existing ) {
            // Only update if not already completed
            if ( $existing['progress_status'] !== 'completed' ) {
                $result = $wpdb->update(
                    $table_name,
                    array(
                        'progress_status' => 'in_progress',
                        'date_started' => current_time( 'mysql' )
                    ),
                    array(
                        'user_id' => $user_id,
                        'blueprint_id' => $blueprint_id,
                        'day_number' => $day_number
                    ),
                    array( '%s', '%s' ),
                    array( '%d', '%d', '%d' )
                );
            } else {
                $result = true; // Already completed, consider success
            }
        } else {
            // Create new entry
            $result = $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'blueprint_id' => $blueprint_id,
                    'day_number' => $day_number,
                    'progress_status' => 'in_progress',
                    'date_started' => current_time( 'mysql' )
                ),
                array( '%d', '%d', '%d', '%s', '%s' )
            );
        }
        
        return $result !== false;
    }
    
    /**
     * Mark a day as completed for a user.
     *
     * @since    1.0.0
     * @param    int    $user_id      The user ID.
     * @param    int    $blueprint_id The blueprint ID.
     * @param    int    $day_number   The day number.
     * @param    string $notes        Optional notes about completion.
     * @return   bool   Whether the operation was successful.
     */
    public static function mark_day_completed( $user_id, $blueprint_id, $day_number, $notes = '' ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'skillsprint_progress';
        
        // Check if entry already exists
        $existing = self::get_user_day_progress( $user_id, $blueprint_id, $day_number );
        
        if ( $existing ) {
            // Update existing entry
            $result = $wpdb->update(
                $table_name,
                array(
                    'progress_status' => 'completed',
                    'date_completed' => current_time( 'mysql' ),
                    'notes' => $notes
                ),
                array(
                    'user_id' => $user_id,
                    'blueprint_id' => $blueprint_id,
                    'day_number' => $day_number
                ),
                array( '%s', '%s', '%s' ),
                array( '%d', '%d', '%d' )
            );
        } else {
            // Create new entry (rare case if day was never marked as started)
            $result = $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'blueprint_id' => $blueprint_id,
                    'day_number' => $day_number,
                    'progress_status' => 'completed',
                    'date_started' => current_time( 'mysql' ),
                    'date_completed' => current_time( 'mysql' ),
                    'notes' => $notes
                ),
                array( '%d', '%d', '%d', '%s', '%s', '%s', '%s' )
            );
        }
        
        if ( $result !== false ) {
            // Fire action to notify that day was completed
            do_action( 'skillsprint_day_completed', $user_id, $blueprint_id, $day_number );
            
            // Check if all days are completed
            if ( self::check_blueprint_completion( $user_id, $blueprint_id ) ) {
                // Fire action to notify that blueprint was completed
                do_action( 'skillsprint_blueprint_completed', $user_id, $blueprint_id );
            }
        }
        
        return $result !== false;
    }
    
    /**
     * Check if a blueprint is completed by a user.
     *
     * @since    1.0.0
     * @param    int    $user_id      The user ID.
     * @param    int    $blueprint_id The blueprint ID.
     * @return   bool   Whether the blueprint is completed.
     */
    public static function check_blueprint_completion( $user_id, $blueprint_id ) {
        $days_data = self::get_blueprint_days_data( $blueprint_id );
        $total_days = count( $days_data );
        
        if ( $total_days === 0 ) {
            return false;
        }
        
        $progress = self::get_user_blueprint_progress( $user_id, $blueprint_id );
        $completed_days = 0;
        
        foreach ( $progress as $day_progress ) {
            if ( $day_progress['progress_status'] === 'completed' ) {
                $completed_days++;
            }
        }
        
        return $completed_days === $total_days;
    }
    
    /**
     * Get blueprint completion percentage for a user.
     *
     * @since    1.0.0
     * @param    int    $user_id      The user ID.
     * @param    int    $blueprint_id The blueprint ID.
     * @return   int    Completion percentage (0-100).
     */
    public static function get_blueprint_completion_percentage( $user_id, $blueprint_id ) {
        $days_data = self::get_blueprint_days_data( $blueprint_id );
        $total_days = count( $days_data );
        
        if ( $total_days === 0 ) {
            return 0;
        }
        
        $progress = self::get_user_blueprint_progress( $user_id, $blueprint_id );
        $completed_days = 0;
        
        foreach ( $progress as $day_progress ) {
            if ( $day_progress['progress_status'] === 'completed' ) {
                $completed_days++;
            }
        }
        
        return round( ( $completed_days / $total_days ) * 100 );
    }
    
    /**
     * Get blueprint days data.
     *
     * @since    1.0.0
     * @param    int    $blueprint_id The blueprint ID.
     * @return   array  Blueprint days data.
     */
    public static function get_blueprint_days_data( $blueprint_id ) {
        $days_data = get_post_meta( $blueprint_id, '_skillsprint_days_data', true );
        
        if ( ! $days_data || ! is_array( $days_data ) ) {
            $days_data = array();
        }
        
        return $days_data;
    }
    
    /**
     * Get a specific day's data for a blueprint.
     *
     * @since    1.0.0
     * @param    int    $blueprint_id The blueprint ID.
     * @param    int    $day_number   The day number.
     * @return   array|false  Day data or false if not found.
     */
    public static function get_blueprint_day_data( $blueprint_id, $day_number ) {
        $days_data = self::get_blueprint_days_data( $blueprint_id );
        
        foreach ( $days_data as $day_data ) {
            if ( isset( $day_data['day_number'] ) && $day_data['day_number'] == $day_number ) {
                return $day_data;
            }
        }
        
        return false;
    }
    
    /**
     * Save quiz response.
     *
     * @since    1.0.0
     * @param    int     $user_id      The user ID.
     * @param    int     $blueprint_id The blueprint ID.
     * @param    string  $quiz_id      The quiz ID.
     * @param    string  $question_id  The question ID.
     * @param    string  $user_answer  The user's answer.
     * @param    bool    $is_correct   Whether the answer is correct.
     * @param    int     $points       Points earned.
     * @param    int     $attempt      Attempt number.
     * @return   bool    Whether the operation was successful.
     */
    public static function save_quiz_response( $user_id, $blueprint_id, $quiz_id, $question_id, $user_answer, $is_correct, $points = 0, $attempt = 1 ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'skillsprint_quiz_responses';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'blueprint_id' => $blueprint_id,
                'quiz_id' => $quiz_id,
                'question_id' => $question_id,
                'user_answer' => $user_answer,
                'is_correct' => $is_correct ? 1 : 0,
                'points_earned' => $points,
                'attempt_number' => $attempt,
                'date_submitted' => current_time( 'mysql' )
            ),
            array( '%d', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%s' )
        );
        
        return $result !== false;
    }
    
    /**
     * Get quiz responses for a user.
     *
     * @since    1.0.0
     * @param    int     $user_id      The user ID.
     * @param    int     $blueprint_id The blueprint ID.
     * @param    string  $quiz_id      The quiz ID.
     * @param    int     $attempt      Attempt number. 0 for all attempts.
     * @return   array   Quiz responses.
     */
    public static function get_quiz_responses( $user_id, $blueprint_id, $quiz_id, $attempt = 0 ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'skillsprint_quiz_responses';
        
        if ( $attempt > 0 ) {
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $table_name WHERE user_id = %d AND blueprint_id = %d AND quiz_id = %s AND attempt_number = %d",
                    $user_id,
                    $blueprint_id,
                    $quiz_id,
                    $attempt
                ),
                ARRAY_A
            );
        } else {
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $table_name WHERE user_id = %d AND blueprint_id = %d AND quiz_id = %s",
                    $user_id,
                    $blueprint_id,
                    $quiz_id
                ),
                ARRAY_A
            );
        }
        
        return $results;
    }
    
    /**
     * Calculate quiz score.
     *
     * @since    1.0.0
     * @param    int     $user_id      The user ID.
     * @param    int     $blueprint_id The blueprint ID.
     * @param    string  $quiz_id      The quiz ID.
     * @param    int     $attempt      Attempt number.
     * @return   array   Score information.
     */
    public static function calculate_quiz_score( $user_id, $blueprint_id, $quiz_id, $attempt = 1 ) {
        $responses = self::get_quiz_responses( $user_id, $blueprint_id, $quiz_id, $attempt );
        
        $total_questions = count( $responses );
        $correct_answers = 0;
        $total_points = 0;
        
        foreach ( $responses as $response ) {
            if ( $response['is_correct'] ) {
                $correct_answers++;
            }
            $total_points += $response['points_earned'];
        }
        
        $score_percentage = $total_questions > 0 ? round( ( $correct_answers / $total_questions ) * 100 ) : 0;
        
        return array(
            'total_questions' => $total_questions,
            'correct_answers' => $correct_answers,
            'score_percentage' => $score_percentage,
            'total_points' => $total_points
        );
    }
    
    /**
     * Check if user passed a quiz.
     *
     * @since    1.0.0
     * @param    int     $user_id       The user ID.
     * @param    int     $blueprint_id  The blueprint ID.
     * @param    string  $quiz_id       The quiz ID.
     * @param    int     $attempt       Attempt number.
     * @return   bool    Whether the user passed the quiz.
     */
    public static function did_user_pass_quiz( $user_id, $blueprint_id, $quiz_id, $attempt = 1 ) {
        $quiz_meta = get_post_meta( $blueprint_id, '_skillsprint_quiz_' . $quiz_id, true );
        $passing_score = isset( $quiz_meta['passing_score'] ) ? intval( $quiz_meta['passing_score'] ) : 70;
        
        $score_info = self::calculate_quiz_score( $user_id, $blueprint_id, $quiz_id, $attempt );
        
        return $score_info['score_percentage'] >= $passing_score;
    }
    
    /**
     * Get the latest quiz attempt number.
     *
     * @since    1.0.0
     * @param    int     $user_id      The user ID.
     * @param    int     $blueprint_id The blueprint ID.
     * @param    string  $quiz_id      The quiz ID.
     * @return   int     Latest attempt number.
     */
    public static function get_latest_quiz_attempt( $user_id, $blueprint_id, $quiz_id ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'skillsprint_quiz_responses';
        
        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT MAX(attempt_number) FROM $table_name WHERE user_id = %d AND blueprint_id = %d AND quiz_id = %s",
                $user_id,
                $blueprint_id,
                $quiz_id
            )
        );
        
        return intval( $result ) ? intval( $result ) : 0;
    }
    
    /**
     * Add user achievement.
     *
     * @since    1.0.0
     * @param    int     $user_id        The user ID.
     * @param    string  $achievement_id The achievement ID.
     * @param    array   $meta           Achievement metadata.
     * @return   bool    Whether the operation was successful.
     */
    public static function add_user_achievement( $user_id, $achievement_id, $meta = array() ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'skillsprint_achievements';
        
        // Check if user already has this achievement
        $existing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table_name WHERE user_id = %d AND achievement_id = %s",
                $user_id,
                $achievement_id
            )
        );
        
        if ( $existing ) {
            return true; // User already has this achievement
        }
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'achievement_id' => $achievement_id,
                'date_earned' => current_time( 'mysql' ),
                'meta' => maybe_serialize( $meta )
            ),
            array( '%d', '%s', '%s', '%s' )
        );
        
        if ( $result !== false ) {
            do_action( 'skillsprint_achievement_earned', $user_id, $achievement_id, $meta );
        }
        
        return $result !== false;
    }
    
    /**
     * Get user achievements.
     *
     * @since    1.0.0
     * @param    int     $user_id The user ID.
     * @return   array   User achievements.
     */
    public static function get_user_achievements( $user_id ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'skillsprint_achievements';
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d ORDER BY date_earned DESC",
                $user_id
            ),
            ARRAY_A
        );
        
        foreach ( $results as &$result ) {
            $result['meta'] = maybe_unserialize( $result['meta'] );
        }
        
        return $results;
    }
    
    /**
     * Add points to a user.
     *
     * @since    1.0.0
     * @param    int     $user_id      The user ID.
     * @param    int     $points       The points to add.
     * @param    string  $source       The source of points.
     * @param    string  $description  Description of why points were awarded.
     * @param    int     $blueprint_id The blueprint ID (optional).
     * @return   bool    Whether the operation was successful.
     */
    public static function add_user_points( $user_id, $points, $source, $description = '', $blueprint_id = null ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'skillsprint_user_points';
        
        $data = array(
            'user_id' => $user_id,
            'points' => $points,
            'source' => $source,
            'description' => $description,
            'date_earned' => current_time( 'mysql' )
        );
        
        $format = array( '%d', '%d', '%s', '%s', '%s' );
        
        if ( $blueprint_id ) {
            $data['blueprint_id'] = $blueprint_id;
            $format[] = '%d';
        }
        
        $result = $wpdb->insert( $table_name, $data, $format );
        
        if ( $result !== false ) {
            do_action( 'skillsprint_points_added', $user_id, $points, $source, $blueprint_id );
        }
        
        return $result !== false;
    }
    
    /**
     * Get total points for a user.
     *
     * @since    1.0.0
     * @param    int     $user_id The user ID.
     * @return   int     Total points.
     */
    public static function get_user_total_points( $user_id ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'skillsprint_user_points';
        
        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(points) FROM $table_name WHERE user_id = %d",
                $user_id
            )
        );
        
        return intval( $result ) ? intval( $result ) : 0;
    }
    
    /**
     * Get user points history.
     *
     * @since    1.0.0
     * @param    int     $user_id      The user ID.
     * @param    int     $limit        Number of records to return.
     * @param    int     $offset       Offset for pagination.
     * @param    int     $blueprint_id Optional blueprint ID to filter by.
     * @return   array   Points history.
     */
    public static function get_user_points_history( $user_id, $limit = 10, $offset = 0, $blueprint_id = null ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'skillsprint_user_points';
        
        if ( $blueprint_id ) {
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $table_name WHERE user_id = %d AND blueprint_id = %d ORDER BY date_earned DESC LIMIT %d OFFSET %d",
                    $user_id,
                    $blueprint_id,
                    $limit,
                    $offset
                ),
                ARRAY_A
            );
        } else {
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $table_name WHERE user_id = %d ORDER BY date_earned DESC LIMIT %d OFFSET %d",
                    $user_id,
                    $limit,
                    $offset
                ),
                ARRAY_A
            );
        }
        
        return $results;
    }
    
    /**
     * Get user streak information.
     *
     * @since    1.0.0
     * @param    int     $user_id The user ID.
     * @return   array   Streak information.
     */
    public static function get_user_streak( $user_id ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'skillsprint_streaks';
        
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d",
                $user_id
            ),
            ARRAY_A
        );
        
        if ( ! $result ) {
            return array(
                'current_streak' => 0,
                'longest_streak' => 0,
                'last_activity_date' => null
            );
        }
        
        return $result;
    }
    
    /**
     * Update user streak.
     *
     * @since    1.0.0
     * @param    int     $user_id  The user ID.
     * @return   bool    Whether the operation was successful.
     */
    public static function update_user_streak( $user_id ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'skillsprint_streaks';
        $current_date = current_time( 'Y-m-d' );
        
        // Get current streak info
        $streak_info = self::get_user_streak( $user_id );
        
        // Initialize values
        $current_streak = 0;
        $longest_streak = 0;
        $last_activity_date = null;
        
        if ( $streak_info ) {
            $current_streak = $streak_info['current_streak'];
            $longest_streak = $streak_info['longest_streak'];
            $last_activity_date = $streak_info['last_activity_date'];
        }
        
        // Calculate new streak values
        if ( ! $last_activity_date ) {
            // First activity
            $current_streak = 1;
            $longest_streak = 1;
        } else {
            // Check if the last activity was yesterday
            $last_date = new DateTime( $last_activity_date );
            $current = new DateTime( $current_date );
            $interval = $last_date->diff( $current );
            
            if ( $interval->days == 1 ) {
                // Activity on consecutive days
                $current_streak++;
                if ( $current_streak > $longest_streak ) {
                    $longest_streak = $current_streak;
                }
            } elseif ( $interval->days == 0 ) {
                // Activity on the same day, no change in streak
            } else {
                // Streak broken
                $current_streak = 1;
            }
        }
        
        // Update or insert streak record
        if ( $streak_info && isset( $streak_info['id'] ) ) {
            $result = $wpdb->update(
                $table_name,
                array(
                    'current_streak' => $current_streak,
                    'longest_streak' => $longest_streak,
                    'last_activity_date' => $current_date
                ),
                array(
                    'user_id' => $user_id
                ),
                array( '%d', '%d', '%s' ),
                array( '%d' )
            );
        } else {
            $result = $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'current_streak' => $current_streak,
                    'longest_streak' => $longest_streak,
                    'last_activity_date' => $current_date
                ),
                array( '%d', '%d', '%d', '%s' )
            );
        }
        
        return $result !== false;
    }
    
    /**
     * Get leaderboard data.
     *
     * @since    1.0.0
     * @param    int     $limit  Number of users to return.
     * @param    int     $offset Offset for pagination.
     * @return   array   Leaderboard data.
     */
    public static function get_leaderboard( $limit = 10, $offset = 0 ) {
        global $wpdb;
        
        $points_table = $wpdb->prefix . 'skillsprint_user_points';
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                    user_id, 
                    SUM(points) as total_points,
                    COUNT(DISTINCT blueprint_id) as blueprints_engaged
                FROM 
                    $points_table 
                GROUP BY 
                    user_id 
                ORDER BY 
                    total_points DESC, 
                    blueprints_engaged DESC
                LIMIT %d OFFSET %d",
                $limit,
                $offset
            ),
            ARRAY_A
        );
        
        // Add user data
        foreach ( $results as &$result ) {
            $user = get_user_by( 'id', $result['user_id'] );
            if ( $user ) {
                $result['user_name'] = $user->display_name;
                $result['user_avatar'] = get_avatar_url( $user->ID, array( 'size' => 50 ) );
                
                // Get streak info
                $streak_info = self::get_user_streak( $user->ID );
                $result['current_streak'] = $streak_info['current_streak'];
                $result['longest_streak'] = $streak_info['longest_streak'];
            }
        }
        
        return $results;
    }
}