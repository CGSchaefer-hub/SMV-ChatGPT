<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Kurs erstellen verarbeiten
|--------------------------------------------------------------------------
*/

function cm_handle_frontend_course_create()
{
    if (!isset($_POST['cm_create_course'])) {
        return;
    }

    if (
        !isset($_POST['cm_course_nonce']) ||
        !wp_verify_nonce($_POST['cm_course_nonce'], 'cm_create_course')
    ) {
        wp_die('Ungültige Anfrage');
    }

    if (!cm_user_is_kursleiter() && !cm_user_is_admin()) {
        wp_die('Keine Berechtigung');
    }

    $title = sanitize_text_field($_POST['title'] ?? '');
    $content = wp_kses_post($_POST['content'] ?? '');
    $max = intval($_POST['max_participants'] ?? 0);

    if (empty($title)) {
        wp_safe_redirect(add_query_arg('cm_error', 'missing_title', wp_get_referer()));
        exit;
    }

    $course_id = wp_insert_post([
        'post_type'   => 'course',
        'post_title'  => $title,
        'post_content'=> $content,
        'post_status' => 'publish',
        'post_author' => get_current_user_id(),
    ]);

    if (is_wp_error($course_id)) {
        wp_die('Fehler beim Erstellen');
    }

    update_post_meta($course_id, '_cm_max_participants', $max);

    wp_safe_redirect(add_query_arg('cm_success', 'course_created', wp_get_referer()));
    exit;
}

add_action('init', 'cm_handle_frontend_course_create');


/*
|--------------------------------------------------------------------------
| Formular anzeigen
|--------------------------------------------------------------------------
*/

function cm_render_frontend_course_form()
{
    if (!is_user_logged_in()) {
        return '<p>Bitte einloggen.</p>';
    }

    if (!cm_user_is_kursleiter() && !cm_user_is_admin()) {
        return '<p>Keine Berechtigung.</p>';
    }

    ob_start();
    ?>

    <?php if (isset($_GET['cm_success'])) : ?>
        <div class="cm-success">
            Kurs erfolgreich erstellt.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['cm_error'])) : ?>
        <div class="cm-error">
            Fehler beim Erstellen.
        </div>
    <?php endif; ?>

    <form method="post" class="cm-course-create-form">

        <?php wp_nonce_field('cm_create_course', 'cm_course_nonce'); ?>

        <p>
            <label>Titel *</label>
            <input type="text" name="title" required>
        </p>

        <p>
            <label>Beschreibung</label>
            <textarea name="content" rows="6"></textarea>
        </p>

        <p>
            <label>Maximale Teilnehmerzahl *</label>
            <input type="number" name="max_participants" min="1" required>
        </p>

        <p>
            <button type="submit" name="cm_create_course">
                Kurs erstellen
            </button>
        </p>

    </form>

    <?php
    return ob_get_clean();
}
