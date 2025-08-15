<?php

declare(strict_types=1);

require_once __DIR__ . '/../ctnext_user.module';

spl_autoload_register(function (string $class) {
  $file = __DIR__ . '/src/' . str_replace('\\', '/', $class) . '.php';
  if (file_exists($file)) {
    require $file;
  }
});
