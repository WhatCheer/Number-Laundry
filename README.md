# What's Number Laundry?

Number Laundry is a tiny API that accepts phone numbers and returns helpful information as json.

It's up and running with free access at http://numberlaundry.whatcheer.com/ but you can run your own copy with this source.

# Requirements

* PHP 5.3+
* A PDO compatible database

# How do I get set up?

Install it!

## 1) Database

Number Laundry uses the excellent [Paris](https://github.com/j4mie/paris) ORM which uses [PDO](http://php.net/PDO), so a variety of databases should work.  Keep in mind you will be running a table with 10k+ rows, so choose carefully.  We use MySQL.

So, fire up your database and load <tt>db/schema.sql</tt>.  Now, you have a choice to make.  There is an existing db import at <tt>db/insert-2011-08-18.sql</tt> that you can load up.  If that's too old you can use the script at <tt>generate.py</tt> to transform the JSON from Twilio into an insert statement (that will need tweaking).  Either choice works, just have to pick one.

## 2) Files

Upload the whole shebang to your web host (you can skip the <tt>db</tt> directory).

## 3) Config

Now, move <tt>config.php.example</tt> to <tt>config.php</tt> and edit it to have the correct MySQL settings.

Additionally, move <tt>example.htaccess</tt> to <tt>.htaccess</tt> and make any necessary changes there as well.

## 4) Try it out!

# License

Number Laundry is released under the MIT License (See LICENSE)

# About Us

## Why did you make this?

We needed clean phone numbers and [Twilio](http://twilio.com/) international rates lookups for a project of ours called [Bumble](http://bumblephone.com). We built something quickly and realized it was pretty handy. So, we decided it would be nice to provide this service to anyone else who needed it.

## Who are you?

We're [What Cheer](http://whatcheer.com), a web design and development studio located in [Omaha, Nebraska](http://iliveinomaha.com/). We make fancy websites.
