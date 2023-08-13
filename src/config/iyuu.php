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
];
