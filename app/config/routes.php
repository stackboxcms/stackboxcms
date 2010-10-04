<?php
$pageRouteItem = '/<*page>';

// User Login
$router->route('login', '/login')
	->defaults(array('module' => 'User_Session', 'action' => 'new', 'format' => 'html'))
	->post(array('action' => 'post'));
$router->route('logout', '/logout')
	->defaults(array('module' => 'User_Session', 'action' => 'delete', 'format' => 'html'));
	
// HTTP Errors
$router->route('http_error', '/error/<#errorCode>(.<:format>)')
	->defaults(array('module' => 'Error', 'action' => 'display', 'format' => 'html', 'page' => '/'));

// User reserved route
$router->route('user', '/user/<:action>(.<:format>)')
	->defaults(array('module' => 'User', 'format' => 'html'))
	->post(array('action' => 'post'))
	->put(array('action' => 'put'))
	->delete(array('action' => 'delete'));
	
// Admin reserved route
$router->route('admin', '/admin/<:action>(.<:format>)')
	->defaults(array('module' => 'Page_Admin', 'format' => 'html'));

/*
$router->route('module_item_action', $pageRouteItem . 'm,<:module_name>,<#module_id>/<#module_item>/<:module_action>(.<:format>)')
	->defaults(array('page' => '/', 'module' => 'Page', 'action' => 'index', 'format' => 'html'));
/*/

// Normal Routes
$router->route('module_item', $pageRouteItem . 'm,<:module_name>,<#module_id>/<#module_item>(/<:module_action>)(.<:format>)')
	->defaults(array('page' => '/', 'module' => 'Page', 'action' => 'index', 'module_action' => 'view', 'format' => 'html'))
	->get(array('module_action' => 'view'))
	->post(array('module_action' => 'post'))
	->put(array('module_action' => 'put'))
	->delete(array('module_action' => 'delete'));
	
$router->route('module', $pageRouteItem . 'm,<:module_name>,<#module_id>(/<:module_action>)(.<:format>)')
	->defaults(array('page' => '/', 'module' => 'Page', 'action' => 'index', 'module_action' => 'index', 'format' => 'html'))
	->get(array('module_action' => 'index'))
	->post(array('module_action' => 'post'))
	->put(array('module_action' => 'put'))
	->delete(array('module_action' => 'delete'));

$router->route('page_action', $pageRouteItem . '/<:action>\.<:format>')
	->defaults(array('page' => '/', 'module' => 'Page', 'format' => 'html'));

$router->route('index_action', '/<:action>\.<:format>')
	->defaults(array('page' => '/', 'module' => 'Page', 'format' => 'html'));

$router->route('page', $pageRouteItem)
	->defaults(array('page' => '/', 'module' => 'Page', 'action' => 'index', 'format' => 'html'))
	->post(array('action' => 'post'))
	->put(array('action' => 'put'))
	->delete(array('action' => 'delete'));
