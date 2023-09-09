<?php
return [
    //是否开启域名路由
    'enable' => true,
    // 多应用绑定关系
    'bind' => [
        // 'abc.com' => '', // 不属于任何应用
        'abcd'  => 'touser', // 绑定到touser应用
        'abce'  => 'toadmin', // 绑定到toadmin应用
    ]
];
