<?php
/*
Plugin Name: Minapper Wechat Search 微慕搜索助手
Plugin URI: https://www.minapper.com
Description: 提交微信小程序的页面和内容到微信搜一搜
Version: 1.2
Author: jianbo
Author URI: https://www.watch-life.net
License: GPL v3
WordPress requires at least: 4.7.1
*/

const MINAPPER_WECHAT_SEARCH_PLUGIN_NAME='minapper-wechat-search';
define('MINAPPER_WECHAT_SEARCH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MINAPPER_WECHAT_SEARCH_PLUGIN_URL',plugins_url(MINAPPER_WECHAT_SEARCH_PLUGIN_NAME.'/', dirname(__FILE__)));

include(MINAPPER_WECHAT_SEARCH_PLUGIN_DIR . 'includes/minapper-wechat-config.php');
include(MINAPPER_WECHAT_SEARCH_PLUGIN_DIR . 'includes/minapper-wechat-api.php');
include(MINAPPER_WECHAT_SEARCH_PLUGIN_DIR . 'includes/minapper-util.php');
include(MINAPPER_WECHAT_SEARCH_PLUGIN_DIR . 'includes/minapper-posts-search-list.php');


if ( ! class_exists( 'MinapperWechatSearch' ) ) {

    class MinapperWechatSearch {
        public $wxapi = null;
        public function __construct() { 
            
        
            
            // 管理配置 
            if ( is_admin() ) {             
                
                //new WP_Category_Config();
            add_action( 'admin_enqueue_scripts', 'minapper_wechat_search_admin_style', 9999 );
               add_action('admin_menu', 'minapper_wechat_search_menu');       
               add_filter( 'plugin_action_links', 'minapper_wechat_search_action_links', 10, 2 );
             
                 
            }
            $this->wxapi = new Minapper_Wechat_API();


        }

        

    }


    // 实例化并加入全局变量
    $GLOBALS['MinapperWechatSearch'] = new MinapperWechatSearch();
    
    function MWS() {
        
        if( ! isset( $GLOBALS['MinapperWechatSearch'] ) ) {
            $GLOBALS['MinapperWechatSearch'] = new MinapperWechatSearch();
        }
        
        return $GLOBALS['MinapperWechatSearch'];
    }

    function minapper_wechat_search_admin_style() {
		wp_enqueue_style( 'minapper-wechat-search-admin-css', MINAPPER_WECHAT_SEARCH_PLUGIN_URL. 'includes/css/menu.css', array(),'4.0.4' );
	}

    function minapper_wechat_search_action_links( $links, $file ) {
        if ( plugin_basename( __FILE__ ) !== $file ) {
            return $links;
        }

        $settings_link = '<a href="https://www.minapper.com/" target="_blank"> <span style="color:#d54e21; font-weight:bold;">' . esc_html__( '微慕增强版', 'REST API TO MiniProgram' ) . '</span></a>';

        array_unshift( $links, $settings_link );

        $settings_link = '<a href="https://www.minapper.com/" target="_blank"> <span style="color:#d54e21; font-weight:bold;">' . esc_html__( '微慕专业版', 'REST API TO MiniProgram' ) . '</span></a>';

        array_unshift( $links, $settings_link );


        $settings_link = '<a href="https://www.minapper.com/" target="_blank"> <span style="color:green; font-weight:bold;">' . esc_html__( '技术支持', 'REST API TO MiniProgram' ) . '</span></a>';

        array_unshift( $links, $settings_link );

        $settings_link = '<a href="admin.php?page=mianpper_wechat_search_slug">' . esc_html__( '设置', 'REST API TO MiniProgram' ) . '</a>';

        array_unshift( $links, $settings_link );

        return $links;
    }

}
