# TyMessage
支持laravel5.1
send message with Tianyi open platform
用天翼开放平台发送短信

在config/app.php的
providers中加入
Yc13g\TyMessage\MessageServiceProvider::class
aliases中加入
'TyMessage' => Yc13g\TyMessage\Message::class
