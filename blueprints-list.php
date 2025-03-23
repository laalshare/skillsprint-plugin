<?php
/**
 * Blueprints list template
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/public/partials
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="skillsprint-blueprints">
    <?php if ($show_filters) : ?>
        <div class="skillsprint-blueprints-filters">
            <form method="get" action="<?php echo esc_url(get_post_type_archive_link('blueprint')); ?>">
                <div class="skillsprint-blueprints-filter-row">
                    <div class="skillsprint-blueprints-filter">
                        <label for="blueprint_search"><?php esc_html_e('Search', 'skillsprint'); ?></label>
                        <input type="text" id="blueprint_search" name="blueprint_search" value="<?php echo isset($_GET['blueprint_search']) ? esc_attr($_GET['blueprint_search']) : ''; ?>" placeholder="<?php esc_attr_e('Search blueprints...', 'skillsprint'); ?>">
                    </div>
                    
                    <div class="skillsprint-blueprints-filter">
                        <label for="blueprint_category"><?php esc_html_e('Category', 'skillsprint'); ?></label>
                        <select id="blueprint_category" name="blueprint_category">
                            <option value=""><?php esc_html_e('All Categories', 'skillsprint'); ?></option>
                            <?php
                            if ($categories && !is_wp_error($categories)) {
                                foreach ($categories as $category) {
                                    $selected = isset($_GET['blueprint_category']) && $_GET['blueprint_category'] === $category->slug ? 'selected' : '';
                                    echo '<option value="' . esc_attr($category->slug) . '" ' . $selected . '>' . esc_html($category->name) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="skillsprint-blueprints-filter">
                        <label for="blueprint_difficulty"><?php esc_html_e('Difficulty', 'skillsprint'); ?></label>
                        <select id="blueprint_difficulty" name="blueprint_difficulty">
                            <option value=""><?php esc_html_e('All Difficulties', 'skillsprint'); ?></option>
                            <?php
                            if ($difficulties && !is_wp_error($difficulties)) {
                                foreach ($difficulties as $difficulty) {
                                    $selected = isset($_GET['blueprint_difficulty']) && $_GET['blueprint_difficulty'] === $difficulty->slug ? 'selected' : '';
                                    echo '<option value="' . esc_attr($difficulty->slug) . '" ' . $selected . '>' . esc_html($difficulty->name) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="skillsprint-blueprints-filter-submit">
                        <button type="submit" class="skillsprint-button"><?php esc_html_e('Filter', 'skillsprint'); ?></button>
                        <?php if (isset($_GET['blueprint_search']) || isset($_GET['blueprint_category']) || isset($_GET['blueprint_difficulty'])) : ?>
                            <a href="<?php echo esc_url(get_post_type_archive_link('blueprint')); ?>" class="skillsprint-button outline"><?php esc_html_e('Reset', 'skillsprint'); ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>
    
    <?php if ($blueprints_query->have_posts()) : ?>
        <div class="skillsprint-blueprints-count">
            <?php printf(
                _n(
                    'Showing %d blueprint',
                    'Showing %d blueprints',
                    $blueprints_query->found_posts,
                    'skillsprint'
                ),
                $blueprints_query->found_posts
            ); ?>
        </div>
        
        <?php if ($layout === 'grid') : ?>
            <div class="skillsprint-grid skillsprint-columns-<?php echo esc_attr($columns); ?>">
                <?php
                while ($blueprints_query->have_posts()) :
                    $blueprints_query->the_post();
                    
                    // Get difficulty
                    $difficulty_terms = wp_get_post_terms(get_the_ID(), 'blueprint_difficulty');
                    $difficulty = !empty($difficulty_terms) && !is_wp_error($difficulty_terms) ? $difficulty_terms[0] : null;
                    
                    // Get progress if user is logged in
                    $progress = 0;
                    if (is_user_logged_in()) {
                        $user_id = get_current_user_id();
                        $progress = SkillSprint_DB::get_blueprint_completion_percentage($user_id, get_the_ID());
                    }
                    ?>
                    <div class="skillsprint-card">
                        <?php if (has_post_thumbnail()) : ?>
                            <img src="<?php the_post_thumbnail_url('medium'); ?>" alt="<?php the_title_attribute(); ?>" class="skillsprint-card-img">
                        <?php endif; ?>
                        <div class="skillsprint-card-content">
                            <h3 class="skillsprint-card-title"><?php the_title(); ?></h3>
                            <div class="skillsprint-card-text"><?php the_excerpt(); ?></div>
                            <?php if (is_user_logged_in() && $progress > 0) : ?>
                                <div class="skillsprint-progress-bar-container">
                                    <div class="skillsprint-progress-bar" style="width: <?php echo esc_attr($progress); ?>%"></div>
                                </div>
                                <div class="skillsprint-progress-text">
                                    <span><?php printf(__('Progress: %d%%', 'skillsprint'), $progress); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="skillsprint-card-footer">
                            <?php if ($difficulty) : ?>
                                <span class="skillsprint-badge <?php echo esc_attr($difficulty->slug); ?>"><?php echo esc_html($difficulty->name); ?></span>
                            <?php endif; ?>
                            <a href="<?php the_permalink(); ?>" class="skillsprint-button small">
                                <?php
                                if (is_user_logged_in() && $progress > 0) {
                                    echo $progress < 100 ? esc_html__('Continue', 'skillsprint') : esc_html__('Review', 'skillsprint');
                                } else {
                                    esc_html_e('Start Learning', 'skillsprint');
                                }
                                ?>
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <div class="skillsprint-blueprints-list">
                <?php
                while ($blueprints_query->have_posts()) :
                    $blueprints_query->the_post();
                    
                    // Get difficulty
                    $difficulty_terms = wp_get_post_terms(get_the_ID(), 'blueprint_difficulty');
                    $difficulty = !empty($difficulty_terms) && !is_wp_error($difficulty_terms) ? $difficulty_terms[0] : null;
                    
                    // Get progress if user is logged in
                    $progress = 0;
                    if (is_user_logged_in()) {
                        $user_id = get_current_user_id();
                        $progress = SkillSprint_DB::get_blueprint_completion_percentage($user_id, get_the_ID());
                    }
                    ?>
                    <div class="skillsprint-blueprint-item">
                        <div class="skillsprint-blueprint-item-inner">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="skillsprint-blueprint-item-image">
                                    <img src="<?php the_post_thumbnail_url('thumbnail'); ?>" alt="<?php the_title_attribute(); ?>">
                                </div>
                            <?php endif; ?>
                            <div class="skillsprint-blueprint-item-content">
                                <h3 class="skillsprint-blueprint-item-title"><?php the_title(); ?></h3>
                                <div class="skillsprint-blueprint-item-excerpt"><?php the_excerpt(); ?></div>
                                <div class="skillsprint-blueprint-item-meta">
                                    <?php if ($difficulty) : ?>
                                        <span class="skillsprint-badge <?php echo esc_attr($difficulty->slug); ?>"><?php echo esc_html($difficulty->name); ?></span>
                                    <?php endif; ?>
                                    <?php
                                    // Get author
                                    $author_id = get_post_field('post_author', get_the_ID());
                                    $author_name = get_the_author_meta('display_name', $author_id);
                                    ?>
                                    <span class="skillsprint-blueprint-item-author">
                                        <?php printf(__('By %s', 'skillsprint'), esc_html($author_name)); ?>
                                    </span>
                                </div>
                                <?php if (is_user_logged_in() && $progress > 0) : ?>
                                    <div class="skillsprint-progress-bar-container">
                                        <div class="skillsprint-progress-bar" style="width: <?php echo esc_attr($progress); ?>%"></div>
                                    </div>
                                    <div class="skillsprint-progress-text">
                                        <span><?php printf(__('Progress: %d%%', 'skillsprint'), $progress); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="skillsprint-blueprint-item-actions">
                                <a href="<?php the_permalink(); ?>" class="skillsprint-button">
                                    <?php
                                    if (is_user_logged_in() && $progress > 0) {
                                        echo $progress < 100 ? esc_html__('Continue', 'skillsprint') : esc_html__('Review', 'skillsprint');
                                    } else {
                                        esc_html_e('Start Learning', 'skillsprint');
                                    }
                                    ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
        
        <?php
        $pagination = paginate_links(array(
            'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
            'format' => '?paged=%#%',
            'current' => max(1, get_query_var('paged')),
            'total' => $blueprints_query->max_num_pages,
            'prev_text' => '&laquo; ' . __('Previous', 'skillsprint'),
            'next_text' => __('Next', 'skillsprint') . ' &raquo;',
            'type' => 'list',
        ));
        
        if ($pagination) {
            echo '<nav class="skillsprint-pagination">' . $pagination . '</nav>';
        }
        ?>
    <?php else : ?>
        <div class="skillsprint-no-blueprints">
            <p><?php esc_html_e('No blueprints found matching your criteria. Try adjusting your filters or search terms.', 'skillsprint'); ?></p>
        </div>
    <?php endif; ?>
    
    <?php wp_reset_postdata(); ?>
</div>