<?php if (!defined('ABSPATH')) exit; ?>

<div class="cm-dashboard">

    <h2>Meine Kurse</h2>

    <?php if (empty($courses)) : ?>

        <p>Keine Kurse gefunden.</p>

    <?php else : ?>

        <?php foreach ($courses as $course) : ?>

            <?php
            $participants = cm_get_participants($course->ID);
            ?>

            <div class="cm-course-dashboard">

                <h3><?php echo esc_html($course->post_title); ?></h3>

                <p>
                    Teilnehmer: <?php echo count($participants); ?>
                </p>

                <a class="button" href="<?php echo get_permalink($course->ID); ?>">
                    Anzeigen
                </a>

            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</div>
