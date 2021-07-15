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
  * Flying disc icon by me (CC BY 4.0)
