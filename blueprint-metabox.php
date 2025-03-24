<?php
/**
 * Blueprint metabox template
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
        <?php for ($i = 1; $i <= 7; $i++) : ?>
            <div class="skillsprint-blueprint-day-tab" data-day="<?php echo esc_attr($i); ?>">
                <span class="skillsprint-day-number"><?php echo esc_html($i); ?></span>
                <span class="skillsprint-day-label"><?php printf(esc_html__('Day %d', 'skillsprint'), $i); ?></span>
            </div>
        <?php endfor; ?>
    </div>
    
    <div class="skillsprint-blueprint-days-content">
        <?php for ($i = 0; $i < 7; $i++) : 
            $day_number = $i + 1;
            $day_data = isset($days_data[$i]) ? $days_data[$i] : array(
                'day_number' => $day_number,
                'title' => sprintf(__('Day %d', 'skillsprint'), $day_number),
                'learning_objectives' => '',
                'content' => '',
                'resources' => array(),
                'quiz_id' => ''
            );
            $title = isset($day_data['title']) ? $day_data['title'] : sprintf(__('Day %d', 'skillsprint'), $day_number);
            $learning_objectives = isset($day_data['learning_objectives']) ? $day_data['learning_objectives'] : '';
            $content = isset($day_data['content']) ? $day_data['content'] : '';
            $resources = isset($day_data['resources']) ? $day_data['resources'] : array();
            $quiz_id = isset($day_data['quiz_id']) ? $day_data['quiz_id'] : sprintf('quiz_day_%d', $day_number);
        ?>
            <div class="skillsprint-blueprint-day-content" data-day="<?php echo esc_attr($day_number); ?>">
                <div class="skillsprint-form-group">
                    <label for="skillsprint_day_<?php echo esc_attr($day_number); ?>_title" class="skillsprint-form-label"><?php esc_html_e('Day Title', 'skillsprint'); ?></label>
                    <input type="text" id="skillsprint_day_<?php echo esc_attr($day_number); ?>_title" name="skillsprint_days_data[<?php echo esc_attr($i); ?>][title]" value="<?php echo esc_attr($title); ?>" class="widefat">
                </div>
                
                <div class="skillsprint-form-group">
                    <label for="skillsprint_day_<?php echo esc_attr($day_number); ?>_learning_objectives" class="skillsprint-form-label"><?php esc_html_e('Learning Objectives', 'skillsprint'); ?></label>
                    <textarea id="skillsprint_day_<?php echo esc_attr($day_number); ?>_learning_objectives" name="skillsprint_days_data[<?php echo esc_attr($i); ?>][learning_objectives]" rows="4" class="widefat"><?php echo esc_textarea($learning_objectives); ?></textarea>
                    <p class="description"><?php esc_html_e('Enter learning objectives for this day, one per line.', 'skillsprint'); ?></p>
                </div>
                
                <div class="skillsprint-form-group">
                    <label for="skillsprint_day_<?php echo esc_attr($day_number); ?>_content" class="skillsprint-form-label"><?php esc_html_e('Day Content', 'skillsprint'); ?></label>
                    <?php
                    wp_editor(
                        $content,
                        'skillsprint_day_' . $day_number . '_content',
                        array(
                            'media_buttons' => true,
                            'textarea_name' => 'skillsprint_days_data[' . $i . '][content]',
                            'textarea_rows' => 10,
                            'teeny' => false
                        )
                    );
                    ?>
                </div>
                
                <div class="skillsprint-form-group">
                    <label class="skillsprint-form-label"><?php esc_html_e('Resources', 'skillsprint'); ?></label>
                    <div class="skillsprint-resources-container">
                        <?php
                        if (!empty($resources)) {
                            foreach ($resources as $resource_index => $resource) {
                                $resource_title = isset($resource['title']) ? $resource['title'] : '';
                                $resource_url = isset($resource['url']) ? $resource['url'] : '';
                                $resource_type = isset($resource['type']) ? $resource['type'] : 'link';
                                ?>
                                <div class="skillsprint-resource-item">
                                    <div class="skillsprint-resource-fields">
                                        <input type="text" name="skillsprint_days_data[<?php echo esc_attr($i); ?>][resources][<?php echo esc_attr($resource_index); ?>][title]" value="<?php echo esc_attr($resource_title); ?>" placeholder="<?php esc_attr_e('Resource Title', 'skillsprint'); ?>" class="regular-text">
                                        <input type="text" name="skillsprint_days_data[<?php echo esc_attr($i); ?>][resources][<?php echo esc_attr($resource_index); ?>][url]" value="<?php echo esc_attr($resource_url); ?>" placeholder="<?php esc_attr_e('Resource URL', 'skillsprint'); ?>" class="regular-text">
                                        <select name="skillsprint_days_data[<?php echo esc_attr($i); ?>][resources][<?php echo esc_attr($resource_index); ?>][type]">
                                            <option value="link" <?php selected($resource_type, 'link'); ?>><?php esc_html_e('Link', 'skillsprint'); ?></option>
                                            <option value="file" <?php selected($resource_type, 'file'); ?>><?php esc_html_e('Document', 'skillsprint'); ?></option>
                                            <option value="video" <?php selected($resource_type, 'video'); ?>><?php esc_html_e('Video', 'skillsprint'); ?></option>
                                        </select>
                                        <button type="button" class="button button-secondary skillsprint-remove-resource"><?php esc_html_e('Remove', 'skillsprint'); ?></button>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                    <button type="button" class="button button-secondary skillsprint-add-resource" data-day="<?php echo esc_attr($day_number); ?>"><?php esc_html_e('Add Resource', 'skillsprint'); ?></button>
                </div>
                
                <div class="skillsprint-form-group">
                    <label class="skillsprint-form-label"><?php esc_html_e('Quiz', 'skillsprint'); ?></label>
                    <div class="skillsprint-quiz-header">
                        <input type="hidden" class="skillsprint-quiz-id" name="skillsprint_days_data[<?php echo esc_attr($i); ?>][quiz_id]" value="<?php echo esc_attr($quiz_id); ?>">
                        <button type="button" class="button button-primary skillsprint-edit-quiz" data-quiz-id="<?php echo esc_attr($quiz_id); ?>" data-day="<?php echo esc_attr($day_number); ?>" data-blueprint-id="<?php echo esc_attr($post->ID); ?>"><?php esc_html_e('Edit Quiz', 'skillsprint'); ?></button>
                        <p class="description"><?php esc_html_e('Create a quiz that users must complete to progress to the next day.', 'skillsprint'); ?></p>
                    </div>
                    <div class="skillsprint-quiz-editor" style="display: none;">
                        <div class="skillsprint-quiz-settings">
                            <div class="skillsprint-form-group">
                                <label for="skillsprint_quiz_title_<?php echo esc_attr($day_number); ?>" class="skillsprint-form-label"><?php esc_html_e('Quiz Title', 'skillsprint'); ?></label>
                                <input type="text" id="skillsprint_quiz_title_<?php echo esc_attr($day_number); ?>" class="skillsprint-quiz-title widefat" value="<?php echo esc_attr(sprintf(__('Day %d Quiz', 'skillsprint'), $day_number)); ?>">
                            </div>
                            <div class="skillsprint-form-group">
                                <label for="skillsprint_quiz_description_<?php echo esc_attr($day_number); ?>" class="skillsprint-form-label"><?php esc_html_e('Quiz Description', 'skillsprint'); ?></label>
                                <textarea id="skillsprint_quiz_description_<?php echo esc_attr($day_number); ?>" class="skillsprint-quiz-description widefat" rows="3"></textarea>
                            </div>
                            <div class="skillsprint-form-row">
                                <div class="skillsprint-form-group">
                                    <label for="skillsprint_quiz_passing_score_<?php echo esc_attr($day_number); ?>" class="skillsprint-form-label"><?php esc_html_e('Passing Score (%)', 'skillsprint'); ?></label>
                                    <input type="number" id="skillsprint_quiz_passing_score_<?php echo esc_attr($day_number); ?>" class="skillsprint-quiz-passing-score" min="0" max="100" value="70">
                                </div>
                                <div class="skillsprint-form-group">
                                    <label for="skillsprint_quiz_max_attempts_<?php echo esc_attr($day_number); ?>" class="skillsprint-form-label"><?php esc_html_e('Max Attempts', 'skillsprint'); ?></label>
                                    <input type="number" id="skillsprint_quiz_max_attempts_<?php echo esc_attr($day_number); ?>" class="skillsprint-quiz-max-attempts" min="0" value="3">
                                    <p class="description"><?php esc_html_e('0 for unlimited attempts', 'skillsprint'); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="skillsprint-quiz-questions">
                            <h3><?php esc_html_e('Questions', 'skillsprint'); ?></h3>
                            <div class="skillsprint-quiz-questions-container"></div>
                            <button type="button" class="button button-secondary skillsprint-add-question"><?php esc_html_e('Add Question', 'skillsprint'); ?></button>
                        </div>
                        
                        <div class="skillsprint-quiz-actions">
                            <button type="button" class="button button-primary skillsprint-save-quiz" data-quiz-id="<?php echo esc_attr($quiz_id); ?>" data-day="<?php echo esc_attr($day_number); ?>" data-blueprint-id="<?php echo esc_attr($post->ID); ?>"><?php esc_html_e('Save Quiz', 'skillsprint'); ?></button>
                            <span class="skillsprint-quiz-save-status"></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endfor; ?>
    </div>
    
    <div class="skillsprint-builder-actions">
        <button type="button" class="button button-primary skillsprint-save-blueprint"><?php esc_html_e('Save Blueprint', 'skillsprint'); ?></button>
        <span class="skillsprint-save-status"></span>
    </div>
</div>