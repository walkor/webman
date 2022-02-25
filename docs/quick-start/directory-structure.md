---
layout: default
title: Directory Structure
parent: Quick Start
nav_order: 2
---

# Directory Structure
```
.
├── app                           Application directory
│   ├── controller                Controller directory
│   ├── model                     Model directory
│   ├── view                      View directory
│   └── middleware                App Middleware directory
│       └── StaticFile.php        Static File Middleware (default)
├── config                        Configuration directory
│   ├── app.php                   App configuration
│   ├── autoload.php              File in this file will load automaticly
│   ├── bootstrap.php             Callback configuration to run when onWorkerStart when the process starts
│   ├── container.php             Container arrangement
│   ├── dependence.php            Container Dependency Configuration
│   ├── database.php              Database configuration
│   ├── exception.php             Exception configuration
│   ├── log.php                   Log configuration
│   ├── middleware.php            Middleware configuration
│   ├── process.php               Custom process configuration
│   ├── redis.php                 Redis configuration
│   ├── route.php                 Routing configuration
│   ├── server.php                Server configuration such as port and number of processes
│   ├── view.php                  View configuration
│   ├── static.php                Static file switch and static file middleware configuration
│   ├── translation.php           Multilingual configuration
│   └── session.php               Session configuration
├── public                        Static resource directory
├── process                       Custom process directory
├── runtime                       The runtime directory of the application, which requires writable permissions
├── start.php                     Service startup file
├── vendor                        The third-party class library directory installed by composer
└── support                       Class library adaptation (including third-party class libraries)
    ├── Db.php                    Database adaptation
    ├── Redis.php                 Redis class
    ├── Cache.php                 Cache class
    ├── Log.php                   Log class
    ├── Translation.php           Multilingual
    ├── View.php                  View class
    ├── Container.php             Container class
    ├── Request.php               Request class
    ├── Response.php              Response class
    ├── helpers.php               Helper function
    ├── bootstrap                 The class directory called when the process starts onWorkerStart
    │   └── Session.php           Initialize the session class when the process starts
    ├── exception                 Exception correlation
    │   ├── BusinessException.php Business exception class
    │   └── Handler.php           Business exception capture processing class
    └── view                      View class catalog, supporting multiple template engines
        ├── Blade.php             Blade view class
        ├── Raw.php               Native view class
        ├── ThinkPHP.php          ThinkPHP view class
        └── Twig.php              Twig view class
```
