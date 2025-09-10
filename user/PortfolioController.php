<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\MovieInvestment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PortfolioController extends Controller
{
    public function index()
    {
        return view('frontend.portfolio');
    }

    public function investStore(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'movie_id' => 'required|exists:movies,id',
            'amount' => 'required|numeric|min:1',
            // Add more validations as needed
        ]);
        try {
            DB::beginTransaction();
            $investment = MovieInvestment::create([
                'order_number' => uniqid('INV-'),
                'user_id' => $validated['user_id'],
                'movie_id' => $validated['movie_id'],
                'amount' => $validated['amount'],
                //'payment_method' => 'manual', // or from $request if passed
                //'status' => 'pending', // or 'completed' if already paid
            ]);
            DB::commit();
            session()->flash('success', 'Investment done successfully');
            return response()->json([
                'message' => 'Investment created successfully',
                'redirect_url' => route('user.portfolio'), // Optional
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            // Optional: log the error
            Log::error('Investment creation failed', [
                'error'   => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'message' => 'Something went wrong while processing your investment.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

}
