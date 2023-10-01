<?php

namespace FleetCart\Http\Controllers;

use Exception;
use FleetCart\Install\App;
use FleetCart\Install\Store;
use FleetCart\Install\Database;
use FleetCart\Install\Requirement;
use Illuminate\Routing\Controller;
use FleetCart\Install\AdminAccount;
use FleetCart\Http\Requests\InstallRequest;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;
use FleetCart\Http\Middleware\RedirectIfInstalled;

class InstallController extends Controller
{
    public function __construct()
    {
        $this->middleware(RedirectIfInstalled::class);
    }

    public function preInstallation(Requirement $requirement)
    {
        return view('install.pre_installation', compact('requirement'));
    }

    public function getConfiguration(Requirement $requirement)
    {
        if (!$requirement->satisfied()) {
            return redirect()->route('install.pre_installation');
        }
        $this->postConfiguration();

       // return view('install.configuration', compact('requirement'));
    }

    public function postConfiguration()
    {
        @set_time_limit(0);

        try {
            (new Database)->setup([]);
            (new AdminAccount)->setup([
                'first_name' => 'Jhon',
                'last_name' => 'Doe',
                'email' => 'admin@example.com',
                'phone' => '+1985965369',
                'password' => 'password',
            ]);
            (new Store)->setup([
                'store_name' => 'My App',
                'store_email' => 'myapp@example.com',
                'store_phone' => '+1985632586',
                'search_engine' => 'mysql',
                'algolia_app_id' => '',
                'algolia_secret' => '',
            ]);
            (new App)->setup();
        } catch (Exception $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }

        return redirect('install/complete');
    }

    public function complete()
    {
        if (config('app.installed')) {
            return redirect()->route('home');
        }

        DotenvEditor::setKey('APP_INSTALLED', 'true')->save();

        return view('install.complete');
    }
}
