<?php
/**
 * yqdoc内页
 *
 * @package Package Name
 * @subpackage Subpackage
 * @category Category
 * @author xiaoz
 * @link https://www.xiaoz.me/
 */
defined('BASEPATH') OR exit('No direct script access allowed');
class Post extends CI_Controller {
	public $token;
	public $login;
	public $title;
	public $keywords;
	public $description;
	public $template;
	public $cache;
	public $donation;
	public function __construct(){
		parent::__construct();
		$this->template = $this->config->item('template');
		$this->token = $this->config->item('token');
		$this->login = $this->config->item('login');
		$this->title = $this->config->item('title');
		$this->subtitle = $this->config->item('subtitle');
		$this->keywords = $this->config->item('keywords');
		$this->description = $this->config->item('description');
		$this->cache = $this->config->item('cache');
		$this->donation = $this->config->item('donation');
		//加载类库
	 	$params = array(
	 		'api_token' => $this->token,
	 		'login' => $this->login
	 	);
	 	$this->load->library('get_data',$params);
	 	
	}
	//public function _remap($name,$slug) {
 //       $this->doc($name,$slug);
 //   }
	public function doc($name,$slug = ''){
		//判断是否开启缓存
		if($this->cache === TRUE){
			//文章页面缓存1小时
			$this->output->cache(1 * 60);
		}
		
		//XSS过滤
		$name = $this->security->xss_clean($name);
		$slug = $this->security->xss_clean($slug);
		if( ($slug == '') OR ( ! isset($slug)) ) {
			$toc_tmp = $this->get_data->toc($name);
			//如果请求数据失败了，则重新执行一次
			if( ! $toc_tmp) {
				$toc_tmp = $this->get_data->toc($name);
			}
			$slug = $toc_tmp[0]->slug;
			$url = current_url();
			$url = $url.'/'.$slug;
			header("location: {$url}");
		}
		
		$data = $this->get_data->doc($name,$slug);
		//如果执行失败，重新执行一次
		if( ! $data) {
			$data = $this->get_data->doc($name,$slug);
			echo 'dsd';
		}
		//var_dump($data);
		//获取副标题
		$subtitle = $data->title;
		//获取当前文档列表
		$data->toc = $this->get_data->toc($name);
		//如果执行失败，重新执行一次
		if( ! $data->toc) {
			$data->toc = $this->get_data->toc($name);
		}
		$data->title = $this->title;
		$data->subtitle = $data->book->name."【{$subtitle}】";
		$data->keywords = $this->keywords.','.$data->book->name;
		//$data->description = $data->subtitle."。".$data->book->description;
		$data->repos = $this->get_data->repos();
		//如果执行失败，重新执行一次
		if( ! $data->repos){
			$data->repos = $this->get_data->repos();
		}
		$data->name = $data->book->name;
		//捐赠地址
		$data->donation = $this->donation;
		//文档最后更新时间
		$data->content_updated_at = date('Y-m-d H:i',strtotime($data->book->content_updated_at));

		//加载视图
		$this->load->view($this->template.'/header',$data);
		$this->load->view($this->template.'/post',$data);
		$this->load->view($this->template.'/footer');
	}
}