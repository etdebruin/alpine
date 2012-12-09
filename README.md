# Alpine

## Installing

These steps will walk you through creating a new app using Alpine.  One day it will all be taken care of by a script I'm sure.

* Create your app folder, i.e. `myblog`
* Change directory into `myblog` and `git clone https://github.com/etdebruin/alpine.git`
* Make sure you set up your Apache virtual server with `myblog` as your document root
* Make sure you are able to do mod rewrites.
* In your `myblog` folder, create a .htaccess file that looks like this:

```
    RewriteEngine On
    RewriteBase /
    RewriteRule ^(.*)$ alpine/dispatch.php?path=$1 [QSA,NC,L]
```

* Now, create a `conf` folder in `myblog` so you have `myblog/conf`
* Create a `local.php` file and this is where you set up your database connection

```php
    <?

      Config::set('db', 'myblog');
      Config::set('dbUser', 'mybloguser');
      Config::set('dbPassword', 'myblogpassword');

    ?>
```

* Create a `model` folder in `myblog` so you have `myblog/model`
* Create a `template` folder in `myblog` so you have `myblog/template`
* Inside the `template` folder create an `index.php` file which may be blank

Your app should be ready to go.
