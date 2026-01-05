<?php
/**
 * Plugin Name:       LW Interactive Map Pro
 * Plugin URI:        https://hoikylangthang.io.vn
 * Description:       Bản đồ hành trình chuyên nghiệp.
 * Version:           0.0.1
 * Author:            Gemini AI
 * Text Domain:       lw-map
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

[cite_start]// Define Constants [cite: 380]
define( 'LW_MAP_VERSION', '0.0.1' );
define( 'LW_MAP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LW_MAP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

[cite_start]// Include Helpers [cite: 381]
require_once LW_MAP_PLUGIN_DIR . 'includes/lw-map-helpers.php';

// =================================================================
// TÍCH HỢP AUTO UPDATE TỪ GITHUB (PLUGIN UPDATE CHECKER)
// =================================================================
// Đảm bảo bạn đã tải thư viện và đặt vào: lw-map/includes/plugin-update-checker/
if ( file_exists( LW_MAP_PLUGIN_DIR . 'includes/plugin-update-checker/plugin-update-checker.php' ) ) {
    
    require_once LW_MAP_PLUGIN_DIR . 'includes/plugin-update-checker/plugin-update-checker.php';
    
    // Sử dụng namespace của thư viện v5
    use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

    $myUpdateChecker = PucFactory::buildUpdateChecker(
        'https://github.com/nhockool1002/lw-map', // <--- THAY ĐỔI ĐƯỜNG DẪN NÀY
        __FILE__,
        'lw-map'
    );

    // Cấu hình (Tùy chọn): Nếu repo là Private, bỏ comment dòng dưới và điền Token
    // $myUpdateChecker->setAuthentication('YOUR_GITHUB_ACCESS_TOKEN');

    // Cấu hình (Tùy chọn): Branch mặc định là master/main nếu không dùng Release Tags
    // $myUpdateChecker->setBranch('main');
}

[cite_start]// Include Admin & Public Classes [cite: 382]
require_once LW_MAP_PLUGIN_DIR . 'admin/class-lw-map-admin.php';
require_once LW_MAP_PLUGIN_DIR . 'public/class-lw-map-public.php';

[cite_start]// Initialize Plugin [cite: 382]
function run_lw_map_pro() {
	// Init Admin
	$plugin_admin = new LW_Map_Admin();
	$plugin_admin->init();

	// Init Public
	$plugin_public = new LW_Map_Public();
	$plugin_public->init();
}

run_lw_map_pro();