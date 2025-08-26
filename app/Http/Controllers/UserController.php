<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Filiale;
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;

class UserController extends AdminController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::with('filiale');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        $users = $query->paginate(10);

        // Pour le select filiale (si tu l'utilises encore quelque part)
        $filiales = Filiale::all();

        return view('users.index', compact('users', 'filiales'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $filiales = Filiale::all();
        return view('users.create', compact('filiales'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,user',
            'filiale_id' => 'nullable|exists:filiales,id'
        ]);

        $userData = $request->only(['name', 'email', 'role', 'filiale_id']);
        $userData['password'] = Hash::make($request->password);

        User::create($userData);

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur créé avec succès');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $filiales = Filiale::all(); // Si tu as besoin d'afficher les filiales à modifier
        return view('users.edit', compact('user', 'filiales'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:user,admin',
            'filiale_id' => 'nullable|exists:filiales,id',
        ]);

        // Mise à jour
        $user->update($request->only('name', 'email', 'role', 'filiale_id'));

        // Redirection avec message de succès
        return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour avec succès.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->back()->with('success', 'Utilisateur supprimé avec succès.');
    }

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->isAdmin()) {
                abort(403, 'Accès non autorisé');
            }
            return $next($request);
        });
    }
}
