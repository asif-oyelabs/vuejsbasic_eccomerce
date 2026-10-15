<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class EnquirylistController extends Controller
{
    // Read: list all users
  public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $enquiries = Enquiry::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return response()->json($enquiries);
    }
    
public function store(Request $request)
{
    $data = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:enquiries,email'],
        'phone' => ['required', 'string', 'max:255'],
        'subject' => ['required', 'string', 'max:255'],
        'message' => ['required', 'string'],
        'status' => ['required', 'string', 'max:255'],
    ]);

    $enquiry = Enquiry::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'phone' => $data['phone'],
        'subject' => $data['subject'],
        'message' => $data['message'],
        'status' => $data['status'],
    ]);

    return response()->json([
        'message' => 'Enquiry created successfully.',
        'enquiry' => $enquiry->only('id', 'name', 'email', 'phone', 'subject', 'message', 'status'),
    ], 201);
}

public function update(Request $request, Enquiry $enquiry)
{
    $data = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255'],
        'phone' => ['required', 'string', 'max:255'],
        'subject' => ['required', 'string', 'max:255'],
        'message' => ['required', 'string'],
        'status' => ['required', 'string', 'max:255'],
    ]);

    $enquiry->name = $data['name'];
    $enquiry->email = $data['email'];
    $enquiry->phone = $data['phone'];
    $enquiry->subject = $data['subject'];
    $enquiry->message = $data['message'];
    $enquiry->status = $data['status'];

    $enquiry->save();

    return response()->json([
        'message' => 'Enquiry updated successfully.',
        'enquiry' => $enquiry->only('id', 'name', 'email', 'phone', 'subject', 'message', 'status'),
    ]);
}


    // Read: view one user
    public function show(Enquiry $enquiry)
    {
        return response()->json([
            'enquiry' => $enquiry->only('id', 'name', 'email', 'phone', 'subject', 'message', 'status'),
        ]);
    }

 
    // Delete: remove a user
    public function destroy(Enquiry $enquiry)
    {
        $enquiry->delete();

        return response()->json([
            'message' => 'Enquiry deleted successfully.',
        ]);
    }
}
