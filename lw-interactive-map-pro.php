<?php
/**
 * Plugin Name:       LW Interactive Map Pro
 * Plugin URI:        https://hoikylangthang.io.vn
 * Description:       Bản đồ hành trình chuyên nghiệp.
 * Version:           0.0.3-05
 * Author:            Nhut Nguyen ft Gemini Pro AI
 * Text Domain:       lw-map
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define Constants
define( 'LW_MAP_VERSION', '0.0.3-05' );
define( 'LW_MAP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LW_MAP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include Helpers
require_once LW_MAP_PLUGIN_DIR . 'includes/lw-map-helpers.php';

// =================================================================
// TÍCH HỢP AUTO UPDATE TỪ GITHUB (PLUGIN UPDATE CHECKER)
// =================================================================
// Đảm bảo bạn đã tải thư viện và đặt vào: lw-map/includes/plugin-update-checker/
if ( file_exists( LW_MAP_PLUGIN_DIR . 'includes/plugin-update-checker/plugin-update-checker.php' ) ) {
    
    require_once LW_MAP_PLUGIN_DIR . 'includes/plugin-update-checker/plugin-update-checker.php';
    
    // Gọi trực tiếp namespace đầy đủ để tránh lỗi cú pháp trong lệnh if
    $myUpdateChecker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        'https://github.com/nhockool1002/lw-map', 
        __FILE__,
        'lw-map'
    );
}

// Include Admin & Public Classes
require_once LW_MAP_PLUGIN_DIR . 'admin/class-lw-map-admin.php';
require_once LW_MAP_PLUGIN_DIR . 'public/class-lw-map-public.php';

// Initialize Plugin
function run_lw_map_pro() {
    // Init Admin
    $plugin_admin = new LW_Map_Admin();
    $plugin_admin->init();

    // Init Public
    $plugin_public = new LW_Map_Public();
    $plugin_public->init();
}

run_lw_map_pro();
