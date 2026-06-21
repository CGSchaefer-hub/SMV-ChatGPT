<?php

if (!defined('ABSPATH')) exit;

require_once CM_PLUGIN_DIR . 'includes/router.php';

/*
|--------------------------------------------------------------------------
| APP ROUTER
|--------------------------------------------------------------------------
*/

function cm_app_router()
{
    $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

    /*
    |--------------------------------------------------------------------------
    | /kurse
    |--------------------------------------------------------------------------
    */

    if ($path === 'kurse') {

get_header();

echo cm_app_render_course_list();

get_footer();

exit;
    }

    /*
    |--------------------------------------------------------------------------
    | /kurs?course_id=123
    |--------------------------------------------------------------------------
    */

    if ($path === 'kurs') {

        $course_id = intval($_GET['course_id'] ?? 0);

        echo cm_app_render_single_course($course_id);
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | /dashboard
    |--------------------------------------------------------------------------
    */

    if ($path === 'dashboard') {

        echo cm_shortcode_meine_kurse();
        exit;
    }
}

add_action('template_redirect', 'cm_app_router');
