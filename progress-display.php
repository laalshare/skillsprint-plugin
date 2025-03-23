<?php
/**
 * Progress display template
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/public/partials
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get blueprint data
$blueprint_id = isset($blueprint_id) ? $blueprint_id : get_the_ID();
$user_id = isset($user_id) ? $user_id : get_current_user_id();

// Get user progress data
$user_progress = isset($user_progress) ? $user_progress : SkillSprint_DB::get_user_blueprint_progress($user_id, $blueprint_id);
$total_percentage = isset($total_percentage) ? $total_percentage : SkillSprint_DB::get_blueprint_completion_percentage($user_id, $blueprint_id);

// Get days data
$days_data = isset($days_data) ? $days_data : SkillSprint_DB::get_blueprint_days_data($blueprint_id);

// Get current day (if specified)
$current_day = isset($current_day) ? $current_day : (isset($_GET['day']) ? intval($_GET['day']) : 1);

// Get settings
$settings = get_option('skillsprint_settings');
$days_free_access = isset($settings['days_free_access']) ? intval($settings['days_free_access']) : 2;
?>

<div class="skillsprint-progress-display">
    <div class="skillsprint-progress-header">
        <h3 class="skillsprint-progress-title"><?php esc_html_e('Your Progress', 'skillsprint'); ?></h3>
        <div class="skillsprint-progress-percentage">
            <div class="skillsprint-progress-circle" data-percentage="<?php echo esc_attr($total_percentage); ?>">
                <svg viewBox="0 0 36 36" class="skillsprint-progress-svg">
                    <path class="skillsprint-progress-circle-bg"
                        d="M18 2.0845
                        a 15.9155 15.9155 0 0 1 0 31.831
                        a 15.9155 15.9155 0 0 1 0 -31.831"
                    />
                    <path class="skillsprint-progress-circle-fill"
                        stroke-dasharray="<?php echo esc_attr($total_percentage); ?>, 100"
                        d="M18 2.0845
                        a 15.9155 15.9155 0 0 1 0 31.831
                        a 15.9155 15.9155 0 0 1 0 -31.831"
                    />
                    <text x="18" y="20.35" class="skillsprint-progress-circle-text"><?php echo esc_html($total_percentage); ?>%</text>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="skillsprint-days-progress">
        <div class="skillsprint-days-tabs">
            <?php
            $completed_up_to = 0;
            
            foreach ($user_progress as $day => $day_progress) {
                if ($day_progress['completed']) {
                    $completed_up_to = $day;
                } else {
                    break;
                }
            }
            
            for ($day = 1; $day <= 7; $day++) :
                $day_status = isset($user_progress[$day]) ? $user_progress[$day]['status'] : 'locked';
                $day_completed = isset($user_progress[$day]) && $user_progress[$day]['completed'];
                $is_current = $day === $current_day;
                
                // Determine if day should be locked
                $locked = false;
                if (!is_user_logged_in() && $day > $days_free_access) {
                    $locked = true;
                    $day_status = 'locked';
                } elseif (is_user_logged_in() && $day > 1 && $day > $completed_up_to + 1) {
                    $locked = true;
                    $day_status = 'locked';
                }
                
                $tab_url = $locked ? '#' : add_query_arg('day', $day, get_permalink($blueprint_id));
                $tab_class = 'skillsprint-day-tab';
                $tab_class .= $is_current ? ' current' : '';
                $tab_class .= $day_completed ? ' completed' : '';
                $tab_class .= $locked ? ' locked' : '';
                $tab_class .= ' ' . $day_status;
                
                ?>
                <a href="<?php echo esc_url($tab_url); ?>" class="<?php echo esc_attr($tab_class); ?>" data-day="<?php echo esc_attr($day); ?>">
                    <span class="skillsprint-day-number"><?php echo esc_html($day); ?></span>
                    <span class="skillsprint-day-label"><?php printf(esc_html__('Day %d', 'skillsprint'), $day); ?></span>
                    <span class="skillsprint-day-status">
                        <?php if ($day_completed) : ?>
                            <span class="dashicons dashicons-yes"></span>
                        <?php elseif ($locked) : ?>
                            <span class="dashicons dashicons-lock"></span>
                        <?php endif; ?>
                    </span>
                </a>
            <?php endfor; ?>
        </div>
        
        <?php if (isset($days_data[$current_day - 1])) : 
            $day_data = $days_data[$current_day - 1];
            $day_title = isset($day_data['title']) ? $day_data['title'] : sprintf(__('Day %d', 'skillsprint'), $current_day);
            $day_objectives = isset($day_data['objectives']) ? $day_data['objectives'] : array();
            $day_time_estimate = isset($day_data['time_estimate']) ? $day_data['time_estimate'] : '';
            $day_quiz_id = isset($day_data['quiz_id']) ? $day_data['quiz_id'] : '';
            
            // Get current day progress
            $day_progress = isset($user_progress[$current_day]) ? $user_progress[$current_day] : array(
                'status' => 'not_started',
                'completed' => false,
                'quiz_status' => 'not_started',
                'quiz_score' => 0,
                'quiz_attempts' => 0,
                'date_started' => '',
                'date_completed' => ''
            );
            
            // Check if quiz exists for this day
            $has_quiz = !empty($day_quiz_id);
            $quiz_completed = $has_quiz && isset($day_progress['quiz_status']) && $day_progress['quiz_status'] === 'passed';
        ?>
            <div class="skillsprint-day-info">
                <div class="skillsprint-day-header">
                    <h4 class="skillsprint-day-title"><?php echo esc_html($day_title); ?></h4>
                    <?php if ($day_completed) : ?>
                        <div class="skillsprint-day-completed-badge">
                            <span class="dashicons dashicons-yes"></span>
                            <?php esc_html_e('Completed', 'skillsprint'); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($day_time_estimate)) : ?>
                    <div class="skillsprint-day-time-estimate">
                        <span class="dashicons dashicons-clock"></span>
                        <?php printf(esc_html__('Estimated time: %s', 'skillsprint'), esc_html($day_time_estimate)); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($day_objectives)) : ?>
                    <div class="skillsprint-day-objectives">
                        <h5><?php esc_html_e('Learning Objectives', 'skillsprint'); ?></h5>
                        <ul>
                            <?php foreach ($day_objectives as $objective) : ?>
                                <li><?php echo wp_kses_post($objective); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if (is_user_logged_in()) : ?>
                    <div class="skillsprint-day-progress-details">
                        <?php if (!empty($day_progress['date_started'])) : ?>
                            <div class="skillsprint-day-started">
                                <span class="skillsprint-progress-label"><?php esc_html_e('Started:', 'skillsprint'); ?></span>
                                <span class="skillsprint-progress-value"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($day_progress['date_started']))); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($day_progress['date_completed'])) : ?>
                            <div class="skillsprint-day-finished">
                                <span class="skillsprint-progress-label"><?php esc_html_e('Completed:', 'skillsprint'); ?></span>
                                <span class="skillsprint-progress-value"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($day_progress['date_completed']))); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($has_quiz) : ?>
                            <div class="skillsprint-day-quiz-status">
                                <span class="skillsprint-progress-label"><?php esc_html_e('Quiz:', 'skillsprint'); ?></span>
                                <span class="skillsprint-progress-value">
                                    <?php 
                                    switch ($day_progress['quiz_status']) {
                                        case 'passed':
                                            printf(
                                                esc_html__('Passed (%d%%)', 'skillsprint'),
                                                $day_progress['quiz_score']
                                            );
                                            break;
                                        case 'failed':
                                            if (isset($day_progress['quiz_attempts']) && $day_progress['quiz_attempts'] > 0) {
                                                printf(
                                                    esc_html__('Attempts: %d - Last Score: %d%%', 'skillsprint'),
                                                    $day_progress['quiz_attempts'],
                                                    $day_progress['quiz_score']
                                                );
                                            } else {
                                                esc_html_e('Not completed', 'skillsprint');
                                            }
                                            break;
                                        case 'in_progress':
                                            esc_html_e('In progress', 'skillsprint');
                                            break;
                                        default:
                                            esc_html_e('Not started', 'skillsprint');
                                            break;
                                    }
                                    ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!$day_completed) : ?>
                    <div class="skillsprint-day-actions">
                        <?php if ($current_day > 1 && (!is_user_logged_in() || $completed_up_to < $current_day - 1)) : ?>
                            <?php if (!is_user_logged_in() && $current_day > $days_free_access) : ?>
                                <div class="skillsprint-day-locked-message">
                                    <div class="skillsprint-alert warning">
                                        <span class="dashicons dashicons-lock"></span>
                                        <p><?php esc_html_e('Please log in to access this content.', 'skillsprint'); ?></p>
                                        <button type="button" class="skillsprint-button skillsprint-login-button"><?php esc_html_e('Log In', 'skillsprint'); ?></button>
                                    </div>
                                </div>
                            <?php else : ?>
                                <div class="skillsprint-day-locked-message">
                                    <div class="skillsprint-alert warning">
                                        <span class="dashicons dashicons-lock"></span>
                                        <p><?php esc_html_e('You need to complete the previous days before accessing this content.', 'skillsprint'); ?></p>
                                        <a href="<?php echo esc_url(add_query_arg('day', $completed_up_to + 1, get_permalink($blueprint_id))); ?>" class="skillsprint-button">
                                            <?php printf(esc_html__('Go to Day %d', 'skillsprint'), $completed_up_to + 1); ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else : ?>
                            <?php if ($has_quiz && !$quiz_completed) : ?>
                                <div class="skillsprint-day-reminder">
                                    <div class="skillsprint-alert info">
                                        <span class="dashicons dashicons-info"></span>
                                        <p><?php esc_html_e('Remember to complete the quiz at the end of this day to mark it as completed.', 'skillsprint'); ?></p>
                                    </div>
                                </div>
                            <?php elseif (!$has_quiz && is_user_logged_in()) : ?>
                                <button type="button" class="skillsprint-button skillsprint-mark-completed" data-blueprint-id="<?php echo esc_attr($blueprint_id); ?>" data-day="<?php echo esc_attr($current_day); ?>">
                                    <?php esc_html_e('Mark Day as Completed', 'skillsprint'); ?>
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="skillsprint-day-navigation">
        <?php if ($current_day > 1) : 
            $prev_day = $current_day - 1;
            $prev_day_url = add_query_arg('day', $prev_day, get_permalink($blueprint_id));
        ?>
            <a href="<?php echo esc_url($prev_day_url); ?>" class="skillsprint-day-nav-link skillsprint-prev-day">
                <span class="dashicons dashicons-arrow-left-alt2"></span>
                <?php printf(esc_html__('Day %d', 'skillsprint'), $prev_day); ?>
            </a>
        <?php endif; ?>
        
        <?php if ($current_day < 7) : 
            $next_day = $current_day + 1;
            
            // Determine if next day is accessible
            $next_day_accessible = true;
            if (!is_user_logged_in() && $next_day > $days_free_access) {
                $next_day_accessible = false;
            } elseif (is_user_logged_in() && $next_day > $completed_up_to + 1) {
                $next_day_accessible = false;
            }
            
            $next_day_url = $next_day_accessible 
                ? add_query_arg('day', $next_day, get_permalink($blueprint_id))
                : '#';
            
            $next_day_class = 'skillsprint-day-nav-link skillsprint-next-day';
            $next_day_class .= !$next_day_accessible ? ' disabled' : '';
        ?>
            <a href="<?php echo esc_url($next_day_url); ?>" class="<?php echo esc_attr($next_day_class); ?>" <?php echo !$next_day_accessible ? 'data-locked="true"' : ''; ?>>
                <?php printf(esc_html__('Day %d', 'skillsprint'), $next_day); ?>
                <span class="dashicons dashicons-arrow-right-alt2"></span>
                <?php if (!$next_day_accessible) : ?>
                    <span class="dashicons dashicons-lock"></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (!is_user_logged_in()) : ?>
    <?php include SKILLSPRINT_PLUGIN_DIR . 'public/partials/login-modal.php'; ?>
    <?php include SKILLSPRINT_PLUGIN_DIR . 'public/partials/register-modal.php'; ?>
<?php endif; ?>