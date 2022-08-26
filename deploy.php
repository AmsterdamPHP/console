<?php
namespace Deployer;

require 'recipe/composer.php';

// Set configurations
set('repository', 'git@github.com:AmsterdamPHP/console.git');
set('shared_files', ['.env']);
set('shared_dirs', []);
set('writable_dirs', []);

// Configure servers
server('production', 'amsterdamphp.nl')
    ->user('phpamst01')
    ->identityFile(null, '~/.ssh/id_rsa_0049b0d3413599f2a7d1024602070609')
    ->env('deploy_path', '/data/www/console');

after('deploy:update_code', 'deploy:shared');
