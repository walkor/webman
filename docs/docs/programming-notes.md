---
layout: default
title: Programming Notes
nav_order: 2
---

# Programming Notes
{: .no_toc }

Developer should to know how and what Webman can do.
{: .fs-6 .fw-300 }
## Table of contents
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## Operating System

Webman supports both linux and windows systems ( [custom processes](https://www.workerman.net/doc/webman/process.html) are not supported under windows ). However, since workerman cannot support multi-process settings and daemon processes under windows, the windows system is only recommended for development and debugging in the development environment. For the official environment, please use the linux system.

## Resident Memory
Webman is a resident memory framework. Generally speaking, after php files are loaded into memory, they will be reused and will not be read from disk again (except template files). Therefore, the formal environment business code or configuration changes need to be executed `php start.php reload` to take effect. If you change the process-related configuration, you need to restart `php start.php restart`.

> _In order to facilitate development, webman comes with a FileMonitor custom process to monitor business file updates, and automatically execute reload when business files are updated. This function is only valid under linux system and running in debug mode._

## About output statements
In traditional php-fpm projects, the echo var_dumpoutput data using functions such as etc. will be directly displayed on the page, while in webman, these outputs are often displayed on the terminal and not displayed on the page (except for the output in the template file).

## Do not execute `exit` `die` statement
Executing die or exit will cause the process to exit and restart, causing the current request to not be properly responded to.

## Don't execute the `pcntl_fork` function
`pcntl_fork` User creates a process, which is not allowed in webman.
