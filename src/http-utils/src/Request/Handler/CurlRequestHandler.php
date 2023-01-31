<?php

declare(strict_types=1);

namespace Larmias\Http\Utils\Request\Handler;

use Larmias\Http\Message\Response;
use Larmias\Http\Message\Stream;
use Larmias\Http\Utils\Request\Exceptions\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Larmias\Http\Utils\Request\CookieOption;

class CurlRequestHandler implements RequestHandlerInterface
{
    /**
     * @param RequestInterface $request
     * @param array $options
     * @return ResponseInterface
     */
    public function send(RequestInterface $request, array $options): ResponseInterface
    {
        // 初始化
        $ch = \curl_init();

        // 设置请求头
        $headers = [];
        foreach ($request->getHeaders() as $name => $value) {
            if (\is_array($value)) {
                continue;
            }
            $headers[] = $name . ':' . $value;
        }

        if (!empty($headers)) {
            \curl_setopt($ch, \CURLOPT_HTTPHEADER, $headers);
        }

        // 设置请求方式
        \curl_setopt($ch, \CURLOPT_CUSTOMREQUEST, $request->getMethod());

        // 设置请求数据
        $body = $request->getBody();
        $size = $body->getSize();
        if ($size !== null && $size > 0) {
            \curl_setopt($ch, \CURLOPT_POSTFIELDS, (string)$body);
        }

        // 设置请求地址
        \curl_setopt($ch, \CURLOPT_URL, (string)$request->getUri()->withFragment(''));

        // 不校验https
        if (isset($options['valid_https']) && !$options['valid_https']) {
            \curl_setopt($ch, \CURLOPT_SSL_VERIFYPEER, false);
            \curl_setopt($ch, \CURLOPT_SSL_VERIFYHOST, false);
        }

        // 设置超时
        if (isset($options['timeout']) && $options['timeout']) {
            \curl_setopt($ch, \CURLOPT_TIMEOUT, $options['timeout']);
        }

        // 设置代理访问
        if (isset($options['proxy']) && $options['proxy']) {
            \curl_setopt($ch, \CURLOPT_PROXYTYPE, \CURLPROXY_HTTP);
            \curl_setopt($ch, \CURLOPT_PROXY, $options['proxy']);
        }
        // 设置保存cookie
        if (isset($options['cookie']) && $options['cookie']) {
            /** @var CookieOption $cookie */
            $cookie = \is_string($options['cookie']) ? new CookieOption($options['cookie']) : $options['cookie'];
            if ($cookie->savePath) {
                \curl_setopt($ch, \CURLOPT_COOKIEJAR, $cookie->savePath);
            }
            if ($cookie->content) {
                \curl_setopt($ch, \CURLOPT_COOKIE, $cookie->content);
            }
            if ($cookie->file) {
                \curl_setopt($ch, \CURLOPT_COOKIEFILE, $cookie->file);
            }
        }

        // 以文件流信息返回不输出
        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        // 抓取跳转后的页面
        \curl_setopt($ch, \CURLOPT_FOLLOWLOCATION, true);
        try {
            // 获取结果
            $result = \curl_exec($ch);
            $errNo = \curl_errno($ch);
            if ($errNo !== CURLE_OK) {
                $error = \curl_error($ch);
                throw new RequestException($request, $error . ': ' . $errNo);
            }
            $info = \curl_getinfo($ch);
            $response = new Response();
            $response = $response->withStatus($info['http_code'] ?? 200)->withProtocolVersion($info['protocol'] ?? '');
            $response = $response->withHeader('Content-Type', $info['content_type'] ?? '');
            $stream = Stream::create($result);
            $stream->seek(0);
            return $response->withBody($stream);
        } finally {
            // 关闭
            \curl_close($ch);
        }
    }
}