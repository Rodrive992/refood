<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Local;

class LocalesYUsuariosSeeder extends Seeder
{
    public function run(): void
    {
        // LOCAL 1: La Piscala
        $lapiscala = Local::create([
            'nombre'    => 'lapiscala',
            'direccion' => null,
            'telefono'  => null,
            'activo'    => 1,
        ]);

        User::create([
            'id_local' => $lapiscala->id,
            'name'     => 'lpadmin',
            'email'    => 'lpadmin@refood.com',
            'role'     => 'admin',
            'password' => 'admin',
        ]);

        User::create([
            'id_local' => $lapiscala->id,
            'name'     => 'lpmozo1',
            'email'    => 'lpmozo1@refood.com',
            'role'     => 'mozo',
            'password' => 'mozo',
        ]);

        // LOCAL 2: Andiamo
        $andiamo = Local::create([
            'nombre'    => 'andiamo',
            'direccion' => null,
            'telefono'  => null,
            'activo'    => 1,
        ]);

        User::create([
            'id_local' => $andiamo->id,
            'name'     => 'andadmin',
            'email'    => 'andadmin@refood.com',
            'role'     => 'admin',
            'password' => 'admin',
        ]);

        User::create([
            'id_local' => $andiamo->id,
            'name'     => 'andmozo1',
            'email'    => 'andmozo1@refood.com',
            'role'     => 'mozo',
            'password' => 'mozo',
        ]);
    }
}
