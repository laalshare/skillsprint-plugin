<?php
/**
 * Quiz system functionality of the plugin.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/includes
 */

/**
 * Quiz system functionality of the plugin.
 *
 * Handles all quiz functionality.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/includes
 */
class SkillSprint_Quiz {

    /**
     * AJAX handler for submitting a quiz.
     *
     * @since    1.0.0
     */
    public function ajax_submit_quiz() {
        // Check nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skillsprint_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skillsprint' ) ) );
        }
        
        // Check user login
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in to submit quizzes.', 'skillsprint' ) ) );
        }
        
        // Get parameters
        $user_id = get_current_user_id();
        $blueprint_id = isset( $_POST['blueprint_id'] ) ? intval( $_POST['blueprint_id'] ) : 0;
        $quiz_id = isset( $_POST['quiz_id'] ) ? sanitize_text_field( $_POST['quiz_id'] ) : '';
        $answers = isset( $_POST['answers'] ) ? $_POST['answers'] : array();
        
        if ( ! $blueprint_id || empty( $quiz_id ) || empty( $answers ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid quiz submission.', 'skillsprint' ) ) );
        }
        
        // Get quiz data
        $quiz_data = get_post_meta( $blueprint_id, '_skillsprint_quiz_' . $quiz_id, true );
        
        if ( ! $quiz_data || empty( $quiz_data['questions'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Quiz not found.', 'skillsprint' ) ) );
        }
        
        // Check if user has reached max attempts
        $max_attempts = isset( $quiz_data['max_attempts'] ) ? intval( $quiz_data['max_attempts'] ) : 3;
        $settings = get_option( 'skillsprint_settings' );
        $default_max_attempts = isset( $settings['max_quiz_attempts'] ) ? intval( $settings['max_quiz_attempts'] ) : 3;
        
        if ( $max_attempts <= 0 ) {
            $max_attempts = $default_max_attempts;
        }
        
        $current_attempt = SkillSprint_DB::get_latest_quiz_attempt( $user_id, $blueprint_id, $quiz_id );
        $next_attempt = $current_attempt + 1;
        
        if ( $max_attempts > 0 && $current_attempt >= $max_attempts ) {
            wp_send_json_error( array( 'message' => __( 'You have reached the maximum number of attempts for this quiz.', 'skillsprint' ) ) );
        }
        
        // Process quiz answers
        $total_points = 0;
        $earned_points = 0;
        $correct_count = 0;
        $question_results = array();
        
        foreach ( $quiz_data['questions'] as $question ) {
            $question_id = $question['id'];
            $points = isset( $question['points'] ) ? intval( $question['points'] ) : 1;
            $total_points += $points;
            
            $user_answer = isset( $answers[$question_id] ) ? $answers[$question_id] : '';
            $is_correct = false;
            
            // Evaluate answer based on question type
            switch ( $question['type'] ) {
                case 'multiple_choice':
                    $is_correct = $user_answer === $question['correct_answer'];
                    break;
                    
                case 'true_false':
                    $is_correct = $user_answer === $question['correct_answer'];
                    break;
                    
                case 'multiple_answer':
                    // Sort answers for consistent comparison
                    sort( $user_answer );
                    $correct = $question['correct_answer'];
                    sort( $correct );
                    $is_correct = $user_answer == $correct;
                    break;
                    
                case 'matching':
                    $is_correct = true;
                    foreach ( $question['correct_answer'] as $key => $value ) {
                        if ( ! isset( $user_answer[$key] ) || $user_answer[$key] !== $value ) {
                            $is_correct = false;
                            break;
                        }
                    }
                    break;
                    
                case 'short_answer':
                    $is_correct = false;
                    $user_answer_lower = strtolower( trim( $user_answer ) );
                    
                    foreach ( $question['correct_answer'] as $accepted_answer ) {
                        if ( strtolower( trim( $accepted_answer ) ) === $user_answer_lower ) {
                            $is_correct = true;
                            break;
                        }
                    }
                    break;
            }
            
            // Calculate points
            if ( $is_correct ) {
                $earned_points += $points;
                $correct_count++;
            }
            
            // Save response to database
            SkillSprint_DB::save_quiz_response(
                $user_id,
                $blueprint_id,
                $quiz_id,
                $question_id,
                is_array( $user_answer ) ? json_encode( $user_answer ) : $user_answer,
                $is_correct,
                $is_correct ? $points : 0,
                $next_attempt
            );
            
            // Add to question results
            $question_results[$question_id] = array(
                'is_correct' => $is_correct,
                'points_earned' => $is_correct ? $points : 0,
                'correct_answer' => $question['correct_answer'],
                'explanation' => isset( $question['explanation'] ) ? $question['explanation'] : ''
            );
        }
        
        // Calculate score
        $total_questions = count( $quiz_data['questions'] );
        $score_percentage = $total_points > 0 ? round( ( $earned_points / $total_points ) * 100 ) : 0;
        $passing_score = isset( $quiz_data['passing_score'] ) ? intval( $quiz_data['passing_score'] ) : 70;
        $settings = get_option( 'skillsprint_settings' );
        $default_passing_score = isset( $settings['default_quiz_pass_score'] ) ? intval( $settings['default_quiz_pass_score'] ) : 70;
        
        if ( $passing_score <= 0 ) {
            $passing_score = $default_passing_score;
        }
        
        $passed = $score_percentage >= $passing_score;
        
        // Fire action for quiz completion
        do_action( 'skillsprint_quiz_completed', $user_id, $blueprint_id, $quiz_id, $passed );
        
        // Return results
        wp_send_json_success( array(
            'message' => __( 'Quiz submitted successfully!', 'skillsprint' ),
            'score' => array(
                'earned_points' => $earned_points,
                'total_points' => $total_points,
                'correct_count' => $correct_count,
                'total_questions' => $total_questions,
                'percentage' => $score_percentage,
                'passed' => $passed,
                'passing_score' => $passing_score,
                'attempt' => $next_attempt,
                'max_attempts' => $max_attempts
            ),
            'question_results' => $question_results
        ) );
    }
    
    /**
     * AJAX handler for getting quiz results.
     *
     * @since    1.0.0
     */
    public function ajax_get_quiz_results() {
        // Check nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skillsprint_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skillsprint' ) ) );
        }
        
        // Check user login
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in to view quiz results.', 'skillsprint' ) ) );
        }
        
        // Get parameters
        $user_id = get_current_user_id();
        $blueprint_id = isset( $_POST['blueprint_id'] ) ? intval( $_POST['blueprint_id'] ) : 0;
        $quiz_id = isset( $_POST['quiz_id'] ) ? sanitize_text_field( $_POST['quiz_id'] ) : '';
        $attempt = isset( $_POST['attempt'] ) ? intval( $_POST['attempt'] ) : 0;
        
        if ( ! $blueprint_id || empty( $quiz_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid quiz request.', 'skillsprint' ) ) );
        }
        
        // Get quiz data
        $quiz_data = get_post_meta( $blueprint_id, '_skillsprint_quiz_' . $quiz_id, true );
        
        if ( ! $quiz_data ) {
            wp_send_json_error( array( 'message' => __( 'Quiz not found.', 'skillsprint' ) ) );
        }
        
        // Get the latest attempt if none specified
        if ( $attempt <= 0 ) {
            $attempt = SkillSprint_DB::get_latest_quiz_attempt( $user_id, $blueprint_id, $quiz_id );
        }
        
        // If no attempts yet, return empty results
        if ( $attempt <= 0 ) {
            wp_send_json_success( array(
                'has_attempts' => false,
                'message' => __( 'You have not attempted this quiz yet.', 'skillsprint' )
            ) );
        }
        
        // Get quiz responses
        $responses = SkillSprint_DB::get_quiz_responses( $user_id, $blueprint_id, $quiz_id, $attempt );
        
        if ( empty( $responses ) ) {
            wp_send_json_error( array( 'message' => __( 'No results found for this attempt.', 'skillsprint' ) ) );
        }
        
        // Get score info
        $score_info = SkillSprint_DB::calculate_quiz_score( $user_id, $blueprint_id, $quiz_id, $attempt );
        $passing_score = isset( $quiz_data['passing_score'] ) ? intval( $quiz_data['passing_score'] ) : 70;
        $settings = get_option( 'skillsprint_settings' );
        $default_passing_score = isset( $settings['default_quiz_pass_score'] ) ? intval( $settings['default_quiz_pass_score'] ) : 70;
        
        if ( $passing_score <= 0 ) {
            $passing_score = $default_passing_score;
        }
        
        $passed = $score_info['score_percentage'] >= $passing_score;
        
        // Format response data
        $question_results = array();
        
        foreach ( $responses as $response ) {
            $question_id = $response['question_id'];
            $user_answer = $response['user_answer'];
            
            // Try to decode JSON if it's an array
            if ( strpos( $user_answer, '[' ) === 0 || strpos( $user_answer, '{' ) === 0 ) {
                $decoded = json_decode( $user_answer, true );
                if ( $decoded !== null ) {
                    $user_answer = $decoded;
                }
            }
            
            // Find question data
            $question_data = null;
            foreach ( $quiz_data['questions'] as $question ) {
                if ( $question['id'] === $question_id ) {
                    $question_data = $question;
                    break;
                }
            }
            
            if ( $question_data ) {
                $question_results[$question_id] = array(
                    'question' => $question_data['text'],
                    'type' => $question_data['type'],
                    'user_answer' => $user_answer,
                    'correct_answer' => $question_data['correct_answer'],
                    'is_correct' => (bool) $response['is_correct'],
                    'points_earned' => intval( $response['points_earned'] ),
                    'points_possible' => isset( $question_data['points'] ) ? intval( $question_data['points'] ) : 1,
                    'explanation' => isset( $question_data['explanation'] ) ? $question_data['explanation'] : ''
                );
            }
        }
        
        // Return results
        wp_send_json_success( array(
            'has_attempts' => true,
            'score' => array(
                'earned_points' => $score_info['total_points'],
                'total_questions' => $score_info['total_questions'],
                'correct_count' => $score_info['correct_answers'],
                'percentage' => $score_info['score_percentage'],
                'passed' => $passed,
                'passing_score' => $passing_score,
                'attempt' => $attempt,
                'max_attempts' => isset( $quiz_data['max_attempts'] ) ? intval( $quiz_data['max_attempts'] ) : 3
            ),
            'question_results' => $question_results
        ) );
    }
}