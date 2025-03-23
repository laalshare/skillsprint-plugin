<?php
/**
 * Admin dashboard page.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/admin/partials
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get general stats
global $wpdb;
$progress_table = $wpdb->prefix . 'skillsprint_progress';
$quiz_table = $wpdb->prefix . 'skillsprint_quiz_responses';
$points_table = $wpdb->prefix . 'skillsprint_user_points';
$achievements_table = $wpdb->prefix . 'skillsprint_achievements';

// Count blueprints
$blueprint_count = wp_count_posts('blueprint')->publish;

// Count learners
$learner_count = $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM $progress_table");

// Count completed days
$completed_days = $wpdb->get_var("SELECT COUNT(*) FROM $progress_table WHERE progress_status = 'completed'");

// Count quizzes taken
$quizzes_taken = $wpdb->get_var("SELECT COUNT(DISTINCT CONCAT(blueprint_id, '_', quiz_id, '_', user_id)) FROM $quiz_table");

// Count total points awarded
$total_points = $wpdb->get_var("SELECT SUM(points) FROM $points_table");

// Count total achievements earned
$total_achievements = $wpdb->get_var("SELECT COUNT(*) FROM $achievements_table");

// Get recent activity
$recent_activity = $wpdb->get_results(
    "SELECT p.user_id, p.blueprint_id, p.day_number, p.progress_status, p.date_completed
    FROM $progress_table as p
    WHERE p.date_completed IS NOT NULL
    ORDER BY p.date_completed DESC
    LIMIT 10"
);

// Get top performers
$top_performers = $wpdb->get_results(
    "SELECT user_id, SUM(points) as total_points
    FROM $points_table
    GROUP BY user_id
    ORDER BY total_points DESC
    LIMIT 5"
);

// Get popular blueprints
$popular_blueprints = $wpdb->get_results(
    "SELECT blueprint_id, COUNT(DISTINCT user_id) as learner_count
    FROM $progress_table
    GROUP BY blueprint_id
    ORDER BY learner_count DESC
    LIMIT 5"
);
?>

<div class="wrap skillsprint-admin-dashboard">
    <h1>SkillSprint Dashboard</h1>
    
    <div class="skillsprint-admin-welcome">
        <h2>Welcome to SkillSprint 7-Day Learning Platform</h2>
        <p>Manage your 7-day learning blueprints, track learner progress, and view analytics all in one place.</p>
    </div>
    
    <div class="skillsprint-admin-stats">
        <div class="skillsprint-admin-stats-card">
            <div class="skillsprint-admin-stats-value"><?php echo esc_html($blueprint_count); ?></div>
            <div class="skillsprint-admin-stats-label">Blueprints</div>
            <a href="<?php echo esc_url(admin_url('edit.php?post_type=blueprint')); ?>" class="skillsprint-admin-stats-link">View all</a>
        </div>
        
        <div class="skillsprint-admin-stats-card">
            <div class="skillsprint-admin-stats-value"><?php echo esc_html($learner_count ? $learner_count : '0'); ?></div>
            <div class="skillsprint-admin-stats-label">Learners</div>
            <a href="<?php echo esc_url(admin_url('admin.php?page=skillsprint-user-progress')); ?>" class="skillsprint-admin-stats-link">View progress</a>
        </div>
        
        <div class="skillsprint-admin-stats-card">
            <div class="skillsprint-admin-stats-value"><?php echo esc_html($completed_days ? $completed_days : '0'); ?></div>
            <div class="skillsprint-admin-stats-label">Days Completed</div>
        </div>
        
        <div class="skillsprint-admin-stats-card">
            <div class="skillsprint-admin-stats-value"><?php echo esc_html($quizzes_taken ? $quizzes_taken : '0'); ?></div>
            <div class="skillsprint-admin-stats-label">Quizzes Taken</div>
        </div>
    </div>
    
    <div class="skillsprint-admin-columns">
        <div class="skillsprint-admin-column">
            <div class="skillsprint-admin-card">
                <h3>Recent Activity</h3>
                
                <?php if ($recent_activity) : ?>
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Blueprint</th>
                                <th>Action</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_activity as $activity) : ?>
                                <tr>
                                    <td>
                                        <?php 
                                        $user = get_user_by('id', $activity->user_id);
                                        echo $user ? esc_html($user->display_name) : 'User #' . esc_html($activity->user_id);
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $blueprint_title = get_the_title($activity->blueprint_id);
                                        echo $blueprint_title ? esc_html($blueprint_title) : 'Blueprint #' . esc_html($activity->blueprint_id);
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($activity->progress_status === 'completed') {
                                            echo 'Completed Day ' . esc_html($activity->day_number);
                                        } else {
                                            echo 'Started Day ' . esc_html($activity->day_number);
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
                    <p>No recent activity yet.</p>
                <?php endif; ?>
            </div>
            
            <div class="skillsprint-admin-card">
                <h3>Top Performers</h3>
                
                <?php if ($top_performers) : ?>
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_performers as $performer) : ?>
                                <tr>
                                    <td>
                                        <?php 
                                        $user = get_user_by('id', $performer->user_id);
                                        echo $user ? esc_html($user->display_name) : 'User #' . esc_html($performer->user_id);
                                        ?>
                                    </td>
                                    <td><?php echo esc_html($performer->total_points); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p>No performance data yet.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="skillsprint-admin-column">
            <div class="skillsprint-admin-card">
                <h3>Popular Blueprints</h3>
                
                <?php if ($popular_blueprints) : ?>
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th>Blueprint</th>
                                <th>Learners</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($popular_blueprints as $blueprint) : ?>
                                <tr>
                                    <td>
                                        <?php 
                                        $blueprint_title = get_the_title($blueprint->blueprint_id);
                                        if ($blueprint_title) {
                                            echo '<a href="' . esc_url(get_edit_post_link($blueprint->blueprint_id)) . '">' . esc_html($blueprint_title) . '</a>';
                                        } else {
                                            echo 'Blueprint #' . esc_html($blueprint->blueprint_id);
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo esc_html($blueprint->learner_count); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p>No blueprint engagement data yet.</p>
                <?php endif; ?>
            </div>
            
            <div class="skillsprint-admin-card">
                <h3>Quick Actions</h3>
                
                <div class="skillsprint-admin-actions">
                    <a href="<?php echo esc_url(admin_url('post-new.php?post_type=blueprint')); ?>" class="button button-primary">Create New Blueprint</a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=skillsprint-user-progress')); ?>" class="button">View User Progress</a>
                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=blueprint')); ?>" class="button">Manage Blueprints</a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=skillsprint-settings')); ?>" class="button">Plugin Settings</a>
                </div>
                
                <h4>Plugin Documentation</h4>
                <ul>
                    <li><a href="#" target="_blank">How to Create a Blueprint</a></li>
                    <li><a href="#" target="_blank">Setting Up Quizzes</a></li>
                    <li><a href="#" target="_blank">Gamification Best Practices</a></li>
                    <li><a href="#" target="_blank">Full Plugin Documentation</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.skillsprint-admin-dashboard {
    max-width: 1200px;
}

.skillsprint-admin-welcome {
    background-color: #fff;
    border-left: 4px solid #2271b1;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
    margin: 20px 0;
    padding: 1px 12px;
}

.skillsprint-admin-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 20px;
}

.skillsprint-admin-stats-card {
    background-color: #fff;
    border-radius: 3px;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
    flex: 1;
    min-width: 200px;
    padding: 20px;
    position: relative;
    text-align: center;
}

.skillsprint-admin-stats-value {
    color: #2271b1;
    font-size: 36px;
    font-weight: 600;
    margin-bottom: 5px;
}

.skillsprint-admin-stats-label {
    color: #646970;
    font-size: 14px;
}

.skillsprint-admin-stats-link {
    bottom: 10px;
    font-size: 12px;
    position: absolute;
    right: 10px;
}

.skillsprint-admin-columns {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.skillsprint-admin-column {
    flex: 1;
    min-width: 300px;
}

.skillsprint-admin-card {
    background-color: #fff;
    border-radius: 3px;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
    margin-bottom: 20px;
    padding: 20px;
}

.skillsprint-admin-card h3 {
    border-bottom: 1px solid #c3c4c7;
    margin-top: 0;
    padding-bottom: 10px;
}

.skillsprint-admin-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
}
</style>