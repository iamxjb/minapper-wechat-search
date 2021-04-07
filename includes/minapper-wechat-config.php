<?php 

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function minapper_wechat_search_menu() {

    //add_menu_page('微慕搜索助手', '微慕搜索助手', 'administrator', 'mianpper_wechat_search_slug','mianpper_wechat_search_page', plugins_url(MINAPPER_WECHAT_SEARCH_PLUGIN_NAME.'/images/icon16.png'), 99);
    add_menu_page('微慕搜索助手', '微慕搜索助手', 'administrator', 'mianpper_wechat_search_slug','mianpper_wechat_search_page', 'none', 99);
    add_submenu_page('mianpper_wechat_search_slug', "基础设置", "基础设置", "administrator", 'mianpper_wechat_search_slug','mianpper_wechat_search_page');
    add_submenu_page('mianpper_wechat_search_slug', "提交微信搜索", "提交微信搜索", "administrator", 'post_wechat_search_slug', 'post_wechat_search_page',100);
   // 调用注册设置函数
   add_action( 'admin_init', 'register_minapper_wechat_search_settings' );

}


function get_MinapperWechatSearch_jquery_source() {
        $url = plugins_url('',__FILE__); 
        wp_enqueue_style("tabs", plugins_url().'/'.MINAPPER_WECHAT_SEARCH_PLUGIN_NAME.'/includes/js/tab/tabs.css', false, "1.0", "all");
        wp_enqueue_script("tabs", plugins_url().'/'.MINAPPER_WECHAT_SEARCH_PLUGIN_NAME.'/includes/js/tab/tabs.min.js', false, "1.0");
        wp_enqueue_script('rawscript', plugins_url().'/'.MINAPPER_WECHAT_SEARCH_PLUGIN_NAME.'/includes/js/script.js', false, '1.0');
        if ( function_exists( 'wp_enqueue_media' ) ) {
            wp_enqueue_media();
        }    
    }


function register_minapper_wechat_search_settings() {
    // 注册设置
    register_setting( 'MinapperWechatSearch-group', 'mws_appid' );
    register_setting( 'MinapperWechatSearch-group', 'mws_secret' );
    register_setting( 'MinapperWechatSearch-group', 'mws_content_search_category_id');
    register_setting( 'MinapperWechatSearch-group', 'mws_miniprogram_cate_path');
    register_setting( 'MinapperWechatSearch-group', 'mws_miniprogram_cate_id'); 
    
    register_setting( 'MinapperWechatSearch-group', 'mws_miniprogram_post_id');
    register_setting( 'MinapperWechatSearch-group', 'mws_miniprogram_post_path'); 
    



    
    
}

function mianpper_wechat_search_page() {
?>
<div class="wrap">

<h2>微慕搜索助手</h2>


<p>Minapper wachat search by <a href="https://www.minapper.com" target="_blank">微慕</a>.
<?php

if (!empty($_REQUEST['settings-updated']))
{
    echo '<div id="message" class="updated fade"><p><strong>设置已保存</strong></p></div>';

} 

if (version_compare(PHP_VERSION, '5.6.0', '<=') )
{
    
    echo '<div class="notice notice-error is-dismissible">
    <p><font color="red">提示：php版本小于5.6.0, 插件程序将无法正常使用,当前系统的php版本是:'.PHP_VERSION.'</font></p>
    </div>';

}
?>
<form method="post" action="options.php">
    <div class="responsive-tabs">
    <?php settings_fields( 'MinapperWechatSearch-group' ); ?>
    <?php do_settings_sections( 'MinapperWechatSearch-group' ); ?>
    <div class="responsive-tabs">
    <h2>设置</h2>
    <div class="section">
        <table class="form-table">
            <tr valign="top">
            <th scope="row">小程序AppID</th>
            <td><input type="text" name="mws_appid" style="width:400px; height:40px" value="<?php echo esc_attr( get_option('mws_appid') ); ?>" />* </td>
            </tr>
             
            <tr valign="top">
            <th scope="row">AppSecret</th>
            <td><input type="text" name="mws_secret" style="width:400px; height:40px" value="<?php echo esc_attr( get_option('mws_secret') ); ?>" /></td>
            </tr>

            <tr valign="top">
            <th scope="row">小程序文章详情页面路径</th>
            <td><input type="text" name="mws_miniprogram_post_path" placeholder="例如:pages/dedail/detail"  style="width:400px; height:40px" value="<?php echo esc_attr( get_option('mws_miniprogram_post_path') ); ?>" />
            <br /><p style="color: #959595 ; display:inline">小程序端文章详情的页面路径，例如微慕小程序文章详情页路径为：</p><p style="color: red; display:inline"><b>pages/detail/detail</b></p>
            </td>
 
            </tr>

            <tr valign="top">
            <th scope="row">小程序文章详情id参数名</th>
            <td><input type="text" name="mws_miniprogram_post_id" placeholder="例如:id"  style="width:400px; height:40px" value="<?php echo esc_attr( get_option('mws_miniprogram_post_id') ); ?>" />(只支持英文字母字符)
            <br /><p style="color: #959595 ; display:inline">小程序端跳转文章详情使用参数名，例如微慕小程序文章详情完整路径为：pages/detail/detail?id=888,那么此处填写参数名为：</p><p style="color: red; display:inline"><b>id</b></p>
            </td>

            <tr valign="top">
            <th scope="row">小程序分类页面路径</th>
            <td><input type="text" name="mws_miniprogram_cate_path" placeholder="例如:pages/list/list"  style="width:400px; height:40px" value="<?php echo esc_attr( get_option('mws_miniprogram_cate_path') ); ?>" />
            <br /><p style="color: #959595 ; display:inline">小程序端分类的页面路径，例如微慕小程序分类页面路径为：</p><p style="color: red; display:inline"><b>pages/list/list</b></p>
            </td>
 
            </tr>

            <tr valign="top">
            <th scope="row">小程序分类id参数名</th>
            <td><input type="text" name="mws_miniprogram_cate_id" placeholder="例如:categoryID"  style="width:400px; height:40px" value="<?php echo esc_attr( get_option('mws_miniprogram_cate_id') ); ?>" />(只支持英文字母字符)
            <br /><p style="color: #959595 ; display:inline">小程序端跳转文章分类使用参数名，例如微慕小程序文章详情完整路径为：pages/list/list?categoryID=888,那么此处填写参数名为：</p><p style="color: red; display:inline"><b>categoryID</b></p>
            </td>

            
            </tr>

            <tr valign="top">
            <th scope="row">提交内容搜索类目</th>
            <td>
            <select id="mws_content_search_category_id" name="mws_content_search_category_id" >
            <?php

                $categorys= array(
                    array(
                        'category_id'=>1,
                        'category_name'=>'综合',
                    ),
                    array(
                    'category_id'=>2,
                    'category_name'=>'新闻',
                        ),
                    array(
                        'category_id'=>3,
                        'category_name'=>'教育',
                    ),
                    array(
                    'category_id'=>4,
                    'category_name'=>'娱乐',
                    ),
                    array(
                        'category_id'=>5,
                        'category_name'=>'体育',
                    ),
                    array(
                        'category_id'=>6,
                        'category_name'=>'汽车',
                    ),
                    array(
                    'category_id'=>7,
                    'category_name'=>'旅游',
                        ),
                    array(
                        'category_id'=>8,
                        'category_name'=>'IT科技',
                    ),
                    array(
                    'category_id'=>9,
                    'category_name'=>'医疗',
                    ),
                    array(
                        'category_id'=>10,
                        'category_name'=>'财经',
                    ),
                    array(
                    'category_id'=>11,
                    'category_name'=>'时尚',
                        ),
                    array(
                        'category_id'=>12,
                        'category_name'=>'美食',
                    ),
                    array(
                    'category_id'=>13,
                    'category_name'=>'法律',
                    ),
                    array(
                        'category_id'=>14,
                        'category_name'=>'文化',
                    ),
                    array(
                        'category_id'=>15,
                        'category_name'=>'游戏',
                    ),
                    array(
                    'category_id'=>16,
                    'category_name'=>'房产',
                    ),
                    array(
                        'category_id'=>17,
                        'category_name'=>'母婴',
                    ),
                    array(
                    'category_id'=>18,
                    'category_name'=>'商品',
                        ),
                    array(
                        'category_id'=>19,
                        'category_name'=>'生活',
                    ),
                    array(
                    'category_id'=>20,
                    'category_name'=>'政务',
                    )
        );
                foreach( $categorys as $category ) {      
                    $category_name = $category['category_name'];
                    $category_id = $category['category_id'];                   
                    ?>
                     
               <option  value="<?php echo $category_id;  ?>" <?php echo get_option('mws_content_search_category_id')==$category_id?'selected':''; ?>><?php echo $category_name ?></option>"
                   <?php }  ?>
            </select>
            </td>
            </tr>   
                   
        </table>
    </div>

    <h2>微慕增强版</h2>
    <div class="section">
        <div style="display: flex; flex-direction: row; margin-bottom: 10px">
            <a href="https://www.minapper.com" target="_blank" style="text-decoration: none"><div style="width:120px; height:32px; background-color: #ff8f3b; border-radius: 4px; color: #fff;display: flex;justify-content: center; align-items: center;margin-right: 16px">微慕官网</div></a>
           <a href="https://shops.minapper.com"  target="_blank" style="text-decoration: none"><div style="width:120px; height:32px; background-color: #fff; border: 1px solid #ff8f3b; border-radius: 4px; box-sizing: border-box; color: #ff8f3b;display: flex;justify-content: center; align-items: center">微慕商城</div></a>
        </div>
                <p style="color: #4c4c4c;text-align:justify; line-height: 2">微慕增强版WordPress小程序是一款，在原守望轩开源小程序（现微慕开源小程序）基础上重新架构、设计、优化过的wordpress多端小程序，性能和用户体验更佳，界面设计更加简洁清新，同时打通<span style="font-weight:bold">微信小程序、QQ小程序、百度小程序、支付宝小程序、头条小程序...真正实现一站多端</span>，可使用微信扫描下方小程序码直接体验：</p>
        <div>
            <img src="<?php echo MINAPPER_WECHAT_SEARCH_PLUGIN_URL.'images/minapper-plus.jpg' ?>" alt="微慕增强版" width="100%"></img>
        </div>
    </div>

    <h2>微慕版专业版</h2>
    <div class="section">
        <div style="display: flex; flex-direction: row; margin-bottom: 10px">
            <a href="https://www.minapper.com" target="_blank" style="text-decoration: none"><div style="width:120px; height:32px; background-color: #fc6e6e; border-radius: 4px; color: #fff;display: flex;justify-content: center; align-items: center;margin-right: 16px">微慕官网</div></a>
           <a href="https://shops.minapper.com"  target="_blank" style="text-decoration: none"><div style="width:120px; height:32px; background-color: #fff; border: 1px solid #fc6e6e; border-radius: 4px; box-sizing: border-box; color: #fc6e6e;display: flex;justify-content: center; align-items: center">微慕商城</div></a>
        </div>
                <p style="color: #4c4c4c;text-align:justify; line-height: 2">微慕版专业版WordPress小程序和插件，在“守望轩”开源小程序的基础上，架构完全重构，在性能上大幅度优化，增加了<span style="font-weight:bold">动态圈子、积分系统、文章投稿、发布动态、付费阅读、会员权限、多种图文列表样式、预约表单、模板消息</span>等功能，并且免费提供标准版、旅游版、图片版、企业版4套前端模板，可使用微信扫描下方小程序码直接体验：</p>
        <div>
            <img src="<?php echo MINAPPER_WECHAT_SEARCH_PLUGIN_URL.'images/minapper-pro.jpg' ?>" alt="微慕专业版" width="100%"></img>

        </div>
    </div>


 </div>

    
    <?php submit_button();?>
</form>
 <?php get_MinapperWechatSearch_jquery_source(); ?>
            <script>
               jQuery(document).ready(function($) {
                RESPONSIVEUI.responsiveTabs();
                // if($("input[name=post_meta]").attr('checked')) {
                //     $("#section_meta_list").addClass("hide");
                // }
            });
            </script>
</div>
<?php }  