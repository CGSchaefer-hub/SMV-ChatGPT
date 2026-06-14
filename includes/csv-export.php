<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| CSV-Export verarbeiten
|--------------------------------------------------------------------------
*/

function cm_handle_csv_export()
{
    if (
        !is_user_logged_in() ||
        !isset($_GET['cm_export_csv'])
    ) {
        return;
    }

    $course_id = intval($_GET['cm_export_csv']);

    /*
    |--------------------------------------------------------------------------
    | Berechtigungen prüfen
    |--------------------------------------------------------------------------
    */

    if (!cm_user_can_manage_course($course_id)) {
        wp_die(
            __('Keine Berechtigung für diesen Export.', 'course-manager')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Teilnehmer laden
    |--------------------------------------------------------------------------
    */

    $participants = cm_get_participants($course_id);

    /*
    |--------------------------------------------------------------------------
    | Dateiname erzeugen
    |--------------------------------------------------------------------------
    */

    $course_title = sanitize_title(
        get_the_title($course_id)
    );

    $filename = sprintf(
        'teilnehmer-%s-%s.csv',
        $course_title,
        date('Y-m-d')
    );

    /*
    |--------------------------------------------------------------------------
    | CSV-Ausgabe vorbereiten
    |--------------------------------------------------------------------------
    */

    header('Content-Type: text/csv; charset=utf-8');
    header(
        'Content-Disposition: attachment; filename="' .
        $filename .
        '"'
    );

    $output = fopen('php://output', 'w');

    /*
    |--------------------------------------------------------------------------
    | UTF-8 BOM für Excel
    |--------------------------------------------------------------------------
    */

    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    /*
    |--------------------------------------------------------------------------
    | Kopfzeile
    |--------------------------------------------------------------------------
    */

    fputcsv(
        $output,
        [
            'Vorname',
            'Nachname',
            'E-Mail',
            'Telefon',
            'Anmeldedatum'
        ],
        ';'
    );

    /*
    |--------------------------------------------------------------------------
    | Datensätze
    |--------------------------------------------------------------------------
    */

    foreach ($participants as $participant) {

        fputcsv(
            $output,
            [
                $participant->firstname,
                $participant->lastname,
                $participant->email,
                $participant->phone,
                $participant->created_at
            ],
            ';'
        );
    }

    fclose($output);

    exit;
}

add_action(
    'init',
    'cm_handle_csv_export'
);


/*
|--------------------------------------------------------------------------
| Export-Link erzeugen
|--------------------------------------------------------------------------
*/

function cm_get_csv_export_url($course_id)
{
    return add_query_arg(
        [
            'cm_export_csv' => $course_id
        ],
        home_url('/')
    );
}
