---
layout: default
title: Simple Example
parent: Quick Start
nav_order: 3
---

# Simple Example
{: .no_toc }

Let's get started with creating a project using Webman as a demonstration example.
{: .fs-6 .fw-300 }
## Table of contents
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## Return String
Create a new controller file `app/controller/User.php` as follows
```php
<?php
namespace app\controller;

use support\Request;

class User
{
    public function hello(Request $request)
    {
        $default_name = 'webman';
        // Get the name parameter from the get request, and return $default_name if the name parameter is not passed
        $name = $request->get('name', $default_name);
        // return a string to the browser
        return response('hello ' . $name);
    }
}
```
access in browser `http://127.0.0.1:8787/user/hello?name=tom`

The browser will return `hello tom`

## Return JSON
Change the file `app/controller/User.php` as follows
```php
<?php
namespace app\controller;

use support\Request;

class User
{
    public function hello(Request $request)
    {
        $default_name = 'webman';
        $name = $request->get('name', $default_name);
        // Changed
        return json([
            'code' => 0,
            'msg' => 'ok',
            'data' => $name
        ]);
    }
}
```
access in browser `http://127.0.0.1:8787/user/hello?name=tom`

The browser will return `{"code":0,"msg":"ok","data":"tom""}`

Returning data using the json helper function will automatically add a header `Content-Type: application/json`

## Return XML

Likewise, using the helper function `xml($xml)` will return a `Content-Type: text/xmlheader xml` response.

where the `$xml` parameter can be a `xml` string or an `SimpleXMLElementobject`

## Return jsonp
Likewise, using a helper function `jsonp($data, $callback_name = 'callback')` will return a `jsonp` response.

## Return to view
Change the file `app/controller/User.php` as follows
```php
<?php
namespace app\controller;

use support\Request;

class User
{
    public function hello(Request $request)
    {
        $default_name = 'webman';
        $name = $request->get('name', $default_name);
        return view('user/hello', ['name' => $name]);
    }
}
```
Create a new file `app/view/user/hello.html` as follows

```html
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>webman</title>
</head>
<body>
hello <?= htmlspecialchars($name) ?>
</body>
</html>
```
Visiting in a browser `http://127.0.0.1:8787/user/hello?name=tom`

will return an hello tomhtml page with the content.

> **Note:** webman uses php native syntax as a template by default. See [Views](https://www.workerman.net/doc/webman/view.html) if you want to use other views .
