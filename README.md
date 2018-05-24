EMLauncher
==========

## Setup

EC2のAmazonLinuxでEMLauncherを動かす手順です。

### 1. Launch EC2 instance

インスタンスを立ち上げたらセキュリティグループの設定でHTTP(80)を許可しておきます。

t1.microの場合はメモリが足りなくなることがあるので、swapファイルを用意します。
```BASH
sudo dd if=/dev/zero of=/swapfile bs=1M count=1024
sudo mkswap /swapfile
sudo swapon /swapfile
sudo sh -c "echo '/swapfile swap swap defaults 0 0' >> /etc/fstab"
```

### 2. Install required packages

```BASH
sudo yum install php php-pdo php-mysql httpd mysql55-server memcached php-pecl-memcache php-mbstring php-pecl-imagick git
```

### 3. Deploy codes

```BASH
git clone https://github.com/KLab/emlauncher.git
cd emlauncher
git submodule init
git submodule update
```
Apacheがファイルにアクセスできるようにパーミッションを調整してください。

### 4. Apache setup

/etc/httpd/conf/httpd.confを編集します。
```XML
DocumentRoot "/path/to/emlauncher/web"
SetEnv MFW_ENV 'ec2'
<Directory "/path/to/emlauncher/web">
  AllowOverride All
  ...略...
</Directory>
```

```BASH
sudo /etc/init.d/httpd start
sudo chkconfig httpd on
```


### 5. Database setup

```BASH
sudo /etc/init.d/mysqld start
sudo chkconfig mysqld on
```

DBのユーザ名、パスワードを書いたファイルを作成します。

例:
```
echo 'emlauncher:password' > $HOME/dbauth
```

data/sql/database.sqlのパスワードを合わせて修正し、MySQLに流します。
```BASH
mysql -uroot < /path/to/emlauncher/data/sql/database.sql
mysql -uroot emlauncher < /path/to/emlauncher/data/sql/tables.sql
```

### 6. Memcache setup

```BASH
sudo /etc/init.d/memcached start
sudo chkconfig memcached on
```

### 7. Configuration

#### mfw_serverevn_config.php
``config/mfw_serverenv_config_sample.php``をコピーし、``$serverenv_config['ec2']['database']['authfile']``を
5で作成したdbauthファイルのパスに書き換えます。

#### emlauncher_config.php
``config/emlauncher_config_sample.php``をコピーし、自身の環境に合わせて書き換えます。

S3のbucket名に指定するbucketは予め作成しておきます。

### 8. Complete

ブラウザでインスタンスにHTTPでアクセスします。
EMLauncherのログインページが表示されたら完了です。

## このForkでの新機能
EMlauncherにユーザー追加と一覧、削除の機能を追加しました。
~~とくにメニューとかに追加はしていないので、一旦トップページからログインした後に直接URLを入力して機能を呼び出してください~~（管理者権限が付与されているユーザにAdminメニューが表示されます）。

※ユーザーの追加や一覧、削除には管理者権限が必要です。
既存のユーザーに管理者権限を付与する機能は未実装なので、以下のようなSQLクエリーで``user_pass``テーブルの``as_admin``を非0値としてください。
```
update user_pass set as_admin=1 where mail='既存ユーザーのEmail';
```

### 新規ユーザーの登録
```
https://<EMlauncherのドメイン>/account/new
```
新規ユーザーの登録の際にas_administratorにチェックすると管理者権限を付与します。
新規ユーザーの登録の際にpasswordとconfirm_passwordを空欄とすると、新規に追加したユーザーにパスワードリセットのメールが送信されます。

### 既存ユーザーの一覧と削除
```
https://<EMlauncherのドメイン>/account/userlist
```

### iOSの「Over-the-Airによるプロファイル配布サービス」機能を利用したiOSテスト端末のUDID自動収集機能実装
RubyのWEBRickを使ったSECPサーバーを利用したEMlauncher向けProfile Serviceサーバーを用意することでテスト用に利用するiOS端末のUDIDを収集し、EMlauncherにアップロードされているAdHocテスト用アプリケーションのmobileprovisionに端末のUDIDが含まれていない灰にはテストアプリケーションの所有者に対してメールで対象端末のUDIDの追加をリクエストできるように改良を加えました。
この機能は以下の様にemlauncher_config.phpのenable_request_ios_udidを有効(true)にして、同じくemlauncher_config.phpのscep_hostにSECPサービスを稼働させたサーバーのホスト名を設定することで有効化されます。

```emlauncher_config.php
                /**
                 * trueならiOSからのアクセスの場合は端末のUUIDを送信させて
                 * 端末のUDIDがDBのテーブルに登録されているかを確認する。
                 * ※端末からのUDIDの取得にはProfile Serviceの稼働が必要
                 */
                'enable_request_ios_udid' => true,
                'scep_domain' => 'scep.example.com',
```

#### 参考資料 (AppleのOTA によるプロファイル配布サービスの資料)
<https://developer.apple.com/library/content/documentation/NetworkingInternet/Conceptual/iPhoneOTAConfiguration/profile-service/profile-service.html>

<https://developer.apple.com/jp/documentation/iPhoneOTAConfiguration.pdf>

#### EMlauncher向けSECPサービス(Rubyによる実装)のサンプル
<https://github.com/kazuhidet/EMlauncherProfileService>

### AWS S3以外のS3互換ストレージでの利用

AWS S3以外のS3互換オブジェクトストレージでの利用が可能なように、設定を追懐しています。
`config/emlauncher_config.php` の `$emlauncher_config['ec2']['aws']['base_url']` にS3互換オブジェクトストレージのエンドポイントURLを記載してください。

#### 参考

<https://github.com/purintai/emlauncher/commit/ef4f8186b6a6358c613898ebe2bc875b177ffedc>
