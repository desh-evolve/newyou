<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageService;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PackageServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PackageService::with(['package', 'service'])->where('status', 'active');

        // Filter by package
        if ($request->filled('package_id')) {
            $query->where('package_id', $request->package_id);
        }

        // Filter by service
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('package', function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                })->orWhereHas('service', function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                });
            });
        }

        $packageServices = $query->orderBy('package_id')->orderBy('service_id')->paginate(20)->withQueryString();
        $packages = Package::where('status', 'active')->orderBy('sort_order')->get();
        $services = Service::where('status', 'active')->orderBy('sort_order')->get();

        return view('admin.package-services.index', compact('packageServices', 'packages', 'services'));
    }

    /**
     * Show the form for assigning services to packages.
     */
    public function assign(Request $request)
    {
        $packages = Package::where('status', 'active')->orderBy('sort_order')->get();
        $services = Service::where('status', 'active')->orderBy('sort_order')->get();
        
        $selectedPackage = null;
        if ($request->filled('package_id')) {
            $selectedPackage = Package::with(['packageServices' => function ($query) {
                $query->where('status', 'active');
            }, 'packageServices.service'])->find($request->package_id);
        }

        return view('admin.package-services.assign', compact('packages', 'services', 'selectedPackage'));
    }

    /**
     * Store service assignments to a package.
     */
    public function storeAssignment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_id' => 'required|exists:packages,id',
            'services' => 'required|array|min:1',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.quantity' => 'required|integer|min:1',
            'services.*.custom_price' => 'nullable|numeric|min:0',
            'services.*.notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $package = Package::findOrFail($request->package_id);

        // Check if package is soft deleted
        if ($package->status === 'delete') {
            return redirect()->back()->with('error', 'Cannot assign services to a deleted package.');
        }

        // Get the service IDs that will be assigned
        $newServiceIds = collect($request->services)->pluck('service_id')->toArray();
        
        // Soft delete assignments that are not in the new list
        PackageService::where('package_id', $package->id)
            ->whereNotIn('service_id', $newServiceIds)
            ->update([
                'status' => 'delete',
                'updated_by' => Auth::id()
            ]);

        // Add or update assignments
        $now = now();
        $userId = Auth::id();

        $upsertData = collect($request->services)->map(function ($service) use ($package, $now, $userId) {
            return [
                'package_id'   => $package->id,
                'service_id'   => $service['service_id'],
                'quantity'     => $service['quantity'] ?? 1,
                'custom_price' => $service['custom_price'] ?? null,
                'notes'        => $service['notes'] ?? null,
                'status'       => 'active',
                'updated_by'   => $userId,
                'created_by'   => $userId,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        })->all();

        PackageService::withoutGlobalScopes()->upsert(
            $upsertData,
            ['package_id', 'service_id'], // unique key
            ['quantity', 'custom_price', 'notes', 'status', 'updated_by', 'updated_at']
        );

        return redirect()->route('admin.package-services.assign', ['package_id' => $package->id])
            ->with('success', 'Services assigned to package successfully!');
    }

    /**
     * Update a single package service record.
     */
    public function updateSingle(Request $request, PackageService $packageService)
    {
        if ($packageService->status === 'delete') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update deleted record.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
            'custom_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $packageService->update([
            'quantity' => $request->quantity,
            'custom_price' => $request->custom_price,
            'notes' => $request->notes,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Updated successfully!',
            'data' => $packageService->fresh()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PackageService $packageService)
    {
        if ($packageService->status === 'delete') {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Record already deleted.'
                ], 404);
            }
            abort(404);
        }

        $packageService->update([
            'status' => 'delete',
            'updated_by' => Auth::id(),
        ]);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Service removed from package successfully!'
            ]);
        }

        return redirect()->back()
            ->with('success', 'Service removed from package successfully!');
    }

    /**
     * Bulk assign a service to multiple packages.
     */
    public function bulkAssign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'package_ids' => 'required|array|min:1',
            'package_ids.*' => 'exists:packages,id',
            'quantity' => 'required|integer|min:1',
            'custom_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $service = Service::findOrFail($request->service_id);

        // Check if service is soft deleted
        if ($service->status === 'delete') {
            return redirect()->back()->with('error', 'Cannot assign a deleted service.');
        }

        $assignedCount = 0;

        foreach ($request->package_ids as $packageId) {
            $package = Package::find($packageId);
            
            // Skip if package is soft deleted
            if ($package && $package->status === 'delete') {
                continue;
            }

            // Update or create the assignment
            $packageService = PackageService::updateOrCreate(
                [
                    'package_id' => $packageId,
                    'service_id' => $service->id,
                ],
                [
                    'quantity' => $request->quantity,
                    'custom_price' => $request->custom_price ?? null,
                    'notes' => $request->notes ?? null,
                    'status' => 'active',
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]
            );

            // Only count if it was newly created or reactivated
            if ($packageService->wasRecentlyCreated || $packageService->wasChanged('status')) {
                $assignedCount++;
            }
        }

        return redirect()->back()
            ->with('success', "Service assigned to {$assignedCount} package(s) successfully!");
    }
}