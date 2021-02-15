<?php

//禁止直接访问
if ( ! defined( 'ABSPATH' ) ) exit;

class Minapper_Wechat_API {
	
	

	  //获取小程序Access Token
	public function get_access_token() {		
		$appid = get_option('mws_appid');
		$secret= get_option('mws_secret');
		$result=array();
		if( ($access_token = get_option('mws-access_token')) !== false && ! empty( $access_token ) && time() < intval($access_token['expire_time'])) {
			$result['errcode']=0;
			$result['access_token']=$access_token['access_token'];
			$result['expire_time']=$access_token['expire_time'];

			return $result;
		}	
		
		$api_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='. $appid .'&secret='. $secret;
		$response = wp_remote_get( $api_url );	
		
		if( ! is_wp_error( $response ) && is_array( $response ) && isset( $response['body'] ) ) {			
			$result = json_decode( $response['body'], true );
			if( ! isset( $result['errcode'] ) || $result['errcode'] == 0 ) {
				
				$access_token = array(
					'access_token' => $result['access_token'],
					'expire_time' => time() + intval( $result['expires_in'])-300
				);
				update_option( 'mws-access_token', $access_token );				
				//return $access_token['access_token'];

				$result['errcode']=0;
				$result['access_token']=$access_token['access_token'];
				$result['expire_time']=$access_token['expire_time'];
			}
			
		}
		else
		{
			$result['errcode']=-2;
			$result['errmsg']=$response;


		}
		
		return $result;
	}
	// 获取小程序API地址
	public function API( $key) {
		$api_urls = array(			
			'submitPages'=>'https://api.weixin.qq.com/wxa/search/wxaapi_submitpages'
			
		);		
		return $api_urls[$key];
	}
	
	// 发起API请求
	private function request( $url, $method,$body){
		$body =json_encode( $body );
		$response = wp_remote_request( $url, array(
			'method' => $method,
			'body' => $body
		) );		
		return ! is_wp_error( $response ) ? json_decode( $response['body'], true ) : false;
	}
	
	
	//提交小程序页面
	public function submitPages($data) 
    {
		
		$access_token_result = $this->get_access_token();
		$result =array();
		if($access_token_result['errcode']==0)
		{
			$access_token=$access_token_result['access_token'];
			$access_token= $access_token?'?access_token=' . $access_token:'';
			$api_url = $this->API('submitPages');
			$api_url=$api_url.$access_token;
			$result = $this->request( $api_url, 'POST',$data);
			$error =$result['errcode'];
			$errmsg='';
			 switch($error)
			 {
				 case '-1':
					 $errmsg="系统繁忙，此时请开发者稍候再试";
				 break;
				 case '0':
					$errmsg="提交成功";
				break;
				 case '40066':
					 $errmsg="递交的页面被sitemap标记为拦截。".$result['errmsg'];
				 break;
				 case '40210':
					 $errmsg="pages 中的path参数不存在或为空";
				 break;
				 case '40211':
					$errmsg="pages 中的数据结构校验失败";
				break;

				 case '40212':
					 $errmsg="paegs 当中存在不合法的query";
				 break;

				 case '40219':
					 $errmsg="pages不存在或者参数为空";
				 break;

				 case '47001':
					 $errmsg="http请求包不是合法的JSON";
				 break;
				 case '47004':
					 $errmsg="每次提交的页面数超过1000";
				 break;
				 case '85091':
					 $errmsg="小程序的搜索开关被关闭。请访问设置页面打开开关再重试";
				 break;
				 case '85083':
					 $errmsg="小程序的搜索功能被禁用";
				 break;
			 }

			 if($error !='0')
			 {
				$errmsg .='错误信息：'.$result['errmsg'];
			 }
			 
			 $result['errmsg']=$errmsg;
			
			
		}
		else
		{
			$result=$access_token_result;

		}
		
		return $result ;
	}


}

