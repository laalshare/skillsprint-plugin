<?php
/**
 * Dashboard template
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/public/templates
 */

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <div class="skillsprint-container">
            <header class="page-header">
                <h1 class="page-title"><?php esc_html_e('Your Learning Dashboard', 'skillsprint'); ?></h1>
            </header>
            
            <?php
            // Check if user is logged in
            if (is_user_logged_in()) {
                $user_id = get_current_user_id();
                $in_progress = SkillSprint_Blueprint::get_user_in_progress_blueprints($user_id, 5);
                $completed = SkillSprint_Blueprint::get_user_completed_blueprints($user_id, 5);
                $recommended = SkillSprint_Blueprint::get_recommended_blueprints($user_id, 3);
                $achievements = SkillSprint_DB::get_user_achievements($user_id);
                $total_points = SkillSprint_DB::get_user_total_points($user_id);
                $streak_info = SkillSprint_DB::get_user_streak($user_id);
                
                include SKILLSPRINT_PLUGIN_DIR . 'public/partials/user-dashboard.php';
            } else {
                include SKILLSPRINT_PLUGIN_DIR . 'public/partials/login-required.php';
            }
            ?>
        </div>
    </main><!-- #main -->
</div><!-- #primary -->

<?php
get_sidebar();
get_footer();