<?php

declare(strict_types=1);

namespace Larmias\Engine;

class Constants
{
    /**
     * 默认运行模式
     * @var int
     */
    public const MODE_BASE = 1;

    /**
     * worker运行模式
     * @var int
     */
    public const MODE_WORKER = 2;

    /**
     * 进程调度器
     * @var int
     */
    public const SCHEDULER_WORKER = 1;

    /**
     * 进程池调度器
     * @var int
     */
    public const SCHEDULER_WORKER_POOL = 2;

    /**
     * 协程调度器
     * @var int
     */
    public const SCHEDULER_CO_WORKER = 3;

    /**
     * 运行模式
     * @var string
     */
    public const OPTION_MODE = 'mode';

    /**
     * 调度器类型
     * @var string
     */
    public const OPTION_SCHEDULER_TYPE = 'scheduler_type';

    /**
     * 进程所属用户
     * @var string
     */
    public const OPTION_USER = 'user';

    /**
     * 设置进程所属组
     * @var string
     */
    public const OPTION_GROUP = 'group';

    /**
     * 传输层协议
     * @var string
     */
    public const OPTION_TRANSPORT = 'transport';

    /**
     * 守护进程化
     * @var string
     */
    public const OPTION_DAEMONIZE = 'daemonize';

    /**
     * Worker进程数
     * @var string
     */
    public const OPTION_WORKER_NUM = 'worker_num';

    /**
     * pid文件地址
     * @var string
     */
    public const OPTION_PID_FILE = 'pid_file';

    /**
     * 日志文件地址
     * @var string
     */
    public const OPTION_LOG_FILE = 'log_file';

    /**
     * stdout重定向文件地址
     * @var string
     */
    public const OPTION_STDOUT_FILE = 'stdout_file';

    /**
     * 端口复用
     * @var string
     */
    public const OPTION_REUSE_PORT = 'reuse_port';

    /**
     * 原生上下文配置
     * @var string
     */
    public const OPTION_RAW_CONTEXT = 'raw_context';

    /**
     * 协程hook开关
     * @var string
     */
    public const OPTION_ENABLE_COROUTINE = 'enable_coroutine';

    /**
     * 最大数据包尺寸
     * @var string
     */
    public const OPTION_PACKAGE_MAX_LENGTH = 'package_max_length';

    /**
     * 最大发送数据包尺寸
     * @var string
     */
    public const OPTION_SEND_PACKAGE_MAX_LENGTH = 'send_package_max_length';

    /**
     * Listen 队列长度
     * @var string
     */
    public const OPTION_BACKLOG = 'backlog';

    /**
     * 事件循环类
     * @var string
     */
    public const OPTION_EVENT_LOOP_CLASS = 'event_loop_class';

    /**
     * 进程循环间隔
     * @var string
     */
    public const OPTION_PROCESS_TICK_INTERVAL = 'process_tick_interval';

    /**
     * ssl
     * @var string
     */
    public const OPTION_SSL = 'ssl';

    /**
     * 进程安全停止最大等待时间
     * @var string
     */
    public const OPTION_MAX_WAIT_TIME = 'max_wait_time';

    /**
     * 进程停止等待时间
     * @var string
     */
    public const OPTION_STOP_WAIT_TIME = 'stop_wait_time';

    /**
     * 进程异常退出自动恢复
     * @var string
     */
    public const OPTION_WORKER_AUTO_RECOVER = 'worker_auto_recover';

    /**
     * log debug
     * @var string
     */
    public const OPTION_LOG_DEBUG = 'log_debug';
}