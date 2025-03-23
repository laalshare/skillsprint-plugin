<?php
/**
 * Quiz display template
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/public/partials
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get required variables
$blueprint_id = isset($blueprint_id) ? $blueprint_id : get_the_ID();
$day_number = isset($day_number) ? $day_number : (isset($_GET['day']) ? intval($_GET['day']) : 1);
$quiz_id = isset($quiz_id) ? $quiz_id : "quiz_day_{$day_number}";
$user_id = get_current_user_id();

// Get quiz data
$quiz_data = get_post_meta($blueprint_id, '_skillsprint_quiz_' . $quiz_id, true);

if (empty($quiz_data) || empty($quiz_data['questions'])) {
    return;
}

// Get user progress for this quiz
$user_progress = array();
if (is_user_logged_in()) {
    $blueprint_progress = SkillSprint_DB::get_user_blueprint_progress($user_id, $blueprint_id);
    $user_progress = isset($blueprint_progress[$day_number]) ? $blueprint_progress[$day_number] : array();
}

// Determine quiz state
$quiz_title = isset($quiz_data['title']) ? $quiz_data['title'] : sprintf(__('Day %d Quiz', 'skillsprint'), $day_number);
$quiz_description = isset($quiz_data['description']) ? $quiz_data['description'] : '';
$passing_score = isset($quiz_data['passing_score']) ? intval($quiz_data['passing_score']) : 70;
$max_attempts = isset($quiz_data['max_attempts']) ? intval($quiz_data['max_attempts']) : 3;
$questions = isset($quiz_data['questions']) ? $quiz_data['questions'] : array();

// Determine quiz status
$quiz_status = isset($user_progress['quiz_status']) ? $user_progress['quiz_status'] : 'not_started';
$quiz_score = isset($user_progress['quiz_score']) ? intval($user_progress['quiz_score']) : 0;
$quiz_attempts = isset($user_progress['quiz_attempts']) ? intval($user_progress['quiz_attempts']) : 0;
$quiz_passed = $quiz_status === 'passed';

// Check if user can take the quiz
$can_take_quiz = is_user_logged_in();
$attempts_remaining = $max_attempts <= 0 ? -1 : ($max_attempts - $quiz_attempts);
$attempts_exhausted = $max_attempts > 0 && $quiz_attempts >= $max_attempts;

if ($attempts_exhausted && !$quiz_passed) {
    $can_take_quiz = false;
}

// Get settings
$settings = get_option('skillsprint_settings');
$show_correct_answers = isset($settings['show_quiz_answers']) ? (bool) $settings['show_quiz_answers'] : true;
$show_explanations = isset($settings['show_quiz_explanations']) ? (bool) $settings['show_quiz_explanations'] : true;
?>

<div class="skillsprint-quiz-container" data-quiz-id="<?php echo esc_attr($quiz_id); ?>" data-blueprint-id="<?php echo esc_attr($blueprint_id); ?>" data-day="<?php echo esc_attr($day_number); ?>">
    <div class="skillsprint-quiz-header">
        <h3 class="skillsprint-quiz-title"><?php echo esc_html($quiz_title); ?></h3>
        
        <?php if (!empty($quiz_description)) : ?>
            <div class="skillsprint-quiz-description"><?php echo wp_kses_post($quiz_description); ?></div>
        <?php endif; ?>
        
        <div class="skillsprint-quiz-meta">
            <div class="skillsprint-quiz-meta-item">
                <span class="skillsprint-quiz-meta-label"><?php esc_html_e('Questions:', 'skillsprint'); ?></span>
                <span class="skillsprint-quiz-meta-value"><?php echo count($questions); ?></span>
            </div>
            
            <div class="skillsprint-quiz-meta-item">
                <span class="skillsprint-quiz-meta-label"><?php esc_html_e('Passing Score:', 'skillsprint'); ?></span>
                <span class="skillsprint-quiz-meta-value"><?php echo esc_html($passing_score); ?>%</span>
            </div>
            
            <?php if ($max_attempts > 0) : ?>
                <div class="skillsprint-quiz-meta-item">
                    <span class="skillsprint-quiz-meta-label"><?php esc_html_e('Max Attempts:', 'skillsprint'); ?></span>
                    <span class="skillsprint-quiz-meta-value"><?php echo esc_html($max_attempts); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (is_user_logged_in() && $quiz_attempts > 0) : ?>
                <div class="skillsprint-quiz-meta-item">
                    <span class="skillsprint-quiz-meta-label"><?php esc_html_e('Your Attempts:', 'skillsprint'); ?></span>
                    <span class="skillsprint-quiz-meta-value">
                        <?php 
                        if ($max_attempts > 0) {
                            printf(
                                esc_html__('%1$d of %2$d', 'skillsprint'),
                                $quiz_attempts,
                                $max_attempts
                            );
                        } else {
                            echo esc_html($quiz_attempts);
                        }
                        ?>
                    </span>
                </div>
                
                <?php if ($quiz_status !== 'not_started') : ?>
                    <div class="skillsprint-quiz-meta-item">
                        <span class="skillsprint-quiz-meta-label"><?php esc_html_e('Last Score:', 'skillsprint'); ?></span>
                        <span class="skillsprint-quiz-meta-value">
                            <?php echo esc_html($quiz_score); ?>%
                            <?php if ($quiz_passed) : ?>
                                <span class="skillsprint-quiz-passed">(<?php esc_html_e('Passed', 'skillsprint'); ?>)</span>
                            <?php elseif ($quiz_status === 'failed') : ?>
                                <span class="skillsprint-quiz-failed">(<?php esc_html_e('Failed', 'skillsprint'); ?>)</span>
                            <?php endif; ?>
                        </span>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <?php if ($quiz_passed) : ?>
            <div class="skillsprint-quiz-success-message">
                <span class="dashicons dashicons-yes-alt"></span>
                <p><?php esc_html_e('Congratulations! You have passed this quiz.', 'skillsprint'); ?></p>
            </div>
        <?php elseif ($attempts_exhausted) : ?>
            <div class="skillsprint-quiz-error-message">
                <span class="dashicons dashicons-warning"></span>
                <p><?php esc_html_e('You have used all your attempts for this quiz. Please contact your administrator if you need to retake it.', 'skillsprint'); ?></p>
            </div>
        <?php elseif (!is_user_logged_in()) : ?>
            <div class="skillsprint-quiz-login-required">
                <div class="skillsprint-alert warning">
                    <span class="dashicons dashicons-lock"></span>
                    <p><?php esc_html_e('Please log in to take this quiz and track your progress.', 'skillsprint'); ?></p>
                    <div class="skillsprint-quiz-login-buttons">
                        <button type="button" class="skillsprint-button skillsprint-login-button"><?php esc_html_e('Log In', 'skillsprint'); ?></button>
                        <button type="button" class="skillsprint-button secondary skillsprint-register-button"><?php esc_html_e('Create Account', 'skillsprint'); ?></button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ($can_take_quiz && !$quiz_passed) : ?>
        <form class="skillsprint-quiz-form">
            <input type="hidden" name="quiz_id" value="<?php echo esc_attr($quiz_id); ?>">
            <input type="hidden" name="blueprint_id" value="<?php echo esc_attr($blueprint_id); ?>">
            <input type="hidden" name="day_number" value="<?php echo esc_attr($day_number); ?>">
            
            <?php foreach ($questions as $question_index => $question) : 
                $question_id = isset($question['id']) ? $question['id'] : 'q_' . $question_index;
                $question_type = isset($question['type']) ? $question['type'] : 'multiple_choice';
                $question_text = isset($question['text']) ? $question['text'] : '';
                $question_options = isset($question['options']) ? $question['options'] : array();
                $question_points = isset($question['points']) ? intval($question['points']) : 1;
            ?>
                <div class="skillsprint-quiz-question" data-question-id="<?php echo esc_attr($question_id); ?>" data-question-type="<?php echo esc_attr($question_type); ?>">
                    <div class="skillsprint-quiz-question-header">
                        <h4 class="skillsprint-quiz-question-text"><?php echo wp_kses_post($question_text); ?></h4>
                        <?php if ($question_points > 1) : ?>
                            <div class="skillsprint-quiz-question-points">
                                <?php printf(esc_html__('Points: %d', 'skillsprint'), $question_points); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="skillsprint-quiz-question-options">
                        <?php if ($question_type === 'multiple_choice') : ?>
                            <?php foreach ($question_options as $option_index => $option_text) : ?>
                                <div class="skillsprint-quiz-option">
                                    <label>
                                        <input type="radio" name="question[<?php echo esc_attr($question_id); ?>]" value="<?php echo esc_attr($option_index); ?>">
                                        <span class="skillsprint-quiz-option-text"><?php echo wp_kses_post($option_text); ?></span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php elseif ($question_type === 'multiple_answer') : ?>
                            <?php foreach ($question_options as $option_index => $option_text) : ?>
                                <div class="skillsprint-quiz-option">
                                    <label>
                                        <input type="checkbox" name="question[<?php echo esc_attr($question_id); ?>][]" value="<?php echo esc_attr($option_index); ?>">
                                        <span class="skillsprint-quiz-option-text"><?php echo wp_kses_post($option_text); ?></span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php elseif ($question_type === 'true_false') : ?>
                            <div class="skillsprint-quiz-option">
                                <label>
                                    <input type="radio" name="question[<?php echo esc_attr($question_id); ?>]" value="true">
                                    <span class="skillsprint-quiz-option-text"><?php esc_html_e('True', 'skillsprint'); ?></span>
                                </label>
                            </div>
                            <div class="skillsprint-quiz-option">
                                <label>
                                    <input type="radio" name="question[<?php echo esc_attr($question_id); ?>]" value="false">
                                    <span class="skillsprint-quiz-option-text"><?php esc_html_e('False', 'skillsprint'); ?></span>
                                </label>
                            </div>
                        <?php elseif ($question_type === 'matching') : ?>
                            <div class="skillsprint-quiz-matching">
                                <?php
                                // Randomize right side options
                                $right_options = array();
                                foreach ($question_options as $option) {
                                    $right_options[] = $option['right'];
                                }
                                shuffle($right_options);
                                ?>
                                
                                <div class="skillsprint-quiz-matching-pairs">
                                    <?php foreach ($question_options as $option_index => $option) : ?>
                                        <div class="skillsprint-quiz-matching-pair">
                                            <div class="skillsprint-quiz-matching-left">
                                                <?php echo wp_kses_post($option['left']); ?>
                                            </div>
                                            <div class="skillsprint-quiz-matching-right">
                                                <select name="question[<?php echo esc_attr($question_id); ?>][<?php echo esc_attr($option['left']); ?>]">
                                                    <option value=""><?php esc_html_e('Select match...', 'skillsprint'); ?></option>
                                                    <?php foreach ($right_options as $right_option) : ?>
                                                        <option value="<?php echo esc_attr($right_option); ?>"><?php echo wp_kses_post($right_option); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php elseif ($question_type === 'short_answer') : ?>
                            <div class="skillsprint-quiz-short-answer">
                                <input type="text" name="question[<?php echo esc_attr($question_id); ?>]" class="skillsprint-quiz-short-answer-input" placeholder="<?php esc_attr_e('Your answer...', 'skillsprint'); ?>">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="skillsprint-quiz-submit">
                <button type="submit" class="skillsprint-button skillsprint-submit-quiz">
                    <?php esc_html_e('Submit Quiz', 'skillsprint'); ?>
                </button>
                
                <div class="skillsprint-quiz-submit-note">
                    <?php if ($max_attempts > 0 && $quiz_attempts > 0) : ?>
                        <p>
                            <?php 
                            printf(
                                esc_html__('You have %d attempts remaining for this quiz.', 'skillsprint'),
                                $attempts_remaining
                            ); 
                            ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    <?php elseif ($quiz_passed || $attempts_exhausted) : ?>
        <?php if ($quiz_passed && $show_correct_answers) : ?>
            <div class="skillsprint-quiz-review">
                <h4 class="skillsprint-quiz-review-title"><?php esc_html_e('Quiz Review', 'skillsprint'); ?></h4>
                
                <?php foreach ($questions as $question_index => $question) : 
                    $question_id = isset($question['id']) ? $question['id'] : 'q_' . $question_index;
                    $question_type = isset($question['type']) ? $question['type'] : 'multiple_choice';
                    $question_text = isset($question['text']) ? $question['text'] : '';
                    $question_options = isset($question['options']) ? $question['options'] : array();
                    $question_correct = isset($question['correct_answer']) ? $question['correct_answer'] : '';
                    $question_explanation = isset($question['explanation']) ? $question['explanation'] : '';
                ?>
                    <div class="skillsprint-quiz-review-question">
                        <div class="skillsprint-quiz-review-question-text">
                            <h5><?php echo wp_kses_post($question_text); ?></h5>
                        </div>
                        
                        <div class="skillsprint-quiz-review-question-answer">
                            <div class="skillsprint-quiz-review-answer-label"><?php esc_html_e('Correct Answer:', 'skillsprint'); ?></div>
                            
                            <?php if ($question_type === 'multiple_choice') : ?>
                                <div class="skillsprint-quiz-review-answer-text">
                                    <?php echo wp_kses_post($question_options[$question_correct]); ?>
                                </div>
                            <?php elseif ($question_type === 'multiple_answer') : ?>
                                <div class="skillsprint-quiz-review-answer-text">
                                    <?php 
                                    $correct_texts = array();
                                    foreach ($question_correct as $correct_index) {
                                        $correct_texts[] = $question_options[$correct_index];
                                    }
                                    echo wp_kses_post(implode(', ', $correct_texts));
                                    ?>
                                </div>
                            <?php elseif ($question_type === 'true_false') : ?>
                                <div class="skillsprint-quiz-review-answer-text">
                                    <?php echo $question_correct === 'true' ? esc_html__('True', 'skillsprint') : esc_html__('False', 'skillsprint'); ?>
                                </div>
                            <?php elseif ($question_type === 'matching') : ?>
                                <div class="skillsprint-quiz-review-answer-text">
                                    <ul class="skillsprint-quiz-review-matching">
                                        <?php foreach ($question_correct as $left => $right) : ?>
                                            <li>
                                                <strong><?php echo wp_kses_post($left); ?></strong> ➔ <?php echo wp_kses_post($right); ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php elseif ($question_type === 'short_answer') : ?>
                                <div class="skillsprint-quiz-review-answer-text">
                                    <?php 
                                    if (is_array($question_correct)) {
                                        echo wp_kses_post(implode(', ', $question_correct));
                                    } else {
                                        echo wp_kses_post($question_correct);
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($show_explanations && !empty($question_explanation)) : ?>
                                <div class="skillsprint-quiz-review-explanation">
                                    <div class="skillsprint-quiz-review-explanation-label"><?php esc_html_e('Explanation:', 'skillsprint'); ?></div>
                                    <div class="skillsprint-quiz-review-explanation-text"><?php echo wp_kses_post($question_explanation); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="skillsprint-quiz-completed-actions">
            <?php if ($quiz_passed) : ?>
                <?php if ($day_number < 7) : ?>
                    <a href="<?php echo esc_url(add_query_arg('day', $day_number + 1, get_permalink($blueprint_id))); ?>" class="skillsprint-button">
                        <?php printf(esc_html__('Continue to Day %d', 'skillsprint'), $day_number + 1); ?>
                    </a>
                <?php else : ?>
                    <a href="<?php echo esc_url(get_post_type_archive_link('blueprint')); ?>" class="skillsprint-button">
                        <?php esc_html_e('Explore More Blueprints', 'skillsprint'); ?>
                    </a>
                <?php endif; ?>
            <?php elseif ($attempts_exhausted) : ?>
                <a href="<?php echo esc_url(get_permalink($blueprint_id)); ?>" class="skillsprint-button secondary">
                    <?php esc_html_e('Back to Blueprint', 'skillsprint'); ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="skillsprint-quiz-result" style="display: none;">
        <div class="skillsprint-quiz-result-inner">
            <div class="skillsprint-quiz-result-header">
                <h4 class="skillsprint-quiz-result-title"></h4>
                <div class="skillsprint-quiz-result-score">
                    <div class="skillsprint-quiz-result-percentage"></div>
                    <div class="skillsprint-quiz-result-correct"></div>
                </div>
            </div>
            
            <div class="skillsprint-quiz-result-feedback"></div>
            
            <div class="skillsprint-quiz-result-actions">
                <button type="button" class="skillsprint-button skillsprint-continue-button"><?php esc_html_e('Continue', 'skillsprint'); ?></button>
                <button type="button" class="skillsprint-button secondary skillsprint-retry-button"><?php esc_html_e('Retry Quiz', 'skillsprint'); ?></button>
            </div>
        </div>
    </div>
</div>

<?php if (!is_user_logged_in()) : ?>
    <?php include SKILLSPRINT_PLUGIN_DIR . 'public/partials/login-modal.php'; ?>
    <?php include SKILLSPRINT_PLUGIN_DIR . 'public/partials/register-modal.php'; ?>
<?php endif; ?>