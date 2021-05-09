<?php
/* -------------------------------------------------------------------------- */
/*                                  Utilities                                 */
/* -------------------------------------------------------------------------- */

Route::get('/clear-cache', function () {
    Artisan::call('view:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    return redirect()->to('/');
});

Route::get('/php-info', function () {
    return phpinfo();
});

/* -------------------------------------------------------------------------- */
/*                                    Route                                   */
/* -------------------------------------------------------------------------- */

Route::match(['GET', 'POST'], '/complain', 'Front\HomeController@complainStepOne')->name('complain');

Route::namespace ('Admin')->prefix('admin')->as('admin.')->group(function () {
    Route::group(['middleware' => 'auth:admin'], function () {
        Route::get('/home', 'AdminController@index')->name('home');

        Route::resources([
            'employees' => 'EmployeeController',
            'notices' => 'NoticeController',
            'projects' => 'ProjectController',
            'album' => 'GalleryController',
        ]);

        Route::resource('/publication', 'PublicationController', ['names' => [
            'index' => 'publication.index',
            'create' => 'publication.create',
            'store' => 'publication.store',
            'edit' => 'publication.edit',
            'update' => 'publication.update',
            'destroy' => 'publication.delete',
        ]]);

    });
});
