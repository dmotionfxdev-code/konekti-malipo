<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class ApiKeyController extends Controller
{
    public function index(Request $request) { return view('api-keys', ['apiKeys' => $request->user()->apiKeys()->whereNull('revoked_at')->latest()->get()]); }
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);
        $plain = 'km_live_'.Str::random(48);
        $key = $request->user()->apiKeys()->create(['name' => $data['name'], 'prefix' => substr($plain, 0, 16), 'key_hash' => hash('sha256', $plain)]);
        return back()->with('api_key_once', $plain)->with('success', "API key {$key->name} created. Copy it now; it will not be shown again.");
    }
    public function destroy(Request $request, int $apiKey): RedirectResponse
    {
        $key = $request->user()->apiKeys()->findOrFail($apiKey); $key->update(['revoked_at' => now()]);
        return back()->with('success', 'API key ime-revoke.');
    }
}
