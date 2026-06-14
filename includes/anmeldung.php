<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Anmeldung verarbeiten
|--------------------------------------------------------------------------
*/

function cm_handle_course_registration()
{
    if (
        !isset($_POST['cm_register_submit']) ||
        !isset($_POST['cm_register_nonce'])
    ) {
        return;
    }

    if (
        !wp_verify_nonce(
            $_POST['cm_register_nonce'],
            'cm_register_course'
        )
    ) {
        wp_die(__('Ungültige Anfrage.', 'course-manager'));
    }

    $course_id = intval($_POST['course_id']);

    $firstname = sanitize_text_field(
        $_POST['firstname'] ?? ''
    );

    $lastname = sanitize_text_field(
        $_POST['lastname'] ?? ''
    );

    $email = sanitize_email(
        $_POST['email'] ?? ''
    );

    $phone = sanitize_text_field(
        $_POST['phone'] ?? ''
    );

    /*
    |--------------------------------------------------------------------------
    | Pflichtfelder prüfen
    |--------------------------------------------------------------------------
    */

    if (
        empty($firstname) ||
        empty($lastname) ||
        empty($email)
    ) {

        wp_safe_redirect(
            add_query_arg(
                'cm_error',
                'missing_fields',
                wp_get_referer()
            )
        );

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Gültige E-Mail prüfen
    |--------------------------------------------------------------------------
    */

    if (!is_email($email)) {

        wp_safe_redirect(
            add_query_arg(
                'cm_error',
                'invalid_email',
                wp_get_referer()
            )
        );

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Kurs vorhanden?
    |--------------------------------------------------------------------------
    */

    $course = get_post($course_id);

    if (
        !$course ||
        $course->post_type !== 'course'
    ) {

        wp_safe_redirect(
            add_query_arg(
                'cm_error',
                'course_not_found',
                wp_get_referer()
            )
        );

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Noch Plätze frei?
    |--------------------------------------------------------------------------
    */

    if (cm_is_course_full($course_id)) {

        wp_safe_redirect(
            add_query_arg(
                'cm_error',
                'course_full',
                wp_get_referer()
            )
        );

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Doppelanmeldung verhindern
    |--------------------------------------------------------------------------
    */

    if (
        cm_email_already_registered(
            $course_id,
            $email
        )
    ) {

        wp_safe_redirect(
            add_query_arg(
                'cm_error',
                'already_registered',
                wp_get_referer()
            )
        );

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Teilnehmer speichern
    |--------------------------------------------------------------------------
    */

    $success = cm_add_participant(
        $course_id,
        $firstname,
        $lastname,
        $email,
        $phone
    );

    if (!$success) {

        wp_safe_redirect(
            add_query_arg(
                'cm_error',
                'database_error',
                wp_get_referer()
            )
        );

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Bestätigungsmail an Teilnehmer
    |--------------------------------------------------------------------------
    */

    $subject = sprintf(
        __('Anmeldung für "%s"', 'course-manager'),
        get_the_title($course_id)
    );

    $message =
        "Hallo {$firstname},\n\n" .
        "deine Anmeldung für den Kurs \"" .
        get_the_title($course_id) .
        "\" wurde erfolgreich gespeichert.\n\n" .
        "Viele Grüße";

    wp_mail(
        $email,
        $subject,
        $message
    );

    /*
    |--------------------------------------------------------------------------
    | Erfolg
    |--------------------------------------------------------------------------
    */

    wp_safe_redirect(
        add_query_arg(
            'cm_success',
            '1',
            wp_get_referer()
        )
    );

    exit;
}

add_action(
    'init',
    'cm_handle_course_registration'
);


/*
|--------------------------------------------------------------------------
| Fehlermeldungen
|--------------------------------------------------------------------------
*/

function cm_get_registration_message()
{
    if (isset($_GET['cm_success'])) {

        return '<div class="cm-success">
                    Anmeldung erfolgreich abgeschlossen.
                </div>';
    }

    if (!isset($_GET['cm_error'])) {
        return '';
    }

    switch ($_GET['cm_error']) {

        case 'missing_fields':
            return '<div class="cm-error">
                        Bitte alle Pflichtfelder ausfüllen.
                    </div>';

        case 'invalid_email':
            return '<div class="cm-error">
                        Bitte eine gültige E-Mail-Adresse angeben.
                    </div>';

        case 'course_not_found':
            return '<div class="cm-error">
                        Der Kurs wurde nicht gefunden.
                    </div>';

        case 'course_full':
            return '<div class="cm-error">
                        Der Kurs ist bereits ausgebucht.
                    </div>';

        case 'already_registered':
            return '<div class="cm-error">
                        Diese E-Mail-Adresse ist bereits angemeldet.
                    </div>';

        case 'database_error':
            return '<div class="cm-error">
                        Die Anmeldung konnte nicht gespeichert werden.
                    </div>';

        default:
            return '';
    }
}


/*
|--------------------------------------------------------------------------
| Formular erzeugen
|--------------------------------------------------------------------------
*/

function cm_render_registration_form($course_id)
{
    if (cm_is_course_full($course_id)) {

        return '<p><strong>Der Kurs ist ausgebucht.</strong></p>';
    }

    ob_start();
    ?>

    <?php echo cm_get_registration_message(); ?>

    <form method="post" class="cm-registration-form">

        <?php
        wp_nonce_field(
            'cm_register_course',
            'cm_register_nonce'
        );
        ?>

        <input
            type="hidden"
            name="course_id"
            value="<?php echo esc_attr($course_id); ?>"
        >

        <p>
            <label>Vorname *</label><br>
            <input
                type="text"
                name="firstname"
                required
            >
        </p>

        <p>
            <label>Nachname *</label><br>
            <input
                type="text"
                name="lastname"
                required
            >
        </p>

        <p>
            <label>E-Mail *</label><br>
            <input
                type="email"
                name="email"
                required
            >
        </p>

        <p>
            <label>Telefon</label><br>
            <input
                type="text"
                name="phone"
            >
        </p>

        <p>
            <button
                type="submit"
                name="cm_register_submit"
            >
                Jetzt anmelden
            </button>
        </p>

    </form>

    <?php

    return ob_get_clean();
}
