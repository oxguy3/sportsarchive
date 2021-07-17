# sportsarchive
Source code to sportsarchive.net

## Setup

### Requirements
* PHP 7.4+ (not PHP 8)
* [Composer](https://getcomposer.org/download/)
* [Node.js](https://nodejs.org/en/download/)
  * On Ubuntu, I installed with [NodeSource](https://github.com/nodesource/distributions#readme) to get a new enough version.
* NPM (sometimes included with Node.js)
* Yarn (`npm install -g yarn`)
* [Symfony binary](https://symfony.com/download) (only needed for local dev server)

### Environment
On my local machine, I have a `.env.local` file in the repository that looks like this:
```
S3_STORAGE_KEY="key"
S3_STORAGE_SECRET="secret"
S3_HEADSHOTS_PREFIX=dev-yournamehere/
S3_DOCUMENTS_PREFIX=dev-yournamehere/
```

### Commands
Run these commands for initial install:
* `composer install`
* `yarn install`

These are the primary commands for working on the site locally:

* `symfony server:start` – run a local web server
* `yarn encore dev --watch` – automatically rebuild JS/CSS assets
* `bin/fantasticon.sh` – rebuild the icon font

## Building
Here is the script used to build the site in production:
```
#!/usr/bin/env bash
cd sportsarchive/
git pull
sudo chmod -R 777 var/cache/ var/log/
php7.4 /usr/local/bin/composer install
yarn install
bin/fantasticon.sh
yarn encore production
sudo chown -R www-data:www-data var/cache/ var/log/
```

On the production server, I have a `.env.local` in the root folder of the project that looks like this:
```
APP_ENV=prod
APP_SECRET=secret
DATABASE_URL="postgresql://user:pass@127.0.0.1:5432/sportsarchive?serverVersion=13&charset=utf8"
S3_STORAGE_KEY="key"
S3_STORAGE_SECRET="secret"
S3_HEADSHOTS_PREFIX=prod/
S3_DOCUMENTS_PREFIX=prod/
```

I use Apache as my web server with the following config:
```
<Macro sportsarchive>
	DocumentRoot /opt/sportsarchive/public
	<Directory "/opt/sportsarchive/public">
		Options -Indexes +MultiViews
		AllowOverride None
		Require all granted

		FallbackResource /index.php
	</Directory>

	php_value upload_max_filesize 750M
	php_value post_max_size 750M

	Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'sha256-UExc6/nGJKLS+BrRNn5BIQLwD+lK1rTxOKFIGkyTD0c=' https://www.googletagmanager.com https://www.google-analytics.com https://ssl.google-analytics.com; img-src 'self' data: https://nyc3.digitaloceanspaces.com https://imgproxy.sportsarchive.net www.googletagmanager.com https://www.google-analytics.com; style-src 'self' 'unsafe-inline'; connect-src 'self' https://www.google-analytics.com; report-uri https://d23266040c21bd2a00e0e190e8a04a64.report-uri.com/r/d/csp/enforce"
	Header always set Strict-Transport-Security "max-age=31536000"
	Header always set X-XSS-Protection "1; mode=block"
	Header always set X-Frame-Options "SAMEORIGIN"
	Header always set X-Content-Type-Options "nosniff"
	Header unset Server
	ServerSignature Off

	RewriteEngine on
</Macro>
<VirtualHost *:80>
	ServerName www.sportsarchive.net
	ServerAlias sportsarchive.net

	Use sportsarchive

	RewriteCond %{SERVER_NAME} =www.sportsarchive.net [OR]
	RewriteCond %{SERVER_NAME} =sportsarchive.net
	RewriteRule ^ https://www.sportsarchive.net%{REQUEST_URI} [END,NE,R=permanent]
</VirtualHost>
<VirtualHost *:443>
	ServerName www.sportsarchive.net
	ServerAlias sportsarchive.net

	Use sportsarchive

        RewriteCond %{SERVER_NAME} =sportsarchive.net
        RewriteRule ^ https://www.sportsarchive.net%{REQUEST_URI} [END,NE,R=permanent]

	Include /etc/letsencrypt/options-ssl-apache.conf
	SSLCertificateFile /etc/letsencrypt/live/sportsarchive.net/fullchain.pem
	SSLCertificateKeyFile /etc/letsencrypt/live/sportsarchive.net/privkey.pem
</VirtualHost>
```

## License
The code in this repository is licensed under the MIT License:

> Copyright 2021 Hayden Schiff
>
> Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
>
> The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
>
> THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

### Credits
* Logo derived from [Font Awesome](https://fontawesome.com/) icons ([futbol solid](https://fontawesome.com/icons/futbol?style=solid) and [box solid](https://fontawesome.com/icons/box?style=solid))
* Icons
  * Most icons come from [Font Awesome 5](https://fontawesome.com/) (CC BY 4.0)
  * Rugby, cricket icons come from [Material Icons](https://fonts.google.com/icons) by Google (Apache License 2.0)
  * [Lacrosse](https://thenounproject.com/term/lacrosse/2174330/) by Deemak Daksina from the Noun Project (CC BY)
  * [tennis racquet](https://thenounproject.com/term/tennis-racquet/483296/) by Creative Stall from the Noun Project (CC BY)
  * [Curling stones](https://thenounproject.com/term/curling-stones/1545123/) by icon 54 from the Noun Project (CC BY)
  * Flying disc icon by me (CC BY 4.0)
