<?php
/**
 * Plugin Name: Course Manager
 * Plugin URI: https://example.com
 * Description: Verwaltung von Kursen und Teilnehmern mit Kursleiter-Rolle.
 * Version: 1.0.0
 * Author: ChatGPT
 * Text Domain: course-manager
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CM_VERSION', '1.0.0');
define('CM_PLUGIN_FILE', __FILE__);
define('CM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CM_PLUGIN_URL', plugin_dir_url(__FILE__));

/*
|--------------------------------------------------------------------------
| Includes
|--------------------------------------------------------------------------
*/

require_once CM_PLUGIN_DIR . 'includes/database.php';
require_once CM_PLUGIN_DIR . 'includes/roles.php';
require_once CM_PLUGIN_DIR . 'includes/post-types.php';
require_once CM_PLUGIN_DIR . 'includes/anmeldung.php';
require_once CM_PLUGIN_DIR . 'includes/shortcode-kursliste.php';
require_once CM_PLUGIN_DIR . 'includes/shortcode-kurs.php';
require_once CM_PLUGIN_DIR . 'includes/kursleiter-dashboard.php';
require_once CM_PLUGIN_DIR . 'includes/csv-export.php';
require_once CM_PLUGIN_DIR . 'includes/admin-pages.php';
require_once CM_PLUGIN_DIR . 'includes/rest-api.php';
require_once CM_PLUGIN_DIR . 'includes/frontend-kursleiter.php';

/*
|--------------------------------------------------------------------------
| Aktivierung
|--------------------------------------------------------------------------
*/

function cm_activate_plugin()
{
    cm_create_tables();
    cm_create_roles();
    cm_register_course_post_type();

    flush_rewrite_rules();
}

register_activation_hook(__FILE__, 'cm_activate_plugin');

/*
|--------------------------------------------------------------------------
| Deaktivierung
|--------------------------------------------------------------------------
*/

function cm_deactivate_plugin()
{
    flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'cm_deactivate_plugin');

/*
|--------------------------------------------------------------------------
| Init
|--------------------------------------------------------------------------
*/

function cm_init_plugin()
{
    load_plugin_textdomain(
        'course-manager',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
}

add_action('plugins_loaded', 'cm_init_plugin');

/*
|--------------------------------------------------------------------------
| Assets Frontend
|--------------------------------------------------------------------------
*/

function cm_enqueue_frontend_assets()
{
    wp_enqueue_style(
        'cm-style',
        CM_PLUGIN_URL . 'assets/style.css',
        [],
        CM_VERSION
    );

    wp_enqueue_script(
        'cm-script',
        CM_PLUGIN_URL . 'assets/script.js',
        ['jquery'],
        CM_VERSION,
        true
    );

    wp_localize_script(
        'cm-script',
        'cm_ajax',
        [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cm_nonce')
        ]
    );
}

add_action('wp_enqueue_scripts', 'cm_enqueue_frontend_assets');

/*
|--------------------------------------------------------------------------
| Assets Backend
|--------------------------------------------------------------------------
*/

function cm_enqueue_admin_assets($hook)
{
    wp_enqueue_style(
        'cm-admin-style',
        CM_PLUGIN_URL . 'assets/style.css',
        [],
        CM_VERSION
    );
}

add_action('admin_enqueue_scripts', 'cm_enqueue_admin_assets');

/*
|--------------------------------------------------------------------------
| Admin-Menü
|--------------------------------------------------------------------------
*/

function cm_register_admin_menu()
{
    add_menu_page(
        __('Kursverwaltung', 'course-manager'),
        __('Kurse', 'course-manager'),
        'edit_posts',
        'course-manager',
        'cm_admin_dashboard_page',
        'dashicons-welcome-learn-more',
        25
    );

    add_submenu_page(
        'course-manager',
        __('Teilnehmer', 'course-manager'),
        __('Teilnehmer', 'course-manager'),
        'edit_posts',
        'course-manager-participants',
        'cm_admin_participants_page'
    );

    add_submenu_page(
        'course-manager',
        __('Export', 'course-manager'),
        __('Export', 'course-manager'),
        'edit_posts',
        'course-manager-export',
        'cm_admin_export_page'
    );

    add_submenu_page(
        'course-manager',
        __('Einstellungen', 'course-manager'),
        __('Einstellungen', 'course-manager'),
        'manage_options',
        'course-manager-settings',
        'cm_admin_settings_page'
    );
}

add_action('admin_menu', 'cm_register_admin_menu');

/*
|--------------------------------------------------------------------------
| Kursleiter prüfen
|--------------------------------------------------------------------------
*/

function cm_is_kursleiter($user_id = null)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    $user = get_userdata($user_id);

    if (!$user) {
        return false;
    }

    return in_array('kursleiter', (array)$user->roles, true);
}

/*
|--------------------------------------------------------------------------
| Kursleiter oder Admin
|--------------------------------------------------------------------------
*/

function cm_can_manage_course($course_id)
{
    if (current_user_can('manage_options')) {
        return true;
    }

    if (!cm_is_kursleiter()) {
        return false;
    }

    $course = get_post($course_id);

    if (!$course) {
        return false;
    }

    return (int)$course->post_author === get_current_user_id();
}

/*
|--------------------------------------------------------------------------
| Teilnehmer zählen
|--------------------------------------------------------------------------
*/

function cm_get_participant_count($course_id)
{
    global $wpdb;

    $table = $wpdb->prefix . 'course_participants';

    return (int)$wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE course_id = %d",
            $course_id
        )
    );
}

/*
|--------------------------------------------------------------------------
| Freie Plätze
|--------------------------------------------------------------------------
*/

function cm_get_available_places($course_id)
{
    $max = (int)get_post_meta(
        $course_id,
        '_cm_max_participants',
        true
    );

    $current = cm_get_participant_count($course_id);

    return max(0, $max - $current);
}

/*
|--------------------------------------------------------------------------
| Kurs ausgebucht?
|--------------------------------------------------------------------------
*/

function cm_is_course_full($course_id)
{
    return cm_get_available_places($course_id) <= 0;
}

/*
|--------------------------------------------------------------------------
| Kursstatus
|--------------------------------------------------------------------------
*/

function cm_get_course_status($course_id)
{
    return cm_is_course_full($course_id)
        ? __('Ausgebucht', 'course-manager')
        : __('Offen', 'course-manager');
}
