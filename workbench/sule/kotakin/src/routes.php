<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', 'Sule\Kotakin\Controllers\Home@index');

// Installer Routes
// -----------------------------------------------------------------------------
Route::get('/install', 'Sule\Kotakin\Controllers\Install@index');
Route::post('/install', array(
    'before' => 'csrf', 
    function() {
        $install = new Sule\Kotakin\Controllers\Install();
        return $install->index();
    }
));
Route::get('/install/db', 'Sule\Kotakin\Controllers\Install@db');
Route::post('/install/db', array(
    'before' => 'csrf', 
    function() {
        $install = new Sule\Kotakin\Controllers\Install();
        return $install->db();
    }
));
Route::get('/install/user', function() {
    $install = new Sule\Kotakin\Controllers\InstallFinishing(
        App::make('kotakin'), 
        App::make('sentry')
    );

    return $install->index();
});
Route::post('/install/user', array(
    'before' => 'csrf', 
    function() {
        $install = new Sule\Kotakin\Controllers\InstallFinishing(
            App::make('kotakin'), 
            App::make('sentry')
        );

        return $install->index();
    }
));
Route::get('/install/complete', function() {
    $install = new Sule\Kotakin\Controllers\InstallFinishing(
        App::make('kotakin'), 
        App::make('sentry')
    );

    return $install->complete();
});
// -----------------------------------------------------------------------------
// END Installer Routes

Route::get('blank', function(){
    return '';
});

Route::get('i10n', function(){
    $i10n = new Sule\Kotakin\Controllers\I10n(
        App::make('kotakin'), 
        App::make('sentry')
    );

    return $i10n->fire();
});

Route::get('i/{slug}', function($slug){
    $link = new Sule\Kotakin\Controllers\Link(
        App::make('kotakin'), 
        App::make('sentry')
    );

    return $link->index($slug);
})->where('slug', '[0-9a-z\-]+');
Route::post('i/{slug}', array(
    'before' => 'csrf', 
    function($slug) {
        $link = new Sule\Kotakin\Controllers\Link(
            App::make('kotakin'), 
            App::make('sentry')
        );

        return $link->index($slug);
    }
))->where('slug', '[0-9a-z\-]+');

// Admin Routes
// -----------------------------------------------------------------------------
Route::get('admin/login', array(
    'before' => 'admin.login', 
    function() {
        $admin = new Sule\Kotakin\Controllers\Admin\Account(
            App::make('kotakin'), 
            App::make('sentry')
        );

        return $admin->login();
    }
));
Route::post('admin/login', array(
    'before' => 'csrf', 
    function() {
        $admin = new Sule\Kotakin\Controllers\Admin\Account(
            App::make('kotakin'), 
            App::make('sentry')
        );

        return $admin->login();
    }
));
Route::get('admin/logout', function() {
    $admin = new Sule\Kotakin\Controllers\Admin\Account(
        App::make('kotakin'), 
        App::make('sentry')
    );

    return $admin->logout();
});

Route::get('admin/forgot', array(
    'before' => 'csrf', 
    'before' => 'admin.login', 
    function() {
        $admin = new Sule\Kotakin\Controllers\Admin\Account(
            App::make('kotakin'), 
            App::make('sentry')
        );

        return $admin->forgot();
    }
));
Route::post('admin/forgot', array(
    'before' => 'csrf', 
    'before' => 'admin.login', 
    function() {
        $admin = new Sule\Kotakin\Controllers\Admin\Account(
            App::make('kotakin'), 
            App::make('sentry')
        );

        return $admin->forgot();
    }
));

Route::get('admin/reset/{code}', array(
    'before' => 'admin.login', 
    function($code) {
        $admin = new Sule\Kotakin\Controllers\Admin\Account(
            App::make('kotakin'), 
            App::make('sentry')
        );

        return $admin->reset($code);
    }
))->where('code', '[0-9a-zA-Z]+');
Route::post('admin/reset/{code}', array(
    'before' => 'csrf', 
    'before' => 'admin.login', 
    function($code) {
        $admin = new Sule\Kotakin\Controllers\Admin\Account(
            App::make('kotakin'), 
            App::make('sentry')
        );

        return $admin->reset($code);
    }
))->where('code', '[0-9a-zA-Z]+');

Route::get('admin/reset_complete', array(
    'before' => 'admin.login', 
    function() {
        $admin = new Sule\Kotakin\Controllers\Admin\Account(
            App::make('kotakin'), 
            App::make('sentry')
        );

        return $admin->resetComplete();
    }
));

Route::group(array('before' => 'admin.auth'), function() {

    Route::get('admin', function() {
        $dashboard = new Sule\Kotakin\Controllers\Admin\Dashboard(
            App::make('kotakin'), 
            App::make('sentry'), 
            App::make('jitimage')
        );

        return $dashboard->index();
    });

    Route::get('admin/folder/{slug}', function($slug) {
        $dashboard = new Sule\Kotakin\Controllers\Admin\Dashboard(
            App::make('kotakin'), 
            App::make('sentry'), 
            App::make('jitimage')
        );

        return $dashboard->index($slug);
    })->where('slug', '[0-9a-zA-Z\-\/]+');

    Route::post('admin', array(
        'before' => 'csrf', 
        function() {
            $dashboard = new Sule\Kotakin\Controllers\Admin\Dashboard(
                App::make('kotakin'), 
                App::make('sentry'), 
                App::make('jitimage')
            );

            return $dashboard->index();
        }
    ))->where('slug', '[0-9a-zA-Z\-\/]+');
    Route::post('admin/folder/{slug}', array(
        'before' => 'csrf', 
        function($slug) {
            $dashboard = new Sule\Kotakin\Controllers\Admin\Dashboard(
                App::make('kotakin'), 
                App::make('sentry'), 
                App::make('jitimage')
            );

            return $dashboard->index($slug);
        }
    ))->where('slug', '[0-9a-zA-Z\-\/]+');

    Route::get('admin/search', function() {
        $search = new Sule\Kotakin\Controllers\Admin\Search(
            App::make('kotakin'), 
            App::make('sentry')
        );

        return $search->index();
    });

    Route::get('admin/links', function() {
        $link = new Sule\Kotakin\Controllers\Admin\Link(
            App::make('kotakin'), 
            App::make('sentry')
        );

        return $link->index();
    });

    Route::get('admin/links/item/{slug}', function($slug) {
        $link = new Sule\Kotakin\Controllers\Admin\Link(
            App::make('kotakin'), 
            App::make('sentry')
        );

        return $link->item($slug);
    })->where('slug', '[0-9a-z\-]+');

    Route::get('admin/file/{slug}', function($slug) {
        $doc = new Sule\Kotakin\Controllers\Admin\Document(
            App::make('kotakin'), 
            App::make('sentry'), 
            App::make('jitimage'), 
            App::make('jitimage.cache')
        );

        return $doc->view($slug);
    })->where('slug', '[0-9a-f\-]+');

    Route::get('admin/preference/user', function() {
        $user = new Sule\Kotakin\Controllers\Admin\User(
            App::make('kotakin'), 
            App::make('sentry')
        );

        return $user->index();
    });

    Route::get('admin/preference/user/{userId}', function($userId) {
        $user = new Sule\Kotakin\Controllers\Admin\User(
            App::make('kotakin'), 
            App::make('sentry')
        );

        return $user->edit($userId);
    })->where('userId', '[0-9]+');
    Route::post('admin/preference/user/{userId}', array(
        'before' => 'csrf', 
        function($userId) {
            $user = new Sule\Kotakin\Controllers\Admin\User(
                App::make('kotakin'), 
                App::make('sentry')
            );

            return $user->save($userId);
        }
    ))->where('userId', '[0-9]+');

    Route::get('admin/me', function() {
        $user = new Sule\Kotakin\Controllers\Admin\User(
            App::make('kotakin'), 
            App::make('sentry')
        );

        return $user->edit(0, true);
    });
    Route::post('admin/me', array(
        'before' => 'csrf', 
        function() {
            $user = new Sule\Kotakin\Controllers\Admin\User(
                App::make('kotakin'), 
                App::make('sentry')
            );

            return $user->save(0, true);
        }
    ));

    Route::get('admin/preference/general', function() {
        $general = new Sule\Kotakin\Controllers\Admin\General(
            App::make('kotakin'), 
            App::make('sentry')
        );

        return $general->index();
    });
    Route::post('admin/preference/general', array(
        'before' => 'csrf', 
        function() {
            $general = new Sule\Kotakin\Controllers\Admin\General(
                App::make('kotakin'), 
                App::make('sentry')
            );

            return $general->save();
        }
    ));

    Route::get('admin/preference/mail_template', function() {
        $mail = new Sule\Kotakin\Controllers\Admin\EmailTemplate(
            App::make('kotakin'), 
            App::make('sentry')
        );

        return $mail->index();
    });
    Route::post('admin/preference/mail_template', array(
        'before' => 'csrf', 
        function() {
            $mail = new Sule\Kotakin\Controllers\Admin\EmailTemplate(
                App::make('kotakin'), 
                App::make('sentry')
            );

            return $mail->save();
        }
    ));

    Route::get('admin/preference/archive', function() {
        $archive = new Sule\Kotakin\Controllers\Admin\Archive(
            App::make('kotakin'), 
            App::make('sentry')
        );

        return $archive->index();
    });

    Route::get('admin/preference/archive/{id}', function($id) {
        $archive = new Sule\Kotakin\Controllers\Admin\Archive(
            App::make('kotakin'), 
            App::make('sentry')
        );

        return $archive->item($id);
    })->where('id', '[0-9]+');

    Route::get('admin/preference/editor', function() {
        $editor = new Sule\Kotakin\Controllers\Admin\Editor(
            App::make('kotakin'), 
            App::make('sentry')
        );

        return $editor->index();
    });
    Route::post('admin/preference/editor', array(
        'before' => 'csrf', 
        function() {
            $editor = new Sule\Kotakin\Controllers\Admin\Editor(
                App::make('kotakin'), 
                App::make('sentry')
            );

            return $editor->save();
        }
    ));

});

Route::post('admin/upload/file', array(
    'before' => 'csrf', 
    function() {
        $doc = new Sule\Kotakin\Controllers\Admin\Document(
            App::make('kotakin'), 
            App::make('sentry'), 
            App::make('jitimage'), 
            App::make('jitimage.cache')
        );

        return $doc->upload();
    }
));

Route::delete('admin/file/{slug}', function($slug) {
    $doc = new Sule\Kotakin\Controllers\Admin\Document(
        App::make('kotakin'), 
        App::make('sentry'), 
        App::make('jitimage'), 
        App::make('jitimage.cache')
    );

    return $doc->delete($slug);
})->where('slug', '[0-9a-f\-]+');
// -----------------------------------------------------------------------------
// END Admin Routes

// User Routes
// -----------------------------------------------------------------------------
Route::get('{slug}/login', array(
    'before' => 'account.login', 
    function($slug) {
        $acc = new Sule\Kotakin\Controllers\Account(
            App::make('kotakin'), 
            App::make('sentry'), 
            $slug
        );

        return $acc->login();
    }
))->where('slug', '[0-9a-z\-]+');
Route::post('{slug}/login', array(
    'before' => 'csrf', 
    function($slug) {
        $acc = new Sule\Kotakin\Controllers\Account(
            App::make('kotakin'), 
            App::make('sentry'), 
            $slug
        );

        return $acc->login();
    }
))->where('slug', '[0-9a-z\-]+');
Route::get('{slug}/logout', function($slug) {
    $acc = new Sule\Kotakin\Controllers\Account(
        App::make('kotakin'), 
        App::make('sentry'), 
        $slug
    );

    return $acc->logout();
})->where('slug', '[0-9a-z\-]+');

Route::get('{slug}/forgot', array(
    'before' => 'csrf', 
    'before' => 'account.login', 
    function($slug) {
        $acc = new Sule\Kotakin\Controllers\Account(
            App::make('kotakin'), 
            App::make('sentry'), 
            $slug
        );

        return $acc->forgot();
    }
))->where('slug', '[0-9a-z\-]+');
Route::post('{slug}/forgot', array(
    'before' => 'csrf', 
    'before' => 'account.login', 
    function($slug) {
        $acc = new Sule\Kotakin\Controllers\Account(
            App::make('kotakin'), 
            App::make('sentry'), 
            $slug
        );

        return $acc->forgot();
    }
))->where('slug', '[0-9a-z\-]+');

Route::get('{slug}/reset/{code}', array(
    'before' => 'account.login', 
    function($slug, $code) {
        $acc = new Sule\Kotakin\Controllers\Account(
            App::make('kotakin'), 
            App::make('sentry'), 
            $slug
        );

        return $acc->reset($code);
    }
))->where('slug', '[0-9a-z\-]+')->where('code', '[0-9a-zA-Z]+');
Route::post('{slug}/reset/{code}', array(
    'before' => 'csrf', 
    'before' => 'account.login', 
    function($slug, $code) {
        $acc = new Sule\Kotakin\Controllers\Account(
            App::make('kotakin'), 
            App::make('sentry'), 
            $slug
        );

        return $acc->reset($code);
    }
))->where('slug', '[0-9a-z\-]+')->where('code', '[0-9a-zA-Z]+');

Route::get('{slug}/reset_complete', array(
    'before' => 'account.login', 
    function($slug) {
        $acc = new Sule\Kotakin\Controllers\Account(
            App::make('kotakin'), 
            App::make('sentry'), 
            $slug
        );

        return $acc->resetComplete();
    }
))->where('slug', '[0-9a-z\-]+');

Route::post('{slug}/upload/file', array(
    'before' => 'csrf', 
    function($slug) {
        $doc = new Sule\Kotakin\Controllers\Document(
            App::make('kotakin'), 
            App::make('sentry'), 
            App::make('jitimage'),
            App::make('jitimage.cache'), 
            $slug
        );

        return $doc->upload();
    }
))->where('slug', '[0-9a-z\-]+');

Route::delete('{slug}/file/{fileSlug}', function($slug, $fileSlug) {
    $doc = new Sule\Kotakin\Controllers\Document(
        App::make('kotakin'), 
        App::make('sentry'), 
        App::make('jitimage'), 
        App::make('jitimage.cache'), 
        $slug
    );

    return $doc->delete($fileSlug);
})->where('slug', '[0-9a-z\-]+')->where('fileSlug', '[0-9a-f\-]+');

Route::group(array('before' => 'account.auth'), function() {

    Route::post('{slug}/notify', array(
        'before' => 'csrf', 
        function($slug) {
            $dashboard = new Sule\Kotakin\Controllers\Dashboard(
                App::make('kotakin'), 
                App::make('sentry'), 
                $slug
            );

            return $dashboard->notify();
        }
    ))->where('slug', '[0-9a-z\-]+');

    Route::get('{slug}/search', function($slug) {
        $search = new Sule\Kotakin\Controllers\Search(
            App::make('kotakin'), 
            App::make('sentry'), 
            $slug
        );

        return $search->index();
    })->where('slug', '[0-9a-z\-]+');

    Route::get('{slug}/me', function($slug) {
        $user = new Sule\Kotakin\Controllers\User(
            App::make('kotakin'), 
            App::make('sentry'), 
            $slug
        );

        return $user->edit();
    })->where('slug', '[0-9a-z\-]+');
    Route::post('{slug}/me', array(
        'before' => 'csrf', 
        function($slug) {
            $user = new Sule\Kotakin\Controllers\User(
                App::make('kotakin'), 
                App::make('sentry'), 
                $slug
            );

            return $user->save();
        }
    ))->where('slug', '[0-9a-z\-]+');

    Route::get('{slug}/file/{fileSlug}', function($slug, $fileSlug) {
        $doc = new Sule\Kotakin\Controllers\Document(
            App::make('kotakin'), 
            App::make('sentry'), 
            App::make('jitimage'), 
            App::make('jitimage.cache'), 
            $slug
        );

        return $doc->view($fileSlug);
    })->where('slug', '[0-9a-z\-]+')->where('fileSlug', '[0-9a-f\-]+');

    Route::get('{slug}', function($slug) {
        $dashboard = new Sule\Kotakin\Controllers\Dashboard(
            App::make('kotakin'), 
            App::make('sentry'), 
            $slug
        );

        return $dashboard->index();
    })->where('slug', '[0-9a-z\-]+');

    Route::get('{slug}/folder/{folderSlug}', function($slug, $folderSlug) {
        $dashboard = new Sule\Kotakin\Controllers\Dashboard(
            App::make('kotakin'), 
            App::make('sentry'), 
            $slug
        );

        return $dashboard->index($folderSlug);
    })->where('slug', '[0-9a-z\-]+')->where('folderSlug', '[0-9a-zA-Z\-\/]+');

    Route::post('{slug}/folder/{folderSlug}', array(
        'before' => 'csrf', 
        function($slug, $folderSlug) {
            $dashboard = new Sule\Kotakin\Controllers\Dashboard(
                App::make('kotakin'), 
                App::make('sentry'), 
                $slug
            );

            return $dashboard->index($folderSlug);
        }
    ))->where('slug', '[0-9a-z\-]+')->where('folderSlug', '[0-9a-zA-Z\-\/]+');
    Route::post('{slug}', array(
        'before' => 'csrf', 
        function($slug) {
            $dashboard = new Sule\Kotakin\Controllers\Dashboard(
                App::make('kotakin'), 
                App::make('sentry'), 
                $slug
            );

            return $dashboard->index();
        }
    ))->where('slug', '[0-9a-zA-Z\-\/]+');

});
// -----------------------------------------------------------------------------
// END User Routes
