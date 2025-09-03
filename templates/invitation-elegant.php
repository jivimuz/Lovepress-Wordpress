<?php
/**
 * Template Name: Elegant
 */
$data = LovePress::get_template_data(get_the_ID());
$nama_pasangan = esc_html($data['couple_names'] ?? 'Nama Pasangan');
$tanggal_acara = esc_html($data['date'] ?? '');
$waktu_acara = esc_html($data['time'] ?? '');
$datetime_string = $tanggal_acara . ' ' . $waktu_acara;
$timezone = new DateTimeZone('Asia/Jakarta');
$datetime_obj = DateTime::createFromFormat('Y-m-d H:i', $datetime_string, $timezone);
$iso_datetime = $datetime_obj ? $datetime_obj->format('c') : '';
$primary_color = esc_attr($data['primary_color'] ?? '#b83280');
$secondary_color = esc_attr($data['secondary_color'] ?? '#ffedd5');
$foto_utama = get_the_post_thumbnail_url(get_the_ID(), 'large');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@400;500&display=swap" rel="stylesheet">
    <title><?php the_title(); ?></title>
    <?php wp_head(); ?>
    <style>:root { --primary-color: <?php echo $primary_color; ?>; --secondary-color: <?php echo $secondary_color; ?>; --text-color: #555; --heading-font: 'Playfair Display', serif; --body-font: 'Poppins', sans-serif; }</style>
</head>
<body <?php body_class('lovepress-elegant-template'); ?>>

    <div id="lp-opening-modal" style="background-image: url('<?php echo esc_url($foto_utama); ?>');">
        <div class="lp-overlay"></div>
        <div class="lp-opening-content">
            <p>The Wedding Of</p>
            <h2><?php echo $nama_pasangan; ?></h2>
            <p>Kepada Bapak/Ibu/Saudara/i,</p>
            <p class="guest-name">[Nama Tamu Disini]</p>
            <p class="small-text">Tanpa mengurangi rasa hormat, kami mengundang Anda untuk hadir di acara pernikahan kami.</p>
            <button id="lp-open-invitation">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M3 18.5V5.5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2zm16-13H5v13h14V5.5zM12 16a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm-3-3a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/></svg>
                Buka Undangan
            </button>
        </div>
    </div>

    <?php if (!empty($data['background_video'])): ?>
        <div class="lp-video-bg"><video autoplay muted loop playsinline><source src="<?php echo esc_url($data['background_video']); ?>" type="video/mp4"></video></div>
    <?php endif; ?>

    <div class="lp-container">
        
        <?php if ($data['show_hero'] === '1'): ?>
        <section id="hero" class="lp-section lp-hero" style="background-image: url('<?php echo esc_url($foto_utama); ?>');" data-animation="<?php echo esc_attr($data['ani_hero'] ?: $data['animation']); ?>">
            <div class="lp-overlay"></div>
            <div class="lp-hero-content">
                <p class="lp-animate">The Wedding Of</p>
                <h1 class="lp-animate"><?php echo $nama_pasangan; ?></h1>
                <p class="lp-animate"><?php echo $datetime_obj ? $datetime_obj->format('d F Y') : ''; ?></p>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($data['show_story'] === '1' && has_post_format() && !empty(get_the_content())): ?>
        <section id="story" class="lp-section" data-animation="<?php echo esc_attr($data['ani_story'] ?: $data['animation']); ?>">
             <div class="lp-section-content lp-animate">
                <h2>Our Story</h2>
                <div class="lp-story-text"><?php echo wpautop(get_the_content()); ?></div>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($data['show_event'] === '1'): ?>
        <section id="event" class="lp-section" data-animation="<?php echo esc_attr($data['ani_event'] ?: $data['animation']); ?>">
            <div class="lp-section-content lp-animate">
                <h2>Save The Date</h2>
                <div id="lp-countdown" data-date="<?php echo esc_attr($iso_datetime); ?>">
                    <div><span id="days">00</span> Days</div> <div><span id="hours">00</span> Hours</div>
                    <div><span id="minutes">00</span> Minutes</div> <div><span id="seconds">00</span> Seconds</div>
                </div>
                <div class="lp-event-details">
                    <h3><?php echo esc_html($data['venue'] ?? 'Lokasi Acara'); ?></h3>
                    <p><?php echo esc_html($data['venue_addr'] ?? ''); ?></p>
                    <p><strong><?php echo $datetime_obj ? $datetime_obj->format('l, d F Y') : ''; ?></strong></p>
                    <p><?php echo esc_html($waktu_acara); ?></p>
                </div>
                <?php if (!empty($data['map_embed'])): ?><div class="lp-map"><?php echo $data['map_embed']; ?></div><?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($data['show_gallery'] === '1' && !empty($data['gallery_urls'])): ?>
        <section id="gallery" class="lp-section" data-animation="<?php echo esc_attr($data['ani_gallery'] ?: $data['animation']); ?>">
            <div class="lp-section-content lp-animate">
                <h2>Our Moments</h2>
                <div class="lp-gallery-grid">
                    <?php foreach ($data['gallery_urls'] as $img_url): ?>
                        <div class="lp-gallery-item"><img src="<?php echo esc_url($img_url); ?>" alt="Wedding Gallery"></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Bagian Kado Digital -->
        <?php if ($data['show_gift'] === '1' && (!empty($data['gift_account_number_1']) || !empty($data['gift_qris_image_url']))): ?>
        <section id="gift" class="lp-section" data-animation="<?php echo esc_attr($data['ani_gift'] ?: $data['animation']); ?>">
            <div class="lp-section-content lp-animate">
                <h2><?php echo esc_html($data['gift_title'] ?: 'Kirim Kado'); ?></h2>
                <?php if (!empty($data['gift_description'])): ?>
                    <p class="lp-gift-description"><?php echo nl2br(esc_html($data['gift_description'])); ?></p>
                <?php else: ?>
                    <p class="lp-gift-description">Doa restu Anda merupakan karunia yang sangat berarti bagi kami. Dan jika memberi adalah ungkapan tulus tanda kasih Anda, kami akan dengan senang hati menerimanya.</p>
                <?php endif; ?>

                <div class="lp-gift-container">
                    <?php if (!empty($data['gift_qris_image_url'])): ?>
                    <div class="lp-gift-card">
                        <h3>QRIS</h3>
                        <img src="<?php echo esc_url($data['gift_qris_image_url']); ?>" alt="QRIS Code" class="lp-qris-image">
                        <p>Scan QR Code untuk mengirim via e-wallet.</p>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($data['gift_account_number_1'])): ?>
                    <div class="lp-gift-card">
                        <h3><?php echo esc_html($data['gift_bank_name_1'] ?: 'Transfer Bank'); ?></h3>
                        <p>No. Rekening:</p>
                        <p class="lp-account-number" id="lp-account-number-1"><?php echo esc_html($data['gift_account_number_1']); ?></p>
                        <p>a.n. <?php echo esc_html($data['gift_account_name_1']); ?></p>
                        <button class="lp-copy-button" data-clipboard-target="#lp-account-number-1">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                            Salin
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

         <?php if ($data['show_rsvp'] === '1'): ?>
        <section id="rsvp" class="lp-section" data-animation="<?php echo esc_attr($data['ani_rsvp'] ?: $data['animation']); ?>">
            <div class="lp-section-content lp-animate">
                <h2>RSVP</h2>
                <p>Mohon konfirmasi kehadiran Anda untuk membantu kami mempersiapkan acara.</p>
                <form id="lp-rsvp-form">
                    <input type="hidden" name="post_id" value="<?php echo get_the_ID(); ?>">
                    <p><input type="text" name="name" placeholder="Nama Anda" required></p>
                    <p>
                        <select name="attend" required>
                            <option value="">Konfirmasi Kehadiran</option>
                            <option value="Hadir">Ya, saya akan hadir</option>
                            <option value="Tidak Hadir">Maaf, saya tidak bisa hadir</option>
                        </select>
                    </p>
                    <p><button type="submit">Kirim Konfirmasi</button></p>
                    <div id="lp-rsvp-message"></div>
                </form>
            </div>
        </section>
        <?php endif; ?>
    </div>

    <?php if (!empty($data['music_mp3'])): ?>
        <audio id="lp-music" loop><source src="<?php echo esc_url($data['music_mp3']); ?>" type="audio/mpeg"></audio>
        <button id="lp-music-toggle" class="paused">
            <svg class="pause-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="none" d="M0 0h24v24H0z"/><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
            <svg class="play-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M8 5v14l11-7z"/></svg>
        </button>
    <?php endif; ?>

    <?php wp_footer(); ?>
</body>
</html>

