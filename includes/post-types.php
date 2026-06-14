<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Post Type "course" registrieren
|--------------------------------------------------------------------------
*/

function cm_register_course_post_type()
{
    $labels = [
        'name'               => __('Kurse', 'course-manager'),
        'singular_name'      => __('Kurs', 'course-manager'),
        'add_new'            => __('Neuen Kurs anlegen', 'course-manager'),
        'add_new_item'       => __('Neuen Kurs anlegen', 'course-manager'),
        'edit_item'          => __('Kurs bearbeiten', 'course-manager'),
        'new_item'           => __('Neuer Kurs', 'course-manager'),
        'view_item'          => __('Kurs anzeigen', 'course-manager'),
        'search_items'       => __('Kurse durchsuchen', 'course-manager'),
        'not_found'          => __('Keine Kurse gefunden', 'course-manager'),
        'menu_name'          => __('Kurse', 'course-manager')
    ];

    register_post_type('course', [
        'labels' => $labels,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-welcome-learn-more',
        'supports' => ['title', 'editor'],
        'has_archive' => true,
        'rewrite' => [
            'slug' => 'kurse'
        ],

        'capability_type' => ['course', 'courses'],
        'map_meta_cap' => true
    ]);
}

add_action('init', 'cm_register_course_post_type');


/*
|--------------------------------------------------------------------------
| Metabox für maximale Teilnehmerzahl
|--------------------------------------------------------------------------
*/

function cm_add_course_meta_boxes()
{
    add_meta_box(
        'cm_course_settings',
        __('Kurseinstellungen', 'course-manager'),
        'cm_render_course_settings_box',
        'course',
        'side'
    );
}

add_action('add_meta_boxes', 'cm_add_course_meta_boxes');


function cm_render_course_settings_box($post)
{
    wp_nonce_field('cm_save_course_settings', 'cm_course_nonce');

    $max_participants = get_post_meta(
        $post->ID,
        '_cm_max_participants',
        true
    );

    $current_participants = cm_get_participant_total($post->ID);

    ?>
    <p>
        <label for="cm_max_participants">
            <strong>Maximale Teilnehmerzahl</strong>
        </label>
    </p>

    <input
        type="number"
        min="1"
        style="width:100%;"
        id="cm_max_participants"
        name="cm_max_participants"
        value="<?php echo esc_attr($max_participants); ?>"
    >

    <hr>

    <p>
        <strong>Angemeldete Teilnehmer:</strong><br>
        <?php echo intval($current_participants); ?>
    </p>

    <p>
        <strong>Freie Plätze:</strong><br>
        <?php echo max(0, intval($max_participants) - $current_participants); ?>
    </p>

    <?php
}


/*
|--------------------------------------------------------------------------
| Metadaten speichern
|--------------------------------------------------------------------------
*/

function cm_save_course_meta($post_id)
{
    if (!isset($_POST['cm_course_nonce'])) {
        return;
    }

    if (!wp_verify_nonce(
        $_POST['cm_course_nonce'],
        'cm_save_course_settings'
    )) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['cm_max_participants'])) {

        update_post_meta(
            $post_id,
            '_cm_max_participants',
            intval($_POST['cm_max_participants'])
        );
    }
}

add_action('save_post_course', 'cm_save_course_meta');


/*
|--------------------------------------------------------------------------
| Zusätzliche Spalten im Adminbereich
|--------------------------------------------------------------------------
*/

function cm_course_columns($columns)
{
    $new_columns = [];

    foreach ($columns as $key => $value) {

        $new_columns[$key] = $value;

        if ($key === 'title') {

            $new_columns['max_participants'] = __('Max. Plätze', 'course-manager');
            $new_columns['current_participants'] = __('Angemeldet', 'course-manager');
            $new_columns['free_places'] = __('Freie Plätze', 'course-manager');
        }
    }

    return $new_columns;
}

add_filter(
    'manage_course_posts_columns',
    'cm_course_columns'
);


function cm_course_column_content($column, $post_id)
{
    switch ($column) {

        case 'max_participants':

            echo intval(
                get_post_meta(
                    $post_id,
                    '_cm_max_participants',
                    true
                )
            );

            break;

        case 'current_participants':

            echo intval(
                cm_get_participant_total($post_id)
            );

            break;

        case 'free_places':

            $max = intval(
                get_post_meta(
                    $post_id,
                    '_cm_max_participants',
                    true
                )
            );

            $current = cm_get_participant_total($post_id);

            echo max(0, $max - $current);

            break;
    }
}

add_action(
    'manage_course_posts_custom_column',
    'cm_course_column_content',
    10,
    2
);


/*
|--------------------------------------------------------------------------
| Kursleiter sehen nur eigene Kurse
|--------------------------------------------------------------------------
*/

function cm_limit_courses_to_author($query)
{
    if (
        !is_admin()
        || !$query->is_main_query()
        || $query->get('post_type') !== 'course'
    ) {
        return;
    }

    if (
        current_user_can('manage_options')
    ) {
        return;
    }

    if (
        in_array(
            'kursleiter',
            wp_get_current_user()->roles
        )
    ) {
        $query->set(
            'author',
            get_current_user_id()
        );
    }
}

add_action(
    'pre_get_posts',
    'cm_limit_courses_to_author'
);
