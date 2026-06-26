<?php

define('MW_FILESIZE_LIMIT', 1000000);

class mw_upload_image_size_limit
{

	public function __construct()
	{
		//add_filter('wp_handle_upload_prefilter', array($this, 'error_message'));
		add_filter('wp_handle_upload', [$this, 'upload_handler']);
		//add_filter('sanitize_file_name', 'new_filename', 10, 2);
	}

	/*
	public function new_filename($filename, $filename_raw) {
	$data = normalizer_normalize($filename);
	$data= iconv('UTF-8', 'ASCII//TRANSLIT', $data);
	$data = preg_replace("#[^A-Za-z1-9]#","_", $data);
	return $data;
	}
	*/

	public function upload_handler($file)
	{
		$image_type = $file['type'];
		if (in_array($image_type, ['image/jpg', 'image/jpeg', 'image/png'], true)) {
			$image_editor = wp_get_image_editor($file['file']);

			if (!is_wp_error($image_editor)) {
				$filesize = filesize($file['file']);
				//$file['error'] = json_encode($filesize);
				if ($filesize > MW_FILESIZE_LIMIT) {
					$sizes = $image_editor->get_size();
					if ((isset($sizes['width']) && $sizes['width'] > 2000)
						|| (isset($sizes['height']) && $sizes['height'] > 2000)
					) {
						$image_editor->resize(2000, 2000, false);
					}
					$image_editor->set_quality(80);
					$saved_image = $image_editor->save($file['file']);
				}
			}
		}

		return $file;
	}
}

$mw_upload_image_size_limit = new mw_upload_image_size_limit();
//add_action('admin_head', array($WP_Image_Size_Limit, 'load_styles'));
