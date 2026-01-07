<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function lw_get_all_icons() {
    $defaults = [
        ['name' => 'Blue (Mặc định)',   'url' => 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png', 'is_default' => true],
        ['name' => 'Red (Nổi bật)',      'url' => 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png', 'is_default' => true],
        ['name' => 'Green (Thiên nhiên)','url' => 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png', 'is_default' => true],
        ['name' => 'Gold (Đặc biệt)',    'url' => 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-gold.png', 'is_default' => true],
        ['name' => 'Violet (Mộng mơ)',   'url' => 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-violet.png', 'is_default' => true],
    ];
    $customs = get_option('lw_map_icons', []);
    if (!is_array($customs)) $customs = [];
    return array_merge($defaults, $customs);
}

function lw_get_map_themes() {
    return [
        'osm' => ['name' => 'OpenStreetMap (Chuẩn)', 'url' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'],
        'carto_light' => ['name' => 'CartoDB Positron (Sáng)', 'url' => 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png'],
        'carto_dark' => ['name' => 'CartoDB Dark Matter (Tối)', 'url' => 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'],
        'esri_sat' => ['name' => 'Esri World Imagery (Vệ tinh)', 'url' => 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}']
    ];
}

function lw_get_gradients() {
    return [
        // Original 4 gradients
        'default' => ['name' => 'Ocean Blue', 'css' => 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)', 'hex' => '#2563eb'],
        'sunset'  => ['name' => 'Sunset Orange', 'css' => 'linear-gradient(135deg, #f59e0b 0%, #ea580c 100%)', 'hex' => '#ea580c'],
        'nature'  => ['name' => 'Lush Green', 'css' => 'linear-gradient(135deg, #10b981 0%, #059669 100%)', 'hex' => '#059669'],
        'coffee'  => ['name' => 'Coffee Bean', 'css' => 'linear-gradient(135deg, #78350f 0%, #451a03 100%)', 'hex' => '#78350f'],
        
        // Blue Variations
        'blue1' => ['name' => 'Sky Blue', 'css' => 'linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%)', 'hex' => '#0284c7'],
        'blue2' => ['name' => 'Deep Blue', 'css' => 'linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%)', 'hex' => '#1e3a8a'],
        'blue3' => ['name' => 'Azure Blue', 'css' => 'linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%)', 'hex' => '#3b82f6'],
        'blue4' => ['name' => 'Navy Blue', 'css' => 'linear-gradient(135deg, #1e3a8a 0%, #1e1b4b 100%)', 'hex' => '#1e1b4b'],
        'blue5' => ['name' => 'Cyan Blue', 'css' => 'linear-gradient(135deg, #06b6d4 0%, #0891b2 100%)', 'hex' => '#0891b2'],
        
        // Purple Variations
        'purple1' => ['name' => 'Royal Purple', 'css' => 'linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%)', 'hex' => '#7c3aed'],
        'purple2' => ['name' => 'Lavender', 'css' => 'linear-gradient(135deg, #a78bfa 0%, #8b5cf6 100%)', 'hex' => '#8b5cf6'],
        'purple3' => ['name' => 'Deep Purple', 'css' => 'linear-gradient(135deg, #6d28d9 0%, #5b21b6 100%)', 'hex' => '#5b21b6'],
        'purple4' => ['name' => 'Violet Dream', 'css' => 'linear-gradient(135deg, #9333ea 0%, #7e22ce 100%)', 'hex' => '#7e22ce'],
        'purple5' => ['name' => 'Plum Purple', 'css' => 'linear-gradient(135deg, #a855f7 0%, #9333ea 100%)', 'hex' => '#9333ea'],
        
        // Pink & Rose Variations
        'pink1' => ['name' => 'Rose Pink', 'css' => 'linear-gradient(135deg, #f43f5e 0%, #e11d48 100%)', 'hex' => '#e11d48'],
        'pink2' => ['name' => 'Cherry Blossom', 'css' => 'linear-gradient(135deg, #ec4899 0%, #db2777 100%)', 'hex' => '#db2777'],
        'pink3' => ['name' => 'Bubblegum', 'css' => 'linear-gradient(135deg, #f472b6 0%, #ec4899 100%)', 'hex' => '#ec4899'],
        'pink4' => ['name' => 'Fuchsia', 'css' => 'linear-gradient(135deg, #d946ef 0%, #c026d3 100%)', 'hex' => '#c026d3'],
        'pink5' => ['name' => 'Magenta', 'css' => 'linear-gradient(135deg, #e879f9 0%, #d946ef 100%)', 'hex' => '#d946ef'],
        
        // Red Variations
        'red1' => ['name' => 'Fire Red', 'css' => 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)', 'hex' => '#dc2626'],
        'red2' => ['name' => 'Crimson', 'css' => 'linear-gradient(135deg, #dc2626 0%, #b91c1c 100%)', 'hex' => '#b91c1c'],
        'red3' => ['name' => 'Scarlet', 'css' => 'linear-gradient(135deg, #f87171 0%, #ef4444 100%)', 'hex' => '#ef4444'],
        'red4' => ['name' => 'Ruby Red', 'css' => 'linear-gradient(135deg, #be123c 0%, #9f1239 100%)', 'hex' => '#9f1239'],
        'red5' => ['name' => 'Burgundy', 'css' => 'linear-gradient(135deg, #991b1b 0%, #7f1d1d 100%)', 'hex' => '#7f1d1d'],
        
        // Orange Variations
        'orange1' => ['name' => 'Amber', 'css' => 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)', 'hex' => '#d97706'],
        'orange2' => ['name' => 'Tangerine', 'css' => 'linear-gradient(135deg, #fb923c 0%, #f97316 100%)', 'hex' => '#f97316'],
        'orange3' => ['name' => 'Peach', 'css' => 'linear-gradient(135deg, #fdba74 0%, #fb923c 100%)', 'hex' => '#fb923c'],
        'orange4' => ['name' => 'Coral', 'css' => 'linear-gradient(135deg, #ff7849 0%, #ff6b35 100%)', 'hex' => '#ff6b35'],
        'orange5' => ['name' => 'Pumpkin', 'css' => 'linear-gradient(135deg, #ea580c 0%, #c2410c 100%)', 'hex' => '#c2410c'],
        
        // Yellow & Gold Variations
        'yellow1' => ['name' => 'Luxury Gold', 'css' => 'linear-gradient(135deg, #eab308 0%, #ca8a04 100%)', 'hex' => '#ca8a04'],
        'yellow2' => ['name' => 'Sunshine', 'css' => 'linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%)', 'hex' => '#f59e0b'],
        'yellow3' => ['name' => 'Honey', 'css' => 'linear-gradient(135deg, #fcd34d 0%, #fbbf24 100%)', 'hex' => '#fbbf24'],
        'yellow4' => ['name' => 'Lemon', 'css' => 'linear-gradient(135deg, #fde047 0%, #facc15 100%)', 'hex' => '#facc15'],
        'yellow5' => ['name' => 'Amber Gold', 'css' => 'linear-gradient(135deg, #a16207 0%, #854d0e 100%)', 'hex' => '#854d0e'],
        
        // Green Variations
        'green1' => ['name' => 'Emerald', 'css' => 'linear-gradient(135deg, #10b981 0%, #059669 100%)', 'hex' => '#059669'],
        'green2' => ['name' => 'Forest', 'css' => 'linear-gradient(135deg, #16a34a 0%, #15803d 100%)', 'hex' => '#15803d'],
        'green3' => ['name' => 'Mint', 'css' => 'linear-gradient(135deg, #34d399 0%, #10b981 100%)', 'hex' => '#10b981'],
        'green4' => ['name' => 'Lime', 'css' => 'linear-gradient(135deg, #84cc16 0%, #65a30d 100%)', 'hex' => '#65a30d'],
        'green5' => ['name' => 'Jade', 'css' => 'linear-gradient(135deg, #059669 0%, #047857 100%)', 'hex' => '#047857'],
        
        // Teal & Cyan Variations
        'teal1' => ['name' => 'Teal Ocean', 'css' => 'linear-gradient(135deg, #14b8a6 0%, #0d9488 100%)', 'hex' => '#0d9488'],
        'teal2' => ['name' => 'Turquoise', 'css' => 'linear-gradient(135deg, #2dd4bf 0%, #14b8a6 100%)', 'hex' => '#14b8a6'],
        'teal3' => ['name' => 'Aqua', 'css' => 'linear-gradient(135deg, #5eead4 0%, #2dd4bf 100%)', 'hex' => '#2dd4bf'],
        'teal4' => ['name' => 'Cyan Sky', 'css' => 'linear-gradient(135deg, #06b6d4 0%, #0891b2 100%)', 'hex' => '#0891b2'],
        'teal5' => ['name' => 'Ocean Teal', 'css' => 'linear-gradient(135deg, #0f766e 0%, #0d9488 100%)', 'hex' => '#0d9488'],
        
        // Indigo Variations
        'indigo1' => ['name' => 'Deep Indigo', 'css' => 'linear-gradient(135deg, #6366f1 0%, #4f46e5 100%)', 'hex' => '#4f46e5'],
        'indigo2' => ['name' => 'Royal Indigo', 'css' => 'linear-gradient(135deg, #818cf8 0%, #6366f1 100%)', 'hex' => '#6366f1'],
        'indigo3' => ['name' => 'Midnight Indigo', 'css' => 'linear-gradient(135deg, #4338ca 0%, #3730a3 100%)', 'hex' => '#3730a3'],
        'indigo4' => ['name' => 'Electric Indigo', 'css' => 'linear-gradient(135deg, #6366f1 0%, #5b21b6 100%)', 'hex' => '#5b21b6'],
        'indigo5' => ['name' => 'Violet Indigo', 'css' => 'linear-gradient(135deg, #7c3aed 0%, #6366f1 100%)', 'hex' => '#6366f1'],
        
        // Gray & Slate Variations
        'gray1' => ['name' => 'Cool Slate', 'css' => 'linear-gradient(135deg, #64748b 0%, #475569 100%)', 'hex' => '#475569'],
        'gray2' => ['name' => 'Charcoal', 'css' => 'linear-gradient(135deg, #475569 0%, #334155 100%)', 'hex' => '#334155'],
        'gray3' => ['name' => 'Midnight', 'css' => 'linear-gradient(135deg, #334155 0%, #1e293b 100%)', 'hex' => '#1e293b'],
        'gray4' => ['name' => 'Dark Night', 'css' => 'linear-gradient(135deg, #1e293b 0%, #0f172a 100%)', 'hex' => '#0f172a'],
        'gray5' => ['name' => 'Storm Gray', 'css' => 'linear-gradient(135deg, #94a3b8 0%, #64748b 100%)', 'hex' => '#64748b'],
        
        // Special Multi-Color Gradients
        'special1' => ['name' => 'Sunset Sky', 'css' => 'linear-gradient(135deg, #f59e0b 0%, #ef4444 50%, #ec4899 100%)', 'hex' => '#ec4899'],
        'special2' => ['name' => 'Ocean Sunset', 'css' => 'linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #ec4899 100%)', 'hex' => '#ec4899'],
        'special3' => ['name' => 'Tropical', 'css' => 'linear-gradient(135deg, #10b981 0%, #06b6d4 50%, #3b82f6 100%)', 'hex' => '#3b82f6'],
        'special4' => ['name' => 'Aurora', 'css' => 'linear-gradient(135deg, #10b981 0%, #14b8a6 50%, #06b6d4 100%)', 'hex' => '#06b6d4'],
        'special5' => ['name' => 'Rainbow', 'css' => 'linear-gradient(135deg, #ef4444 0%, #f59e0b 25%, #eab308 50%, #10b981 75%, #3b82f6 100%)', 'hex' => '#3b82f6'],
        'special6' => ['name' => 'Purple Dream', 'css' => 'linear-gradient(135deg, #8b5cf6 0%, #ec4899 50%, #f43f5e 100%)', 'hex' => '#f43f5e'],
        'special7' => ['name' => 'Blue Lagoon', 'css' => 'linear-gradient(135deg, #06b6d4 0%, #3b82f6 50%, #6366f1 100%)', 'hex' => '#6366f1'],
        'special8' => ['name' => 'Forest Mist', 'css' => 'linear-gradient(135deg, #10b981 0%, #14b8a6 50%, #06b6d4 100%)', 'hex' => '#06b6d4'],
        'special9' => ['name' => 'Fire & Ice', 'css' => 'linear-gradient(135deg, #ef4444 0%, #f59e0b 50%, #06b6d4 100%)', 'hex' => '#06b6d4'],
        'special10' => ['name' => 'Cosmic', 'css' => 'linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%)', 'hex' => '#d946ef'],
        
        // Additional Unique Gradients
        'unique1' => ['name' => 'Sapphire', 'css' => 'linear-gradient(135deg, #0ea5e9 0%, #0284c7 50%, #0369a1 100%)', 'hex' => '#0369a1'],
        'unique2' => ['name' => 'Amethyst', 'css' => 'linear-gradient(135deg, #a855f7 0%, #9333ea 50%, #7e22ce 100%)', 'hex' => '#7e22ce'],
        'unique3' => ['name' => 'Emerald Forest', 'css' => 'linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%)', 'hex' => '#047857'],
        'unique4' => ['name' => 'Rose Gold', 'css' => 'linear-gradient(135deg, #f43f5e 0%, #ec4899 50%, #f59e0b 100%)', 'hex' => '#f59e0b'],
        'unique5' => ['name' => 'Mint Chocolate', 'css' => 'linear-gradient(135deg, #34d399 0%, #10b981 50%, #78350f 100%)', 'hex' => '#78350f'],
        'unique6' => ['name' => 'Lavender Sunset', 'css' => 'linear-gradient(135deg, #a78bfa 0%, #c084fc 50%, #f472b6 100%)', 'hex' => '#f472b6'],
        'unique7' => ['name' => 'Ocean Breeze', 'css' => 'linear-gradient(135deg, #06b6d4 0%, #3b82f6 50%, #6366f1 100%)', 'hex' => '#6366f1'],
        'unique8' => ['name' => 'Cherry Blossom', 'css' => 'linear-gradient(135deg, #fda4af 0%, #fb7185 50%, #f43f5e 100%)', 'hex' => '#f43f5e'],
        'unique9' => ['name' => 'Tropical Paradise', 'css' => 'linear-gradient(135deg, #fbbf24 0%, #f59e0b 50%, #10b981 100%)', 'hex' => '#10b981'],
        'unique10' => ['name' => 'Midnight Blue', 'css' => 'linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #2563eb 100%)', 'hex' => '#2563eb'],
        'unique11' => ['name' => 'Peachy Keen', 'css' => 'linear-gradient(135deg, #fdba74 0%, #fb923c 50%, #f97316 100%)', 'hex' => '#f97316'],
        'unique12' => ['name' => 'Mystic Purple', 'css' => 'linear-gradient(135deg, #7c3aed 0%, #6d28d9 50%, #5b21b6 100%)', 'hex' => '#5b21b6'],
        'unique13' => ['name' => 'Spring Green', 'css' => 'linear-gradient(135deg, #84cc16 0%, #65a30d 50%, #4d7c0f 100%)', 'hex' => '#4d7c0f'],
        'unique14' => ['name' => 'Coral Reef', 'css' => 'linear-gradient(135deg, #fb7185 0%, #f43f5e 50%, #e11d48 100%)', 'hex' => '#e11d48'],
        'unique15' => ['name' => 'Electric Blue', 'css' => 'linear-gradient(135deg, #60a5fa 0%, #3b82f6 50%, #2563eb 100%)', 'hex' => '#2563eb'],
        'unique16' => ['name' => 'Golden Hour', 'css' => 'linear-gradient(135deg, #fbbf24 0%, #f59e0b 50%, #ea580c 100%)', 'hex' => '#ea580c'],
    ];
}

function lw_can_access_settings() {
    $current_user_id = get_current_user_id();
    if (current_user_can('administrator') || $current_user_id == 1) return true;
    $allowed_users = get_option('lw_map_allowed_users', []);
    return in_array($current_user_id, $allowed_users);
}

function lw_prepare_points_data($points) {
    if (empty($points)) return [];
    
    foreach($points as &$p) {
        $p['has_post'] = false;
        $p['thumb'] = ''; 
        $p['date'] = ''; // Đảm bảo date luôn được khởi tạo
        $p['excerpt'] = ''; // Đảm bảo excerpt luôn được khởi tạo
        
        if(!empty($p['link'])) {
            $post_id = url_to_postid($p['link']);
            if($post_id) {
                $p['has_post'] = true;
                
                // Lấy post object trực tiếp, không trigger filters
                $post = get_post($post_id);
                if ($post) {
                    // Lấy date an toàn
                    $p['date'] = date_i18n('d/m/Y', strtotime($post->post_date));
                    
                    // Lấy excerpt trực tiếp từ post object để tránh trigger filters
                    // Không sử dụng get_the_excerpt() vì nó có thể trigger the_content filter
                    if (!empty($post->post_excerpt)) {
                        $excerpt = $post->post_excerpt;
                    } else {
                        // Lấy từ post_content và strip tags
                        $excerpt = strip_tags($post->post_content);
                    }
                    $p['excerpt'] = wp_trim_words($excerpt, 20, '...');
                    
                    // Lấy thumbnail
                    $thumb = get_the_post_thumbnail_url($post_id, 'medium'); 
                    if(!$thumb) $thumb = 'https://via.placeholder.com/300x150/e0e0e0/999999?text=No+Image';
                    $p['thumb'] = $thumb;
                }
            }
        }
    }
    return $points;
}

function lw_get_post_list_for_js() {
    $args = ['numberposts' => -1, 'post_status' => 'publish', 'post_type' => 'post', 'orderby' => 'date', 'order' => 'DESC'];
    $posts = get_posts($args);
    $data = [];
    foreach ($posts as $p) {
        $data[] = ['id' => $p->ID, 'title' => $p->post_title, 'link' => get_permalink($p->ID)];
    }
    return $data;
}