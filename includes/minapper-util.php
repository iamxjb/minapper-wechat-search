<?php 

//禁止直接访问
if ( ! defined( 'ABSPATH' ) ) exit;

class  MWS_Util{

    public function __construct() {		
		//$this->rest_api_init();
    }

    public static  function  getPostImages($content,$postId)
    {
        //获取文章的首图
        $post_frist_image=self::get_post_content_first_image($content);
        $post_thumbnail_image="";   
        $data =array();
        if (has_post_thumbnail($postId)) {
            //获取缩略的ID
            $thumbnailId = get_post_thumbnail_id($postId);
            //特色图缩略图
            $image=wp_get_attachment_image_src($thumbnailId, 'thumbnail');
            $post_thumbnail_image=$image[0];           
        }

        if(!empty($post_frist_image) && empty($post_thumbnail_image))
        {
            $post_thumbnail_image=$post_frist_image;
        }

        

        $data['post_thumbnail_image']=$post_thumbnail_image;   
        $post_all_images= self::get_post_content_images($content); 
        $data['post_all_images']=$post_all_images;
        return  $data; 
    }

    public static  function get_post_content_images($post_content){
        // if(!$post_content){
        //     $the_post       = get_post();
        //     $post_content   = $the_post->post_content;
        // } 
        preg_match_all('|<img.*?src=[\'"](.*?)[\'"].*?>|i', do_shortcode($post_content), $matches);
        $images=array();
        if($matches && isset($matches[1]))
        {
            $_images=$matches[1]; 
           
            for($i=0; $i<count($matches[1]);$i++) {
                if($i>2)
                {
                    break;
                }
                $imageurl['imagesurl'] =$matches[1][$i];
                $imageurl['id'] ='image'.$i;
                $images[]=$imageurl;                
                
            }
    
        }
        return $images;
            
    }

    //获取文章的第一张图片
public static  function get_post_content_first_image($post_content){
	if(!$post_content){
		$the_post		= get_post();
        if(!empty($the_post))
        {
          $post_content = $the_post->post_content;  
        }
        else
        {
            return "";
        }

		
	} 

	preg_match_all( '/class=[\'"].*?wp-image-([\d]*)[\'"]/i', $post_content, $matches );
        if( $matches && isset($matches[1]) && isset($matches[1][0]) ){	
            $image_id = $matches[1][0];
            if($image_url = self::get_post_image_url($image_id)){
                return $image_url;
            }
        }

        preg_match_all('|<img.*?src=[\'"](.*?)[\'"].*?>|i', do_shortcode($post_content), $matches);
        if( $matches && isset($matches[1]) && isset($matches[1][0]) ){	   
            return $matches[1][0];
        }
    }

    //获取文章图片的地址
    public static  function get_post_image_url($image_id, $size='full'){
        if($thumb = wp_get_attachment_image_src($image_id, $size)){
            return $thumb[0];
        }
        return false;	
    }

    public static   function getPosts ($current_page,$per_page,$s){
        global $wpdb; 
        $wpdb->minapper_ext= $wpdb->prefix.'minapper_ext';
        $sql = "SELECT t.*,
        (select s.meta_value from ".$wpdb->postmeta." s where s.post_id =t.id and s.meta_key='_minapperWechatSearhDataPost') as searhDataPostCount,
        (select s5.meta_value from ".$wpdb->postmeta." s5 where s5.post_id =t.id and s5.meta_key='_minapperWechatContentPost') as minapperContentPost,
        (select s6.meta_value from ".$wpdb->postmeta." s6 where s6.post_id =t.id and s6.meta_key='_minapperWechatContentTestPost') as minapperContentTestPost

           FROM  ".$wpdb->posts  ;
        $sql .=" t  where post_status='publish' ";
       
        if(!empty($s))
        {
            $sql .=" and post_title like '%".$s."%'";
        }
        if(!empty($per_page))
        {
            $sql .="  order by post_date desc  limit ".$current_page. ",".$per_page;
        }
        $posts = $wpdb->get_results($sql,ARRAY_A);
        if(!empty($posts)){
            return $posts;
        }        
        else
        {
            return null;
        }
    }

    public static   function getPostsCount ($s,$postype){
        global $wpdb; 
        $sql = "SELECT count(1) FROM  ".$wpdb->posts  ;  

        $sql .=" t  where post_status='publish' ";
        if(!empty($s))
        {
            $sql .=" and post_title like '%".$s."%'";
        }
        $postsCount = (int)$wpdb->get_var($sql);
        return $postsCount;
    }

    public static function post_check($value) {
        $value = addslashes($value);
        $value = str_replace("_", "\_", $value);    
        $value = str_replace("%", "\%", $value);
        $value = nl2br($value);
        $value = htmlspecialchars($value);
        $value= sanitize_text_field($value);
        return $value;
    
    }
}
