<?php

namespace App\Http\Controllers;

use App\Services\FirestoreService;
use Illuminate\Http\Request;

class FirebaseAdminController extends Controller
{
    protected FirestoreService $firestore;

    public function __construct(FirestoreService $firestore)
    {
        $this->firestore = $firestore;
    }

    // ── Dashboard ─────────────────────────────────────

    public function dashboard()
    {
        $users = $this->firestore->getAll('users');
        $listings = $this->firestore->getAll('listings');
        $bookings = $this->firestore->getAll('bookings');

        $listingsAktif = count(array_filter($listings, fn($l) => ($l['status'] ?? '') === 'Aktif' || ($l['status'] ?? '') === 'Available'));
        $bookingsMenunggu = count(array_filter($bookings, fn($b) => ($b['status'] ?? '') === 'Menunggu' || ($b['status'] ?? '') === 'pending'));
        $bookingsAktif = count(array_filter($bookings, fn($b) => ($b['status'] ?? '') === 'Disewa' || ($b['status'] ?? '') === 'active'));

        $totalRevenue = 0;
        foreach ($bookings as $b) {
            $p = str_replace(['.', ','], '', $b['totalPrice'] ?? '0');
            $totalRevenue += ($b['status'] === 'Selesai' || $b['status'] === 'completed') ? (float)$p : 0;
        }

        $stats = [
            'total_users'       => count($users),
            'total_listings'    => count($listings),
            'total_bookings'    => count($bookings),
            'total_revenue'     => $totalRevenue,
            'active_listings'   => $listingsAktif,
            'pending_bookings'  => $bookingsMenunggu,
        ];

        $recentBookings = array_slice(array_reverse($bookings), 0, 8);

        return view('admin.dashboard', compact('stats', 'recentBookings', 'users'));
    }

    // ── Users ──────────────────────────────────────────

    public function users(Request $request)
    {
        $allUsers = $this->firestore->getAll('users');

        $search = $request->get('search');
        $role = $request->get('role');

        $users = array_filter($allUsers, function ($u) use ($search, $role) {
            if ($search) {
                $q = strtolower($search);
                $name = strtolower($u['name'] ?? '');
                $email = strtolower($u['email'] ?? '');
                if (!str_contains($name, $q) && !str_contains($email, $q)) {
                    return false;
                }
            }
            if ($role && ($u['role'] ?? '') !== $role) {
                return false;
            }
            return true;
        });

        $users = array_values($users);

        return view('admin.users', compact('users', 'search', 'role'));
    }

    public function showUser(string $userId)
    {
        $user = $this->firestore->get('users', $userId);
        if (!$user) {
            return back()->with('error', 'User tidak ditemukan.');
        }

        $allBookings = $this->firestore->getAll('bookings');
        $userBookings = array_filter($allBookings, function ($b) use ($userId) {
            return ($b['borrowerId'] ?? '') === $userId || ($b['lenderId'] ?? '') === $userId;
        });
        $userBookings = array_values($userBookings);

        return view('admin.user-detail', compact('user', 'userBookings'));
    }

    // ── Listings ───────────────────────────────────────

    public function listings(Request $request)
    {
        $allListings = $this->firestore->getAll('listings');

        $status = $request->get('status');
        $search = $request->get('search');

        $listings = array_filter($allListings, function ($l) use ($status, $search) {
            if ($status) {
                $s = $l['status'] ?? '';
                if ($status === 'aktif' && !in_array(strtolower($s), ['aktif', 'available'])) return false;
                if ($status === 'rented' && strtolower($s) !== 'rented') return false;
                if ($status === 'nonaktif' && !in_array(strtolower($s), ['nonaktif', 'inactive'])) return false;
            }
            if ($search) {
                $q = strtolower($search);
                if (!str_contains(strtolower($l['title'] ?? ''), $q)) return false;
            }
            return true;
        });

        $listings = array_values($listings);

        return view('admin.listings', compact('listings', 'status', 'search'));
    }

    public function deleteListing(string $listingId)
    {
        $this->firestore->delete('listings', $listingId);
        return back()->with('success', 'Barang berhasil dihapus.');
    }

    // ── Bookings ───────────────────────────────────────

    public function bookings(Request $request)
    {
        $allBookings = $this->firestore->getAll('bookings');

        $status = $request->get('status');
        $search = $request->get('search');

        $bookings = array_filter($allBookings, function ($b) use ($status, $search) {
            if ($status) {
                $s = strtolower($b['status'] ?? '');
                $t = strtolower($status);
                if ($t === 'menunggu' && $s !== 'menunggu' && $s !== 'pending') return false;
                elseif ($t === 'disewa' && $s !== 'disewa' && $s !== 'ongoing' && $s !== 'active') return false;
                elseif ($t === 'selesai' && $s !== 'selesai' && $s !== 'completed') return false;
                elseif ($t === 'dibatalkan' && $s !== 'dibatalkan' && $s !== 'cancelled') return false;
                elseif (!in_array($t, ['menunggu', 'disewa', 'selesai', 'dibatalkan']) && $s !== $t) return false;
            }
            if ($search) {
                $q = strtolower($search);
                $title = strtolower($b['listingTitle'] ?? '');
                $code = strtolower($b['bookingCode'] ?? '');
                if (!str_contains($title, $q) && !str_contains($code, $q)) return false;
            }
            return true;
        });

        $bookings = array_values($bookings);

        $users = [];
        foreach ($this->firestore->getAll('users') as $u) {
            $users[$u['id']] = $u;
        }
        $listingsMap = [];
        foreach ($this->firestore->getAll('listings') as $l) {
            $listingsMap[$l['id']] = $l;
        }

        return view('admin.bookings', compact('bookings', 'status', 'search', 'users', 'listingsMap'));
    }

    public function updateBookingStatus(Request $request, string $bookingId)
    {
        $data = $request->validate([
            'status' => 'required|in:Menunggu,Disewa,Selesai,Dibatalkan,pending,approved,rejected,ongoing,completed,cancelled',
        ]);

        $data['updated_at'] = now()->toIso8601String();
        $this->firestore->set('bookings', $bookingId, $data);

        return back()->with('success', 'Status transaksi berhasil diupdate.');
    }
}
