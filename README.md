

### 系统框架
    Lumen 8.1.0
    https://lumen.laravel.com/docs/8.x
### 系统依赖
    os => centos 
    nginx => 1.18.0 及以上
    php => 7.3.0 以上
    mysql => 5.6 或以上
    supervisor 
    redis
    oss

### 代码目录结构
     | app
        | Console 存放 Cli 程序脚本 一般用于 Crontab 脚本和 一次性脚本
        | Events 系统事件
        | Exceptions 系统异常
        | Http
            | Bll 系统逻辑层代码目录
            | Controllers 控制器层代码目录
            | Middleware 系统中间件目录
        | Jobs Queue 目录（redis queue）
        | Listeners 事件监听者目录
        | Models  数据 Model 层代码目录
        | Providers  服务提供者目录
     | bootstrap  启动和自动载入配置
     | config   各种配置
     | database  数据库初始化，迁移等
     | public  对外访问根目录 /public 此目录内对外网可见
     | resources 资源目录
     | routes 路由表
     | storage 各种临时存储文件以及文件默认上传地址
     | tests 单元测试demo
     | vendor 系统核心框架以及插件源码
     | .env.example 系统环境配置的 demo 版
     | .env 真正的环境配置文件，一般只在服务器上存在
>##### App目录
>     app 目录包含了应用的核心代码，注意不是框架的核心代码，框架的核心代码在 /vendor/laravel/framework 里面，此外你为应用编写的代码绝大多数也会放到这里；
>##### Bootstrap目录
>     bootstrap 目录包含了app.php，用于框架的启动和自动载入配置；
>##### Config目录
>     config 目录包含了应用所有的配置文件，建议通读一遍这些配置文件以便熟悉 Lumen 所有默认配置项；
>##### Database目录
>     database 目录包含了数据库迁移文件及填充文件，如果有使用 SQLite 的话，你还可以将其作为 SQLite 数据库存放目录；
>##### Public目录
>     public 目录包含了应用入口文件 index.php 和前端资源文件（图片、JavaScript、CSS等），该目录也是 Apache 或 Nginx 等 Web 服务器所指向的应用根目录，这样做的好处是隔离了应用核心文件直接暴露于 Web 根目录之下；
>##### Resources目录
>     resources 目录包含了应用视图文件和未编译的原生前端资源文件（LESS、SASS、JavaScript），以及本地化语言文件；
>##### Routes目录
>     api.php 文件包含的路由位于 api 中间件组约束之内，支持频率限制功能，这些路由是无状态的，所以请求通过这些路由进入应用需要通过 token 进行认证并且不能访问 Session 状态。
>##### Storage目录
>     storage 目录包含了编译后的 Blade 模板、基于文件的 Session、文件缓存，以及其它由框架生成的文件，该目录被细分为成 app、framework 和 logs 子目录，app 目录用于存放应用生成的文件，framework 目录用于存放框架生成的文件和缓存，最后，logs 目录存放的是应用的日志文件。
>
>     storage/app/public 目录用于存储用户生成的文件，比如可以被公开访问的用户头像，要达到被 Web 用户访问的目的，你还需要在 public （应用根目录下的 public 目录）目录下生成一个软连接 storage 指向这个目录。你可以通过 php artisan storage:link 命令生成这个软链接。
>##### Tests目录
>     tests 目录包含自动化测试文件，其中默认已经提供了一个开箱即用的PHPUnit 示例；每一个测试类都要以 Test 开头，你可以通过 phpunit 或 php vendor/bin/phpunit 命令来运行测试。
>##### Vendor目录
>     vendor 目录包含了应用所有通过 Composer 加载的依赖。

### env 配置 demo 版

```
    APP_NAME=lumen-api
    APP_ENV=dev
    APP_DEBUG=true
    APP_KEY=1ca892e44cfd626c9ed16658d7ac3d36
    APP_TIMEZONE=PRC
    LOG_CHANNEL=daily
    PATH_STORAGE=/opt/htdocs/logs/app/www
    LOG_SLACK_WEBHOOK_URL=

    #数据库配置 根据实际情况修改
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=test
    DB_USERNAME=root
    DB_PASSWORD=123456

    #缓存配置
    CACHE_DRIVER=redis
    QUEUE_DRIVER=redis
    REDIS_HOST=127.0.0.1
    REDIS_PASSWORD=null
    REDIS_PORT=6379

    JWT_SECRET=9fWhfueBJERsmA49S4BTQyvLbDC8rXNT
    JWT_TTL=21600

    #BASE_CONFIG
    DOMAIN_FORCE_HTTPS={}
    DOMAIN_FORCE_HTTP={}

```



### 框架接口规范：

```
{
    "code": 0,   //接口返回code   0为成功，  其他全部是失败或者异常
    "msg": "成功",   //接口范围描述信息， code不为0是  msg内容是错误信息描述
    "data": {   //接口具体返回内容  根据不同接口 有不同返回结构

    }
}
```



### 框架错误信息：
    错误信息配置在   /config/error.php 中 注意code 分段
    系统中任何地方抛错 可以用
    `throw new BusinessException('INVALID_ARGUMENT');`
    系统会自动捕捉到 然后转换成标准错误信息输出，  但是其中的 错误code要在 error.php 定义清楚

```
public function demo()
    {
        //抛异常报错
//        throw new BusinessException('INVALID_ARGUMENT');
        //直接返回报错
//        return self::error('INVALID_ARGUMENT');
        //成功 返回数据
        return self::success([]);

    }
```

### 框架注意事项
    路由层 
    控制器层 
        命名规则：[Base]Controller.php，
        功能： 一般只负责参数接收，校验，结果返回，部分异常捕捉，不宜放太重的逻辑，
    业务逻辑层 Bll层
         命名规则  [name]Bll.php
         大部分业务逻辑应该写在这一层，这里可以做一些抽象，封装，可以对控制器做公用输出
    Model 层
        数据定义，模型定义，部分数据操作函数等
    Console
        如果有需要cli 执行的Corntab  可以放进来执行 ，或者一次性执行脚本

###  数据库设计规范

 ```
        1.库名、表名、字段名必须使用小写字母，“_”分割。如sys_menus
        2.库名、表名、字段名见名知意,建议使用名词而不是动词。如用户评论可用表名user_comment
        3.库名、表名、字段名必须不超过12个字符。如注册来源reg_source
        4.存储精确浮点数必须使用DECIMAL替代FLOAT和DOUBLE。
        5.使用短数据类型，比如取值范围为0-80时，使用TINYINT UNSIGNED。
        6.非唯一索引必须按照“idx_字段名称_字段名称[_字段名]”进行命名。
        7.唯一索引必须按照“uniq_字段名称_字段名称[_字段名]”进行命名。
        8.索引名称必须使用小写,单张表的索引数量控制在5个以内。
        9.不建议使用%前缀模糊查询，索引使用不到，例如LIKE “%name”。
        10.合理创建联合索引（避免冗余），(a,b,c) 相当于 (a) 、(a,b) 、(a,b,c)。
        11.每张表数据量建议控制在5000w以下。
        12.分表如果使用hash算法进行散表，表名后缀使用16进制，比如user_ff
        13.字段类型尽量不要使用TEXT、BLOB类型。建议字段定义为NOT NULL。
        14.表字符集选择utf8mb4,支持特殊表情等字符集
        15.查询语句不建议使用SELECT *,按需获取。
        16.字段状态类型定义默认值从1起步，不要使用0和1来表示否或者是，例如1-是 2-否
        17.字段存在多个枚举类型时候，建议值间隔放大，如10，20，30
        18.重要业务场景不要使用自增长唯一键作为业务使用，如账户表gc_account中account_id，单独一列提供业务使用。
        
        数据表设计 demo：
        CREATE TABLE `admin_users` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `name` varchar(100) NOT NULL DEFAULT '' COMMENT '管理员姓名',
          `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '管理员手机号',
          `password` varchar(200) NOT NULL DEFAULT '' COMMENT '密码',
          `token` varchar(400) NOT NULL DEFAULT '' COMMENT 'token',
          `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
          `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
          `deleted_at` datetime NULL DEFAULT NULL COMMENT '删除时间',
          PRIMARY KEY (`id`),
          UNIQUE KEY `unq_mobile` (`mobile`)
        ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='管理员用户表'
```


```
        注意 以下字段 每个表都必须存在(特殊情况除外)，最好是直接复用以下语句，系统中已经针对字段有逻辑处理
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,  //自增ID
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
        `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
        `deleted_at` datetime NULL DEFAULT NULL COMMENT '删除时间', //此字段用于软删标记
```

    数据库字段 全部小写字母，不同字母之间用下划线分隔
    表和字段 必须要有中文备注，如果有状态、枚举等标记字段  把每个枚举的意义也列在字段备注中

### Lumen控制器接收参数校验规则
```

    $rules = [
        'mobile' => 'required|numeric|min:11',
        'client_type' => 'required|integer|in:1,2,3,4',
        'password' => 'required_if:login_type,2|string',
        'captcha' => 'required_if:login_type,1|string|min:4|max:4',
        'login_type' => 'required|integer|in:1,2',
        'interviewer' => 'required|integer',
        'name' => 'required|string|max:50',
    ];
    
    $message = [
        'name.max' => '姓名最大50个字符',
        'captcha.required_if' => '验证码不能为空',
        'password.required_if' => '密码不能为空'
    ];
    
    $this->validate($request, $rules, $message);
```
