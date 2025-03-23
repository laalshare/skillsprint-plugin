<?php
/**
 * Quiz metabox template.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/admin/partials
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get quiz data for this day if it exists
$quiz_id = isset($day_data['quiz_id']) ? $day_data['quiz_id'] : 'quiz_day_' . $day_number;
$quiz_data = get_post_meta($post->ID, '_skillsprint_quiz_' . $quiz_id, true);

// Set default values if quiz doesn't exist yet
$quiz_title = isset($quiz_data['title']) ? $quiz_data['title'] : sprintf(__('Day %d Quiz', 'skillsprint'), $day_number);
$quiz_description = isset($quiz_data['description']) ? $quiz_data['description'] : '';
$passing_score = isset($quiz_data['passing_score']) ? intval($quiz_data['passing_score']) : 70;
$max_attempts = isset($quiz_data['max_attempts']) ? intval($quiz_data['max_attempts']) : 3;
$questions = isset($quiz_data['questions']) ? $quiz_data['questions'] : array();

// Get settings for defaults
$settings = get_option('skillsprint_settings');
$default_passing_score = isset($settings['default_quiz_pass_score']) ? intval($settings['default_quiz_pass_score']) : 70;
$default_max_attempts = isset($settings['max_quiz_attempts']) ? intval($settings['max_quiz_attempts']) : 3;
?>

<div class="skillsprint-quiz-metabox" data-day="<?php echo esc_attr($day_number); ?>">
    <input type="hidden" name="skillsprint_days_data[<?php echo esc_attr($day_number - 1); ?>][quiz_id]" value="<?php echo esc_attr($quiz_id); ?>" class="skillsprint-quiz-id">
    
    <div class="skillsprint-quiz-header">
        <div class="skillsprint-form-group">
            <label for="skillsprint_quiz_<?php echo esc_attr($day_number); ?>_title" class="skillsprint-form-label"><?php esc_html_e('Quiz Title', 'skillsprint'); ?></label>
            <input type="text" id="skillsprint_quiz_<?php echo esc_attr($day_number); ?>_title" class="skillsprint-quiz-title regular-text" value="<?php echo esc_attr($quiz_title); ?>">
        </div>
        
        <div class="skillsprint-form-group">
            <label for="skillsprint_quiz_<?php echo esc_attr($day_number); ?>_description" class="skillsprint-form-label"><?php esc_html_e('Quiz Description', 'skillsprint'); ?></label>
            <textarea id="skillsprint_quiz_<?php echo esc_attr($day_number); ?>_description" class="skillsprint-quiz-description large-text" rows="2"><?php echo esc_textarea($quiz_description); ?></textarea>
        </div>
        
        <div class="skillsprint-quiz-settings">
            <div class="skillsprint-form-group">
                <label for="skillsprint_quiz_<?php echo esc_attr($day_number); ?>_passing_score" class="skillsprint-form-label"><?php esc_html_e('Passing Score (%)', 'skillsprint'); ?></label>
                <input type="number" id="skillsprint_quiz_<?php echo esc_attr($day_number); ?>_passing_score" class="skillsprint-quiz-passing-score small-text" value="<?php echo esc_attr($passing_score ?: $default_passing_score); ?>" min="0" max="100">
            </div>
            
            <div class="skillsprint-form-group">
                <label for="skillsprint_quiz_<?php echo esc_attr($day_number); ?>_max_attempts" class="skillsprint-form-label"><?php esc_html_e('Max Attempts', 'skillsprint'); ?></label>
                <input type="number" id="skillsprint_quiz_<?php echo esc_attr($day_number); ?>_max_attempts" class="skillsprint-quiz-max-attempts small-text" value="<?php echo esc_attr($max_attempts ?: $default_max_attempts); ?>" min="1">
                <p class="description"><?php esc_html_e('Set to 0 for unlimited attempts', 'skillsprint'); ?></p>
            </div>
        </div>
    </div>
    
    <div class="skillsprint-quiz-questions">
        <h3><?php esc_html_e('Questions', 'skillsprint'); ?></h3>
        
        <div class="skillsprint-quiz-questions-container">
            <?php if (!empty($questions)) : ?>
                <?php foreach ($questions as $question_key => $question) : ?>
                    <div class="skillsprint-quiz-question" data-question-type="<?php echo esc_attr($question['type']); ?>">
                        <div class="skillsprint-quiz-question-header">
                            <h4 class="skillsprint-quiz-question-title"><?php echo esc_html($question['text']); ?></h4>
                            <div class="skillsprint-quiz-question-actions">
                                <button type="button" class="button button-secondary skillsprint-edit-question"><?php esc_html_e('Edit', 'skillsprint'); ?></button>
                                <button type="button" class="button button-secondary skillsprint-remove-question"><?php esc_html_e('Remove', 'skillsprint'); ?></button>
                            </div>
                        </div>
                        
                        <div class="skillsprint-quiz-question-preview">
                            <div class="skillsprint-quiz-question-type">
                                <?php 
                                $type_labels = array(
                                    'multiple_choice' => __('Multiple Choice', 'skillsprint'),
                                    'true_false' => __('True/False', 'skillsprint'),
                                    'multiple_answer' => __('Multiple Answer', 'skillsprint'),
                                    'matching' => __('Matching', 'skillsprint'),
                                    'short_answer' => __('Short Answer', 'skillsprint'),
                                );
                                echo isset($type_labels[$question['type']]) ? esc_html($type_labels[$question['type']]) : esc_html($question['type']); 
                                ?>
                            </div>
                            
                            <?php if ($question['type'] === 'multiple_choice' && !empty($question['options'])) : ?>
                                <ul class="skillsprint-quiz-question-options-preview">
                                    <?php foreach ($question['options'] as $option_key => $option) : ?>
                                        <li class="<?php echo $option_key === $question['correct_answer'] ? 'correct' : ''; ?>">
                                            <?php echo esc_html($option); ?>
                                            <?php if ($option_key === $question['correct_answer']) : ?>
                                                <span class="dashicons dashicons-yes"></span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php elseif ($question['type'] === 'true_false') : ?>
                                <div class="skillsprint-quiz-question-options-preview">
                                    <div class="<?php echo $question['correct_answer'] === 'true' ? 'correct' : ''; ?>">
                                        <?php esc_html_e('True', 'skillsprint'); ?>
                                        <?php if ($question['correct_answer'] === 'true') : ?>
                                            <span class="dashicons dashicons-yes"></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="<?php echo $question['correct_answer'] === 'false' ? 'correct' : ''; ?>">
                                        <?php esc_html_e('False', 'skillsprint'); ?>
                                        <?php if ($question['correct_answer'] === 'false') : ?>
                                            <span class="dashicons dashicons-yes"></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php elseif ($question['type'] === 'multiple_answer' && !empty($question['options'])) : ?>
                                <ul class="skillsprint-quiz-question-options-preview">
                                    <?php foreach ($question['options'] as $option_key => $option) : ?>
                                        <li class="<?php echo in_array($option_key, $question['correct_answer']) ? 'correct' : ''; ?>">
                                            <?php echo esc_html($option); ?>
                                            <?php if (in_array($option_key, $question['correct_answer'])) : ?>
                                                <span class="dashicons dashicons-yes"></span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php elseif ($question['type'] === 'matching' && !empty($question['options'])) : ?>
                                <table class="skillsprint-quiz-question-matching-preview">
                                    <tr>
                                        <th><?php esc_html_e('Left', 'skillsprint'); ?></th>
                                        <th><?php esc_html_e('Right', 'skillsprint'); ?></th>
                                    </tr>
                                    <?php foreach ($question['options'] as $option) : ?>
                                        <tr>
                                            <td><?php echo esc_html($option['left']); ?></td>
                                            <td><?php echo esc_html($question['correct_answer'][$option['left']]); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            <?php elseif ($question['type'] === 'short_answer') : ?>
                                <div class="skillsprint-quiz-question-short-answer-preview">
                                    <?php esc_html_e('Acceptable answers:', 'skillsprint'); ?>
                                    <span class="correct">
                                        <?php 
                                        if (is_array($question['correct_answer'])) {
                                            echo esc_html(implode(', ', $question['correct_answer']));
                                        } else {
                                            echo esc_html($question['correct_answer']);
                                        }
                                        ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($question['explanation'])) : ?>
                                <div class="skillsprint-quiz-question-explanation-preview">
                                    <strong><?php esc_html_e('Explanation:', 'skillsprint'); ?></strong>
                                    <?php echo wp_kses_post($question['explanation']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($question['points']) && $question['points'] > 1) : ?>
                                <div class="skillsprint-quiz-question-points-preview">
                                    <?php printf(esc_html__('Points: %d', 'skillsprint'), $question['points']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="skillsprint-quiz-question-form" style="display: none;">
                            <input type="hidden" class="skillsprint-quiz-question-id" value="<?php echo esc_attr($question['id']); ?>">
                            
                            <div class="skillsprint-quiz-field">
                                <label><?php esc_html_e('Question Text', 'skillsprint'); ?></label>
                                <textarea class="skillsprint-quiz-question-text large-text" rows="2"><?php echo esc_textarea($question['text']); ?></textarea>
                            </div>
                            
                            <div class="skillsprint-quiz-field">
                                <label><?php esc_html_e('Question Type', 'skillsprint'); ?></label>
                                <select class="skillsprint-quiz-question-type">
                                    <option value="multiple_choice" <?php selected($question['type'], 'multiple_choice'); ?>><?php esc_html_e('Multiple Choice', 'skillsprint'); ?></option>
                                    <option value="true_false" <?php selected($question['type'], 'true_false'); ?>><?php esc_html_e('True/False', 'skillsprint'); ?></option>
                                    <option value="multiple_answer" <?php selected($question['type'], 'multiple_answer'); ?>><?php esc_html_e('Multiple Answer', 'skillsprint'); ?></option>
                                    <option value="matching" <?php selected($question['type'], 'matching'); ?>><?php esc_html_e('Matching', 'skillsprint'); ?></option>
                                    <option value="short_answer" <?php selected($question['type'], 'short_answer'); ?>><?php esc_html_e('Short Answer', 'skillsprint'); ?></option>
                                </select>
                            </div>
                            
                            <!-- Multiple Choice Options -->
                            <div class="skillsprint-quiz-field skillsprint-quiz-options-field" <?php echo $question['type'] !== 'multiple_choice' ? 'style="display: none;"' : ''; ?>>
                                <label><?php esc_html_e('Answer Options', 'skillsprint'); ?></label>
                                <div class="skillsprint-quiz-options-container">
                                    <?php if ($question['type'] === 'multiple_choice' && !empty($question['options'])) : ?>
                                        <?php foreach ($question['options'] as $option_key => $option) : ?>
                                            <div class="skillsprint-quiz-option">
                                                <input type="text" class="skillsprint-quiz-option-text regular-text" value="<?php echo esc_attr($option); ?>">
                                                <label>
                                                    <input type="radio" class="skillsprint-quiz-option-correct" <?php checked($option_key, $question['correct_answer']); ?>>
                                                    <?php esc_html_e('Correct', 'skillsprint'); ?>
                                                </label>
                                                <button type="button" class="button button-secondary skillsprint-remove-option"><?php esc_html_e('Remove', 'skillsprint'); ?></button>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="button button-secondary skillsprint-add-option"><?php esc_html_e('Add Option', 'skillsprint'); ?></button>
                            </div>
                            
                            <!-- Multiple Answer Options -->
                            <div class="skillsprint-quiz-field skillsprint-quiz-multiple-answer-field" <?php echo $question['type'] !== 'multiple_answer' ? 'style="display: none;"' : ''; ?>>
                                <label><?php esc_html_e('Answer Options', 'skillsprint'); ?></label>
                                <div class="skillsprint-quiz-options-container">
                                    <?php if ($question['type'] === 'multiple_answer' && !empty($question['options'])) : ?>
                                        <?php foreach ($question['options'] as $option_key => $option) : ?>
                                            <div class="skillsprint-quiz-option">
                                                <input type="text" class="skillsprint-quiz-option-text regular-text" value="<?php echo esc_attr($option); ?>">
                                                <label>
                                                    <input type="checkbox" class="skillsprint-quiz-option-correct" <?php checked(in_array($option_key, $question['correct_answer'])); ?>>
                                                    <?php esc_html_e('Correct', 'skillsprint'); ?>
                                                </label>
                                                <button type="button" class="button button-secondary skillsprint-remove-option"><?php esc_html_e('Remove', 'skillsprint'); ?></button>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="button button-secondary skillsprint-add-option"><?php esc_html_e('Add Option', 'skillsprint'); ?></button>
                            </div>
                            
                            <!-- True/False -->
                            <div class="skillsprint-quiz-field skillsprint-quiz-true-false-field" <?php echo $question['type'] !== 'true_false' ? 'style="display: none;"' : ''; ?>>
                                <label><?php esc_html_e('Correct Answer', 'skillsprint'); ?></label>
                                <div class="skillsprint-quiz-true-false-container">
                                    <label>
                                        <input type="radio" class="skillsprint-quiz-true-false-correct" name="skillsprint-quiz-true-false-<?php echo esc_attr($question_key); ?>" value="true" <?php checked($question['correct_answer'], 'true'); ?>>
                                        <?php esc_html_e('True', 'skillsprint'); ?>
                                    </label>
                                    <label>
                                        <input type="radio" class="skillsprint-quiz-true-false-correct" name="skillsprint-quiz-true-false-<?php echo esc_attr($question_key); ?>" value="false" <?php checked($question['correct_answer'], 'false'); ?>>
                                        <?php esc_html_e('False', 'skillsprint'); ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Matching -->
                            <div class="skillsprint-quiz-field skillsprint-quiz-matching-field" <?php echo $question['type'] !== 'matching' ? 'style="display: none;"' : ''; ?>>
                                <label><?php esc_html_e('Matching Pairs', 'skillsprint'); ?></label>
                                <div class="skillsprint-quiz-matching-container">
                                    <?php if ($question['type'] === 'matching' && !empty($question['options'])) : ?>
                                        <?php foreach ($question['options'] as $option) : ?>
                                            <div class="skillsprint-quiz-matching-pair">
                                                <input type="text" class="skillsprint-quiz-matching-left regular-text" value="<?php echo esc_attr($option['left']); ?>" placeholder="<?php esc_attr_e('Left', 'skillsprint'); ?>">
                                                <span class="dashicons dashicons-arrow-right-alt"></span>
                                                <input type="text" class="skillsprint-quiz-matching-right regular-text" value="<?php echo esc_attr($option['right']); ?>" placeholder="<?php esc_attr_e('Right', 'skillsprint'); ?>">
                                                <button type="button" class="button button-secondary skillsprint-remove-matching-pair"><?php esc_html_e('Remove', 'skillsprint'); ?></button>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="button button-secondary skillsprint-add-matching-pair"><?php esc_html_e('Add Pair', 'skillsprint'); ?></button>
                            </div>
                            
                            <!-- Short Answer -->
                            <div class="skillsprint-quiz-field skillsprint-quiz-short-answer-field" <?php echo $question['type'] !== 'short_answer' ? 'style="display: none;"' : ''; ?>>
                                <label><?php esc_html_e('Acceptable Answers', 'skillsprint'); ?></label>
                                <div class="skillsprint-quiz-short-answer-container">
                                    <?php 
                                    $short_answers = array();
                                    if ($question['type'] === 'short_answer') {
                                        if (is_array($question['correct_answer'])) {
                                            $short_answers = $question['correct_answer'];
                                        } else {
                                            $short_answers = array($question['correct_answer']);
                                        }
                                    }
                                    
                                    if (!empty($short_answers)) : 
                                        foreach ($short_answers as $answer) : 
                                    ?>
                                        <div class="skillsprint-quiz-short-answer">
                                            <input type="text" class="skillsprint-quiz-short-answer-text regular-text" value="<?php echo esc_attr($answer); ?>">
                                            <button type="button" class="button button-secondary skillsprint-remove-short-answer"><?php esc_html_e('Remove', 'skillsprint'); ?></button>
                                        </div>
                                    <?php 
                                        endforeach;
                                    endif; 
                                    ?>
                                </div>
                                <button type="button" class="button button-secondary skillsprint-add-short-answer"><?php esc_html_e('Add Acceptable Answer', 'skillsprint'); ?></button>
                            </div>
                            
                            <div class="skillsprint-quiz-field">
                                <label><?php esc_html_e('Explanation', 'skillsprint'); ?></label>
                                <textarea class="skillsprint-quiz-question-explanation large-text" rows="2"><?php echo esc_textarea($question['explanation']); ?></textarea>
                            </div>
                            
                            <div class="skillsprint-quiz-field">
                                <label><?php esc_html_e('Points', 'skillsprint'); ?></label>
                                <input type="number" class="skillsprint-quiz-question-points small-text" value="<?php echo esc_attr(isset($question['points']) ? $question['points'] : 1); ?>" min="1">
                            </div>
                            
                            <div class="skillsprint-quiz-question-actions">
                                <button type="button" class="button button-primary skillsprint-save-question"><?php esc_html_e('Save Question', 'skillsprint'); ?></button>
                                <button type="button" class="button button-secondary skillsprint-cancel-question"><?php esc_html_e('Cancel', 'skillsprint'); ?></button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <button type="button" class="button button-secondary skillsprint-add-question"><?php esc_html_e('Add Question', 'skillsprint'); ?></button>
    </div>
    
    <div class="skillsprint-quiz-save">
        <button type="button" class="button button-primary skillsprint-save-quiz" data-day="<?php echo esc_attr($day_number); ?>" data-quiz-id="<?php echo esc_attr($quiz_id); ?>" data-blueprint-id="<?php echo esc_attr($post->ID); ?>"><?php esc_html_e('Save Quiz', 'skillsprint'); ?></button>
        <span class="skillsprint-quiz-save-status"></span>
    </div>
</div>
$settings = get_option('skillsprint