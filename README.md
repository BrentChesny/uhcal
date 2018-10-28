# Custom UHasselt Calendars

This self-hosted tool allows you to create customized iCal files that contain exactly the courses that you want.

## Installation

Installing this tool on your web-server is fairly straightforward. Just follow the following steps:

- Clone the repository on your web-server.
- Point the root of your web-server to the `public` folder in the repository.
- Make sure you have [Composer](https://getcomposer.org) and [npm](https://www.npmjs.com)/[Yarn](https://yarnpkg.com/en/) installed.
- In the root of the repository, run the following commands:
  - `sudo chown -R :www-data .`
  - `composer install`
  - `cp .env.example .env`
  - `php artisan key:generate`
  - `touch database/database.sqlite`
  - `php artisan migrate`
  - `npm install` or `yarn install`
  - `npm run production` or `yarn run production`
