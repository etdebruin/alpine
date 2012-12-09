# Alpine

## Installing

These steps will walk you through creating a new app using Alpine.

* Create your app folder, i.e. `myblog`
* Change directory into `myblog` and `git clone https://github.com/etdebruin/alpine.git`
* Make sure you set up your Apache virtual server with `myblog` as your document root
* Make sure you are able to do mod rewrites.
* In your `myblog` folder, create a .htaccess file that looks like this:

  RewriteEngine On
  RewriteBase /
  RewriteRule ^(.*)$ alpine/dispatch.php?path=$1 [QSA,NC,L]


Your app should be ready to go.
