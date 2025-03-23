<?php
/**
 * Blueprint days metabox template.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/admin/partials
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="skillsprint-blueprint-builder">
    <div class="skillsprint-blueprint-days-tabs">
        <?php foreach ($days_data as $day) : 
            $day_number = isset($day['day_number']) ? intval($day['day_number']) : 0;
            $day_title = isset($day['title']) ? $day['title'] : sprintf(__('Day %d', 'skillsprint'), $day_number);
        ?>
            <div class="skillsprint-blueprint-day-tab" data-day="<?php echo esc_attr($day_number); ?>">
                <?php echo esc_html($day_title); ?>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="skillsprint-blueprint-days-content">
        <?php foreach ($days_data as $day) : 
            $day_number = isset($day['day_number']) ? intval($day['day_number']) : 0;
            $day_title = isset($day['title']) ? $day['title'] : sprintf(__('Day %d', 'skillsprint'), $day_number);
            $learning_objectives = isset($day['learning_objectives']) ? $day['learning_objectives'] : '';
            $content = isset($day['content']) ? $day['content'] : '';
            $resources = isset($day['resources']) ? $day['resources'] : array();
            $quiz_id = isset($day['quiz_id']) ? $day['quiz_id'] : sprintf('quiz_day_%d', $day_number);
        ?>
            <div class="skillsprint-blueprint-day-content" data-day="<?php echo esc_attr($day_number); ?>">
                <input type="hidden" name="skillsprint_days_data[<?php echo esc_attr($day_number - 1); ?>][day_number]" value="<?php echo esc_attr($day_number); ?>">
                
                <div class="skillsprint-form-group">
                    <label for="skillsprint_day_<?php echo esc_attr($day_number); ?>_title" class="skillsprint-form-label"><?php esc_html_e('Day Title', 'skillsprint'); ?></label>
                    <input type="text" id="skillsprint_day_<?php echo esc_attr($day_number); ?>_title" name="skillsprint_days_data[<?php echo esc_attr($day_number - 1); ?>][title]" value="<?php echo esc_attr($day_title); ?>" class="regular-text">
                </div>
                
                <div class="skillsprint-form-group">
                    <label for="skillsprint_day_<?php echo esc_attr($day_number); ?>_learning_objectives" class="skillsprint-form-label"><?php esc_html_e('Learning Objectives', 'skillsprint'); ?></label>
                    <textarea id="skillsprint_day_<?php echo esc_attr($day_number); ?>_learning_objectives" name="skillsprint_days_data[<?php echo esc_attr($day_number - 1); ?>][learning_objectives]" rows="4" class="large-text"><?php echo esc_textarea($learning_objectives); ?></textarea>
                    <p class="description"><?php esc_html_e('Add learning objectives for this day. Use bullet points or numbered lists.', 'skillsprint'); ?></p>
                </div>
                
                <div class="skillsprint-form-group">
                    <label for="skillsprint_day_<?php echo esc_attr($day_number); ?>_content" class="skillsprint-form-label"><?php esc_html_e('Day Content', 'skillsprint'); ?></label>
                    <?php
                    wp_editor(
                        $content,
                        'skillsprint_day_' . esc_attr($day_number) . '_content',
                        array(
                            'textarea_name' => 'skillsprint_days_data[' . esc_attr($day_number - 1) . '][content]',
                            'media_buttons' => true,
                            'textarea_rows' => 10,
                            'editor_height' => 300,
                        )
                    );
                    ?>
                    <p class="description"><?php esc_html_e('Add the main content for this day.', 'skillsprint'); ?></p>
                </div>
                
                <div class="skillsprint-form-group">
                    <label class="skillsprint-form-label"><?php esc_html_e('Day Resources', 'skillsprint'); ?></label>
                    <div class="skillsprint-resources-container">
                        <?php if (!empty($resources)) : ?>
                            <?php foreach ($resources as $key => $resource) : ?>
                                <div class="skillsprint-resource-item">
                                    <div class="skillsprint-resource-fields">
                                        <input type="text" name="skillsprint_days_data[<?php echo esc_attr($day_number - 1); ?>][resources][<?php echo esc_attr($key); ?>][title]" value="<?php echo esc_attr($resource['title']); ?>" placeholder="<?php esc_attr_e('Resource Title', 'skillsprint'); ?>" class="regular-text">
                                        <input type="text" name="skillsprint_days_data[<?php echo esc_attr($day_number - 1); ?>][resources][<?php echo esc_attr($key); ?>][url]" value="<?php echo esc_attr($resource['url']); ?>" placeholder="<?php esc_attr_e('Resource URL', 'skillsprint'); ?>" class="regular-text">
                                        <select name="skillsprint_days_data[<?php echo esc_attr($day_number - 1); ?>][resources][<?php echo esc_attr($key); ?>][type]">
                                            <option value="link" <?php selected($resource['type'], 'link'); ?>><?php esc_html_e('Link', 'skillsprint'); ?></option>
                                            <option value="file" <?php selected($resource['type'], 'file'); ?>><?php esc_html_e('Document', 'skillsprint'); ?></option>
                                            <option value="video" <?php selected($resource['type'], 'video'); ?>><?php esc_html_e('Video', 'skillsprint'); ?></option>
                                        </select>
                                    </div>
                                    <button type="button" class="button button-secondary skillsprint-remove-resource"><?php esc_html_e('Remove', 'skillsprint'); ?></button>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="skillsprint-resource-item">
                                <div class="skillsprint-resource-fields">
                                    <input type="text" name="skillsprint_days_data[<?php echo esc_attr($day_number - 1); ?>][resources][0][title]" placeholder="<?php esc_attr_e('Resource Title', 'skillsprint'); ?>" class="regular-text">
                                    <input type="text" name="skillsprint_days_data[<?php echo esc_attr($day_number - 1); ?>][resources][0][url]" placeholder="<?php esc_attr_e('Resource URL', 'skillsprint'); ?>" class="regular-text">
                                    <select name="skillsprint_days_data[<?php echo esc_attr($day_number - 1); ?>][resources][0][type]">
                                        <option value="link"><?php esc_html_e('Link', 'skillsprint'); ?></option>
                                        <option value="file"><?php esc_html_e('Document', 'skillsprint'); ?></option>
                                        <option value="video"><?php esc_html_e('Video', 'skillsprint'); ?></option>
                                    </select>
                                </div>
                                <button type="button" class="button button-secondary skillsprint-remove-resource"><?php esc_html_e('Remove', 'skillsprint'); ?></button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="button button-secondary skillsprint-add-resource" data-day="<?php echo esc_attr($day_number); ?>"><?php esc_html_e('Add Resource', 'skillsprint'); ?></button>
                </div>
                
                <div class="skillsprint-form-group">
                    <label class="skillsprint-form-label"><?php esc_html_e('Quiz for This Day', 'skillsprint'); ?></label>
                    <div class="skillsprint-quiz-container">
                        <input type="hidden" name="skillsprint_days_data[<?php echo esc_attr($day_number - 1); ?>][quiz_id]" value="<?php echo esc_attr($quiz_id); ?>" class="skillsprint-quiz-id">
                        
                        <?php
                        // Check if quiz data exists
                        $quiz_data = get_post_meta($post->ID, '_skillsprint_quiz_' . $quiz_id, true);
                        $quiz_title = isset($quiz_data['title']) ? $quiz_data['title'] : sprintf(__('Day %d Quiz', 'skillsprint'), $day_number);
                        $quiz_description = isset($quiz_data['description']) ? $quiz_data['description'] : '';
                        $passing_score = isset($quiz_data['passing_score']) ? intval($quiz_data['passing_score']) : 70;
                        $max_attempts = isset($quiz_data['max_attempts']) ? intval($quiz_data['max_attempts']) : 3;
                        $questions = isset($quiz_data['questions']) ? $quiz_data['questions'] : array();
                        ?>
                        
                        <div class="skillsprint-quiz-header">
                            <div class="skillsprint-quiz-field">
                                <label for="skillsprint_quiz_<?php echo esc_attr($day_number); ?>_title"><?php esc_html_e('Quiz Title', 'skillsprint'); ?></label>
                                <input type="text" id="skillsprint_quiz_<?php echo esc_attr($day_number); ?>_title" class="skillsprint-quiz-title regular-text" value="<?php echo esc_attr($quiz_title); ?>">
                            </div>
                            
                            <div class="skillsprint-quiz-field">
                                <label for="skillsprint_quiz_<?php echo esc_attr($day_number); ?>_description"><?php esc_html_e('Quiz Description', 'skillsprint'); ?></label>
                                <textarea id="skillsprint_quiz_<?php echo esc_attr($day_number); ?>_description" class="skillsprint-quiz-description large-text" rows="2"><?php echo esc_textarea($quiz_description); ?></textarea>
                            </div>
                            
                            <div class="skillsprint-quiz-settings">
                                <div class="skillsprint-quiz-field">
                                <label for="skillsprint_quiz_<?php echo esc_attr($day_number); ?>_passing_score"><?php esc_html_e('Passing Score (%)', 'skillsprint'); ?></label>
                                    <input type="number" id="skillsprint_quiz_<?php echo esc_attr($day_number); ?>_passing_score" class="skillsprint-quiz-passing-score small-text" value="<?php echo esc_attr($passing_score); ?>" min="0" max="100">
                                </div>
                                
                                <div class="skillsprint-quiz-field">
                                    <label for="skillsprint_quiz_<?php echo esc_attr($day_number); ?>_max_attempts"><?php esc_html_e('Max Attempts', 'skillsprint'); ?></label>
                                    <input type="number" id="skillsprint_quiz_<?php echo esc_attr($day_number); ?>_max_attempts" class="skillsprint-quiz-max-attempts small-text" value="<?php echo esc_attr($max_attempts); ?>" min="1">
                                </div>
                            </div>
                        </div>
                        
                        <div class="skillsprint-quiz-questions">
                            <h4><?php esc_html_e('Questions', 'skillsprint'); ?></h4>
                            
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
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.skillsprint-blueprint-builder {
    margin-top: 20px;
}

.skillsprint-blueprint-days-tabs {
    display: flex;
    border-bottom: 1px solid #ddd;
    margin-bottom: 20px;
}

.skillsprint-blueprint-day-tab {
    padding: 10px 15px;
    cursor: pointer;
    background-color: #f8f8f8;
    border: 1px solid #ddd;
    border-bottom: none;
    margin-right: 5px;
}

.skillsprint-blueprint-day-tab.active {
    background-color: #fff;
    border-bottom: 1px solid #fff;
    margin-bottom: -1px;
    font-weight: bold;
}

.skillsprint-blueprint-day-content {
    display: none;
    padding: 20px;
    background-color: #fff;
    border: 1px solid #ddd;
}

.skillsprint-blueprint-day-content.active {
    display: block;
}

.skillsprint-form-group {
    margin-bottom: 20px;
}

.skillsprint-form-label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
}

.skillsprint-resources-container,
.skillsprint-quiz-questions-container {
    margin-bottom: 10px;
}

.skillsprint-resource-item,
.skillsprint-quiz-question {
    background-color: #f8f8f8;
    border: 1px solid #ddd;
    padding: 15px;
    margin-bottom: 10px;
}

.skillsprint-resource-fields {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 10px;
}

.skillsprint-quiz-header {
    margin-bottom: 20px;
}

.skillsprint-quiz-settings {
    display: flex;
    gap: 20px;
    margin-top: 10px;
}

.skillsprint-quiz-question-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.skillsprint-quiz-question-title {
    margin: 0;
}

.skillsprint-quiz-question-actions {
    display: flex;
    gap: 5px;
}

.skillsprint-quiz-question-preview {
    background-color: #fff;
    padding: 10px;
    border: 1px solid #ddd;
    margin-bottom: 10px;
}

.skillsprint-quiz-question-type {
    font-weight: bold;
    margin-bottom: 5px;
    color: #2271b1;
}

.skillsprint-quiz-question-options-preview li.correct,
.skillsprint-quiz-question-options-preview div.correct,
.skillsprint-quiz-question-short-answer-preview span.correct {
    color: #46b450;
    font-weight: bold;
}

.skillsprint-quiz-question-explanation-preview {
    margin-top: 10px;
    background-color: #f0f0f1;
    padding: 5px 10px;
    font-style: italic;
}

.skillsprint-quiz-question-points-preview {
    margin-top: 5px;
    font-weight: bold;
}

.skillsprint-quiz-matching-pair {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.skillsprint-quiz-option {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.skillsprint-quiz-true-false-container {
    display: flex;
    gap: 20px;
}

.skillsprint-quiz-question-form {
    background-color: #fff;
    padding: 15px;
    border: 1px solid #ddd;
}

.skillsprint-quiz-field {
    margin-bottom: 15px;
}

.skillsprint-quiz-field label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
}

.skillsprint-quiz-save {
    margin-top: 20px;
}

.skillsprint-quiz-save-status {
    margin-left: 10px;
    font-style: italic;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Initialize tabs
    $('.skillsprint-blueprint-day-tab').first().addClass('active');
    $('.skillsprint-blueprint-day-content').first().addClass('active');
    
    // Tab click handler
    $('.skillsprint-blueprint-day-tab').on('click', function() {
        const dayNumber = $(this).data('day');
        
        // Activate tab
        $('.skillsprint-blueprint-day-tab').removeClass('active');
        $(this).addClass('active');
        
        // Show content
        $('.skillsprint-blueprint-day-content').removeClass('active');
        $(`.skillsprint-blueprint-day-content[data-day="${dayNumber}"]`).addClass('active');
    });
    
    // Add resource button
    $('.skillsprint-add-resource').on('click', function() {
        const dayNumber = $(this).data('day');
        const $container = $(this).prev('.skillsprint-resources-container');
        const resourceIndex = $container.children().length;
        
        const $newResource = $(`
            <div class="skillsprint-resource-item">
                <div class="skillsprint-resource-fields">
                    <input type="text" name="skillsprint_days_data[${dayNumber - 1}][resources][${resourceIndex}][title]" placeholder="Resource Title" class="regular-text">
                    <input type="text" name="skillsprint_days_data[${dayNumber - 1}][resources][${resourceIndex}][url]" placeholder="Resource URL" class="regular-text">
                    <select name="skillsprint_days_data[${dayNumber - 1}][resources][${resourceIndex}][type]">
                        <option value="link">Link</option>
                        <option value="file">Document</option>
                        <option value="video">Video</option>
                    </select>
                </div>
                <button type="button" class="button button-secondary skillsprint-remove-resource">Remove</button>
            </div>
        `);
        
        $container.append($newResource);
    });
    
    // Remove resource button
    $(document).on('click', '.skillsprint-remove-resource', function() {
        $(this).closest('.skillsprint-resource-item').remove();
    });
    
    // Add question button
    $('.skillsprint-add-question').on('click', function() {
        const $container = $(this).prev('.skillsprint-quiz-questions-container');
        const questionId = 'q_' + Date.now();
        
        const $newQuestion = $(`
            <div class="skillsprint-quiz-question" data-question-type="multiple_choice">
                <div class="skillsprint-quiz-question-header">
                    <h4 class="skillsprint-quiz-question-title">New Question</h4>
                    <div class="skillsprint-quiz-question-actions">
                        <button type="button" class="button button-secondary skillsprint-edit-question">Edit</button>
                        <button type="button" class="button button-secondary skillsprint-remove-question">Remove</button>
                    </div>
                </div>
                
                <div class="skillsprint-quiz-question-preview">
                    <div class="skillsprint-quiz-question-type">Multiple Choice</div>
                </div>
                
                <div class="skillsprint-quiz-question-form">
                    <input type="hidden" class="skillsprint-quiz-question-id" value="${questionId}">
                    
                    <div class="skillsprint-quiz-field">
                        <label>Question Text</label>
                        <textarea class="skillsprint-quiz-question-text large-text" rows="2"></textarea>
                    </div>
                    
                    <div class="skillsprint-quiz-field">
                        <label>Question Type</label>
                        <select class="skillsprint-quiz-question-type">
                            <option value="multiple_choice">Multiple Choice</option>
                            <option value="true_false">True/False</option>
                            <option value="multiple_answer">Multiple Answer</option>
                            <option value="matching">Matching</option>
                            <option value="short_answer">Short Answer</option>
                        </select>
                    </div>
                    
                    <!-- Multiple Choice Options -->
                    <div class="skillsprint-quiz-field skillsprint-quiz-options-field">
                        <label>Answer Options</label>
                        <div class="skillsprint-quiz-options-container">
                            <div class="skillsprint-quiz-option">
                                <input type="text" class="skillsprint-quiz-option-text regular-text" value="Option 1">
                                <label>
                                    <input type="radio" class="skillsprint-quiz-option-correct" checked>
                                    Correct
                                </label>
                                <button type="button" class="button button-secondary skillsprint-remove-option">Remove</button>
                            </div>
                            <div class="skillsprint-quiz-option">
                                <input type="text" class="skillsprint-quiz-option-text regular-text" value="Option 2">
                                <label>
                                    <input type="radio" class="skillsprint-quiz-option-correct">
                                    Correct
                                </label>
                                <button type="button" class="button button-secondary skillsprint-remove-option">Remove</button>
                            </div>
                        </div>
                        <button type="button" class="button button-secondary skillsprint-add-option">Add Option</button>
                    </div>
                    
                    <!-- Multiple Answer Options -->
                    <div class="skillsprint-quiz-field skillsprint-quiz-multiple-answer-field" style="display: none;">
                        <label>Answer Options</label>
                        <div class="skillsprint-quiz-options-container">
                            <div class="skillsprint-quiz-option">
                                <input type="text" class="skillsprint-quiz-option-text regular-text" value="Option 1">
                                <label>
                                    <input type="checkbox" class="skillsprint-quiz-option-correct" checked>
                                    Correct
                                </label>
                                <button type="button" class="button button-secondary skillsprint-remove-option">Remove</button>
                            </div>
                            <div class="skillsprint-quiz-option">
                                <input type="text" class="skillsprint-quiz-option-text regular-text" value="Option 2">
                                <label>
                                    <input type="checkbox" class="skillsprint-quiz-option-correct">
                                    Correct
                                </label>
                                <button type="button" class="button button-secondary skillsprint-remove-option">Remove</button>
                            </div>
                        </div>
                        <button type="button" class="button button-secondary skillsprint-add-option">Add Option</button>
                    </div>
                    
                    <!-- True/False -->
                    <div class="skillsprint-quiz-field skillsprint-quiz-true-false-field" style="display: none;">
                        <label>Correct Answer</label>
                        <div class="skillsprint-quiz-true-false-container">
                            <label>
                                <input type="radio" class="skillsprint-quiz-true-false-correct" name="skillsprint-quiz-true-false-new" value="true" checked>
                                True
                            </label>
                            <label>
                                <input type="radio" class="skillsprint-quiz-true-false-correct" name="skillsprint-quiz-true-false-new" value="false">
                                False
                            </label>
                        </div>
                    </div>
                    
                    <!-- Matching -->
                    <div class="skillsprint-quiz-field skillsprint-quiz-matching-field" style="display: none;">
                        <label>Matching Pairs</label>
                        <div class="skillsprint-quiz-matching-container">
                            <div class="skillsprint-quiz-matching-pair">
                                <input type="text" class="skillsprint-quiz-matching-left regular-text" value="" placeholder="Left">
                                <span class="dashicons dashicons-arrow-right-alt"></span>
                                <input type="text" class="skillsprint-quiz-matching-right regular-text" value="" placeholder="Right">
                                <button type="button" class="button button-secondary skillsprint-remove-matching-pair">Remove</button>
                            </div>
                        </div>
                        <button type="button" class="button button-secondary skillsprint-add-matching-pair">Add Pair</button>
                    </div>
                    
                    <!-- Short Answer -->
                    <div class="skillsprint-quiz-field skillsprint-quiz-short-answer-field" style="display: none;">
                        <label>Acceptable Answers</label>
                        <div class="skillsprint-quiz-short-answer-container">
                            <div class="skillsprint-quiz-short-answer">
                                <input type="text" class="skillsprint-quiz-short-answer-text regular-text" value="">
                                <button type="button" class="button button-secondary skillsprint-remove-short-answer">Remove</button>
                            </div>
                        </div>
                        <button type="button" class="button button-secondary skillsprint-add-short-answer">Add Acceptable Answer</button>
                    </div>
                    
                    <div class="skillsprint-quiz-field">
                        <label>Explanation</label>
                        <textarea class="skillsprint-quiz-question-explanation large-text" rows="2"></textarea>
                    </div>
                    
                    <div class="skillsprint-quiz-field">
                        <label>Points</label>
                        <input type="number" class="skillsprint-quiz-question-points small-text" value="1" min="1">
                    </div>
                    
                    <div class="skillsprint-quiz-question-actions">
                        <button type="button" class="button button-primary skillsprint-save-question">Save Question</button>
                        <button type="button" class="button button-secondary skillsprint-cancel-question">Cancel</button>
                    </div>
                </div>
            </div>
        `);
        
        $container.append($newQuestion);
    });
    
    // Remove question button
    $(document).on('click', '.skillsprint-remove-question', function() {
        $(this).closest('.skillsprint-quiz-question').remove();
    });
    
    // Edit question button
    $(document).on('click', '.skillsprint-edit-question', function() {
        const $question = $(this).closest('.skillsprint-quiz-question');
        $question.find('.skillsprint-quiz-question-preview').hide();
        $question.find('.skillsprint-quiz-question-form').show();
    });
    
    // Cancel question edit button
    $(document).on('click', '.skillsprint-cancel-question', function() {
        const $question = $(this).closest('.skillsprint-quiz-question');
        $question.find('.skillsprint-quiz-question-form').hide();
        $question.find('.skillsprint-quiz-question-preview').show();
    });
    
    // Question type change handler
    $(document).on('change', '.skillsprint-quiz-question-type', function() {
        const $question = $(this).closest('.skillsprint-quiz-question');
        const type = $(this).val();
        
        // Update question type attribute
        $question.attr('data-question-type', type);
        
        // Hide all question type specific fields
        $question.find('.skillsprint-quiz-options-field, .skillsprint-quiz-multiple-answer-field, .skillsprint-quiz-true-false-field, .skillsprint-quiz-matching-field, .skillsprint-quiz-short-answer-field').hide();
        
        // Show the appropriate field based on question type
        switch (type) {
            case 'multiple_choice':
                $question.find('.skillsprint-quiz-options-field').show();
                break;
            case 'multiple_answer':
                $question.find('.skillsprint-quiz-multiple-answer-field').show();
                break;
            case 'true_false':
                $question.find('.skillsprint-quiz-true-false-field').show();
                break;
            case 'matching':
                $question.find('.skillsprint-quiz-matching-field').show();
                break;
            case 'short_answer':
                $question.find('.skillsprint-quiz-short-answer-field').show();
                break;
        }
    });
    
    // Add option button
    $(document).on('click', '.skillsprint-add-option', function() {
        const $container = $(this).prev('.skillsprint-quiz-options-container');
        const isMultipleAnswer = $(this).closest('.skillsprint-quiz-multiple-answer-field').length > 0;
        
        const $newOption = $(`
            <div class="skillsprint-quiz-option">
                <input type="text" class="skillsprint-quiz-option-text regular-text" value="New Option">
                <label>
                    <input type="${isMultipleAnswer ? 'checkbox' : 'radio'}" class="skillsprint-quiz-option-correct">
                    Correct
                </label>
                <button type="button" class="button button-secondary skillsprint-remove-option">Remove</button>
            </div>
        `);
        
        $container.append($newOption);
    });
    
    // Remove option button
    $(document).on('click', '.skillsprint-remove-option', function() {
        $(this).closest('.skillsprint-quiz-option').remove();
    });
    
    // Add matching pair button
    $(document).on('click', '.skillsprint-add-matching-pair', function() {
        const $container = $(this).prev('.skillsprint-quiz-matching-container');
        
        const $newPair = $(`
            <div class="skillsprint-quiz-matching-pair">
                <input type="text" class="skillsprint-quiz-matching-left regular-text" value="" placeholder="Left">
                <span class="dashicons dashicons-arrow-right-alt"></span>
                <input type="text" class="skillsprint-quiz-matching-right regular-text" value="" placeholder="Right">
                <button type="button" class="button button-secondary skillsprint-remove-matching-pair">Remove</button>
            </div>
        `);
        
        $container.append($newPair);
    });
    
    // Remove matching pair button
    $(document).on('click', '.skillsprint-remove-matching-pair', function() {
        $(this).closest('.skillsprint-quiz-matching-pair').remove();
    });
    
    // Add short answer button
    $(document).on('click', '.skillsprint-add-short-answer', function() {
        const $container = $(this).prev('.skillsprint-quiz-short-answer-container');
        
        const $newAnswer = $(`
            <div class="skillsprint-quiz-short-answer">
                <input type="text" class="skillsprint-quiz-short-answer-text regular-text" value="">
                <button type="button" class="button button-secondary skillsprint-remove-short-answer">Remove</button>
            </div>
        `);
        
        $container.append($newAnswer);
    });
    
    // Remove short answer button
    $(document).on('click', '.skillsprint-remove-short-answer', function() {
        $(this).closest('.skillsprint-quiz-short-answer').remove();
    });
    
    // Save question button
    $(document).on('click', '.skillsprint-save-question', function() {
        const $question = $(this).closest('.skillsprint-quiz-question');
        const $form = $question.find('.skillsprint-quiz-question-form');
        const $preview = $question.find('.skillsprint-quiz-question-preview');
        
        // Get question data
        const questionId = $form.find('.skillsprint-quiz-question-id').val();
        const questionText = $form.find('.skillsprint-quiz-question-text').val();
        const questionType = $form.find('.skillsprint-quiz-question-type').val();
        const questionExplanation = $form.find('.skillsprint-quiz-question-explanation').val();
        const questionPoints = $form.find('.skillsprint-quiz-question-points').val();
        
        // Build question data based on type
        let correctAnswer;
        let options = [];
        
        switch (questionType) {
            case 'multiple_choice':
                $form.find('.skillsprint-quiz-options-field .skillsprint-quiz-option').each(function(index) {
                    const optionText = $(this).find('.skillsprint-quiz-option-text').val();
                    const isCorrect = $(this).find('.skillsprint-quiz-option-correct').is(':checked');
                    
                    options.push(optionText);
                    
                    if (isCorrect) {
                        correctAnswer = index;
                    }
                });
                break;
                
            case 'multiple_answer':
                correctAnswer = [];
                
                $form.find('.skillsprint-quiz-multiple-answer-field .skillsprint-quiz-option').each(function(index) {
                    const optionText = $(this).find('.skillsprint-quiz-option-text').val();
                    const isCorrect = $(this).find('.skillsprint-quiz-option-correct').is(':checked');
                    
                    options.push(optionText);
                    
                    if (isCorrect) {
                        correctAnswer.push(index);
                    }
                });
                break;
                
            case 'true_false':
                correctAnswer = $form.find('.skillsprint-quiz-true-false-correct:checked').val();
                break;
                
            case 'matching':
                correctAnswer = {};
                options = [];
                
                $form.find('.skillsprint-quiz-matching-pair').each(function() {
                    const leftText = $(this).find('.skillsprint-quiz-matching-left').val();
                    const rightText = $(this).find('.skillsprint-quiz-matching-right').val();
                    
                    if (leftText && rightText) {
                        options.push({
                            left: leftText,
                            right: rightText
                        });
                        
                        correctAnswer[leftText] = rightText;
                    }
                });
                break;
                
            case 'short_answer':
                correctAnswer = [];
                
                $form.find('.skillsprint-quiz-short-answer-text').each(function() {
                    const answerText = $(this).val();
                    
                    if (answerText) {
                        correctAnswer.push(answerText);
                    }
                });
                break;
        }
        
        // Update question preview
        $question.find('.skillsprint-quiz-question-title').text(questionText);
        
        let previewHtml = `
            <div class="skillsprint-quiz-question-type">${questionType.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</div>
        `;
        
        switch (questionType) {
            case 'multiple_choice':
                previewHtml += '<ul class="skillsprint-quiz-question-options-preview">';
                
                options.forEach((option, index) => {
                    const isCorrect = index === correctAnswer;
                    previewHtml += `
                        <li class="${isCorrect ? 'correct' : ''}">
                            ${option}
                            ${isCorrect ? '<span class="dashicons dashicons-yes"></span>' : ''}
                        </li>
                    `;
                });
                
                previewHtml += '</ul>';
                break;
                
            case 'true_false':
                previewHtml += '<div class="skillsprint-quiz-question-options-preview">';
                previewHtml += `
                    <div class="${correctAnswer === 'true' ? 'correct' : ''}">
                        True
                        ${correctAnswer === 'true' ? '<span class="dashicons dashicons-yes"></span>' : ''}
                    </div>
                    <div class="${correctAnswer === 'false' ? 'correct' : ''}">
                        False
                        ${correctAnswer === 'false' ? '<span class="dashicons dashicons-yes"></span>' : ''}
                    </div>
                `;
                previewHtml += '</div>';
                break;
                
            case 'multiple_answer':
                previewHtml += '<ul class="skillsprint-quiz-question-options-preview">';
                
                options.forEach((option, index) => {
                    const isCorrect = correctAnswer.includes(index);
                    previewHtml += `
                        <li class="${isCorrect ? 'correct' : ''}">
                            ${option}
                            ${isCorrect ? '<span class="dashicons dashicons-yes"></span>' : ''}
                        </li>
                    `;
                });
                
                previewHtml += '</ul>';
                break;
                
            case 'matching':
                previewHtml += '<table class="skillsprint-quiz-question-matching-preview">';
                previewHtml += '<tr><th>Left</th><th>Right</th></tr>';
                
                options.forEach(option => {
                    previewHtml += `
                        <tr>
                            <td>${option.left}</td>
                            <td>${option.right}</td>
                        </tr>
                    `;
                });
                
                previewHtml += '</table>';
                break;
                
            case 'short_answer':
                previewHtml += '<div class="skillsprint-quiz-question-short-answer-preview">';
                previewHtml += 'Acceptable answers: <span class="correct">' + correctAnswer.join(', ') + '</span>';
                previewHtml += '</div>';
                break;
        }
        
        if (questionExplanation) {
            previewHtml += `
                <div class="skillsprint-quiz-question-explanation-preview">
                    <strong>Explanation:</strong> ${questionExplanation}
                </div>
            `;
        }
        
        if (questionPoints && parseInt(questionPoints) > 1) {
            previewHtml += `
                <div class="skillsprint-quiz-question-points-preview">
                    Points: ${questionPoints}
                </div>
            `;
        }
        
        $preview.html(previewHtml);
        
        // Hide form and show preview
        $form.hide();
        $preview.show();
    });
    
    // Save quiz button
    $('.skillsprint-save-quiz').on('click', function() {
        const $button = $(this);
        const dayNumber = $button.data('day');
        const quizId = $button.data('quiz-id');
        const blueprintId = $button.data('blueprint-id');
        const $quizContainer = $button.closest('.skillsprint-quiz-container');
        const $status = $button.next('.skillsprint-quiz-save-status');
        
        // Get quiz data
        const quizTitle = $quizContainer.find('.skillsprint-quiz-title').val();
        const quizDescription = $quizContainer.find('.skillsprint-quiz-description').val();
        const passingScore = $quizContainer.find('.skillsprint-quiz-passing-score').val();
        const maxAttempts = $quizContainer.find('.skillsprint-quiz-max-attempts').val();
        
        // Get questions
        const questions = [];
        
        $quizContainer.find('.skillsprint-quiz-question').each(function() {
            const $question = $(this);
            const $form = $question.find('.skillsprint-quiz-question-form');
            
            const questionId = $form.find('.skillsprint-quiz-question-id').val();
            const questionText = $form.find('.skillsprint-quiz-question-text').val();
            const questionType = $form.find('.skillsprint-quiz-question-type').val();
            const questionExplanation = $form.find('.skillsprint-quiz-question-explanation').val();
            const questionPoints = $form.find('.skillsprint-quiz-question-points').val();
            
            // Build question data based on type
            let correctAnswer;
            let options = [];
            
            switch (questionType) {
                case 'multiple_choice':
                    $form.find('.skillsprint-quiz-options-field .skillsprint-quiz-option').each(function(index) {
                        const optionText = $(this).find('.skillsprint-quiz-option-text').val();
                        const isCorrect = $(this).find('.skillsprint-quiz-option-correct').is(':checked');
                        
                        options.push(optionText);
                        
                        if (isCorrect) {
                            correctAnswer = index;
                        }
                    });
                    break;
                    
                case 'multiple_answer':
                    correctAnswer = [];
                    
                    $form.find('.skillsprint-quiz-multiple-answer-field .skillsprint-quiz-option').each(function(index) {
                        const optionText = $(this).find('.skillsprint-quiz-option-text').val();
                        const isCorrect = $(this).find('.skillsprint-quiz-option-correct').is(':checked');
                        
                        options.push(optionText);
                        
                        if (isCorrect) {
                            correctAnswer.push(index);
                        }
                    });
                    break;
                    
                case 'true_false':
                    correctAnswer = $form.find('.skillsprint-quiz-true-false-correct:checked').val();
                    break;
                    
                case 'matching':
                    correctAnswer = {};
                    options = [];
                    
                    $form.find('.skillsprint-quiz-matching-pair').each(function() {
                        const leftText = $(this).find('.skillsprint-quiz-matching-left').val();
                        const rightText = $(this).find('.skillsprint-quiz-matching-right').val();
                        
                        if (leftText && rightText) {
                            options.push({
                                left: leftText,
                                right: rightText
                            });
                            
                            correctAnswer[leftText] = rightText;
                        }
                    });
                    break;
                    
                case 'short_answer':
                    correctAnswer = [];
                    
                    $form.find('.skillsprint-quiz-short-answer-text').each(function() {
                        const answerText = $(this).val();
                        
                        if (answerText) {
                            correctAnswer.push(answerText);
                        }
                    });
                    break;
            }
            
            questions.push({
                id: questionId,
                type: questionType,
                text: questionText,
                options: options,
                correct_answer: correctAnswer,
                explanation: questionExplanation,
                points: parseInt(questionPoints) || 1
            });
        });
        
        // Build quiz data
        const quizData = {
            title: quizTitle,
            description: quizDescription,
            passing_score: parseInt(passingScore) || 70,
            max_attempts: parseInt(maxAttempts) || 3,
            questions: questions
        };
        
        // Send AJAX request to save quiz data
        $button.prop('disabled', true).text('Saving...');
        $status.text('');
        
        $.post(ajaxurl, {
            action: 'skillsprint_save_quiz_data',
            blueprint_id: blueprintId,
            quiz_id: quizId,
            quiz_data: quizData,
            nonce: $('#skillsprint_blueprint_days_nonce').val()
        }, function(response) {
            if (response.success) {
                $status.text('Quiz saved successfully!').css('color', 'green');
            } else {
                $status.text('Error saving quiz: ' + response.data.message).css('color', 'red');
            }
            
            $button.prop('disabled', false).text('Save Quiz');
            
            // Automatically hide status after 3 seconds
            setTimeout(function() {
                $status.text('').css('color', '');
            }, 3000);
        }).fail(function() {
            $status.text('Error saving quiz. Please try again.').css('color', 'red');
            $button.prop('disabled', false).text('Save Quiz');
        });
    });
});
</script>

