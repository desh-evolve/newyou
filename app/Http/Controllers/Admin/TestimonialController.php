<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        $query = Testimonial::with(['user', 'approvedByUser'])->active();

        // Filter by approval status
        if ($request->has('approval_status') && $request->approval_status != '') {
            $query->where('approval_status', $request->approval_status);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")
                  ->orWhere('testimonial', 'like', "%{$search}%");
            });
        }

        $testimonials = $query->ordered()->paginate(15);

        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function create()
    {
        return view('admin.testimonials.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'testimonial' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'approval_status' => 'required|in:pending,approved,rejected',
            'show_on_website' => 'nullable|boolean',
            'display_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except('image');
        $data['user_id'] = Auth::id();
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();
        $data['show_on_website'] = $request->has('show_on_website') ? 1 : 0;

        if ($request->approval_status === 'approved') {
            $data['approved_at'] = now();
            $data['approved_by'] = Auth::id();
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $data['image'] = $image->storeAs('testimonials', $imageName, 'public');
        }

        Testimonial::create($data);

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial created successfully.');
    }

    public function edit(Testimonial $testimonial)
    {
        if ($testimonial->status === 'delete') {
            abort(404);
        }

        return view('admin.testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        if ($testimonial->status === 'delete') {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'testimonial' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'approval_status' => 'required|in:pending,approved,rejected',
            'show_on_website' => 'nullable|boolean',
            'display_order' => 'nullable|integer',
            'admin_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except('image');
        $data['updated_by'] = Auth::id();
        $data['show_on_website'] = $request->has('show_on_website') ? 1 : 0;

        // Set approval data
        if ($request->approval_status === 'approved' && $testimonial->approval_status !== 'approved') {
            $data['approved_at'] = now();
            $data['approved_by'] = Auth::id();
        }

        // If rejected, hide from website
        if ($request->approval_status === 'rejected') {
            $data['show_on_website'] = 0;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($testimonial->image) {
                Storage::disk('public')->delete($testimonial->image);
            }

            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $data['image'] = $image->storeAs('testimonials', $imageName, 'public');
        }

        $testimonial->update($data);

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial updated successfully.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->softDelete(Auth::id());

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial deleted successfully.');
    }

    public function approve(Testimonial $testimonial)
    {
        if ($testimonial->status === 'delete') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot approve deleted testimonial.'
            ], 400);
        }

        $testimonial->approve(Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Testimonial approved successfully.'
        ]);
    }

    public function reject(Request $request, Testimonial $testimonial)
    {
        if ($testimonial->status === 'delete') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot reject deleted testimonial.'
            ], 400);
        }

        $testimonial->reject(Auth::id(), $request->admin_notes);

        return response()->json([
            'success' => true,
            'message' => 'Testimonial rejected successfully.'
        ]);
    }

    public function toggleVisibility(Testimonial $testimonial)
    {
        if ($testimonial->status === 'delete') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot toggle visibility of deleted testimonial.'
            ], 400);
        }

        if ($testimonial->approval_status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Only approved testimonials can be shown on website.'
            ], 400);
        }

        $testimonial->toggleWebsiteVisibility(Auth::id());

        return response()->json([
            'success' => true,
            'show_on_website' => $testimonial->show_on_website,
            'message' => 'Visibility updated successfully.'
        ]);
    }

    public function updateOrder(Request $request)
    {
        $orders = $request->orders;

        foreach ($orders as $order) {
            Testimonial::where('id', $order['id'])
                ->where('status', 'active')
                ->update([
                    'display_order' => $order['order'],
                    'updated_by' => Auth::id(),
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Display order updated successfully.'
        ]);
    }
}