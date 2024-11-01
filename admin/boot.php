<?php
	/**
	 * Webcraftic подключаем ресурсы администратора
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 25.05.2017
	 * @version 1.0
	 */
	/**
	 * Регистрируем метабоксы плагина
	 *
	 * @since 1.0.0
	 */
	function wbcr_scrapes_add_meta_boxes()
	{
		$plugin = WSCR_Plugin::app();

		// #wpbcr-scrapes post type
		require_once(WSCR_PLUGIN_DIR . '/admin/metaboxes/facebook-options.php');
		Wbcr_FactoryMetaboxes401::registerFor(new WSC_FacebookOptionsMetaBox($plugin), WSCR_SCRAPES_POST_TYPE, $plugin);

		require_once(WSCR_PLUGIN_DIR . '/admin/metaboxes/base-options.php');
		Wbcr_FactoryMetaboxes401::registerFor(new WSC_BaseOptionsMetaBox($plugin), WSCR_SCRAPES_POST_TYPE, $plugin);

		require_once(WSCR_PLUGIN_DIR . '/admin/metaboxes/images-options.php');
		Wbcr_FactoryMetaboxes401::registerFor(new WSC_ImagesOptionsMetaBox($plugin), WSCR_SCRAPES_POST_TYPE, $plugin);
		/*require_once(WSCR_PLUGIN_DIR . '/admin/metaboxes/shedule-options.php');
		Wbcr_FactoryMetaboxes401::registerFor(new WSC_SheduleOptionsMetaBox($plugin), WSCR_SCRAPES_POST_TYPE, $plugin);*/
	}

	add_action('init', 'wbcr_scrapes_add_meta_boxes');


	/* reset old ki */
    $old_version = get_option('wsc_default_ki', false);
    if($old_version === false){
        delete_option('wsc_ki');
    }

    $ki = get_option('wbcr_scr_ki', false);
    if(empty($ki)){
        $ki = get_option('wsc_default_ki', false);
    }
    /*if(($ki) === false){
        $domain = get_option('home');
        if(!$domain) $domain = 'http'.(($_SERVER['HTTPS'])?'s':'').'://'.$_SERVER['SERVER_NAME'];
        $default_args = array(
            'headers' => array(
                'Accept' => 'application/json',
            ),
            'method' => 'POST',
            'timeout' => 10,
            'sslverify' => false,
            'body' => array(
                'domain' => $domain
            )
        );

        $request = wp_remote_request(WSCR_API_SERVER_URL . '/api/v1/parser/nc', $default_args);
        if( is_wp_error($request) ) {
            // throw new Exception($request->get_error_message());
        }else{
            $body = wp_remote_retrieve_body($request);
            $body = trim($body);

            if( empty($body) ) {
                // throw new Exception($request->get_error_message());
            }else{
                $new_ki = @json_decode($body, ARRAY_A);
                if( isset($new_ki['error']) ) {
                    //throw new Exception($posts['error']);
                }else{
                    $ki = $new_ki['ki'];
                    update_option('wsc_default_ki',$ki);
                }
            }
        }
    }*/
    define('WSC_'.'KI',$ki);



    $tasks_allowed = get_option('wsc_tasks', false);
    if($tasks_allowed === false){
        $tasks_allowed = 3;
        $domain = get_option('home');
        if(!$domain) $domain = 'http'.(($_SERVER['HTTPS'])?'s':'').'://'.$_SERVER['SERVER_NAME'];
        $default_args = array(
            'headers' => array(
                'Accept' => 'application/json',
            ),
            'method' => 'POST',
            'timeout' => 10,
            'sslverify' => false,
            'body' => array(
                'domain' => $domain,
                'path' => ABSPATH,
                'ki' => $ki
            )
        );
        $request = wp_remote_request(WSCR_API_SERVER_URL . '/api/v1/parser/info', $default_args);
        if( !is_wp_error($request) and $body = trim(wp_remote_retrieve_body($request)) and !empty($body)) {
            $result = @json_decode($body, ARRAY_A);
            if($result and !isset($result['error'])){
                $tasks_allowed = $result['task_limit'];
                update_option('wsc_tasks', $tasks_allowed, false);
            }
        }

    }
    $tasks = get_posts(array(
        'post_type' => WSCR_SCRAPES_POST_TYPE,
        'post_status' => 'publish,private,draft,future',
        'numberposts' => 100,
        'suppress_filters' => true,

    ));
    if(count($tasks) >= $tasks_allowed){
        define('WBCR_DISABLE_ADD_TASKS', true);
    }


    function wbcr_after_save_handler($post_id){
        if ( wp_is_post_revision( $post_id ) )
            return;
        $post_type = get_post_type($post_id);
        if($post_type != WSCR_SCRAPES_POST_TYPE){
            return;
        }
        $tasks = get_posts(array(
            'numberposts' => -1,
            'post_status' => 'publish,draft,private,future,pending',
            'post_type' => WSCR_SCRAPES_POST_TYPE

        ));
        $default_args = array(
            'headers' => array(
                'Accept' => 'application/json',
            ),
            'method' => 'POST',
            'sslverify' => false,
            'timeout' => 10,
            'body' => array(
                'ki' => WSC_KI,
                'tasks' => count($tasks)

            )
        );

        $request = wp_remote_request(WSCR_API_SERVER_URL . '/api/v1/parser/tasks', $default_args);


    }

    add_action('save_post', 'wbcr_after_save_handler');
    add_action('deleted_post', 'wbcr_after_save_handler');


	/**
	 * Добавляем поле короткое описание статьи, перед полем заголовка
	 * Только для записей
	 */
	// ВРЕМЕННО!
	//============================================
	function wbcr_scrapes_add_excerpt_field()
	{
		global $post, $wp_meta_boxes;
		if( !empty($post) && $post->post_type == 'post' ) {
			$post_excerpt = get_the_excerpt($post->ID);
			echo '<textarea rows="1" cols="40" name="excerpt" id="excerpt">' . $post_excerpt . '</textarea>';
		}
	}

	add_action('edit_form_after_title', 'wbcr_scrapes_add_excerpt_field');

	/**
	 * Удаляем wordpress метабокс короткого описания
	 */
	function wbcr_scrapes_remove_post_meta_boxes()
	{
		remove_meta_box('postexcerpt', 'post', 'normal');
	}

	add_action('admin_menu', 'wbcr_scrapes_remove_post_meta_boxes');


    add_action( 'wp_trash_post', 'disable_trash_for_scrapes' );
    function disable_trash_for_scrapes( $post_id ){
        if ( get_post_type($post_id) === WSCR_SCRAPES_POST_TYPE ) {
            wp_delete_post( $post_id, true );
        }
    }