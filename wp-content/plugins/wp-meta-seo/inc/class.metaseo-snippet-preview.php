<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class WPMSEOSnippetPreview
 * Generates a Google Search snippet preview.
 * Takes a $post, $title and $description
 */
class WPMSEOSnippetPreview
{

    /**
     * Snippet content
     *
     * @var string
     */
    protected $content;
    /**
     * Options
     *
     * @var array
     */
    protected $options;
    /**
     * Current post
     *
     * @var object
     */
    protected $post;
    /**
     * Snippet title
     *
     * @var string
     */
    protected $title;
    /**
     * Snippet description
     *
     * @var string
     */
    protected $description;
    /**
     * Snippet date
     *
     * @var string
     */
    protected $date = '';
    /**
     * Snippet URL
     *
     * @var string
     */
    protected $url;
    /**
     * Snippet slug
     *
     * @var string
     */
    protected $slug = '';

    /**
     * WPMSEOSnippetPreview constructor.
     *
     * @param object $post        Current post
     * @param string $title       Title
     * @param string $description Description
     *
     * @return void
     */
    public function __construct($post, $title, $description)
    {
        $this->post        = $post;
        $this->title       = esc_html($title);
        $this->description = esc_html($description);

        $this->setDate();
        $this->setUrl();
        $this->setContent();
    }

    /**
     * Getter for $this->content
     *
     * @return string html for snippet preview
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Sets date if available
     *
     * @return void
     */
    protected function setDate()
    {
        if (is_object($this->post) && isset($this->options['showdate-' . $this->post->post_type])
            && $this->options['showdate-' . $this->post->post_type] === true) {
            $date       = $this->getPostDate();
            $this->date = '<span class="date">' . esc_html($date) . ' - </span>';
        }
    }

    /**
     * Retrieves a post date when post is published, or return current date when it's not.
     *
     * @return string
     */
    protected function getPostDate()
    {
        if (isset($this->post->post_date) && $this->post->post_status === 'publish') {
            $date = date_i18n('j M Y', strtotime($this->post->post_date));
        } else {
            $date = date_i18n('j M Y');
        }

        return (string) $date;
    }

    /**
     * Generates the url that is displayed in the snippet preview.
     *
     * @return void
     */
    protected function setUrl()
    {
        $this->url = str_replace(array('http://', 'https://'), '', get_bloginfo('url')) . '/';
        $this->setSlug();
    }

    /**
     * Sets the slug and adds it to the url if the post has been published and the post name exists.
     *
     * If the post is set to be the homepage the slug is also not included.
     *
     * @return void
     */
    protected function setSlug()
    {
        $frontpage_post_id   = (int) (get_option('page_on_front'));
        $permalink_structure = get_option('permalink_structure');
        if (is_object($this->post) && isset($this->post->post_name) && $this->post->post_name !== ''
            && (int) $this->post->ID !== (int) $frontpage_post_id) {
            $this->slug = sanitize_title($this->title);
            if (!empty($permalink_structure)) {
                $this->url .= esc_html($this->slug);
            }
        }
    }

    /**
     * Generates the html for the snippet preview and assign it to $this->content.
     *
     * @return void
     */
    protected function setContent()
    {
        $content = '<div id="wpmseosnippet">
<a class="title" id="wpmseosnippet_title" href="#">' . esc_html($this->title) . '</a>
<span class="url">' . esc_url($this->url) . '</span>
<p class="desc">' . esc_html($this->date) . '<span class="autogen"></span><span class="content">' . esc_html($this->description) . '</span></p>
</div>';
        $this->setContentThroughFilter($content);
    }

    /**
     * Sets the html for the snippet preview through a filter
     *
     * @param string $content Content string.
     *
     * @return void
     */
    protected function setContentThroughFilter($content)
    {
        $properties         = get_object_vars($this);
        $properties['desc'] = $properties['description'];
        $this->content      = apply_filters('wpmseo_snippet', $content, $this->post, $properties);
    }
}
