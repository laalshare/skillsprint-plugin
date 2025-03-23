<?php
/**
 * Single blueprint template
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/public/templates
 */

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <?php
        while (have_posts()) :
            the_post();
            
            // Check if the partial file exists before including it
            $template_path = SKILLSPRINT_PLUGIN_DIR . 'public/partials/blueprint-content.php';
            if (file_exists($template_path)) {
                include($template_path);
            } else {
                // Fallback if the partial doesn't exist
                echo '<div class="error">Template file not found: ' . esc_html($template_path) . '</div>';
                // Display basic content
                the_content();
            }
            
            // If comments are open or we have at least one comment, load up the comment template.
            if (comments_open() || get_comments_number()) :
                comments_template();
            endif;
            
        endwhile; // End of the loop.
        ?>
    </main><!-- #main -->
</div><!-- #primary -->

<?php
get_sidebar();
get_footer();