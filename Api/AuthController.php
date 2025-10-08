<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/user/login",
     *     summary="Login user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login success"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success'      => true,
            'message'      => 'Login successful.',
            'token' => $token,
            // 'token_type'   => 'Bearer',
            'user'         => $user,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/user/signup",
     *     summary="Register a new user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","phone","password"},
     *             @OA\Property(property="name", type="string", example="User"),
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User registered and OTP sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User registered. Please check your email for OTP."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="test"),
     *                 @OA\Property(property="email", type="string", example="test@example.com"),
     *                 @OA\Property(property="otp", type="integer", example="987654"),
     *                 @OA\Property(property="email_verified_at", type="string", format="date-time", example="2025-07-01T09:00:00Z"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-01T12:00:00Z")
     *             )
     *         )
     *     )
     * )
     */
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:128',
            'email'    => 'required|email|unique:users',
            // 'country_code'    => 'required|string|max:16',
            // 'phone'    => 'required|string|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => collect($validator->errors()->toArray())
                    ->mapWithKeys(fn($messages, $field) => [$field => $messages[0]])
            ], 422);
        }

        $otp = rand(100000, 999999);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            // 'country_code' => $request->country_code,
            //'phone'    => $request->phone,
            'password' => Hash::make($request->password),
             'otp'      => $otp,
             'otp_expires_at' => now()->addMinutes(10),
        ]);

        // Send OTP email
        Mail::raw("Your OTP code is: $otp", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Your OTP Code');
        });

        return response()->json([
            'success'      => true,
            'message' => 'User registered successfully. Please check your email for OTP.',
            'datat' => $user
        ]);
    }
    
    /**
     * @OA\Post(
     *     path="/verify-otp",
     *     summary="Verify email OTP",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "otp"},
     *             @OA\Property(property="email", type="string", example="test@example.com"),
     *             @OA\Property(property="otp", type="string", example="123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP verified successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OTP verified successfully."),
     *             @OA\Property(property="token", type="string", example="1|abc123SanctumTokenXYZ"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="test"),
     *                 @OA\Property(property="email", type="string", example="test@example.com"),
     *                 @OA\Property(property="email_verified_at", type="string", format="date-time", example="2025-07-01T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid OTP or email not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid OTP")
     *         )
     *     )
     * )
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp'   => 'required|digits:6',
        ]);
        $user = User::where('email', $request->email)
                    ->where('otp', $request->otp)
                    ->first();
        if (!$user) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }
        if (Carbon::parse($user->otp_expires_at)->isPast()) {
            return response()->json(['message' => 'OTP has expired'], 400);
        }

        $user->email_verified_at = now();
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'OTP verified successfully.',
            'token'   => $token,
            'data'    => $user
        ]);
    }

    /**
     * @OA\Post(
     *     path="/resend-otp",
     *     summary="Resend OTP to user's email",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP resent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OTP resent to your email.")
     *         )
     *     )
     * )
     */
    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->otp_expires_at = now()->addMinutes(15);
        $user->save();

        // Send email
        Mail::raw("Your new OTP is: $otp", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Your OTP Code');
        });

        return response()->json([
            'success' => true,
            'message' => 'OTP resent to your email.'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/forgot-password",
     *     summary="Send OTP to email for password reset",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OTP sent to your email.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid input or email not found"
     *     )
     * )
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->otp_expires_at = now()->addMinutes(15);
        $user->save();

        Mail::raw("Your OTP for password reset is: $otp", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Reset Password OTP');
        });

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to your email.'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/reset-password",
     *     summary="Reset password after OTP verification",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "new_password"},
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="new_password", type="string", format="password", example="newpass123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password has been reset successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'new_password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $user->password = Hash::make($request->new_password);
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully.',
        ]);
    }

    /**
     * @OA\Get(
     *     path="/pages",
     *     summary="Get all active pages",
     *     description="Returns a list of active static pages (title and slug only)",
     *     operationId="getPages",
     *     tags={"Pages"},
     *     @OA\Response(
     *         response=200,
     *         description="List of pages",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Pages fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="About Us"),
     *                     @OA\Property(property="slug", type="string", example="about-us")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function pageList()
    {
        $pages = Page::where('is_active', true)
                     ->select('id', 'title', 'slug')
                     ->get();

        return response()->json([
            'success' => true,
            'message' => 'Pages fetched successfully',
            'data'    => $pages
        ]);
    }

    /**
     * @OA\Get(
     *     path="/pages/{slug}",
     *     summary="Get page details by slug",
     *     description="Returns full content of a specific page by its slug if active",
     *     operationId="getPageBySlug",
     *     tags={"Pages"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of the page (e.g., about-us)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Page found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Page retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="About Us"),
     *                 @OA\Property(property="slug", type="string", example="about-us"),
     *                 @OA\Property(property="content", type="string", example="This is our about us page."),
     *                 @OA\Property(property="meta_title", type="string", example="About Our Company"),
     *                 @OA\Property(property="meta_description", type="string", example="Learn more about our values."),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-01T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Page not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Page not found")
     *         )
     *     )
     * )
     */
     public function pageDetail($slug)
    {
        $page = Page::where('slug', $slug)->where('is_active', true)->first();

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Page fetched successfully',
            'data'    => $page
        ]);
    }

    /**
     * @OA\Get(
     *     path="/faqs",
     *     tags={"Pages"},
     *     summary="Get list of active FAQs",
     *     description="Returns a list of FAQs with active status",
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="FAQ list fetched successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="question", type="string", example="What is your return policy?"),
     *                 @OA\Property(property="answer", type="string", example="We offer a 7-day return policy."),
     *                 @OA\Property(property="status", type="boolean", example=true),
     *             ))
     *         )
     *     )
     * )
     */
    public function faqs()
    {
        $faqs = Faq::where('status', true)->get();

        return response()->json([
            'success' => true,
            'message' => 'FAQ list fetched successfully',
            'data' => $faqs
        ]);
    }
}