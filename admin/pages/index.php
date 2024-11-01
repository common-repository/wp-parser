<?php
	// Exit if accessed directly
	if( !defined('ABSPATH') ) {
		exit;
	}
	
	class WSCR_IndexPage extends Wbcr_FactoryPages402_AdminPage {

		/**
		 * The id of the page in the admin menu.
		 *
		 * Mainly used to navigate between pages.
		 * @see FactoryPages402_AdminPage
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $id = "index";

		public $internal = true;

		private $meta_options;

		/**
		 * @param Wbcr_Factory401_Plugin $plugin
		 */
		public function __construct(Wbcr_Factory401_Plugin $plugin)
		{
			$this->menu_title = __('Процесс парсинга', 'wbcr-scrapes');
			//$this->menuIcon = "\f226";
			$this->menu_post_type = WSCR_SCRAPES_POST_TYPE;
			$this->capabilitiy = "read_wbcr-scrapes";

			parent::__construct($plugin);

			$this->plugin = $plugin;
		}

		public function assets($scripts, $styles)
		{
			$this->styles->add(WSCR_PLUGIN_URL . '/assets/css/feed.css');
			$this->scripts->add(WSCR_PLUGIN_URL . '/assets/js/feed.js');
		}

		protected function getOption($task_id, $option_name, $default = null)
		{
			$task_id = (int)$task_id;
			$prefix = 'wbcr_scrapes_';

			if( empty($this->meta_options) ) {
				$meta_vals = get_post_meta($task_id, '', true);

				foreach($meta_vals as $name => $val) {
					$this->meta_options[$name] = $val[0];
				}
			}

			return isset($this->meta_options[$prefix . $option_name])
				? $this->meta_options[$prefix . $option_name]
				: $default;
		}

		protected function updateOption($task_id, $option_name, $option_value)
		{
			return update_post_meta($task_id, 'wbcr_scrapes_' . $option_name, $option_value);
		}

		protected function removeOption($task_id, $option_name)
		{
			return delete_post_meta($task_id, 'wbcr_scrapes_' . $option_name);
		}

		public function indexAction()
		{

			$task_id = $this->request->get('task_id');

			try {
				if( empty($task_id) ) {
					throw new Exception(__('Arguments task_id is empty', 'wbcr-scrapes'));
				}

				$source_channel = WSCR_Helper::getMetaOption($task_id, 'source_channel');

				if( $source_channel == 'facebook_feed' ) {
					require_once WSCR_PLUGIN_DIR . '/admin/includes/class.scrape-facebook-posts.php';
					$scrape_posts = new WSC_ScrapeFacebookPosts($task_id);
					$posts = $scrape_posts->run();
				} else if( $source_channel == 'site_stream' ) {
					require_once WSCR_PLUGIN_DIR . '/admin/includes/class.scrape-site-stream-posts.php';
					$scrape_posts = new WSC_ScrapeSiteSreamPosts($task_id);
					$posts = $scrape_posts->run();
				} else if( $source_channel == 'default' ) {
					require_once WSCR_PLUGIN_DIR . '/admin/includes/class.scrape-posts-by-links.php';
					$scrape_posts = new WSC_ScrapePostsByLinks($task_id);
					$posts = $scrape_posts->run();
				} else {
					throw new Exception('Не известная ошибка!');
				}

				echo '<div id="WBCR">';
				echo '<div class="factory-bootstrap-401 factory-fontawesome-321">';
				echo '<div class="wbcr-scrapes-feed">';

				foreach((array)$posts as $post) {
					if( $source_channel == 'facebook_feed' ) {
						echo '<div class="wbcr-scrapes-feed-item">';
						echo '<div class="wbcr-scrapes-meta"><i class="fa fa-smile-o" aria-hidden="true"></i> <span class="wbcr-scrapes-emotions-count">' . $post->facebook_emotions . '</span><a href="#" class="wbcr-scrapes-remove-feed-item" data-post-id="' . $post->getPostId() . '">' . __('Удалить', 'wbcr-scrapes') . '</a></div>';
						echo '<figure class="wbcr-scrapes-preview"><img src="' . $post->facebook_image . '" width="500"></figure>';
						echo '<h3 class="wbcr-scrapes-title"><a href="' . $post->facebook_permalink . '">' . $post->page_title . '</a></h3>';
						echo '<div class="wbcr-scrapes-description">' . $post->facebook_description . '</div>';
						echo '<div class="wbcr-scrapes-more-buttons"><a href="' . $post->origin_url . '" target="_blank">' . __('Посмотреть источник', 'wbcr-scrapes') . '</a> | <a href="' . admin_url('post.php?post=' . $post->getPostId() . '&action=edit') . '" target="_blank">' . __('Перейти к записи', 'wbcr-scrapes') . '</a></div>';
					} else {
						echo '<div class="wbcr-scrapes-feed-item">';
						echo '<div class="wbcr-scrapes-meta"><a href="#" class="wbcr-scrapes-remove-feed-item" data-post-id="' . $post->getPostId() . '">' . __('Удалить', 'wbcr-scrapes') . '</a></div>';
						echo '<figure class="wbcr-scrapes-preview"><img src="' . get_the_post_thumbnail_url($post->getPostId(), 'full') . '" width="500"></figure>';
						echo '<h3 class="wbcr-scrapes-title"><a href="' . $post->origin_url . '">' . $post->page_title . '</a></h3>';
						echo '<div class="wbcr-scrapes-description">' . $post->page_description . '</div>';
						echo '<div class="wbcr-scrapes-more-buttons"><a href="' . $post->origin_url . '" target="_blank">' . __('Посмотреть источник', 'wbcr-scrapes') . '</a> | <a href="' . admin_url('post.php?post=' . $post->getPostId() . '&action=edit') . '" target="_blank">' . __('Перейти к записи', 'wbcr-scrapes') . '</a></div>';
					}
					echo (!$post->isNewPost())
						? '<div class="wbcr-scrapes-post-already-added">' . __('УЖЕ ДОБАВЛЕНА', 'wbcr-scrapes') . '</div>'
						: '';
					echo '</div>';
					/* else {
						echo '<div>';
						echo '<h4>Задание было прервано из-за внутренней ошибки.</h4>';
						echo 'Url facebook записи: ' . $post->permalink_url . "<br>";
						echo 'Url получаемой страницы: ' . $post->getUrl() . "<br>";
						echo '</div>';
					}*/
				}

				echo '</div>';
				echo '</div>';
				echo '</div>';
			} catch( Exception $e ) {
				WSCR_Helper::writeLog($e->getMessage());
				echo $e->getMessage();
				//$this->removeOption($task_id, 'workstatus', 'running');
			}
		}
	}

	