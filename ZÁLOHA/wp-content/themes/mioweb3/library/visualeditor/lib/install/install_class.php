<?php

use Mioweb\Lib\License;
use Mioweb\Tus\Tus;

function MwWebInstall()
{
	return mwWebInstall::instance();
}

class mwWebInstall
{

	/** @var mwWebInstall Single instance holder. */
	protected static $_instance = null;

	public $webs = [];

	public $tags = [];

	public $menus = [];

	public $contents = [];

	public $images = [];

	public $edit_mode = false;

	public $tutorial_mode = false;

	private $builder_mode = false;

	function __construct()
	{
		add_action('init', [$this, 'init']);

		$this->edit_mode = current_user_can('edit_pages') ? true : false;
		$this->builder_mode = $this->edit_mode && !isset($_GET['mw_preview']) ? true : false;

		if ($this->builder_mode) {
			add_action('wp_ajax_tus_file_uploaded', [$this, 'import_web_zip']);

			add_action('wp_ajax_mw_install_web_template', [$this, 'install_web_ajax']);
			add_action('wp_ajax_mw_install_tut_web_template', [$this, 'install_tut_web_ajax']); // for gameboarding

			// install web action
			if (isset($_POST['web_to_install'])) {
				add_action('init', [$this, 'install_web']);
			}
			if (isset($_GET['web_to_install'])) {
				add_action('init', [$this, 'install_web_get']);
			}
			if (isset($_GET['export_mioweb_template'])) {
				add_action('init', [$this, 'export_theme_zip']);
				//$this->export_theme_zip();
			}
			if (isset($_POST['export_web_from_mw'])) {
				add_action('init', [$this, 'export_web_zip']);
				//$this->export_web_zip();
			}
		}
	}

	function init()
	{
		if (!MW()->installedWeb() && $this->builder_mode) {
			$to_install = get_option('mw_web_to_install');
			if ($to_install && isset($this->webs[$to_install])) {
				$this->install_web($to_install);
				wp_redirect(get_home_url());
				delete_option('mw_web_to_install');
				die();
			}
		}
	}

	function webInstaller()
	{
		if (!MW()->getLicense()->isValid()) {
			$this->install_steps('licence');
		} elseif (!MW()->installedWeb()) {
			/*
			global $vePage;

			if ($vePage->tutorials->setTutorial('game')) {
				$this->tutorial_mode = true;
			}*/

			$this->install_steps('web');
		}
	}

	function export_theme_zip()
	{
		$post_id = $_GET['export_mioweb_template'];

		// create zip
		$zipname = WP_CONTENT_DIR . '/' . uniqid('zip', true) . '.zip';
		$zip = new ZipArchive();
		$zip->open($zipname, ZipArchive::CREATE);

		// get page setting
		$page = $this->getPage($post_id, $zip);

		$zip->addFromString('config.php', $page);
		$zip->close();

		//filename
		$post = get_post($post_id);
		$slug = $post->post_name;

		$this->readZip($zipname, $slug . '.zip');

		die();
	}

	function export_web_zip()
	{
		global $vePage;

		$pages = [];
		if (isset($_POST['export']['all_pages'])) {
			$pages = get_pages();
		} elseif (isset($_POST['export']['pages'])) {
			foreach ($_POST['export']['pages'] as $p) {
				$pages[] = get_post($p);
			}
		}

		// create zip
		$zipname = WP_CONTENT_DIR . '/' . uniqid('zip', true) . '.zip';
		$zip = new ZipArchive();
		$zip->open($zipname, ZipArchive::CREATE);

		$install = [];
		$setting = [];
		$image_list = [];

		$install['pages'] = [];
		$install['menus'] = [];
		$install['contents'] = [];
		$install['version'] = '3';

		// visual setting

		if (isset($_POST['export']['web_look'])) {
			$setting['ve_header'] = $this->get_layer_vars(get_option('ve_header'), $zip, $image_list);
			$setting['ve_footer'] = $this->get_layer_vars(get_option('ve_footer'), $zip, $image_list);
			$setting['ve_appearance'] = $this->get_layer_vars(get_option('ve_appearance'), $zip, $image_list);
			$setting['ve_buttons'] = $this->get_layer_vars(get_option('ve_buttons'), $zip, $image_list);
			$setting['image_list'] = $image_list;
		}
		if (isset($_POST['export']['export_blog']['setting'])) {
			$setting['blog_header'] = $this->get_layer_vars(get_option('blog_header'), $zip, $image_list);
			$setting['blog_footer'] = $this->get_layer_vars(get_option('blog_footer'), $zip, $image_list);
			$setting['blog_appearance'] = $this->get_layer_vars(get_option('blog_appearance'), $zip, $image_list);
			$setting['blog_comments'] = $this->get_layer_vars(get_option('blog_comments'), $zip, $image_list);
			$setting['image_list'] = $image_list;
		}
		$zip->addFromString('mw_web_setting.php', visualEditor::code($setting));

		$home_page = get_option('page_on_front');
		$blog_page = get_option('page_for_posts');

		// blog content
		$pagetozip = $this->getLayerContent($zip);
		if ($pagetozip) {
			$zip->addFromString('mw_blog_content.php', $pagetozip);
		}

		if (!count($pages) && !count($setting)) {
			echo __('Export nelze vytvořit je prázdný.', 'cms');
			die();
		}

		//pages
		foreach ($pages as $page) {
			$page_name = $page->post_name . '_' . $page->post_parent;

			$pagetozip = $this->getPage($page->ID, $zip);
			$zip->addFromString($page_name . '.php', $pagetozip);
			$install['pages'][$page_name] = [
				'id' => $page->ID,
				'post' => [
					'post_title' => $page->post_title,
					'post_name' => $page->post_name,
					'post_status' => $page->post_status,
					'comment_status' => $page->comment_status,
					'post_type' => 'page',
					'post_content' => '',
					'post_excerpt' => $page->post_excerpt,
					'post_parent' => $page->post_parent,
					'menu_order' => $page->menu_order,
				],
			];
			if ($page->ID == $home_page) {
				$install['pages'][$page_name]['page'] = 'home';
			}
			if ($page->ID == $blog_page) {
				$install['pages'][$page_name]['page'] = 'blog';
			}
		}

		//menus
		foreach ($this->menus as $menu_id) {
			if (is_nav_menu($menu_id)) {
				$menu = wp_get_nav_menu_object($menu_id);
				$menu_items = wp_get_nav_menu_items($menu_id);
				$install['menus'][$menu_id] = [
					'name' => $menu->name,
					'items' => [],
				];
				foreach ((array) $menu_items as $menu_item) {
					$page = get_post($menu_item->object_id);
					$install['menus'][$menu_id]['items'][$menu_item->ID] = [
						'type' => $menu_item->type == 'custom' ? 'link' : 'page',
						'page' => $menu_item->type == 'custom' ? '' : $page->post_name . '_' . $page->post_parent,
						'link' => $menu_item->type == 'custom' ? $menu_item->url : '',
						'parent' => $menu_item->menu_item_parent,
						'title' => $menu_item->post_title,
						'target' => $menu_item->target,
						'order' => $menu_item->menu_order,
					];
				}
			}
		}
		//contents
		foreach ($this->contents as $content_id => $type) {
			$page = get_post($content_id);
			if ($page) {
				$pagetozip = $this->getPage($content_id, $zip, $type);
				$zip->addFromString($page->post_name . '.php', $pagetozip);
				$install['contents'][$page->post_name] = [
					'id' => $page->ID,
					'post' => [
						'post_title' => $page->post_title,
						'post_name' => $page->post_name,
						'post_status' => $page->post_status,
						'post_type' => $page->post_type,
						'post_content' => '',
					],
				];
			}
		}

		$zip->addFromString('mw_web_install.php', visualEditor::code($install));

		$zip->close();
		$pages = null; // Free some memory before readfile()

		$this->readZip($zipname);

		die();
	}

	private function readZip(string $zipPath, string $zipName = 'mioweb_export.zip')
	{
		header('Content-Type: application/zip');
		header('Content-disposition: attachment; filename=' . $zipName);
		header('Content-Length: ' . filesize($zipPath));

		if (ob_get_level()) {
			ob_end_clean();
		}
//		set_time_limit(0);

//		In case of `readfile()` and memory problems in future, switch to stream download below
//		$inputStream = fopen($zipname, 'rb');
//		$outputStream = fopen('php://output', 'wb');
//		stream_copy_to_stream($inputStream, $outputStream);
//		fclose($outputStream);
//		fclose($inputStream);

//		Or look at http://teddy.fr/2007/11/28/how-serve-big-files-through-php/

		readfile($zipPath);
		unlink($zipPath);
	}

	function getPage($post_id, &$zip, $type = 'page', $code = true)
	{
		global $wpdb, $vePage;

		$result = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . "ve_posts_layer WHERE vpl_type='" . $type . "' AND vpl_post_id=" . $post_id);

		$layer = visualEditor::decode($result->vpl_layer);
		$layer = mwBackCompatibility::layer_set($layer);

		$ve_header = get_post_meta($post_id, 've_header', true);
		$ve_footer = get_post_meta($post_id, 've_footer', true);
		$ve_appearance = get_post_meta($post_id, 've_appearance', true);

		// page images
		$image_list = [];
		$layer = $this->get_layer_vars($layer, $zip, $image_list);
		$ve_header = $this->get_layer_vars($ve_header, $zip, $image_list);
		$ve_footer = $this->get_layer_vars($ve_footer, $zip, $image_list);
		$ve_appearance = $this->get_layer_vars($ve_appearance, $zip, $image_list);

		$config = [];
		$config['version'] = '3';
		$config['page_template'] = get_post_meta($post_id, 've_page_template', true);
		$config['image_list'] = $image_list;
		$config['layer'] = $layer;
		$config['setting'] = [
			've_header' => $ve_header,
			've_footer' => $ve_footer,
			've_appearance' => $ve_appearance,
		];

		return $code ? visualEditor::code($config) : $config;
	}

	function getLayerContent(&$zip, $type = 'blog', $post_id = 0)
	{
		global $wpdb, $vePage;

		$result = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . "ve_posts_layer WHERE vpl_type='" . $type . "' AND vpl_post_id=" . $post_id);
		if ($wpdb->num_rows) {
			$layer = visualEditor::decode($result->vpl_layer);
			$layer = mwBackCompatibility::layer_set($layer);

			// layer images
			$image_list = [];
			$layer = $this->get_layer_vars($layer, $zip, $image_list);

			$config = [];
			$config['image_list'] = $image_list;
			$config['layer'] = $layer;

			return visualEditor::code($config);
		}

		return '';
	}

	// find all images in layer for export
	function get_layer_vars($setting, &$zip, &$image_list)
	{
		//print_r($setting);
		//echo '<br><br>';
		if (is_array($setting)) {
			foreach ($setting as $key => $val) {
 /*
				if($key==='image_gallery_items') {
				foreach($val as $img_key=>$img_id) {
				$g_image=wp_get_attachment_image_src( $img_id, 'full' );
				$path_parts = pathinfo($g_image[0]);
				$image_name = $path_parts['basename'];
				$fullsize_path = get_attached_file( $img_id );
				if(file_exists($fullsize_path)) {
				$image_list[$image_name]=$image_name;
				$image_list[$image_name.'_ID']=$img_id;
				$zip->addFile($fullsize_path,$image_name);
				$setting[$key][$img_key]='%%replace_image_'.$image_name.'%%';
				} else unset($setting[$key][$img_key]);
				}
				} else */
				if (is_array($val)) {
					$setting[$key] = $this->get_layer_vars($val, $zip, $image_list);

					// delete gallery images
					if (isset($setting[$key]['image_gallery_items'])) {
						if (count($setting[$key]['image_gallery_items'])) {
							foreach ($setting[$key]['image_gallery_items'] as $img_key => $img_id) {
								if ($img_id) {
									$img_val = wp_get_attachment_image_src($img_id, 'full');
									$image_src = str_replace(get_home_url() . '/', '', $img_val[0]);

									$path_parts = pathinfo($image_src);
									$image_name = $path_parts['basename'];

									if (file_exists(ABSPATH . '/' . $image_src) && $image_src) {
										$image_list[$image_name] = $image_name;
										$zip->addFile(ABSPATH . '/' . $image_src, $image_name);
										$setting[$key]['image_gallery_items'][$img_key] = '%%replace_image_' . $image_name . '%%';
									} else {
										unset($setting[$key]['image_gallery_items'][$img_key]);
									}
								}
							}
						}
						//print_r($setting[$key]['image_gallery_items']);
						//$setting[$key]['image_gallery_items']=array();
					}

					// delete id of images
					if (isset($setting[$key]['imageid'])) {
						$setting[$key]['imageid'] = '';
					}

					// delete se form id
					if (isset($setting[$key]['type']) && $setting[$key]['type'] == 'seform') {
						$setting[$key]['content'] = '';
					}
					// variable content
					if (isset($setting[$key]['type']) && $setting[$key]['type'] == 'variable_content') {
						$this->contents[$setting[$key]['style']['content']] = 've_elvar';
					}
				} elseif (($key === 'image' || $key === 'large_image' || $key === 'custom_image' || $key === 'logo') && $val && substr($val, 0, 4) != 'http') {
					$path_parts = pathinfo($val);
					$image_name = $path_parts['basename'];
					if (file_exists(ABSPATH . '/' . $val) && $val) {
						$image_list[$image_name] = $image_name;
						$zip->addFile(ABSPATH . '/' . $val, $image_name);
						$setting[$key] = '%%replace_image_' . $image_name . '%%';
					} else {
						$setting[$key] = '';
					}
				} elseif ($key == 'menu') {
					if ($val) {
						$this->menus[$val] = $val;
					}
				} elseif ($key == 'before_header') {
					if ($val) {
						$this->contents[$val] = 've_header';
					}
				} elseif ($key == 'custom_footer') {
					if ($val) {
						$this->contents[$val] = 'cms_footer';
					}
				} elseif ($key == 'slider_content') {
					if ($val) {
						$this->contents[$val] = 'mw_slider';
					}
				}
			}
		}

		return $setting;
	}

	// find all images in layer for import
	function insert_layer_vars($setting, $image_list, $path)
	{
		if (is_array($setting)) {
			foreach ($setting as $key => $val) {
 /*
				if($key==='image_gallery_items') {
				foreach($val as $img_key=>$img) {
				if(strpos($img,'%%replace_image_')!==false) {
				$name=str_replace('%%replace_image_','',$img);
				$name=str_replace('%%','',$name);
				if(isset($image_list[$name.'_ID'])) {
				if(file_exists($path.'/'.$image_list[$name])) $setting[$key][$img_key]=$this->images[$name];
				else unset($setting[$key][$img_key]);
				}
				}
				}
				} else */
				if (is_array($val)) {
					$setting[$key] = $this->insert_layer_vars($val, $image_list, $path);

					if (isset($setting[$key]['type']) && $setting[$key]['type'] == 'variable_content') {
						if (isset($val['content']) && $val['content'] && isset($setting[$key])) {
							$setting[$key]['content'] = $this->contents[$val['content']];
						}
					}

					if (isset($setting[$key]['image_gallery_items'])) {
						if (count($setting[$key]['image_gallery_items'])) {
							foreach ($setting[$key]['image_gallery_items'] as $image_key => $img_val) {
								if (strpos($img_val, '%%replace_image_') !== false) {
									$name = str_replace('%%replace_image_', '', $img_val);
									$name = str_replace('%%', '', $name);
									if (isset($image_list[$name])) {
										if (file_exists($path . '/' . $image_list[$name])) {
											$setting[$key]['image_gallery_items'][$image_key] = get_home_url() . '/' . str_replace(ABSPATH, '', $path) . '/' . $image_list[$name];
										} else {
											unset($setting[$key]['image_gallery_items'][$image_key]);
										}
									}
								}
							}
						}

						//$setting[$key]['image_gallery_items']=array();
					}
				} elseif (($key === 'image' || $key === 'large_image' || $key === 'custom_image' || $key === 'logo') && $val && substr($val, 0, 4) != 'http') {
					if (strpos($val, '%%replace_image_') !== false) {
						$name = str_replace('%%replace_image_', '', $val);
						$name = str_replace('%%', '', $name);
						if (isset($image_list[$name])) {
							$setting[$key] = file_exists($path . '/' . $image_list[$name]) ? '/' . str_replace(ABSPATH, '', $path) . '/' . $image_list[$name] : '';
						}
					}
				} elseif ($key === 'menu') {
					if ($val && isset($setting[$key])) {
						$setting[$key] = $this->menus[$val];
					}
				} elseif ($key === 'before_header') {
					if ($val && isset($setting[$key])) {
						$setting[$key] = $this->contents[$val];
					}
				} elseif ($key === 'custom_footer') {
					if ($val && isset($setting[$key])) {
						$setting[$key] = $this->contents[$val];
					}
				} elseif ($key === 'slider_content') {
					if ($val && isset($setting[$key])) {
						$setting[$key] = $this->contents[$val];
					}
				}
			}
		}

		return $setting;
	}

	// find all old page ids in setting and replace it
	function replace_pages_id($setting, $installed_pages)
	{
		if (is_array($setting)) {
			foreach ($setting as $key => $val) {
				if (is_array($val)) {
					$setting[$key] = $this->replace_pages_id($val, $installed_pages);
				} elseif ($key == 'page') {
					if (isset($installed_pages[$val])) {
						$setting[$key] = $installed_pages[$val];
					}
				}
			}
		}

		return $setting;
	}

	function importItemZip($objectId, ?string $originalItemId = null)
	{
		$zip = new ZipArchive();
		$files = json_decode(stripslashes($_POST['import_template_upload']), true);
		if (is_array($files)) {
			$file = array_shift($files);
			$filePath = tus()->getUploadsDir() . '/' . $file['name'];
			$res = $zip->open($filePath);
			if ($res === true) {
				if (!function_exists('wp_generate_attachment_metadata')) {
					require_once(ABSPATH . 'wp-admin' . '/includes/image.php');
					require_once(ABSPATH . 'wp-admin' . '/includes/file.php');
					require_once(ABSPATH . 'wp-admin' . '/includes/media.php');
				}

				WP_Filesystem();
				$folder = wp_upload_dir();

				$zip_config = zip_open($filePath);

				$images = [];
				// extract config
				if ($zip_config) {
					while ($zip_entry = zip_read($zip_config)) {
						$filename = zip_entry_name($zip_entry);
						if ($filename == 'config.php') {
							$config_code = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
							zip_entry_close($zip_entry);
						} else {
							// save list of images in zip
							$filetype = wp_check_filetype($filename, null);
							if (preg_match('#^image/#', $filetype['type']) && !file_exists($folder['path'] . '/' . $filename)) {
								$images[] = $filename;
							}
						}
					}
				}
				zip_close($zip_config);

				if (isset($config_code)) {
					//extract and save images from zip
					$zip->extractTo($folder['path'], $images);
					$zip->close();

					foreach ($images as $filename) {
						if (file_exists($folder['path'] . '/' . $filename)) {
							$filetype = wp_check_filetype($filename, null);
							$attachment = [
									'post_mime_type' => $filetype['type'],
									'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
									'post_content' => '',
									'post_status' => 'inherit',
									'guid' => $folder['url'] . '/' . $filename,
							];

							$attachment_id = wp_insert_attachment($attachment, $folder['path'] . '/' . $filename, 0, true);

							if (!is_wp_error($attachment_id)) {
								$attachment_data = $this->mw_generate_attachment_metadata($attachment_id, $folder['path'] . '/' . $filename);
								wp_update_attachment_metadata($attachment_id, $attachment_data);
							}
						}
					}
					if ($originalItemId !== null) {
						$object = mwSetting()->getObject($objectId);
						$originalItem = $object->service()->getItem($originalItemId);
						$postParent = wp_get_post_parent_id($originalItem);

						$new_post = [
							'post_id' => $originalItemId,
							'post_title' => $originalItem->getName(),
							'post_name' => $originalItem->getName(),
							'post_status' => 'publish',
							'comment_status' => 'open',
							'post_type' => 'page',
							'post_content' => '',
							'post_excerpt' => '',
							'post_parent' => $postParent,
							'menu_order' => $originalItem->getOrder(),
						];

						$postId = $this->importItem($new_post, $config_code, $folder, 'page', true);
						wp_redirect(get_permalink($postId));
					} else {
						$tosave = $_POST;

						return $this->importItem($tosave, $config_code, $folder, $objectId);
					}
				} else {
					mwMessages()->error(__('Stránku nelze importovat. Soubor neobsahuje MioWeb šablonu.', 'cms'));
				}
			} else {
				mwMessages()->error(__('Stránku nelze importovat. Soubor není ve formátu ZIP.', 'cms'));
			}

			tus()->deleteFile($file['name']); // Delete zip
		} else {
			mwMessages()->error(__('Stránku se nepodařilo importovat. Pravděpodobně jste nenahrál žádný soubor.', 'cms'));
		}

		return null;
	}

	function importItem($tosave, $page_code, $folder, $objectId = 'page', $addContent = false)
	{
		$config = visualEditor::decode($page_code);

		// replace images, contents in layer
		$config['layer'] = $this->insert_layer_vars($config['layer'], $config['image_list'], $folder['path']);

		// from mw20
		if ($this->isOldVersion($config)) {
			$config['layer'] = mwBackCompatibility::layer_set($config['layer'], true);
		}

		$layer = visualEditor::code($config['layer']);
		$tosave['post_content'] = $layer;
		$tosave['template'] = $config['page_template']['directory'];

		if (!$addContent) {
			$object = mwSetting()->getObject($objectId);
			$itemId = $object->service()->add($tosave);
		} else {
			$itemId = $tosave['post_id'];
		}

		// save setting and replace imported images to setting
		foreach ($config['setting'] as $key => $val) {
			$val = $this->insert_layer_vars($val, $config['image_list'], $folder['path']);

			// from mw20
			if ($this->isOldVersion($config)) {
				$val = mwBackCompatibility::meta_set($val, $key, true);
			}

			MWDB()->setPostMeta($itemId, $key, $val);
		}

		if ($addContent) {
			global $wpdb, $vePage;

			$fonts = $vePage->display->get_layer_fonts($layer, []);
			if (isset($fonts['google']) && $fonts['google']) {
				update_post_meta($itemId, 've_google_fonts', $fonts['google']);
			}
			if (isset($fonts['file']) && $fonts['file']) {
				update_post_meta($itemId, 've_file_fonts', $fonts['file']);
			}
			$wpdb->update($wpdb->prefix . 've_posts_layer', ['vpl_layer' => $layer], ['vpl_post_id' => $itemId, 'vpl_type' => 'page']);
			$itemId = wp_update_post([
					'ID' => $itemId,
					'post_content' => $layer,
			]);
		}

		return $itemId;
	}

	// import web
	function import_web_zip()
	{
		if (!isset($_POST['input_id']) || $_POST['input_id'] !== 'import_import_file') {
			return;
		}

		global $wpdb, $vePage;
		if (isset($_POST['file']) && $_POST['file']) {
			$file = $_POST['file'];
			$filePath = tus()->getUploadsDir() . '/' . $file['name'];
			$zip = new ZipArchive();
			$res = $zip->open($filePath);
			if ($res === true) {
				if (!function_exists('wp_generate_attachment_metadata')) {
					require_once(ABSPATH . 'wp-admin' . '/includes/image.php');
					require_once(ABSPATH . 'wp-admin' . '/includes/file.php');
					require_once(ABSPATH . 'wp-admin' . '/includes/media.php');
				}

				WP_Filesystem();
				$folder = wp_upload_dir();

				$zip_config = zip_open($filePath);

				$images = [];
				$files = [];
				// extract pages
				if ($zip_config) {
					while ($zip_entry = zip_read($zip_config)) {
						$filename = zip_entry_name($zip_entry);

						$filetype = wp_check_filetype($filename, null);
						if (pathinfo($filename, PATHINFO_EXTENSION) == 'php') {
							$files[$filename] = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
							zip_entry_close($zip_entry);
						} elseif (preg_match('#^image/#', $filetype['type']) && !file_exists($folder['path'] . '/' . $filename)) {
							// save list of images in zip
							$images[] = $filename;
						}
					}
				}
				zip_close($zip_config);

				if (isset($files['mw_web_install.php']) && isset($files['mw_web_setting.php'])) {
					$install = visualEditor::decode($files['mw_web_install.php']);
					$setting = visualEditor::decode($files['mw_web_setting.php']);

					//extract and save images from zip
					$zip->extractTo($folder['path'], $images);
					foreach ($images as $filename) {
						if (file_exists($folder['path'] . '/' . $filename)) {
							$filetype = wp_check_filetype($filename, null);
							$attachment = [
								'post_mime_type' => $filetype['type'],
								'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
								'post_content' => '',
								'post_status' => 'inherit',
								'guid' => $folder['url'] . '/' . $filename,
							];

							$attachment_id = wp_insert_attachment($attachment, $folder['path'] . '/' . $filename, 0, true);

							if (!is_wp_error($attachment_id)) {
								$attachment_data = $this->mw_generate_attachment_metadata($attachment_id, $folder['path'] . '/' . $filename);
								wp_update_attachment_metadata($attachment_id, $attachment_data);
								$this->images[$filename] = $attachment_id;
							}
						}
					}

					$installed_pages = [];
					$installed_contents = [];
					$installed_menus = [];
					$new_pages_id = [];

					//install menus
					if (isset($install['menus']) && is_array($install['menus'])) {
						foreach ($install['menus'] as $id => $menu) {
							//$menu_id=$this->install_menu($menu, $pages_to_install);
							$menu_id = $this->install_create_menu($menu['name']);

							$installed_menus[$id] = $menu_id;
						}
					}
					$this->menus = $installed_menus;

					//install contents
					if (isset($install['contents']) && is_array($install['contents'])) {
						foreach ($install['contents'] as $slug => $page) {
							if (isset($files[$slug . '.php'])) {
								$post_id = $this->importItem($page['post'], $files[$slug . '.php'], $folder, $page['post']['post_type']);
								$installed_contents[$page['id']] = $post_id;
							}
						}
					}
					$this->contents = $installed_contents;

					// install pages
					foreach ($install['pages'] as $slug => $page) {
						if (isset($files[$slug . '.php'])) {
							$post_id = $this->importItem($page['post'], $files[$slug . '.php'], $folder);
							$new_pages_id[$page['id']] = $post_id;

							if (isset($page['page'])) {
								if ($page['page'] == 'home') {
									update_option('page_on_front', $post_id);
									update_option('show_on_front', 'page');
								}
								if ($page['page'] == 'blog') {
									update_option('page_for_posts', $post_id);
								}
							}
							$installed_pages[$slug] = $post_id;
						}
					}

					// save blog content
					if (isset($files['mw_blog_content.php'])) {
						$config = visualEditor::decode($files['mw_blog_content.php']);

						// replace images, contents in layer
						$config['layer'] = $this->insert_layer_vars($config['layer'], $config['image_list'], $folder['path']);
						MWDB()->setLayer(0, 'blog', visualEditor::code($config['layer']), true);
					}

					// replace old parent id with new parent id
					foreach ($new_pages_id as $ipage_id) {
						$old_parent_id = wp_get_post_parent_id($ipage_id);
						if ($old_parent_id) {
							wp_update_post([
								'ID' => $ipage_id,
								'post_parent' => $new_pages_id[$old_parent_id],
							]);
						}

						// TO-DO change id of pages in links and buttons
						$result = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . "ve_posts_layer WHERE vpl_type='page' AND vpl_post_id=" . $ipage_id);
						if ($result) {
							$player = visualEditor::decode($result->vpl_layer);
							$player = $this->replace_pages_id($player, $new_pages_id);
							MWDB()->setLayer($ipage_id, 'page', visualEditor::code($player), true);
						}
					}

					//install menu items
					if (count($installed_menus)) {
						foreach ($installed_menus as $id => $menu_id) {
							$this->install_menu($install['menus'][$id], $installed_pages, $menu_id);
						}
					}

					// web setting
					if (isset($setting) && is_array($setting)) {
						foreach ($setting as $key => $val) {
							$val = $this->insert_layer_vars($val, $setting['image_list'], $folder['path']);

							// from mw20
							if ($this->isOldVersion($install)) {
								$val = mwBackCompatibility::option_set($val, $key, true);
							}

							update_option($key, $val);
						}
					}

					// web imported info
					$ve_imported_web = get_option('ve_imported_web');
					if (!$ve_imported_web) {
						$ve_imported_web = [];
					}
					$ve_imported_web[] = [
						'pages' => $installed_pages,
						'menus' => $installed_menus,
						'contents' => $installed_contents,
					];
					update_option('ve_imported_web', $ve_imported_web);

					echo '<script type="text/javascript">window.location="' . home_url() . '";</script>';
//					wp_redirect(home_url());
				} else {
					echo __('Import nelze provést. Soubor neobsahuje Mioweb šablonu.', 'cms_ve');
				}

				$zip->close();
			} else {
				echo __('Import nelze provést. Soubor není ve formátu ZIP.', 'cms_ve');
			}

			tus()->deleteFile($file['name']); // Delete zip
		} else {
			echo __('Nebyl nahrán žádný soubor k importu.', 'cms_ve');
		}

		exit();
	}

	function isOldVersion($code)
	{
		return !isset($code['version']) || $code['version'] != '3';
	}

	function mw_generate_attachment_metadata($attachment_id, $file)
	{
		$attachment = get_post($attachment_id);
		$metadata = [];
		$support = false;
		if (preg_match('!^image/!', get_post_mime_type($attachment)) && file_is_displayable_image($file)) {
			$imagesize = getimagesize($file);
			$metadata['width'] = $imagesize[0];
			$metadata['height'] = $imagesize[1];
			// Make the file path relative to the upload dir
			$metadata['file'] = _wp_relative_upload_path($file);
			// make thumbnails and other intermediate sizes
			global $_wp_additional_image_sizes;
			/*
			$sizes = array();
			foreach ( array('thumbnail','medium') as $s ) {
			$sizes[$s] = array( 'width' => '', 'height' => '', 'crop' => false );
			if ( isset( $_wp_additional_image_sizes[$s]['width'] ) )
			$sizes[$s]['width'] = intval( $_wp_additional_image_sizes[$s]['width'] ); // For theme-added sizes
			else
			$sizes[$s]['width'] = get_option( "{$s}_size_w" ); // For default sizes set in options
			if ( isset( $_wp_additional_image_sizes[$s]['height'] ) )
			$sizes[$s]['height'] = intval( $_wp_additional_image_sizes[$s]['height'] ); // For theme-added sizes
			else
			$sizes[$s]['height'] = get_option( "{$s}_size_h" ); // For default sizes set in options
			if ( isset( $_wp_additional_image_sizes[$s]['crop'] ) )
			$sizes[$s]['crop'] = intval( $_wp_additional_image_sizes[$s]['crop'] ); // For theme-added sizes
			else
			$sizes[$s]['crop'] = get_option( "{$s}_crop" ); // For default sizes set in options
			}

			if ( $sizes ) {
			$editor = wp_get_image_editor( $file );
			if ( ! is_wp_error( $editor ) )
			$metadata['sizes'] = $editor->multi_resize( $sizes );
			} else {
			$metadata['sizes'] = array();
			}
			*/
			// fetch additional metadata from exif/iptc
			$image_meta = wp_read_image_metadata($file);
			if ($image_meta) {
				$metadata['image_meta'] = $image_meta;
			}
		}

		return $metadata;
	}

	function add_webs($webs)
	{
		$this->webs = array_merge($this->webs, $webs);
	}

	function add_web_tags($tags)
	{
		$this->tags = array_merge($this->tags, $tags);
	}

	function install_steps($type = 'web')
	{
		echo '<div class="mw_web_installer_container mw_web_installer_container_' . $type . ' mw_bg_light">';
		$this->write_step($type);
		echo '</div>';
	}

	function write_step($id)
	{
		if ($id == 'licence') {
			$this->write_step_licence();
		}
		if ($id == 'web') {
			$this->write_step_web_select();
		}
	}

	function write_step_licence()
	{
		?>
		<form class="add_license_key_container mw_admin_setting_container" action="" method="post">
			<h3 class="mw_title"><?php echo __('Vložte své licenční číslo', 'cms_ve'); ?></h3>
			<p><?php echo __('Vložte své licenční číslo pro ověření platnosti této šablony. Seznam licenčních čísel naleznete <a href="' . MY_ACCOUNT_URL . '" target="_blank">na svém účtu</a>. Přístupové údaje do členské sekce vám byly zaslány na váš e-mail při zakoupení Miowebu.', 'cms_ve'); ?></p>

			<?php $license_key = get_option('web_option_license'); ?>
			<input type="text" class="mw_input"
				   placeholder="<?php echo __('Zde vložte své licenční číslo', 'cms_ve'); ?>" name="licence_key"
				   value="<?php echo $license_key['license'] ?? ''; ?>"/>
			<?php
			if (isset($license_key['license'])) {
				echo License::getStatusCode($license_key['license']);
			}
			wp_nonce_field('add_license_key', 'add_license_key_field');

			?>
			<input type="submit" class="mw_button" value="<?php echo __('Uložit licenční číslo', 'cms_ve'); ?>"/>

		</form>
		<?php
	}

	function write_step_web_select()
	{
		$title = $this->tutorial_mode ? __('S kým hrajete?', 'cms_ve') : __('Vyberte šablonu webu, která se vám líbí', 'cms_ve');
		echo '<h3 class="mw_title">' . $title . '</h3>';
		echo $this->write_web_selector(0);
	}

	function write_web_selector($reinstall = 0)
	{
		$content = '<div class="mw_web_select_container">';

		$content .= '<ul class="mw_horizontal_menu mw_select_web_tag">';
		$i = 1;
		foreach ($this->tags as $key => $val) {
			$active = $i === 1 ? 'active' : '';
			$content .= '<li><a class="mw_select_tag ' . $active . '" data-container="ve_select_web_container" data-tag="' . $key . '" href="#">' . $val . '</a></li>';
			$i++;
		}
		$content .= '</ul>';

		$content .= '<div class="mw_select_web_template_container">';
		foreach ($this->webs as $key => $val) {
			$content .= $this->get_web_item($key);
		}
		$content .= '</div>';

		$content .= '<script>
				jQuery(function ($) {
					$(".mw_web_select_container").mwWebTemplateSelector({
						confirm: ' . $reinstall . ',
					});
				});
			</script>';
		$content .= '</div>';

		return $content;
	}

	function get_web_item($id)
	{
		$path = $this->webs[$id];
		require_once($path . 'install.php');

		$show = true;
		if (isset($web['group']) && is_array($web['group'])) {
			if (!in_array(MW()->getLicense()->getSourceGroup(), $web['group'])) {
				$show = false;
			}
		}

		// show for lite version
		if (mw_is_lite_editor() && !isset($web['lite'])) {
			$show = false;
		}

		$lang = get_locale();

		// hide expert EA webs for EN
		if (($id == 'expert' || $id == 'expert2') && $lang == 'en_US') {
			$show = false;
		}

		if ($show) {
			$tag_class = 'mw_tag_item mw_tag_item_all';
			if (isset($web['tags'])) {
				foreach ($web['tags'] as $tag) {
					$tag_class .= ' mw_tag_item_' . $tag;
				}
			}

			$thumb = $lang == 'en_US' && isset($web['thumb_en']) ? $web['thumb_en'] : $web['thumb'];

			$action = 'mw_install_web_template';
			if ($this->tutorial_mode) {
				$action = 'mw_install_tut_web_template';
			}

			return mwAdminComponents::templateItem([
				'id' => $id,
				'title' => $web['title'],
				'thumb_url' => $thumb,
				'demo_url' => $web['demo'] ?? '',
				'selected' => false,
				'value' => $id,
				'button_class' => $action,
			], $tag_class);
		} // show
	}

	// web instalation
	// **********************************************************************

	function install_web_ajax()
	{
		$this->install_web($_POST['temp_id'], false, true);
		echo get_home_url();
		die();
	}

	function install_tut_web_ajax()
	{
		$current_tut = get_option(MW_CURRENT_TUTORIAL_OPTION);
		$current_tut['time'] = $_POST['time'];
		$current_tut['step'] = $_POST['step'];
		$current_tut['template'] = $_POST['temp_id'];
		$current_tut['start'] = $_POST['start'];
		update_option(MW_CURRENT_TUTORIAL_OPTION, $current_tut);

		$this->install_web($_POST['temp_id'], false);
		echo get_home_url();
		die();
	}

	function install_web_get()
	{
		$this->install_web($_GET['web_to_install'], false);
		die();
	}

	function install_web($web_to_install = 'empty', $redirect = true, $send_statistics = false)
	{
		global $vePage;

		if (isset($_POST['web_to_install'])) {
			$web_to_install = $_POST['web_to_install'];
		}

		$installed_web = get_option('ve_installed_web');
		if ($installed_web) {
			if (isset($installed_web['pages']) && is_array($installed_web['pages'])) {
				foreach ($installed_web['pages'] as $del) {
					wp_delete_post($del);
				}
			}
			if (isset($installed_web['posts']) && is_array($installed_web['posts'])) {
				foreach ($installed_web['posts'] as $del) {
					wp_delete_post($del);
				}
			}
			if (isset($installed_web['menus']) && is_array($installed_web['menus'])) {
				foreach ($installed_web['menus'] as $del) {
					wp_delete_nav_menu($del);
				}
			}
			if (isset($installed_web['contents']) && is_array($installed_web['contents'])) {
				foreach ($installed_web['contents'] as $del) {
					wp_delete_post($del);
				}
			} /*
			if(isset($installed_web['sidebars']) && is_array($installed_web['sidebars'])) {
			$sidebars = get_option('cms_sidebars');
			$deleted=array();
			if($sidebars && is_array($sidebars)) {
			foreach($installed_web['sidebars'] as $del) {
			foreach($sidebars as $sidebar) {
			if($sidebar['id']!=$del) $deleted[]=$sidebar;
			}
			}
			update_option('cms_sidebars', $deleted );
			}
			}  */
		}

		$path = $this->webs[$web_to_install];
		if (isset($_POST['web_color_to_install_' . $web_to_install])) {
			$color = $_POST['web_color_to_install_' . $web_to_install];
			$color_set = require_once($path . 'variants/' . $color . '.php');
		}
		require_once($path . 'install.php');

		if (isset($web['home'])) {
			update_option('show_on_front', $web['home']);
		}

		// install pages
		$installed_pages = [];
		if (isset($web['pages']) && is_array($web['pages'])) {
			foreach ($web['pages'] as $id => $page) {
				$post_id = $this->install_page($id, $path, $installed_pages);
				if (isset($page['page'])) {
					if ($page['page'] == 'home') {
						update_option('page_on_front', $post_id);
					}
					if ($page['page'] == 'blog') {
						update_option('page_for_posts', $post_id);
					}
				}
				$installed_pages[$id] = $post_id;
			}
		}
		$installed_posts = [];
		if (isset($web['posts']) && is_array($web['posts'])) {
			foreach ($web['posts'] as $id => $post) {
				$post_id = $this->install_post($id, $post);
				$installed_posts[$id] = $post_id;
			}
		}
		//menus
		$installed_menus = [];
		if (isset($web['menus']) && is_array($web['menus'])) {
			foreach ($web['menus'] as $id => $menu) {
				$menu_id = $this->install_menu($menu, $installed_pages);
				$installed_menus[$id] = $menu_id;
			}
		}
		//contents
		$installed_contents = [];
		if (isset($web['content_blocks']) && is_array($web['content_blocks'])) {
			foreach ($web['content_blocks'] as $id => $c) {
				$c_id = $this->install_content($id, $path);
				$installed_contents[$id] = $c_id;
			}
		}
		//sidebars
		$installed_sidebars = [];
		if (isset($web['sidebars']) && is_array($web['sidebars'])) {
			foreach ($web['sidebars'] as $id => $sidebar) {
				$sidebar_id = $this->install_sidebar($id, $sidebar);
				$installed_sidebars[$id] = $sidebar_id;
			}
		}
		//images
		$installed_images = [];
		if (isset($web['images_to_media']) && is_array($web['images_to_media'])) {
			foreach ($web['images_to_media'] as $id => $image) {
				$image_id = $this->install_image($id, $image);
				$installed_images[$id] = $image_id;
			}
		}

		// web setting
		if (file_exists($path . 'setting.php')) {
			require_once($path . 'setting.php');
			if (isset($web_setting) && is_array($web_setting)) {
				foreach ($web_setting as $key => $val) {
					update_option($key, $val);
				}
			}
		}

		// web installed info
		$ve_installed_web = [
			'web_theme' => $web_to_install,
			'pages' => $installed_pages,
			'posts' => $installed_posts,
			'menus' => $installed_menus,
			'sidebars' => $installed_sidebars,
			'contents' => $installed_contents,
			'images' => $installed_images,
		];
		update_option('ve_installed_web', $ve_installed_web);

		if ($redirect) {
			wp_redirect(home_url());
		}

		if ($send_statistics) {
			$licence = get_option('web_option_license');
			mwSendStatistics($licence['license']);
		}
	}

	function install_page($id, $path, $installed)
	{
		global $vePage;
		$post_id = '';

		require_once($path . 'pages/' . $id . '.php');
		if (isset($page['layer'])) {
			$layer = $page['layer'];
			$setting = $page['setting'];
		} else {
			$temp = explode('/', $page['page']['theme']);
			require_once(MW()->get_template_dir($temp[0]) . MW()->p_templates[$temp[0]]['path'] . $temp[1] . '/config.php');
			$layer = $config['layer'];
			$setting = $config['setting'];
		}

		if ((!isset($page['page']['page_type']) || !$page['page']['page_type'] == 'home_blog') || (isset($page['page']['page_type']) && $page['page']['page_type'] == 'blog')) {
			$new_post = [
				'post_title' => $page['page']['title'],
				'post_name' => $page['page']['slug'],
				'post_parent' => isset($page['page']['parent']) ? $installed[$page['page']['parent']] : 0,
				'post_content' => $layer,
				'post_status' => 'publish',
				'comment_status' => 'open',
				'post_type' => 'page',
			];

			$post_id = $vePage->builder->save_new_page($new_post, $page['page']['theme'], $layer);
		}

		if (isset($page['page']['page_type'])) {
			if ($page['page']['page_type'] == 'blog' || $page['page']['page_type'] == 'home_blog') {
				MWDB()->deleteLayer(0, 'blog');
				MWDB()->setLayer(0, 'blog', $layer);
			}
		}
		if (!empty($setting)) {
			foreach ($setting as $key => $val) {
				update_post_meta($post_id, $key, $val);
			}
		}

		return $post_id;
	}

	function install_post($id, $new_post)
	{
		global $vePage;

		if (!isset($new_post['title'])) {
			$new_post['title'] = __('Název článku', 'cms_ve');
		}
		if (!isset($new_post['content'])) {
			$new_post['content'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean eu imperdiet neque, eget volutpat enim. Donec tellus est, dictum sed eros id, condimentum aliquam metus. Quisque a auctor nisi. Nam tristique hendrerit lectus, non sollicitudin neque porta sed. Mauris ac bibendum diam, eu posuere sem. Nunc libero nulla, bibendum vel accumsan a, pulvinar condimentum urna. Vivamus eu neque in tellus fringilla viverra. Suspendisse sit amet arcu posuere, faucibus est in, aliquam eros. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Phasellus sed urna finibus eros elementum aliquet.

Aliquam erat volutpat. Duis suscipit vehicula dolor, ut molestie ex lacinia eget. Vivamus maximus, eros nec malesuada convallis, lacus eros congue nisi, vel vulputate dolor magna vel nisl. Pellentesque ut risus at eros feugiat porttitor. Fusce pharetra libero sed hendrerit dapibus. Maecenas in risus laoreet lectus fermentum tempor eget vitae eros. Donec id mauris id sapien commodo efficitur. Morbi vitae auctor odio.';
		}

		$new_post_args = [
			'post_title' => $new_post['title'],
			'post_name' => $id,
			'post_content' => $new_post['content'],
			'post_status' => 'publish',
			'post_type' => 'post',
			'comment_status' => 'open',
		];

		$post_id = wp_insert_post($new_post_args);

		if (isset($new_post['image']) && $new_post['image']) {
			update_post_meta($post_id, '_thumbnail_id', $new_post['image']);
		}

		return $post_id;
	}

	function install_content($id, $path)
	{
		global $vePage;

		require_once($path . 'content_blocks/' . $id . '.php');

		if (isset($page['layer'])) {
			$layer = $page['layer'];
			$setting = $page['setting'];
		} else {
			$temp = explode('/', $page['page']['theme']);
			require_once(MW()->get_template_dir($temp[0]) . MW()->p_templates[$temp[0]]['path'] . $temp[1] . '/config.php');
			$layer = $config['layer'];
			$setting = $config['setting'];
		}

		$new_post = [
			'post_title' => $page['page']['title'],
			'post_status' => 'publish',
			'post_type' => $page['page']['post_type'],
		];
		$c_id = $vePage->builder->save_new_window_post($new_post, $page['page']['theme'], $layer, $page['page']['post_type']);

		if (!empty($setting)) {
			foreach ($setting as $key => $val) {
				update_post_meta($post_id, $key, $val);
			}
		}

		return $c_id;
	}

	function install_create_menu($name, $i = 0)
	{
		$new_name = $i > 0 ? $name . '_' . $i : $name;
		$menu_exists = wp_get_nav_menu_object($new_name);
		$menu_id = !$menu_exists ? wp_create_nav_menu($new_name) : $this->install_create_menu($name, $i + 1);

		return $menu_id;
	}

	function install_menu($menu_setting, $pages, $menu_id = 0)
	{
		if (!$menu_id) {
			$menu_id = $this->install_create_menu($menu_setting['name']);
		}

		if (isset($menu_setting['items']) && is_array($menu_setting['items'])) {
			$installed_items = [];

			$i = 1;
			foreach ($menu_setting['items'] as $key => $menu_item) {
				$item = [];
				if ($menu_item['type'] == 'link') {
					$item['menu-item-type'] = 'custom';
					$item['menu-item-object'] = 'custom';
					$item['menu-item-object-id'] = '0';
					$item['menu-item-url'] = $menu_item['link'];
				} else {
					$item['menu-item-type'] = 'post_type';
					$item['menu-item-object'] = 'page';
					$item['menu-item-object-id'] = $pages[$menu_item['page']];
				}
				if (isset($menu_item['target']) && $menu_item['target']) {
					$item['menu-item-target'] = '_blank';
				}
				$item['menu-item-title'] = $menu_item['title'] ?? '';
				$item['menu-item-parent-id'] = isset($menu_item['parent']) && $menu_item['parent'] ? $installed_items[$menu_item['parent']] : 0;
				$item['menu-item-position'] = $menu_item['order'] ?? $i;
				$item['menu-item-status'] = 'publish';
				$new_item_id = wp_update_nav_menu_item($menu_id, 0, $item);

				$installed_items[$key] = $new_item_id;

				$i++;
			}
		}

		return $menu_id;
	}

	function install_image($title, $image)
	{
		$attachment = [
			'guid' => $image['url'],
			'post_mime_type' => 'image/jpeg',
			'post_title' => $title,
		];
		$attachment_metadata = [
			'width' => $image['width'],
			'height' => $image['height'],
			'file' => $image['url'],
		];
		$attachment_metadata['sizes'] = ['full' => $attachment_metadata];
		$attachment_id = wp_insert_attachment($attachment);
		wp_update_attachment_metadata($attachment_id, $attachment_metadata);

		return $attachment_id;
	}

	function install_sidebar($sidebar_id, $sidebar, $i = 1)
	{
		$new_id = $i > 1 ? $sidebar_id . '_' . $i : $sidebar_id;
		$new_name = $i > 1 ? $sidebar['name'] . ' ' . $i : $sidebar['name'];
		$sidebars = get_option('cms_sidebars');
		$exist = false;
		if (is_array($sidebars)) {
			foreach ($sidebars as $sid) {
				if ($sid['id'] == $new_id) {
					$exist = true;
				}
			}
		}
		if ($exist) {
			$new_id = $this->install_sidebar($sidebar_id, $sidebar, $i + 1);
		} else {
			$sidebars[] = [
				'name' => $new_name,
				'id' => $new_id,
				'description' => $sidebar['desc'],
			];
			update_option('cms_sidebars', $sidebars);

			if (isset($sidebar['widgets'])) {
				foreach ($sidebar['widgets'] as $wid => $widget) {
					$this->install_widget($widget, $wid, $new_id);
				}
			}
		}

		return $new_id;
	}

	function install_widget($widget, $type, $sidebar_id)
	{
		$active_sidebars = get_option('sidebars_widgets');
		$widget_options = get_option('widget_' . $type);
		$widget_options[] = $widget;
		$widget_keys = array_keys($widget_options);
		$new_id = array_pop($widget_keys);
		$active_sidebars[$sidebar_id][] = $type . '-' . $new_id; //add a widget to sidebar
		update_option('widget_' . $type, $widget_options); //update widget default options
		update_option('sidebars_widgets', $active_sidebars); //update sidebars
	}

	/** @return mwWebInstall Returns singleton instance of Installator. */
	public static function instance()
	{
		$var = static::$_instance;
		if ($var === null) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}


}
