<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$data = LovePress::get_template_data(get_the_ID());
$ani = esc_attr($data['animation']);
$primary = esc_attr($data['primary_color'] ?: '#b83280');
$secondary = esc_attr($data['secondary_color'] ?: '#ffedd5');
$bgvideo = esc_attr($data['background_video'] ?? '');
$ani_hero = esc_attr(get_post_meta(get_the_ID(),'ani_hero',true)) ?: $ani;
$ani_event = esc_attr(get_post_meta(get_the_ID(),'ani_event',true)) ?: $ani;
$ani_gallery = esc_attr(get_post_meta(get_the_ID(),'ani_gallery',true)) ?: $ani;
$ani_rsvp = esc_attr(get_post_meta(get_the_ID(),'ani_rsvp',true)) ?: $ani;
?><style>:root{--lp-primary: <?php echo $primary; ?>; --lp-secondary: <?php echo $secondary; ?>}</style>
<style>:root{--lp-primary: <?php echo $primary; ?>; --lp-secondary: <?php echo $secondary; ?>}</style>

<div class="lp-wrap">
<?php if(!empty($bgvideo)): ?>
  <div class="lp-bg-video"><?php echo $bgvideo; ?></div>
<?php endif; ?>

  <?php if($data['show_hero']!=='0'): ?>
  <section class="lp-hero" data-lp-ani="<?php echo $ani_hero; ?>">
    <h1><?php echo esc_html($data['couple_names']); ?></h1>
    <p><?php echo esc_html($data['date']); ?> · <?php echo esc_html($data['time']); ?></p>
  </section>
  <?php endif; ?>

  <?php if($data['show_event']!=='0'): ?>
  <section class="lp-event" data-lp-ani="<?php echo $ani_event; ?>">
    <h2>Acara</h2>
    <p><strong><?php echo esc_html($data['venue']); ?></strong><br><?php echo esc_html($data['venue_addr']); ?></p>
  </section>
  <?php endif; ?>

  <?php if(!empty($data['gallery']) && $data['show_gallery']!=='0'): ?>
  <section class="lp-gallery" data-lp-ani="<?php echo $ani_gallery; ?>">
    <div class="lp-grid">
      <?php foreach($data['gallery'] as $img): ?>
        <figure><img src="<?php echo esc_url($img); ?>" alt=""></figure>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if(!empty($data['rsvp_url']) && $data['show_rsvp']!=='0'): ?>
  <section class="lp-rsvp" data-lp-ani="<?php echo $ani_rsvp; ?>">
    <a class="lp-btn" href="<?php echo esc_url($data['rsvp_url']); ?>" target="_blank" rel="noopener">Konfirmasi Kehadiran</a>
  </section>
  <?php endif; ?>

  <?php if(!empty($data['map_embed'])): ?>
  <section class="lp-map" data-lp-ani="<?php echo $ani; ?>">
    <div class="lp-map-embed"><?php echo $data['map_embed']; ?></div>
  </section>
  <?php endif; ?>
</div>
