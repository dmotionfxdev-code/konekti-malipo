<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PaymentGatewayAccount;
use App\Payments\Providers\Pesapal\PesapalClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class GatewayAccountController extends Controller
{
    public function store(Request $request, PesapalClient $pesapal): RedirectResponse
    {
        $data = $request->validate(['consumer_key' => ['required','string','max:255'], 'consumer_secret' => ['required','string','max:255'], 'environment' => ['required','in:sandbox,production']]);
        $account = $request->user()->gatewayAccounts()->updateOrCreate(['gateway' => 'pesapal', 'environment' => $data['environment']], ['credentials' => ['consumer_key' => $data['consumer_key'], 'consumer_secret' => $data['consumer_secret']], 'enabled' => true]);
        try {
            $ipnUrl = route('payments.webhook.account', ['gateway' => 'pesapal', 'account' => $account]);
            $result = $pesapal->registerIpn($account, $ipnUrl);
            if (empty($result['ipn_id'])) throw new \RuntimeException('Pesapal did not return an IPN ID.');
            $account->update(['ipn_id' => $result['ipn_id'], 'ipn_url' => $ipnUrl]);
        } catch (\Throwable $exception) {
            report($exception); return back()->withErrors(['gateway' => 'Credentials zimehifadhiwa, lakini IPN haikusajiliwa. Hakikisha domain yako ya HTTPS inapatikana, kisha jaribu tena.']);
        }
        return back()->with('success', 'Pesapal imeunganishwa na IPN ID imehifadhiwa automatically.');
    }
}
