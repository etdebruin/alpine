# Alpine

In it's infancy, but it's a fun little framework for developers who like to work with databases and modelling but don't enjoy the view part so much.

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
* Copy the following files into your `template` folder:

```
cp myblog/alpine/lib/skeleton/alpine_head.php myblog/template/
cp myblog/alpine/lib/skeleton/alpine_foot.php myblog/template/
```

Your app should be ready to go and by that I mean, you should see a "Welcome to Alpine!"

## Creating your first model

This is where the fun begins.  Essentially, Alpine is tightly wrapped around your database schema so this is where we will begin.  We'll see if we can continue with the `myblog` app.

* Open your favorite SQL editor and make sure you run the following sequel:

```sql
CREATE TABLE `post` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

* You guessed it, we created a table for our posts
* Now in our `myblog/model` folder, let's create `post.php`, all it needs to look like is this:

```php
<?

  class Post extends Alpine {

  }

?>
```

* We now have our first basic model, let's create our first view
* Create a file in your `myblog/template` called `post.php` and make it look something like this:

```html
<h1>((title))</h1>
<p>((content))</p>
```
* Let's create another file in `myblog/template` and call it `post_add.php` and make it look like this:

```html
<h2>Add a post</h2>
<p>Title: ((edit_title))</p>
<p>Content: ((edit_content))</p>
```

* Now go to your web address, say `http://myblog.dev/post/add`

Do you see any magic?
