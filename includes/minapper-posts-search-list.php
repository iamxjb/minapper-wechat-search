<?php
//禁止直接访问
if ( ! defined( 'ABSPATH' ) ) exit; 
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}




class Minapper_Posts_List extends WP_List_Table {   
    

    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'post',     //singular name of the listed records
             'plural'    => 'posts',    //显示复选框listed records
             'ajax'      => false        //does this table support ajax?
        ) );
        
    }

    function column_default($item, $column_name){     
        return $item[$column_name];
    }

    

    

    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        );
    }

    
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />',          //Render a checkbox instead of text
            'ID'     => 'id',    
            'post_title'     => '标题',          
            'post_date'  => '日期',        
            'searhDataPostCount'     => '页面提交次数',
            'minapperContentPost'     => '内容提交次数(正式数据)',
            'minapperContentTestPost'     => '内容提交次数(测试数据)'              
        
            
        );
        return $columns;
    }


    function get_sortable_columns() {
        $sortable_columns = array(
            'title'     => array('title',false),     //true means it's already sorted
            'rating'    => array('rating',false),
            'director'  => array('director',false)
        );
        return $sortable_columns;
    }

    //批量提交
    function get_bulk_actions() {
        $actions = array(
            'searhDataPost'    => '提交页面搜索',
            'searhContentPost'    => '提交正式数据到内容搜索',
            'searhContentDelete'    => '删除内容搜索正式数据',
            'searhContentTestPost'    => '提交测试数据到内容搜索',
            'searhContentTestDelete'    => '删除内容搜索测试数据'
         
        );
        return $actions;
    }


    function process_bulk_action() {
        $current_action=$this->current_action();
        $mws_miniprogram_post_path= get_option("mws_miniprogram_post_path");
        $mws_miniprogram_post_id= get_option("mws_miniprogram_post_id");
        $appid = get_option('mws_appid');
        $secret= get_option('mws_secret');
        if(empty($appid))
        {
            echo '<div id="message" class="error"><p><strong>没有填写小程序appid</strong></p></div>';
            return;
        }
        if(empty($secret))
        {
            echo '<div id="message" class="error"><p><strong>没有填写小程序secret</strong></p></div>';
            return;
        }        
        if(empty($mws_miniprogram_post_id))
        {
            echo '<div id="message" class="error"><p><strong>没有填写小程序文章详情页面路径</strong></p></div>';
            return;
        }
        if(empty($mws_miniprogram_post_path))
        {
            echo '<div id="message" class="error"><p><strong>填写小程序跳转文章详情id参数名</strong></p></div>';
            return;
        }
        $path=$mws_miniprogram_post_path;
        $postIds =array();
        if(isset($_REQUEST ['post']))
        {        
           $postIds=$_REQUEST ['post'];
           $postIds=array_map(array($this,'checkPostId'),$postIds);
        }       
        if(empty($postIds))
        {         
            return;
        }
        else
        {
            if(is_array($postIds) && count($postIds)==0 && !empty($postIds))
            {
                return;
            } 
            $flag=false;
            foreach ($postIds as  $postId )  
            {
                if(!is_int($postId) && get_post((int)$postId)==null)
                {
                    $flag=true;
                    break;
                
                }
            }
            if($flag)
            {
                return;
            }
            
            
        }
        if('searhDataPost'=== $current_action) {
            if($postIds=="")
            {
                return;
            }
            if($current_action=="searhDataPost")
            {
                $pages=array();
                foreach ($postIds as  $postId )  
                { 
                    if(get_post((int)$postId)==null)
                    {
                        continue;
                    }                    
                    $query=$mws_miniprogram_post_id."=".$postId;
                    $page = array(
                        'path' =>$path,
                        'query' =>$query
                        
                    );

                    $pages[]=$page; 
                } 
                $data['pages']=$pages;
                //var_dump($pages);
                $result = MWS()->wxapi->submitPages($data);
                $errcode=$result['errcode'];
                $errmsg=$result['errmsg'];
                if($errcode !='0')
                {
                    echo '<div id="message" class="error"><p><strong>'.$errmsg.'</strong></p></div>';
                }
                else
                {
                    foreach ($postIds as  $postId )  
                    {
                        if(get_post((int)$postId)==null)
                        {
                            continue;
                        } 
                        $postId=(int) $postId;
                        $searhDataPostCount = (int)get_post_meta($postId, '_minapperWechatSearhDataPost', true); 
                        $searhDataPostCount =$searhDataPostCount+1;  
                        if(!update_post_meta($postId, '_minapperWechatSearhDataPost', $searhDataPostCount))   
                        {  
                            add_post_meta($postId, '_minapperWechatSearhDataPost', 1, true);  
                        }
                    }
                    
                    echo '<div id="message" class="updated fade"><p><strong>'.$errmsg.'</strong></p></div>';
                    
                }               
                
            } 
        }

        else  if('searhContentPost'=== $current_action || 'searhContentDelete'=== $current_action || 'searhContentTestPost'=== $current_action || 'searhContentTestDelete'=== $current_action ) {
            $category_id=empty(get_option('mws_content_search_category_id'))?1:(int)get_option('mws_content_search_category_id');
            $mws_miniprogram_cate_path= get_option("mws_miniprogram_cate_path");
            $mws_miniprogram_cate_id= get_option("mws_miniprogram_cate_id");

          
            
            $metakey="_minapperWechatContentPost";
            if('searhContentTestPost'=== $current_action || 'searhContentTestDelete'=== $current_action)
            {
                $metakey="_minapperWechatContentTestPost";
            }

            $update=1;
            if('searhContentDelete'=== $current_action  || 'searhContentTestDelete'=== $current_action)
            {
                $update=3;
            }
            $datatype="wxsearch_cpdata";
            if('searhContentTestPost'=== $current_action)
            {
                $datatype="wxsearch_testcpdata";
            }

            if(empty($mws_miniprogram_cate_path))
            {
                echo '<div id="message" class="error"><p><strong>没有填写小程序文章分类路径</strong></p></div>';
                return;
            }
            if(empty($mws_miniprogram_cate_id))
            {
                echo '<div id="message" class="error"><p><strong>填写小程序跳转分类id参数名</strong></p></div>';
                return;
            }
            if($postIds=="")
            {
                return;
            }
           

            $pages=array();                                            
            foreach ($postIds as $postId)  
            {  
                if(get_post((int)$postId)==null)
                {
                    continue;
                }
                $post=get_post((int)$postId);
                $query=$mws_miniprogram_post_id."=".$postId;       
                $data_list=array();                
                $PageData['@type']=$datatype;                    
                $PageData['update']=$update;
                $PageData['content_id']=$postId;                    
                $PageData['page_type']=2;
                $PageData['category_id']=$category_id;
                $PageData['h5_url']= get_permalink($postId);
                $PageData['title']=$post->post_title; 
                // $PageData['abstract']=$post->post_excerpt;                  
                $content=$post->post_content;
                $images =MWS_Util::getPostImages($content, $postId); 
                $cover_img=array();
                if(!empty($images))
                {
                    if(!empty($images['post_thumbnail_image']))
                    {
                        $_cover_img['cover_img_url']=$images['post_thumbnail_image'];
                        $_cover_img['cover_img_size']=1; 
                        $cover_img[]=$_cover_img;
                        $PageData['cover_img']=$cover_img;
                    }
                        
                }

                $section=array();
                $categorys =get_the_category((int)$postId);
                $i=0;                    
                foreach($categorys as $category)
                {
                
                    if($i>3)
                    {
                        break;
                    }
                    $_section['section_name']=$category->cat_name;
                    $cat_id=(int)$category->term_id;
                    $cat_path=$mws_miniprogram_cate_path.'?'.$mws_miniprogram_cate_id.'='.$cat_id; 
                    $_section['section_url']=$cat_path;
                    $section[]=$_section;
                    $i++;

                }
                
                $PageData['section']=$section;  
                $mainbody=wp_filter_nohtml_kses($content); 
                $PageData['mainbody']=$mainbody;
                $post_date = strtotime($post->post_date);
                $PageData['time_publish']=$post_date;
                $post_modified=strtotime($post->post_modified);
                $PageData['time_modify']=$post_modified;
                $data_list[]=$PageData;
                $page = array(
                    'path' =>$path,
                    'query' =>$query,
                    'data_list'=>$data_list                    
                );                    

                $pages[]=$page; 
            } 
            $data['pages']=$pages;
            $result = MWS()->wxapi->submitPages($data);
            $errcode=$result['errcode'];
            $errmsg=$result['errmsg']; 
            //var_dump($images['post_all_images']); 
                //var_dump($pages);             
            if($errcode !='0')
            {
                echo '<div id="message" class="error"><p><strong>'.$errmsg.'</strong></p></div>';
            }
            else
            {
                foreach ($postIds as  $postId)  
                {
                    if(get_post((int)$postId)==null)
                    {
                        continue;
                    }
                    $postId=(int)$postId;
                    $minapperContentPost = (int)get_post_meta($postId, $metakey, true); 
                    if($update==1)
                    {
                        $minapperContentPost =$minapperContentPost+1;  
                    }
                    else if($update==3)
                    {
                        $minapperContentPost =0;  
                        
                    }
                    
                    if(!update_post_meta($postId, $metakey, $minapperContentPost))   
                    {  
                        add_post_meta($postId, $metakey, 1, true);  
                    }
                }                
                echo '<div id="message" class="updated fade"><p><strong>'.$errmsg.'</strong></p></div>';
                
            }   
        } 
        
    }
    function checkPostId($postId)    
    { 
        $postId=MWS_Util::post_check($postId);
        if(get_post((int)$postId) !=null)
        {
          
            return $postId;
        }
        
        
    }

    function prepare_items() {
        global $wpdb;
        $per_page = 30;     
        $columns = $this->get_columns();
        $s='';
        if(isset($_REQUEST['s']))
        {
            $s=$_REQUEST['s'];
        }
        if(empty($s) || !is_string($s) || mb_strlen($s,'utf8')>30)
        {         
            $s="";
        }
        else{
            $str = '/select|insert|update|delete|\#|\'|\\*|\*|\.\.\/|\.\/|union|into|load_file|outfile/i';
            if(preg_match($str, $s))
            {
                $s='';
            }
            else{
                $s = MWS_Util::post_check($s); 
            }
                       
        }
        $hidden = array();
        $sortable = $this->get_sortable_columns();       
        $this->_column_headers = array($columns, $hidden, $sortable);      
        $this->process_bulk_action();   
        $current_page = $this->get_pagenum();        
        $current_page=($current_page-1)*$per_page;
        $data = MWS_Util::getPosts($current_page,$per_page,$s,'');
        if(!empty($data))
        {
            $total_items = MWS_Util::getPostsCount($s,'');
                   
            $this->items = $data;        
            $this->set_pagination_args( array(
                'total_items' => $total_items,                  //WE have to calculate the total number of items
                'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
                'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
            ) );

        }
        
    }
}

function post_wechat_search_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('您无权修改本页设置'));
    }
    

    $PostsListTable = new Minapper_Posts_List();    
    $PostsListTable->prepare_items();

    ?>
   
     <div class="wrap">
        
        <div id="icon-users" class="icon32"><br/></div>
        <h2>提交微信搜索</h2>

        <p>Minapper wachat search by <a href="https://www.minapper.com" target="_blank">微慕</a>.
        
        
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="postBaiduMapFrom" method="post">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <?php 
                $PostsListTable->search_box( __( 'Search' ), 'search-box-id' ); 
    ?>
       <input type="hidden" name="page" value="post_wechat_search_slug"/>
          
            <!-- Now we can render the completed list table -->
            <?php $PostsListTable->display() ?>
        </form>
        
    </div>
    <?php 


        
}








