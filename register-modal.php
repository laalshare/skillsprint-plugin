<?php
/**
 * Register modal template
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/public/partials
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="skillsprint-register-modal" class="skillsprint-modal-overlay">
    <div class="skillsprint-modal">
        <div class="skillsprint-modal-header">
            <h3 class="skillsprint-modal-title"><?php esc_html_e('Create Account', 'skillsprint'); ?></h3>
            <button type="button" class="skillsprint-modal-close">&times;</button>
        </div>
        
        <div class="skillsprint-modal-body">
            <form id="skillsprint-register-form" class="skillsprint-form">
                <div class="skillsprint-form-message"></div>
                
                <div class="skillsprint-form-group">
                    <label for="skillsprint-register-username" class="skillsprint-form-label"><?php esc_html_e('Username', 'skillsprint'); ?></label>
                    <input type="text" id="skillsprint-register-username" name="username" class="skillsprint-form-input" required>
                </div>
                
                <div class="skillsprint-form-group">
                    <label for="skillsprint-register-email" class="skillsprint-form-label"><?php esc_html_e('Email', 'skillsprint'); ?></label>
                    <input type="email" id="skillsprint-register-email" name="email" class="skillsprint-form-input" required>
                </div>
                
                <div class="skillsprint-form-group">
                    <label for="skillsprint-register-password" class="skillsprint-form-label"><?php esc_html_e('Password', 'skillsprint'); ?></label>
                    <input type="password" id="skillsprint-register-password" name="password" class="skillsprint-form-input" required>
                </div>
                
                <div class="skillsprint-form-group">
                    <label for="skillsprint-register-password-confirm" class="skillsprint-form-label"><?php esc_html_e('Confirm Password', 'skillsprint'); ?></label>
                    <input type="password" id="skillsprint-register-password-confirm" name="password_confirm" class="skillsprint-form-input" required>
                </div>
                
                <div class="skillsprint-form-group">
                    <button type="submit" class="skillsprint-button full-width"><?php esc_html_e('Create Account', 'skillsprint'); ?></button>
                </div>
            </form>
            
            <div class="skillsprint-form-links">
                <?php esc_html_e('Already have an account?', 'skillsprint'); ?> 
                <a href="#" class="skillsprint-login-button"><?php esc_html_e('Log In', 'skillsprint'); ?></a>
            </div>
        </div>
    </div>
</div>