<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. LIMPIAR LA CACHÉ DE ROLES Y PERMISOS (Agrégalo aquí)
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Crear los permisos
        $permissions = [
            Permission::updateOrCreate(['name' => 'register-maestro']),
            Permission::updateOrCreate(['name' => 'view-maestros']),
            Permission::updateOrCreate(['name' => 'edit-maestros']),
            Permission::updateOrCreate(['name' => 'delete-maestros']),
        ];
        
        // 3. Crear el rol de Admin y asignarle todos los permisos
        $adminRole = Role::updateOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions($permissions);

        // 4. Crear el rol de Maestro
        Role::updateOrCreate(['name' => 'maestro']);

        // 5. Asignar el rol al usuario
        /*$user = User::where('email', 'edwin@edusync.com')->first();
        if ($user) {
            $user->assignRole($adminRole);
        }*/
    }
}