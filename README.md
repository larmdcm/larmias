<p align="center">
    <h1 align="center">Larmias</h1>
</p>

<p align="center">轻量 • 简单 • 快速</p>

<p align="center">
<a href="https://github.com/larmdcm/larmias/issues"><img src="https://img.shields.io/github/issues/larmdcm/larmias" alt=""></a>
<a href="https://github.com/larmdcm/larmias"><img src="https://img.shields.io/github/stars/larmdcm/larmias" alt=""></a>
<img src="https://img.shields.io/badge/php-%3E%3D8.0-brightgreen" alt="">
<img src="https://img.shields.io/badge/license-apache%202-blue" alt="">
</p>

# 介绍

Larmias 是一个现代化高性能常驻内存多引擎框架，支持 Workerman 引擎和 Swoole 引擎，支持 HTTP Server、WebSocket、TCP Server、UDP Server 以及多进程等功能。
所有标准组件均基于 [PSR 标准](https://www.php-fig.org/psr) 实现，并采用强大的依赖注入设计，确保支持 `可替换` 和 `可复用` 的特性，让开发者能够更加方便地进行组件的定制和拓展。

## 组件

Larmias 框架提供以下组件：

- `larmias/log`：基于 Psr3 的日志组件，支持多通道记录日志；
- `larmias/http-message`：基于 Psr7 的 HTTP 消息组件；
- `larmias/routing`：符合 Psr7 规范的路由组件；
- `larmias/di`：基于 Psr11 的依赖注入容器；
- `larmias/event`：基于 Psr14 的事件组件；
- `larmias/http-server`：基于 Psr15 的 HTTP 服务组件；
- `larmias/cache`：基于 Psr16 的缓存组件，支持 File、Redis(可扩展)；
- `larmias/database`：数据库组件，支持连接池；
- `larmias/session`：支持多驱动的 Session 组件；
- `larmias/view`：Blade 视图组件；
- `larmias/validator`：验证器组件；
- `larmias/async-queue`：异步队列组件；
- `larmias/auth`：用户认证组件；
- `larmias/captcha`：验证码组件；
- `larmias/command`：命令行组件；
- `larmias/config`：配置读写组件；
- `larmias/contracts`：框架契约；
- `larmias/engine`：基础引擎组件；
- `larmias/engine-swoole`：基于swoole实现的引擎组件；
- `larmias/engine-workerman`：基于workerman实现的引擎组件；
- `larmias/enum`：枚举组件；
- `larmias/env`：环境变量读写组件；
- `larmias/exception-hander`：异常处理组件；
- `larmias/lock`：锁组件支持Redis；
- `larmias/pool`：连接池组件；
- `larmias/redis`：redis组件，支持连接池；
- `larmias/snowflake`：雪花算法组件；
- `larmias/utils`：常用工具组件；
- `larmias/translation`：翻译组件；
- `larmias/timer`：定时器组件；
- `larmias/throttle`：限流组件；
- `larmias/shared-memory`：内存数据库组件；
- `larmias/task`：异步任务组件；
- `larmias/crontab`：定时任务组件；

## 许可证

Larmias 框架基于 Apache 2.0开源许可证，详细信息请参见 LICENSE 文件。

## 贡献一览

[![Contributor over time](https://contributor-overtime-api.apiseven.com/contributors-svg?chart=contributorOverTime&repo=larmdcm/larmias)](https://contributor-overtime-api.apiseven.com/contributors-svg?chart=contributorOverTime&repo=larmdcm/larmias)

欢迎有兴趣的朋友参与开发