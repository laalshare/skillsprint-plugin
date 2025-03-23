<?php
/**
 * Login modal template
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/public/partials
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="skillsprint-login-modal" class="skillsprint-modal-overlay">
    <div class="skillsprint-modal">
        <div class="skillsprint-modal-header">
            <h3 class="skillsprint-modal-title"><?php esc_html_e('Log In', 'skillsprint'); ?></h3>
            <button type="button" class="skillsprint-modal-close">&times;</button>
        </div>
        
        <div class="skillsprint-modal-body">
            <form id="skillsprint-login-form" class="skillsprint-form">
                <div class="skillsprint-form-message"></div>
                
                <div class="skillsprint-form-group">
                    <label for="skillsprint-login-username" class="skillsprint-form-label"><?php esc_html_e('Username or Email', 'skillsprint'); ?></label>
                    <input type="text" id="skillsprint-login-username" name="username" class="skillsprint-form-input" required>
                </div>
                
                <div class="skillsprint-form-group">
                    <label for="skillsprint-login-password" class="skillsprint-form-label"><?php esc_html_e('Password', 'skillsprint'); ?></label>
                    <input type="password" id="skillsprint-login-password" name="password" class="skillsprint-form-input" required>
                </div>
                
                <div class="skillsprint-form-group">
                    <label class="skillsprint-form-checkbox">
                        <input type="checkbox" name="remember" value="1">
                        <?php esc_html_e('Remember Me', 'skillsprint'); ?>
                    </label>
                </div>
                
                <div class="skillsprint-form-group">
                    <button type="submit" class="skillsprint-button full-width"><?php esc_html_e('Log In', 'skillsprint'); ?></button>
                </div>
            </form>
            
            <div class="skillsprint-form-links">
                <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" target="_blank"><?php esc_html_e('Forgot Password?', 'skillsprint'); ?></a>
                <span class="skillsprint-separator">|</span>
                <a href="#" class="skillsprint-register-button"><?php esc_html_e('Create Account', 'skillsprint'); ?></a>
            </div>
        </div>
    </div>
</div>