<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::where('user_id', Auth::id())
            ->active()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('client.testimonials.index', compact('testimonials'));
    }

    public function create()
    {
        return view('client.testimonials.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'testimonial' => 'required|string|min:50',
            'rating' => 'required|integer|min:1|max:5',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
        $data['approval_status'] = 'pending';
        $data['status'] = 'active';

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $data['image'] = $image->storeAs('testimonials', $imageName, 'public');
        }

        Testimonial::create($data);

        return redirect()->route('client.testimonials.index')
            ->with('success', 'Testimonial submitted successfully and is pending approval.');
    }

    public function edit(Testimonial $testimonial)
    {
        // Check if user owns this testimonial
        if ($testimonial->user_id !== Auth::id() || $testimonial->status === 'delete') {
            abort(403, 'Unauthorized action.');
        }

        // Clients can only edit pending or rejected testimonials
        if (!in_array($testimonial->approval_status, ['pending', 'rejected'])) {
            return redirect()->route('client.testimonials.index')
                ->with('error', 'You can only edit pending or rejected testimonials.');
        }

        return view('client.testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        // Check if user owns this testimonial
        if ($testimonial->user_id !== Auth::id() || $testimonial->status === 'delete') {
            abort(403, 'Unauthorized action.');
        }

        // Clients can only edit pending or rejected testimonials
        if (!in_array($testimonial->approval_status, ['pending', 'rejected'])) {
            return redirect()->route('client.testimonials.index')
                ->with('error', 'You can only edit pending or rejected testimonials.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'testimonial' => 'required|string|min:50',
            'rating' => 'required|integer|min:1|max:5',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except('image');
        $data['updated_by'] = Auth::id();
        $data['approval_status'] = 'pending';

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

        return redirect()->route('client.testimonials.index')
            ->with('success', 'Testimonial updated and resubmitted for approval.');
    }

    public function destroy(Testimonial $testimonial)
    {
        // Check if user owns this testimonial
        if ($testimonial->user_id !== Auth::id() || $testimonial->status === 'delete') {
            abort(403, 'Unauthorized action.');
        }

        $testimonial->softDelete(Auth::id());

        return redirect()->route('client.testimonials.index')
            ->with('success', 'Testimonial deleted successfully.');
    }
}