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

function cm_api_get_courses($request)
{
    $courses = get_posts([
        'post_type' => 'course',
        'post_status' => 'publish',
        'posts_per_page' => -1
    ]);

    $data = [];

    foreach ($courses as $course) {

        $max = (int)get_post_meta($course->ID, '_cm_max_participants', true);
        $current = cm_get_participant_total($course->ID);

        $data[] = [
            'id' => $course->ID,
            'title' => $course->post_title,
            'description' => wp_trim_words($course->post_content, 30),
            'max' => $max,
            'current' => $current,
            'free' => max(0, $max - $current)
        ];
    }

    return rest_ensure_response($data);
}

function cm_api_get_course($request)
{
    $id = (int)$request['id'];

    $course = get_post($id);

    if (!$course || $course->post_type !== 'course') {
        return new WP_Error('not_found', 'Course not found', ['status' => 404]);
    }

    return [
        'id' => $course->ID,
        'title' => $course->post_title,
        'content' => apply_filters('the_content', $course->post_content),
        'max' => (int)get_post_meta($id, '_cm_max_participants', true),
        'current' => cm_get_participant_total($id)
    ];
}
