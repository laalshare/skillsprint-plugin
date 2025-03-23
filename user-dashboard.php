<?php
/**
 * User dashboard template
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/public/partials
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="skillsprint-dashboard">
    <div class="skillsprint-dashboard-main">
        <div class="skillsprint-dashboard-welcome">
            <h2><?php printf(__('Welcome, %s!', 'skillsprint'), wp_get_current_user()->display_name); ?></h2>
            <?php if ($streak_info['current_streak'] > 0) : ?>
                <div class="skillsprint-streak-info">
                    <span class="skillsprint-streak-count"><?php echo esc_html($streak_info['current_streak']); ?></span>
                    <span class="skillsprint-streak-label"><?php echo _n('day streak', 'day streak', $streak_info['current_streak'], 'skillsprint'); ?></span>
                    <span class="skillsprint-streak-fire">🔥</span>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($in_progress)) : ?>
            <div class="skillsprint-dashboard-section">
                <h3 class="skillsprint-dashboard-section-title"><?php esc_html_e('Continue Learning', 'skillsprint'); ?></h3>
                <div class="skillsprint-blueprint-cards">
                    <?php foreach ($in_progress as $blueprint) : ?>
                        <div class="skillsprint-card">
                            <?php if (!empty($blueprint['thumbnail'])) : ?>
                                <img src="<?php echo esc_url($blueprint['thumbnail']); ?>" alt="<?php echo esc_attr($blueprint['title']); ?>" class="skillsprint-card-img">
                            <?php endif; ?>
                            <div class="skillsprint-card-content">
                                <h3 class="skillsprint-card-title"><?php echo esc_html($blueprint['title']); ?></h3>
                                <?php if (!empty($blueprint['excerpt'])) : ?>
                                    <p class="skillsprint-card-text"><?php echo esc_html($blueprint['excerpt']); ?></p>
                                <?php endif; ?>
                                <div class="skillsprint-progress-bar-container">
                                    <div class="skillsprint-progress-bar" style="width: <?php echo esc_attr($blueprint['progress']); ?>%"></div>
                                </div>
                                <div class="skillsprint-progress-text">
                                    <span><?php printf(__('Progress: %d%%', 'skillsprint'), $blueprint['progress']); ?></span>
                                    <span><?php printf(__('Last: Day %d', 'skillsprint'), $blueprint['last_day_accessed']); ?></span>
                                </div>
                            </div>
                            <div class="skillsprint-card-footer">
                                <span class="skillsprint-badge <?php echo esc_attr($blueprint['difficulty']['slug']); ?>"><?php echo esc_html($blueprint['difficulty']['name']); ?></span>
                                <a href="<?php echo esc_url($blueprint['permalink']); ?>" class="skillsprint-button small"><?php esc_html_e('Continue', 'skillsprint'); ?></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($in_progress) >= 5) : ?>
                    <a href="#" class="skillsprint-view-all"><?php esc_html_e('View All In-Progress', 'skillsprint'); ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($completed)) : ?>
            <div class="skillsprint-dashboard-section">
                <h3 class="skillsprint-dashboard-section-title"><?php esc_html_e('Completed Blueprints', 'skillsprint'); ?></h3>
                <div class="skillsprint-blueprint-cards">
                    <?php foreach ($completed as $blueprint) : ?>
                        <div class="skillsprint-card">
                            <?php if (!empty($blueprint['thumbnail'])) : ?>
                                <img src="<?php echo esc_url($blueprint['thumbnail']); ?>" alt="<?php echo esc_attr($blueprint['title']); ?>" class="skillsprint-card-img">
                            <?php endif; ?>
                            <div class="skillsprint-card-content">
                                <h3 class="skillsprint-card-title"><?php echo esc_html($blueprint['title']); ?></h3>
                                <?php if (!empty($blueprint['excerpt'])) : ?>
                                    <p class="skillsprint-card-text"><?php echo esc_html($blueprint['excerpt']); ?></p>
                                <?php endif; ?>
                                <p class="skillsprint-completion-date">
                                    <?php 
                                    printf(
                                        __('Completed on: %s', 'skillsprint'),
                                        date_i18n(get_option('date_format'), strtotime($blueprint['completion_date']))
                                    ); 
                                    ?>
                                </p>
                            </div>
                            <div class="skillsprint-card-footer">
                                <span class="skillsprint-badge <?php echo esc_attr($blueprint['difficulty']['slug']); ?>"><?php echo esc_html($blueprint['difficulty']['name']); ?></span>
                                <a href="<?php echo esc_url($blueprint['permalink']); ?>" class="skillsprint-button small"><?php esc_html_e('Review', 'skillsprint'); ?></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($completed) >= 5) : ?>
                    <a href="#" class="skillsprint-view-all"><?php esc_html_e('View All Completed', 'skillsprint'); ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($recommended)) : ?>
            <div class="skillsprint-dashboard-section">
                <h3 class="skillsprint-dashboard-section-title"><?php esc_html_e('Recommended For You', 'skillsprint'); ?></h3>
                <div class="skillsprint-blueprint-cards">
                    <?php foreach ($recommended as $blueprint) : ?>
                        <div class="skillsprint-card">
                            <?php if (!empty($blueprint['thumbnail'])) : ?>
                                <img src="<?php echo esc_url($blueprint['thumbnail']); ?>" alt="<?php echo esc_attr($blueprint['title']); ?>" class="skillsprint-card-img">
                            <?php endif; ?>
                            <div class="skillsprint-card-content">
                                <h3 class="skillsprint-card-title"><?php echo esc_html($blueprint['title']); ?></h3>
                                <?php if (!empty($blueprint['excerpt'])) : ?>
                                    <p class="skillsprint-card-text"><?php echo esc_html($blueprint['excerpt']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="skillsprint-card-footer">
                                <span class="skillsprint-badge <?php echo esc_attr($blueprint['difficulty']['slug']); ?>"><?php echo esc_html($blueprint['difficulty']['name']); ?></span>
                                <a href="<?php echo esc_url($blueprint['permalink']); ?>" class="skillsprint-button small"><?php esc_html_e('Start Learning', 'skillsprint'); ?></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="skillsprint-dashboard-section">
            <h3 class="skillsprint-dashboard-section-title"><?php esc_html_e('Explore More Blueprints', 'skillsprint'); ?></h3>
            <div class="skillsprint-browse-categories">
                <p><?php esc_html_e('Browse blueprints by category:', 'skillsprint'); ?></p>
                <div class="skillsprint-categories-list">
                    <?php
                    $categories = get_terms(array(
                        'taxonomy' => 'blueprint_category',
                        'hide_empty' => true,
                    ));
                    
                    if ($categories && !is_wp_error($categories)) :
                        foreach ($categories as $category) :
                            ?>
                            <a href="<?php echo esc_url(get_term_link($category)); ?>" class="skillsprint-category-link">
                                <?php echo esc_html($category->name); ?>
                            </a>
                            <?php
                        endforeach;
                    endif;
                    ?>
                </div>
            </div>
            <div class="skillsprint-browse-difficulties">
                <p><?php esc_html_e('Browse blueprints by difficulty:', 'skillsprint'); ?></p>
                <div class="skillsprint-difficulties-list">
                    <?php
                    $difficulties = get_terms(array(
                        'taxonomy' => 'blueprint_difficulty',
                        'hide_empty' => true,
                    ));
                    
                    if ($difficulties && !is_wp_error($difficulties)) :
                        foreach ($difficulties as $difficulty) :
                            ?>
                            <a href="<?php echo esc_url(get_term_link($difficulty)); ?>" class="skillsprint-badge <?php echo esc_attr($difficulty->slug); ?>">
                                <?php echo esc_html($difficulty->name); ?>
                            </a>
                            <?php
                        endforeach;
                    endif;
                    ?>
                </div>
            </div>
            <a href="<?php echo esc_url(get_post_type_archive_link('blueprint')); ?>" class="skillsprint-button"><?php esc_html_e('View All Blueprints', 'skillsprint'); ?></a>
        </div>
    </div>
    
    <div class="skillsprint-dashboard-sidebar">
        <div class="skillsprint-dashboard-widget">
            <h3 class="skillsprint-dashboard-widget-title"><?php esc_html_e('Your Stats', 'skillsprint'); ?></h3>
            <div class="skillsprint-stats-grid">
                <div class="skillsprint-stat-item">
                    <div class="skillsprint-stat-value" data-stat="points"><?php echo esc_html($total_points); ?></div>
                    <div class="skillsprint-stat-label"><?php esc_html_e('Points', 'skillsprint'); ?></div>
                </div>
                <div class="skillsprint-stat-item">
                    <div class="skillsprint-stat-value" data-stat="streak"><?php echo esc_html($streak_info['current_streak']); ?></div>
                    <div class="skillsprint-stat-label"><?php esc_html_e('Day Streak', 'skillsprint'); ?></div>
                </div>
                <div class="skillsprint-stat-item">
                    <div class="skillsprint-stat-value" data-stat="blueprints_completed"><?php echo esc_html(count($completed)); ?></div>
                    <div class="skillsprint-stat-label"><?php esc_html_e('Completed', 'skillsprint'); ?></div>
                </div>
                <div class="skillsprint-stat-item">
                    <div class="skillsprint-stat-value" data-stat="blueprints_started"><?php echo esc_html(count($in_progress)); ?></div>
                    <div class="skillsprint-stat-label"><?php esc_html_e('In Progress', 'skillsprint'); ?></div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($achievements)) : ?>
            <div class="skillsprint-dashboard-widget">
                <h3 class="skillsprint-dashboard-widget-title"><?php esc_html_e('Recent Achievements', 'skillsprint'); ?></h3>
                <div class="skillsprint-achievements-list">
                    <?php
                    $recent_achievements = array_slice($achievements, 0, 5);
                    foreach ($recent_achievements as $achievement) :
                        ?>
                        <div class="skillsprint-achievement">
                            <div class="skillsprint-achievement-icon">
                                <i class="<?php echo esc_attr($achievement['icon']); ?>"></i>
                            </div>
                            <div class="skillsprint-achievement-content">
                                <div class="skillsprint-achievement-title"><?php echo esc_html($achievement['title']); ?></div>
                                <div class="skillsprint-achievement-description"><?php echo esc_html($achievement['description']); ?></div>
                                <div class="skillsprint-achievement-date">
                                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($achievement['date_earned']))); ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    endforeach;
                    ?>
                </div>
                <?php if (count($achievements) > 5) : ?>
                    <a href="#" class="skillsprint-view-all"><?php esc_html_e('View All Achievements', 'skillsprint'); ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="skillsprint-dashboard-widget">
            <h3 class="skillsprint-dashboard-widget-title"><?php esc_html_e('Leaderboard', 'skillsprint'); ?></h3>
            <?php
            // Get leaderboard
            $leaderboard = SkillSprint_DB::get_leaderboard(5);
            
            if (!empty($leaderboard)) :
                ?>
                <div class="skillsprint-leaderboard">
                    <table class="skillsprint-leaderboard-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Rank', 'skillsprint'); ?></th>
                                <th><?php esc_html_e('Learner', 'skillsprint'); ?></th>
                                <th><?php esc_html_e('Points', 'skillsprint'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leaderboard as $index => $learner) : ?>
                                <tr <?php echo $learner['user_id'] == get_current_user_id() ? 'class="current-user"' : ''; ?>>
                                    <td class="skillsprint-leaderboard-rank"><?php echo esc_html($index + 1); ?></td>
                                    <td class="skillsprint-leaderboard-user">
                                        <img src="<?php echo esc_url($learner['user_avatar']); ?>" alt="" class="skillsprint-leaderboard-avatar">
                                        <?php echo esc_html($learner['user_name']); ?>
                                        <?php if ($learner['current_streak'] > 0) : ?>
                                            <span class="skillsprint-leaderboard-streak" title="<?php printf(esc_attr__('%d day streak', 'skillsprint'), $learner['current_streak']); ?>">
                                                🔥 <?php echo esc_html($learner['current_streak']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html($learner['total_points']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <a href="#" class="skillsprint-view-all"><?php esc_html_e('View Full Leaderboard', 'skillsprint'); ?></a>
            <?php else : ?>
                <p><?php esc_html_e('No leaderboard data available yet.', 'skillsprint'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>