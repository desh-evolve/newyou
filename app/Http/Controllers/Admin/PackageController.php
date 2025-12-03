<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $query = Package::withCount('services');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Featured Filter
        if ($request->filled('featured')) {
            $query->where('is_featured', $request->featured);
        }

        // Sorting
        $sortField = $request->get('sort', 'sort_order');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $packages = $query->paginate(15)->withQueryString();

        return view('admin.packages.index', compact('packages'));
    }

    public function create()
    {
        $services = Service::active()->ordered()->get();
        return view('admin.packages.create', compact('services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:packages,slug',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'validity_days' => 'nullable|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|in:active,inactive',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'services' => 'nullable|array',
            'services.*.id' => 'exists:services,id',
            'services.*.quantity' => 'integer|min:1',
            'services.*.custom_price' => 'nullable|numeric|min:0',
            'services.*.notes' => 'nullable|string|max:500',
        ]);

        // Handle slug
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        
        // Ensure unique slug
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Package::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter++;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('packages', 'public');
        }

        $validated['is_featured'] = $request->boolean('is_featured');

        $package = Package::create(collect($validated)->except('services')->toArray());

        // Attach services
        if (!empty($validated['services'])) {
            foreach ($validated['services'] as $serviceData) {
                $package->services()->attach($serviceData['id'], [
                    'quantity' => $serviceData['quantity'] ?? 1,
                    'custom_price' => $serviceData['custom_price'] ?? null,
                    'notes' => $serviceData['notes'] ?? null,
                ]);
            }
        }

        return redirect()
            ->route('admin.packages.index')
            ->with('success', 'Package created successfully!');
    }

    public function show(Package $package)
    {
        $package->load(['services', 'packageServices.service']);
        return view('admin.packages.show', compact('package'));
    }

    public function edit(Package $package)
    {
        $services = Service::active()->ordered()->get();
        $package->load('packageServices');
        return view('admin.packages.edit', compact('package', 'services'));
    }

    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('packages')->ignore($package->id)],
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'validity_days' => 'nullable|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|in:active,inactive',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'services' => 'nullable|array',
            'services.*.id' => 'exists:services,id',
            'services.*.quantity' => 'integer|min:1',
            'services.*.custom_price' => 'nullable|numeric|min:0',
            'services.*.notes' => 'nullable|string|max:500',
        ]);

        // Handle slug
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            if ($package->image) {
                Storage::disk('public')->delete($package->image);
            }
            $validated['image'] = $request->file('image')->store('packages', 'public');
        }

        // Handle image removal
        if ($request->has('remove_image') && $request->remove_image) {
            if ($package->image) {
                Storage::disk('public')->delete($package->image);
            }
            $validated['image'] = null;
        }

        $validated['is_featured'] = $request->boolean('is_featured');

        $package->update(collect($validated)->except('services')->toArray());

        // Sync services
        $package->services()->detach();
        if (!empty($validated['services'])) {
            foreach ($validated['services'] as $serviceData) {
                $package->services()->attach($serviceData['id'], [
                    'quantity' => $serviceData['quantity'] ?? 1,
                    'custom_price' => $serviceData['custom_price'] ?? null,
                    'notes' => $serviceData['notes'] ?? null,
                ]);
            }
        }

        return redirect()
            ->route('admin.packages.index')
            ->with('success', 'Package updated successfully!');
    }

    public function destroy(Package $package)
    {
        // Delete image
        if ($package->image) {
            Storage::disk('public')->delete($package->image);
        }

        // Detach all services
        $package->services()->detach();

        $package->delete();

        return redirect()
            ->route('admin.packages.index')
            ->with('success', 'Package deleted successfully!');
    }

    public function toggleStatus(Package $package)
    {
        $package->update([
            'status' => $package->status === 'active' ? 'inactive' : 'active'
        ]);

        return response()->json([
            'success' => true,
            'status' => $package->status,
            'message' => 'Status updated successfully!'
        ]);
    }

    public function toggleFeatured(Package $package)
    {
        $package->update([
            'is_featured' => !$package->is_featured
        ]);

        return response()->json([
            'success' => true,
            'is_featured' => $package->is_featured,
            'message' => 'Featured status updated successfully!'
        ]);
    }
}