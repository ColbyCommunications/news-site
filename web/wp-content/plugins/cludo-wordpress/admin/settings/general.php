<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}

$section = new CludoSettingsSection('general', $this);

function cludo_url_get_host_and_path($parsed_url){
	$path = array_key_exists('path', $parsed_url) ? $parsed_url['path'] : '';

	return $parsed_url['host'] . rtrim($path, '/');
}

$section->addField('_crawler_info', function(CludoSettingsSection $main){
	$crawler_html = '<ul class="cludo__crawler-info">';

	$crawlers = $main->api->getIndexes();

	$site_url = parse_url(get_site_url());
	$crawler_scheme = '';
	$current_site_index_possible = false;

	foreach ($crawlers as $crawler){
		$crawler_html .= '<li>';
		$crawler_html .= '<h4><span class="cludo__crawler__name">' . $crawler['name'] . '</span>';
		$crawler_html .= '<a href="https://my.cludo.com/content/crawlers/details/'.$crawler['id'].'" class="cludo__crawler__configure" target="_blank"><span class="dashicons dashicons-admin-generic"></span><span class="cludo__crawler__configure__label">Configure</span></a></h4>';

		$is_indexing_site_url = false;
		$is_https_mismatch = false;

		foreach($crawler['urls'] as $url){
			$crawler_url = parse_url($url);

			// Check if host + path are the same
			if(cludo_url_get_host_and_path($site_url) === cludo_url_get_host_and_path($crawler_url)){
				$is_indexing_site_url = true;

				$crawler_scheme = $crawler_url['scheme'];

				if($site_url['scheme'] !== $crawler_url['scheme']){
					$is_https_mismatch = true;
				}
				else {
					$is_https_mismatch = false;
					$current_site_index_possible = true;
					break;
				}
			}
		}

		if(!$is_indexing_site_url){
			$crawler_html .= '<span class="cludo__info">'. __('Not configured to index this website.', CLUDO_WP_TEXTDOMAIN) . '</span>';
		}
		elseif($is_https_mismatch){
			$crawler_html .= '<span class="cludo__warning">'. sprintf(__('Error: Crawler URL is added as %s, but this site uses %s.<br />Please use HTTPS for both your website and your crawler.<br />No pages will be indexed.', CLUDO_WP_TEXTDOMAIN), strtoupper($crawler_scheme), strtoupper($site_url['scheme']) ) . '</span>';
		}
		else {
			$crawler_html .= '<span class="cludo__success">'. __('Indexing this website.', CLUDO_WP_TEXTDOMAIN) . '</span>';
		}

	}

	$crawler_html .= '</ul>';

	$html = '';
	if(!$current_site_index_possible){
		$html .= '<div class="notice notice-error update-message"><p>&nbsp;' . sprintf(__('<strong>Warning: No content will be indexed.</strong> <br/> Please configure at least one crawler to include %s.', CLUDO_WP_TEXTDOMAIN), get_site_url()) . '</p></div>';
	}
	$html .= $crawler_html;

	return ['type' => 'html', 'title' => 'Crawlers', 'default' => $html];
});

$section->addSection();