<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageService;
use App\Models\Service;
use Illuminate\Http\Request;

class PackageServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = PackageService::with(['package', 'service']);

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
            $query->whereHas('package', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('service', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $packageServices = $query->orderBy('package_id')->orderBy('service_id')->paginate(20)->withQueryString();
        $packages = Package::active()->ordered()->get();
        $services = Service::active()->ordered()->get();

        return view('admin.package-services.index', compact('packageServices', 'packages', 'services'));
    }

    public function assign(Request $request)
    {
        $packages = Package::active()->ordered()->get();
        $services = Service::active()->ordered()->get();
        
        $selectedPackage = null;
        if ($request->filled('package_id')) {
            $selectedPackage = Package::with('packageServices.service')->find($request->package_id);
        }

        return view('admin.package-services.assign', compact('packages', 'services', 'selectedPackage'));
    }

    public function storeAssignment(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'services' => 'required|array|min:1',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.quantity' => 'required|integer|min:1',
            'services.*.custom_price' => 'nullable|numeric|min:0',
            'services.*.notes' => 'nullable|string|max:500',
        ]);

        $package = Package::findOrFail($validated['package_id']);

        // Clear existing assignments for this package
        $package->services()->detach();

        // Add new assignments
        foreach ($validated['services'] as $serviceData) {
            $package->services()->attach($serviceData['service_id'], [
                'quantity' => $serviceData['quantity'],
                'custom_price' => $serviceData['custom_price'] ?? null,
                'notes' => $serviceData['notes'] ?? null,
            ]);
        }

        return redirect()
            ->route('admin.package-services.assign', ['package_id' => $package->id])
            ->with('success', 'Services assigned to package successfully!');
    }

    public function updateSingle(Request $request, PackageService $packageService)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'custom_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $packageService->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Updated successfully!',
            'data' => $packageService->fresh()
        ]);
    }

    public function destroy(PackageService $packageService)
    {
        $packageService->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Service removed from package successfully!'
            ]);
        }

        return redirect()->back()->with('success', 'Service removed from package successfully!');
    }

    public function bulkAssign(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'package_ids' => 'required|array|min:1',
            'package_ids.*' => 'exists:packages,id',
            'quantity' => 'required|integer|min:1',
            'custom_price' => 'nullable|numeric|min:0',
        ]);

        $service = Service::findOrFail($validated['service_id']);
        $assignedCount = 0;

        foreach ($validated['package_ids'] as $packageId) {
            // Check if already assigned
            $exists = PackageService::where('package_id', $packageId)
                                    ->where('service_id', $service->id)
                                    ->exists();
            
            if (!$exists) {
                PackageService::create([
                    'package_id' => $packageId,
                    'service_id' => $service->id,
                    'quantity' => $validated['quantity'],
                    'custom_price' => $validated['custom_price'] ?? null,
                ]);
                $assignedCount++;
            }
        }

        return redirect()->back()->with('success', "Service assigned to {$assignedCount} package(s) successfully!");
    }
}