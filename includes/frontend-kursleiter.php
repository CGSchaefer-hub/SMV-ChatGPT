<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Kursleiter-Registrierung verarbeiten
|--------------------------------------------------------------------------
*/

function cm_handle_kursleiter_registration()
{
    if (!isset($_POST['cm_register_kursleiter'])) {
        return;
    }

    if (
        !isset($_POST['cm_kursleiter_nonce']) ||
        !wp_verify_nonce($_POST['cm_kursleiter_nonce'], 'cm_register_kursleiter')
    ) {
        wp_die('Ungültige Anfrage');
    }

    $firstname = sanitize_text_field($_POST['firstname']);
    $lastname  = sanitize_text_field($_POST['lastname']);
    $username  = sanitize_user($_POST['username']);
    $email     = sanitize_email($_POST['email']);
    $password  = $_POST['password'];

    if (
        username_exists($username) ||
        email_exists($email)
    ) {
        wp_safe_redirect(add_query_arg('error', 'user_exists', wp_get_referer()));
        exit;
    }

    $user_id = wp_create_user(
        $username,
        $password,
        $email
    );

    if (is_wp_error($user_id)) {
        wp_die($user_id->get_error_message());
    }

    wp_update_user([
        'ID' => $user_id,
        'first_name' => $firstname,
        'last_name' => $lastname
    ]);

    $user = new WP_User($user_id);
    $user->set_role('kursleiter');

    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);

    wp_safe_redirect(site_url('/dashboard'));
    exit;
}

add_action('init', 'cm_handle_kursleiter_registration');


/*
|--------------------------------------------------------------------------
| Formular Kursleiter-Registrierung
|--------------------------------------------------------------------------
*/

function cm_render_kursleiter_registration()
{
    ob_start();
    ?>

    <form method="post" class="cm-registration-form">

        <?php wp_nonce_field(
            'cm_register_kursleiter',
            'cm_kursleiter_nonce'
        ); ?>

        <p>
            <input type="text"
                   name="firstname"
                   placeholder="Vorname"
                   required>
        </p>

        <p>
            <input type="text"
                   name="lastname"
                   placeholder="Nachname"
                   required>
        </p>

        <p>
            <input type="text"
                   name="username"
                   placeholder="Benutzername"
                   required>
        </p>

        <p>
            <input type="email"
                   name="email"
                   placeholder="E-Mail"
                   required>
        </p>

        <p>
            <input type="password"
                   name="password"
                   placeholder="Passwort"
                   required>
        </p>

        <p>
            <button type="submit"
                    name="cm_register_kursleiter">
                Als Kursleiter registrieren
            </button>
        </p>

    </form>

    <?php

    return ob_get_clean();
}

add_shortcode(
    'kursleiter_registrierung',
    'cm_render_kursleiter_registration'
);


/*
|--------------------------------------------------------------------------
| Kurs bearbeiten
|--------------------------------------------------------------------------
*/

function cm_render_course_edit_form()
{
    if (!is_user_logged_in()) {
        return '<p>Bitte anmelden.</p>';
    }

    $course_id = intval($_GET['course_id'] ?? 0);

    if (!$course_id) {
        return '<p>Kein Kurs ausgewählt.</p>';
    }

    $course = get_post($course_id);

    if (
        !$course ||
        $course->post_author != get_current_user_id()
    ) {
        return '<p>Keine Berechtigung.</p>';
    }

    if (isset($_POST['cm_update_course'])) {

        check_admin_referer(
            'cm_edit_course',
            'cm_edit_course_nonce'
        );

        $title = sanitize_text_field($_POST['title']);
        $content = wp_kses_post($_POST['content']);
        $max = intval($_POST['max_participants']);

        wp_update_post([
            'ID' => $course_id,
            'post_title' => $title,
            'post_content' => $content
        ]);

        update_post_meta(
            $course_id,
            '_cm_max_participants',
            $max
        );

        $course = get_post($course_id);
    }

    $max = get_post_meta(
        $course_id,
        '_cm_max_participants',
        true
    );

    ob_start();
    ?>

    <form method="post">

        <?php wp_nonce_field(
            'cm_edit_course',
            'cm_edit_course_nonce'
        ); ?>

        <p>
            <label>Titel</label><br>
            <input type="text"
                   name="title"
                   value="<?php echo esc_attr($course->post_title); ?>"
                   required>
        </p>

        <p>
            <label>Beschreibung</label><br>

            <textarea name="content"
                      rows="8"
                      cols="60"><?php
                echo esc_textarea($course->post_content);
            ?></textarea>
        </p>

        <p>
            <label>Maximale Teilnehmerzahl</label><br>

            <input type="number"
                   name="max_participants"
                   value="<?php echo esc_attr($max); ?>"
                   min="1"
                   required>
        </p>

        <p>
            <button type="submit"
                    name="cm_update_course">
                Änderungen speichern
            </button>
        </p>

    </form>

    <?php

    return ob_get_clean();
}

add_shortcode(
    'kurs_bearbeiten',
    'cm_render_course_edit_form'
);


/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

function cm_render_kursleiter_dashboard()
{
    if (!is_user_logged_in()) {
        return '<p>Bitte anmelden.</p>';
    }

    return do_shortcode('[meine_kurse]');
}

add_shortcode(
    'kursleiter_dashboard',
    'cm_render_kursleiter_dashboard'
);
