<?php
/*
Plugin Name: LovePress
Description: LovePress — pembuat halaman undangan nikah/couple dengan template cantik, animasi, dan builder sederhana. Semua pengaturan di WP Admin, dan hanya halaman LovePress yang dirender di front-end.
Version: 2.2.0
Author: Jivi
Text Domain: lovepress
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LovePress {

    const CPT = 'lovepress_page';
    const RSVP_CPT = 'lovepress_rsvp';
    const OPT = 'lovepress_settings';
    const NS  = 'lovepress';

    public function __construct(){
        add_action('init', [$this,'register_cpt']);
        add_action('init', [$this,'register_rsvp_cpt']);
        add_action('add_meta_boxes', [$this,'add_meta_boxes']);
        add_action('save_post', [$this,'save_meta']);
        add_action('admin_menu', [$this,'admin_menu']);
        add_action('admin_enqueue_scripts', [$this,'admin_assets']);
        add_action('wp_enqueue_scripts', [$this,'frontend_assets']);
        add_filter('single_template', [$this,'single_template']);
        add_shortcode('lovepress', [$this,'shortcode']);
        add_action('wp_ajax_nopriv_lovepress_submit_rsvp', [$this,'ajax_submit_rsvp']);
        add_action('wp_ajax_lovepress_submit_rsvp', [$this,'ajax_submit_rsvp']);
        add_action('wp_ajax_lovepress_get_template_css', [$this, 'ajax_get_template_css']);
        add_action('wp_ajax_lovepress_save_template_css', [$this, 'ajax_save_template_css']);
        register_activation_hook(__FILE__, [__CLASS__,'activate']);
        register_deactivation_hook(__FILE__, [__CLASS__,'deactivate']);
    }

    public static function activate(){
        (new self)->register_cpt();
        (new self)->register_rsvp_cpt();
        $old = get_option('marriedpress_settings');
        if ($old && ! get_option(self::OPT)) {
            update_option(self::OPT, $old);
        }
        flush_rewrite_rules();
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }
    
    public function register_cpt(){
        $labels = [
            'name' => 'Undangan LovePress', 'singular_name' => 'Undangan LovePress',
            'add_new_item' => 'Tambah Undangan Baru', 'edit_item' => 'Edit Undangan',
            'new_item' => 'Undangan Baru', 'view_item' => 'Lihat Undangan',
            'all_items' => 'Semua Undangan',
        ];
        $args = [
            'labels' => $labels, 'public' => true, 'publicly_queryable' => true,
            'show_ui' => true, 'show_in_menu' => 'lovepress', 'show_in_rest' => true,
            'supports' => ['title','thumbnail','editor'],
            'rewrite' => ['slug' => 'love', 'with_front' => false],
            'menu_icon' => 'dashicons-heart',
        ];
        register_post_type(self::CPT, $args);
    }
    
    public function register_rsvp_cpt(){
        $labels = [ 'name' => 'LovePress RSVPs', 'singular_name' => 'RSVP', 'all_items' => 'Semua RSVP'];
        $args = [
            'labels' => $labels, 'public' => false, 'show_ui' => true,
            'show_in_menu' => 'lovepress', 'supports' => ['title'],
            'capability_type' => 'post', 'capabilities' => ['create_posts' => 'do_not_allow'],
            'map_meta_cap' => true,
        ];
        register_post_type(self::RSVP_CPT, $args);
    }

    public function admin_menu(){
        add_menu_page('LovePress', 'LovePress', 'manage_options', 'lovepress', [$this,'settings_page'], 'dashicons-heart', 27);
        add_submenu_page('lovepress','Tambah Undangan Baru','Tambah Baru','manage_options','post-new.php?post_type='.self::CPT);
        add_submenu_page('lovepress','Semua Undangan','Semua Undangan','manage_options','edit.php?post_type='.self::CPT);
        add_submenu_page('lovepress', 'Editor Template', 'Editor Template', 'manage_options', 'lovepress-template-editor', [$this, 'template_editor_page']);
        add_submenu_page('lovepress','Settings','Settings','manage_options','lovepress',[$this,'settings_page']);
    }

    public function admin_assets($hook){
        $screen = get_current_screen();
        if ( (strpos($hook, 'lovepress') !== false) || ($screen && $screen->post_type == self::CPT) ){
            wp_enqueue_style(self::NS.'-admin', plugins_url('assets/style.css', __FILE__), [], '2.2.0');
        }
        if ($screen && $screen->post_type == self::CPT) {
            wp_enqueue_media(); // Enqueue media uploader scripts
        }
        if ($hook == 'lovepress_page_lovepress-template-editor') {
            wp_enqueue_style('codemirror', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css');
            wp_enqueue_script('codemirror', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js', [], false, true);
            wp_enqueue_script('codemirror-css', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js', ['codemirror'], false, true);
        }
    }

    public function frontend_assets(){
        if (is_singular(self::CPT)){
            wp_enqueue_style(self::NS.'-style', plugins_url('assets/style.css', __FILE__), [], '2.2.0');
            wp_enqueue_style(self::NS.'-ani', plugins_url('assets/animations.css', __FILE__), [], '2.2.0');
            $tpl_slug = get_post_meta(get_the_ID(), 'template', true) ?: 'minimalist';
            $custom_css = get_option('lovepress_template_css_' . $tpl_slug, '');
            if (!empty($custom_css)) {
                wp_add_inline_style(self::NS.'-style', $custom_css);
            }
            wp_enqueue_script(self::NS.'-front', plugins_url('assets/frontend.js', __FILE__), ['jquery'], '2.2.0', true);
            wp_localize_script(self::NS.'-front', 'lovepress_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('lovepress_nonce')
            ]);
        }
    }
    
    public function add_meta_boxes(){
        add_meta_box('lp_details','LovePress — Detail Undangan',[$this,'meta_details'], self::CPT,'normal','high');
        add_meta_box('lp_gift', 'LovePress — Kado Digital', [$this, 'meta_gift'], self::CPT, 'normal', 'default');
        add_meta_box('lp_sections','LovePress — Bagian Halaman',[$this,'meta_sections'], self::CPT,'side','default');
        add_meta_box('lp_appearance','LovePress — Tampilan',[$this,'meta_appearance'], self::CPT,'side','default');
    }

    public function meta_details($post){
        $fields = [
            'couple_names' => ['label' => 'Nama Pasangan', 'type' => 'text'],
            'date' => ['label' => 'Tanggal Acara', 'type' => 'date'],
            'time' => ['label' => 'Waktu', 'type' => 'text'],
            'venue' => ['label' => 'Tempat', 'type' => 'text'],
            'venue_addr' => ['label' => 'Alamat', 'type' => 'text'],
            'map_embed' => ['label' => 'Embed Maps (iframe)', 'type' => 'textarea'],
            'music_mp3' => ['label' => 'URL Musik Latar (opsional)', 'type' => 'url'],
            'gallery' => ['label' => 'Galeri (ID/URL gambar, pisahkan koma)', 'type' => 'textarea']
        ];
        wp_nonce_field('lovepress_meta_save', 'lovepress_meta_nonce');
        echo '<div class="mplp-row">';
        foreach ($fields as $k=>$field){
            $val = get_post_meta($post->ID, $k, true);
            echo '<p><label><strong>'.$field['label'].'</strong><br/>';
            if ($field['type'] === 'textarea') {
                echo '<textarea class="widefat" rows="3" name="'.$k.'">'.esc_textarea($val).'</textarea>';
            } else {
                echo '<input type="'.$field['type'].'" class="widefat" name="'.$k.'" value="'.esc_attr($val).'"/>';
            }
            echo '</label></p>';
        }
        echo '</div>';
    }

    public function meta_gift($post) {
        $fields = [
            'gift_title' => ['label' => 'Judul Bagian Kado', 'type' => 'text', 'placeholder' => 'Kirim Kado'],
            'gift_description' => ['label' => 'Deskripsi Kado', 'type' => 'textarea', 'placeholder' => 'Doa restu Anda adalah karunia terindah. Namun jika memberi adalah ungkapan tulus Anda, kami akan menerimanya dengan senang hati.'],
            'gift_bank_name_1' => ['label' => 'Nama Bank', 'type' => 'text', 'placeholder' => 'Contoh: BCA'],
            'gift_account_number_1' => ['label' => 'No. Rekening', 'type' => 'text'],
            'gift_account_name_1' => ['label' => 'Atas Nama', 'type' => 'text'],
            'gift_qris_image' => ['label' => 'Gambar QRIS', 'type' => 'media'],
        ];
        echo '<div class="mplp-row">';
        foreach ($fields as $k => $field) {
            $val = get_post_meta($post->ID, $k, true);
            echo '<p><label><strong>' . $field['label'] . '</strong><br/>';
            if ($field['type'] === 'textarea') {
                echo '<textarea class="widefat" rows="3" name="' . $k . '" placeholder="' . ($field['placeholder'] ?? '') . '">' . esc_textarea($val) . '</textarea>';
            } else if ($field['type'] === 'media') {
                $image_url = $val ? wp_get_attachment_image_url($val, 'thumbnail') : '';
                echo '<div class="lp-media-uploader">';
                echo '<input type="hidden" name="' . $k . '" value="' . esc_attr($val) . '" class="lp-media-id">';
                echo '<div class="lp-media-preview">' . ($image_url ? '<img src="' . esc_url($image_url) . '">' : '') . '</div>';
                echo '<button type="button" class="button lp-upload-button">Pilih/Upload Gambar</button> ';
                echo '<button type="button" class="button lp-remove-button"' . (!$val ? ' style="display:none;"' : '') . '>Hapus Gambar</button>';
                echo '</div>';
            } else {
                echo '<input type="' . $field['type'] . '" class="widefat" name="' . $k . '" value="' . esc_attr($val) . '" placeholder="' . ($field['placeholder'] ?? '') . '"/>';
            }
            echo '</label></p>';
        }
        echo '</div>';
        ?>
        <script>
        jQuery(document).ready(function($){
            $('.lp-media-uploader').each(function(){
                var uploader = $(this);
                var uploadBtn = uploader.find('.lp-upload-button');
                var removeBtn = uploader.find('.lp-remove-button');
                var mediaIdInput = uploader.find('.lp-media-id');
                var previewContainer = uploader.find('.lp-media-preview');

                uploadBtn.on('click', function(e) {
                    e.preventDefault();
                    var frame = wp.media({
                        title: 'Pilih Gambar QRIS',
                        button: { text: 'Gunakan Gambar Ini' },
                        multiple: false
                    });
                    frame.on('select', function() {
                        var attachment = frame.state().get('selection').first().toJSON();
                        mediaIdInput.val(attachment.id);
                        previewContainer.html('<img src="' + attachment.sizes.thumbnail.url + '">');
                        removeBtn.show();
                    });
                    frame.open();
                });

                removeBtn.on('click', function(e) {
                    e.preventDefault();
                    mediaIdInput.val('');
                    previewContainer.html('');
                    $(this).hide();
                });
            });
        });
        </script>
        <?php
    }

    public function meta_sections($post){
        $sections = [
            'show_hero' => 'Bagian Hero', 'show_story' => 'Bagian Cerita',
            'show_gallery' => 'Galeri', 'show_event' => 'Detail Acara', 
            'show_rsvp' => 'RSVP', 'show_gift' => 'Kado Digital'
        ];
        echo '<div>';
        foreach ($sections as $k=>$label){
            $val = get_post_meta($post->ID, $k, true);
            $checked = empty($val) || $val === '1' ? 'checked' : '';
            echo '<p><label><input type="checkbox" name="'.$k.'" value="1" '.$checked.'/> '.$label.'</label></p>';
        }
        echo '</div>';
        $ani_opts = [''=>'(Default)','lp-ani-fade'=>'Fade','lp-ani-slide'=>'Slide','lp-ani-zoom'=>'Zoom','lp-ani-flip'=>'Flip'];
        echo '<hr/><p><strong>Animasi per Bagian</strong></p>';
        foreach (['hero'=>'Hero','event'=>'Event','gallery'=>'Gallery','story'=>'Cerita','rsvp'=>'RSVP','gift'=>'Kado'] as $key=>$label){
            $meta = esc_attr(get_post_meta($post->ID, 'ani_'.$key, true));
            echo '<p><label>'.$label.'<br/><select name="ani_'.$key.'" class="widefat">';
            foreach($ani_opts as $k=>$l){ $sel = ($meta===$k)?'selected':''; echo "<option value='$k' $sel>$l</option>"; }
            echo '</select></label></p>';
        }
    }
    
    public function meta_appearance($post){
        $ani = esc_attr(get_post_meta($post->ID, 'animation', true));
        $tpl = esc_attr(get_post_meta($post->ID, 'template', true));
        $primary = esc_attr(get_post_meta($post->ID, 'primary_color', true));
        $secondary = esc_attr(get_post_meta($post->ID, 'secondary_color', true));
        $bgvideo = esc_attr(get_post_meta($post->ID, 'background_video', true));
        $ani_opts = ['lp-ani-fade'=>'Fade','lp-ani-slide'=>'Slide','lp-ani-zoom'=>'Zoom','lp-ani-flip'=>'Flip'];
        $tpl_opts = self::get_available_templates();
        echo '<p><label><strong>Animasi</strong><br/><select name="animation" class="widefat">';
        foreach($ani_opts as $k=>$l){ $sel = $ani===$k?'selected':''; echo "<option value='$k' $sel>$l</option>"; }
        echo '</select></label></p>';
        echo '<p><label><strong>Template</strong><br/><select name="template" class="widefat">';
        foreach($tpl_opts as $k=>$l){ $sel = $tpl===$k?'selected':''; echo "<option value='$k' $sel>$l</option>"; }
        echo '</select></label></p>';
        echo '<p><label><strong>Warna Utama</strong><br/><input type="color" name="primary_color" value="'.($primary?:'#b83280').'"/></label></p>';
        echo '<p><label><strong>Warna Sekunder</strong><br/><input type="color" name="secondary_color" value="'.($secondary?:'#ffedd5').'"/></label></p>';
        echo '<p><label><strong>Background Video (URL mp4)</strong><br/><input type="url" class="widefat" name="background_video" value="'.$bgvideo.'" placeholder="https://...mp4"/></label></p>';
    }

    public function save_meta($post_id){
        if (!isset($_POST['lovepress_meta_nonce']) || !wp_verify_nonce($_POST['lovepress_meta_nonce'], 'lovepress_meta_save')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (get_post_type($post_id) !== self::CPT) return;
        if (!current_user_can('edit_post',$post_id)) return;
        
        $keys = [
            'couple_names','date','time','venue','venue_addr','music_mp3','gallery','animation','template','background_video',
            'show_hero','show_story','show_gallery','show_event','show_rsvp','show_gift',
            'ani_hero','ani_event','ani_gallery','ani_rsvp', 'ani_story', 'ani_gift',
            'gift_title', 'gift_description', 'gift_bank_name_1', 'gift_account_number_1', 'gift_account_name_1', 'gift_qris_image'
        ];
        
        foreach ($keys as $k){
            if (isset($_POST[$k])) {
                if (in_array($k, ['music_mp3', 'background_video'])) { update_post_meta($post_id, $k, esc_url_raw($_POST[$k])); }
                elseif (in_array($k, ['gallery', 'gift_description'])) { update_post_meta($post_id, $k, sanitize_textarea_field($_POST[$k])); }
                elseif ($k === 'gift_qris_image') { update_post_meta($post_id, $k, absint($_POST[$k])); }
                else { update_post_meta($post_id, $k, sanitize_text_field($_POST[$k])); }
            } else {
                if (strpos($k, 'show_') === 0) { update_post_meta($post_id, $k, '0'); }
            }
        }
        
        if(isset($_POST['primary_color'])) update_post_meta($post_id, 'primary_color', sanitize_hex_color($_POST['primary_color']));
        if(isset($_POST['secondary_color'])) update_post_meta($post_id, 'secondary_color', sanitize_hex_color($_POST['secondary_color']));
        if(isset($_POST['map_embed'])) update_post_meta($post_id, 'map_embed', wp_kses_post($_POST['map_embed']));
    }
    
    public function settings_page(){
        if (!current_user_can('manage_options')) return;
        if (isset($_POST['lovepress_save']) && check_admin_referer('lovepress_settings')) {
            $opt = [
                'default_animation' => sanitize_text_field($_POST['default_animation'] ?? 'lp-ani-fade'),
                'default_template'  => sanitize_key($_POST['default_template'] ?? 'elegant'),
            ];
            update_option(self::OPT, $opt);
            echo '<div class="notice notice-success is-dismissible"><p>Pengaturan disimpan.</p></div>';
        }
        $opt = get_option(self::OPT, ['default_animation'=>'lp-ani-fade','default_template'=>'elegant']);
        ?>
        <div class="wrap">
            <h1>LovePress — Pengaturan</h1>
            <form method="post">
                <?php wp_nonce_field('lovepress_settings'); ?>
                <table class="form-table">
                    <tr><th>Animasi Default</th><td>
                        <select name="default_animation">
                            <option value="lp-ani-fade" <?php selected($opt['default_animation'],'lp-ani-fade'); ?>>Fade</option>
                            <option value="lp-ani-slide" <?php selected($opt['default_animation'],'lp-ani-slide'); ?>>Slide</option>
                            <option value="lp-ani-zoom"  <?php selected($opt['default_animation'],'lp-ani-zoom'); ?>>Zoom</option>
                            <option value="lp-ani-flip"  <?php selected($opt['default_animation'],'lp-ani-flip'); ?>>Flip</option>
                        </select>
                    </td></tr>
                    <tr><th>Template Default</th><td>
                        <select name="default_template">
                            <?php foreach(self::get_available_templates() as $slug => $name): ?>
                                <option value="<?php echo esc_attr($slug); ?>" <?php selected($opt['default_template'], $slug); ?>><?php echo esc_html($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td></tr>
                </table>
                <?php submit_button('Simpan Perubahan', 'primary', 'lovepress_save'); ?>
            </form>
        </div>
        <?php
    }

    public static function get_available_templates() {
        // Implement logic to scan `templates` directory if you want it to be dynamic
        return ['elegant'=>'Elegant'];
    }

    public function single_template($template){
        if (is_singular(self::CPT)){
            $tpl_slug = get_post_meta(get_the_ID(), 'template', true);
            if(empty($tpl_slug)){
                $opt = get_option(self::OPT, ['default_template' => 'elegant']);
                $tpl_slug = $opt['default_template'];
            }
            $plugin_template = plugin_dir_path(__FILE__)."templates/invitation-{$tpl_slug}.php";
            if (file_exists($plugin_template)) return $plugin_template;
        }
        return $template;
    }

    public function shortcode($atts){
        $atts = shortcode_atts(['id'=>get_the_ID()], $atts);
        $pid = intval($atts['id']);
        if (!$pid || get_post_type($pid) !== self::CPT) return '';
        ob_start();
        global $post; $post = get_post($pid); setup_postdata($post);
        include $this->single_template('');
        wp_reset_postdata();
        return ob_get_clean();
    }

    public static function get_template_data($post_id){
        $keys = [
            'couple_names','date','time','venue','venue_addr','map_embed','music_mp3','gallery','animation','template','primary_color','secondary_color','background_video',
            'show_hero','show_story','show_gallery','show_event','show_rsvp','show_gift',
            'ani_hero','ani_story','ani_event','ani_gallery','ani_rsvp', 'ani_gift',
            'gift_title', 'gift_description', 'gift_bank_name_1', 'gift_account_number_1', 'gift_account_name_1', 'gift_qris_image'
        ];
        $data = [];
        foreach ($keys as $k) $data[$k] = get_post_meta($post_id, $k, true);
        
        $gallery_urls = [];
        if (!empty($data['gallery'])){
            $parts = array_map('trim', explode(',', $data['gallery']));
            foreach ($parts as $p){
                if (is_numeric($p)) { $url = wp_get_attachment_image_url(intval($p), 'large'); if ($url) $gallery_urls[] = $url; } 
                elseif (filter_var($p, FILTER_VALIDATE_URL)) { $gallery_urls[] = esc_url_raw($p); }
            }
        }
        $data['gallery_urls'] = $gallery_urls;

        $qris_id = absint($data['gift_qris_image']);
        $data['gift_qris_image_url'] = $qris_id ? wp_get_attachment_image_url($qris_id, 'medium') : '';
        
        $opt = get_option(self::OPT, []);
        if (empty($data['animation'])) $data['animation'] = $opt['default_animation'] ?? 'lp-ani-fade';
        if (empty($data['template']))  $data['template']  = $opt['default_template'] ?? 'elegant';
        
        foreach(['hero','story','gallery','event','rsvp', 'gift'] as $section){
            $data['show_'.$section] = get_post_meta($post_id, 'show_'.$section, true);
        }
        return $data;
    }

    public function ajax_submit_rsvp(){
        check_ajax_referer('lovepress_nonce', 'nonce');
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $attend = isset($_POST['attend']) ? sanitize_text_field($_POST['attend']) : '';
        if (!$post_id || empty($name) || empty($attend)) { wp_send_json_error(['msg'=>'Data tidak lengkap.']); return; }
        
        $rpost_id = wp_insert_post([
            'post_type' => self::RSVP_CPT,
            'post_title' => $name . ' (' . $attend . ') - Undangan #' . $post_id,
            'post_status' => 'publish',
        ]);
        if ($rpost_id && !is_wp_error($rpost_id)){
            update_post_meta($rpost_id, 'lp_rsvp_for_invitation', $post_id);
            update_post_meta($rpost_id, 'lp_rsvp_name', $name);
            update_post_meta($rpost_id, 'lp_rsvp_attendance', $attend);
            wp_send_json_success(['msg'=>'Terima kasih, konfirmasi Anda telah diterima.']);
        } else {
            wp_send_json_error(['msg'=>'Gagal menyimpan data.']);
        }
    }

    public function template_editor_page() {
        ?>
        <div class="wrap">
            <h1>Editor Template LovePress</h1>
            <p>Ubah tampilan template dengan menambahkan CSS kustom. Perubahan disimpan di database dan tidak mengubah file asli.</p>
            <div id="lp-editor-controls">
                <label for="lp-template-selector">Pilih Template untuk Diedit:</label>
                <select id="lp-template-selector">
                    <option value="">— Pilih Template —</option>
                    <?php foreach (self::get_available_templates() as $slug => $name) {
                        echo '<option value="' . esc_attr($slug) . '">' . esc_html($name) . '</option>';
                    } ?>
                </select>
            </div>
            <div id="lp-editor-container" style="display:none;">
                <textarea id="lp-css-editor"></textarea>
                <button id="lp-save-css" class="button button-primary">Simpan Perubahan</button>
                <span class="spinner"></span>
                <div id="lp-save-feedback"></div>
            </div>
        </div>
        <style>#lp-editor-controls { margin-bottom: 20px; } .CodeMirror { border: 1px solid #ddd; height: 500px; } #lp-save-css { margin-top: 15px; } #lp-save-feedback { margin-top: 10px; font-weight: bold; }</style>
        <script>
        jQuery(document).ready(function($) {
            var editor;
            $('#lp-template-selector').on('change', function() {
                var selectedTemplate = $(this).val();
                var container = $('#lp-editor-container');
                if (!selectedTemplate) { container.hide(); return; }
                if (!editor) { editor = CodeMirror.fromTextArea(document.getElementById('lp-css-editor'), { mode: 'css', lineNumbers: true }); }
                container.show(); editor.setValue('Memuat...');
                $.post(ajaxurl, { action: 'lovepress_get_template_css', template: selectedTemplate, nonce: '<?php echo wp_create_nonce("lp_editor_nonce"); ?>' })
                    .done(res => editor.setValue(res.success ? res.data : '// Gagal memuat.'));
            });
            $('#lp-save-css').on('click', function() {
                var btn = $(this), spinner = btn.next('.spinner'), feedback = $('#lp-save-feedback');
                btn.prop('disabled', true); spinner.addClass('is-active'); feedback.text('');
                $.post(ajaxurl, {
                    action: 'lovepress_save_template_css',
                    template: $('#lp-template-selector').val(),
                    css: editor.getValue(),
                    nonce: '<?php echo wp_create_nonce("lp_editor_nonce"); ?>'
                }).done(function(res) {
                    feedback.css('color', res.success ? 'green' : 'red').text(res.success ? 'Berhasil disimpan!' : 'Gagal menyimpan.');
                }).always(function() {
                    btn.prop('disabled', false); spinner.removeClass('is-active');
                    setTimeout(() => feedback.text(''), 3000);
                });
            });
        });
        </script>
        <?php
    }

    public function ajax_get_template_css() {
        check_ajax_referer('lp_editor_nonce', 'nonce');
        if (!current_user_can('manage_options')) { wp_send_json_error('Tidak ada izin.'); }
        $template = sanitize_key($_POST['template']);
        $css = get_option('lovepress_template_css_' . $template, "/* CSS Kustom untuk template " . esc_html($template) . " */");
        wp_send_json_success($css);
    }

    public function ajax_save_template_css() {
        check_ajax_referer('lp_editor_nonce', 'nonce');
        if (!current_user_can('manage_options')) { wp_send_json_error('Tidak ada izin.'); }
        $template = sanitize_key($_POST['template']);
        $css = wp_strip_all_tags($_POST['css']);
        update_option('lovepress_template_css_' . $template, $css);
        wp_send_json_success('Disimpan!');
    }
}

new LovePress();

