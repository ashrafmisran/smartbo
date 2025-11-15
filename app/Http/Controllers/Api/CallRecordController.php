<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CallRecordController extends Controller
{
    /**
     * Display a listing of call records.
     */
    public function index(Request $request)
    {
        try {
            $query = DB::table('call_records')
                ->join('users', 'call_records.user_id', '=', 'users.id')
                ->select(
                    'call_records.*',
                    'users.name as user_name'
                )
                ->orderBy('called_at', 'desc');
            
            // Filter by pengundi_ic if provided
            if ($request->has('pengundi_ic')) {
                $query->where('pengundi_ic', $request->pengundi_ic);
            }
            
            // Filter by user_id if provided
            if ($request->has('user_id')) {
                $query->where('call_records.user_id', $request->user_id);
            }
            
            // Filter by date range if provided
            if ($request->has('date_from')) {
                $query->whereDate('called_at', '>=', $request->date_from);
            }
            
            if ($request->has('date_to')) {
                $query->whereDate('called_at', '<=', $request->date_to);
            }
            
            $records = $query->paginate(50);
            
            return response()->json([
                'success' => true,
                'data' => $records
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve call records',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created call record.
     */
    public function store(Request $request)
    {
        try {
            // Validate the incoming request
            $validated = $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'pengundi_ic' => 'required|string|max:12',
                'phone_number' => 'required|string|max:20',
                'kod_cula' => 'nullable|string|max:2|in:VA,VB,VC,VD,VN,VS,VT,VR,VW,VX,VY,VZ',
                'notes' => 'nullable|string|max:1000',
            ]);
            
            // Insert the call record
            $recordId = DB::table('call_records')->insertGetId([
                'user_id' => $validated['user_id'],
                'pengundi_ic' => $validated['pengundi_ic'],
                'phone_number' => $validated['phone_number'],
                'kod_cula' => $validated['kod_cula'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'called_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Retrieve the created record with user info
            $record = DB::table('call_records')
                ->join('users', 'call_records.user_id', '=', 'users.id')
                ->where('call_records.id', $recordId)
                ->select(
                    'call_records.*',
                    'users.name as user_name'
                )
                ->first();
            
            return response()->json([
                'success' => true,
                'message' => 'Call record created successfully',
                'data' => $record
            ], 201);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create call record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified call record.
     */
    public function show(string $id)
    {
        try {
            $record = DB::table('call_records')
                ->join('users', 'call_records.user_id', '=', 'users.id')
                ->where('call_records.id', $id)
                ->select(
                    'call_records.*',
                    'users.name as user_name'
                )
                ->first();
            
            if (!$record) {
                return response()->json([
                    'success' => false,
                    'message' => 'Call record not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $record
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve call record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified call record.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Validate the incoming request
            $validated = $request->validate([
                'kod_cula' => 'nullable|string|max:2|in:VA,VB,VC,VD,VN,VS,VT,VR,VW,VX,VY,VZ',
                'notes' => 'nullable|string|max:1000',
            ]);
            
            // Check if record exists
            $exists = DB::table('call_records')->where('id', $id)->exists();
            if (!$exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Call record not found'
                ], 404);
            }
            
            // Update the record
            $updateData = array_filter([
                'kod_cula' => $validated['kod_cula'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'updated_at' => now(),
            ], function ($value) {
                return $value !== null;
            });
            
            DB::table('call_records')
                ->where('id', $id)
                ->update($updateData);
            
            // Retrieve the updated record
            $record = DB::table('call_records')
                ->join('users', 'call_records.user_id', '=', 'users.id')
                ->where('call_records.id', $id)
                ->select(
                    'call_records.*',
                    'users.name as user_name'
                )
                ->first();
            
            return response()->json([
                'success' => true,
                'message' => 'Call record updated successfully',
                'data' => $record
            ], 200);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update call record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified call record.
     */
    public function destroy(string $id)
    {
        try {
            // Check if record exists
            $exists = DB::table('call_records')->where('id', $id)->exists();
            if (!$exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Call record not found'
                ], 404);
            }
            
            // Delete the record
            DB::table('call_records')->where('id', $id)->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Call record deleted successfully'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete call record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get call statistics
     */
    public function statistics(Request $request)
    {
        try {
            $query = DB::table('call_records');
            
            // Apply date filters if provided
            if ($request->has('date_from')) {
                $query->whereDate('called_at', '>=', $request->date_from);
            }
            
            if ($request->has('date_to')) {
                $query->whereDate('called_at', '<=', $request->date_to);
            }
            
            // Total calls
            $totalCalls = $query->count();
            
            // Calls by user
            $callsByUser = DB::table('call_records')
                ->join('users', 'call_records.user_id', '=', 'users.id')
                ->select('users.name', DB::raw('count(*) as total_calls'))
                ->groupBy('users.id', 'users.name')
                ->orderBy('total_calls', 'desc')
                ->get();
            
            // Calls by cula code
            $callsByCula = DB::table('call_records')
                ->whereNotNull('kod_cula')
                ->select('kod_cula', DB::raw('count(*) as total_calls'))
                ->groupBy('kod_cula')
                ->orderBy('total_calls', 'desc')
                ->get();
            
            // Daily call counts (last 7 days)
            $dailyCalls = DB::table('call_records')
                ->select(DB::raw('DATE(called_at) as date'), DB::raw('count(*) as total_calls'))
                ->where('called_at', '>=', now()->subDays(7))
                ->groupBy(DB::raw('DATE(called_at)'))
                ->orderBy('date', 'asc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_calls' => $totalCalls,
                    'calls_by_user' => $callsByUser,
                    'calls_by_cula' => $callsByCula,
                    'daily_calls' => $dailyCalls,
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
