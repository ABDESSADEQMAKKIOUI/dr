<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class InstallController extends Controller
{
    /** Redirect to step 1 if not installed, otherwise block. */
    public function index()
    {
        if ($this->isInstalled()) {
            return redirect('/')->with('error', 'Application is already installed.');
        }

        return redirect()->route('install.step', ['step' => 1]);
    }

    /** Show a specific step. */
    public function step(int $step): View
    {
        if ($this->isInstalled()) {
            abort(403, 'Already installed.');
        }

        $data = match ($step) {
            1 => ['requirements' => $this->checkRequirements()],
            2 => [],
            3 => [],
            4 => [],
            5 => [],
            6 => [],
            default => abort(404),
        };

        return view("install.step{$step}", array_merge(['step' => $step], $data));
    }

    /** Handle step form submissions. */
    public function processStep(Request $request, int $step)
    {
        if ($this->isInstalled()) {
            abort(403, 'Already installed.');
        }

        return match ($step) {
            1 => $this->processStep1($request),
            2 => $this->processStep2($request),
            3 => $this->processStep3($request),
            4 => $this->processStep4($request),
            5 => $this->processStep5($request),
            default => redirect()->route('install.step', ['step' => 1]),
        };
    }

    // ── Step 1: Requirements check (just advance) ──────────────
    private function processStep1(Request $request)
    {
        $requirements = $this->checkRequirements();
        $failed = array_filter($requirements, fn($r) => !$r['pass']);

        if (!empty($failed)) {
            return back()->withErrors(['requirements' => 'Some server requirements are not met. Please fix them before continuing.']);
        }

        return redirect()->route('install.step', ['step' => 2]);
    }

    // ── Step 2: Database credentials ──────────────────────────
    private function processStep2(Request $request)
    {
        $request->validate([
            'db_host'     => 'required|string',
            'db_port'     => 'required|numeric',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        // Test the connection
        try {
            $pdo = new \PDO(
                "mysql:host={$request->db_host};port={$request->db_port};dbname={$request->db_database}",
                $request->db_username,
                $request->db_password ?? ''
            );
        } catch (\PDOException $e) {
            return back()->withErrors(['db_connection' => 'Connection failed: ' . $e->getMessage()])->withInput();
        }

        // Store in session for next steps
        session([
            'install_db' => $request->only('db_host', 'db_port', 'db_database', 'db_username', 'db_password'),
        ]);

        return redirect()->route('install.step', ['step' => 3]);
    }

    // ── Step 3: Run migrations ────────────────────────────────
    private function processStep3(Request $request)
    {
        $db = session('install_db');
        if (!$db) {
            return redirect()->route('install.step', ['step' => 2]);
        }

        // Write .env values temporarily
        $this->setEnvValues([
            'DB_HOST'     => $db['db_host'],
            'DB_PORT'     => $db['db_port'],
            'DB_DATABASE' => $db['db_database'],
            'DB_USERNAME' => $db['db_username'],
            'DB_PASSWORD' => $db['db_password'] ?? '',
        ]);

        try {
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);
        } catch (\Throwable $e) {
            return back()->withErrors(['migration' => 'Migration failed: ' . $e->getMessage()]);
        }

        return redirect()->route('install.step', ['step' => 4]);
    }

    // ── Step 4: Admin account ────────────────────────────────
    private function processStep4(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        session(['install_admin' => $request->only('name', 'email', 'password')]);

        return redirect()->route('install.step', ['step' => 5]);
    }

    // ── Step 5: Company info → write .env, create admin, mark installed ──
    private function processStep5(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:100',
            'currency'     => 'required|string|max:10',
            'timezone'     => 'required|string',
        ]);

        $admin = session('install_admin');
        if (!$admin) {
            return redirect()->route('install.step', ['step' => 4]);
        }

        // Update admin user created by seeder
        try {
            $user = \App\Models\User::where('email', 'admin@admin.com')->first();
            if ($user) {
                $user->update([
                    'name'       => $admin['name'],
                    'first_name' => explode(' ', $admin['name'])[0],
                    'last_name'  => explode(' ', $admin['name'])[1] ?? '',
                    'email'      => $admin['email'],
                    'password'   => Hash::make($admin['password']),
                ]);
            } else {
                \App\Models\User::create([
                    'name'       => $admin['name'],
                    'first_name' => explode(' ', $admin['name'])[0],
                    'last_name'  => explode(' ', $admin['name'])[1] ?? '',
                    'email'      => $admin['email'],
                    'password'   => Hash::make($admin['password']),
                    'is_active'  => true,
                ]);
            }
        } catch (\Throwable $e) {
            return back()->withErrors(['admin' => 'Could not create admin: ' . $e->getMessage()]);
        }

        // Save company settings
        $settingsData = [
            'company_name' => $request->company_name,
            'currency'     => $request->currency,
            'timezone'     => $request->timezone,
        ];

        foreach ($settingsData as $key => $value) {
            \App\Models\Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // Write timezone to .env and mark installed via file marker
        $this->setEnvValues(['APP_TIMEZONE' => $request->timezone]);
        file_put_contents(storage_path('app/.installed'), date('Y-m-d H:i:s'));

        session()->forget(['install_db', 'install_admin']);

        return redirect()->route('install.step', ['step' => 6]);
    }

    // ─────────────────────────────────────────────────────────
    private function isInstalled(): bool
    {
        return file_exists(storage_path('app/.installed'));
    }

    private function checkRequirements(): array
    {
        return [
            ['name' => 'PHP >= 8.2',           'pass' => PHP_VERSION_ID >= 80200],
            ['name' => 'PDO Extension',         'pass' => extension_loaded('pdo')],
            ['name' => 'PDO MySQL',             'pass' => extension_loaded('pdo_mysql')],
            ['name' => 'Mbstring Extension',    'pass' => extension_loaded('mbstring')],
            ['name' => 'OpenSSL Extension',     'pass' => extension_loaded('openssl')],
            ['name' => 'Tokenizer Extension',   'pass' => extension_loaded('tokenizer')],
            ['name' => 'GD / Imagick',          'pass' => extension_loaded('gd') || extension_loaded('imagick')],
            ['name' => '.env file writable',    'pass' => is_writable(base_path('.env'))],
        ];
    }

    private function setEnvValues(array $values): void
    {
        $envPath = base_path('.env');
        $content = file_get_contents($envPath);

        foreach ($values as $key => $value) {
            $value   = str_contains($value, ' ') ? '"' . $value . '"' : $value;
            $pattern = "/^{$key}=.*/m";
            $replace = "{$key}={$value}";

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replace, $content);
            } else {
                $content .= "\n{$replace}";
            }
        }

        file_put_contents($envPath, $content);
    }
}
