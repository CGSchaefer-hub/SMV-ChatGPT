$user_id = wp_create_user(
    $username,
    $password,
    $email
);

$user = new WP_User($user_id);
$user->set_role('kursleiter');

wp_set_current_user($user_id);
wp_set_auth_cookie($user_id);

<input type="text"
name="title"
value="<?php echo esc_attr($course->post_title); ?>">

<textarea name="content">
<?php echo esc_textarea($course->post_content); ?>
</textarea>

<input type="number"
name="max_participants"
value="<?php echo $max; ?>">

wp_update_post([
    'ID' => $course_id,
    'post_title' => $title,
    'post_content' => $content
]);

update_post_meta(
    $course_id,
    '_cm_max_participants',
    $max
);
