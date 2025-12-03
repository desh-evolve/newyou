<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::query();

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

        // Sorting
        $sortField = $request->get('sort', 'sort_order');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $services = $query->paginate(15)->withQueryString();

        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:services,slug',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'nullable|integer|min:1',
            'icon' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Handle slug
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        
        // Ensure unique slug
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Service::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter++;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('services', 'public');
        }

        $service = Service::create($validated);

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Service created successfully!');
    }

    public function show(Service $service)
    {
        $service->load('packages');
        return view('admin.services.show', compact('service'));
    }

    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('services')->ignore($service->id)],
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'nullable|integer|min:1',
            'icon' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Handle slug
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }
            $validated['image'] = $request->file('image')->store('services', 'public');
        }

        // Handle image removal
        if ($request->has('remove_image') && $request->remove_image) {
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }
            $validated['image'] = null;
        }

        $service->update($validated);

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Service updated successfully!');
    }

    public function destroy(Service $service)
    {
        // Check if service is assigned to any packages
        if ($service->packages()->count() > 0) {
            return redirect()
                ->route('admin.services.index')
                ->with('error', 'Cannot delete service. It is assigned to ' . $service->packages()->count() . ' package(s).');
        }

        // Delete image
        if ($service->image) {
            Storage::disk('public')->delete($service->image);
        }

        $service->delete();

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Service deleted successfully!');
    }

    public function toggleStatus(Service $service)
    {
        $service->update([
            'status' => $service->status === 'active' ? 'inactive' : 'active'
        ]);

        return response()->json([
            'success' => true,
            'status' => $service->status,
            'message' => 'Status updated successfully!'
        ]);
    }
}