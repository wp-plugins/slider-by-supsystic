<?php

class Rsc_Promo_Controller extends Rsc_Mvc_Controller
{

    public function indexAction(Rsc_Http_Request $request)
    {
        $code   = $this->getEnvironment()->getPluginName();
        $config = $this->getEnvironment()->getConfig();


        if ($request->post->has('promo_viewed')) {
            update_option(sprintf('%s_promo_shown', $code), 1);
        }

        if (1 === (int) get_option(sprintf('%s_promo_shown', $code)) && !$request->query->has('debug')) {
            $this->send($request);

            return $this->redirect(
                admin_url(
                    sprintf('admin.php?page=%s', $request->query->get('page'))
                )
            );
        }

        return $this->response(
            '@promo/index.twig',
            array(
                'pluginName' => $config->get('promo_plugin_name'),
                'parameters' => array(
                    'url'      => $config->get('promo_plugin_url'),
                    'video'    => $config->get('promo_video_url'),
                    'features' => $config->get('promo_plugin_features'),
                ),
            )
        );
    }

    protected function send(Rsc_Http_Request $httpRequest)
    {
        $code = $this->getEnvironment()->getPluginName();

        $request = array(
            'url'  => 'http://supsystic.com/plugins/slider',
            'body' => array(
                'site_url'      => get_bloginfo('wpurl'),
                'site_name'     => get_bloginfo('name'),
                'where_find_us' => $httpRequest->post->get('where_find_us'),
                'desc'          => $httpRequest->post->get($httpRequest->post->get('where_find_us')),
                'plugin_code'   => $code,
            ),
        );

        wp_remote_post($request['url'], array('body' => $request['body']));
    }

}
