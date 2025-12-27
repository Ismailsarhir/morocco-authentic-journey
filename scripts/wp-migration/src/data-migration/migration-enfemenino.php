<?php

namespace Scripts\GetDatas;
use Scripts\GetDatas\MigrationInterface;
use Scripts\Models\Taxonomy;
use \Scripts\Shared\Logger;
use Scripts\GetDatas\Aufeminin\ApiClientAufeminin;
use \Scripts\Shared\CommonFunctions;
use Scripts\Shared\OldDb;
use Scripts\Common\Utils;

class MigrationEnfemenino implements MigrationInterface {

	use Logger, CommonFunctions;

	protected $api_client;
	protected $ressource;
	protected $mapping;
	protected $term_type;
	protected $extra_args;
	private $db;
	private $foundRows = 0;
	private static $site_id = 1;
	private static $departments = [];
	const SUMMARY_BLOCK_MIN_LENGTH = 3;
	const SUMMARY_MIN_BLOCK_COUNT = 4;

	public function get_api_client(){
		return $this->api_client;
	}

	public function __construct($args = []) {
		$base_url = !empty($args['base_url']) ? $args['base_url'] : 'https://www.enfemenino.com/reloaded/api/';
		$this->api_client = new ApiClientAufeminin($base_url);
		$this->ressource = $args['ressource'] ?? '';
		$this->extra_args = $args['extra_args'] ?? [];
		# TABLES
		// StaticPage
		// Partenaire & VideoPartenaire from db WorldVideo
		$this->db = OldDb::get_instance(DB_HOST, DB_USER, DB_PASSWORD, AUFEMININ_OLD_DB);
		$this->set_data_mapping();
		$this->set_term_type();
	}

	public function getTaxonomies($taxonomy_name='category', $args = []) {
		$method_name = 'get_'.$taxonomy_name;

		if(!method_exists($this, $method_name)) {
			$this->registerLog("the method <<{$method_name}>> is not found in the class << ".__CLASS__." >>", '_error');
			return [];
		}

		return $this->$method_name($args);
	}

	public function getTaxonomie($taxonomy_name='category', $old_id='') {
		$method_name = 'get_'.$taxonomy_name;

		if(!method_exists($this, $method_name)) {
			$this->registerLog("the method <<{$method_name}>> is not found in the class << ".__CLASS__." >>", '_error');
			return [];
		}

		return $this->$method_name($old_id);
	}

	public function getUsers($args = []) {

		$ressource = !empty($args['ressource']) ? $args['ressource'] : "";

		$method_name = 'get_'.$ressource;

		if(!method_exists($this, $method_name)) {
			$this->registerLog("the method <<{$method_name}>> is not found in the class << ".__CLASS__." >>", '_error');
			return [];
		}

		return $this->$method_name($args);

	}

	private function get_authors($args)
	{
		$authors = [];

		if(empty($this->mapping)) {
			return $authors;
		}

		$page = $args['page'] ?? 1;

		list($data, $this->foundRows) = $this->api_client->fetch('author', ['page' => $page]);

		if(empty($data['authors'])) {
			$this->registerLog("no authors data found in page <<{$page}>>", '_error');
			return $authors;
		}

		return $data['authors'];
	}

	private function get_author($args)
	{
		$author_id = $args['author_id'] ?? 0;

		if (empty($author_id)) {
			return null;
		}

		if(empty($this->mapping)) {
			return null;
		}

		$ressource = "author/{$author_id}";

		list($author, $this->foundRows) = $this->api_client->fetch($ressource);

		if(empty($author)) {
			$this->registerLog("no author with id {$author_id} found", '_error');
			return null;
		}

		return $author;
	}

	public function get_admin($args)
	{
		$admin_id = $args['admin_id'] ?? 0;

		if (empty($admin_id)) {
			return null;
		}

		if(empty($this->mapping)) {
			return null;
		}

		$query = "
			SELECT u.* FROM `PHPManageUser` AS u
			LEFT JOIN `PHPManageUserSite` AS us ON us.user_id = u.user_id
			WHERE u.`user_id` = {$admin_id}
			AND us.site_id = 1
		";

		if($result = $this->db->query($query)) {
			$admin = $result->fetch_assoc();
		} else {
			$this->registerLog("no admin with id {$admin_id} found", '_error');
			return null;
		}

		return $admin;
	}

	public function get_admins($args)
	{
		$admins = [];

		if(empty($this->mapping)) {
			return $admins;
		}

		$offset = !empty($args['offset']) ? $args['offset'] : 0;
		$limit = !empty($args['limit']) ? $args['limit'] : 100;
		$first = !empty($args['first']) ? true : false;
		$last_old_id = !empty($args['last_old_id']) ? $args['last_old_id'] : 9999999999;

		$query = "
			SELECT u.* FROM `PHPManageUser` AS u
			LEFT JOIN `PHPManageUserSite` AS us ON us.user_id = u.user_id
			WHERE us.site_id = 1
			AND u.user_id < {$last_old_id}
			ORDER BY u.user_id DESC
			LIMIT {$offset}, {$limit}
		";

		if($first) {
			$query = preg_replace('/SELECT/', 'SELECT SQL_CALC_FOUND_ROWS', $query, 1);
		}

		if($result = $this->db->query($query)) {		
			$admins = $result->fetch_all(MYSQLI_ASSOC);
			if($first){
				$foundRows = $this->db->query("SELECT FOUND_ROWS() as foundRows");
				$foundRows = $foundRows->fetch_assoc()['foundRows'];
				$this->foundRows = $foundRows;
			}
		}

		return $admins;
	}

	public function get_admin_by_manage_id($admin_id)
	{
		$admin = null;

		if(empty($admin_id)) {
			return $admin;
		}

		$query = "
			SELECT u.* FROM `PHPManageUser` AS u
			LEFT JOIN `PHPManageUserSite` AS us ON us.user_id = u.user_id
			WHERE u.`user_id` = {$admin_id}
			AND us.site_id = 1;
		";

		if($result = $this->db->query($query)) {
			$admin = $result->fetch_assoc();
		}

		return $admin;
	}

	function get_info_video_from_db($video_id){
		$data = false;
		if(!empty($video_id)){
			$query = "SELECT VideoPartenaireID,PartenaireName FROM VideoPartenaire inner join Partenaire on VideoPartenaire.PartenaireID = Partenaire.PartenaireID where VideoID = $video_id and PrivateID != '';";
			if($result = $this->db->query($query)) {
				$data = $result->fetch_row();			}
		}
		return $data;
	}
	function get_pages_from_db($page_id = false ,$limit = 1,$offset = 0,$first = false){

		$select = "SELECT * FROM `StaticPage`";

		$offset = !empty($offset) ? $offset : 0;
		$limit = !empty($limit) ? $limit : 1;

		$where = "WHERE Online = 1 AND SiteId = ".self::$site_id;
		if($page_id){
			$where .= " AND StaticPageId = $page_id";
		}

		$query = "$select $where LIMIT $offset, $limit";

		if($first) {
			$query = preg_replace('/SELECT/', 'SELECT SQL_CALC_FOUND_ROWS', $query, 1);
		}

		if($result = $this->db->query($query)) {		
			$posts = $result->fetch_all(MYSQLI_ASSOC);
			if($first){
				$foundRows = $this->db->query("SELECT FOUND_ROWS() as foundRows");
				$foundRows = $foundRows->fetch_assoc()['foundRows'];
				$this->foundRows = $foundRows;
			}
			if($limit == 1){
				$posts = reset($posts);
			}
		}
		return $posts;	
	}

	
	public function get_page($post_id, $content_type){
		$post = $this->get_pages_from_db($post_id);		
		return $post;	
	}


	public function get_pages($args, $content_type){
		$posts = [];
		$offset = !empty($args['offset']) ? $args['offset'] : 0;
		$limit = !empty($args['limit']) ? $args['limit'] : 10;
		$first = !empty($args['first']) ? true : false;
		
		$posts = $this->get_pages_from_db(false,$limit,$offset,$first);		

		return $posts;	
	}

	public function prepare_page_data($page_data){

		$data = $this->get_data($page_data);
		extract($data);


		// type
		$data['post_type'] = "page";
		$data['old_type'] = 'StaticPage';
		
		// dates
		$data['published_gmt'] = !empty($published) ? $published : '';
		$data['published'] = !empty($published) ? get_date_from_gmt($published) : '';
		
		$data['modified_gmt'] = !empty($modified) ? $modified : '';
		$data['modified'] = !empty($modified) ? get_date_from_gmt($modified) : '';
		

		if(empty($title)) {
			$data['title'] = "StaticPage - {$old_id}";
		}
		
		// old path
		if(!empty($old_path)){
			$data['old_path'] = parse_url($old_path, PHP_URL_PATH);
		}

		if(!empty($url_complet_seo)) {
			$data['old_path'] = parse_url($url_complet_seo, PHP_URL_PATH);
		}


		// author
	
		if(!empty($user_id)){
			$data['user_id'] = $this->get_user_id($user_id, 'old_author_id');
		}
	
		// status page 
		if(!empty($online)){
			$data["post_status"] =  $online == "1" ? "publish" : "draft";
			unset($data["online"]);
		}
		
		
		// * body * //
		$content = "";
		if(!empty($template)){
			if($template == "html"){
				$content = $page_data["Content"];
			}elseif($template == "hub" || $template == "front_tool"){
				$content = $this->get_content_from_hub_template($page_data["JSONData"]);
			}
			unset($data["template"]);
		}

		$content = $this->replaceScriptsInContent($content);
		$data["content"] = $content;

		if(empty($content) || $page_data['OnlineForReal'] == '0') {
			$data["post_status"] = 'draft';
		}
		
		// cats
		$cats_ids = $cats_old_ids = [];
		if(!empty($section_id)){
			$cats_old_ids[] = $section_id;
			unset($data['section_id']);
		}
		if(!empty($sub_section_id)){
			$cats_old_ids[] = $sub_section_id;
			unset($data['sub_section_id']);
		}

		if(!empty($cats_old_ids)) {
			$data['metas_data']['old_sections'] = $cats_old_ids;
			$taxonomies_data = $this->prepare_taxonomies_entity($cats_old_ids, ['category']);
			if($taxonomies_data) {
				$cats_ids = array_column($taxonomies_data['category'], 'term_id');
				if(!empty($cats_ids)){
					$data['post_category'] = $cats_ids;
					$data['metas_data']['_category_permalink'] = reset($cats_ids);
	
				}	
			}
		}

		// Template
		if($page_data['ShowHeaderFooter'] == "0"){
			$data['metas_data']["_wp_page_template"] = "page-templates/only-the-content.php";
		} else{
			$data['metas_data']["_wp_page_template"] = "page-templates/page-no-sidebar.php";
		}

		if($page_data['Logo'] != '') {
			$attachment_id = Utils::upload_img($page_data['Logo']);
			$data['metas_data']['logo_title'] = wp_get_attachment_url($attachment_id);
		}

		if (!empty($data['html_Content'])) {
			$data['metas_data']['disable_visual_editor'] = 1;
		}

		$default_data = $this->default_data_page($old_id);
		if(!empty($default_data)) {
			if(!empty($default_data['title'])) {
				$data['title'] = $default_data['title'];
			}

			if(!empty($default_data['metas_data'])) {
				$meta_data = $data['metas_data'] ?? [];
				$data['metas_data'] = array_merge($meta_data, $default_data['metas_data']);
			}

		}

		return $data;
	}

	private function replaceScriptsInContent($content) {

		// style
		$opening_style = '/<style(\b[^>]*)?>/i';
		$closing_style = '/<\/style>/i';
		$content = preg_replace($opening_style,'[tag_style $1]', $content);
		$content = preg_replace($closing_style,'[/tag_style]', $content);
		
		// ifram
		$regex_iframe = '/<iframe\s+([^>]*)>.*?<\/iframe>/ms';
		$content = preg_replace($regex_iframe,'[iframe $1]', $content);

		// script
		$opening_script = '/<script(\b[^>]*)?>/i';
		$closing_script = '/<\/script>/i';
		$content = preg_replace($opening_script,'[tag_script $1]', $content);
		$content = preg_replace($closing_script,'[/tag_script]', $content);
		return $content;
	}

	function get_content_from_hub_template($blocs){
		$content = "";
		$blocs = json_decode($blocs);
		foreach($blocs as $bloc){
			switch ($bloc->type) {
				case "front_tool":
					if(!empty($bloc->template)){
						if($bloc->template == 'ovulationTool') {
							$content .= "\n[calcule_ovulation]";
						} else {
							$content .= "\n[template_front_tool template='{$bloc->template}']";
						}
					}					
				case "paragraph":
					if(!empty($bloc->html)){
						$paragraph = "<p class='paragraph_page'>".$bloc->html."</p>";
						$content .= "\n$paragraph";
					}
					break;
				case "related":
					$prepare_ids = [];
					if(!empty($bloc->contentids)){
						$related_ids = explode(",",$bloc->contentids);
						if(!empty($related_ids)){
							foreach($related_ids as $related_id){
								$related_id = explode(":",$related_id);
								if(!empty($related_id[1])){
									$meta_key_old_id = $related_id[0] == '28' ? 'album_id' : 'edito-feed_id';
									$prepare_ids[] = $this->replace_old_ids_with_new($related_id[1], $meta_key_old_id);
								}
							}
							

						}
					}
					if(!empty($prepare_ids)){
						$prepare_ids = implode(",",$prepare_ids);
						$content .= "\n[related_static posts_ids='$prepare_ids']";
					}
					break;
				case 'seolinks':
					if(!empty($bloc->zoneId)){
						$content .= "\n[add_links_to_content zone_id='".$bloc->zoneId."']";
					}
					break;
				case 'videomanage':
					if(!empty($bloc->videoid)){
						$data_video = $this->get_info_video_from_db($bloc->videoid);
						$shortcode_videomanage = "[videomanage ";
						if(!empty($data_video)){
							if($data_video[1] == "Dailymotion"){
								$shortcode_videomanage = "[fpvideo mediaid='https://www.dailymotion.com/video/".$data_video[0]."' ";
							}else{
								$shortcode_videomanage .= "type='".$data_video[1]."' ";
							}

						}
						if(!empty($bloc->autostart)){
							$shortcode_videomanage .= " autoplay='true'";
						}
						$shortcode_videomanage .= "]";
						$content .= "\n$shortcode_videomanage";
					}
					break;
				case 'faq':
					if(!empty($bloc->faqData)){
						$shortcode_faq = "[faq]";
						$index = 1;
						foreach($bloc->faqData as $item){
							$question = trim(str_replace("\n","",$item->question));
							$answer = trim(str_replace("\n","",$item->answer));
							$shortcode_faq .=' [question title="'.$question.'" ]'.wpautop($answer)."[/question]";
							$index++;
						}
						$shortcode_faq .= "[/faq]";
						$content .= "\n$shortcode_faq";
					}
					break;
				case 'forum':
					if(!empty($bloc->data)){
						$static_forum = "[static_forum ";
						if(!empty($bloc->data->thread_cta)){
							$static_forum .= ' thread_cta="' . $bloc->data->thread_cta . '" ';
						}
						if(!empty($bloc->data->category_cta)){
							$static_forum .= ' category_cta="' . $bloc->data->category_cta . '" ';
						}							
					
						$index = 1;
						if(!empty($bloc->data->contents)){
							foreach ($bloc->data->contents as  $item) {
								foreach($item as $key => $sub_item){
									$static_forum .= "$key"."_$index" . '="' . $sub_item . '" ';
								}
								$index++;
							}
						}
						$static_forum .= "]";
						$content .= "\n$static_forum";
					}
					break;
				case 'affiliation':
					if(!empty($bloc->productsData)){
						$static_affiliation = "[static_affiliation ";
						$index = 1;
						foreach ($bloc->productsData as $product) {
							$static_affiliation .= 'priceId_' . $index . '="' . $product->priceId . '" ';
							$static_affiliation .= 'name_' . $index . '="' . $product->name . '" ';
							$static_affiliation .= 'price_' . $index . '="' . $product->price. '" ';
							$static_affiliation .= 'seller_' . $index . '="' . $product->seller . '" ';
							$static_affiliation .= 'url_' . $index . '="' . esc_url($product->url) . '" ';
							$static_affiliation .= 'image_' . $index . '="' . $product->imageUrl . '" ';
							$static_affiliation .= 'nbProductsToShow_' . $index . '="' . $product->nbProductsToShow . '" ';
							$offerIndex = 1;
							foreach ($product->offers as $offerIndex => $offer) {
								$static_affiliation .= "offer_$index"."_store_name_$offerIndex".'="' . $offer->store->name . '" ';
								$static_affiliation .= "offer_$index"."_store_icon_$offerIndex".'="' . $offer->store->icon . '" ';
								$static_affiliation .= "offer_$index"."_price_$offerIndex".'="' . $offer->price. '" ';
								$static_affiliation .= "offer_$index"."_url_$offerIndex".'="' . esc_url($offer->url) . '" ';
								$offerIndex++;
							}
							$index++;
						}
						
						$static_affiliation .= "]";
						$content .= "\n$static_affiliation";
					}
					break;
			}
		}
		return $content;
	}


	public function getPosts($args, $content_type) {
		$method_name = 'get_'.$content_type;

		if(!method_exists($this, $method_name)) {
			$this->registerLog("the method <<{$method_name}>> is not found in the class << ".__CLASS__." >>", '_error');
			return [];
		}
		return $this->$method_name($args, $content_type);
	}

	// Get Post By old_id
	public function getPostOne($old_id, $content_type) {
		$method_name = 'get_'.$content_type;

		if(!method_exists($this, $method_name)) {
			$this->registerLog("the method <<{$method_name}>> is not found in the class << ".__CLASS__." >>", '_error');
			return [];
		}
		return $this->$method_name($old_id, $content_type);
	}

	// Get Post datas
	public function preparePostData($post_data, $content_type) {
		$method_name = "prepare_{$content_type}_data";

		if(!method_exists($this, $method_name)) {
			$this->registerLog("the method <<{$method_name}>> is not found in the class << ".__CLASS__." >>", '_error');
			return [];
		}
		return $this->$method_name($post_data);
	}

	// Get Post Basics
	public function preparePostBasicsData($post_data, $content_type) {
		return;
	}

	public function getDataFromCustomTable($table, $limit, $offset){
		return;
	}


	public function getCategories()
	{
		return;
	}

	public function prepareUser($user){

		$author = $this->get_data($user);

		if(empty($author)) {
			return [];
		}

		extract($author);

		$email = !empty($author['user_email']) ? $author['user_email'] : '';
		$default_email = "{$user_metas['old_author_id']}@reworldmediafactory.net";

		$email = filter_var($email, FILTER_VALIDATE_EMAIL);
		
		if($email === false){
			$email = $default_email;
		}

		if(!empty($email) && $email !== $default_email) {
			$user = get_user_by('email', $email);
			if(!empty($user) && $user->old_author_id != $user_metas['old_author_id']) {
				$email = $default_email;
			}
		}

		$author['user_email'] = $email;
		$author['user_login'] = $email;
		$author['role'] = 'author';

		if(!empty($user_metas['old_manage_client_id'])) {
			$admin = $this->get_admin_by_manage_id($user_metas['old_manage_client_id']);
			if(!empty($admin)) {
				$author['user_login'] = $admin['username'];
				$author['role'] .= ',administrator';
			}
		}

		// validate author image
		if(!empty($user_metas['user_image']) && !empty($user_metas['user_image']['url_image'])) {
			$is_author_image_valid = $this->url_is_image($user_metas['user_image']['url_image']);
			if(!$is_author_image_valid) {
				unset($author['user_metas']['user_image']);
			}
		}

		$author_rs_metas = ["facebook","instagram","linkedin","twitter","mastodon","youtube","pinterest"];

		foreach($author_rs_metas as $author_rs_meta_key){
			
			$link = !empty($author['user_metas'][$author_rs_meta_key]) ? $author['user_metas'][$author_rs_meta_key] : '';
			
			if(!empty($link) && $author_rs_meta_key == "instagram"){
				if(str_starts_with($link,"@")){
					$link = substr($link, 1);
				}
			}

			if($link = Utils::social_link_after_check($link, $author_rs_meta_key)){

				$author['user_metas'][$author_rs_meta_key] = $link;
			}
		}

		return $author;
	}

	public function prepareAdmin($admin){
		$data = $this->get_data($admin);

		if(empty($data)) {
			return [];
		}

		extract($data);

		if(!empty($wp_admin_id)) {
			return [];
		}

		$email = !empty($user_email) ? $user_email : '';
		$user_login = !empty($user_login) ? $user_login : '';
		$default_email = "{$user_metas['old_admin_id']}@reworldmediafactory.net";

		$email = filter_var($email, FILTER_VALIDATE_EMAIL);
		if($email === false){
			$email = $default_email;
		}

		if(!empty($email) && $email !== $default_email) {
			$user = get_user_by('email', $email);
			if(!empty($user) && $user->old_admin_id != $user_metas['old_admin_id']) {
				$email = $default_email;
			}
		}

		$data['user_email'] = $email;
		$data['user_login'] = $user_login ?? $email;
		$data['role'] = 'administrator';

		if(!empty($url_avatar_50)) {
			$is_valid = $this->url_is_image($url_avatar_50);
			if($is_valid) {
				$data['user_metas']['user_image'] = [
					'url_image' => $url_avatar_50,
					'title_image' => $display_name
				];
			}
		}

		return $data;
	}

	public function getComments($args){}

	public function prepareComment($comment_data){}

	public function get_terms($args) {
		$terms = [];

		if(empty($this->mapping)) {
			return $terms;
		}

		$page = $args['page'] ?? 1;

		list($data, $this->foundRows) = $this->api_client->fetch($this->ressource, ['page' => $page]);

		if(empty($data[$this->ressource])) {
			$this->registerLog("no {$this->ressource} data found in page <<{$page}>>", '_error');
			return $terms;
		}

		foreach($data[$this->ressource] as $term) {
			if($term_obj = $this->get_term_obj($term)) {
				$terms[] = $term_obj;
			}
		}

		return $terms;
	}

	public function get_term($old_id) {

		if(empty($this->mapping)) {
			return null;
		}

		list($term, $this->foundRows) = $this->api_client->fetch("{$this->ressource}/{$old_id}");

		if(empty($term)) {
			$this->registerLog("no data found for the {$this->ressource} <<{$old_id}>>", '_error');
			return null;
		}

		return $this->get_term_obj($term);
	}

	private function set_data_mapping()
	{
		$mapping = [
			'section' => [
				'rubrique' => 'title',
				'rubriqueurl' => 'slug',
				'homerubriqueurl' => 'old_path',
				'tax_args' => [
					'term_metas' => [
						'id' => 'category_old_id',
						'rubriqueid' => 'rubriqueid',
						'chapo' => 'description',
						'allArticlesURL' => 'old_url_all_posts',
						'rubriqueurl' => '_old_slug',
					],
					'wpseo_metas' => [
						'titreHtml' => 'meta_title',
						'metaDesc' => 'meta_desc',
					]
				]
			],
			'subsection' => [
				'sousrubrique' => 'title',
				'sousrubriqueurl' => 'slug',
				'homesousrubriqueurl' => 'old_path',
				'tax_args' => [
					'term_metas' => [
						'id' => 'category_old_id',
						'sousrubriqueid' => 'sousrubriqueid',
						'allArticlesURL' => 'old_url_all_posts',
						'rubriqueid' => 'old_parent_cat_id',
						'sousrubriqueurl' => '_old_slug',
					],
					'wpseo_metas' => [
						'baliseTitle' => 'meta_title',
						'metaDesc' => 'meta_desc',
					]
				]
			],
			'tag' => [
				'tag' => 'title',
				'tagCleaned' => 'slug',
				'pageURLComplete' => 'old_path',
				'tax_args' => [
					'term_metas' => [
						'tagID' => 'post_tag_old_id',
						'pageTitle' => 'tag_page_title',
						'pageChapo' => 'description',
						'tagTypeLabel' => 'tag_type_label',
						'rubriqueID' => '_tag_rubrique_id',
						'rubriqueName' => '_rubrique_name',
						'pageSousRubriqueID' => 'tag_page_sous_rubrique_id',
						'contentCount' => '_tag_content_count',
						'isVideoTag' => 'is_video_tag',
						'isNewsTag' => 'is_news_tag',
						'pageUrlBannerImage' => 'tag_banner_thumb',
						'pageUrlTitleImage' => '_page_url_title_image',
						'pageUrlIconImage' => '_page_url_icon_image',
						'pageUrlMiniatureImage' => '_page_url_miniature_image',
						'pageJsonData' => 'tag_json_data',
						'tagChildren' => 'tag_child_slugs',
						'pageOnline' => 'is_page_online',
						'tagTypeID' => '_tag_type_id',
						'pageNoTeasingAuto' => '_page_no_teasing_auto',
						'pageAccroche' => '_page_accroche',
						'tagCleaned' => '_old_slug',
					],
					'wpseo_metas' => [
						'pageGoogleTitle' => 'meta_title',
						'pageGoogleDescription' => 'meta_desc',
					]
				]
			],
			'author' => [
				'email' => 'user_email',
				'url_www' => 'user_url',
				'display_name' => 'display_name',
				'author_id' => 'user_nicename',
				'user_metas' => [
					'author_id' => 'old_author_id',
					'manage_client_id' => 'old_manage_client_id',
					'description' => 'description',
					'title' => 'user_title',
					'sub_title' => 'user_sub_title',
					'url_facebook' => 'facebook',
					'url_twitter' => 'twitter',
					'url_instagram' => 'instagram',
					'url_pinterest' => 'pinterest',
					'url_linkedin' => 'linkedin',
					'url_tiktok' => 'tiktok',
					'url_wikipedia' => 'wikipedia',
					'user_image' => [
						'url_avatar' => 'url_image',
						'display_name' => 'title_image',
					],
				]
			],
			'edito-feed' => [
				'title' => 'title',
				'contentId' => 'old_id',
				'URLComplete' => 'old_path',
				'chapo' => 'post_excerpt',
				'metas_data' => [
					'topTitle' => 'top_title',
					'shortTitle' => 'short_title',
					'titleBis' => 'title_bis',
					'nbComments' => 'nb_comments',
					'nbShares' => 'nb_shares',
					'noSocialNetwork' => 'no_social_network',
					'noTeasingAuto' => 'no_teasing_auto',
					'selector' => 'selector',
					'consultationRaw' => 'consultation_raw',
					'introTeasing' => 'intro_teasing',
					'isGoogleNews' => 'is_google_news',
					'surTitre' => 'sur_titre',
					'originId' => 'origin_id',
					'outilId' => 'outil_id',
					'videoId' => 'video_id',
					'dispoOnApps' => 'dispo_on_apps',
					'typeId' => 'type_id',
					'besoinID' => 'besoin_id',
					'isStoryVideo' => 'is_story_video',
					'authorId' => 'old_author_id',
					'intro' => '_intro',
					'OGtitle' => '_og_title',
					'title' => '_yoast_wpseo_title',
					'OGdescription' => '_yoast_wpseo_opengraph-description',
					'MetaDescription' => '_yoast_wpseo_metadesc',
					'accroche225x75' => 'accroche225x75',
					'accroche243x145' => 'accroche243x145',
					'accroche122x160' => 'accroche122x160',
					'accroche500x200' => 'accroche500x200',
					'accroche228x140' => 'accroche228x140',
					'accroche300x150' => 'accroche300x150',
					'accroche686x400' => 'accroche686x400',
					'accroche1020x500' => 'accroche1020x500',
					'accroche1257x1257' => 'accroche1257x1257',
					'accrochePinterest0x0' => 'accrochePinterest0x0',
					'accrocheFacebook0x0' => 'accrocheFacebook0x0',
					'DateCreation' => 'date_creation',
					'publicationDate' => 'publication_date',
					'ModificationDate' => 'modification_date',
				],
				'section' => 'section',
				'subSection' => 'sub_section',
				'tags' => 'tag_ids',
				'authorId' => 'author_id',
				'thumbs' => [
					'URLVignette96' => 'url_vignette96',
					'URLVignette50' => 'url_vignette50',
					'URLImage' => 'url_image',
					'URLImageMax' => 'url_image_max',
					'URLImageOrigin' => 'url_image_origin'
				],
				'publicationDate' => 'publication_date',
				'ModificationDate' => 'modification_date',
				'data' => 'content_data'
			],
			'album' => [
				'albumId' => 'old_id',
				'title' => 'title',
				'publicationDate' => 'published',
				'lastModificationDate' => 'modified',
				'urlComplete' => 'old_path',
				'password' => 'post_password',
				'metas_data' => [
					'shortTitle' => '_short_title',
					'seoTitle' => '_yoast_wpseo_title',
					'metaDescription' => '_yoast_wpseo_metadesc',
					'totalVisit' => '_total_visit',
					'username' => '_username_album',
					'isHorizontal' => '_is_horizontal',
					'redirectUrl' => '_redirect_url',
					'editoPackId' => '_edito_pack_id',
					'urlImagePinterest' => '_url_image_pinterest',
					'urlKeywords' => '_old_slug',
					'onlineForReal' => '_online_for_real',
					'isOnline' => '_is_online',
					'previewKey' => '_preview_key',
					'nbPhotos' => '_old_nb_photos',
					'nbComments' => '_old_nb_comments',
					'redirectUrl' => '_redirect_url',
					'categoryId' => '_category_id',
					'thematiqueId' => '_thematique_id',
					'urlImage' => '_url_image',
					'includeURL' => '_include_url',
					'includeURLMobile' => '_include_url_mobile',
				],
				'manageClientId' => 'user_id',
				'section' => 'section',
				'subsection' => 'sub_section',
				'tagsSeo' => 'tag_ids',
				'_embedded' => '_embedded_datas'
			],
			"page" => [
				'StaticPageId' => 'old_id', 
				'Title' => 'title', 
				'DatePublication' => 'published',
				'DateSave' => 'modified',
				'URLComplete' => 'old_path',
				'URLCompleteSEO' => 'url_complet_seo',
				'metas_data' => [
					'Logo' => 'logo_title', 
					'LogoURL' => 'url_logo_title', 
					'SubTitle' => 'sub_title', 
					'PartnerTitle' => 'partner_title',  
					'ShowAds' => 'show_ads', 
					'S3Path' => 's3_path', 
					'URLCompleteSEO' => '_redirect_url', 
					'StaticPageId' => 'old_id', 
					'ManageClientId' => 'old_author_id', 
					'NoIndex' => '_yoast_wpseo_meta-robots-noindex'
				],
				'thumb_data' => [
					"URLImage" => "thumbnail_url",
					"Title" => "thumbnail_title",
				],
				'ManageClientId' => 'user_id', 
				'RubriqueId' => 'section_id', 
				'SousRubriqueId' => 'sub_section_id', 
				'Template' => 'template', 
				"Online" => "online",
				"Content" => "html_Content"
			],
			'department' => [
				'Name' => 'title',
				'tax_args' => [
					'term_metas' => [
						'DeptId' => 'departments_old_id',
					]
				]
			],
			'babyname_list' => [
				'Name' => 'title',
				'urlComplete' => 'old_path',
				'tax_args' => [
					'term_metas' => [
						'ListId' => 'babyname_list_old_id',
						'SEOText' => 'description',
						'PushToHome' => 'pushToHome',
						'PushToHomeListBlock' => 'pushToHomeListBlock',
						'CreationDate' => 'creationDate',
						'SexId' => 'sexe',
						'StartWith' => 'startWith',
						'OriginNames' => 'origin',
						'Length' => 'length',
						'Composed' => 'composed',
						'OriginId' => 'origin_id',
						'Online' => 'online',
						'AlbumId' => 'album_id',
						'Year' => 'year',
						'isGenerated' => 'is_generated',
						'RecordLength' => 'record_length',
						'OrderBy' => 'order_by',
					],
					'wpseo_metas' => [
						'babyNameListTitle' => 'meta_title',
						'babyNameListDescription' => 'meta_desc',
					]
				]
			],
			'horoscope' => [
				'title' => 'title',
				'date_generation' => 'published',
				'conseil' => 'post_excerpt',
				'signe' => 'signe',
				'units' => 'units',
				'metas_data' => [
					'article_type' => 'old_type',
					'title' => '_yoast_wpseo_opengraph-title',
					'title' => '_yoast_wpseo_title',
					'chapo' => '_yoast_wpseo_metadesc',
					'site_id' => '_site_id',
					'global_title' => '_global_title',
					'global_chapo' => '_global_chapo',
					'message_id' => '_message_id',
					'is_google_news' => 'is_google_news',
					'date_publication' => '_date_publication'
				],
			],
			"babyname" => [
				'id' => 'old_id',
				'Name' => 'title',
				'BabyNameOriginNames' => 'translatedOriginNames',
				'metas_data' => [
					'SexId' => 'sexe',
					'Mixte' => 'mixte',
					'Length' => 'length',
					'Frequency' => 'frequency',
					'Trend' => 'trend',
					'Composed' => 'composed',
					'History' => 'history',
					'Etymology' => 'etymology',
					'Psycho' => 'psychology',
					'ColorName' => 'color',
					'StoneName' => 'stone',
					'MetalName' => 'metal',
					'LuckyNumber' => 'luckyNumber',
					'Score' => 'score',
					'celebrationDate' => 'celebrationDay',
					'SimilarBabyNames' => 'babyNameSimilar',
					'AssociatedBabyNames' => 'associatedBabyNames',
					'CelebrityBabyNames' => 'babyNameCelebrate',
					'StatisticsByDept' => 'babyNameStatisticsByDept',
					'StatisticsByYear' => 'babyNameStatisticsByYear',
				],
			],
			'admin' => [
				'user_id' => 'wp_admin_id',
				'username' => 'user_login',
				'email' => 'user_email',
				'display_name' => 'display_name',
				'url_avatar_50' => 'url_image',
				'user_metas' => [
					'user_id' => 'old_admin_id',
					'state' => '_state',
					'lastUsedSite' => '_last_used_site',
					'group_id' => '_group_id',
					'old_manage_clientid' => '_old_manage_clientid',
					'google_plus_id' => '_google_plus_id',
					'description' => 'description',
					'title' => 'user_title',
					'sub_title' => 'user_sub_title'
				]
			],
		];

		$this->mapping = $mapping[$this->ressource] ?? [];
	}

	private function get_term_obj($term)
	{
		$data = $this->get_data($term);
		extract($data);

		if(empty($title)) {
			return null;
		}

		$slug = $slug ?? '';
		$tax_args = $tax_args ?? [];
		$old_path = $old_path ?? '';
		$term_meta = $tax_args['term_metas'] ?? [];

		if('post_tag' == $this->term_type) {
			if(!empty($term_meta['tag_json_data'])) {
				$tax_args['term_metas']['description_extra'] = $term_meta['tag_json_data'];
				unset($tax_args['term_metas']['tag_json_data']);
			}

			if(!empty($term_meta['tag_banner_thumb'])) {
				$tax_args['term_metas']['tax_logo_img'] = $term_meta['tag_banner_thumb'];
			}
		}

		$term_obj = new Taxonomy($title, $slug, $tax_args, $this->term_type, $old_path);

		if(!$term_obj->is_valid()) {
			$tax_log = "The ".$this->ressource." ".$title." is not valid";
			$this->registerLog($tax_log, '_error');
			return null;
		}

		return $term_obj;
	}

	private function set_term_type()
	{
		$terms_mapping = [
			'tag' => 'post_tag',
			'department' => 'departments',
			'babyname_list' => 'babyname_list'
		];
		$this->term_type = $terms_mapping[$this->ressource] ?? 'category';
	}

	public function get_term_type()
	{
		return $this->term_type;
	}

	/**
	 * 
	 * @param  [array] $data  : the original data retrieved from the API
	 * @param  [array] $mapping : an array contains the mapping of different attribute
	 * @return [array] : a new table contains the data to be inserted
	 */
	private function get_data($data) {
		$result = [];

		$result = $this->get_mapped_data($data, $this->mapping);

		return $result;
	}

	function get_mapped_data($item, $mapping) {
		$result = [];

		foreach ($mapping as $key => $value) {
			if (is_array($value)) {
				$result[$key] = $this->get_mapped_data($item, $value);
			} else {
				if (!empty($item[$key])) {
					if (is_string($value) && method_exists($this, $value)) {
						$result[$value] = $this->$value($item, $item[$key]);
					} else {
						$result[$value] = $item[$key];
					}
				}
			}
		}

		return $result;
	}

	private function tag_banner_thumb($item, $data)
	{
		$url = $this->correct_path_thumb($data);

		if (empty($url)) {
			return $data;
		}

		return [
			'url' => $url,
			'name' => $item['pageTitle'],
			'tax_img_meta' => 'old_image_url'
		];
	}

	private function tag_json_data($item, $data)
	{
		$html = '';
		foreach($data as $key => $value) {
			if (!isset($value['type']) || empty($value['html'])) {
				continue;
			}

			if ('paragraph' !== $value['type']) {
				continue;
			}

			$html .= "<p>{$value['html']}</p>";
		}

		return $html;
	}

	private function tag_child_slugs($item, $data)
	{
		$child_slugs = [];

		foreach ($data as $item) {
			if(empty($item['tagCleaned'])) {
				continue;
			}
			$child_slugs[] = $item['tagCleaned'];
		}

		return $child_slugs;
	}

	private function correct_path_thumb($path)
	{
		$url = '';


		if (empty($path)) {
			return $url;
		}

		if (filter_var($path, FILTER_VALIDATE_URL)) {
			return $path;
		}

		$url = 'https://assets.afcdn.com/'.ltrim($path, '/');

		return $url;
	}

	private function get_post($old_id, $content_type) {
		if(empty($this->mapping)) {
			return null;
		}

		list($post, $this->foundRows) = $this->api_client->fetch("{$this->ressource}/{$old_id}", $this->extra_args);

		if(empty($post)) {
			$this->registerLog("no data found for the {$this->ressource} <<{$old_id}>>", '_error');
			return null;
		}

		return $post;
	}

	private function get_posts($args, $content_type) {
		if(empty($this->mapping)) {
			return [];
		}

		$page = $args['page'] ?? 1;
		$this->extra_args = array_merge($this->extra_args, ['page' => $page]);
		$content_type = $args['content_type'] ?? $content_type;

		list($data, $this->foundRows) = $this->api_client->fetch($this->ressource, $this->extra_args);

		if(empty($data[$content_type])) {
			$this->registerLog("no {$this->ressource} data found in page <<{$page}>>", '_error');
			return [];
		}

		return $data[$content_type];
	}

	private function prepare_edito_feed_data($post)
	{

		$data = $this->get_data($post);

		if(empty($data) || (!empty($data['metas_data']['type_id']) && $data['metas_data']['type_id'] == '28')) {
			return [];
		}

		extract($data);

		/* prepare old_path */
		$data['old_path'] = parse_url($old_path, PHP_URL_PATH);
		if (preg_match("/^\/(.*)\/album(\d+)\/(.*).html$/i", $data['old_path'])) {
			return [];
		}


		$data['old_type'] = 'edito-feed';

		/* prepare categories */
		$section_ids = [];
		$rubrique_id = $sous_rubrique_id = '';
		if(!empty($section)) {
			$section_ids[] = $section['id'];
			$rubrique_id = $section['rubriqueid'];
			unset($data['section']);
		}

		if(!empty($sub_section)) {
			$section_ids[] = $sub_section['id'];
			$sous_rubrique_id = $sub_section['sousrubriqueid'];
			unset($data['sub_section']);
		}

		$tag_ids = $breadcrumb_tags = [];
		if(!empty($data['tag_ids'])){
			foreach($data['tag_ids'] as $tag) {
				if (empty($tag['tagParentID']) && !$tag['isGeneric'] && !empty($sous_rubrique_id) && array_key_exists('pageSousRubriqueID', $tag) && $tag['pageSousRubriqueID'] == $sous_rubrique_id){
					$breadcrumb_tags[] = [
						'tagID' => $tag['tagID'],
						'dateSave' => $tag['dateSave']
					];
				}
				$tag_ids[] = $tag['tagID'];
			}
		}

		$categorie_ids = [];
		if(!empty($section_ids)) {
			$data['metas_data']['_old_category_ids'] = $section_ids;
			$taxonomies_data = $this->prepare_taxonomies_entity($section_ids, ['category']);
			if($taxonomies_data) {
				$categorie_ids = array_column($taxonomies_data['category'], 'term_id');
			}

		}


		if(!empty($breadcrumb_tags)) {
			$permalinked_tag = '';
			if(count($breadcrumb_tags) > 1) {
				$dateSave = array_column($breadcrumb_tags, 'dateSave');
				$keyOldestTag = array_keys($dateSave, min($dateSave))[0];
				$permalinked_tag = $breadcrumb_tags[$keyOldestTag]['tagID'];
			}
			if(count($breadcrumb_tags) == 1) {
				$permalinked_tag = $breadcrumb_tags[0]['tagID'];
			}
			if(!empty($permalinked_tag)) {
				$tag_ids = array_values(array_diff($tag_ids, [$permalinked_tag]));
				$data['metas_data']['_old_permalinked_tag_id'] = $permalinked_tag;
				$post_tag = Utils::rw_get_term_by_meta('category', 'post_tag_old_id', $permalinked_tag);
				if(!empty($post_tag)) {
					$categorie_ids[] = $post_tag->term_id;
				}
			}
		}

		$permalinked_cat = '';
		if(!empty($categorie_ids)) {
			$permalinked_cat = count($categorie_ids) == 1 ? reset($categorie_ids) : end($categorie_ids);
		}

		/* prepare tags */
		$post_tags = [];
		if(!empty($tag_ids)) {
			$data['metas_data']['_old_tag_ids'] = $tag_ids;
			$tags_data = $this->prepare_taxonomies_entity($tag_ids, ['post_tag', 'category'], 'post_tag_old_id');
			if(!empty($tags_data['post_tag'])) {
				$post_tags = array_column($tags_data['post_tag'], 'term_slug');
			}

			if(!empty($tags_data['category'])) {
				$extra_category_ids = array_column($tags_data['category'], 'term_id');
				$categorie_ids = array_unique(array_merge($categorie_ids, $extra_category_ids));
			}

			unset($data['tag_ids']);
		}

		if(!empty($categorie_ids)) {
			$data['post_category'] = $categorie_ids;
		}
		if(!empty($post_tags)) {
			$data['post_tags'] = $post_tags;
		}

		/* prepare story author */
		if(!empty($author_id)) {
			$data['user_id'] = $author_id;
			unset($data['author_id']);
		}

		/* prepare story thumb */
		if(!empty($thumbs)) {
			$post_thumb = $this->prepare_story_thumb($thumbs);
			if(!empty($post_thumb)) {
				$data['thumb_data'] = [
					'thumbnail_url' => $post_thumb,
					'thumbnail_title' => $title
				];
			}
			unset($data['thumbs']);
		}

		/* prepare content */
		if(!empty($content_data)) {
			$content = $this->prepare_story_content($content_data);
			if(!empty($content)) {
				$data['content'] = $content;
			}
			unset($data['content_data']);
		}

		/* prepare publication */
		$date_format = 'Y-m-d H:i:s.u';
		$data['published'] = $this->prepare_post_date($publication_date['date'] ?? '', $date_format);
		unset($data['publication_date']);
		
		$modified_date = $this->prepare_post_date($modification_date['date'] ?? '', $date_format);
		$data['modified'] = !empty($modified_date) ? $modified_date :  $data['published'];
		unset($data['modification_date']);


		if(!empty($data['content'])) {
			
			/* Check social media shortcodes */
			if(has_shortcode($data['content'], 'rw_instagram')){
				$data['metas_data']['has_script_instagram'] = 1;
			}
			if(has_shortcode($data['content'], 'rw_twitter')) {
				$data['metas_data']['has_script_twitter'] = 1;
			}
			if(has_shortcode($data['content'], 'rw_tiktok')) {
				$data['metas_data']['has_script_tiktok'] = 1;
			}
			
			/* Check if the content has fpvideo */
			if(has_shortcode($data['content'], 'fpvideo')) {
				$data['post_tags'][] = 'has_video';
			}
		}

		if(!empty($metas_data['_og_title'])) {
			$data['metas_data']['_yoast_wpseo_opengraph-title'] = $metas_data['_og_title'];
			$data['metas_data']['_yoast_wpseo_twitter-title'] = $metas_data['_og_title'];
			unset($data['metas_data']['_og_title']);
		}
		if(!empty($metas_data['_yoast_wpseo_opengraph-description'])) {
			$data['metas_data']['_yoast_wpseo_twitter-description'] = $metas_data['_yoast_wpseo_opengraph-description'];
		}

		if(!empty($permalinked_cat)) {
			$data['metas_data']['_category_permalink'] = $permalinked_cat;
			$data['metas_data']['_yoast_wpseo_primary_category'] = $permalinked_cat;
		}

		return $data;
	}

	private function author_id($item, $data)
	{
		$author_id = $this->get_user_id($data, 'old_author_id');
		if(empty($author_id)) {
			return null;
		}

		return $author_id;
	}

	private function wp_admin_id($item, $data)
	{
		$admin_id = $this->get_user_id($data, 'old_manage_client_id');
		if(empty($admin_id)) {
			return null;
		}

		return $admin_id;
	}

	private function prepare_story_thumb($thumbs)
	{
		$story_thumb = '';

		if(!empty($thumbs['url_image_origin'])) {
			return $thumbs['url_image_origin'];
		}

		$pattern = '/^https:\/\/assets\.afcdn\.com\/story\/\d{8}\/\d+_(.*)\.(jpg|webp|png|jpeg)$/';
		foreach ($thumbs as $key => $value) {
			if (preg_match($pattern, $value, $matches)) {
				$story_thumb_with_origin = str_replace($matches[1], 'origin', $value);
				$story_thumb_without_origin = str_replace('_origin', '', $story_thumb_with_origin);
				$file_headers_origin = get_headers($story_thumb_with_origin);
				if(strpos($file_headers_origin[0], '200')){
					$story_thumb = $story_thumb_with_origin;
				}else{
					$file_headers_no_origin = get_headers($story_thumb_without_origin);
					if(strpos($file_headers_no_origin[0], '200')){
						$story_thumb = $story_thumb_without_origin;
					}
				}
				break;
			}
		}

		return $story_thumb;
	}

	private function prepare_story_content($data)
	{
		$content = '';
		$intertitre_count=0;

		foreach ($data as $item) {
			if (empty($item['type'])) {
				continue;
			}
			switch ($item['type']) {
				case 'paragraphe':
					$content .= "<p>{$item['html']}</p>\n";
					break;
				case 'intertitre':
				case 'intertitre-h2':
					if(!empty($item['html'])){
						$content .= "<h2>{$item['html']}</h2>\n";
						if(strlen(trim($item['html'])) >= self::SUMMARY_BLOCK_MIN_LENGTH){
							$intertitre_count++;
						}
					}
					break;
				case 'intertitre-h3':
					if(!empty($item['html'])){
						$content .= "<h3>{$item['html']}</h3>\n";
					}
					break;
				case 'sources':
					$content .= "[af-sources]{$item['html']}[/af-sources]";
					break;
				case 'videomanage':
					$dailymotion_id = $item['obj']['dailymotionID'] ?? '';
					if(!empty($dailymotion_id)) {
						$video_url = "https://www.dailymotion.com/video/".$dailymotion_id;
						$content .= "[fpvideo mediaid='{$video_url}']\n";
					}
					break;
				case 'affilizz':
					$affilizz_id = $item['contentid'] ?? '';
					$affilizz_loading = $item['loading'] ?? '';
					if(!empty($affilizz_id)) {
						$content .= "[affilizz_rendering id='{$affilizz_id}' loading='{$affilizz_loading}']\n";
					}
					break;
				case 'photo':
					$story_photos = $item['storyphotos'] ?? [];
					$image_data = [];
					if(!empty($story_photos)) {
						foreach ($story_photos as $photo) {
							$image_data[] = [
								'img_url' => $photo['images']['originNoCrop'],
								'img_title' => $photo['Titre'],
								'img_desc' => $photo['Description'],
								'img_credit' => $photo['Credit']
							];
						}

						$images = $this->download_images($image_data);
						if(!empty($images)) {
							if(count($images) == 1) {
								$image = reset($images);
								$image_id = $image['image_id'];
								$content .= wp_get_attachment_image($image_id, 'full', false, ['class'=>"wp-image-{$image_id}"])."\n";
							}else {
								$image_ids = array_column($images, 'image_id');
								if(!empty($image_ids)) {
									$content .= "[album ids='".implode(',', $image_ids)."']\n";
								}
							}
						}
					}
					break;
				case 'embed':
					$regex_iframe = '/<iframe\s+([^>]*)>.*?<\/iframe>/ms';
					$item['html'] = preg_replace($regex_iframe,'[iframe $1]', $item['html']);

					$pattern = '/<blockquote class="([^"]+)".*?<\/blockquote>/';
					$item['html'] = preg_replace_callback($pattern, [$this, 'get_social_media_shortcode'], $item['html']);

					$content .= $item['html']."\n";

					break;
				case 'quote':
					$content .= "<blockquote class='af-bloquote'>{$item['html']}</blockquote>";
					break;
				case 'albummanage':
					if(!empty($item['albumid'])) {
						$content .= "[album old_album_id='{$item['albumid']}']\n";
					}
					break;
				case 'faq':
					if (! empty($item['faqData'])) {
						for ($i=0; $i<count($item['faqData']); $i++) {
							$content .= '<div id="faq-' . $this->url_from_string($item['faqData'][$i]['question']) . '" class="intertitre"><span>' . $item['faqData'][$i]['question'] . '</span></div>';
							$content .= '<p class="standard">' . $item['faqData'][$i]['answer'] . '</p>';
						}
					}
					break;
				case 'optimhub-affilizz':
					$attrs = "";
					if (!empty($item['loading'])) {
						$attrs = "loading='{$item['loading']}'";
					}
					if (!empty($item['productId'])) {
						$attrs .= " product-id='{$item['productId']}'";
					}
					if (!empty($item['designId'])) {
						$attrs .= " design-id='{$item['designId']}'";
					}
					if (!empty($item['mediaId'])) {
						$attrs .= " media-id='{$item['mediaId']}'";
					}
					if (!empty($item['typeComponent'])) {
						$attrs .= " type='{$item['typeComponent']}'";
					}
					if(!empty($attrs)){
						$content .= "[affiliz_catalog {$attrs}]\n";
					}
					break;
			}
		}

		if(!empty($content) && $intertitre_count <= 10 && $intertitre_count >= self::SUMMARY_MIN_BLOCK_COUNT) {
			$content = "[sommaire elem='h2']\n".$content;
		}

		return $content;
	}

	private function prepare_post_date($date, $format='Y-m-d H:i:s')
	{
		return !empty($date) && CommonFunctions::validateDate($date, $format) ? $date : '';
	}

	private function get_social_media_shortcode($matches)
	{
		$shortcode = '';
		if (!empty($matches)){
			$type_shortcode = $matches[1];
			$blockquote = $matches[0];
			switch ($type_shortcode) {
				case 'instagram-media':
					$shortcode = $this->replace_content_by_shortcode($blockquote, 'rw_instagram', '/data-instgrm-permalink="(.*?)"/');
					break;
				case 'tiktok-embed':
					$shortcode = $this->replace_content_by_shortcode($blockquote, 'rw_tiktok', '/cite="(.*?)"/');
					break;
				case 'twitter-tweet':
					$shortcode = $this->replace_content_by_shortcode($blockquote, 'rw_twitter', '/href="(https:\/\/twitter\.com\/[^\/]+\/status\/\d+[^"]+)"/');
					break;
			}
		}
		return $shortcode;
	}

	private function replace_content_by_shortcode($blockquote, $shortcode, $pattern)
	{
		$shortcode_html = '';
		if(preg_match($pattern, $blockquote, $matches)) {
			$link = $matches[1];
			$array_link = explode("?", $link);
			$link = reset($array_link);
			$shortcode_html = "[{$shortcode} link='{$link}']";
		}
		return $shortcode_html;
	}

	public function prepare_album_data($post)
	{
		$data = $this->get_data($post);

		if(empty($data)) {
			return [];
		}

		extract($data);

		$data['old_type'] = 'album';

		/* prepare old_path */
		$data['old_path'] = parse_url($old_path, PHP_URL_PATH);

		/* prepare categories */
		$section_ids = [];
		if(!empty($section)) {
			$section_ids[] = $section['id'];
			unset($data['section']);
		}

		if(!empty($sub_section)) {
			$section_ids[] = $sub_section['id'];
			unset($data['sub_section']);
		}

		$categorie_ids = [];
		if(!empty($section_ids)) {
			$taxonomies_data = $this->prepare_taxonomies_entity($section_ids, ['category']);
			if($taxonomies_data) {
				$categorie_ids = array_column($taxonomies_data['category'], 'term_id');
			}

		}

		$permalinked_cat = '';
		if(!empty($categorie_ids)) {
			$data['post_category'] = $categorie_ids;
			$permalinked_cat = count($categorie_ids) == 1 ? reset($categorie_ids) : end($categorie_ids);
		}

		/* prepare tags */
		$post_tags = [];
		if(!empty($_embedded_datas['tag_ids'])) {
			$tags_data = $this->prepare_taxonomies_entity($_embedded_datas['tag_ids'], ['post_tag']);
			if(!empty($tags_data['post_tag'])) {
				$post_tags = array_column($tags_data['post_tag'], 'term_slug');
			}
			unset($data['tag_ids']);
		}

		if(!empty($post_tags)) {
			$data['post_tags'] = $post_tags;
		}

		/* Prepare User */
		if(!empty($user_id)) {
			$author_id = $this->get_user_id($user_id, 'old_manage_client_id');
			if(!empty($author_id)) {
				$data['user_id'] = $author_id;
			}
		}

		$url_image = '';
		/* Prepare Content */
		if(!empty($_embedded_datas['gallery_data'])) {
			$data['gallery_images'] = $_embedded_datas['gallery_data'];
			$url_image = reset($_embedded_datas['gallery_data']);
			$data['post_tags'][] = 'has_diapo';
		}
		unset($data['_embedded_datas']);

		/* prepare published & modified date */
		$date_format = 'Y-m-d\TH:i:sP';
		$data['published'] = $this->prepare_post_date($published ?? '', $date_format);
		$data['modified'] = $this->prepare_post_date($modified ?? '', $date_format);

		/* Prepare status of album */
		if(empty($metas_data['_is_online'])) {
			$data['post_status'] = 'draft';
		}

		/* prepare story thumb */
		if(!empty($url_image)) {
			$data['thumb_data'] = [
				'thumbnail_url' => $url_image['url'],
				'thumbnail_title' => $url_image['title'],
				'thumbnail_meta_key' => 'origin_url'
			];
		}

		if(!empty($metas_data['_yoast_wpseo_title'])) {
			$data['metas_data']['_yoast_wpseo_opengraph-title'] = $metas_data['_yoast_wpseo_title'];
			$data['metas_data']['_yoast_wpseo_twitter-title'] = $metas_data['_yoast_wpseo_title'];
		}

		if(!empty($permalinked_cat)) {
			$data['metas_data']['_category_permalink'] = $permalinked_cat;
			$data['metas_data']['_yoast_wpseo_primary_category'] = $permalinked_cat;
		}

		return $data;
	}

	private function _embedded_datas($item, $data)
	{
		$result = [];
		if(!empty($data['photos'])) {
			$gallery_data = [];
			foreach($data['photos'] as $photo) {
				$url_image = $photo['urlImage'];
				if(empty($url_image)) {
					$url_image = $photo['images']['origin'] ?? '';
				}

				if(!empty($url_image)) {
					$gallery_data[] = [
						'url' => $url_image,
						'title' => $photo['title'],
						'introduction' => balanceTags($photo['text'], true),
						'copyright' => $photo['credit']
					];
				}
			}

			if(!empty($gallery_data)) {
				$result['gallery_data'] = $gallery_data;
			}
		}

		if(!empty($data['tags'])) {
			$result['tag_ids'] = array_column($data['tags'], 'tagID');
		}

		return $result;
	}

	public function get_foundRows() {
		return $this->foundRows ?? 0;
	}

	public function get_department($department_id) {
		$department = null;

		if(empty($this->mapping)) {
			return $department;
		}

		$query = "SELECT * FROM BabyNameDeptName WHERE DeptId = $department_id";

		if($result = $this->db->query($query)) {
			$department = $result->fetch_assoc();
		}

		return $this->get_term_obj($department);
	}

	public function get_departments($args) {
		$departments = [];

		if(empty($this->mapping)) {
			return $departments;
		}

		$offset = !empty($args['offset']) ? $args['offset'] : 0;
		$limit = !empty($args['limit']) ? $args['limit'] : 100;
		$first = !empty($args['first']) ? true : false;

		$query = "SELECT * FROM BabyNameDeptName LIMIT $offset, $limit";

		if($first) {
			$query = preg_replace('/SELECT/', 'SELECT SQL_CALC_FOUND_ROWS', $query, 1);
		}

		if($result = $this->db->query($query)) {		
			$depts = $result->fetch_all(MYSQLI_ASSOC);
			if($first){
				$foundRows = $this->db->query("SELECT FOUND_ROWS() as foundRows");
				$foundRows = $foundRows->fetch_assoc()['foundRows'];
				$this->foundRows = $foundRows;
			}

			foreach($depts as $dept) {
				if($term_obj = $this->get_term_obj($dept)) {
					$departments[] = $term_obj;
				}
			}
		}

		return $departments;
	}

	private function default_data_page($page_id) {
		$data = [
			'613' => [
				"title" => "Calcul d'ovulation",
				'metas_data' => [
					'_yoast_wpseo_title' => "Calcul ovulation : Evaluez votre période d'ovulation pour concevoir",
					'_yoast_wpseo_opengraph-title' => "Calcul ovulation : Evaluez votre période d'ovulation pour concevoir",
					'_yoast_wpseo_metadesc' => "Calcul ovulation : calculez votre période d'ovulation grâce à notre calendrier d'ovulation interactif et découvrez les meilleurs test d'ovulation",
				]
			],
			'1015' => [
				'title' => "Foire aux questions - Vos choix concernant l'utilisation de cookies",
				'metas_data' => [
					'hide_page_title' => true,
				]
			],
			'1167' => [
				'title' => 'EN SAVOIR PLUS SUR NOS PARTENAIRES PUBLICITAIRES',
				'metas_data' => [
					'hide_page_title' => true,
				]
			],
			'1188' => [
				'title' => 'POLITIQUE DE PROTECTION DES DONNÉES PERSONNELLES',
				'metas_data' => [
					'hide_page_title' => true,
				]
			],
			'162'  => [
				'title' => 'MENTIONS LEGALES',
				'metas_data' => [
					'hide_page_title' => true,
				]
			],
			'1673' => [
				'title' => "CONDITIONS GENERALES D'ABONNEMENT",
				'metas_data' => [
					'hide_page_title' => true,
				]
			],
			'1817' => [
				'title' => 'CONDITIONS GÉNÉRALES D’UTILISATION',
				'metas_data' => [
					'hide_page_title' => true,
				]
			],
			'1818' => [
				'title' => "CONDITIONS GENERALES D'ABONNEMENT",
				'metas_data' => [
					'hide_page_title' => true,
				]
			],
			'1819' => [
				'title' => 'CONDITIONS SPÉCIFIQUES DE TESTS PRODUITS',
				'metas_data' => [
					'hide_page_title' => true,
				]
			],
			'2002' => [
				'title' => 'Inscrivez-vous au Live Shopping SVR !',
				'metas_data' => [
					'hide_page_title' => true,
				]
			],
		];
		

		return $data[$page_id] ?? [];
	}

	public function get_babyname_list($list_id) {
		$babyname_list = null;

		if(empty($this->mapping)) {
			return $babyname_list;
		}

		$query = "
			SELECT 
			bl.*, 
			GROUP_CONCAT(blo.OriginId SEPARATOR ',') AS OriginIds, 
			GROUP_CONCAT(bol.Origin SEPARATOR ',') AS OriginNames 
			FROM BabyNameList bl 
			LEFT JOIN BabyNameListOrigin blo ON bl.ListId = blo.ListId 
			LEFT JOIN BabyNameOriginLangage bol ON blo.OriginId = bol.OriginId 
			WHERE bl.SiteId = 1
			AND bl.ListId = {$list_id}
			AND bl.Online = 1
		";

		if($result = $this->db->query($query)) {
			$babyname_list = $result->fetch_assoc();
		}

		$babyname_list = $this->prepare_baby_name_list($babyname_list);

		return $this->get_term_obj($babyname_list);
	}

	public function get_babyname_lists($args) {
		$babyname_lists = [];

		if(empty($this->mapping)) {
			return $babyname_lists;
		}

		$offset = !empty($args['offset']) ? $args['offset'] : 0;
		$limit = !empty($args['limit']) ? $args['limit'] : 100;
		$first = !empty($args['first']) ? true : false;

		$query = "
			SELECT 
			bl.*, 
			GROUP_CONCAT(blo.OriginId SEPARATOR ',') AS OriginIds, 
			GROUP_CONCAT(bol.Origin SEPARATOR ',') AS OriginNames 
			FROM BabyNameList bl 
			LEFT JOIN BabyNameListOrigin blo ON bl.ListId = blo.ListId 
			LEFT JOIN BabyNameOriginLangage bol ON blo.OriginId = bol.OriginId 
			WHERE bl.SiteId = 1
			AND bl.Online = 1
			GROUP BY bl.ListId
			ORDER BY bl.ListId ASC
			LIMIT $offset, $limit
		";

		if($first) {
			$query = preg_replace('/SELECT/', 'SELECT SQL_CALC_FOUND_ROWS', $query, 1);
		}

		if($result = $this->db->query($query)) {
			$lists = $result->fetch_all(MYSQLI_ASSOC);
			if($first){
				$foundRows = $this->db->query("SELECT FOUND_ROWS() as foundRows");
				$foundRows = $foundRows->fetch_assoc()['foundRows'];
				$this->foundRows = $foundRows;
			}

			foreach($lists as $list) {
				$list = $this->prepare_baby_name_list($list);
				if($term_obj = $this->get_term_obj($list)) {
					$babyname_lists[] = $term_obj;
				}
			}
		}

		return $babyname_lists;
	}

	private function prepare_baby_name_list($babyname_list) {
		$babyname_list['babyNameListTitle'] = $babyname_list['Name'].' : Le top des prénoms';
		$babyname_list['babyNameListDescription'] = 'Vous recherchez des idées de prénom pour votre bébé ? Inspirez-vous avec notre top des prénoms';

		if(!empty($babyname_list['OriginNames'])) {
			$babyname_list['OriginNames'] = $this->translatedOriginNames([], $babyname_list['OriginNames']);
		}

		$babyname_list_slug = $this->url_from_string($babyname_list['Name']);
		$babyname_list['urlComplete'] = "/tools/name/{$babyname_list_slug}-l{$babyname_list['ListId']}.html";

		return $babyname_list;
	}

	private function url_from_string($str)
	{
		$result = mb_strtolower($str);

		$result = preg_replace("/< *img[^>]*(>|$)/i", '', $result);
		$result = preg_replace('#\[s:(<[^>]*>)?([a-zA-Z0-9\/\.]+)(<[^>]*>)?([a-zA-Z0-9\-]+)(<[^>]*>)?\]#i', '', $result); // Smileys feeligo
		$result = preg_replace("/:[a-zA-Z]*:/i", ' ', $result); // Smileys
		$result = str_ireplace("&shy;", '', $result);
		$result = preg_replace("/&[A-Za-z]*;/i", ' ', $result); // HTML char
		$result = preg_replace("/&#[0-9]*(;| |$)/i", ' ', $result); // HTML char
		$result = preg_replace("/<wbr\s?\/?>/i", '', $result);
		$result = preg_replace("/(á|à|â|ä|å|ą)/i", 'a', $result);
		$result = preg_replace("/(é|è|ê|ë|ę)/i", 'e', $result);
		$result = preg_replace("/(İ|í|ì|î|ï)/i", 'i', $result);
		$result = preg_replace("/(ó|ò|ô|ö|õ)/i", 'o', $result);
		$result = preg_replace("/(ú|ù|û|ü)/i", 'u', $result);
		$result = str_ireplace("ł", 'l', $result);
		$result = str_ireplace("ś", 's', $result);
		$result = preg_replace("/(ñ|ń)/i", 'n', $result);
		$result = preg_replace("/(ç|ć)/i", 'c', $result);
		$result = preg_replace("/(ź|ż)/i", 'z', $result);
		$result = str_ireplace("ß", 'ss', $result);
		$result = str_ireplace("æ", 'ae', $result);
		$result = str_ireplace("œ", 'oe', $result);

		// Remove forbidden string form URL (just one regex to avoid too much CPU usage
		// Wanted missing: n?varchar|ntext|db_name|n?char(index)?|patindex|unicode|substring + 2words (create table, ...)
		$pattern = "/([^a-z0-9]|^)(cast|convert|char|ascii|replace|len|reverse|select|insert|update|delete|exec(ute)?)([^a-z0-9]|$)/i";
		$result = preg_replace($pattern, '-', $result);

		// Return
		$result = preg_replace("/[^a-z0-9]/i", '-', $result);
		$result = preg_replace("/-{2,}/i", '-', $result);
		$result = trim($result, '-');

		return strtolower($result);
	}

	public function get_baby_names_list_content($oldListId, $newListId) {
		$babynames = [];

		$query = "
			SELECT BabyNameId 
			FROM BabyNameListContent 
			WHERE ListId = {$oldListId} 
			Order By Ordre ASC
		";

		if($result = $this->db->query($query)) {
			$names = $result->fetch_all(MYSQLI_ASSOC);
			$order = 1;
			foreach ($names as $name) {
				$baby_name = Utils::get_post_by_old_id($name["BabyNameId"], 'BabyName_id', 'baby-name');
				if(!empty($baby_name)) {
					$babynames[] = [
						'list_id' => $newListId,
						'baby_name_id' => $baby_name->ID,
						'baby_name' => $baby_name->post_title,
						'order' => $order,
						'old_babyname_list_content' => "{$oldListId}_{$name["BabyNameId"]}_{$order}"
					];
					$order++;
					unset($baby_name);
				}
			}
		}

		return $babynames;
	}

	private function replace_old_ids_with_new($id , $meta_key) {
		$post =  Utils::get_post_by_old_id($id, $meta_key);

		if(!empty($post)) {
			$id = $post->ID;
		}

		return $id;
	}

	public function get_babyname($post_id, $content_type) {
		$post = $this->get_babyname_from_db($post_id);
		return $post;
	}

	public function get_babynames($args, $content_type){
		$posts = [];
		$offset = !empty($args['offset']) ? $args['offset'] : 0;
		$limit = !empty($args['limit']) ? $args['limit'] : 10;
		$first = !empty($args['first']) ? true : false;
		$last_old_id = !empty($args['last_old_id']) ? $args['last_old_id'] : 0;

		$posts = $this->get_babyname_from_db(false, $limit, $offset, $first, $last_old_id);
		return $posts;
	}

	public function get_babyname_from_db($id = false ,$limit = 1,$offset = 0,$first = false, $last_old_id = 0){

		// '' AS SimilarBabyNames : a virtual column to store the ids of similar babynames
		$select = "
			SELECT 
				bn.*, 
				bnc.Name AS 'ColorName', 
				bnm.Name AS 'MetalName', 
				bns.Name AS 'StoneName', 
				bc.Day AS 'celebrationDate',
				GROUP_CONCAT(DISTINCT(bnol.Origin)) AS 'BabyNameOriginNames', 
				'AssociatedNames' AS 'AssociatedBabyNames',
				'SimilarNames' AS 'SimilarBabyNames',
				'CelebritiesNames' AS 'CelebrityBabyNames',
				'StatisticsByDept' AS 'StatisticsByDept',
				'StatisticsByYear' AS 'StatisticsByYear'
			FROM `BabyName` AS bn
			LEFT JOIN `BabyNameColor` AS bnc ON bnc.ColorId = bn.Color
			LEFT JOIN `BabyNameMetal` AS bnm ON bnm.MetalId = bn.Metal
			LEFT JOIN `BabyNameStone` AS bns ON bns.StoneID = bn.Stone
			LEFT JOIN `BabyNameOrigin` AS bno ON bno.BabynameId = bn.id
			LEFT JOIN `BabyNameOriginLangage` AS bnol ON bnol.OriginId = bno.OriginId
			LEFT JOIN `BabyNameCelebrationDay` bc on bn.CelebrationDayId = bc.CelebrationDayId
		";

		$offset = !empty($offset) ? $offset : 0;
		$limit = !empty($limit) ? $limit : 1;

		$where = " WHERE bn.SiteId = ".self::$site_id;
		$where .= " AND bn.Online = 1";

		if($id){
			$where .= " AND bn.id = {$id}";
		}elseif(!empty($last_old_id)) {
			$where .= " AND bn.id < {$last_old_id}";
		}

		$query = "$select $where GROUP BY bn.id ORDER BY bn.id DESC LIMIT $offset, $limit";

		if($first) {
			$query = preg_replace('/SELECT/', 'SELECT SQL_CALC_FOUND_ROWS', $query, 1);
		}

		if($result = $this->db->query($query)) {
			$posts = $result->fetch_all(MYSQLI_ASSOC);
			if($first){
				$foundRows = $this->db->query("SELECT FOUND_ROWS() as foundRows");
				$foundRows = $foundRows->fetch_assoc()['foundRows'];
				$this->foundRows = $foundRows;
			}
			if($limit == 1){
				$posts = reset($posts);
			}
		}

		return $posts;
	}

	//Retreive similars names
	private function babyNameSimilar($item, $data) {
		$similars = [];

		if(empty($item['Name'])){
			return $similars;
		}

		if(empty($item['SiteId'])) {
			return $similars;
		}

		$clean_search = preg_replace('/[^[:alpha:]|-]/u', ' ', $item['Name']);
		$sql_similars = "
			SELECT Id
			FROM BabyName bn
			WHERE Name LIKE '%".$clean_search."%' AND NAME <> '".$clean_search."'
			AND Online=1 AND Killed=0 AND SexId='".$item['SexId']."' and SiteId=1
			ORDER BY Name
			LIMIT 10
		";

		if($result = $this->db->query($sql_similars)) {
			$similar_names = $result->fetch_all(MYSQLI_ASSOC);
			foreach ($similar_names as $similar) {
				$baby_name = Utils::get_post_by_old_id($similar["Id"], 'BabyName_id', 'baby-name');
				if(!empty($baby_name)) {
					$similars[] = $baby_name->ID;
					unset($baby_name);
				}
			}
			unset($similar_names);
		}
		
		return implode(',', $similars);
	}

	private function associatedBabyNames($item, $data) {
		$matches = [];

		$sql_matches = "
			SELECT bnm.matchedBabyNameId
			FROM BabyNameMatches bnm
			INNER JOIN BabyName bn on bn.id = bnm.matchedBabyNameId
			WHERE killed=0 AND mainBabyNameId = ".$item['id']."
		";

		if($result = $this->db->query($sql_matches)) {
			$matche_names = $result->fetch_all(MYSQLI_ASSOC);
			foreach ($matche_names as $match) {
				$baby_name = Utils::get_post_by_old_id($match['matchedBabyNameId'], 'BabyName_id', 'baby-name');
				if(!empty($baby_name)) {
					$matches[] = $baby_name->ID;
					unset($baby_name);
				}
			}
			unset($matche_names);
		}

		return implode(',', $matches);
	}

	private function babyNameCelebrate($item, $data) {
		$celebrities = [];

		$sql_celebrities = "
			SELECT Firstname, Lastname FROM
			BabyNameCelebrityNames bc
			INNER JOIN BabyName b on b.Name = bc.Firstname
			WHERE b.id = ".$item['id']."
		";

		if($result = $this->db->query($sql_celebrities)) {
			$celebrities_names = $result->fetch_all(MYSQLI_ASSOC);
			foreach ($celebrities_names as $celebrity) {
				$celebrities[] = $celebrity['Firstname'] . ' ' . $celebrity['Lastname'];
			}
			unset($celebrities_names);
		}

		return implode(',', $celebrities);
	}

	private function translatedOriginNames($item, $data) {
		$origins = [];

		$origin_names = explode(',', $data);
		if(empty($origin_names)) {
			return $origins;
		}

		foreach($origin_names as $name) {
			$query = "
				SELECT tp.Translation FROM
				TranslationPhp tp
				WHERE tp.SentenceMd5 = MD5('".$name."')
				AND tp.SiteId=1 AND tp.Context='babyname'
			";

			if($result = $this->db->query($query)) {
				$translate_name = $result->fetch_row();
				if(!empty($translate_name[0])) {
					$origins[] = $translate_name[0];
				} else {
					$origins[] = $name;
				}
			}
		}

		return implode(',', $origins);
	}

	private function babyNameStatisticsByDept($item, $data) {
		$statistics = [];

		$query = "
			SELECT Year, Dept, Number, bdp.Name AS Name 
			FROM BabyNameStatisticsByDept bsd 
			INNER JOIN BabyNameDeptName bdp on bsd.Dept = bdp.DeptId 
			WHERE BabyNameId={$item['id']} AND Year=2017 
			ORDER BY Number DESC LIMIT 0,10
		";

		$position = 1;
		if($result = $this->db->query($query)) {
			$stats = $result->fetch_all(MYSQLI_ASSOC);
			foreach ($stats as $statistic) {
				$this->db->query('SET @i=0;');
				$sql_rank = "
					SELECT rank FROM (
						SELECT Dept, BabyNameId, @i:=@i+1 as rank FROM
						BabyNameStatisticsByDept
						WHERE dept = {$statistic['Dept']} and Year=2017 order by number desc
					) as r 
					WHERE babynameid = {$item['id']};
				";

				if($result = $this->db->query($sql_rank)) {
					if ($current = $result->fetch_assoc()) {
						$rank = $current['rank'];
					} else {
						$rank = "NC";
					}
				}

				$statistics[] = [
					"year" => $statistic['Year'],
					"dept" => [
						"id" => $this->get_department_stats($statistic['Dept']),
						"title" => $statistic['Name']
					],
					"nb" => $statistic['Number'],
					"rang" => $rank
				];

				$position++;
			}
		}

		return $statistics;
	}

	private function get_department_stats($dept_id) {
		if(!empty(self::$departments[$dept_id])) {
			return self::$departments[$dept_id];
		}

		$department = $this->get_term_by_id_or_slug('departments', $dept_id);
		if(!empty($department)) {
			self::$departments[$dept_id] = $department['term_id'];
		}

		return self::$departments[$dept_id];
	}

	private function babyNameStatisticsByYear($item, $data){
		$statistics = [];

		$query = "
			SELECT Year, Nb FROM BabyNameStatisticsByYear 
			WHERE id={$item['id']} AND Year <> 'XXXX'
		";

		if($result = $this->db->query($query)) {
			$stats_year = $result->fetch_all(MYSQLI_ASSOC);
			foreach ($stats_year as $statistic) {
				$statistics[] = [
					'year' => $statistic['Year'],
					'nb' => $statistic['Nb']
				];
			}
		}

		return $statistics;
	}

	public function prepare_babyname_data($post) {
		$data = $this->get_data($post);

		if(empty($data)) {
			return [];
		}

		extract($data);

		// type
		$data['post_type'] = "baby-name";
		$data['old_type'] = 'BabyName';

		if(!empty($metas_data['celebrationDay']) && $this->prepare_post_date($metas_data['celebrationDay'])) {
			$date = new \DateTime($metas_data['celebrationDay']);
			$date = $date->format('Y-m-d');
			$data['metas_data']['celebrationDay'] = $date;
		}

		if(!empty($translatedOriginNames)) {
			$data['metas_data']['origin'] = $translatedOriginNames;
			unset($data['translatedOriginNames']);
		}

		$babyname_slug = $this->url_from_string($title);
		$data['old_path'] = "/tools/name/{$babyname_slug}-b{$old_id}.html";

		return $data;
	}
}