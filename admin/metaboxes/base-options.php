<?php
	
	class WSC_BaseOptionsMetaBox extends Wbcr_FactoryMetaboxes401_FormMetabox {
		
		/**
		 * A visible title of the metabox.
		 *
		 * Inherited from the class FactoryMetabox.
		 * @link http://codex.wordpress.org/Function_Reference/add_meta_box
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $title;
		
		
		/**
		 * A prefix that will be used for names of input fields in the form.
		 * Inherited from the class FactoryFormMetabox.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $scope = 'wbcr_scrapes';
		
		/**
		 * The priority within the context where the boxes should show ('high', 'core', 'default' or 'low').
		 *
		 * @link http://codex.wordpress.org/Function_Reference/add_meta_box
		 * Inherited from the class FactoryMetabox.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $priority = 'core';
		
		public $cssClass = 'factory-bootstrap-401 factory-fontawesome-321';

		protected $errors = array();
		protected $source_channel;
		protected $facebook_group_id;
		protected $paginate_url;
		
		public function __construct($plugin)
		{
			parent::__construct($plugin);
			
			$this->title = __('Базовые настройки', 'wpcr-scrapes');
		}

		public function save($post_id)
		{
			if( !$this->checkErrors() ) {
				parent::save($post_id);
			}
		}

		public function checkErrors()
		{
			global $post;

			$this->source_channel = WSCR_Helper::getMetaOption($post->ID, 'source_channel', 'facebook_feed');

			$is_error = false;
			if( !empty($post) ) {
				if( $this->source_channel == 'facebook_feed' ) {
					$this->facebook_group_id = WSCR_Helper::getMetaOption($post->ID, 'facebook_group_id');
					if( empty($this->facebook_group_id) ) {
						$is_error = true;
					}
				}
				if( $this->source_channel == 'site_stream' ) {
					$this->paginate_url = esc_url(WSCR_Helper::getMetaOption($post->ID, 'paginate_url'));
					if( empty($this->paginate_url) ) {
						$is_error = true;
					}
				}
				if( $this->source_channel == 'default' ) {
					$collected_links = esc_url(WSCR_Helper::getMetaOption($post->ID, 'collected_links'));
					if( empty($collected_links) ) {
						$is_error = true;
					}
				}
			}

			if( empty($post) || empty($this->source_channel) || $is_error ) {
				$this->errors[] = 'Пожалуйста, завершите настройки первого шага.';
			}

			if( !empty($this->errors) ) {
				return true;
			}

			return false;
		}

		public function html()
		{
			parent::html();
			foreach($this->errors as $error) {
				echo $error;
			}
		}

		/**
		 * Configures a metabox.
		 *
		 * @since 1.0.0
		 * @param Factory401_ScriptList $scripts A set of scripts to include.
		 * @param Factory401_StyleList $styles A set of style to include.
		 * @return void
		 */
		//public function configure($scripts, $styles)
		//{
		// method must be overriden in the derived classed.
		//$styles->add(WSCR_PLUGIN_URL . '/admin/assets/general.css');
		//}
		
		/**
		 * Configures a form that will be inside the metabox.
		 *
		 * @see Wbcr_FactoryMetaboxes401_FormMetabox
		 * @since 1.0.0
		 *
		 * @param FactoryForms401_Form $form A form object to configure.
		 * @return void
		 */
		public function form($form)
		{
			if( $this->checkErrors() ) {
				return;
			}

			global $post;

			/*$items[] = array(
				'type' => 'hidden',
				'name' => 'item',
				'default' => isset($_GET['wbcr_scrapes'])
					? $_GET['wbcr_scrapes']
					: null
			);*/

			if( $this->source_channel == 'facebook_feed' ) {
				//$dropdown_data = array();
				$dropdown_title = __('Выберите сайт', 'wpcr-scrapes');
				//if( $this->source_channel == 'facebook_feed' ) {
				$dropdown_data = array(
					'ajax' => true,
					'url' => admin_url('admin-ajax.php'),
					'data' => array(
						'action' => 'wbcr_scrapes_get_sites',
						'facebook_group_id' => $this->facebook_group_id,
						'task_id' => $post->ID
					)
				);
				//} else if( $this->source_channel == 'site_stream' ) {
				//$dropdown_title = __('Адрес сайта', 'wpcr-scrapes');
				/*$dropdown_data = array(
					array(
						$this->paginate_url,
						WSCR_Helper::getDomainByUrl($this->paginate_url)
					)
				);*/
				//}

				$items[] = array(
					'type' => 'dropdown',
					'name' => 'site_url',
					'data' => $dropdown_data,
					'empty' => __('- empty -', 'wpcr-scrapes'),
					'title' => $dropdown_title,
					'hint' => __('Введите сайт, для создания шаблона. Данный шаблон будет применяться для точности собираемой информации.', 'wpcr-scrapes')
				);
			}

			$items[] = array(
				'type' => 'textbox',
				'name' => 'post_title',
				'placeholder' => __('Введите строку в формате xpath', 'wpcr-scrapes'),
				'title' => __('Отметьте заголовок записи', 'wpcr-scrapes'),
				'hint' => __('Укажите парсеру, из какой части шаблона брать заголовок записи.', 'wpcr-scrapes')
			);

			$items[] = array(
				'type' => 'textbox',
				'name' => 'post_content',
				'placeholder' => __('Введите строку в формате xpath', 'wpcr-scrapes'),
				'title' => __('Отметьте содержание записи', 'wpcr-scrapes'),
				'hint' => __('Укажите парсеру, из какой части шаблона брать содержание записи.', 'wpcr-scrapes')
			);

			$cats = get_categories(array(
				'hide_empty' => 0
			));

			$categories_checklist_data = array();

			foreach($cats as $term) {
				$categories_checklist_data[] = array($term->term_id, $term->name);
			}

			$items[] = array(
				'type' => 'list',
				'way' => 'checklist',
				'name' => 'categories',
				'title' => __('Выберите категории', 'wpcr-scrapes'),
				'data' => $categories_checklist_data,
				'hint' => __('Выберите кактегории, которые необходимо присвоить добавленным записям.', 'wpcr-scrapes')
			);

			$items[] = array(
				'type' => 'dropdown',
				'way' => 'buttons',
				'name' => 'post_status',
				'data' => array(
					array('draft', __('Черновик', 'wpcr-scrapes')),
					array('publish', __('Опубликована', 'wpcr-scrapes')),
					array('pending', __('На утверждении', 'wpcr-scrapes')),
					array('private', __('Личная', 'wpcr-scrapes')),

				),
				'title' => __('Статус записи (по умолчанию)', 'wpcr-scrapes'),
				'hint' => __('Выберите статус записи, который будет установлен ей после сохранения.', 'wpcr-scrapes'),
				'default' => 'draft'
			);
			
			if( $this->source_channel == 'facebook_feed' ) {
				$items[] = array(
					'type' => 'dropdown',
					'way' => 'buttons',
					'name' => 'post_excerpt',
					'data' => array(
						array(
							'og_description',
							__('Мета тег og:description', 'wpcr-scrapes')
						),
						array('facebook_feed_description', __('Запись в Facebook', 'wpcr-scrapes'))
					),
					/*'events' => array(
						'all_urls' => array(
							'hide' => '.factory-control-nesting_level'
						),
						'only_current_page' => array(
							'hide' => '.factory-control-nesting_level'
						),
						'custom' => array(
							'show' => '.factory-control-nesting_level'
						)
					),*/
					'title' => __('Краткое описание записи', 'wpcr-scrapes'),
					'hint' => __('Выберите режим, откуда извлекать краткое описание записи.', 'wpcr-scrapes'),
					'default' => 'og_description'
				);
			}

			$items[] = array(
				'type' => 'multiple-textbox',
				'name' => 'html_filters',
				'title' => __('Фильтры', 'wpcr-scrapes'),
				'placeholder' => __('Введите строку в формате xpath', 'wpcr-scrapes'),
				'hint' => __('Если Вкл., плагин будет собирать внешние ссылки на другие сайты. Но не будет переходить по ним.', 'wpcr-scrapes'),
				//'default' => 'sdfsdf,fsdfsdf,gdfhfgh'
			);

			$form->add($items);
		}


		public function onSavingForm($postId)
		{
			if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
				return;
			}

			update_post_meta($postId, 'wbcr_scrapes_workstatus', 'waiting');
			update_post_meta($postId, 'wbcr_scrapes_run_count', 0);
			update_post_meta($postId, 'wbcr_scrapes_start_time', '');
			update_post_meta($postId, 'wbcr_scrapes_end_time', '');
			update_post_meta($postId, 'wbcr_scrapes_task_id', $postId);
		}
	}
	
	Wbcr_FactoryMetaboxes401::register('WSC_BaseOptionsMetaBox', WSCR_Plugin::app());

	