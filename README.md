user
======

四川大学飞扬俱乐部 用户系统。

0. 此版本仅实现了单点登录 API 及登录页（单点登录 API 参考 [api.md](api.md)）。

1. 使用了 [Flight](http://flightphp.com) 框架及 [Medoo](https://medoo.in/) 数据库框架。

2. 权限管理为 RBAC 模型，单点登录（SSO）是 Token-based（没有上 JWT）。

3. 集成了阿里云短信和极验的 SDK。

4. API 支持手机验证码登录，网页版暂无。

5. 包含一些未使用的模块（如 Elasticsearch，微信）。

6. 集成了会员表，但无会员管理。
