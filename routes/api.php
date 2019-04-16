<?php

use Illuminate\Http\Request;


$api = app('Dingo\Api\Routing\Router'); //用於註冊dingo路由

$api->version('v1', function($api) {  //定義第一版本
    $api->get('/hi', function() {      //定義當路由為http://laravel.test/api/hi時執行
        return response('this is version v1');
    });
});

$api->version('v1', [
    'namespace' => 'App\Http\Controllers\Api',    //我們增加了一個參數namespace，使v1版本的路由都會指向App\Http\Controllers\Api，方便我們書寫路由。
    'middleware' => ['serializer:array','bindings']
], function($api) {
    $api->group([  //限制API調用次數
        'middleware' => 'api.throttle',
        'limit' => config('api.rate_limits.sign.limit'),  //數值寫於config/api裡面
        'expires' => config('api.rate_limits.sign.expires'),
        ] , function($api) {
    // 短信验证码
    $api->post('verificationCodes', 'VerificationCodesController@store') //若沒設定NAMESPACE的話這裡要App\Http\Controllers\Api\VerificationCodesController@store
        ->name('api.verificationCodes.store');
    // 用户注册
    $api->post('users', 'UsersController@store')
        ->name('api.users.store');
    // 图片验证码
    $api->post('captchas', 'CaptchasController@store')
        ->name('api.captchas.store');
    // 第三方登录    
     $api->post('socials/{social_type}/authorizations', 'AuthorizationsController@socialStore')
        ->name('api.socials.authorizations.store');
    // 登录
     $api->post('authorizations', 'AuthorizationsController@store')
        ->name('api.authorizations.store');   
    // 刷新token
    $api->put('authorizations/current', 'AuthorizationsController@update')
    ->name('api.authorizations.update');
    // 删除token
    $api->delete('authorizations/current', 'AuthorizationsController@destroy')
    ->name('api.authorizations.destroy');  
    
    $api->group([
        'middleware' => 'api.throttle',
        'limit' => config('api.rate_limits.access.limit'),
        'expires' => config('api.rate_limits.access.expires'),
    ], function ($api) {
        // 游客可以访问的接口

        // 需要 token 验证的接口
        $api->group(['middleware' => 'api.auth'], function($api) {
        // 当前登录用户信息
        $api->get('user', 'UsersController@me')
            ->name('api.user.show');
        // 编辑登录用户信息
        $api->patch('user', 'UsersController@update')
            ->name('api.user.update');
        // 图片资源
        $api->post('images', 'ImagesController@store')
            ->name('api.images.store');
        // 发布话题
        $api->post('topics', 'TopicsController@store')
        ->name('api.topics.store');
        $api->patch('topics/{topic}', 'TopicsController@update')
        ->name('api.topics.update');
        $api->delete('topics/{topic}', 'TopicsController@destroy')
        ->name('api.topics.destroy');
        });
    });   
        // 游客可以访问的接口
        $api->get('categories', 'CategoriesController@index')
            ->name('api.categories.index');

        // 需要 token 验证的接口





    });
});
