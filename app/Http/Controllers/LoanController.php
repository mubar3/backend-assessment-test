<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Illuminate\Validation\Rule;
use App\Http\Requests\LoanShowRequest;
use App\Models\Loan;
use Carbon\Carbon;

class LoanController extends BaseController
{
    public function index()
    {
        $user = Auth::user();
        return response()->json(['status'=>true,'data'=>Loan::where('user_id',$user->id)->where('status','due')->get()]);
    }

    public function store(Request $data)
    {
        $validasi = Validator::make($data->all(), [
            'amount'  => 'required|integer',
            'currency_code'  => ['required',Rule::in(Loan::CURRENCIES)],
            'time_month'  => 'required|integer',
            'terms'  => 'required|integer',
        ]);

        if($validasi->fails()){
            return response()->json(['status'=>false,'data'=>$validasi->errors()]);
        }

        $data->amount=$data->amount/$data->time_month;
        $user = Auth::user();
        DB::beginTransaction();
        try{
            if(Loan::where('status','due')->where('user_id',$user->id)->first()){
                return response()->json(['status'=>false,'data'=>'Masih ada peminjaman yang belum terbayar']);
            }

            // $tanggal=Carbon::now()->subMonth();
            $tanggal=Carbon::now();
            for ($i=0; $i < $data->time_month ; $i++) { 
                Loan::create([
                    'user_id' => $user->id, 
                    'amount' => $data->amount, 
                    'terms' => $data->terms, 
                    'outstanding_amount' => $data->amount, 
                    'currency_code' => $data->currency_code, 
                    'status' => 'due', 
                    'processed_at' => $tanggal->addMonth()->toDateString(), 
                ]);
            }
            DB::commit();
            return response()->json(['status'=>true,'data'=>'Sukses']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status'=>false,'data'=>'Terjadi kesalahan dalam penyimpanan']);
        }
    }

    public function pay(Request $data)
    {
        $validasi = Validator::make($data->all(), [
            'amount'  => 'required|integer',
        ]);

        if($validasi->fails()){
            return response()->json(['status'=>false,'data'=>$validasi->errors()]);
        }

        $user = Auth::user();
        DB::beginTransaction();
        try{
            $peminjaman=Loan::where('status','due')->where('user_id',$user->id)->orderby('processed_at','asc')->get();

            foreach ($peminjaman as $key) {
                if($key->outstanding_amount > $data->amount){
                    $key->outstanding_amount=$key->outstanding_amount - $data->amount;
                    $data->amount=0;
                }else{
                    $data->amount=$data->amount-$key->outstanding_amount;
                    $key->outstanding_amount=0;
                    $key->status='repaid'; 
                }
                // return $key;
                // Loan::find($key->id)->update($key);
                Loan::find($key->id)->update([
                    'status' => $key->status,
                    'outstanding_amount' => $key->outstanding_amount,
                ]);

                if($data->amount == 0){
                    break;
                }
            }
            if($data->amount != 0){
                return response()->json(['status'=>false,'data'=>'Pembayaran melebihi jumlah peminjaman']);
            }

            DB::commit();
            return response()->json(['status'=>true,'data'=>'Sukses']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status'=>false,'data'=>'Terjadi kesalahan dalam penyimpanan']);
        }
    }
}
