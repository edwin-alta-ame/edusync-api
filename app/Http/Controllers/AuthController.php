<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // Registro de nuevos maestros
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password'))
        ]);

        // Esto crea el vínculo en la tabla 'model_has_roles'
        $user->assignRole('maestro'); 

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user','token'), 201);
    }

    // Login y generación de Token
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        return response()->json([
            'token' => $token,
            'user' => auth('api')->user()
        ]);
    }

    // Cerrar sesión
    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }

    // Obtener los datos del usuario autenticado
    public function me()
    {
        try {
            // auth('api')->user() busca al usuario dueño del token JWT enviado
            if (!$user = auth('api')->user()) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Token inválido'], 401);
        }

        return response()->json($user);
    }
    public function index()
    {
        // Filtrar usuarios que tienen el rol de maestro
        $maestros = User::role('maestro')->get();
        return response()->json($maestros);
    }
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Maestro no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6|confirmed', // Password es opcional en edición
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user->name = $request->get('name');
        $user->email = $request->get('email');

        // Solo actualizamos la contraseña si el admin envió una nueva
        if ($request->filled('password')) {
            $user->password = Hash::make($request->get('password'));
        }

        $user->save();

        return response()->json([
            'message' => 'Maestro actualizado correctamente',
            'user' => $user
        ]);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Maestro no encontrado'], 404);
        }

        // Opcional: Evitar que el admin se borre a sí mismo si compartieran el endpoint
        if (auth('api')->id() == $id) {
            return response()->json(['message' => 'No puedes eliminar tu propia cuenta'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Maestro eliminado correctamente']);
    }
}