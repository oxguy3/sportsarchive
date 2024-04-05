# sportsarchive
Source code to sportsarchive.net

## Setup

### Requirements
* PHP 8.1
	* Extensions needed: gd, intl, mbstring, pgsql, xml
	* Ubuntu: `apt install php8.1 php8.1-gd php8.1-intl php8.1-mbstring php8.1-pgsql php8.1-xml`
* [Composer](https://getcomposer.org/download/)
	* Ideally should be named `composer` and located somewhere in your PATH (i.e. `/usr/local/bin/composer`)
* [Node.js](https://nodejs.org/en/download/)
  * On Ubuntu, I installed the LTS release from [NodeSource](https://github.com/nodesource/distributions#readme) to get a new enough version.
* NPM (sometimes included with Node.js)
* Yarn (`npm install -g yarn`)
* poppler-utils (`apt install poppler-utils`)
* [Symfony binary](https://symfony.com/download) (only needed for local dev server)
* PostgreSQL server

### Environment
On my local machine, I have a `.env.local` file in the repository that looks like this:
```
S3_STORAGE_KEY="key"
S3_STORAGE_SECRET="secret"
S3_PREFIX=dev-yournamehere/
```

### Database
You need a local Postgres server. If your server is running at 127.0.0.1:5432 with username/password is postgres/postgres, you don't need to set the database details. Otherwise, you'll need to add this line to your `.env.local` file:
```
DATABASE_URL="postgresql://user:pass@127.0.0.1:5432/sportsarchive?serverVersion=13&charset=utf8"
```
You'll then need to set up the database with these commands:
* `php bin/console doctrine:database:create` (creates the database)
* `php bin/console doctrine:migrations:migrate` (apply database schema)

### Messenger
This project uses a constantly running worker to perform background tasks (namely, deriving assets from PDFs so that the BookReader plugin can work). On initial install, you'll need to set up the database table that task messages are stored in with this command: `php bin/console messenger:setup-transports`.

You'll then need to run one or more workers via the `messenger:consume` command. An easy way to do this is with systemd; here is my config file, which I have installed at `/etc/systemd/system/sportsarchive-messenger@.service`:
```
[Unit]
Description=Symfony messenger-consume %i

[Service]
ExecStart=php /opt/sportsarchive/bin/console messenger:consume async --time-limit=3600
Restart=always
RestartSec=30

[Install]
WantedBy=default.target
```
You can then spin up any number of workers with `systemctl start sportsarchive-messenger@1`. Change 1 to 2, 3, 4, etc to make more workers (currently, I use 5 workers on the production server). You can configure systemd to automatically start the service with `systemctl enable sportsarchive-messenger@1`.

### Commands
Run these commands for initial install, and whenever you pull down a new version of the code:
* `composer install` (installs backend components)
* `yarn install` (installs frontend components)
* `php bin/console doctrine:migrations:migrate` (update database schema)

These are the primary commands for working on the site locally:

* `symfony server:start` – run a local web server
* `yarn encore dev --watch` – automatically rebuild JS/CSS assets
* `bin/fantasticon.sh` – rebuild the icon font

## Building
Here is the script used to build the site in production. The script creates a copy of the project (as `sportsarchive-next/`), updates that copy, then moves it back to `sportsarchive/`. The previous un-updated copy of the project is moved to `sportsarchive-prev/`, and is not deleted until the next time that the build script is run (allowing it to be restored in case of a failed deploy).
```
#!/usr/bin/env bash
rm -rf sportsarchive-prev/
cp -r sportsarchive/ sportsarchive-next/
cd sportsarchive-next/
git pull
sudo chmod -R 777 var/cache/ var/log/
php /usr/local/bin/composer install
yarn install
bin/fantasticon.sh
yarn encore production
sudo chown -R www-data:www-data var/cache/ var/log/
cd ..
mv sportsarchive/ sportsarchive-prev/
mv sportsarchive-next/ sportsarchive/
systemctl daemon-reload
systemctl restart sportsarchive-messenger@*.service
```

On the production server, I have a `.env.local` in the root folder of the project that looks like this:
```
APP_ENV=prod
APP_SECRET=secret
DATABASE_URL="postgresql://user:pass@127.0.0.1:5432/sportsarchive?serverVersion=13&charset=utf8"
S3_STORAGE_KEY="key"
S3_STORAGE_SECRET="secret"
S3_PREFIX=prod/
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

	php_value upload_max_filesize 1000M
	php_value post_max_size 1000M

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

> Copyright 2021–2024 Hayden Schiff
>
> Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
>
> The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
>
> THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

### Credits
* Logo derived from [Font Awesome](https://fontawesome.com/) icons: [futbol solid](https://fontawesome.com/icons/futbol?style=solid) and [box solid](https://fontawesome.com/icons/box?style=solid) (CC BY 4.0)
* [Archive photo](https://pixabay.com/photos/files-ddr-archive-1633406/) by [Chris Stermitz](https://pixabay.com/users/creativesignature-1460253/) from Pixabay ([license](https://pixabay.com/service/license/))
* Icons
  * Most icons come from [Font Awesome 5](https://fontawesome.com/) (CC BY 4.0)
  * Rugby, cricket icons come from [Material Icons](https://fonts.google.com/icons) by Google (Apache License 2.0)
  * [Lacrosse](https://thenounproject.com/term/lacrosse/2174330/) by Deemak Daksina from the Noun Project (CC BY)
  * [tennis racquet](https://thenounproject.com/term/tennis-racquet/483296/) by Creative Stall from the Noun Project (CC BY)
  * [Curling stones](https://thenounproject.com/term/curling-stones/1545123/) by icon 54 from the Noun Project (CC BY)
  * Flying disc icon by me (CC BY 4.0)
