<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Flat;
use Illuminate\Http\Request;
use DataTables;
class BuildingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Building::with('user')
            ->orderBy('id', 'DESC');
            return DataTables::eloquent($data)
                ->addIndexColumn()
                ->addColumn('user_name',function($row){
                   return $row->user?->name;
                 })
                 ->addColumn('action',function($row){
                    $btn = '<div class="card-header d-flex align-items-center">';
                    $btn .= '<a href="#" title="Show" class="btn btn-warning btn-sm me-2"><i class="ri-eye-2-line"></i></a>';
                    $btn .= '</div>';
                    return $btn;
                 })
                 ->rawColumns(['user_name','action'])
                ->make(true);
        }
        return view('admin.building.index');
    }

    public function flatIndex(Request $request)
    {
        if ($request->ajax()) {
            $data = Flat::with(['user','building'])
            ->orderBy('id', 'DESC');
            return DataTables::eloquent($data)
                ->addIndexColumn()
                ->addColumn('user_name',function($row){
                   return $row->user?->name;
                 })
                 ->addColumn('building_name',function($row){
                    return $row->building?->name;
                  })
                 ->addColumn('action',function($row){
                    $btn = '<div class="card-header d-flex align-items-center">';
                    $btn .= '<a href="#" title="Show" class="btn btn-warning btn-sm me-2"><i class="ri-eye-2-line"></i></a>';
                    $btn .= '</div>';
                    return $btn;
                 })
                 ->rawColumns(['user_name','building_name','action'])
                ->make(true);
        }
    return view('admin.flat.index');
  }
}
