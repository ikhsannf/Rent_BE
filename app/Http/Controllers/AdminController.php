<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Dispute;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    // ── Dashboard ─────────────────────────────────────────────────────

    public function dashboard()
    {
        $stats = [
            'total_users'          => User::count(),
            'new_users_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
            'total_bookings'       => Booking::count(),
            'ongoing_bookings'     => Booking::where('status', 'ongoing')->count(),
            'total_revenue'        => Booking::where('status', 'completed')->sum('platform_fee'),
            'open_disputes'        => Dispute::where('status', 'open')->count(),
        ];

        $recentBookings = Booking::with(['listing', 'borrower', 'lender'])
            ->latest()->limit(8)->get();

        $openDisputes = Dispute::with(['booking', 'reportedBy', 'reportedUser'])
            ->where('status', 'open')->latest()->limit(5)->get();


        $users = User::latest()->get();
        

        return view('admin.dashboard', compact('stats', 'recentBookings', 'openDisputes', 'users'));
    }

    // ── Categories ────────────────────────────────────────────────────

    public function categoriesIndex()
    {
        $categories = Category::withCount(['listings' => fn($q) => $q->where('status', 'available')])
            ->orderBy('sort_order')->paginate(15);

        return view('admin.categories.index', compact('categories'));
    }

    public function categoriesStore(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'icon'        => 'nullable|string|max:10',
            'color'       => 'nullable|string|max:7',
            'description' => 'nullable|string|max:255',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'nullable',
        ]);

        $data['slug']      = Str::slug($data['name']);
        $data['is_active'] = $request->has('is_active');

        Category::create($data);

        return back()->with('success', "Kategori \"{$data['name']}\" berhasil ditambahkan.");
    }

    public function categoriesUpdate(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'icon'        => 'nullable|string|max:10',
            'color'       => 'nullable|string|max:7',
            'description' => 'nullable|string|max:255',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'nullable',
        ]);

        $data['slug']      = Str::slug($data['name']);
        $data['is_active'] = $request->has('is_active');

        $category->update($data);

        return back()->with('success', "Kategori \"{$category->name}\" berhasil diupdate.");
    }

    public function categoriesDestroy(Category $category)
    {
        if ($category->listings()->exists()) {
            return back()->with('error', 'Tidak bisa menghapus kategori yang masih memiliki listing.');
        }

        $category->delete();

        return back()->with('success', "Kategori \"{$category->name}\" berhasil dihapus.");
    }

    // ── Users ─────────────────────────────────────────────────────────

    public function usersIndex(Request $request)
    {
        $query = User::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->role) {
            $query->where('role', $request->role);
        }

        match ($request->status) {
            'verified'   => $query->where('is_verified', true),
            'unverified' => $query->where('is_verified', false)->where('is_active', true),
            'inactive'   => $query->where('is_active', false),
            default      => null,
        };

        $users = $query->latest()->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function usersVerify(User $user)
    {
        $user->update(['is_verified' => true]);

        return back()->with('success', "Akun {$user->name} berhasil diverifikasi.");
    }

    public function usersToggle(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun {$user->name} berhasil {$status}.");
    }

    // ── Bookings ──────────────────────────────────────────────────────

    public function bookingsIndex(Request $request)
    {
        $query = Booking::with(['listing', 'borrower', 'lender']);

        if ($request->search) {
            $query->where('booking_code', 'like', "%{$request->search}%")
                  ->orWhereHas('borrower', fn($q) => $q->where('name', 'like', "%{$request->search}%"));
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $bookings = $query->latest()->paginate(15);

        return view('admin.bookings.index', compact('bookings'));
    }

    public function bookingsShow(Booking $booking)
    {
        $booking->load(['listing.category', 'borrower', 'lender']);

        return view('admin.bookings.show', compact('booking'));
    }

    // ── Disputes ──────────────────────────────────────────────────────

    public function disputesIndex(Request $request)
    {
        $query = Dispute::with(['booking', 'reportedBy', 'reportedUser']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $disputes = $query->latest()->paginate(15);

        return view('admin.disputes.index', compact('disputes'));
    }

    public function disputesShow(Dispute $dispute)
    {
        $dispute->load(['booking.listing', 'reportedBy', 'reportedUser', 'resolvedBy']);

        return view('admin.disputes.show', compact('dispute'));
    }

    public function disputesResolve(Request $request, Dispute $dispute)
    {
        $data = $request->validate([
            'status'      => 'required|in:investigating,resolved,closed',
            'admin_notes' => 'required|string|max:1000',
            'fine_amount' => 'nullable|numeric|min:0',
        ]);

        $data['resolved_by'] = auth()->id();
        if (in_array($data['status'], ['resolved', 'closed'])) {
            $data['resolved_at'] = now();
        }

        $dispute->update($data);

        return redirect()->route('admin.disputes.index')
            ->with('success', "Dispute berhasil diupdate menjadi \"{$data['status']}\".");
    }

    // ── Notifications ─────────────────────────────────────────────────

    public function notificationsIndex()
    {
        $unverifiedUsers = User::where('is_verified', false)
            ->where('is_active', true)
            ->latest()
            ->get();

        $pendingBookings = Booking::with(['listing', 'borrower'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        $openDisputes = Dispute::with(['reportedBy', 'reportedUser'])
            ->where('status', 'open')
            ->latest()
            ->get();

        $totalCount = $unverifiedUsers->count() + $pendingBookings->count() + $openDisputes->count();

        return view('admin.notifications.index', compact(
            'unverifiedUsers', 'pendingBookings', 'openDisputes', 'totalCount'
        ));
    }

    // ── Auth ──────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}