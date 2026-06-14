<?php if (!defined('ABSPATH')) exit; ?>

<div class="cm-course">

    <h1><?php the_title(); ?></h1>

    <div class="cm-course-content">
        <?php the_content(); ?>
    </div>

    <hr>

    <p>
        <strong>Max Teilnehmer:</strong>
        <?php echo (int) get_post_meta(get_the_ID(), '_cm_max_participants', true); ?>
    </p>

    <p>
        <strong>Bereits angemeldet:</strong>
        <?php echo cm_get_participant_total(get_the_ID()); ?>
    </p>

    <p>
        <strong>Freie Plätze:</strong>
        <?php
        $max = (int) get_post_meta(get_the_ID(), '_cm_max_participants', true);
        echo max(0, $max - cm_get_participant_total(get_the_ID()));
        ?>
    </p>

    <hr>

    <h2>Anmeldung</h2>

    <?php echo cm_render_registration_form(get_the_ID()); ?>

</div>
