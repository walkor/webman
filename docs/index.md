---
layout: default
title: Home
nav_order: 1
description: "Just the Docs is a responsive Jekyll theme with built-in search that is easily customizable and hosted on GitHub Pages."
permalink: /
---

# Probably the fastest PHP web framework in the world.
{: .fs-9 }

Provides maximum scalability and maximum performance with the smallest core.
{: .fs-6 .fw-300 }

[Get started now](#getting-started){: .btn .btn-primary .fs-5 .mb-4 .mb-md-0 .mr-2 } [View it on GitHub](https://github.com/walkor/webman){: .btn .fs-5 .mb-4 .mb-md-0 }

---

## Getting started

### What is Webman?

webman is a high-performance HTTP service framework developed based on [workerman](https://www.workerman.net/) . Webman is used to replace the traditional php-fpm architecture and provide ultra-high performance and scalable HTTP services. You can use webman to develop websites, you can also develop HTTP interfaces or microservices.

In addition, webman also supports custom processes, which can do anything that [workerman](https://www.workerman.net/) can do, such as websocket services, Internet of Things, games, TCP services, UDP services, unix socket services, and more.

### Webman Concept

> **Provides maximum scalability and maximum performance with the smallest core.**

Webman only provides the most core functions (routing, middleware, session, custom process interface). The rest of the functions are all reused in the composer ecosystem, which means that you can use the most familiar functional components in webman. For example, in terms of databases, developers can choose to use Laravel `illuminate/database`, `ThinkPHP`, or `ThinkORM` other components `Medoo`. Integrating them in webman is very easy.

### Webman has The Following Characteristics

1. High stability. Webman is developed based on workerman, which has always been a highly stable socket framework with few bugs in the industry.
2. Super high performance. With the high performance of workerman and the in-depth optimization of HTTP services, the performance of webman is about 10-100 times higher than that of the traditional php-fpm framework, and it is also much higher than other web frameworks of the same type that reside in memory.
3. High reuse. Most composer components and class libraries can be reused without modification.
4. High scalability. Support for custom processes that can do anything workerman can do.
5. It is super simple and easy to use, the learning cost is extremely low, and the code writing is no different from the traditional framework.
6. Use the most relaxed and friendly MIT open source protocol.

### Project Address
- GitHub: [https://github.com/walkor/webman](https://github.com/walkor/webman) Don't be stingy with your little star
- Code Cloud: [https://gitee.com/walkor/webman](https://gitee.com/walkor/webman) Don't be stingy with your little star

## Third-party Authoritative Pressure Measurement Data

**techempower.com (with database business)**
![techempower.com (with database business)]({{ 'assets/images/benchmark1.png' | relative_url }})
With database query business, the single-machine throughput of webman reaches 390,000 QPS, which is nearly 80 times higher than the laravel framework of the traditional php-fpm architecture.
