Setup
=====

##Apache
Rewrites are required
```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ index.php/$1
```

##Documents
Create a folder called ```wiki``` in the project root and add your Markdown documents to it.

##Search
Create a folder called ```var``` in the project root and run ```php -f public/index.php -- reindex```.
