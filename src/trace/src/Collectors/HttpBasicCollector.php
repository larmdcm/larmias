<?php

declare(strict_types=1);

namespace Larmias\Trace\Collectors;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function date;
use function time;
use function microtime;
use function number_format;

class HttpBasicCollector extends BaseCollector
{
    /**
     * 前置收集处理
     * @param array $args
     * @return void
     */
    public function beforeHandle(array $args): void
    {
        /** @var ServerRequestInterface $request */
        $request = $args['request'];
        $baseData = [
            'begin_time' => microtime(true),
            'request_uri' => (string)$request->getUri(),
            'request_method' => $request->getMethod(),
            'request_time' => date('Y-m-d H:i:s', time()),
        ];
        $this->collect($baseData);
    }

    /**
     * 后置收集处理
     * @param array $args
     * @return void
     */
    public function afterHandle(array $args): void
    {
        /** @var ResponseInterface $response */
        $response = $args['response'];
        $baseData['run_time'] = number_format(microtime(true) - $this->data['begin_time'], 10, '.', '');
        $baseData['reqs'] = $baseData['run_time'] > 0 ? number_format(1 / $baseData['run_time'], 2) : '∞';
        $baseData['response_time'] = date('Y-m-d H:i:s', time());
        $baseData['response_status_code'] = $response->getStatusCode();
        $baseData['response_reason'] = $response->getReasonPhrase();
        $this->collect($baseData);
    }
}