<?php


class SupsysticSlider_Overview_Controller extends SupsysticSlider_Core_BaseController
{

    public function indexAction(Rsc_Http_Request $request)
    {
        $serverSettings = $this->getServerSettings();
        $config = $this->getEnvironment()->getConfig();

        return $this->response(
            '@overview/index.twig',
            array(
                'serverSettings' => $serverSettings,
                'news' => $this->loadNews($config['post_url'])
            )
        );
    }

    public function sendMailAction(Rsc_Http_Request $request) {
        $mail = $request->post->get('mail');
        $headers = 'From: ' . $mail['name'] . ' ' . $mail['email'] . "\r\n" . 'Website: ' . $mail['website'] . "\r\n" . 'Question: ' . $mail['question'] . "\r\n";
        $config = $this->getEnvironment()->getConfig();

        wp_mail($config['mail'], $mail['subject'], $headers, $mail['message']);

        return $this->redirect($this->generateUrl('overview', 'index'));
    }

    protected function getServerSettings() {
        return array(
            'Operating System' => array('value' => PHP_OS),
            'PHP Version' => array('value' => PHP_VERSION),
            'Server Software' => array('value' => $_SERVER['SERVER_SOFTWARE']),
            'MySQL' => array('value' => mysql_get_server_info()),
            'PHP Safe Mode' => array('value' => ini_get('safe_mode') ? 'Yes' : 'No', 'error' => ini_get('safe_mode')),
            'PHP Allow URL Fopen' => array('value' => ini_get('allow_url_fopen') ? 'Yes' : 'No'),
            'PHP Memory Limit' => array('value' => ini_get('memory_limit')),
            'PHP Max Post Size' => array('value' => ini_get('post_max_size')),
            'PHP Max Upload Filesize' => array('value' => ini_get('upload_max_filesize')),
            'PHP Max Script Execute Time' => array('value' => ini_get('max_execution_time')),
            'PHP EXIF Support' => array('value' => extension_loaded('exif') ? 'Yes' : 'No'),
            'PHP EXIF Version' => array('value' => phpversion('exif')),
            'PHP XML Support' => array('value' => extension_loaded('libxml') ? 'Yes' : 'No', 'error' => !extension_loaded('libxml')),
            'PHP CURL Support' => array('value' => extension_loaded('curl') ? 'Yes' : 'No', 'error' => !extension_loaded('curl')),
        );
    }

    protected function loadNews ($url) {
        $news = wp_remote_retrieve_body(wp_remote_get($url));

        return $news;
    }
} 