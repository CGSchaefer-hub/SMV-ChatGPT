<?php if (!defined('ABSPATH')) exit; ?>

<div class="cm-course-list">

    <?php foreach ($courses as $course) : ?>

        <?php
        $course_id = $course->ID;

        $max = (int) get_post_meta($course_id, '_cm_max_participants', true);
        $current = cm_get_participant_total($course_id);
        $free = max(0, $max - $current);
        ?>

        <div class="cm-course-card">

            <h2>
                <a href="<?php echo get_permalink($course_id); ?>">
                    <?php echo esc_html(get_the_title($course_id)); ?>
                </a>
            </h2>

            <p><?php echo wp_trim_words($course->post_content, 25); ?></p>

            <table class="cm-course-table">
                <tr><th>Max</th><td><?php echo $max; ?></td></tr>
                <tr><th>Belegt</th><td><?php echo $current; ?></td></tr>
                <tr><th>Frei</th><td><?php echo $free; ?></td></tr>
            </table>

        </div>

    <?php endforeach; ?>

</div>
