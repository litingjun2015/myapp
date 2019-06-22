

#

##
export GOOGLE_APPLICATION_CREDENTIALS="/home/vagrant/Code/myapp/config/starlit-granite-20190622-118100326d54.json"

##
php artisan make:controller TestController


## The routes/api.php file:

Route::get('test', 'TestController@index');