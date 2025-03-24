<?php
/**
 * Simple template for archive blueprint
 */
get_header();
?>
<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <header class="page-header">
            <h1 class="page-title">All Blueprints</h1>
        </header>

        <?php if (have_posts()) : ?>
            <div class="skillsprint-grid">
                <?php while (have_posts()) : the_post(); ?>
                    <div class="skillsprint-card">
                        <?php if (has_post_thumbnail()) : ?>
                            <img src="<?php the_post_thumbnail_url('medium'); ?>" alt="<?php the_title_attribute(); ?>" class="skillsprint-card-img">
                        <?php endif; ?>
                        <div class="skillsprint-card-content">
                            <h3 class="skillsprint-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <div class="skillsprint-card-text"><?php the_excerpt(); ?></div>
                        </div>
                        <div class="skillsprint-card-footer">
                            <a href="<?php the_permalink(); ?>" class="skillsprint-button">View Blueprint</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <?php the_posts_pagination(); ?>
        <?php else : ?>
            <p>No blueprints found.</p>
        <?php endif; ?>
    </main>
</div>
<?php
get_sidebar();
get_footer();