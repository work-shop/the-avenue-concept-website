<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class ImageHelper
 * This class implements some function for image optimize
 */
class ImageHelper
{

    /**
     * Source url of image
     *
     * @var string
     */
    public static $src = '';

    /**
     * ImageHelper constructor.
     *
     * @param array $params Params
     */
    public function __construct(array $params = array())
    {
        $c       = new ReflectionClass(__CLASS__);
        $statics = $c->getStaticProperties();
        foreach ($params as $key => $value) {
            if (!in_array($key, array_keys($statics))) {
                $this->{$key} = $value;
            } else {
                self::${$key} = $value;
            }
        }
    }

    /**
     * Get image info
     *
     * @param string $src Source url of image
     *
     * @return ImageHelper
     */
    public static function IGetPart($src = '')
    {
        if ($src === '') {
            $src = self::$src;
        }
        $ipart = new ImageHelper(array(
            'src'         => $src,
            'base_path'   => '',
            'name'        => '',
            'ext'         => '',
            'dimension'   => '',
            'name_prefix' => '',
            'name_suffix' => ''
        ));

        $dot = strrpos($src, '.');
        if ($dot === false) {
            return $ipart;
        }

        $bslash = strrpos($src, '/');
        if ($bslash === false) {
            $bslash = 0;
        } else {
            $bslash ++;
        }

        $ipart->_src      = $src;
        $ipart->base_path = substr($src, 0, $bslash);
        $ipart->name      = substr($src, $bslash, $dot - $bslash);
        $ipart->ext       = substr($src, $dot);
        $ipart->dimension = self::IGetDimension($ipart->name);
        if (!empty($ipart->dimension)) {
            $ipart->name_prefix = substr($ipart->name, 0, strpos($ipart->name, $ipart->dimension) - 1);
            $ipart->name_suffix = str_replace($ipart->name_prefix . '-' . $ipart->dimension, '', $ipart->name);
        } else {
            $ipart->name_prefix = '';
            $ipart->name_suffix = '';
        }

        return $ipart;
    }

    /**
     * Get Image width and height
     *
     * @param string $src Source url of image
     *
     * @return mixed|string
     */
    public static function IGetDimension($src)
    {
        if (preg_match_all('/(\d+)x(\d+)/i', $src, $match)) {
            return count($match) > 0 ? end($match[0]) : '';
        }

        return '';
    }

    /**
     * Replace image src
     *
     * @param string $newName New name of image
     * @param string $src     Source url of image
     *
     * @return string
     */
    public static function IReplace($newName, $src = '')
    {
        if ($src === '') {
            $src = self::$src;
        }
        if ($newName === '') {
            return $src;
        }

        $ipart = self::IgetPart($src);
        $src   = $ipart->base_path . $newName . '-' . $ipart->dimension . $ipart->name_suffix . $ipart->ext;
        return $src;
    }

    /**
     * Get image name when scan post
     *
     * @param array  $img List image name
     * @param object $obj URL of image inserted in post content
     *
     * @return boolean|mixed
     */
    public static function IHasClone($img, $obj)
    {
        if (!is_array($img)) {
            $img = array($img);
        }
        $obj_part = self::IGetPart($obj);

        foreach ($img as $_img) {
            $img_part = self::IGetPart($_img);
            if ($img_part->ext !== $obj_part->ext) {
                continue;
            }

            if ($obj_part->dimension !== '') {
                $cmp = $obj_part->name_prefix;
            } else {
                $cmp = $obj_part->name;
            }

            if (strcmp($img_part->name, $cmp) === 0) {
                return $_img;
            }
        }

        return false;
    }

    /**
     * Get image info when scan post
     *
     * @param string $content Post content
     * @param array  $imgs    List img in content
     *
     * @return array
     */
    public static function IScan($content, $imgs = array())
    {
        $ifound = array();
        $img_tags = wpmsExtractTags($content, 'img', true, true);
        //For standard images names, convert spaces to -
        $_imgs = array();
        foreach ($imgs as $iname => $iid) {
            $iname         = preg_replace('/(\s{1,})/', '-', $iname);
            $_imgs[$iname] = $iid;
        }
        if (!empty($img_tags)) {
            foreach ($img_tags as $order => $tag) {
                // only find img tag have source
                $attrs = array('src', 'width', 'height', 'alt');
                foreach ($attrs as $attr) {
                    if (empty($tag['attributes'][$attr])) {
                        ${$attr}  = false;
                    } else {
                        ${$attr}  = $tag['attributes'][$attr];
                    }
                }

                if (!$src) {
                    continue;
                }
                $img_name = self::IHasClone(array_keys($_imgs), $src);
                if ($img_name) {
                    if (!empty($_imgs[$img_name])) {
                        $ifound[$order]['id']     = $_imgs[$img_name];
                        $ifound[$order]['src']    = $src;
                        $ifound[$order]['width']  = $width;
                        $ifound[$order]['height'] = $height;
                        $ifound[$order]['alt']    = trim($alt);
                    } else {
                        continue;
                    }
                }
            }
        }

        return $ifound;
    }

    /**
     * Get posts list
     *
     * @param integer $iID      ID of post
     * @param string  $opt_type Meta type
     *
     * @return array|mixed
     */
    public static function getPostList($iID, $opt_type)
    {
        //Get image info from wp_postmeta with key equals to _metaseo_img_meta_not_good
        $meta_key = '_metaseo_' . strtolower(trim($opt_type));
        $posts    = get_post_meta($iID, $meta_key, true);

        if (is_array($posts) && !empty($posts)) {
            return $posts;
        }

        return array();
    }

    /**
     * Get image not good
     *
     * @param array $imgs  List images
     * @param array $posts List post container image
     *
     * @return array
     */
    public static function IPrepare($imgs, $posts)
    {
        $iNotGood       = array();
        $iNotGoodTotal  = array();
        $imNotGoodTotal = array();
        $imNotGood      = array();
        $upload_dir     = wp_upload_dir();

        // scan img tag for each post content
        foreach ($posts as $post) {
            if (empty($post->post_content)) {
                continue;
            }
            $ifound = self::IScan($post->post_content, $imgs);
            if (count($ifound) < 1) {
                continue;
            }

            foreach ($ifound as $order => $img) {
                $iID = $img['id'];
                //Get image that its size is not good
                $imgpath = str_replace(site_url(), ABSPATH, $img['src']);
                if (!list($width_origin, $height_origin) = getimagesize($imgpath)) {
                    continue;
                }
                $ratio_origin = $width_origin / $height_origin;
                $width        = $img['width'];
                $height       = $img['height'];
                //Check if img tag is missing with/height attribute value or not
                if (!$width && !$height) {
                    $width  = $width_origin;
                    $height = $height_origin;
                } elseif ($width && !$height) {
                    $height = $width * (1 / $ratio_origin);
                } elseif ($height && !$width) {
                    $width = $height * ($ratio_origin);
                }

                if ((int) $width_origin > (int) $width || (int) $height_origin > (int) $height) {
                    $img_before = str_replace(array($upload_dir['baseurl']), '', $img['src']);
                    $ibpart     = self::IGetPart($img_before);

                    $img_after = $ibpart->base_path
                                 . (!empty($ibpart->name_prefix) ? $ibpart->name_prefix : $ibpart->name)
                                 . '-' . $width . 'x' . $height
                                 . $ibpart->name_suffix
                                 . $ibpart->ext;

                    // create new image url and dir
                    $srcs = array(
                        'img_before_dir' => $upload_dir['basedir'] . $img_before,
                        'img_before_url' => $upload_dir['baseurl'] . $img_before,
                        'img_after_dir'  => $upload_dir['basedir'] . $img_after,
                        'img_after_url'  => $upload_dir['baseurl'] . $img_after
                    );

                    // get size img
                    $size = (filesize($srcs['img_before_dir']) / 1024);
                    if ($size > 1024) {
                        $size  = $size / 1024;
                        $sizes = 'MB';
                    } else {
                        $sizes = 'KB';
                    }
                    $size = round($size, 1);

                    $iNotGood[$iID][$post->ID]['ID']                      = $post->ID;
                    $iNotGood[$iID][$post->ID]['title']                   = $post->post_title;
                    $iNotGood[$iID][$post->ID]['post_type']               = $post->post_type;
                    $iNotGood[$iID][$post->ID]['img_before_optm'][$order] = array(
                        'size'      => $size,
                        'sizes'     => $sizes,
                        'src'       => $srcs['img_before_url'],
                        'width'     => $width,
                        'height'    => $height,
                        'dimension' => self::IGetDimension($img_before)
                    );

                    $iNotGood[$iID][$post->ID]['img_after_optm'][$order] = array(
                        'size'       => 0,
                        'path'       => $img_after,
                        'src'        => $srcs['img_after_url'],
                        'src_origin' => $srcs['img_before_url'],
                        'width'      => $width,
                        'height'     => $height,
                        'dimension'  => self::IGetDimension($img_after)
                    );

                    //Get the number of images which their size are not good
                    if (!isset($iNotGoodTotal[$iID])) {
                        $iNotGoodTotal[$iID] = 0;
                    }
                    $iNotGoodTotal[$iID] ++;
                } else {
                    if (!isset($iNotGood[$iID])) {
                        $iNotGood[$iID] = array();
                    }
                    if (!isset($iNotGoodTotal[$iID])) {
                        $iNotGoodTotal[$iID] = 0;
                    }
                }

                //Get image that its meta/metas is/are not good
                $meta_value                                                = $img['alt'];
                $imNotGood[$iID][$post->ID]['ID']                          = $post->ID;
                $imNotGood[$iID][$post->ID]['title']                       = $post->post_title;
                $imNotGood[$iID][$post->ID]['post_type']                   = $post->post_type;
                $imNotGood[$iID][$post->ID]['meta'][$order]['img_src']     = $img['src'];
                $imNotGood[$iID][$post->ID]['meta'][$order]['type']['alt'] = $meta_value;
                if ($meta_value === '') {
                    if (!isset($imNotGoodTotal[$iID]['alt'])) {
                        $imNotGoodTotal[$iID]['alt'] = 0;
                    }
                    $imNotGoodTotal[$iID]['alt'] ++;
                }
            }
        }

        foreach ($imgs as $name => $iID) {
            if (!isset($iNotGoodTotal[$iID])) {
                $iNotGoodTotal[$iID] = - 1;
            }
            if (!isset($iNotGood[$iID])) {
                $iNotGood[$iID] = array();
            }
            if (!isset($imNotGood[$iID])) {
                $imNotGood[$iID] = array();
            }
            if (!isset($imNotGoodTotal[$iID])) {
                $imNotGoodTotal[$iID]['alt'] = 0;
            }
        }

        foreach ($imNotGoodTotal as &$mStatis) {
            if (!isset($mStatis['alt'])) {
                $mStatis['alt'] = 0;
            }
        }

        unset($posts, $imgs);

        $ret = array(
            'iNotGood'       => $iNotGood,
            'iNotGoodTotal'  => $iNotGoodTotal,
            'imNotGood'      => $imNotGood,
            'imNotGoodTotal' => $imNotGoodTotal
        );

        return $ret;
    }

    /**
     * Scan image that has not good size or not good meta(s), then update info to postmeta table
     *
     * @param array   $imgs   List images
     * @param boolean $delete Is delete
     * @param integer $postID ID of post
     *
     * @return array
     */
    public static function IScanPosts($imgs, $delete = false, $postID = 0)
    {
        global $wpdb;
        $imgs_1 = array_slice($imgs, 0, 1);
        $idPost = array_shift($imgs_1);

        // get list sizes image
        $metadats = get_post_meta($idPost, '_wp_attachment_metadata', true);
        $sizes    = $metadats['sizes'];

        // get list url image and image thumbnail
        $list_thum_url   = array();
        $imageUrl        = wp_get_attachment_url($idPost);
        $list_thum_url[] = $imageUrl;
        if (!empty($sizes)) {
            foreach ($sizes as $key => $size) {
                $thum_url        = wp_get_attachment_image_src($idPost, $key);
                $list_thum_url[] = $thum_url[0];
            }
        }

        $msg   = array();
        $_imgs = array_flip($imgs);
        // sql where
        $w = '';
        $w .= '(';
        $i = 0;
        foreach ($list_thum_url as $url) {
            $url = str_replace(array('https', 'http'), array('', ''), $url);
            $i ++;
            if ((int) $i === count($list_thum_url)) {
                $w .= $wpdb->prepare(' post_content LIKE %s', array('%' . $url . '%'));
            } else {
                $w .= $wpdb->prepare(' post_content LIKE %s OR', array('%' . $url . '%'));
            }
        }

        $w     .= ')';
        $where = array();
        if ($delete) {
            $where[] = 'ID != ' . (int) $postID;
        }

        $post_types = MetaSeoContentListTable::getPostTypes();
        foreach ($post_types as &$post_type) {
            $post_type = esc_sql($post_type);
        }

        $post_types = implode("', '", $post_types);
        $where[]    = 'post_type IN (\'' . $post_types . '\')';
        $where[]    = "post_content LIKE '%<img%'";
        $where[]    = $w;
        // query post
        $posts = $wpdb->get_results('SELECT ID, post_title, post_content, post_type, post_date
					FROM ' . $wpdb->posts . '
					WHERE ' . implode(' AND ', $where) . ' ORDER BY ID'); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Variable has been prepare

        $results = self::IPrepare($imgs, $posts);
        //Update some value into fields in wp_postmeta
        if (count($results['iNotGood']) > 0) {
            foreach ($results['iNotGood'] as $iID => $post_group) {
                if ($results['iNotGoodTotal'][$iID] > 0) {
                    //This has a litle bit value
                    if ($results['iNotGoodTotal'][$iID] > 1) {
                        $im = ' images ';
                    } else {
                        $im = ' image ';
                    }

                    $msg[$iID]['iNotGood']['msg']
                                                      = $results['iNotGoodTotal'][$iID] . $im . esc_html__('with wrong size', 'wp-meta-seo');
                    $msg[$iID]['iNotGood']['warning'] = true;

                    $msg[$iID]['iNotGood']['button'] = '<a href="javascript:void(0);"
                     class=" img-resize wpmsbtn wpmsbtn_small" data-img-name="' . esc_attr($_imgs[$iID]) . '"
                      data-post-id="' . esc_attr($iID) . '" data-opt-key="resize_image"
                       onclick="showPostsList(this)">
                       ' . esc_html__('Resize image', 'wp-meta-seo') . '<span class="spinner-light"></span></a>';

                    update_post_meta($iID, '_metaseo_resize_image_counter', $results['iNotGoodTotal'][$iID]);
                    update_post_meta($iID, '_metaseo_resize_image', $post_group);
                } else {
                    $msg[$iID]['iNotGood']['msg']     = esc_html__('Image sizes are good!', 'wp-meta-seo');
                    $msg[$iID]['iNotGood']['warning'] = false;

                    delete_post_meta($iID, '_metaseo_resize_image_counter');
                    delete_post_meta($iID, '_metaseo_resize_image');
                }
            }
        }

        // create list image wrong
        if (count($results['imNotGood']) > 0) {
            foreach ($results['imNotGood'] as $iID => $post_group) {
                //This has a litle bit value
                if ($results['imNotGoodTotal'][$iID]['alt'] > 1) {
                    $i                                    = $results['imNotGoodTotal'][$iID]['alt'] . ' alt text';
                    $msg[$iID]['imNotGood']['msg']['alt'] = $i . esc_html__('s are missing', 'wp-meta-seo');
                } elseif ((int) $results['imNotGoodTotal'][$iID]['alt'] === 1) {
                    $i                                    = $results['imNotGoodTotal'][$iID]['alt'] . ' alt text';
                    $msg[$iID]['imNotGood']['msg']['alt'] = $i . esc_html__(' is missing', 'wp-meta-seo');
                }

                $msg[$iID]['imNotGood']['warning'] = true;
                $msg[$iID]['imNotGood']['button']  = '<a href="javascript:void(0);"
                 class=" fix-metas wpmsbtn wpmsbtn_small" data-img-name="' . esc_attr($_imgs[$iID]) . '"
                  data-post-id="' . esc_attr($iID) . '" data-opt-key="fix_metas" onclick="showPostsList(this)"
                   alt="' . esc_attr__('Edit the image information', 'wp-meta-seo') . '">
                   ' . esc_html__('Edit meta in content', 'wp-meta-seo') . '<span class="spinner-light"></span></a>';


                update_post_meta($iID, '_metaseo_fix_metas_counter', count($post_group));
                update_post_meta($iID, '_metaseo_fix_metas', $post_group);


                if ((int) $results['imNotGoodTotal'][$iID]['alt'] === 0) {
                    if ((int) $results['iNotGoodTotal'][$iID] !== - 1) {
                        $msg[$iID]['imNotGood']['button'] = '<a href="javascript:void(0);"
                         class=" fix-metas wpmsbtn wpmsbtn_small wpmsbtn_secondary" data-img-name="' . esc_attr($_imgs[$iID]) . '"
                          data-post-id="' . esc_attr($iID) . '" data-opt-key="fix_metas" onclick="showPostsList(this)"
                           alt="' . esc_attr__('This image has been detected in your content,
                            edit information here…', 'wp-meta-seo') . '">
                           ' . esc_html__('Edit meta', 'wp-meta-seo') . '<span class="spinner-light"></span></a>';
                    } else {
                        $msg[$iID]['imNotGood']['button'] = '';
                    }
                    $msg[$iID]['imNotGood']['warning'] = false;
                    $msg[$iID]['imNotGood']['msg']     = '';
                }
            }
        }

        unset($results, $imgs);

        return $msg;
    }

    /**
     * Ajax optimize image and update content
     *
     * @param integer $post_id     Post id
     * @param integer $img_post_id Image id
     * @param array   $img_exclude Image exclude
     *
     * @return array
     */
    public static function optimizeImages($post_id, $img_post_id, $img_exclude)
    {
        global $wpdb;
        $ret = array('success' => false, 'msg' => '');
        $res = $wpdb->get_row($wpdb->prepare('SELECT post_content FROM ' . $wpdb->posts . ' WHERE ID=%d', array($post_id)));
        if (empty($res)) {
            $ret['msg'] = esc_html__('This post is not existed or deleted, please choose one another!', 'wp-meta-seo');
            return $ret;
        }
        $post_content   = $res->post_content;
        $imgs_to_resize = get_post_meta($img_post_id, '_metaseo_resize_image', true);
        if (preg_match_all('#<img.*/>#Usmi', $post_content, $matches)) {
            $replacement = array();
            foreach ($matches[0] as $order => $tag) {
                $replacement[$order] = $tag;
                //This block of code maybe changed later
                if (preg_match('/(width|height)="([^\"]+)"/i', $tag, $dimension)) {
                    if (!isset($imgs_to_resize[$post_id])) {
                        continue;
                    }

                    foreach ($imgs_to_resize[$post_id]['img_after_optm'] as $key => $img) {
                        if (!in_array($order, $img_exclude)) {
                            if (stripos($tag, $img['src_origin']) !== false &&
                                (('width' === $dimension[1] && (int) $dimension[2] === (int) $img['width'])
                                 || ('height' === $dimension[1] && (int) $dimension[2] === (int) $img['height']))
                            ) {
                                $replacement[$order] = str_replace($img['src_origin'], $img['src'], $tag);
                            }
                        }
                    }
                }
                #}
            }

            //Replace all imgs sources that have new value
            $post_content = str_replace($matches[0], $replacement, $post_content);
        }

        //Update post content with all imgs has been just optimized
        $id = wp_update_post(array(
            'ID'           => $post_id,
            'post_content' => $post_content
        ));

        if ($id) {
            $ret['success'] = true;
            $ret['msg']     = esc_html__('Well! This image is so good now.', 'wp-meta-seo');
        } else {
            $ret['msg'] = esc_html__('Opps! An error occured when updating the post, please try again', 'wp-meta-seo');
        }

        return $ret;
    }

    /**
     * Create new size for image
     *
     * @param string $src         Source url of image
     * @param float  $width       Width of image
     * @param float  $height      Height of image
     * @param string $destination Destination
     *
     * @return boolean|string
     */
    public static function IResize($src, $width, $height, $destination)
    {
        $imgpath = str_replace(site_url(), ABSPATH, $src);
        if (!list($w_origin, $h_origin) = getimagesize($imgpath)) {
            return esc_html__('Unsupported picture type!', 'wp-meta-seo');
        }

        if (is_readable($destination)) {
            return true;
        }

        $type = strtolower(substr(strrchr($imgpath, '.'), 1));
        if ($type === 'jpeg') {
            $type = 'jpg';
        }

        switch ($type) {
            case 'bmp':
                $img = imagecreatefromwbmp($imgpath);
                break;
            case 'gif':
                $img = imagecreatefromgif($imgpath);
                break;
            case 'jpg':
                $img = imagecreatefromjpeg($imgpath);
                break;
            case 'png':
                $img = imagecreatefrompng($imgpath);
                break;
            default:
                return esc_html__('Unsupported picture type!', 'wp-meta-seo');
        }

        $new = imagecreatetruecolor($width, $height);

        // preserve transparency
        if ($type === 'gif' || $type === 'png') {
            imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
            imagealphablending($new, false);
            imagesavealpha($new, true);
        }

        imagecopyresampled($new, $img, 0, 0, 0, 0, $width, $height, $w_origin, $h_origin);

        //Output
        switch ($type) {
            case 'bmp':
                imagewbmp($new, $destination);
                break;
            case 'gif':
                imagegif($new, $destination);
                break;
            case 'jpg':
                imagejpeg($new, $destination);
                break;
            case 'png':
                imagepng($new, $destination);
                break;
        }

        return true;
    }
}
