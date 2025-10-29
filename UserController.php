<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use DataTables;
class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = User::whereDoesntHave('roles', function ($q) {
                $q->where('name', 'admin');
            })
            ->orderBy('id', 'DESC');
            return DataTables::eloquent($data)
                ->addIndexColumn()
                 ->addColumn('image',function($row){
                    $img = '<img src="' .$row->image. '" style="border-radius: 100%; width: 40px; height: 40px;">';
                    return $img;
                })
                 ->addColumn('action',function($row){
                    $btn = '<div class="card-header d-flex align-items-center">';
                    $btn .= '<a href="#" title="Show" class="btn btn-warning btn-sm me-2"><i class="ri-eye-2-line"></i></a>';
                    $btn .= '</div>';
                    return $btn;
                 })
                 ->rawColumns(['image','action'])
                ->make(true);
        }
        return view('admin.user.index');
    }
}
