<?php
/**
 * Admin dashboard widget template
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/admin/partials
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="skillsprint-admin-dashboard-widget">
    <div class="skillsprint-stats-container">
        <div class="skillsprint-stat-box">
            <div class="skillsprint-stat-value"><?php echo esc_html($blueprint_count); ?></div>
            <div class="skillsprint-stat-label"><?php esc_html_e('Blueprints', 'skillsprint'); ?></div>
        </div>
        
        <div class="skillsprint-stat-box">
            <div class="skillsprint-stat-value"><?php echo esc_html($learner_count ? $learner_count : '0'); ?></div>
            <div class="skillsprint-stat-label"><?php esc_html_e('Learners', 'skillsprint'); ?></div>
        </div>
        
        <div class="skillsprint-stat-box">
            <div class="skillsprint-stat-value"><?php echo esc_html($completed_blueprint_count ? $completed_blueprint_count : '0'); ?></div>
            <div class="skillsprint-stat-label"><?php esc_html_e('Completed', 'skillsprint'); ?></div>
        </div>
    </div>
    
    <h3><?php esc_html_e('Recent Activity', 'skillsprint'); ?></h3>
    
    <?php if ($recent_activity) : ?>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('User', 'skillsprint'); ?></th>
                    <th><?php esc_html_e('Blueprint', 'skillsprint'); ?></th>
                    <th><?php esc_html_e('Activity', 'skillsprint'); ?></th>
                    <th><?php esc_html_e('Date', 'skillsprint'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_activity as $activity) : ?>
                    <tr>
                        <td>
                            <?php 
                            $user = get_user_by('id', $activity->user_id);
                            if ($user) {
                                echo '<a href="' . esc_url(admin_url('admin.php?page=skillsprint-user-progress&user=' . $activity->user_id)) . '">' . esc_html($user->display_name) . '</a>';
                            } else {
                                echo esc_html__('User #', 'skillsprint') . esc_html($activity->user_id);
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            $blueprint_title = get_the_title($activity->blueprint_id);
                            if ($blueprint_title) {
                                echo '<a href="' . esc_url(get_edit_post_link($activity->blueprint_id)) . '">' . esc_html($blueprint_title) . '</a>';
                            } else {
                                echo esc_html__('Blueprint #', 'skillsprint') . esc_html($activity->blueprint_id);
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            if ($activity->progress_status === 'completed') {
                                echo esc_html__('Completed Day ', 'skillsprint') . esc_html($activity->day_number);
                            } else {
                                echo esc_html__('Started Day ', 'skillsprint') . esc_html($activity->day_number);
                            }
                            ?>
                        </td>
                        <td>
                            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($activity->date_completed))); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p><?php esc_html_e('No recent activity yet.', 'skillsprint'); ?></p>
    <?php endif; ?>
    
    <p class="skillsprint-admin-dashboard-links">
        <a href="<?php echo esc_url(admin_url('admin.php?page=skillsprint')); ?>"><?php esc_html_e('View Full Dashboard', 'skillsprint'); ?></a> |
        <a href="<?php echo esc_url(admin_url('post-new.php?post_type=blueprint')); ?>"><?php esc_html_e('Create New Blueprint', 'skillsprint'); ?></a>
    </p>
</div>