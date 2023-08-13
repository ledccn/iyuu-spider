<?php
/**
 * IYUU配置
 */

return [
    'default' => [
        'host' => 'http://api.iyuu.cn',
        'endpoints' =>  [
            'bind'      => '/App.Api.Bind',
            'sites'     => '/App.Api.Sites',
            'findMovieSubject'  =>  '/App.Api.FindMovieSubject',
            'bindMovieSubject'  =>  '/App.Api.BindMovieSubject',
        ],
    ],
    'push' => [
        'host'   =>  'http://api.iyuu.cn:2120',
        'endpoints' =>  [
            'add' => '/spider/torrent/create',
            'find' => '/spider/torrent/find',
            'update' => '/api/update',
        ],
    ],
    /**
     * 谷歌验证码
     */
    'google' => [
        //默认的谷歌密钥
        'secret' => 'H6WSXWT22FE4DDDR',
        //场景列表
        'scenes' => [],
    ],
];
