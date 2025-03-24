<?php
/**
 * Simplified Blueprint content template
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
        <h1 class="skillsprint-blueprint-title"><?php echo esc_html($post->post_title); ?></h1>
        
        <div class="skillsprint-blueprint-meta">
            <div class="skillsprint-blueprint-meta-item">
                <i class="dashicons dashicons-admin-users"></i>
                <?php echo esc_html(get_the_author_meta('display_name', $post->post_author)); ?>
            </div>
            
            <div class="skillsprint-blueprint-meta-item">
                <i class="dashicons dashicons-calendar-alt"></i>
                <?php echo esc_html(get_the_date('', $post->ID)); ?>
            </div>
        </div>
        
        <div class="skillsprint-blueprint-description">
            <?php echo wpautop(wp_kses_post(get_the_excerpt($post->ID))); ?>
        </div>
    </div>
    
    <div class="skillsprint-days-content">
        <div class="skillsprint-day-content active">
            <?php echo wpautop(wp_kses_post($post->post_content)); ?>
            
            <?php if (is_user_logged_in()): ?>
            <div class="skillsprint-day-actions">
                <button class="skillsprint-button">
                    <?php esc_html_e('Start Learning', 'skillsprint'); ?>
                </button>
            </div>
            <?php else: ?>
            <div class="skillsprint-day-actions">
                <button class="skillsprint-button skillsprint-login-button">
                    <?php esc_html_e('Log In to Start Learning', 'skillsprint'); ?>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>