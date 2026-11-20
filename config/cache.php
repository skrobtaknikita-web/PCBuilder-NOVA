<?php
return ['default'=>env('CACHE_STORE','file'),'stores'=>['array'=>['driver'=>'array'],'file'=>['driver'=>'file','path'=>storage_path('framework/cache/data')],'database'=>['driver'=>'database','connection'=>env('DB_CACHE_CONNECTION'),'table'=>'cache']],'prefix'=>env('CACHE_PREFIX','pc_configurator_cache_')];
