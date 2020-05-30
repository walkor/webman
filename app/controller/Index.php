<?php
namespace app\controller;

use support\Request;
use JasonGrimes\Paginator;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Formatter\IntlFormatter;

class User
{
    /**
     * 用户列表
     */
    public function get(Request $request)
    {
        return 'ok';
        locale('fr');
        return  response(trans(
            'apple',
            ['%count%' => 1]
        ));
    }

}
