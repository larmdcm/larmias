<?php

declare(strict_types=1);

namespace Larmias\Log\Handler;

use Larmias\Log\Contracts\LoggerHandlerInterface;

class SocketLogHandler implements LoggerHandlerInterface
{
    /**
     * @var array
     */
    protected array $config = [
        // socket服务器地址
        'host' => 'localhost',
        // 发送的端口
        'port' => 1116,
        // 是否显示加载的文件列表
        'show_included_files' => false,
        // 日志强制记录到配置的client_id
        'force_client_ids' => [],
        // 限制允许读取日志的client_id
        'allow_client_ids' => [],
        //输出到浏览器默认展开的日志级别
        'expand_level' => ['debug'],
    ];

    /**
     * @var string[]
     */
    protected array $css = [
        'sql' => 'color:#009bb4;',
        'sql_warn' => 'color:#009bb4;font-size:14px;',
        'error' => 'color:#f4006b;font-size:14px;',
        'page' => 'color:#40e2ff;background:#171717;',
        'big' => 'font-size:20px;color:red;',
    ];

    /**
     * @var string
     */
    protected string $tabId = '';

    /**
     * @var string
     */
    protected string $clientId = '';

    /**
     * @var array
     */
    protected array $allowForceClientIds = [];

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = \array_merge($this->config, $config);
    }

    /**
     * @param array $logs
     * @return bool
     */
    public function save(array $logs): bool
    {
        if (!$this->check()) {
            return false;
        }
        $trace = [];
        // 基本信息
        $trace[] = [
            'type' => 'group',
            'msg' => 'cmd:' . implode(' ', $_SERVER['argv']) . ' [文件加载：' . count(get_included_files()) . ']',
            'css' => $this->css['page'],
        ];

        foreach ($logs as $type => $val) {
            $trace[] = [
                'type' => \in_array($type, $this->config['expand_level']) ? 'group' : 'groupCollapsed',
                'msg' => '[ ' . $type . ' ]',
                'css' => $this->css[$type] ?? '',
            ];

            foreach ($val as $msg) {
                if (!\is_string($msg['content'])) {
                    $msg['content'] = var_export($msg['content'], true);
                }
                $trace[] = [
                    'type' => 'log',
                    'msg' => $msg['content'],
                    'css' => '',
                ];
            }

            $trace[] = [
                'type' => 'groupEnd',
                'msg' => '',
                'css' => '',
            ];
        }
        if ($this->config['show_included_files']) {
            $trace[] = [
                'type' => 'groupCollapsed',
                'msg' => '[ file ]',
                'css' => '',
            ];

            $trace[] = [
                'type' => 'log',
                'msg' => \implode("\n", get_included_files()),
                'css' => '',
            ];

            $trace[] = [
                'type' => 'groupEnd',
                'msg' => '',
                'css' => '',
            ];
        }

        $trace[] = [
            'type' => 'groupEnd',
            'msg' => '',
            'css' => '',
        ];
        if (!empty($this->allowForceClientIds)) {
            foreach ($this->allowForceClientIds as $forceClientId) {
                $this->sendToClient($this->tabId, $forceClientId, $trace, $forceClientId);
            }
        } else {
            $this->sendToClient($this->tabId, $this->clientId, $trace);
        }
        return true;
    }

    /**
     * @return string
     */
    public function getTabId(): string
    {
        return $this->tabId;
    }

    /**
     * @param string $tabId
     */
    public function setTabId(string $tabId): void
    {
        $this->tabId = $tabId;
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    /**
     * @return bool
     */
    protected function check(): bool
    {
        if (!$this->tabId && !$this->config['force_client_ids']) {
            return false;
        }

        $allowClientIds = $this->config['allow_client_ids'];
        if (!empty($allowClientIds)) {
            //通过数组交集得出授权强制推送的client_id
            $this->allowForceClientIds = \array_intersect($allowClientIds, $this->config['force_client_ids']);
            if (!$this->tabId && \count($this->allowForceClientIds)) {
                return true;
            }
            if (!\in_array($this->clientId, $allowClientIds)) {
                return false;
            }
        } else {
            $this->allowForceClientIds = $this->config['force_client_ids'];
        }

        return true;
    }

    /**
     * @param string $tabId
     * @param string $clientId
     * @param array $logs
     * @param string $forceClientId
     * @return bool
     */
    protected function sendToClient(string $tabId, string $clientId, array $logs, string $forceClientId = ''): bool
    {
        $logs = [
            'tabid' => $tabId,
            'client_id' => $clientId,
            'logs' => $logs,
            'force_client_id' => $forceClientId,
        ];

        $msg = \json_encode($logs, \JSON_UNESCAPED_UNICODE);
        $address = '/' . $clientId;

        return $this->send($msg, $address);
    }

    /**
     * @param string $message
     * @param string $address
     * @return bool
     */
    protected function send(string $message = '', string $address = '/'): bool
    {
        $url = 'http://' . $this->config['host'] . ':' . $this->config['port'] . $address;
        $ch = \curl_init();

        \curl_setopt($ch, \CURLOPT_URL, $url);
        \curl_setopt($ch, \CURLOPT_POST, true);
        \curl_setopt($ch, \CURLOPT_POSTFIELDS, $message);
        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($ch, \CURLOPT_CONNECTTIMEOUT, 1);
        \curl_setopt($ch, \CURLOPT_TIMEOUT, 10);

        $headers = [
            "Content-Type: application/json;charset=UTF-8",
        ];

        \curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); //设置header

        return (bool)curl_exec($ch);
    }
}