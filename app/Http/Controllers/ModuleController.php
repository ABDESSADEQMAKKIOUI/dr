<?php

namespace App\Http\Controllers;

use App\Models\AppModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ModuleController extends Controller
{
    public function index()
    {
        // Seed default modules if empty
        if (AppModule::count() === 0) {
            foreach (AppModule::defaultModules() as $mod) {
                AppModule::create($mod);
            }
        }

        $modules = AppModule::orderBy('name')->get();
        return view('system.modules', compact('modules'));
    }

    public function toggle(Request $request, AppModule $module)
    {
        $module->update(['is_enabled' => !$module->is_enabled]);
        Cache::forget("module_{$module->key}");

        return back()->with('success', "Module '{$module->name}' " . ($module->is_enabled ? 'enabled' : 'disabled') . '.');
    }

    public function update(Request $request, AppModule $module)
    {
        $data = $request->validate([
            'is_enabled' => 'required|boolean',
        ]);

        $module->update($data);
        Cache::forget("module_{$module->key}");

        return response()->json(['success' => true, 'is_enabled' => $module->is_enabled]);
    }
}
