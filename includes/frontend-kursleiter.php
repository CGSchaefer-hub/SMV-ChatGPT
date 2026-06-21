$user_id = wp_create_user(
    $username,
    $password,
    $email
);

$user = new WP_User($user_id);
$user->set_role('kursleiter');

wp_set_current_user($user_id);
wp_set_auth_cookie($user_id);
