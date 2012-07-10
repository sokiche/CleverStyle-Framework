<?php
namespace	cs;
use			\h;
/**
 * Provides next triggers:<br>
 *  System/Page/pre_display<code>
 *  System/Page/get_header_info<code>
 *  [
 *   'id'	=> <i>user_id</i><br>
 *  ]</code>
 */
class Page {
	public		$Content, $interface = true,
				$Html = '', $Keywords = '', $Description = '', $Title = [],
				$debug_info = '',
				$Head		= '',
				$pre_Body	= '',
					$Header	= '',
						$mainmenu = '', $mainsubmenu = '', $menumore = '',
					$Left	= '',
					$Top	= '',
					$Right	= '',
					$Bottom	= '',
					$Footer	= '',
				$post_Body	= '',
				$level		= [					//Number of tabs by default for margins the substitution
					'Head'				=> 2,	//of values into template
					'pre_Body'			=> 2,
					'Header'			=> 4,
					'mainmenu'			=> 3,
					'mainsubmenu'		=> 3,
					'menumore'			=> 3,
					'user_info'			=> 5,
					'debug_info'		=> 3,
					'Left'				=> 7,
					'Top'				=> 7,
					'Content'			=> 8,
					'Bottom'			=> 7,
					'Right'				=> 7,
					'Footer'			=> 4,
					'post_Body'			=> 2
				];

	protected	$theme, $color_scheme, $pcache_basename, $includes,
				$user_avatar_image, $user_info,
				$core_js	= [0 => '', 1 => ''],
				$core_css	= [0 => '', 1 => ''],
				$js			= [0 => '', 1 => ''],
				$css		= [0 => '', 1 => ''],
				$Search		= [],
				$Replace	= [];

	function __construct () {
		global $interface;
		$this->interface = (bool)$interface;
		unset($GLOBALS['interface']);
	}
	function init ($name, $keywords, $description, $theme, $color_scheme) {
		$this->Title[0] = htmlentities($name, ENT_COMPAT, 'utf-8');
		$this->Keywords = $keywords;
		$this->Description = $description;
		$this->set_theme($theme);
		$this->set_color_scheme($color_scheme);
	}
	function set_theme ($theme) {
		$this->theme = $theme;
	}
	function set_color_scheme ($color_scheme) {
		$this->color_scheme = $color_scheme;
	}
	function content ($add, $level = false) {
		if ($level !== false) {
			$this->Content .= h::level($add, $level);
		} else {
			$this->Content .= $add;
		}
	}
	/**
 	 * Loading of theme template
	 */
	protected function get_template () {
		global $Config, $L;
		/**
 		 * Theme detection
		 */
		if (is_object($Config) && $Config->core['allow_change_theme']) {
			$this->theme		= in_array($this->theme, $Config->core['active_themes']) ? $this->theme : $Config->core['theme'];
			$theme				= _getcookie('theme');
			if ($theme && $theme !== $this->theme && in_array($theme, $Config->core['active_themes'])) {
				$this->theme = $theme;
			}
			unset($theme);
			$this->theme		= in_array($this->theme, $Config->core['active_themes']) ? $this->theme : $Config->core['theme'];
			$theme				= _getcookie('theme');
			if ($theme && $theme !== $this->theme && in_array($theme, $Config->core['active_themes'])) {
				$this->theme = $theme;
			}
			unset($theme);
			$this->color_scheme	= in_array($this->color_scheme, $Config->core['color_schemes'][$this->theme]) ? $this->color_scheme : $Config->core['color_schemes'][$this->theme][0];
			$color_scheme		= _getcookie('color_scheme');
			if ($color_scheme && $color_scheme !== $this->color_scheme && in_array($color_scheme, $Config->core['color_schemes'][$this->theme])) {
				$this->color_scheme = $color_scheme;
			}
			unset($color_scheme);
		}
		/**
 		 * Base name for cache files
		 */
		$this->pcache_basename = '_'.$this->theme.'_'.$this->color_scheme.'_'.$L->clang.'.';
		/**
 		 * Template loading
		 */
		if ($this->interface) {
			ob_start();
			if (
				is_object($Config) && $Config->core['site_mode'] &&
				(file_exists(THEMES.'/'.$this->theme.'/index.html') || file_exists(THEMES.'/'.$this->theme.'/index.php'))
			) {
				_include_once(THEMES.'/'.$this->theme.'/prepare.php', false);
				if (!_include_once(THEMES.'/'.$this->theme.'/index.php', false)) {
					_include_once(THEMES.'/'.$this->theme.'/index.html');
				}
			} elseif (!($Config->core['site_mode'] == 1 && _include_once(THEMES.'/'.$this->theme.'/closed.html'))) {
				echo	"<!doctype html>\n".
						"<html>\n".
						"	<head>\n".
						"<!--head-->\n".
						"	</head>\n".
						"	<body>\n".
						"<!--content-->\n".
						"	</body>\n".
						"</html>";
			}
			$this->Html = ob_get_clean();
		}
	}
	/**
 	 * Processing of template, cubstituting of content, preparing for the output
	 */
	protected function prepare () {
		global $copyright, $L, $Config;
		/**
 		 * Loading of template
		 */
		$this->get_template();
		/**
 		 * Loading of CSS and JavaScript
		 */
		$this->get_includes();
		/**
 		 * Getting user information
		 */
		$this->get_header_info();
		/**
 		 * Forming page title
		 */
		foreach ($this->Title as $i => $v) {
			if (!trim($v)) {
				unset($this->Title[$i]);
			} else {
				$this->Title[$i] = trim($v);
			}
		}
		if (is_object($Config)) {
			$this->Title = $Config->core['title_reverse'] ? array_reverse($this->Title) : $this->Title;
			$this->Title = implode(' '.trim($Config->core['title_delimiter']).' ', $this->Title);
		} else {
			$this->Title = $this->Title[0];
		}
		/**
		 * Forming <head> content
		 */
		if ($this->core_css[1]) {
			$this->core_css[1] = h::style($this->core_css[1]);
		}
		if ($this->css[1]) {
			$this->css[1] = h::style($this->css[1]);
		}
		if ($this->core_js[1]) {
			$this->core_js[1] = h::script($this->core_js[1]);
		}
		if ($this->js[1]) {
			$this->js[1] = h::script($this->js[1]);
		}
		$this->Head =	h::title($this->Title).
			h::meta(
				[
					'name'		=> 'keywords',
					'content'	=> $this->Keywords
				],
				[
					'name'		=> 'description',
					'content'	=> $this->Description
				],
				[
					'name'		=> 'generator',
					'content'	=> $copyright[0]
				],
				ADMIN || API ? [
					'name'		=> 'robots',
					'content'	=> 'noindex,nofollow'
				] : false
			).
			h::link([
					'rel'		=> 'shortcut icon',
					'href'		=> file_exists(THEMES.'/'.$this->theme.'/'.$this->color_scheme.'/'.'img/favicon.ico') ?
									'themes/'.$this->theme.'/'.$this->color_scheme.'/img/favicon.ico' :
									file_exists(THEMES.'/'.$this->theme.'/img/favicon.ico') ?
									'themes/'.$this->theme.'/img/favicon.ico' :
									'includes/img/favicon.ico'
			]).
			h::base(is_object($Config) ? [
				'href' => $Config->server['base_url']
			] : false).
			$this->Head.
			implode('', $this->core_css).
			implode('', $this->css).
			implode('', $this->core_js).
			implode('', $this->js);
		/**
 		 * Getting footer information
		 */
		$this->Footer .= $this->get_footer();
		/**
 		 * Substitution of information into template
		 */
		$this->Html = str_replace(
			[
				'<!--html_lang-->',
				'<!--head-->',
				'<!--pre_Body-->',
				'<!--header-->',
				'<!--main-menu-->',
				'<!--main-submenu-->',
				'<!--menu-more-->',
				'<!--user_avatar_image-->',
				'<!--user_info-->',
				'<!--left_blocks-->',
				'<!--top_blocks-->',
				'<!--content-->',
				'<!--bottom_blocks-->',
				'<!--right_blocks-->',
				'<!--footer-->',
				'<!--post_Body-->'
			],
			[
				$L->clang,
				h::level($this->Head, $this->level['Head']),
				h::level($this->pre_Body, $this->level['pre_Body']),
				h::level($this->Header, $this->level['Header']),
				h::level($this->mainmenu, $this->level['mainmenu']),
				h::level($this->mainsubmenu, $this->level['mainsubmenu']),
				h::level($this->menumore, $this->level['menumore']),
				$this->user_avatar_image,
				h::level($this->user_info, $this->level['user_info']),
				h::level($this->Left, $this->level['Left']),
				h::level($this->Top, $this->level['Top']),
				h::level($this->Content, $this->level['Content']),
				h::level($this->Bottom, $this->level['Bottom']),
				h::level($this->Right, $this->level['Right']),
				h::level($this->Footer, $this->level['Footer']),
				h::level($this->post_Body, $this->level['post_Body'])
			],
			$this->Html
		);
	}
	/**
	 * Replacing anything in source code of filally genereted page
	 *
	 * @param string|string[] $search
	 * @param string|string[] $replace
	 */
	function replace ($search, $replace = '') {
		if (is_array($search)) {
			foreach ($search as $i => $val) {
				$this->Search[] = $val;
				$this->Replace[] = is_array($replace) ? $replace[$i] : $replace;
			}
		} else {
			$this->Search[] = $search;
			$this->Replace[] = $replace;
		}
	}
	/**
	 * Processing of replacing in content
	 *
	 * @param string	$data
	 *
	 * @return string
	 */
	protected function process_replacing ($data) {
		errors_off();
		foreach ($this->Search as $i => $search) {
			$data = preg_replace($search, $this->Replace[$i], $data) ?: str_replace($search, $this->Replace[$i], $data);
		}
		errors_on();
		return $data;
	}
	/**
	 * Including of JavaScript
	 * 
	 * @param string|string[]	$add	Path to including file, or code
	 * @param string			$mode	Can be <b>file</b> or <b>code</b>
	 */
	function js ($add, $mode = 'file') {
		$this->js_internal($add, $mode);
	}
	protected function js_internal ($add, $mode = 'file', $core = false) {
		if (is_array($add)) {
			foreach ($add as $script) {
				if ($script) {
					$this->js_internal($script, $mode, $core);
				}
			}
		} elseif ($add) {
			if ($core) {
				if ($mode == 'file') {
					$this->core_js[0] .= h::script([
						'src'	=> $add,
						'level'	=> false
					])."\n";
				} elseif ($mode == 'code') {
					$this->core_js[1] .= $add."\n";
				}
			} else {
				if ($mode == 'file') {
					$this->js[0] .= h::script([
						'src'	=> $add,
						'level'	=> false
					])."\n";
				} elseif ($mode == 'code') {
					$this->js[1] .= $add."\n";
				}
			}
		}
	}
	/**
	 * Including of CSS
	 *
	 * @param string|string[]	$add	Path to including file, or code
	 * @param string			$mode	Can be <b>file</b> or <b>code</b>
	 */
	function css ($add, $mode = 'file') {
		$this->css_internal($add, $mode);
	}
	protected function css_internal ($add, $mode = 'file', $core = false) {
		if (is_array($add)) {
			foreach ($add as $style) {
				if ($style) {
					$this->css_internal($style, $mode, $core);
				}
			}
		} elseif ($add) {
			if ($core) {
				if ($mode == 'file') {
					$this->core_css[0] .= h::link([
						'href'	=> $add,
						'rel'	=> 'stylesheet'
					]);
				} elseif ($mode == 'code') {
					$this->core_css[1] = $add."\n";
				}
			} else {
				if ($mode == 'file') {
					$this->css[0] .= h::link([
						'href'	=> $add,
						'rel'	=> 'stylesheet'
					]);
				} elseif ($mode == 'code') {
					$this->css[1] = $add."\n";
				}
			}
		}
	}
	/**
	 * Adding text to the title page
	 * 
	 * @param string	$add
	 */
	function title ($add) {
		$this->Title[] = htmlentities($add, ENT_COMPAT, 'utf-8');
	}
	/**
 	 * Getting of CSS and JavaScript includes
	 */
	protected function get_includes () {
		global $Config;
		if (!is_object($Config)) {
			return;
		}
		/**
 		 * If CSS and JavaScript compression enabled
		 */
		if ($Config->core['cache_compress_js_css']) {
			/**
 			 * Current cache checking
			 */
			if (
				!file_exists(PCACHE.'/'.$this->pcache_basename.'css') ||
				!file_exists(PCACHE.'/'.$this->pcache_basename.'js') ||
				!file_exists(PCACHE.'/pcache_key')
			) {
				$this->rebuild_cache();
			}
			$key = file_get_contents(PCACHE.'/pcache_key');
			/**
 			 * Including of CSS
			 */
			$css_list = get_list(PCACHE, '/^[^_](.*)\.css$/i', 'f', 'storage/pcache');
			$css_list = array_merge(
				['storage/pcache/'.$this->pcache_basename.'css'],
				$css_list
			);
			foreach ($css_list as &$file) {
				$file .= '?'.$key;
			}
			unset($file);
			$this->css_internal($css_list, 'file', true);
			/**
			 * Including of JavaScript
			 */
			$js_list = get_list(PCACHE, '/^[^_](.*)\.js$/i', 'f', 'storage/pcache');
			$js_list = array_merge(
				['storage/pcache/'.$this->pcache_basename.'js'],
				$js_list
			);
			foreach ($js_list as &$file) {
				$file .= '?'.$key;
			}
			unset($file);
			$this->js_internal($js_list, 'file', true);
		} else {
			$this->get_includes_list();
			/**
			 * Including of CSS
			 */
			foreach ($this->includes['css'] as $file) {
				$this->css_internal($file, 'file', true);
			}
			/**
			 * Including of JavaScript
			 */
			foreach ($this->includes['js'] as $file) {
				$this->js_internal($file, 'file', true);
			}
		}
	}
	/**
	 * Getting of JavaScript and CSS files list to be included
	 */
	protected function get_includes_list ($for_cache = false) {
		$theme_folder	= THEMES.'/'.$this->theme;
		$scheme_folder	= $theme_folder.'/schemes/'.$this->color_scheme;
		$theme_pfolder	= 'themes/'.$this->theme;
		$scheme_pfolder	= $theme_pfolder.'/schemes/'.$this->color_scheme;
		$this->includes = array(
			'css' => array_merge(
				(array)get_list(CSS,					'/(.*)\.css$/i',	'f', $for_cache ? true : 'includes/css',			true, false, '!include'),
				(array)get_list($theme_folder.'/css',	'/(.*)\.css$/i',	'f', $for_cache ? true : $theme_pfolder.'/css',		true, false, '!include'),
				(array)get_list($scheme_folder.'/css',	'/(.*)\.css$/i',	'f', $for_cache ? true : $scheme_pfolder.'/css',	true, false, '!include')
			),
			'js' => array_merge(
				(array)get_list(JS,						'/(.*)\.js$/i',		'f', $for_cache ? true : 'includes/js',				true, false, '!include'),
				(array)get_list($theme_folder.'/js',	'/(.*)\.js$/i',		'f', $for_cache ? true : $theme_pfolder.'/js',		true, false, '!include'),
				(array)get_list($scheme_folder.'/js',	'/(.*)\.js$/i',		'f', $for_cache ? true : $scheme_pfolder.'/js',		true, false, '!include')
			)
		);
		unset($theme_folder, $scheme_folder, $theme_pfolder, $scheme_pfolder);
		sort($this->includes['css']);
		sort($this->includes['js']);
	}
	/**
	 * Rebuilding of JavaScript and CSS cache
	 */
	function rebuild_cache () {
		$this->get_includes_list(true);
		$key = '';
		foreach ($this->includes as $extension => &$files) {
			$temp_cache = '';
			foreach ($files as $file) {
				if (file_exists($file)) {
					$current_cache = file_get_contents($file);
					if ($extension == 'css') {
						/**
						 * Insert external elements into resulting css file.
						 * It is needed, because those files will not be copied into new destination of resulting css file.
						 */
						$this->css_includes_processing($current_cache, $file);
					}
					$temp_cache .= $current_cache;
					unset($current_cache);
				}
			}
			file_put_contents(PCACHE.'/'.$this->pcache_basename.$extension, gzencode($temp_cache, 9), LOCK_EX|FILE_BINARY);
			$key .= md5($temp_cache);
		}
		file_put_contents(PCACHE.'/pcache_key', mb_substr(md5($key), 0, 5), LOCK_EX|FILE_BINARY);
	}
	/**
	 * Analyses file for images, fonts and css links and include they content into single resulting css file.<br>
	 * Supports next file extensions for possible includes:<br>
	 * jpeg, jpe, jpg, gif, png, ttf, ttc, svg, svgz, woff, eot, css
	 *
	 * @param $data	//Content of processed file
	 * @param $file	//Path to file, that includes specified in previous parameter content
	 */
	function css_includes_processing (&$data, $file) {
		$cwd	= getcwd();
		chdir(dirname($file));
		/**
		 * Simple minification, removes comments, newlines, tabs and unnecessary spaces
		 */
		$data	= preg_replace('#(/\*.*?\*/)|\t|\n|\r#s', '', $data);
		$data	= preg_replace('#\s*([,:;+>{}])\s*#s', '$1', $data);
		/**
		 * Includes processing
		 */
		preg_replace_callback(
			'/(url\((.*?)\))|(@import[\s\t\n\r]{0,1}[\'"](.*?)[\'"])/',
			function ($link) use (&$data) {
				$link		= trim(array_pop($link), '\'" ');
				$format		= substr($link, strrpos($link, '.') + 1);
				$mime_type	= 'text/html';
				switch ($format) {
					case 'jpeg':
					case 'jpe':
					case 'jpg':
						$mime_type = 'image/jpg';
					break;
					case 'gif':
						$mime_type = 'image/gif';
					break;
					case 'png':
						$mime_type = 'image/png';
					break;
					case 'ttf':
					case 'ttc':
						$mime_type = 'application/x-font-ttf';
					break;
					case 'svg':
					case 'svgz':
						$mime_type = 'image/svg+xml';
					break;
					case 'woff':
						$mime_type = 'application/x-font-woff';
					break;
					case 'eot':
						$mime_type = 'application/vnd.ms-fontobject';
					break;
					case 'css':
						$mime_type = 'text/css';
					break;
				}
				$content	= file_get_contents(realpath($link));
				/**
				 * For recursing includes processing, if CSS file includes others CSS files
				 */
				if ($format == 'css') {
					$this->css_includes_processing($content, realpath($link));
				}
				$data = str_replace($link, 'data:'.$mime_type.';charset=utf-8;base64,'.base64_encode($content), $data);
			},
			$data
		);
		chdir($cwd);
	}
	/**
	 * Getting footer information
	 *
	 * @return string
	 */
	protected function get_footer () {
		global $copyright, $L, $db;
		if (!($copyright && is_array($copyright))) {
			$this->Content	= '';
			interface_off();
			__finish();
		}
		return h::div(
			$L->page_footer_info('<!--generate time-->', $db->queries, format_time(round($db->time, 5)), '<!--peak memory usage-->')
		).
		h::div(
			$copyright[1].h::br().$copyright[2],
			[
				'id'	=> 'copyright'
			]
		);
	}
	/**
 	 * Getting of debug information
	 */
	protected function get_debug_info () {
		global $Config, $L, $db;
		$debug_tabs			= '';
		$debug_tabs_content	= '';
		/**
 		 * Objects
		 */
		if ($Config->core['show_objects_data']) {
			global $Core, $timeload, $loader_init_memory;
			$debug_tabs[]			= [
				$L->objects,
				[
					'href'	=> '#debug_objects_tab'
				]
			];
			$tmp				= '';
			$last				= $timeload['loader_init'];
			foreach ($Core->Loaded as $object => $data) {
				$tmp .=	h::p(
					$object
				).
				h::{'p.cs-padding-left'}(
					$L->creation_duration.': '.format_time(round($data[0] - $last, 5)),
					$L->time_from_start_execution.': '.format_time(round($data[0] - $timeload['start'], 5)),
					$L->memory_usage.': '.format_filesize($data[1], 5)
				);
				$last = $data[0];
			}
			unset($object, $data, $last);
			$debug_tabs_content	.= h::{'div#debug_objects_tab'}(
				h::p(
					$L->total_list_of_objects.': '.implode(', ', array_keys($Core->Loaded)),
					$L->loader
				).
				h::{'p.cs-padding-left'}(
					$L->creation_duration.': '.format_time(round($timeload['loader_init'] - $timeload['start'], 5)),
					$L->memory_usage.': '.format_filesize($loader_init_memory, 5)
				).
				$tmp
			);
			unset($tmp);
		}
		/**
 		 * DB queries
		 */
		if ($Config->core['show_db_queries']) {
			$debug_tabs[]		= [
				$L->db_queries,
				[
					'href'	=> '#debug_db_queries_tab'
				]
			];
			$tmp				= '';
			foreach ($db->get_connections_list() as $name => $database) {
				$tmp	.= h::{'p.cs-padding-left'}(
					$L->debug_db_info(
						$name != 0 ? $L->db.' '.$database->database : $L->core_db.' ('.$database->database.')',
						format_time(round($database->connecting_time, 5)),
						$database->queries['num'],
						format_time(round($database->time, 5))
					)
				);
				foreach ($database->queries['text'] as $i => $text) {
					$tmp	.= h::code(
						$text.
						h::br(2).
						'#'.h::i(format_time(round($database->queries['time'][$i], 5))).
						($error = (stripos($text, 'select') === 0 && !$database->queries['result'][$i]) ? '('.$L->error.')' : ''),
						[
							'class' => ($database->queries['time'][$i] > 0.1 ? 'ui-state-highlight ' : '').($error ? 'ui-state-error ' : '').'cs-debug-code'
						]
					);
				}
			}
			unset($error, $name, $database, $i, $text);
			$debug_tabs_content	.= h::{'div#debug_db_queries_tab'}(
				h::p(
					$L->debug_db_total($db->queries, format_time(round($db->time, 5))),
					$L->false_connections.': '.h::b(implode(', ', $db->get_connections_list(false)) ?: $L->no),
					$L->successful_connections.': '.h::b(implode(', ', $db->get_connections_list(true)) ?: $L->no),
					$L->mirrors_connections.': '.h::b(implode(', ', $db->get_connections_list('mirror')) ?: $L->no),
					$L->active_connections.': '.(count($db->get_connections_list()) ? '' : h::b($L->no))
				).
				$tmp
			);
			unset($tmp);
		}
		//TODO Storages information
		/**
 		 * Cookies
		 */
		if ($Config->core['show_cookies']) {
			$debug_tabs[]		= [
				$L->cookies,
				[
					'href'	=> '#debug_cookies_tab'
				]
			];
			$tmp				= [h::td($L->key.':', ['style' => 'width: 20%;']).h::td($L->value)];
			foreach ($_COOKIE as $i => $v) {
				$tmp[]	= h::td($i.':', ['style' => 'width: 20%;']).h::td(xap($v));
			}
			unset($i, $v);
			$debug_tabs_content	.= h::{'div#debug_cookies_tab'}(
				h::{'table.cs-padding-left'}(
					h::tr($tmp),
					[
						'style' => 'width: 100%'
					]
				)
			);
			unset($tmp);
		}
		$this->debug_info = $this->process_replacing(
			h::{'div#debug_window_tabs'}(
				h::{'ul li| a'}($debug_tabs).
				$debug_tabs_content
			)
		);
	}
	/**
	 * Display notice
	 *
	 * @param string $text
	 */
	function notice ($text) {
		$this->Top .= h::{'div.ui-state-highlight.ui-corner-all.ui-priority-primary.cs-center.cs-state-messages'}(
			$text
		);
	}
	/**
	 * Display warning
	 *
	 * @param string $text
	 */
	function warning ($text) {
		$this->Top .= h::{'div.ui-state-error.ui-corner-all.ui-priority-primary.cs-center.cs-state-messages'}(
			$text
		);
	}
	/**
	 * Error pages processing
	 */
	function error_page () {
		interface_off();
		ob_start();
		if (
			!_include_once(THEMES.'/'.$this->theme.'/error.html', false) &&
			!_include_once(THEMES.'/'.$this->theme.'/error.php', false)
		) {
			echo "<!doctype html>\n".(error_header(ERROR_PAGE) ?: ERROR_PAGE);
		}
		$this->Content = ob_get_clean();
		__finish();
	}
	/**
	 * Substitutes header information about user, login/registration forms, etc.
	 */
	protected function get_header_info () {
		global $User, $L, $Core;
		if (is_object ($User) && $User->is('user')) {
			if ($User->avatar) {
				$this->user_avatar_image = 'url('.h::url($User->avatar, true).')';
			} else {
				$this->user_avatar_image = 'url(/includes/img/guest.gif)';
			}
			$this->user_info = h::b($L->hello.', '.$User->get_username().'!').
			h::{'icon#logout_process'}(
				'power',
				[
					'style'			=> 'cursor: pointer;',
					'data-title'	=> $L->logout
				]
			).
			h::{'p.actions'}(
				h::a(
					$L->profile,
					[
						'href'	=> '/profile/'.$User->login
					]
				).
				'|'.
				h::a(
					$L->settings,
					[
						'href'	=> '/profile/settings'
					]
				)
			);
			$Core->run_trigger(
				'System/Page/get_header_info',
				[
					'id'	=> $User->get('id')
				]
			);
		} else {
			$this->user_avatar_image = 'url(/includes/img/guest.gif)';
			$this->user_info = h::{'div#anonym_header_form'}(
				h::b($L->hello.', '.$L->guest.'!').
				h::br().
				h::{'button#login_slide.cs-button-compact'}(
					h::icon('check').$L->log_in
				).
				h::{'button#registration_slide.cs-button-compact'}(
					h::icon('pencil').$L->register,
					[
						 'data-title'	=> $L->quick_registration_form
					]
				)
			).
			h::{'div#register_header_form'}(
				h::{'input#register[tabindex=1]'}(
					[
						 'placeholder'	=> $L->email_or
					]
				).
				h::{'select#register_list'}(
					[
						 'in'			=> array_merge([''], (array)_mb_substr(get_list(MODULES.'/System/registration', '/^.*?\.php$/i', 'f'), 0, -4))
					]
				).
				h::{'button#register_process.cs-button-compact[tabindex=2]'}(h::icon('pencil').$L->register).
				h::{'button.cs-button-compact.cs-header-back[tabindex=3]'}(
					h::icon('carat-1-s'),
					[
						 'data-title'	=> $L->back
					]
				).
				h::{'button.cs-button-compact.cs-header-restore-password[tabindex=4]'}(
					h::icon('help'),
					[
						 'data-title'	=> $L->restore_password
					]
				),
				[
					 'style'	=> 'display: none;'
				]
			).
			h::{'div#login_header_form'}(
				h::{'input#user_login[tabindex=1]'}([
					'placeholder'	=> $L->login_or_email_or
				]).
				h::{'select#login_list'}([
					'in'			=> array_merge([''], (array)_mb_substr(get_list(MODULES.'/System/registration', '/^.*?\.php$/i', 'f'), 0, -4))
				]).
				h::{'input#user_password[type=password][tabindex=2]'}([
					'placeholder'	=> $L->password
				]).
				h::{'icon#show_password.pointer'}('locked').
				h::{'button#login_process.cs-button-compact[tabindex=3]'}(h::icon('check').$L->log_in).
				h::{'button.cs-button-compact.cs-header-back[tabindex=5]'}(
					h::icon('carat-1-s'),
					[
						'data-title'	=> $L->back
					]
				).
				h::{'button.cs-button-compact.cs-header-restore-password[tabindex=4]'}(
					h::icon('help'),
					[
						'data-title'	=> $L->restore_password
					]
				),
				[
					'style'	=> 'display: none;'
				]
			);
		}
	}
	/**
	 * Cloning restriction
	 *
	 * @final
	 */
	function __clone () {}
	/**
 	 * Page generation
	 */
	function __finish () {
		global $Config;
		/**
 		 * Cleaning of output
		 */
		if (OUT_CLEAN) {
			ob_end_clean();
		}
		/**
 		 * For AJAX and API requests only content without page template
		 */
		if (!$this->interface) {
			/**
 			 * Processing of replacing in content
			 */
			echo $this->process_replacing($this->Content);
		} else {
			global $Error, $L, $timeload, $User, $Core;
			$Core->run_trigger('System/Page/pre_display');
			$Error->display();
			/**
 			 * Processing of template, cubstituting of content, preparing for the output
			 */
			$this->prepare();
			/**
			 * Processing of replacing in content
			 */
			$this->Html = $this->process_replacing($this->Html);
			/**
 			 * Detection of compression
			 */
			$ob = false;
			if (is_object($Config) && !zlib_compression() && $Config->core['gzip_compression'] && (is_object($Error) && !$Error->num())) {
				ob_start('ob_gzhandler');
				$ob = true;
			}
			$timeload['end'] = microtime(true);
			/**
 			 * Getting of debug information
			 */
			if (
				is_object($User) && (
					$User->is('admin') || (
						$Config->can_be_admin && $Config->core['ip_admin_list_only']
					)
				) && defined('DEBUG') && DEBUG
			) {
				$this->get_debug_info();
			}
			echo str_replace(
				[
					'<!--debug_info-->',
					'<!--generate time-->',
					'<!--peak memory usage-->'
				],
				[
					$this->debug_info ? h::level(
						h::{'div#debug'}(
							h::level($this->debug_info),
							[
								'data-dialog'	=> '{"autoOpen": false, "height": "400", "hide": "puff", "show": "scale", "width": "700"}',
								'title'			=> $L->debug,
								'style'			=> 'display: none;'
							]
						),
						$this->level['debug_info']
					) : '',
					format_time(round($timeload['end'] - $timeload['start'], 5)),
					format_filesize(memory_get_peak_usage(), 5)
				],
				$this->Html
			);
			if ($ob) {
				ob_end_flush();
			}
		}
	}
}