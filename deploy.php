<?php
/*
 * This file has been generated automatically.
 * Please change the configuration for correct use deploy.
 */

require 'recipe/composer.php';

// Set configurations
set('repository', 'git@github.com:AmsterdamPHP/console.git');
set('shared_files', ['.env']);
set('shared_dirs', []);
set('writable_dirs', []);

// Configure servers
server('production', 'amsterdamphp.nl')
    ->user('phpamst01')
    ->identityFile()
    ->env('deploy_path', '/data/www/console');

// Tasks
task('cron:sync', function () {})->desc("Sync CRON settings");

after('success', 'cron:sync');
after('deploy:update_code', 'deploy:shared');
