<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Teilnehmer löschen
|--------------------------------------------------------------------------
*/

function cm_handle_delete_participant()
{
    if (
        !isset($_GET['cm_delete_participant']) ||
        !isset($_GET['_wpnonce'])
    ) {
        return;
    }

    $participant_id = intval($_GET['cm_delete_participant']);

    if (
        !wp_verify_nonce(
            $_GET['_wpnonce'],
            'cm_delete_participant_' . $participant_id
        )
    ) {
        wp_die(__('Ungültige Anfrage.', 'course-manager'));
    }

    $participant = cm_get_participant($participant_id);

    if (!$participant) {
        return;
    }

    if (!cm_user_can_manage_course($participant->course_id)) {
        wp_die(__('Keine Berechtigung.', 'course-manager'));
    }

    cm_delete_participant($participant_id);

    wp_safe_redirect(remove_query_arg([
        'cm_delete_participant',
        '_wpnonce'
    ]));

    exit;
}

add_action('init', 'cm_handle_delete_participant');


/*
|--------------------------------------------------------------------------
| Shortcode [meine_kurse]
|--------------------------------------------------------------------------
*/

function cm_shortcode_meine_kurse()
{
    if (
        !is_user_logged_in()
    ) {
        return '<p>Bitte zuerst anmelden.</p>';
    }

    if (
        !cm_user_is_admin() &&
        !cm_user_is_kursleiter()
    ) {
        return '<p>Du bist kein Kursleiter.</p>';
    }

    $args = [
        'post_type' => 'course',
        'post_status' => ['publish', 'draft'],
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ];

    if (!cm_user_is_admin()) {
        $args['author'] = get_current_user_id();
    }

    $courses = get_posts($args);

    ob_start();

    ?>

    <div class="cm-dashboard">

        <h2>Meine Kurse</h2>

        <?php if (empty($courses)) : ?>

            <p>Es wurden keine Kurse gefunden.</p>

        <?php else : ?>

            <?php foreach ($courses as $course) : ?>

                <?php

                $course_id = $course->ID;

                $participants = cm_get_participants(
                    $course_id
                );

                $max_participants = (int)get_post_meta(
                    $course_id,
                    '_cm_max_participants',
                    true
                );

                ?>

                <div class="cm-course-dashboard">

                    <h3>
                        <?php echo esc_html(
                            get_the_title($course_id)
                        ); ?>
                    </h3>

                    <p>

                        <strong>Teilnehmer:</strong>

                        <?php echo count($participants); ?>

                        /

                        <?php echo $max_participants; ?>

                    </p>

                    <p>

                        <a
                            class="button"
                            href="<?php echo esc_url(
                                get_permalink($course_id)
                            ); ?>"
                        >
                            Kurs anzeigen
                        </a>

                        <a
                            class="button"
                            href="<?php echo esc_url(
                                admin_url(
                                    'post.php?post=' .
                                    $course_id .
                                    '&action=edit'
                                )
                            ); ?>"
                        >
                            Bearbeiten
                        </a>

                    </p>

                    <h4>Teilnehmerliste</h4>

                    <?php if (empty($participants)) : ?>

                        <p>Noch keine Anmeldungen vorhanden.</p>

                    <?php else : ?>

                        <table class="cm-participant-table">

                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>E-Mail</th>
                                    <th>Telefon</th>
                                    <th>Datum</th>
                                    <th>Aktion</th>
                                </tr>
                            </thead>

                            <tbody>

                            <?php foreach ($participants as $participant) : ?>

                                <tr>

                                    <td>

                                        <?php
                                        echo esc_html(
                                            $participant->firstname .
                                            ' ' .
                                            $participant->lastname
                                        );
                                        ?>

                                    </td>

                                    <td>

                                        <?php
                                        echo esc_html(
                                            $participant->email
                                        );
                                        ?>

                                    </td>

                                    <td>

                                        <?php
                                        echo esc_html(
                                            $participant->phone
                                        );
                                        ?>

                                    </td>

                                    <td>

                                        <?php
                                        echo esc_html(
                                            date_i18n(
                                                'd.m.Y H:i',
                                                strtotime(
                                                    $participant->created_at
                                                )
                                            )
                                        );
                                        ?>

                                    </td>

                                    <td>

                                        <a
                                            class="button button-secondary"
                                            href="<?php echo esc_url(
                                                wp_nonce_url(
                                                    add_query_arg(
                                                        'cm_delete_participant',
                                                        $participant->id
                                                    ),
                                                    'cm_delete_participant_' .
                                                    $participant->id
                                                )
                                            ); ?>"
                                            onclick="return confirm('Teilnehmer wirklich löschen?');"
                                        >
                                            Löschen
                                        </a>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                            </tbody>

                        </table>

                        <p>

                            <a class="button button-primary"
   href="<?php echo esc_url(cm_get_csv_export_url($course_id)); ?>">
    CSV exportieren
</a>

                        </p>

                    <?php endif; ?>

                </div>

                <hr>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

    <?php

    return ob_get_clean();
}

add_shortcode(
    'meine_kurse',
    'cm_shortcode_meine_kurse'
);
add_shortcode('kurs_erstellen', 'cm_render_frontend_course_form');
