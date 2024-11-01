<?php
	
	class WSC_FacebookOptionsMetaBox extends Wbcr_FactoryMetaboxes401_FormMetabox {
		
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

		public function __construct($plugin)
		{
			parent::__construct($plugin);
			
			$this->title = __('ШАГ #1. Настройки', 'wpcr-scrapes');
		}

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
			global $post;
			$source_channel = WSCR_Helper::getMetaOption($post->ID, 'source_channel');

			$items[] = array(
				'type' => 'html',
				'html' => array($this, 'printScripts')
			);

			if( empty($source_channel) ) {
				$items[] = array(
					'type' => 'dropdown',
					'way' => 'buttons',
					'name' => 'source_channel',
					'data' => array(
						/*array(
							'facebook_feed',
							__('Facebook страницы', 'wpcr-scrapes')
						),*/
						array('site_stream', __('Сайты', 'wpcr-scrapes')),
						array('default', __('Ссылки', 'wpcr-scrapes'))
					),
					'events' => array(
						/*'facebook_feed' => array(
							'show' => '.factory-control-facebook_group_id, .factory-control-facebook_date_range_filter, .factory-control-load_posts_limit',
							'hide' => '.factory-control-collected_links, .factory-control-paginate_url, .factory-control-post_per_page, .factory-control-xpath_post_title_url',
						),*/
						'site_stream' => array(
							'show' => '.factory-control-paginate_url, .factory-control-post_per_page, .factory-control-xpath_post_title_url',
							'hide' => '.factory-control-collected_links, .factory-control-facebook_group_id, .factory-control-facebook_date_range_filter, .factory-control-load_posts_limit'
						),
						'default' => array(
							'show' => '.factory-control-collected_links',
							'hide' => '.factory-control-posts_limit, .factory-control-paginate_url, .factory-control-post_per_page, .factory-control-xpath_post_title_url, .factory-control-facebook_group_id, .factory-control-facebook_date_range_filter, .factory-control-load_posts_limit'
						)
					),
					'title' => __('Выберите канал для парсинга', 'wpcr-scrapes'),
					//'hint' => __('Facebook страницы - это сбор популярных записе', 'wpcr-scrapes'),
					'default' => 'site_stream'
				);
			}

			if( empty($source_channel) || $source_channel == 'facebook_feed' ) {
				$items[] = array(
					'type' => 'textbox',
					'name' => 'facebook_group_id',
					'title' => __('ID группы в facebook', 'wpcr-scrapes'),
					'hint' => __('Введите ID выбранной вами группы в facebook, из этой группы будут извлекаться ссылки на импортируемый контент.', 'wpcr-scrapes')
				);
			}

			if( empty($source_channel) || $source_channel == 'default' ) {
				$items[] = array(
					'type' => 'textarea',
					'name' => 'collected_links',
					'title' => __('Ссылки для парсинга', 'wpcr-scrapes'),
					'hint' => __('Вставляйте ссылки на записи, которые хотите спарсить. Все ссылки добавляете с новой строки.', 'wpcr-scrapes')
				);
			}

			if( empty($source_channel) || $source_channel == 'site_stream' ) {
				$items[] = array(
					'type' => 'textbox',
					'name' => 'paginate_url',
					'title' => __('Введите ссылку пагинации', 'wpcr-scrapes'),
					'hint' => __('Парсер будет переходить по страницам, используя этот шаблон ссылки, чтобы спарсить все записи.', 'wpcr-scrapes')
				);

				$items[] = array(
					'type' => 'textbox',
					'name' => 'xpath_post_title_url',
					'placeholder' => __('Введите строку в формате xpath', 'wpcr-scrapes'),
					'title' => __('Отметьте ссылку на запись', 'wpcr-scrapes'),
					'hint' => __('Укажите парсеру, откуда брать ссылки на страницы записей.', 'wpcr-scrapes')
				);

				$items[] = array(
					'type' => 'textbox',
					'name' => 'post_per_page',
					'title' => __('Сколько записей на странице?', 'wpcr-scrapes'),
					'hint' => __('Введите число записей, которые вы видите на странице пагинации.', 'wpcr-scrapes'),
					'default' => 10
				);

				$items[] = array(
					'type' => 'hidden',
					'name' => 'site_url',
                    'filter_value' => array($this, 'rawFilter')
				);
			}

			if( empty($source_channel) || $source_channel == 'facebook_feed' || $source_channel == 'site_stream' ) {
				$items[] = array(
					'type' => 'textbox',
					'name' => 'posts_limit',
					'title' => __('Лимит публикуемых записей', 'wpcr-scrapes'),
					'hint' => __('Установите сколько вам нужно собрать и опубликовать записей за одно выполнение задания.', 'wpcr-scrapes'),
					'default' => 3

				);
			}

			if( empty($source_channel) || $source_channel == 'facebook_feed' ) {
				$items[] = array(
					'type' => 'textbox',
					'name' => 'load_posts_limit',
					'title' => __('Лимит записей для анализа', 'wpcr-scrapes'),
					'hint' => __('Установите из скольки записей нужно отобрать самые популярные. Это число должно быть больше, чем лимит публикуемых записей. По умолчанию 10.', 'wpcr-scrapes'),
					'default' => 10
				);

				$items[] = array(
					'type' => 'checkbox',
					'way' => 'buttons',
					'name' => 'facebook_date_range_filter',
					'title' => __('Отбирать записи по дате?', 'wpcr-scrapes'),
					'hint' => __('Если Вкл., вы сможете установить настройки выбора записей за установленный период времени.', 'wpcr-scrapes'),
					'default' => false,
					'eventsOn' => array(
						'show' => '.factory-control-facebook_start_date_filter'
					),
					'eventsOff' => array(
						'hide' => '.factory-control-facebook_start_date_filter'
					)
				);

				$items[] = array(
					'type' => 'datetimepicker-range',
					'name' => 'facebook_start_date_filter',
					'range_1' => array(
						'format' => 'YYYY/MM/DD HH:mm',
						'default' => date('Y/m/d H:i', strtotime('-1 week'))
					),
					'range_2' => array(
						'format' => 'YYYY/MM/DD HH:mm',
						'default' => date('Y/m/d H:i')
					),
					'title' => __('Выберите период', 'wpcr-scrapes'),
					'hint' => __('Если Вкл., вы сможете установить настройки выбора записей за установленный период времени.', 'wpcr-scrapes')
				);
			}

			$form->add($items);
		}

		/*
		 * Печать глобальных переменных
		 */
		public function printScripts()
		{
			global $post;
			?>
			<script>
				var wbcrScrapesPluginPath = '<?=WSCR_PLUGIN_URL?>';
				var wbcrScrapesSourceChannel = '<?=WSCR_Helper::getMetaOption($post->ID, 'source_channel')?>';
			</script>
		<?php
		}

        /**
         * Метод для фильтрации value
         * @param string $value
         * @param string $raw_value
         * @return mixed
         */
        public function rawFilter($value, $raw_value)
        {
            // Вернуть не фильтрованное значение
            return $raw_value;
        }
	}

	Wbcr_FactoryMetaboxes401::register('WSC_FacebookOptionsMetaBox', WSCR_Plugin::app());

