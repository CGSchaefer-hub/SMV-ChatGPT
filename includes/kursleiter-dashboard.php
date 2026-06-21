<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once CM_PLUGIN_DIR . 'includes/frontend-course-form.php';

/*
|--------------------------------------------------------------------------
| Teilnehmer löschen
|--------------------------------------------------------------------------
*/

function cm_handle_delete_participant()
{
    if (
        !isset($_GET['cm_delete_participant']) ||
        !isset($_GET['_wpnonce'])
    ) {
        return;
    }

    $participant_id = intval($_GET['cm_delete_participant']);

    if (
        !wp_verify_nonce(
            $_GET['_wpnonce'],
            'cm_delete_participant_' . $participant_id
        )
    ) {
        wp_die(__('Ungültige Anfrage.', 'course-manager'));
    }

    $participant = cm_get_participant($participant_id);

    if (!$participant) {
        return;
    }

    if (!cm_user_can_manage_course($participant->course_id)) {
        wp_die(__('Keine Berechtigung.', 'course-manager'));
    }

    cm_delete_participant($participant_id);

    wp_safe_redirect(remove_query_arg([
        'cm_delete_participant',
        '_wpnonce'
    ]));

    exit;
}

add_action('init', 'cm_handle_delete_participant');


/*
|--------------------------------------------------------------------------
| Shortcode [meine_kurse]
|--------------------------------------------------------------------------
*/

function cm_shortcode_meine_kurse()
{
    if (!is_user_logged_in()) {
        return '<p>Bitte zuerst anmelden.</p>';
    }

    if (!cm_user_is_admin() && !cm_user_is_kursleiter()) {
        return '<p>Du bist kein Kursleiter.</p>';
    }

    $view = $_GET['view'] ?? 'list';

    ob_start();
    ?>

    <div class="cm-dashboard">

        <h2>Mein Kurs-Dashboard</h2>

        <!-- Navigation -->
        <p>
            <a href="<?php echo add_query_arg('view', 'list'); ?>" class="button">
                Meine Kurse
            </a>

            <a href="<?php echo add_query_arg('view', 'create'); ?>" class="button button-primary">
                + Kurs erstellen
            </a>
        </p>

        <hr>

        <?php
        /*
        |--------------------------------------------------------------------------
        | VIEW: KURS ERSTELLEN
        |--------------------------------------------------------------------------
        */
    
<a class="button button-primary"
href="<?php echo site_url('/kurs-erstellen'); ?>">
Neuen Kurs erstellen
</a>

<a class="button"
href="<?php echo site_url(
'/kurs-bearbeiten?course_id=' . $course->ID
); ?>">
Bearbeiten
</a>
        
        if ($view === 'create') {

            echo cm_render_frontend_course_form();

        } else {

            /*
            |--------------------------------------------------------------------------
            | VIEW: LISTE
            |--------------------------------------------------------------------------
            */

            $args = [
                'post_type' => 'course',
                'post_status' => ['publish', 'draft'],
                'posts_per_page' => -1,
                'orderby' => 'date',
                'order' => 'DESC'
            ];

            if (!cm_user_is_admin()) {
                $args['author'] = get_current_user_id();
            }

            $courses = get_posts($args);
            ?>

            <h3>Meine Kurse</h3>

            <?php if (empty($courses)) : ?>

                <p>Keine Kurse vorhanden.</p>

            <?php else : ?>

                <?php foreach ($courses as $course) : ?>

                    <?php
                    $participants = cm_get_participants($course->ID);
                    $max = (int) get_post_meta($course->ID, '_cm_max_participants', true);
                    ?>

                    <div class="cm-course-dashboard">

                        <h3><?php echo esc_html($course->post_title); ?></h3>

                        <p>
                            Teilnehmer:
                            <?php echo count($participants); ?>
                            /
                            <?php echo $max; ?>
                        </p>

                        <a class="button" href="<?php echo get_permalink($course->ID); ?>">
                            Anzeigen
                        </a>

                        <a class="button" href="<?php echo admin_url('post.php?post=' . $course->ID . '&action=edit'); ?>">
                            Bearbeiten
                        </a>

                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        <?php } ?>

    </div>

    <?php
    return ob_get_clean();
}

add_shortcode(
    'meine_kurse',
    'cm_shortcode_meine_kurse'
);

add_shortcode(
    'kurs_erstellen',
    'cm_render_frontend_course_form'
);
