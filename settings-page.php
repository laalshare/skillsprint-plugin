<?php
/**
 * Settings page template
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/admin/partials
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <form method="post" action="options.php">
        <?php
        settings_fields('skillsprint_settings');
        $options = get_option('skillsprint_settings');
        ?>
        
        <div class="skillsprint-settings-section">
            <h2><?php esc_html_e('General Settings', 'skillsprint'); ?></h2>
            <p><?php esc_html_e('Configure general settings for the 7-Day Learning Platform.', 'skillsprint'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="skillsprint_days_free_access"><?php esc_html_e('Days With Free Access', 'skillsprint'); ?></label>
                    </th>
                    <td>
                        <input type="number" min="0" max="7" id="skillsprint_days_free_access" name="skillsprint_settings[days_free_access]" value="<?php echo esc_attr($options['days_free_access'] ?? 2); ?>" class="small-text">
                        <p class="description"><?php esc_html_e('Number of days that will be freely accessible without requiring user registration (0-7).', 'skillsprint'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php esc_html_e('Strict Day Progression', 'skillsprint'); ?>
                    </th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><?php esc_html_e('Strict Day Progression', 'skillsprint'); ?></legend>
                            <label for="skillsprint_strict_progression">
                                <input type="checkbox" id="skillsprint_strict_progression" name="skillsprint_settings[strict_progression]" value="1" <?php checked(isset($options['strict_progression']) ? $options['strict_progression'] : true); ?>>
                                <?php esc_html_e('If enabled, users must complete each day before accessing the next one.', 'skillsprint'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="skillsprint-settings-section">
            <h2><?php esc_html_e('Gamification Settings', 'skillsprint'); ?></h2>
            <p><?php esc_html_e('Configure gamification features like points, streaks, and achievements.', 'skillsprint'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <?php esc_html_e('Enable Gamification', 'skillsprint'); ?>
                    </th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><?php esc_html_e('Enable Gamification', 'skillsprint'); ?></legend>
                            <label for="skillsprint_gamification_enabled">
                                <input type="checkbox" id="skillsprint_gamification_enabled" name="skillsprint_settings[gamification_enabled]" value="1" <?php checked(isset($options['gamification_enabled']) ? $options['gamification_enabled'] : true); ?>>
                                <?php esc_html_e('Enable gamification features like points, streaks, and achievements.', 'skillsprint'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php esc_html_e('Enable Leaderboard', 'skillsprint'); ?>
                    </th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><?php esc_html_e('Enable Leaderboard', 'skillsprint'); ?></legend>
                            <label for="skillsprint_leaderboard_enabled">
                                <input type="checkbox" id="skillsprint_leaderboard_enabled" name="skillsprint_settings[leaderboard_enabled]" value="1" <?php checked(isset($options['leaderboard_enabled']) ? $options['leaderboard_enabled'] : true); ?>>
                                <?php esc_html_e('Enable the leaderboard to show top learners.', 'skillsprint'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="skillsprint_points_per_day_completion"><?php esc_html_e('Points Per Day Completion', 'skillsprint'); ?></label>
                    </th>
                    <td>
                        <input type="number" min="0" id="skillsprint_points_per_day_completion" name="skillsprint_settings[points_per_day_completion]" value="<?php echo esc_attr($options['points_per_day_completion'] ?? 10); ?>" class="small-text">
                        <p class="description"><?php esc_html_e('Points awarded for completing a day.', 'skillsprint'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="skillsprint_points_per_quiz_correct"><?php esc_html_e('Points Per Correct Quiz Answer', 'skillsprint'); ?></label>
                    </th>
                    <td>
                        <input type="number" min="0" id="skillsprint_points_per_quiz_correct" name="skillsprint_settings[points_per_quiz_correct]" value="<?php echo esc_attr($options['points_per_quiz_correct'] ?? 5); ?>" class="small-text">
                        <p class="description"><?php esc_html_e('Points awarded for each correct quiz answer.', 'skillsprint'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="skillsprint_points_per_blueprint_completion"><?php esc_html_e('Points Per Blueprint Completion', 'skillsprint'); ?></label>
                    </th>
                    <td>
                        <input type="number" min="0" id="skillsprint_points_per_blueprint_completion" name="skillsprint_settings[points_per_blueprint_completion]" value="<?php echo esc_attr($options['points_per_blueprint_completion'] ?? 50); ?>" class="small-text">
                        <p class="description"><?php esc_html_e('Points awarded for completing an entire blueprint.', 'skillsprint'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="skillsprint_streak_bonus_multiplier"><?php esc_html_e('Streak Bonus Multiplier', 'skillsprint'); ?></label>
                    </th>
                    <td>
                        <input type="number" min="1" step="0.1" id="skillsprint_streak_bonus_multiplier" name="skillsprint_settings[streak_bonus_multiplier]" value="<?php echo esc_attr($options['streak_bonus_multiplier'] ?? 1.5); ?>" class="small-text">
                        <p class="description"><?php esc_html_e('Multiplier for points when user has an active streak (e.g., 1.5 means 50% bonus).', 'skillsprint'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="skillsprint-settings-section">
            <h2><?php esc_html_e('Quiz Settings', 'skillsprint'); ?></h2>
            <p><?php esc_html_e('Configure default quiz and assessment settings.', 'skillsprint'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="skillsprint_default_quiz_pass_score"><?php esc_html_e('Default Quiz Pass Score (%)', 'skillsprint'); ?></label>
                    </th>
                    <td>
                        <input type="number" min="0" max="100" id="skillsprint_default_quiz_pass_score" name="skillsprint_settings[default_quiz_pass_score]" value="<?php echo esc_attr($options['default_quiz_pass_score'] ?? 70); ?>" class="small-text">
                        <p class="description"><?php esc_html_e('Default passing score percentage for quizzes.', 'skillsprint'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="skillsprint_max_quiz_attempts"><?php esc_html_e('Default Max Quiz Attempts', 'skillsprint'); ?></label>
                    </th>
                    <td>
                        <input type="number" min="1" id="skillsprint_max_quiz_attempts" name="skillsprint_settings[max_quiz_attempts]" value="<?php echo esc_attr($options['max_quiz_attempts'] ?? 3); ?>" class="small-text">
                        <p class="description"><?php esc_html_e('Default maximum number of attempts for quizzes.', 'skillsprint'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="skillsprint-settings-section">
            <h2><?php esc_html_e('Advanced Settings', 'skillsprint'); ?></h2>
            <p><?php esc_html_e('Configure advanced plugin settings.', 'skillsprint'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <?php esc_html_e('Preserve Data on Uninstall', 'skillsprint'); ?>
                    </th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><?php esc_html_e('Preserve Data on Uninstall', 'skillsprint'); ?></legend>
                            <label for="skillsprint_preserve_data_on_uninstall">
                                <input type="checkbox" id="skillsprint_preserve_data_on_uninstall" name="skillsprint_settings[preserve_data_on_uninstall]" value="1" <?php checked(isset($options['preserve_data_on_uninstall']) ? $options['preserve_data_on_uninstall'] : true); ?>>
                                <?php esc_html_e('If enabled, plugin data (blueprints, user progress, etc.) will be preserved when the plugin is uninstalled.', 'skillsprint'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </table>
        </div>
        
        <?php submit_button(); ?>
    </form>
</div>