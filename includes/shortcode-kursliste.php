<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Shortcode [kursliste]
|--------------------------------------------------------------------------
*/

function cm_shortcode_kursliste($atts)
{
    $atts = shortcode_atts(
        [
            'orderby' => 'title',
            'order' => 'ASC'
        ],
        $atts,
        'kursliste'
    );

    $courses = get_posts([
        'post_type'      => 'course',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => sanitize_text_field($atts['orderby']),
        'order'          => sanitize_text_field($atts['order'])
    ]);

    if (empty($courses)) {
        return '<p>Zurzeit sind keine Kurse verfügbar.</p>';
    }

    ob_start();
    ?>

    <div class="cm-course-list">

        <?php foreach ($courses as $course) : ?>

            <?php

            $course_id = $course->ID;

            $max_participants = (int) get_post_meta(
                $course_id,
                '_cm_max_participants',
                true
            );

            $current_participants = cm_get_participant_total($course_id);

            $free_places = max(
                0,
                $max_participants - $current_participants
            );

            $course_status = $free_places > 0
                ? __('Offen', 'course-manager')
                : __('Ausgebucht', 'course-manager');

            ?>

            <div class="cm-course-card">

                <h2>
                    <a href="<?php echo esc_url(get_permalink($course_id)); ?>">
                        <?php echo esc_html(get_the_title($course_id)); ?>
                    </a>
                </h2>

                <div class="cm-course-description">
                    <?php
                    echo wp_kses_post(
                        wp_trim_words(
                            $course->post_content,
                            40
                        )
                    );
                    ?>
                </div>

                <table class="cm-course-table">

                    <tr>
                        <th>Maximale Plätze</th>
                        <td>
                            <?php echo esc_html($max_participants); ?>
                        </td>
                    </tr>

                    <tr>
                        <th>Angemeldet</th>
                        <td>
                            <?php echo esc_html($current_participants); ?>
                        </td>
                    </tr>

                    <tr>
                        <th>Freie Plätze</th>
                        <td>
                            <?php echo esc_html($free_places); ?>
                        </td>
                    </tr>

                    <tr>
                        <th>Status</th>
                        <td>
                            <?php echo esc_html($course_status); ?>
                        </td>
                    </tr>

                </table>

                <p class="cm-course-button">

                    <?php if ($free_places > 0) : ?>

                        <?php if ($free_places > 0) : ?>

    <a class="button cm-toggle-form" data-id="<?php echo $course_id; ?>">
        Jetzt anmelden
    </a>

    <div id="cm-form-<?php echo $course_id; ?>" style="display:none;">
        <?php echo cm_render_inline_registration_form($course_id); ?>
    </div>

<?php else : ?>

    <span class="cm-course-full">Ausgebucht</span>

<?php endif; ?>

                    <?php else : ?>

                        <span class="cm-course-full">
                            Kurs ausgebucht
                        </span>

                    <?php endif; ?>

                </p>

            </div>

        <?php endforeach; ?>

    </div>

    <?php

    return ob_get_clean();
}

add_shortcode(
    'kursliste',
    'cm_shortcode_kursliste'
);
