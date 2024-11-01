<?php
/*
Plugin Name: Tuxx
Plugin URI: https://www.tuxx.nl/
Description: Embed an holiday overview from Tuxx on your website by copy-and-pasting Tuxx URLs.
Version: 1.0.4
Author: Bas Koenen
Author URI: https://www.linkedin.com/in/baskoenen/
License: GPL2
*/

if (!class_exists('TuxxWordPress') && !isset($tuxx_plugin)) {

    class TuxxWordPress
    {
        public static $domains = [
            'https://www.tuxx.be/',
            'https://www.tuxx.nl/',
            'https://www.tuxxinfo.com/',
            'https://www.tuxx.in/',
            'https://www.tuxx.cn/',
            'https://www.tuxxinfo.de/',
            'https://www.tuxx.co.uk/',
            'https://www.tuxx.at/',
            'https://www.tuxx.com.br/',
            'https://www.tuxx.jp/',
            'https://www.tuxx.fr/',
            'https://www.tuxxinfo.it/',
            'https://www.tuxx.ru/',
            'https://www.tuxx.za.com/',
            'https://www.tuxx.es/',
            'https://www.tuxx.se/',
            'https://www.tuxx.ch/',
            'https://www.tuxxinfo.dk/',
            'https://www.tuxx.pl/',
            'https://www.tuxx.pt/',
            'https://www.tuxx.ae/',
            'https://www.tuxx.cz/',
            'https://www.tuxx.uk/'
        ];

        public function __construct()
        {
            //add_action('admin_menu', [$this, 'create_settings_page']);
            $this->register_hooks();
        }

        function register_hooks()
        {
            foreach (TuxxWordPress::$domains as $domain) {
                wp_oembed_add_provider($domain . '*', $domain . '/services/oembed');
            }
            add_filter('embed_oembed_html', [$this, 'embed_oembed_html'], 10, 4);
            add_filter('oembed_ttl', [$this, 'oembed_ttl']);
            add_action('template_redirect', [$this, 'oembed_update']);
        }

        public function create_settings_page()
        {
            add_menu_page(
                'Tuxx',
                'Tuxx',
                'manage_options',
                'tuxx',
                [$this, 'update_settings'],
                'dashicons-admin-plugins',
                100
            );
        }

        public function update_settings()
        {
            echo '<h1>Tuxx plugin</h1>';
            echo '<p>Currently, no options are supported. More options will be available in the near future.';
        }

        public static function get_tuxx_domain($url)
        {
            foreach (self::$domains as $domain) {
                if (strlen($url) >= strlen($domain) && substr($url, 0, strlen($domain)) == $domain) {
                    return $domain;
                }
            }
            return false;
        }

        public static function strip_tags_blacklist($html, $tags) {
            foreach ($tags as $tag) {
                $regex = '#<\s*' . $tag . '[^>]*>.*?<\s*/\s*'. $tag . '>#msi';
                $html = preg_replace($regex, '', $html);
            }
            return $html;
        }

        public static function overwrite_oembed_defaults() {
            return [
                'width' => 800,
                'height' => 800
            ];
        }

        public static function embed_oembed_html($cache, $url, $attr, $post_ID)
        {
            $tuxxDomain = self::get_tuxx_domain($url);
            if ($tuxxDomain === false) return $cache;

            // Setting for removing all links from the content
            $remove_links = true;
            if ($remove_links) {
                $cache = preg_replace('/<a\s+(.*?)>/m', '', $cache);
                $cache = preg_replace('/<\/a>/m', '', $cache);
            }

            // Hide on default
            $cache .= '<style>';
            $cache .= '.hidden-xs {';
            $cache .= '  display: none;';
            $cache .= '}';
            $cache .= '</style>';

            $cache .= '<br /><br />';
            $cache .= '<nobr>Source: <a href="' . $tuxxDomain . '" target="_blank">' . parse_url($tuxxDomain, 1) . '</a></nobr>';

            return $cache;
        }

        public static function oembed_ttl($ttl)
        {
            return 1;
        }

        public static function oembed_update()
        {
            if (is_single()) {
                $GLOBALS['wp_embed']->cache_oembed(get_queried_object_id());
            }
        }

    }

    $tuxx_plugin = new TuxxWordPress();

}