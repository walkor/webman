#!/usr/bin/env php
<?php

// 避免加载了 laravel/illuminate/foundation/helper.php 导致无法控制顺序的函数重定义报错
require_once __DIR__ . '/support/helpers.php';

require_once __DIR__ . '/vendor/autoload.php';

support\App::run();
