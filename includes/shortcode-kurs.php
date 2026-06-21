<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Shortcode [kurs]
|--------------------------------------------------------------------------
*/

function cm_shortcode_kurs($atts)
{
    $atts = shortcode_atts(
        [
            'id' => 0
        ],
        $atts,
        'kurs'
    );

    /*
    |--------------------------------------------------------------------------
    | Kurs-ID bestimmen
    |--------------------------------------------------------------------------
    */

    $course_id = intval($atts['id']);

    // Falls keine ID angegeben wurde und man sich auf einer Kursseite befindet
    if ($course_id === 0) {
        $course_id = get_the_ID();
    }

    if ($course_id <= 0) {
        return '<p>Kein Kurs angegeben.</p>';
    }

    $course = get_post($course_id);

    if (
        !$course ||
        $course->post_type !== 'course' ||
        $course->post_status !== 'publish'
    ) {
        return '<p>Der Kurs wurde nicht gefunden.</p>';
    }

    /*
    |--------------------------------------------------------------------------
    | Teilnehmerzahlen
    |--------------------------------------------------------------------------
    */

    $max_participants = (int) get_post_meta(
        $course_id,
        '_cm_max_participants',
        true
    );

    $current_participants = cm_get_participant_total(
        $course_id
    );

    $free_places = max(
        0,
        $max_participants - $current_participants
    );

    $status = ($free_places > 0)
        ? __('Offen', 'course-manager')
        : __('Ausgebucht', 'course-manager');

    /*
    |--------------------------------------------------------------------------
    | Kursleiter
    |--------------------------------------------------------------------------
    */

    $course_author_id = $course->post_author;
    $course_author = get_userdata($course_author_id);

    ob_start();
    ?>

    <div class="cm-course">

        <h1 class="cm-course-title">
            <?php echo esc_html(get_the_title($course_id)); ?>
        </h1>

        <?php if ($course_author) : ?>

            <p class="cm-course-author">
                <strong>Kursleiter:</strong>
                <?php echo esc_html($course_author->display_name); ?>
            </p>

        <?php endif; ?>

        <div class="cm-course-content">

            <?php
            echo apply_filters(
                'the_content',
                $course->post_content
            );
            ?>

        </div>

        <hr>

        <table class="cm-course-table">

            <tr>
                <th>Maximale Teilnehmerzahl</th>
                <td>
                    <?php echo esc_html($max_participants); ?>
                </td>
            </tr>

            <tr>
                <th>Bereits angemeldet</th>
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
                    <?php echo esc_html($status); ?>
                </td>
            </tr>

        </table>

        <hr>

        <h2>
            <?php _e(
                'Anmeldung',
                'course-manager'
            ); ?>
        </h2>

        <?php
        echo cm_render_registration_form(
            $course_id
        );
        ?>

    </div>

    <?php

    return ob_get_clean();
}

add_shortcode(
    'kurs',
    'cm_shortcode_kurs'
);


/*
|--------------------------------------------------------------------------
| Einzelansicht von Kursen automatisch ersetzen
|--------------------------------------------------------------------------
*/

function cm_replace_single_course_content($content)
{
    if (
        in_the_loop() &&
        is_main_query()
    ) {
        return do_shortcode('[kurs]');
    }

    return $content;
}
