<?php

if (!defined('ABSPATH')) exit;

/*
|--------------------------------------------------------------------------
| REST API REGISTRIEREN
|--------------------------------------------------------------------------
*/

add_action('rest_api_init', function () {

    register_rest_route('cm/v1', '/courses', [
        'methods'  => 'GET',
        'callback' => 'cm_api_get_courses',
        'permission_callback' => '__return_true'
    ]);

    register_rest_route('cm/v1', '/course/(?P<id>\d+)', [
        'methods'  => 'GET',
        'callback' => 'cm_api_get_course',
        'permission_callback' => '__return_true'
    ]);

    register_rest_route('cm/v1', '/register', [
        'methods'  => 'POST',
        'callback' => 'cm_api_register',
        'permission_callback' => '__return_true'
    ]);

    register_rest_route('cm/v1', '/course/create', [
        'methods'  => 'POST',
        'callback' => 'cm_api_create_course',
        'permission_callback' => 'cm_api_check_auth'
    ]);

});
