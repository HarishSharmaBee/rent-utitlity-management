<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Flat;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class BuildingController extends Controller
{
    /**
     * @OA\Post(
     *     path="/buildings/store",
     *     tags={"Buildings"},
     *     summary="Create a new building for the authenticated user",
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","address","no_of_flats"},
     *             @OA\Property(property="name", type="string", example="Sunshine Apartments"),
     *             @OA\Property(property="address", type="string", example="123 MG Road, Bangalore"),
     *             @OA\Property(property="no_of_flats", type="integer", example=20)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Building created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="no_of_flats", type="integer"),
     *             @OA\Property(property="user_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'no_of_flats' => 'required|integer|min:0',
        ]);

        $building = Auth::user()->buildings()->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Building added successfully.',
            'data'    => $building
        ],201);
    }

    /**
     * @OA\Get(
     *     path="/buildings/list",
     *     tags={"Buildings"},
     *     summary="List buildings of the authenticated user (with pagination & search)",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of records per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Column to sort by (name, no_of_flats, created_at, etc.)",
     *         required=false,
     *         @OA\Schema(type="string", example="created_at")
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         description="Sorting order (asc or desc)",
     *         required=false,
     *         @OA\Schema(type="string", example="desc")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by building name or address",
     *         required=false,
     *         @OA\Schema(type="string", example="Green Residency")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of buildings",
     *         @OA\JsonContent(
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="address", type="string"),
     *                     @OA\Property(property="no_of_flats", type="integer"),
     *                     @OA\Property(property="user_id", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request)
    {
        $perPage   = $request->get('per_page', 10);
        $sortBy    = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $search    = $request->get('search');

        $query = Auth::user()->buildings();

        // 🔍 Apply search filter if provided
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%");
            });
        }

        $buildings = $query->orderBy($sortBy, $sortOrder)
                           ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Buildings found successfully.',
            'data'    => $buildings
        ]);
    }

    /**
     * @OA\Put(
     *     path="/buildings/update/{id}",
     *     tags={"Buildings"},
     *     summary="Update a building",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Building ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Residency"),
     *             @OA\Property(property="address", type="string", example="New MG Road, Delhi"),
     *             @OA\Property(property="no_of_flats", type="integer", example=25)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Building updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="no_of_flats", type="integer"),
     *             @OA\Property(property="user_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Building not found or unauthorized"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function update(Request $request, $id)
    {
        $building = Auth::user()->buildings()->find($id);

        if (!$building) {
            return response()->json([
                'success' => false,
                'message' => 'Building not found',
            ], 404);        
        }

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'address' => 'sometimes|nullable|string|max:255',
            'no_of_flats' => 'sometimes|integer|min:0',
        ]);

        $building->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Building updated successfully.',
            'data'    => $building
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/buildings/delete/{id}",
     *     tags={"Buildings"},
     *     summary="Delete a building",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Building ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="Building deleted successfully"),
     *     @OA\Response(response=404, description="Building not found or unauthorized"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function destroy($id)
    {
        $building = Auth::user()->buildings()->find($id);

        if (!$building) {
            return response()->json([
                'success' => false,
                'message' => 'Building not found',
            ], 404);
        }

        $building->delete();

        return response()->json([
            'success' => true,
            'message' => 'Building deleted successfully'
        ]);
    }

    /**
     * List flats with tenant details
     * 
     * @OA\Get(
     *     path="/flats/list",
     *     tags={"Flats"},
     *     summary="Get list of flats with tenant details",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Column to sort by (flat_number, created_at, etc.)",
     *         required=false,
     *         @OA\Schema(type="string", example="created_at")
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         description="Sorting order (asc or desc)",
     *         required=false,
     *         @OA\Schema(type="string", example="desc")
     *     ), 
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by flat number, tenant name, or building name",
     *         required=false,
     *         @OA\Schema(type="string", example="Green Residency")
     *     ),
     *     @OA\Response(response=200, description="List of flats with tenants"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function flatList(Request $request)
    {
        $perPage   = $request->get('per_page', 10);
        $sortBy    = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $search    = $request->get('search');

        $query = Flat::with(['tenant', 'building'])
            ->where('user_id', Auth::id());

        // 🔍 Apply search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('flat_number', 'LIKE', "%{$search}%")
                ->orWhereHas('tenant', function ($tenantQuery) use ($search) {
                    $tenantQuery->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('building', function ($buildingQuery) use ($search) {
                    $buildingQuery->where('name', 'LIKE', "%{$search}%")
                                    ->orWhere('address', 'LIKE', "%{$search}%");
                });
            });
        }

        $flats = $query->orderBy($sortBy, $sortOrder)
                    ->paginate($perPage);

        if ($flats->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No flats found for this user',
                'data'    => [],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Flats found successfully',
            'data'    => $flats
        ]);
    }

    /**
     * Store a new flat with tenant
     * 
     * @OA\Post(
     *     path="/flats/store",
     *     tags={"Flats"},
     *     summary="Create a flat with tenant",
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"flat_number","building_id","rent_start_date","rent_amount","status"},
     *                 @OA\Property(property="flat_number", type="string", example="A101"),
     *                 @OA\Property(property="status", type="integer", example=1, description="If 1, tenant fields are required"),
     *                 @OA\Property(property="building_id", type="integer", example=1),
     *                 @OA\Property(property="rent_start_date", type="string", format="date", example="2025-09-15", description="Required if status = 1"),
     *                 @OA\Property(property="rent_amount", type="number", format="float", example=12000.50),
     *                 @OA\Property(property="tenant_name", type="string", example="John Doe", description="Required if status = 1"),
     *                 @OA\Property(property="phone_number", type="string", example="9876543210", description="Required if status = 1"),
     *                 @OA\Property(property="country_code", type="string", example="+91", description="Required if status = 1"),
     *                 @OA\Property(property="aadhar_image", type="string", format="binary", description="Required if status = 1")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Flat created successfully"
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation failed")
     * )
     */
    public function flatStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flat_number'     => 'required|string|max:50',
            'building_id'     => 'required|exists:buildings,id',
            'rent_start_date' => 'required_if:status,1|date',
            'rent_amount'     => 'required|numeric',
            'status'          => 'required|numeric',
            'tenant_name'     => 'required_if:status,1|string|max:100',
            'phone_number'    => 'required_if:status,1|string|max:20',
            'country_code'    => 'required_if:status,1|string|max:10',
            'aadhar_image'    => 'required_if:status,1|image|mimes:jpg,jpeg,png',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }
    
        $flat = Flat::create([
            'flat_number'     => $request->flat_number,
            'building_id'     => $request->building_id,
            'rent_start_date' => $request->rent_start_date??null,
            'rent_amount'     => $request->rent_amount,
            'status'          => $request->status,
            'user_id'         => Auth::id(),
        ]);
    
        if ($request->status == 1 && $request->hasFile('aadhar_image')) {
            $aadharPath = $request->file('aadhar_image')->store('tenants', 'public');
    
            Tenant::create([
                'tenant_name'   => $request->tenant_name,
                'phone_number'  => $request->phone_number,
                'country_code'  => $request->country_code,
                'flat_id'       => $flat->id,
                'aadhar_image'  => $aadharPath,
                'rent_start_date' => $request->rent_start_date, // store in tenant table
            ]);
        }
    
        $flat = Flat::with('tenant')->where('id', $flat->id)->first();
    
        return response()->json([
            'success' => true,
            'message' => 'Flat created successfully',
            'data'    => $flat
        ], 201);
    }

    /**
     * Delete a flat
     * 
     * @OA\Delete(
     *     path="/flats/delete/{id}",
     *     tags={"Flats"},
     *     summary="Delete a flat with its tenant",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Flat ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Flat deleted successfully"
     *     ),
     *     @OA\Response(response=404, description="Flat not found"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function flatDelete($id)
    {
        $flat = Flat::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$flat) {
            return response()->json([
                'success' => false,
                'message' => 'Flat not found or you are not authorized to delete it',
            ], 404);
        }

        //Delete tenant aadhar images before removing records
        if ($flat->tenant) {
            if (!empty($flat->tenant->aadhar_image)) {
                $image = str_replace('storage','',$flat->tenant->aadhar_image);
                Storage::disk('public')->delete($image);
            }
        }

        $flat->delete();

        return response()->json([
            'success' => true,
            'message' => 'Flat deleted successfully',
        ], 200);
    }

    /**
     * Update a flat with tenant details
     * 
     * @OA\Post(
     *     path="/flats/update/{id}",
     *     tags={"Flats"},
     *     summary="Update a flat and its tenant",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Flat ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="flat_number", type="string", example="A102"),
     *                 @OA\Property(property="building_id", type="integer", example=1),
     *                 @OA\Property(property="status", type="integer", example=1, description="If 1, tenant fields are required"),
     *                 @OA\Property(property="rent_start_date", type="string", format="date", example="2025-09-20", description="Required if status = 1"),
     *                 @OA\Property(property="rent_amount", type="number", format="float", example=15000.75),
     *                 @OA\Property(property="tenant_name", type="string", example="Jane Doe", description="Required if status = 1"),
     *                 @OA\Property(property="phone_number", type="string", example="9876543210", description="Required if status = 1"),
     *                 @OA\Property(property="country_code", type="string", example="+91", description="Required if status = 1"),
     *                 @OA\Property(property="aadhar_image", type="string", format="binary", description="Required if status = 1")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Flat updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Flat not found"),
     *     @OA\Response(response=422, description="Validation failed")
     * )
     */
    public function flatUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'flat_number'     => 'sometimes|required|string|max:50',
            'building_id'     => 'sometimes|required|exists:buildings,id',
            'rent_start_date' => 'required_if:status,1|required|date',
            'rent_amount'     => 'sometimes|required|numeric',
            'status'          => 'sometimes|required|numeric',
            'tenant_name'     => 'required_if:status,1|string|max:100',
            'phone_number'    => 'required_if:status,1|string|max:20',
            'country_code'    => 'required_if:status,1|string|max:10',
            'aadhar_image'    => 'required_if:status,1|image|mimes:jpg,jpeg,png',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }
    
        $flat = Flat::where('id', $id)->where('user_id', Auth::id())->first();
    
        if (!$flat) {
            return response()->json([
                'success' => false,
                'message' => 'Flat not found or unauthorized',
            ], 404);
        }
    
        $flat->update($request->only([
            'flat_number',
            'building_id',
            'rent_start_date',
            'rent_amount',
            'status'
        ]));
    
        // Handle tenant
        if ($request->status == 1) {
            $tenant = $flat->tenant;
    
            $aadharPath = $tenant?->aadhar_image ?? null;
            if ($request->hasFile('aadhar_image')) {
                // Delete old Aadhaar if exists
                if (!empty($aadharPath)) {
                    $image = str_replace('storage', '', $aadharPath);
                    Storage::disk('public')->delete($image);
                }
                $aadharPath = $request->file('aadhar_image')->store('tenants', 'public');
            }
    
            if ($tenant) {
                $tenant->update([
                    'tenant_name'    => $request->tenant_name,
                    'phone_number'   => $request->phone_number,
                    'country_code'   => $request->country_code,
                    'aadhar_image'   => $aadharPath,
                    'rent_start_date'=> $request->rent_start_date,
                ]);
            } else {
                Tenant::create([
                    'tenant_name'    => $request->tenant_name,
                    'phone_number'   => $request->phone_number,
                    'country_code'   => $request->country_code,
                    'flat_id'        => $flat->id,
                    'aadhar_image'   => $aadharPath,
                    'rent_start_date'=> $request->rent_start_date,
                ]);
            }
        }
    
        $flat = Flat::with('tenant')->where('id', $flat->id)->first();
    
        return response()->json([
            'success' => true,
            'message' => 'Flat updated successfully',
            'data'    => $flat
        ], 200);
    }
}
