<?php
/**
 * Leaderboard template
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/public/partials
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="skillsprint-leaderboard">
    <h3 class="skillsprint-leaderboard-title"><?php esc_html_e('Learner Leaderboard', 'skillsprint'); ?></h3>
    
    <?php if (!empty($leaderboard)) : ?>
        <table class="skillsprint-leaderboard-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Rank', 'skillsprint'); ?></th>
                    <?php if ($show_avatars) : ?>
                        <th><?php esc_html_e('Learner', 'skillsprint'); ?></th>
                    <?php else : ?>
                        <th><?php esc_html_e('Name', 'skillsprint'); ?></th>
                    <?php endif; ?>
                    <th><?php esc_html_e('Points', 'skillsprint'); ?></th>
                    <?php if ($show_streaks) : ?>
                        <th><?php esc_html_e('Streak', 'skillsprint'); ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leaderboard as $index => $learner) : ?>
                    <tr <?php echo is_user_logged_in() && $learner['user_id'] == get_current_user_id() ? 'class="current-user"' : ''; ?>>
                        <td class="skillsprint-leaderboard-rank"><?php echo esc_html($index + 1); ?></td>
                        <td class="skillsprint-leaderboard-user">
                            <?php if ($show_avatars) : ?>
                                <img src="<?php echo esc_url($learner['user_avatar']); ?>" alt="" class="skillsprint-leaderboard-avatar">
                            <?php endif; ?>
                            <?php echo esc_html($learner['user_name']); ?>
                        </td>
                        <td><?php echo esc_html($learner['total_points']); ?></td>
                        <?php if ($show_streaks) : ?>
                            <td>
                                <?php if ($learner['current_streak'] > 0) : ?>
                                    <span class="skillsprint-leaderboard-streak" title="<?php printf(esc_attr__('%d day streak', 'skillsprint'), $learner['current_streak']); ?>">
                                        <?php echo esc_html($learner['current_streak']); ?> 🔥
                                    </span>
                                <?php else : ?>
                                    -
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p class="skillsprint-no-data"><?php esc_html_e('No leaderboard data available yet. Start learning to earn points and appear on the leaderboard!', 'skillsprint'); ?></p>
    <?php endif; ?>
</div>