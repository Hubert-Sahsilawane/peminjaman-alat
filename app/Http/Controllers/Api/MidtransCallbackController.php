<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Loan;

class MidtransCallbackController extends Controller
{
    public function callback(Request $request)
    {
        $serverKey = config('midtrans.server_key');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        // Validasi signature (Keamanan)
        if ($hashed == $request->signature_key) {
            // Karena order_id tadi kita tambah time() (misal: INV/202604/0013-17000000), kita ambil nomor invoice aslinya
            $invoicenumber = explode('-', $request->order_id)[0];

            if ($request->transaction_status == 'capture' || $request->transaction_status == 'settlement') {
                // Pembayaran Sukses -> Ubah status di database jadi lunas
                Loan::where('invoice_number', $invoicenumber)->update([
                    'status_pembayaran' => 'lunas'
                ]);
            }
        }

        return response()->json(['message' => 'Callback received']);
    }
}
