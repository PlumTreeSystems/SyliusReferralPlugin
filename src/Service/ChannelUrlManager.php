<?php

namespace PTS\SyliusReferralPlugin\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ChannelUrlManager
{
    private $channelPaths;

    /**
     * ChannelUrlManager constructor.
     * @param $channelPaths
     */
    public function __construct($channelPaths)
    {
        $this->channelPaths = $channelPaths;
    }

    public function isChannel($url, $channel)
    {
        if ($channel['path'] !== '/' && strpos($url, $channel['path']) === false) {
            return false;
        }
        if ($channel['domain'] !== '*' && strpos($url, $channel['domain']) === false) {
            return false;
        }
        if ($channel['path'] !== '/' || $channel['domain'] !== '*') {
            return true;
        }
        return false;
    }

    /*
     * urlParts = [
     *      'scheme'
     *      'host',
     *      'basePath',
     *      'requestUri'
     * ]
     */
    public function addChannelToUrl($urlParts, $channel)
    {

        if ($channel['domain'] !== '*') {
            $urlParts['host'] = $channel['domain'];
        }

        if ($channel['path'] !== '/') {
            $urlParts['basePath'] = $urlParts['basePath'].$channel['path'];
        }

        return $urlParts;
    }

    public function trimChannelFromUrl($urlParts, $channel)
    {
        $defaultChannel = $this->getDefaultChannel();
        if ($channel['domain'] !== '*') {
            $urlParts['host'] = $defaultChannel['domain'];
        }

        if ($channel['path'] !== '/') {
            if (isset($urlParts['basePath'])) {
                $urlParts['basePath'] = str_replace($channel['path'], $defaultChannel['path'] === '/' ? '' : $defaultChannel['path'], $urlParts['basePath']);
            }
            $urlParts['requestUri'] = str_replace($channel['path'], $defaultChannel['path'], $urlParts['requestUri']);
        }

        return $urlParts;
    }

    public function formUrl($urlParts, $optionalQueryParameters = [])
    {
        //$buildUrl = $urlParts['scheme'];

        $buildUrl = $urlParts['host'];

        if (isset($urlParts['basePath'])) {
            $buildUrl = $buildUrl.'/'.$urlParts['basePath'];
        }

        $buildUrl = $buildUrl.'/'.$urlParts['requestUri'];

        $buildUrl = $urlParts['scheme'].'://'.preg_replace('~/+~', '/', preg_replace('~http(:|%3A)//~', 'http%3A%2F%2F', $buildUrl));

        if (sizeof($optionalQueryParameters)) {
            $query = http_build_query($optionalQueryParameters);
            if (strpos($buildUrl, '?') !== false) {
                $buildUrl .= '&'.$query;
            } else {
                $buildUrl .= '?'.$query;
            }
        }

        return $buildUrl;
    }

    public function getUrlParts(Request $request)
    {
        $scheme = $request->getScheme();
        $host = $request->getHost();
        $base = $request->getBasePath();
        $requestUri = $request->getRequestUri();

        $requestUri = str_replace($base !== '/' ? $base : '', '', $requestUri);

        return [
            'scheme' => $scheme,
            'host' => $host,
            'basePath' => $base,
            'requestUri' => $requestUri
        ];
    }

    private function getDefaultChannel()
    {
        $filter = array_filter($this->channelPaths, function ($channel) {
            return $channel['default'];
        });
        if (sizeof($filter) === 1) {
            return array_pop($filter);
        }
        throw new HttpException(500, 'Default channel not found');
    }
}
