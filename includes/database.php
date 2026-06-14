<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Datenbanktabelle anlegen
|--------------------------------------------------------------------------
*/

function cm_create_tables()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'course_participants';
    $charset_collate = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $sql = "CREATE TABLE {$table_name} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        course_id BIGINT UNSIGNED NOT NULL,
        firstname VARCHAR(100) NOT NULL,
        lastname VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(100) DEFAULT '',
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY course_id (course_id),
        KEY email (email)
    ) {$charset_collate};";

    dbDelta($sql);
}

/*
|--------------------------------------------------------------------------
| Teilnehmer hinzufügen
|--------------------------------------------------------------------------
*/

function cm_add_participant(
    $course_id,
    $firstname,
    $lastname,
    $email,
    $phone = ''
) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'course_participants';

    return $wpdb->insert(
        $table_name,
        [
            'course_id' => $course_id,
            'firstname' => sanitize_text_field($firstname),
            'lastname' => sanitize_text_field($lastname),
            'email' => sanitize_email($email),
            'phone' => sanitize_text_field($phone),
            'created_at' => current_time('mysql')
        ],
        [
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s'
        ]
    );
}

/*
|--------------------------------------------------------------------------
| Alle Teilnehmer eines Kurses abrufen
|--------------------------------------------------------------------------
*/

function cm_get_participants($course_id)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'course_participants';

    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT *
             FROM {$table_name}
             WHERE course_id = %d
             ORDER BY created_at ASC",
            $course_id
        )
    );
}

/*
|--------------------------------------------------------------------------
| Einzelnen Teilnehmer abrufen
|--------------------------------------------------------------------------
*/

function cm_get_participant($participant_id)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'course_participants';

    return $wpdb->get_row(
        $wpdb->prepare(
            "SELECT *
             FROM {$table_name}
             WHERE id = %d",
            $participant_id
        )
    );
}

/*
|--------------------------------------------------------------------------
| Teilnehmer löschen
|--------------------------------------------------------------------------
*/

function cm_delete_participant($participant_id)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'course_participants';

    return $wpdb->delete(
        $table_name,
        [
            'id' => $participant_id
        ],
        [
            '%d'
        ]
    );
}

/*
|--------------------------------------------------------------------------
| Prüfen, ob E-Mail bereits angemeldet ist
|--------------------------------------------------------------------------
*/

function cm_email_already_registered($course_id, $email)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'course_participants';

    $count = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*)
             FROM {$table_name}
             WHERE course_id = %d
             AND email = %s",
            $course_id,
            sanitize_email($email)
        )
    );

    return ((int)$count > 0);
}

/*
|--------------------------------------------------------------------------
| Anzahl der Teilnehmer eines Kurses
|--------------------------------------------------------------------------
*/

function cm_get_participant_total($course_id)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'course_participants';

    return (int)$wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*)
             FROM {$table_name}
             WHERE course_id = %d",
            $course_id
        )
    );
}

/*
|--------------------------------------------------------------------------
| Alle Teilnehmer aller Kurse abrufen
|--------------------------------------------------------------------------
*/

function cm_get_all_participants()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'course_participants';

    return $wpdb->get_results(
        "SELECT *
         FROM {$table_name}
         ORDER BY created_at DESC"
    );
}
