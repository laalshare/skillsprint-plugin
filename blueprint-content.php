<?php
/**
 * Blueprint content template
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/public/partials
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="skillsprint-blueprint" data-blueprint="<?php echo esc_attr($post->ID); ?>">
    <div class="skillsprint-blueprint-header">
        <div class="skillsprint-container">
            <h1 class="skillsprint-blueprint-title"><?php the_title(); ?></h1>
            
            <div class="skillsprint-blueprint-meta">
                <div class="skillsprint-blueprint-meta-item">
                    <i class="dashicons dashicons-admin-users"></i>
                    <?php echo esc_html(get_the_author()); ?>
                </div>
                
                <div class="skillsprint-blueprint-meta-item">
                    <i class="dashicons dashicons-calendar-alt"></i>
                    <?php echo esc_html(get_the_date()); ?>
                </div>
                
                <?php
                // Get difficulty
                $difficulty_terms = wp_get_post_terms($post->ID, 'blueprint_difficulty');
                if (!empty($difficulty_terms) && !is_wp_error($difficulty_terms)) {
                    $difficulty = $difficulty_terms[0];
                    ?>
                    <div class="skillsprint-blueprint-meta-item">
                        <i class="dashicons dashicons-chart-bar"></i>
                        <span class="difficulty-badge difficulty-<?php echo esc_attr($difficulty->slug); ?>">
                            <?php echo esc_html($difficulty->name); ?>
                        </span>
                    </div>
                    <?php
                }
                
                // Get estimated completion time
                $estimated_time = get_post_meta($post->ID, '_skillsprint_estimated_completion_time', true);
                if (!empty($estimated_time)) {
                    ?>
                    <div class="skillsprint-blueprint-meta-item">
                        <i class="dashicons dashicons-clock"></i>
                        <?php echo esc_html($estimated_time); ?>
                    </div>
                    <?php
                }
                ?>
            </div>
            
            <div class="skillsprint-blueprint-description">
                <?php the_excerpt(); ?>
            </div>
            
            <div class="skillsprint-blueprint-actions">
                <?php if (is_user_logged_in()) : ?>
                    <?php
                    // Check if user has already started this blueprint
                    $user_id = get_current_user_id();
                    $progress = SkillSprint_DB::get_user_blueprint_progress($user_id, $post->ID);
                    $completion_percentage = SkillSprint_DB::get_blueprint_completion_percentage($user_id, $post->ID);
                    
                    if (!empty($progress)) {
                        // User has started this blueprint
                        ?>
                        <div class="skillsprint-blueprint-progress">
                            <div class="skillsprint-progress-bar-container">
                                <div class="skillsprint-progress-bar" style="width: <?php echo esc_attr($completion_percentage); ?>%"></div>
                            </div>
                            <div class="skillsprint-progress-text">
                                <span class="skillsprint-progress-percentage"><?php echo esc_html($completion_percentage); ?>%</span>
                                <span class="skillsprint-progress-label">Completed</span>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                <?php else : ?>
                    <a href="#login" class="skillsprint-button skillsprint-login-button">
                        <i class="dashicons dashicons-lock"></i> Log In to Track Progress
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="skillsprint-container">
        <div class="skillsprint-days-nav">
            <?php
            // Generate day tabs
            foreach ($days_data as $day) {
                $day_number = isset($day['day_number']) ? intval($day['day_number']) : 0;
                $day_title = isset($day['title']) ? $day['title'] : sprintf(__('Day %d', 'skillsprint'), $day_number);
                
                // Check if this day is accessible
                $is_locked = false;
                
                if ($day_number > $days_free_access && !is_user_logged_in()) {
                    $is_locked = true;
                } elseif ($user_id) {
                    $is_locked = !apply_filters('skillsprint_can_access_day', true, $user_id, $post->ID, $day_number);
                }
                
                // Check if day is completed
                $is_completed = false;
                if ($user_id && !empty($user_progress)) {
                    foreach ($user_progress as $progress) {
                        if ($progress['day_number'] == $day_number && $progress['progress_status'] == 'completed') {
                            $is_completed = true;
                            break;
                        }
                    }
                }
                
                $tab_class = 'skillsprint-day-tab';
                if ($is_locked) {
                    $tab_class .= ' locked';
                }
                if ($is_completed) {
                    $tab_class .= ' completed';
                }
                ?>
                <button class="<?php echo esc_attr($tab_class); ?>" data-day="<?php echo esc_attr($day_number); ?>">
                    <?php echo esc_html($day_title); ?>
                </button>
                <?php
            }
            ?>
        </div>
        
        <div class="skillsprint-days-content">
            <?php
            // Generate day content
            foreach ($days_data as $day) {
                $day_number = isset($day['day_number']) ? intval($day['day_number']) : 0;
                $day_title = isset($day['title']) ? $day['title'] : sprintf(__('Day %d', 'skillsprint'), $day_number);
                $learning_objectives = isset($day['learning_objectives']) ? $day['learning_objectives'] : '';
                $day_content = isset($day['content']) ? $day['content'] : '';
                $resources = isset($day['resources']) ? $day['resources'] : array();
                $quiz_id = isset($day['quiz_id']) ? $day['quiz_id'] : '';
                
                ?>
                <div class="skillsprint-day-content" data-day="<?php echo esc_attr($day_number); ?>">
                    <div class="skillsprint-day-header">
                        <h2 class="skillsprint-day-title"><?php echo esc_html($day_title); ?></h2>
                        
                        <?php if (!empty($learning_objectives)) : ?>
                            <div class="skillsprint-learning-objectives">
                                <h3 class="skillsprint-learning-objectives-title"><?php esc_html_e('Learning Objectives', 'skillsprint'); ?></h3>
                                <div class="skillsprint-learning-objectives-content">
                                    <?php echo wp_kses_post(nl2br($learning_objectives)); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="skillsprint-day-body">
                        <?php echo apply_filters('the_content', $day_content); ?>
                    </div>
                    
                    <?php if (!empty($resources)) : ?>
                        <div class="skillsprint-day-resources">
                            <h3 class="skillsprint-day-resources-title"><?php esc_html_e('Resources', 'skillsprint'); ?></h3>
                            <ul class="skillsprint-resource-list">
                                <?php foreach ($resources as $resource) : ?>
                                    <li class="skillsprint-resource-item">
                                        <a href="<?php echo esc_url($resource['url']); ?>" class="skillsprint-resource-link" target="_blank">
                                            <i class="dashicons dashicons-<?php echo esc_attr($resource['type'] == 'video' ? 'video-alt3' : ($resource['type'] == 'file' ? 'media-document' : 'admin-links')); ?>"></i>
                                            <?php echo esc_html($resource['title']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($quiz_id)) : ?>
                        <?php
                        // Get quiz data
                        $quiz_data = get_post_meta($post->ID, '_skillsprint_quiz_' . $quiz_id, true);
                        if ($quiz_data && !empty($quiz_data['questions'])) :
                            $quiz_title = isset($quiz_data['title']) ? $quiz_data['title'] : __('Quiz', 'skillsprint');
                            $quiz_description = isset($quiz_data['description']) ? $quiz_data['description'] : '';
                            $passing_score = isset($quiz_data['passing_score']) ? intval($quiz_data['passing_score']) : 70;
                            $max_attempts = isset($quiz_data['max_attempts']) ? intval($quiz_data['max_attempts']) : 3;
                            
                            // Get quiz status if user is logged in
                            $quiz_passed = false;
                            $quiz_attempts = 0;
                            $quiz_score = 0;
                            
                            if (is_user_logged_in()) {
                                $day_progress = isset($user_progress[$day_number]) ? $user_progress[$day_number] : array();
                                $quiz_passed = isset($day_progress['quiz_status']) && $day_progress['quiz_status'] === 'passed';
                                $quiz_attempts = isset($day_progress['quiz_attempts']) ? intval($day_progress['quiz_attempts']) : 0;
                                $quiz_score = isset($day_progress['quiz_score']) ? intval($day_progress['quiz_score']) : 0;
                            }
                            ?>
                            <div class="skillsprint-quiz" data-quiz="<?php echo esc_attr($quiz_id); ?>" data-blueprint="<?php echo esc_attr($post->ID); ?>" data-passed="<?php echo $quiz_passed ? 'true' : 'false'; ?>">
                                <div class="skillsprint-quiz-header">
                                    <h3 class="skillsprint-quiz-title"><?php echo esc_html($quiz_title); ?></h3>
                                    <?php if (!empty($quiz_description)) : ?>
                                        <div class="skillsprint-quiz-description"><?php echo wp_kses_post($quiz_description); ?></div>
                                    <?php endif; ?>
                                    <div class="skillsprint-quiz-meta">
                                        <div class="skillsprint-quiz-meta-item">
                                            <i class="dashicons dashicons-chart-bar"></i>
                                            <?php printf(esc_html__('Passing Score: %d%%', 'skillsprint'), $passing_score); ?>
                                        </div>
                                        <div class="skillsprint-quiz-meta-item">
                                            <i class="dashicons dashicons-update"></i>
                                            <?php printf(esc_html__('Maximum Attempts: %d', 'skillsprint'), $max_attempts); ?>
                                        </div>
                                        <?php if (is_user_logged_in() && $quiz_attempts > 0) : ?>
                                            <div class="skillsprint-quiz-meta-item">
                                                <i class="dashicons dashicons-performance"></i>
                                                <?php printf(esc_html__('Your Attempts: %d', 'skillsprint'), $quiz_attempts); ?>
                                            </div>
                                            <?php if ($quiz_passed) : ?>
                                                <div class="skillsprint-quiz-meta-item">
                                                    <i class="dashicons dashicons-yes"></i>
                                                    <?php printf(esc_html__('Score: %d%% (Passed)', 'skillsprint'), $quiz_score); ?>
                                                </div>
                                            <?php elseif ($quiz_score > 0) : ?>
                                                <div class="skillsprint-quiz-meta-item">
                                                    <i class="dashicons dashicons-no"></i>
                                                    <?php printf(esc_html__('Score: %d%% (Failed)', 'skillsprint'), $quiz_score); ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if ($quiz_passed) : ?>
                                    <div class="skillsprint-quiz-passed-message">
                                        <div class="skillsprint-alert success">
                                            <i class="dashicons dashicons-yes-alt"></i>
                                            <p><?php esc_html_e('You have successfully passed this quiz!', 'skillsprint'); ?></p>
                                        </div>
                                    </div>
                                <?php elseif (is_user_logged_in() && $max_attempts > 0 && $quiz_attempts >= $max_attempts) : ?>
                                    <div class="skillsprint-quiz-max-attempts-message">
                                        <div class="skillsprint-alert warning">
                                            <i class="dashicons dashicons-warning"></i>
                                            <p><?php esc_html_e('You have reached the maximum number of attempts for this quiz.', 'skillsprint'); ?></p>
                                        </div>
                                    </div>
                                <?php elseif (is_user_logged_in()) : ?>
                                    <form class="skillsprint-quiz-form" data-quiz="<?php echo esc_attr($quiz_id); ?>" data-blueprint="<?php echo esc_attr($post->ID); ?>">
                                        <div class="skillsprint-quiz-message"></div>
                                        
                                        <?php foreach ($quiz_data['questions'] as $question) : ?>
                                            <div class="skillsprint-question" data-question="<?php echo esc_attr($question['id']); ?>" data-type="<?php echo esc_attr($question['type']); ?>">
                                                <h4 class="skillsprint-question-text"><?php echo wp_kses_post($question['text']); ?></h4>
                                                
                                                <?php if ($question['type'] === 'multiple_choice') : ?>
                                                    <ul class="skillsprint-question-options">
                                                        <?php foreach ($question['options'] as $option_key => $option_text) : ?>
                                                            <li class="skillsprint-question-option">
                                                                <label>
                                                                    <input type="radio" name="question_<?php echo esc_attr($question['id']); ?>" value="<?php echo esc_attr($option_key); ?>">
                                                                    <?php echo wp_kses_post($option_text); ?>
                                                                </label>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                    
                                                <?php elseif ($question['type'] === 'true_false') : ?>
                                                    <ul class="skillsprint-question-options">
                                                        <li class="skillsprint-question-option">
                                                            <label>
                                                                <input type="radio" name="question_<?php echo esc_attr($question['id']); ?>" value="true">
                                                                <?php esc_html_e('True', 'skillsprint'); ?>
                                                            </label>
                                                        </li>
                                                        <li class="skillsprint-question-option">
                                                            <label>
                                                                <input type="radio" name="question_<?php echo esc_attr($question['id']); ?>" value="false">
                                                                <?php esc_html_e('False', 'skillsprint'); ?>
                                                            </label>
                                                        </li>
                                                    </ul>
                                                    
                                                <?php elseif ($question['type'] === 'multiple_answer') : ?>
                                                    <ul class="skillsprint-question-options">
                                                        <?php foreach ($question['options'] as $option_key => $option_text) : ?>
                                                            <li class="skillsprint-question-option">
                                                                <label>
                                                                    <input type="checkbox" name="question_<?php echo esc_attr($question['id']); ?>[]" value="<?php echo esc_attr($option_key); ?>">
                                                                    <?php echo wp_kses_post($option_text); ?>
                                                                </label>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                    
                                                <?php elseif ($question['type'] === 'matching') : ?>
                                                    <div class="skillsprint-question-options matching">
                                                        <?php foreach ($question['options'] as $option) : ?>
                                                            <div class="skillsprint-question-option">
                                                                <span class="matching-left"><?php echo wp_kses_post($option['left']); ?></span>
                                                                <select name="question_<?php echo esc_attr($question['id']); ?>[<?php echo esc_attr($option['left']); ?>]" data-left="<?php echo esc_attr($option['left']); ?>">
                                                                    <option value=""><?php esc_html_e('Select a match', 'skillsprint'); ?></option>
                                                                    <?php
                                                                    // Get all right options
                                                                    $right_options = array_map(function($opt) {
                                                                        return $opt['right'];
                                                                    }, $question['options']);
                                                                    
                                                                    foreach ($right_options as $right_option) :
                                                                        ?>
                                                                        <option value="<?php echo esc_attr($right_option); ?>"><?php echo wp_kses_post($right_option); ?></option>
                                                                        <?php
                                                                    endforeach;
                                                                    ?>
                                                                </select>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    
                                                <?php elseif ($question['type'] === 'short_answer') : ?>
                                                    <div class="skillsprint-question-option">
                                                        <input type="text" name="question_<?php echo esc_attr($question['id']); ?>" placeholder="<?php esc_attr_e('Your answer', 'skillsprint'); ?>">
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (isset($question['points']) && $question['points'] > 1) : ?>
                                                    <div class="skillsprint-question-points"><?php printf(esc_html__('Points: %d', 'skillsprint'), $question['points']); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                        
                                        <div class="skillsprint-quiz-footer">
                                            <button type="submit" class="skillsprint-button"><?php esc_html_e('Submit Quiz', 'skillsprint'); ?></button>
                                        </div>
                                    </form>
                                <?php else : ?>
                                    <div class="skillsprint-quiz-login-required">
                                        <div class="skillsprint-alert warning">
                                            <i class="dashicons dashicons-lock"></i>
                                            <p><?php esc_html_e('Please log in to take this quiz and track your progress.', 'skillsprint'); ?></p>
                                            <button type="button" class="skillsprint-button skillsprint-login-button"><?php esc_html_e('Log In', 'skillsprint'); ?></button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="skillsprint-quiz-results" style="display:none;">
                                    <h3 class="skillsprint-quiz-results-title"><?php esc_html_e('Quiz Results', 'skillsprint'); ?></h3>
                                    <div class="skillsprint-quiz-score">
                                        <div class="skillsprint-quiz-score-circle">0%</div>
                                        <div class="skillsprint-quiz-score-text">
                                            <div class="skillsprint-quiz-score-label"><?php esc_html_e('Your Score', 'skillsprint'); ?></div>
                                            <div class="skillsprint-quiz-score-value"></div>
                                        </div>
                                    </div>
                                    <ul class="skillsprint-quiz-summary"></ul>
                                    <div class="skillsprint-quiz-actions">
                                        <button class="skillsprint-button retry-quiz"><?php esc_html_e('Retry', 'skillsprint'); ?></button>
                                        <button class="skillsprint-button secondary review-quiz"><?php esc_html_e('Review', 'skillsprint'); ?></button>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <div class="skillsprint-day-actions">
                        <div class="skillsprint-day-navigation">
                            <button class="skillsprint-button outline small skillsprint-day-nav-prev" <?php echo $day_number === 1 ? 'disabled' : ''; ?>>
                                <i class="dashicons dashicons-arrow-left-alt"></i> <?php esc_html_e('Previous Day', 'skillsprint'); ?>
                            </button>
                            <button class="skillsprint-button outline small skillsprint-day-nav-next" <?php echo $day_number === count($days_data) ? 'disabled' : ''; ?>>
                                <?php esc_html_e('Next Day', 'skillsprint'); ?> <i class="dashicons dashicons-arrow-right-alt"></i>
                            </button>
                        </div>
                        
                        <?php
                        // Check if day is already completed
                        $is_completed = false;
                        if ($user_id && !empty($user_progress)) {
                            foreach ($user_progress as $progress) {
                                if ($progress['day_number'] == $day_number && $progress['progress_status'] == 'completed') {
                                    $is_completed = true;
                                    break;
                                }
                            }
                        }
                        
                        if ($is_completed) {
                            ?>
                            <button class="skillsprint-button success skillsprint-complete-day-button" disabled>
                                <?php esc_html_e('Day Completed', 'skillsprint'); ?>
                            </button>
                            <?php
                        } elseif ($user_id) {
                            // For quiz days, check if quiz is passed
                            $quiz_required = !empty($quiz_id) && isset($quiz_data) && !empty($quiz_data['questions']);
                            $quiz_passed = $quiz_required && isset($quiz_passed) ? $quiz_passed : false;
                            $disabled = $quiz_required && !$quiz_passed ? 'disabled' : '';
                            ?>
                            <button class="skillsprint-button skillsprint-complete-day-button" data-day="<?php echo esc_attr($day_number); ?>" data-blueprint="<?php echo esc_attr($post->ID); ?>" <?php echo $disabled; ?>>
                                <?php esc_html_e('Complete This Day', 'skillsprint'); ?>
                            </button>
                            <?php if ($quiz_required && !$quiz_passed) : ?>
                                <p class="skillsprint-quiz-required-message">
                                    <?php esc_html_e('You need to pass the quiz to complete this day.', 'skillsprint'); ?>
                                </p>
                            <?php endif; ?>
                            <?php
                        } else {
                            ?>
                            <button class="skillsprint-button skillsprint-login-button">
                                <?php esc_html_e('Log In to Track Progress', 'skillsprint'); ?>
                            </button>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>

<?php if (!is_user_logged_in()) : ?>
    <?php include_once SKILLSPRINT_PLUGIN_DIR . 'public/partials/login-modal.php'; ?>
    <?php include_once SKILLSPRINT_PLUGIN_DIR . 'public/partials/register-modal.php'; ?>
<?php endif; ?>