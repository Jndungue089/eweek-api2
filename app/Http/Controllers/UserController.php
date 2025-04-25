<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //
    public function index()
    {
        $users = User::all();
        if (!$users) {
            return response()->json(['message' => 'Não há usuários cadastrados'], 200);
        }
        return response()->json($users, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fullName' => 'required|string|max:255',
            'age_range' => 'required|string|max:255',
            'course' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'grade' => 'required|string|max:255',
            'school' => 'required|string|max:255',
            'phone' => 'required|integer',
            'password' => 'required|string|min:8',
            'profile' => 'required|string|in:admin,normal',
            'gender' => 'required|string|in:Masculino,Feminino',
        ]);

        $user = User::create([
            'fullName' => $validated['fullName'],
            'age_range' => $validated['age_range'],
            'course' => $validated['course'],
            'email' => $validated['email'],
            'grade' => $validated['grade'],
            'phone' => $validated['phone'],
            'school' => $validated['school'],
            'profile' => $validated['profile'],
            'gender' => $validated['gender'],
            'password' => Hash::make($validated['password'])
        ]);

        return response()->json(['message' => 'Usuário criado com sucesso!', "user" => $user], 202);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'fullName' => 'required|string|max:255',
            'age_range' => 'required|string|max:255',
            'course' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'grade' => 'required|string|max:255',
            'school' => 'required|string|max:255',
            'phone' => 'required|integer',
            'password' => 'required|string|min:8',
            'profile' => 'required|string|in:admin,normal',
            'gender' => 'required|string|in:Masculino,Feminino',
        ]);
        $user->update(array_filter([
            'fullName' => $validated['fullName'],
            'age_range' => $validated['age_range'],
            'course' => $validated['course'],
            'email' => $validated['email'],
            'grade' => $validated['grade'],
            'phone' => $validated['phone'],
            'school' => $validated['school'],
            'profile' => $validated['profile'],
            'gender' => $validated['gender'],
            'password' => Hash::make($validated['password'])
        ]));
        return response()->json(['message' => 'Usuário atualizdado com sucesso!', 'user' => $user]);
    }
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'Usuário deletado com sucesso']);
    }
    public function getUser($id)
    {
        // Busca o usuário pelo ID
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }

        // Retorna apenas os dados necessários
        return response()->json(['user' => $user]);
    }
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Credenciais erradas. Tente novamente!'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'Login feito com Sucesso!',
            'user' => $user,
            'token' => $token
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        if (!$query || strlen($query) < 3) {
            return response()->json(['message' => 'A consulta deve ter pelo menos 3 caracteres.'], 422);
        }

        $users = User::where('fullName', 'like', '%' . $query . '%')
            ->orWhere('email', 'like', '%' . $query . '%')
            ->limit(10)
            ->get(['id', 'fullName', 'email']); // Retorna só os campos necessários

        return response()->json($users);
    }
    
    public function logout(Request $request)
    {
        // Verifica se o usuário está autenticado
        if ($request->user()) {
            // Revoga o token atual do usuário autenticado
            $request->user()->currentAccessToken()->delete();

            return response()->json(['message' => 'Logout realizado com sucesso!'], 200);
        }

        // Caso não haja um usuário autenticado, retornar mensagem genérica
        return response()->json(['message' => $request], 200);
    }
}
