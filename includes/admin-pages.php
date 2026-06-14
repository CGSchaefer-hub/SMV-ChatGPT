<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

function cm_admin_dashboard_page()
{
    ?>
    <div class="wrap">

        <h1>Kursverwaltung</h1>

        <h2>Statistik</h2>

        <?php

        $course_count = wp_count_posts('course')->publish;
        $participant_count = count(cm_get_all_participants());

        ?>

        <table class="widefat striped" style="max-width:600px;">

            <tbody>

            <tr>
                <th>Anzahl Kurse</th>
                <td><?php echo esc_html($course_count); ?></td>
            </tr>

            <tr>
                <th>Anzahl Teilnehmer</th>
                <td><?php echo esc_html($participant_count); ?></td>
            </tr>

            </tbody>

        </table>

        <br>

        <p>

            <a
                href="<?php echo esc_url(
                    admin_url('edit.php?post_type=course')
                ); ?>"
                class="button button-primary"
            >
                Kurse verwalten
            </a>

        </p>

    </div>
    <?php
}


/*
|--------------------------------------------------------------------------
| Teilnehmerseite
|--------------------------------------------------------------------------
*/

function cm_admin_participants_page()
{
    $participants = cm_get_all_participants();

    ?>
    <div class="wrap">

        <h1>Teilnehmer</h1>

        <?php if (empty($participants)) : ?>

            <p>Keine Teilnehmer vorhanden.</p>

        <?php else : ?>

            <table class="widefat striped">

                <thead>
                <tr>
                    <th>Kurs</th>
                    <th>Vorname</th>
                    <th>Nachname</th>
                    <th>E-Mail</th>
                    <th>Telefon</th>
                    <th>Anmeldedatum</th>
                </tr>
                </thead>

                <tbody>

                <?php foreach ($participants as $participant) : ?>

                    <tr>

                        <td>
                            <?php
                            echo esc_html(
                                get_the_title($participant->course_id)
                            );
                            ?>
                        </td>

                        <td>
                            <?php echo esc_html($participant->firstname); ?>
                        </td>

                        <td>
                            <?php echo esc_html($participant->lastname); ?>
                        </td>

                        <td>
                            <?php echo esc_html($participant->email); ?>
                        </td>

                        <td>
                            <?php echo esc_html($participant->phone); ?>
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

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        <?php endif; ?>

    </div>
    <?php
}


/*
|--------------------------------------------------------------------------
| Export-Seite
|--------------------------------------------------------------------------
*/

function cm_admin_export_page()
{
    ?>
    <div class="wrap">

        <h1>CSV-Export</h1>

        <p>
            Die Teilnehmerlisten können direkt aus dem
            Kursleiter-Dashboard exportiert werden.
        </p>

        <table class="widefat striped">

            <thead>
            <tr>
                <th>Kurs</th>
                <th>Teilnehmer</th>
                <th>Aktion</th>
            </tr>
            </thead>

            <tbody>

            <?php

            $courses = get_posts([
                'post_type' => 'course',
                'posts_per_page' => -1,
                'post_status' => 'publish'
            ]);

            foreach ($courses as $course) :

                ?>

                <tr>

                    <td>

                        <?php
                        echo esc_html(
                            get_the_title($course->ID)
                        );
                        ?>

                    </td>

                    <td>

                        <?php
                        echo esc_html(
                            cm_get_participant_total(
                                $course->ID
                            )
                        );
                        ?>

                    </td>

                    <td>

                        <a
                            class="button"
                            href="<?php echo esc_url(
                                cm_get_csv_export_url(
                                    $course->ID
                                )
                            ); ?>"
                        >
                            CSV herunterladen
                        </a>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>
    <?php
}


/*
|--------------------------------------------------------------------------
| Einstellungen
|--------------------------------------------------------------------------
*/

function cm_admin_settings_page()
{
    if (
        isset($_POST['cm_save_settings'])
    ) {

        check_admin_referer(
            'cm_settings'
        );

        update_option(
            'cm_send_confirmation_mail',
            isset($_POST['cm_send_confirmation_mail'])
                ? 1
                : 0
        );

        ?>

        <div class="notice notice-success">
            <p>Einstellungen gespeichert.</p>
        </div>

        <?php
    }

    $send_confirmation_mail = get_option(
        'cm_send_confirmation_mail',
        1
    );

    ?>

    <div class="wrap">

        <h1>Einstellungen</h1>

        <form method="post">

            <?php wp_nonce_field('cm_settings'); ?>

            <table class="form-table">

                <tr>

                    <th scope="row">
                        Bestätigungsmail senden
                    </th>

                    <td>

                        <label>

                            <input
                                type="checkbox"
                                name="cm_send_confirmation_mail"
                                value="1"
                                <?php checked(
                                    $send_confirmation_mail,
                                    1
                                ); ?>
                            >

                            Teilnehmer nach erfolgreicher
                            Anmeldung per E-Mail benachrichtigen.

                        </label>

                    </td>

                </tr>

            </table>

            <p>

                <button
                    type="submit"
                    name="cm_save_settings"
                    class="button button-primary"
                >
                    Einstellungen speichern
                </button>

            </p>

        </form>

    </div>

    <?php
}
