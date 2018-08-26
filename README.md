 ### 运行程序
 
 - `composer install`
 - 创建 config/accounts.php
 ```php
 <?php
 
 return [
     [
         'app_id' => 'fake_id',
         'secret' => 'fake_secret',
     ],
     [
         'app_id' => 'fake_id_2',
         'secret' => 'fake_secret_2',
     ]
 ];
 ```
  - `chmod 777 log`
  - `php bin/main`