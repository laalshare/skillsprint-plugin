<?php
/**
 * Login required template
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/public/partials
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="skillsprint-login-required">
    <div class="skillsprint-alert warning">
        <h3 class="skillsprint-alert-title"><?php esc_html_e('Login Required', 'skillsprint'); ?></h3>
        <p class="skillsprint-alert-message"><?php esc_html_e('You need to be logged in to access this content.', 'skillsprint'); ?></p>
    </div>
    
    <p><?php esc_html_e('Please log in to track your progress, access premium content, and earn achievements as you learn.', 'skillsprint'); ?></p>
    
    <div class="skillsprint-login-buttons">
        <button class="skillsprint-button skillsprint-login-button"><?php esc_html_e('Log In', 'skillsprint'); ?></button>
        <button class="skillsprint-button secondary skillsprint-register-button"><?php esc_html_e('Create Account', 'skillsprint'); ?></button>
    </div>
    
    <div class="skillsprint-why-register">
        <h3><?php esc_html_e('Why Register?', 'skillsprint'); ?></h3>
        <ul>
            <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Track your progress across all blueprints', 'skillsprint'); ?></li>
            <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Access Days 3-7 of all blueprints', 'skillsprint'); ?></li>
            <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Earn points and achievements as you learn', 'skillsprint'); ?></li>
            <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Build learning streaks for bonus points', 'skillsprint'); ?></li>
            <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Get personalized blueprint recommendations', 'skillsprint'); ?></li>
        </ul>
    </div>
</div>