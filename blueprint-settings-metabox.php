<?php
/**
 * Blueprint settings metabox template.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/admin/partials
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="skillsprint-blueprint-settings">
    <div class="skillsprint-form-group">
        <label for="skillsprint_estimated_completion_time" class="skillsprint-form-label"><?php esc_html_e('Estimated Completion Time', 'skillsprint'); ?></label>
        <input type="text" id="skillsprint_estimated_completion_time" name="skillsprint_estimated_completion_time" value="<?php echo esc_attr($estimated_completion_time); ?>" class="regular-text">
        <p class="description"><?php esc_html_e('E.g., "30 minutes per day" or "3.5 hours total"', 'skillsprint'); ?></p>
    </div>
    
    <div class="skillsprint-form-group">
        <label for="skillsprint_recommended_background" class="skillsprint-form-label"><?php esc_html_e('Recommended Background', 'skillsprint'); ?></label>
        <textarea id="skillsprint_recommended_background" name="skillsprint_recommended_background" rows="3" class="large-text"><?php echo esc_textarea($recommended_background); ?></textarea>
        <p class="description"><?php esc_html_e('What background knowledge or experience would be helpful.', 'skillsprint'); ?></p>
    </div>
    
    <div class="skillsprint-form-group">
        <label for="skillsprint_prerequisites" class="skillsprint-form-label"><?php esc_html_e('Prerequisites', 'skillsprint'); ?></label>
        <textarea id="skillsprint_prerequisites" name="skillsprint_prerequisites" rows="3" class="large-text"><?php echo esc_textarea($prerequisites); ?></textarea>
        <p class="description"><?php esc_html_e('Any required knowledge or tools needed for this blueprint.', 'skillsprint'); ?></p>
    </div>
    
    <div class="skillsprint-form-group">
        <label for="skillsprint_what_youll_learn" class="skillsprint-form-label"><?php esc_html_e('What You\'ll Learn', 'skillsprint'); ?></label>
        <textarea id="skillsprint_what_youll_learn" name="skillsprint_what_youll_learn" rows="4" class="large-text"><?php echo esc_textarea($what_youll_learn); ?></textarea>
        <p class="description"><?php esc_html_e('Key takeaways and skills learners will gain from this blueprint.', 'skillsprint'); ?></p>
    </div>
</div>