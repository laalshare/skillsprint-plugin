<?php
/**
 * User progress page template
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/admin/partials
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get users with progress
global $wpdb;
$progress_table = $wpdb->prefix . 'skillsprint_progress';
$user_ids = $wpdb->get_col("SELECT DISTINCT user_id FROM $progress_table ORDER BY user_id ASC");

// Get all blueprints
$blueprints = get_posts(array(
    'post_type' => 'blueprint',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
));
?>

<div class="wrap" id="skillsprint-user-progress-page">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="skillsprint-user-progress-filters">
        <div class="form-field">
            <label for="skillsprint-user-select"><?php esc_html_e('Select User', 'skillsprint'); ?></label>
            <select id="skillsprint-user-select">
                <option value=""><?php esc_html_e('-- Select User --', 'skillsprint'); ?></option>
                <?php
                if (!empty($user_ids)) {
                    foreach ($user_ids as $user_id) {
                        $user = get_user_by('id', $user_id);
                        if ($user) {
                            echo '<option value="' . esc_attr($user_id) . '">' . esc_html($user->display_name) . ' (' . esc_html($user->user_email) . ')</option>';
                        }
                    }
                }
                ?>
            </select>
        </div>
        
        <div class="form-field">
            <label for="skillsprint-blueprint-select"><?php esc_html_e('Blueprint', 'skillsprint'); ?></label>
            <select id="skillsprint-blueprint-select">
                <option value=""><?php esc_html_e('All Blueprints', 'skillsprint'); ?></option>
                <?php
                if (!empty($blueprints)) {
                    foreach ($blueprints as $blueprint) {
                        echo '<option value="' . esc_attr($blueprint->ID) . '">' . esc_html($blueprint->post_title) . '</option>';
                    }
                }
                ?>
            </select>
        </div>
    </div>
    
    <div id="skillsprint-user-progress-container">
        <p><?php esc_html_e('Select a user to view their progress data.', 'skillsprint'); ?></p>
    </div>
</div>