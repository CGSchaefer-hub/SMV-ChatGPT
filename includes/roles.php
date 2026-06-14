<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Rolle "Kursleiter" anlegen
|--------------------------------------------------------------------------
*/

function cm_create_roles()
{
    add_role(
        'kursleiter',
        __('Kursleiter', 'course-manager'),
        [
            'read' => true,

            // Kurse lesen
            'read_course' => true,
            'read_private_courses' => true,

            // Eigene Kurse verwalten
            'edit_courses' => true,
            'edit_published_courses' => true,
            'publish_courses' => true,
            'delete_courses' => true,
            'delete_published_courses' => true,

            // Eigene Beiträge bearbeiten
            'edit_posts' => true,
            'upload_files' => true,
        ]
    );

    /*
    |--------------------------------------------------------------------------
    | Administrator erhält ebenfalls alle Rechte
    |--------------------------------------------------------------------------
    */

    $admin = get_role('administrator');

    if ($admin) {

        $capabilities = [
            'read_course',
            'read_private_courses',
            'edit_course',
            'edit_courses',
            'edit_others_courses',
            'edit_published_courses',
            'publish_courses',
            'delete_course',
            'delete_courses',
            'delete_others_courses',
            'delete_published_courses'
        ];

        foreach ($capabilities as $cap) {
            $admin->add_cap($cap);
        }
    }
}

/*
|--------------------------------------------------------------------------
| Rolle entfernen
|--------------------------------------------------------------------------
*/

function cm_remove_roles()
{
    remove_role('kursleiter');
}

/*
|--------------------------------------------------------------------------
| Prüfen, ob Benutzer Kursleiter ist
|--------------------------------------------------------------------------
*/

function cm_user_is_kursleiter($user_id = null)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    $user = get_userdata($user_id);

    if (!$user) {
        return false;
    }

    return in_array('kursleiter', (array) $user->roles, true);
}

/*
|--------------------------------------------------------------------------
| Prüfen, ob Benutzer Administrator ist
|--------------------------------------------------------------------------
*/

function cm_user_is_admin($user_id = null)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    return user_can($user_id, 'manage_options');
}

/*
|--------------------------------------------------------------------------
| Darf Benutzer einen Kurs verwalten?
|--------------------------------------------------------------------------
*/

function cm_user_can_manage_course($course_id, $user_id = null)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    // Administrator darf alles
    if (cm_user_is_admin($user_id)) {
        return true;
    }

    // Nur Kursleiter dürfen eigene Kurse verwalten
    if (!cm_user_is_kursleiter($user_id)) {
        return false;
    }

    $course = get_post($course_id);

    if (!$course) {
        return false;
    }

    return ((int) $course->post_author === (int) $user_id);
}

/*
|--------------------------------------------------------------------------
| Alle Kursleiter abrufen
|--------------------------------------------------------------------------
*/

function cm_get_all_kursleiter()
{
    return get_users([
        'role' => 'kursleiter',
        'orderby' => 'display_name',
        'order' => 'ASC'
    ]);
}

/*
|--------------------------------------------------------------------------
| Anzahl der Kurse eines Kursleiters
|--------------------------------------------------------------------------
*/

function cm_get_course_count_by_kursleiter($user_id)
{
    $courses = get_posts([
        'post_type' => 'course',
        'author' => $user_id,
        'posts_per_page' => -1,
        'post_status' => ['publish', 'draft']
    ]);

    return count($courses);
}
