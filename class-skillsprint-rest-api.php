<?php
/**
 * REST API functionality of the plugin.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/includes
 */

/**
 * REST API functionality of the plugin.
 *
 * Defines and registers REST API endpoints.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/includes
 */
class SkillSprint_REST_API {

    /**
     * Register REST API routes
     *
     * @since    1.0.0
     */
    public function register_routes() {
        register_rest_route('skillsprint/v1', '/blueprints', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_blueprints'),
            'permission_callback' => '__return_true',
        ));
        
        register_rest_route('skillsprint/v1', '/blueprints/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_blueprint'),
            'permission_callback' => '__return_true',
        ));
        
        register_rest_route('skillsprint/v1', '/blueprint-categories', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_blueprint_categories'),
            'permission_callback' => '__return_true',
        ));
        
        register_rest_route('skillsprint/v1', '/blueprint-difficulties', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_blueprint_difficulties'),
            'permission_callback' => '__return_true',
        ));
        
        // Endpoints that require authentication
        register_rest_route('skillsprint/v1', '/user/progress', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_user_progress'),
            'permission_callback' => array($this, 'user_logged_in'),
        ));
        
        register_rest_route('skillsprint/v1', '/user/progress/(?P<blueprint_id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_user_blueprint_progress'),
            'permission_callback' => array($this, 'user_logged_in'),
        ));
        
        register_rest_route('skillsprint/v1', '/user/progress/(?P<blueprint_id>\d+)/day/(?P<day_number>\d+)/start', array(
            'methods' => 'POST',
            'callback' => array($this, 'mark_day_started'),
            'permission_callback' => array($this, 'user_logged_in'),
        ));
        
        register_rest_route('skillsprint/v1', '/user/progress/(?P<blueprint_id>\d+)/day/(?P<day_number>\d+)/complete', array(
            'methods' => 'POST',
            'callback' => array($this, 'mark_day_completed'),
            'permission_callback' => array($this, 'user_logged_in'),
        ));
        
        register_rest_route('skillsprint/v1', '/user/achievements', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_user_achievements'),
            'permission_callback' => array($this, 'user_logged_in'),
        ));
        
        register_rest_route('skillsprint/v1', '/user/points', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_user_points'),
            'permission_callback' => array($this, 'user_logged_in'),
        ));
        
        register_rest_route('skillsprint/v1', '/leaderboard', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_leaderboard'),
            'permission_callback' => '__return_true',
        ));
        
        register_rest_route('skillsprint/v1', '/quiz/(?P<blueprint_id>\d+)/(?P<quiz_id>[a-zA-Z0-9_-]+)/submit', array(
            'methods' => 'POST',
            'callback' => array($this, 'submit_quiz'),
            'permission_callback' => array($this, 'user_logged_in'),
        ));
        
        register_rest_route('skillsprint/v1', '/quiz/(?P<blueprint_id>\d+)/(?P<quiz_id>[a-zA-Z0-9_-]+)/results', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_quiz_results'),
            'permission_callback' => array($this, 'user_logged_in'),
        ));
    }
    
    /**
     * Check if user is logged in
     *
     * @since    1.0.0
     * @return   bool   Whether the user is logged in
     */
    public function user_logged_in() {
        return is_user_logged_in();
    }
    
    /**
     * Get blueprints
     *
     * @since    1.0.0
     * @param    WP_REST_Request $request Request object
     * @return   WP_REST_Response Response object
     */
    public function get_blueprints($request) {
        $args = array(
            'post_type' => 'blueprint',
            'post_status' => 'publish',
            'posts_per_page' => $request->get_param('per_page') ? intval($request->get_param('per_page')) : 10,
            'paged' => $request->get_param('page') ? intval($request->get_param('page')) : 1,
            'orderby' => $request->get_param('orderby') ? $request->get_param('orderby') : 'title',
            'order' => $request->get_param('order') ? $request->get_param('order') : 'ASC',
        );
        
        // Add category filter
        if ($request->get_param('category')) {
            $args['tax_query'][] = array(
                'taxonomy' => 'blueprint_category',
                'field' => 'slug',
                'terms' => $request->get_param('category'),
            );
        }
        
        // Add difficulty filter
        if ($request->get_param('difficulty')) {
            $args['tax_query'][] = array(
                'taxonomy' => 'blueprint_difficulty',
                'field' => 'slug',
                'terms' => $request->get_param('difficulty'),
            );
        }
        
        // Add search filter
        if ($request->get_param('search')) {
            $args['s'] = $request->get_param('search');
        }
        
        $query = new WP_Query($args);
        $blueprints = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                
                $blueprint_id = get_the_ID();
                
                // Get blueprint data using the existing model
                $blueprint_data = SkillSprint_Blueprint::get_blueprint_data($blueprint_id);
                
                // Get user progress if user is logged in
                if (is_user_logged_in() && $request->get_param('include_progress')) {
                    $user_id = get_current_user_id();
                    $blueprint_data['user_progress'] = array(
                        'completion_percentage' => SkillSprint_DB::get_blueprint_completion_percentage($user_id, $blueprint_id),
                        'days' => SkillSprint_DB::get_user_blueprint_progress($user_id, $blueprint_id),
                    );
                }
                
                $blueprints[] = $blueprint_data;
            }
        }
        
        wp_reset_postdata();
        
        return rest_ensure_response(array(
            'total' => $query->found_posts,
            'total_pages' => $query->max_num_pages,
            'blueprints' => $blueprints,
        ));
    }
    
    /**
     * Get a single blueprint
     *
     * @since    1.0.0
     * @param    WP_REST_Request $request Request object
     * @return   WP_REST_Response Response object
     */
    public function get_blueprint($request) {
        $blueprint_id = $request->get_param('id');
        
        if (!$blueprint_id) {
            return new WP_Error('invalid_blueprint', __('Invalid blueprint ID.', 'skillsprint'), array('status' => 404));
        }
        
        $blueprint = get_post($blueprint_id);
        
        if (!$blueprint || $blueprint->post_type !== 'blueprint' || $blueprint->post_status !== 'publish') {
            return new WP_Error('blueprint_not_found', __('Blueprint not found.', 'skillsprint'), array('status' => 404));
        }
        
        // Get quiz data
        $quiz_data = get_post_meta($blueprint_id, '_skillsprint_quiz_' . $quiz_id, true);
        
        if (!$quiz_data) {
            return new WP_Error('quiz_not_found', __('Quiz not found.', 'skillsprint'), array('status' => 404));
        }
        
        // Get the latest attempt if none specified
        if ($attempt <= 0) {
            $attempt = SkillSprint_DB::get_latest_quiz_attempt($user_id, $blueprint_id, $quiz_id);
        }
        
        // If no attempts yet, return empty results
        if ($attempt <= 0) {
            return rest_ensure_response(array(
                'has_attempts' => false,
                'message' => __('You have not attempted this quiz yet.', 'skillsprint'),
            ));
        }
        
        // Get quiz responses
        $responses = SkillSprint_DB::get_quiz_responses($user_id, $blueprint_id, $quiz_id, $attempt);
        
        if (empty($responses)) {
            return new WP_Error('no_results', __('No results found for this attempt.', 'skillsprint'), array('status' => 404));
        }
        
        // Get score info
        $score_info = SkillSprint_DB::calculate_quiz_score($user_id, $blueprint_id, $quiz_id, $attempt);
        $passing_score = isset($quiz_data['passing_score']) ? intval($quiz_data['passing_score']) : 70;
        $settings = get_option('skillsprint_settings');
        $default_passing_score = isset($settings['default_quiz_pass_score']) ? intval($settings['default_quiz_pass_score']) : 70;
        
        if ($passing_score <= 0) {
            $passing_score = $default_passing_score;
        }
        
        $passed = $score_info['score_percentage'] >= $passing_score;
        
        // Format response data
        $question_results = array();
        
        foreach ($responses as $response) {
            $question_id = $response['question_id'];
            $user_answer = $response['user_answer'];
            
            // Try to decode JSON if it's an array
            if (strpos($user_answer, '[') === 0 || strpos($user_answer, '{') === 0) {
                $decoded = json_decode($user_answer, true);
                if ($decoded !== null) {
                    $user_answer = $decoded;
                }
            }
            
            // Find question data
            $question_data = null;
            foreach ($quiz_data['questions'] as $question) {
                if ($question['id'] === $question_id) {
                    $question_data = $question;
                    break;
                }
            }
            
            if ($question_data) {
                $question_results[$question_id] = array(
                    'question' => $question_data['text'],
                    'type' => $question_data['type'],
                    'user_answer' => $user_answer,
                    'correct_answer' => $question_data['correct_answer'],
                    'is_correct' => (bool) $response['is_correct'],
                    'points_earned' => intval($response['points_earned']),
                    'points_possible' => isset($question_data['points']) ? intval($question_data['points']) : 1,
                    'explanation' => isset($question_data['explanation']) ? $question_data['explanation'] : '',
                );
            }
        }
        
        return rest_ensure_response(array(
            'has_attempts' => true,
            'score' => array(
                'earned_points' => $score_info['total_points'],
                'total_questions' => $score_info['total_questions'],
                'correct_count' => $score_info['correct_answers'],
                'percentage' => $score_info['score_percentage'],
                'passed' => $passed,
                'passing_score' => $passing_score,
                'attempt' => $attempt,
                'max_attempts' => isset($quiz_data['max_attempts']) ? intval($quiz_data['max_attempts']) : 3,
            ),
            'question_results' => $question_results,
        ));
    }


        
        
    
    /**
     * Get user achievements
     *
     * @since    1.0.0
     * @param    WP_REST_Request $request Request object
     * @return   WP_REST_Response Response object
     */
    public function get_user_achievements($request) {
        $user_id = get_current_user_id();
        $achievements = SkillSprint_DB::get_user_achievements($user_id);
        
        $formatted_achievements = array();
        
        foreach ($achievements as $achievement) {
            // Get achievement metadata from the Dashboard class
            $dashboard = new SkillSprint_Dashboard();
            $achievement_info = $dashboard->get_achievement_info($achievement['achievement_id']);
            
            if ($achievement_info) {
                $formatted_achievements[] = array(
                    'id' => $achievement['achievement_id'],
                    'title' => $achievement_info['title'],
                    'description' => $achievement_info['description'],
                    'icon' => $achievement_info['icon'],
                    'date_earned' => $achievement['date_earned'],
                    'meta' => $achievement['meta'],
                );
            }
        }
        
        return rest_ensure_response($formatted_achievements);
    }
    
    /**
     * Get user points
     *
     * @since    1.0.0
     * @param    WP_REST_Request $request Request object
     * @return   WP_REST_Response Response object
     */
    public function get_user_points($request) {
        $user_id = get_current_user_id();
        $total_points = SkillSprint_DB::get_user_total_points($user_id);
        
        $limit = $request->get_param('limit') ? intval($request->get_param('limit')) : 10;
        $offset = $request->get_param('offset') ? intval($request->get_param('offset')) : 0;
        $blueprint_id = $request->get_param('blueprint_id') ? intval($request->get_param('blueprint_id')) : null;
        
        $points_history = SkillSprint_DB::get_user_points_history($user_id, $limit, $offset, $blueprint_id);
        
        return rest_ensure_response(array(
            'total_points' => $total_points,
            'points_history' => $points_history,
        ));
    }
    
    /**
     * Get leaderboard
     *
     * @since    1.0.0
     * @param    WP_REST_Request $request Request object
     * @return   WP_REST_Response Response object
     */
    public function get_leaderboard($request) {
        $limit = $request->get_param('limit') ? intval($request->get_param('limit')) : 10;
        $offset = $request->get_param('offset') ? intval($request->get_param('offset')) : 0;
        
        $leaderboard = SkillSprint_DB::get_leaderboard($limit, $offset);
        
        return rest_ensure_response($leaderboard);
    }
    
    /**
     * Submit a quiz
     *
     * @since    1.0.0
     * @param    WP_REST_Request $request Request object
     * @return   WP_REST_Response Response object
     */
    public function submit_quiz($request) {
        $user_id = get_current_user_id();
        $blueprint_id = $request->get_param('blueprint_id');
        $quiz_id = $request->get_param('quiz_id');
        $answers = $request->get_param('answers');
        
        if (!$blueprint_id || empty($quiz_id) || empty($answers)) {
            return new WP_Error('invalid_parameters', __('Invalid quiz submission.', 'skillsprint'), array('status' => 400));
        }
        
        $blueprint = get_post($blueprint_id);
        
        if (!$blueprint || $blueprint->post_type !== 'blueprint' || $blueprint->post_status !== 'publish') {
            return new WP_Error('blueprint_not_found', __('Blueprint not found.', 'skillsprint'), array('status' => 404));
        }
        
        // Get quiz data
        $quiz_data = get_post_meta($blueprint_id, '_skillsprint_quiz_' . $quiz_id, true);
        
        if (!$quiz_data || empty($quiz_data['questions'])) {
            return new WP_Error('quiz_not_found', __('Quiz not found.', 'skillsprint'), array('status' => 404));
        }
        
        // Check if user has reached max attempts
        $max_attempts = isset($quiz_data['max_attempts']) ? intval($quiz_data['max_attempts']) : 3;
        $settings = get_option('skillsprint_settings');
        $default_max_attempts = isset($settings['max_quiz_attempts']) ? intval($settings['max_quiz_attempts']) : 3;
        
        if ($max_attempts <= 0) {
            $max_attempts = $default_max_attempts;
        }
        
        $current_attempt = SkillSprint_DB::get_latest_quiz_attempt($user_id, $blueprint_id, $quiz_id);
        $next_attempt = $current_attempt + 1;
        
        if ($max_attempts > 0 && $current_attempt >= $max_attempts) {
            return new WP_Error('max_attempts_reached', __('You have reached the maximum number of attempts for this quiz.', 'skillsprint'), array('status' => 400));
        }
        
        // Process quiz answers
        $total_points = 0;
        $earned_points = 0;
        $correct_count = 0;
        $question_results = array();
        
        foreach ($quiz_data['questions'] as $question) {
            $question_id = $question['id'];
            $points = isset($question['points']) ? intval($question['points']) : 1;
            $total_points += $points;
            
            $user_answer = isset($answers[$question_id]) ? $answers[$question_id] : '';
            $is_correct = false;
            
            // Evaluate answer based on question type
            switch ($question['type']) {
                case 'multiple_choice':
                    $is_correct = $user_answer === $question['correct_answer'];
                    break;
                    
                case 'true_false':
                    $is_correct = $user_answer === $question['correct_answer'];
                    break;
                    
                case 'multiple_answer':
                    // Sort answers for consistent comparison
                    sort($user_answer);
                    $correct = $question['correct_answer'];
                    sort($correct);
                    $is_correct = $user_answer == $correct;
                    break;
                    
                case 'matching':
                    $is_correct = true;
                    foreach ($question['correct_answer'] as $key => $value) {
                        if (!isset($user_answer[$key]) || $user_answer[$key] !== $value) {
                            $is_correct = false;
                            break;
                        }
                    }
                    break;
                    
                case 'short_answer':
                    $is_correct = false;
                    $user_answer_lower = strtolower(trim($user_answer));
                    
                    foreach ($question['correct_answer'] as $accepted_answer) {
                        if (strtolower(trim($accepted_answer)) === $user_answer_lower) {
                            $is_correct = true;
                            break;
                        }
                    }
                    break;
            }
            
            // Calculate points
            if ($is_correct) {
                $earned_points += $points;
                $correct_count++;
            }
            
            // Save response to database
            SkillSprint_DB::save_quiz_response(
                $user_id,
                $blueprint_id,
                $quiz_id,
                $question_id,
                is_array($user_answer) ? json_encode($user_answer) : $user_answer,
                $is_correct,
                $is_correct ? $points : 0,
                $next_attempt
            );
            
            // Add to question results
            $question_results[$question_id] = array(
                'is_correct' => $is_correct,
                'points_earned' => $is_correct ? $points : 0,
                'correct_answer' => $question['correct_answer'],
                'explanation' => isset($question['explanation']) ? $question['explanation'] : '',
            );
        }
        
        // Calculate score
        $total_questions = count($quiz_data['questions']);
        $score_percentage = $total_points > 0 ? round(($earned_points / $total_points) * 100) : 0;
        $passing_score = isset($quiz_data['passing_score']) ? intval($quiz_data['passing_score']) : 70;
        $settings = get_option('skillsprint_settings');
        $default_passing_score = isset($settings['default_quiz_pass_score']) ? intval($settings['default_quiz_pass_score']) : 70;
        
        if ($passing_score <= 0) {
            $passing_score = $default_passing_score;
        }
        
        $passed = $score_percentage >= $passing_score;
        
        // Fire action for quiz completion
        do_action('skillsprint_quiz_completed', $user_id, $blueprint_id, $quiz_id, $passed);
        
        return rest_ensure_response(array(
            'success' => true,
            'message' => __('Quiz submitted successfully!', 'skillsprint'),
            'score' => array(
                'earned_points' => $earned_points,
                'total_points' => $total_points,
                'correct_count' => $correct_count,
                'total_questions' => $total_questions,
                'percentage' => $score_percentage,
                'passed' => $passed,
                'passing_score' => $passing_score,
                'attempt' => $next_attempt,
                'max_attempts' => $max_attempts,
            ),
            'question_results' => $question_results,
        ));
    }
    
    /**
     * Get quiz results
     *
     * @since    1.0.0
     * @param    WP_REST_Request $request Request object
     * @return   WP_REST_Response Response object
     */
    public function get_quiz_results($request) {
        $user_id = get_current_user_id();
        $blueprint_id = $request->get_param('blueprint_id');
        $quiz_id = $request->get_param('quiz_id');
        $attempt = $request->get_param('attempt') ? intval($request->get_param('attempt')) : 0;
        
        if (!$blueprint_id || empty($quiz_id)) {
            return new WP_Error('invalid_parameters', __('Invalid quiz request.', 'skillsprint'), array('status' => 400));
        }
        
        $blueprint = get_post($blueprint_id);
        
        if (!$blueprint || $blueprint->post_type !== 'blueprint' || $blueprint->post_status !== 'publish') {
            return new WP_Error('blueprint_not_found', __('Blueprint not found.', 'skillsprint'), array('status' => 404));
        }
        
        // Increment view count
        SkillSprint_Blueprint::increment_view_count($blueprint_id);
        
        // Get blueprint data using the existing model
        $blueprint_data = SkillSprint_Blueprint::get_blueprint_data($blueprint_id);
        
        // Get user progress if user is logged in
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $blueprint_data['user_progress'] = array(
                'completion_percentage' => SkillSprint_DB::get_blueprint_completion_percentage($user_id, $blueprint_id),
                'days' => SkillSprint_DB::get_user_blueprint_progress($user_id, $blueprint_id),
            );
        }
        
        return rest_ensure_response($blueprint_data);
    }
    
    /**
     * Get blueprint categories
     *
     * @since    1.0.0
     * @param    WP_REST_Request $request Request object
     * @return   WP_REST_Response Response object
     */
    public function get_blueprint_categories($request) {
        $categories = get_terms(array(
            'taxonomy' => 'blueprint_category',
            'hide_empty' => $request->get_param('hide_empty') ? (bool)$request->get_param('hide_empty') : true,
        ));
        
        $data = array();
        
        if (!is_wp_error($categories) && !empty($categories)) {
            foreach ($categories as $category) {
                $data[] = array(
                    'id' => $category->term_id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'count' => $category->count,
                );
            }
        }
        
        return rest_ensure_response($data);
    }
    
    /**
     * Get blueprint difficulties
     *
     * @since    1.0.0
     * @param    WP_REST_Request $request Request object
     * @return   WP_REST_Response Response object
     */
    public function get_blueprint_difficulties($request) {
        $difficulties = get_terms(array(
            'taxonomy' => 'blueprint_difficulty',
            'hide_empty' => $request->get_param('hide_empty') ? (bool)$request->get_param('hide_empty') : true,
        ));
        
        $data = array();
        
        if (!is_wp_error($difficulties) && !empty($difficulties)) {
            foreach ($difficulties as $difficulty) {
                $data[] = array(
                    'id' => $difficulty->term_id,
                    'name' => $difficulty->name,
                    'slug' => $difficulty->slug,
                    'description' => $difficulty->description,
                    'count' => $difficulty->count,
                );
            }
        }
        
        return rest_ensure_response($data);
    }
    
    /**
     * Get user progress
     *
     * @since    1.0.0
     * @param    WP_REST_Request $request Request object
     * @return   WP_REST_Response Response object
     */
    public function get_user_progress($request) {
        $user_id = get_current_user_id();
        
        $in_progress = SkillSprint_Blueprint::get_user_in_progress_blueprints($user_id, -1);
        $completed = SkillSprint_Blueprint::get_user_completed_blueprints($user_id, -1);
        
        return rest_ensure_response(array(
            'in_progress' => $in_progress,
            'completed' => $completed,
        ));
    }
    
    /**
     * Get user blueprint progress
     *
     * @since    1.0.0
     * @param    WP_REST_Request $request Request object
     * @return   WP_REST_Response Response object
     */
    public function get_user_blueprint_progress($request) {
        $user_id = get_current_user_id();
        $blueprint_id = $request->get_param('blueprint_id');
        
        if (!$blueprint_id) {
            return new WP_Error('invalid_blueprint', __('Invalid blueprint ID.', 'skillsprint'), array('status' => 404));
        }
        
        $blueprint = get_post($blueprint_id);
        
        if (!$blueprint || $blueprint->post_type !== 'blueprint' || $blueprint->post_status !== 'publish') {
            return new WP_Error('blueprint_not_found', __('Blueprint not found.', 'skillsprint'), array('status' => 404));
        }
        
        $progress = SkillSprint_DB::get_user_blueprint_progress($user_id, $blueprint_id);
        $completion_percentage = SkillSprint_DB::get_blueprint_completion_percentage($user_id, $blueprint_id);
        
        return rest_ensure_response(array(
            'progress' => $progress,
            'completion_percentage' => $completion_percentage,
        ));
    }
    
    /**
     * Mark a day as started
     *
     * @since    1.0.0
     * @param    WP_REST_Request $request Request object
     * @return   WP_REST_Response Response object
     */
    public function mark_day_started($request) {
        $user_id = get_current_user_id();
        $blueprint_id = $request->get_param('blueprint_id');
        $day_number = $request->get_param('day_number');
        
        if (!$blueprint_id || !$day_number) {
            return new WP_Error('invalid_parameters', __('Invalid blueprint ID or day number.', 'skillsprint'), array('status' => 400));
        }
        
        $blueprint = get_post($blueprint_id);
        
        if (!$blueprint || $blueprint->post_type !== 'blueprint' || $blueprint->post_status !== 'publish') {
            return new WP_Error('blueprint_not_found', __('Blueprint not found.', 'skillsprint'), array('status' => 404));
        }
        
        // Check if user can access this day
        $can_access = apply_filters('skillsprint_can_access_day', true, $user_id, $blueprint_id, $day_number);
        
        if (!$can_access) {
            return new WP_Error('access_denied', __('You do not have access to this day.', 'skillsprint'), array('status' => 403));
        }
        
        // Mark day as started
        $result = SkillSprint_DB::mark_day_started($user_id, $blueprint_id, $day_number);
        
        if ($result) {
            return rest_ensure_response(array(
                'success' => true,
                'message' => __('Day started successfully!', 'skillsprint'),
                'day_number' => $day_number,
                'status' => 'in_progress',
            ));
        } else {
            return new WP_Error('error_marking_day', __('Error marking day as started.', 'skillsprint'), array('status' => 500));
        }
    }
    
    /**
     * Mark a day as completed
     *
     * @since    1.0.0
     * @param    WP_REST_Request $request Request object
     * @return   WP_REST_Response Response object
     */
    public function mark_day_completed($request) {
        $user_id = get_current_user_id();
        $blueprint_id = $request->get_param('blueprint_id');
        $day_number = $request->get_param('day_number');
        $notes = $request->get_param('notes');
        
        if (!$blueprint_id || !$day_number) {
            return new WP_Error('invalid_parameters', __('Invalid blueprint ID or day number.', 'skillsprint'), array('status' => 400));
        }
        
        $blueprint = get_post($blueprint_id);
        
        if (!$blueprint || $blueprint->post_type !== 'blueprint' || $blueprint->post_status !== 'publish') {
            return new WP_Error('blueprint_not_found', __('Blueprint not found.', 'skillsprint'), array('status' => 404));
        }
        
        // Check if user can access this day
        $can_access = apply_filters('skillsprint_can_access_day', true, $user_id, $blueprint_id, $day_number);
        
        if (!$can_access) {
            return new WP_Error('access_denied', __('You do not have access to this day.', 'skillsprint'), array('status' => 403));
        }
        // Check if there's a quiz for this day that needs to be passed
        $day_data = SkillSprint_DB::get_blueprint_day_data($blueprint_id, $day_number);
        
        if ($day_data && isset($day_data['quiz_id']) && !empty($day_data['quiz_id'])) {
            // Get quiz data
            $quiz_id = $day_data['quiz_id'];
            $quiz_data = get_post_meta($blueprint_id, '_skillsprint_quiz_' . $quiz_id, true);
            
            if ($quiz_data) {
                // Check if user has passed the quiz
                $passed = SkillSprint_DB::did_user_pass_quiz($user_id, $blueprint_id, $quiz_id);
                
                if (!$passed) {
                    return new WP_Error('quiz_not_passed', __('You need to pass the quiz to complete this day.', 'skillsprint'), array('status' => 400));
                }
            }
        }
        
        // Mark day as completed
        $result = SkillSprint_DB::mark_day_completed($user_id, $blueprint_id, $day_number, $notes);
        
        if ($result) {
            // Check if the entire blueprint is completed
            $completion_percentage = SkillSprint_DB::get_blueprint_completion_percentage($user_id, $blueprint_id);
            $blueprint_completed = $completion_percentage == 100;
            
            // Get next day if available
            $days_data = SkillSprint_DB::get_blueprint_days_data($blueprint_id);
            $next_day = null;
            
            if ($day_number < count($days_data)) {
                $next_day = $day_number + 1;
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => __('Day completed successfully!', 'skillsprint'),
                'day_number' => $day_number,
                'status' => 'completed',
                'next_day' => $next_day,
                'blueprint_completed' => $blueprint_completed,
                'completion_percentage' => $completion_percentage,
            ));
        } else {
            return new WP_Error('error_marking_day', __('Error marking day as completed.', 'skillsprint'), array('status' => 500));
        }



        
    }
}