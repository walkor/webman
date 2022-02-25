---
layout: default
title: Install
parent: Quick Start
nav_order: 1
---

# Install with Compoeser
1. Set up the Alibaba Cloud composer proxy

Since domestic access to composer is relatively slow, it is recommended to set up the Alibaba Cloud composer image and run the following command to set up the Alibaba Cloud proxy
```
composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
```

2. Create a project
`composer create-project workerman/webman`

3. Run
Enter the webman directory
Run in debug mode (for development and debugging)
```
php start.php start
```
Run in daemon mode (for formal environment)
```
php start.php start -d
```

> **Notice** Since version 1.2.3, webman has provided a startup script for windows system (you need to configure environment variables for php). For windows users, please double-click windows.bat to start webman, or run to php windows.phpstart webman.

4. Access

browser access `http://<your-ip-address>:8787`
