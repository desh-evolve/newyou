<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PackageController extends Controller
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
        $query = Package::where('status', '!=', 'delete');

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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $services = Service::where('status', 'active')->orderBy('sort_order')->get();
        return view('admin.packages.create', compact('services'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'validity_days' => 'nullable|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
            'services' => 'nullable|array',
            'services.*.id' => 'exists:services,id',
            'services.*.quantity' => 'integer|min:1',
            'services.*.custom_price' => 'nullable|numeric|min:0',
            'services.*.notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Handle slug
        $slug = $request->slug ?? Str::slug($request->name);
        $slug = $this->generateUniqueSlug($slug);

        $data = [
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'validity_days' => $request->validity_days,
            'status' => $request->status,
            'is_featured' => $request->has('is_featured') ? 1 : 0,
            'sort_order' => $request->sort_order ?? 0,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('packages', 'public');
        }

        $package = Package::create($data);

        // Attach services
        if ($request->has('services') && is_array($request->services)) {
            foreach ($request->services as $serviceData) {
                $package->services()->attach($serviceData['id'], [
                    'quantity' => $serviceData['quantity'] ?? 1,
                    'custom_price' => $serviceData['custom_price'] ?? null,
                    'notes' => $serviceData['notes'] ?? null,
                ]);
            }
        }

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Package $package)
    {
        if ($package->status === 'delete') {
            abort(404);
        }

        $package->load(['services', 'packageServices.service']);
        return view('admin.packages.show', compact('package'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Package $package)
    {
        if ($package->status === 'delete') {
            abort(404);
        }

        $services = Service::where('status', 'active')->orderBy('sort_order')->get();
        $package->load('packageServices');
        return view('admin.packages.edit', compact('package', 'services'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Package $package)
    {
        if ($package->status === 'delete') {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'validity_days' => 'nullable|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
            'services' => 'nullable|array',
            'services.*.id' => 'exists:services,id',
            'services.*.quantity' => 'integer|min:1',
            'services.*.custom_price' => 'nullable|numeric|min:0',
            'services.*.notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Handle slug
        $slug = $request->slug ?? Str::slug($request->name);
        if ($slug !== $package->slug) {
            $slug = $this->generateUniqueSlug($slug, $package->id);
        }

        $data = [
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'validity_days' => $request->validity_days,
            'status' => $request->status,
            'is_featured' => $request->has('is_featured') ? 1 : 0,
            'sort_order' => $request->sort_order ?? 0,
            'updated_by' => Auth::id(),
        ];

        // Handle image removal
        if ($request->has('remove_image') && $package->image) {
            Storage::disk('public')->delete($package->image);
            $data['image'] = null;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            if ($package->image) {
                Storage::disk('public')->delete($package->image);
            }
            $data['image'] = $request->file('image')->store('packages', 'public');
        }

        $package->update($data);

        // Sync services
        $package->services()->detach();
        if ($request->has('services') && is_array($request->services)) {
            foreach ($request->services as $serviceData) {
                $package->services()->attach($serviceData['id'], [
                    'quantity' => $serviceData['quantity'] ?? 1,
                    'custom_price' => $serviceData['custom_price'] ?? null,
                    'notes' => $serviceData['notes'] ?? null,
                ]);
            }
        }

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Package $package)
    {
        if ($package->status === 'delete') {
            abort(404);
        }

        $package->update([
            'status' => 'delete',
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package deleted successfully.');
    }

    /**
     * Toggle package active/inactive status.
     */
    public function toggleStatus(Package $package)
    {
        if ($package->status === 'delete') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update deleted package.'
            ], 404);
        }

        $package->update([
            'status' => $package->status === 'active' ? 'inactive' : 'active',
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'status' => $package->status,
            'message' => 'Status updated successfully!'
        ]);
    }

    /**
     * Toggle package featured status.
     */
    public function toggleFeatured(Package $package)
    {
        if ($package->status === 'delete') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update deleted package.'
            ], 404);
        }

        $package->update([
            'is_featured' => !$package->is_featured,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'is_featured' => $package->is_featured,
            'message' => 'Featured status updated successfully!'
        ]);
    }

    /**
     * Generate unique slug for package.
     */
    private function generateUniqueSlug(string $slug, ?int $excludeId = null): string
    {
        $original = $slug;
        $count = 1;

        while (true) {
            $query = Package::where('slug', $slug)->where('status', '!=', 'delete');
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            if (!$query->exists()) {
                break;
            }
            $slug = $original . '-' . $count++;
        }

        return $slug;
    }
}