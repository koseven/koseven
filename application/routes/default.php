<?php
Route::set('default', '(<controller>(/<action>(/<id>)))')
	->defaults([
		'controller' => 'welcome',
		'action'     => 'index',
	]);
