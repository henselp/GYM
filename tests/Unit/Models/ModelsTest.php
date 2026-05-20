<?php

declare(strict_types=1);

namespace Gymfit\Tests\Unit\Models;

use Gymfit\Models\Usuario;
use Gymfit\Models\Rutina;
use Gymfit\Models\Contacto;
use Gymfit\Models\Mensaje;
use Gymfit\Models\Progreso;
use PHPUnit\Framework\TestCase;

class ModelsTest extends TestCase
{
    public function testUsuarioFromRow(): void
    {
        $user = Usuario::fromRow([
            'id' => '1',
            'nombre' => 'Juan',
            'email' => 'juan@test.com',
            'rol' => 'entrenador',
            'edad' => '30',
            'objetivo' => 'Fitness',
            'nivel' => 'Avanzado',
        ]);

        $this->assertEquals(1, $user->id);
        $this->assertEquals('Juan', $user->nombre);
        $this->assertTrue($user->isTrainer());
        $this->assertFalse($user->isClient());
        $this->assertEquals('Fitness', $user->objetivo);
    }

    public function testUsuarioToPublicArray(): void
    {
        $user = new Usuario(id: 1, nombre: 'Ana', email: 'ana@test.com', rol: 'cliente');
        $arr = $user->toPublicArray();
        $this->assertArrayHasKey('nombre', $arr);
        $this->assertArrayNotHasKey('password_hash', $arr);
    }

    public function testRutinaFromRow(): void
    {
        $r = Rutina::fromRow([
            'id' => '1',
            'cliente_id' => '2',
            'entrenador_id' => '1',
            'contenido' => 'Rutina A',
            'observaciones' => 'Notas',
        ]);
        $this->assertEquals(1, $r->id);
        $this->assertEquals('Rutina A', $r->contenido);
    }

    public function testContactoFromRow(): void
    {
        $c = Contacto::fromRow([
            'id' => '1',
            'nombre' => 'Test',
            'email' => 'test@test.com',
            'mensaje' => 'Hola',
        ]);
        $this->assertEquals('Test', $c->nombre);
    }

    public function testMensajeFromRow(): void
    {
        $m = Mensaje::fromRow([
            'id' => '1',
            'de_usuario_id' => '1',
            'para_usuario_id' => '2',
            'contenido' => 'Hola',
            'leido' => '0',
        ]);
        $this->assertEquals(1, $m->deUsuarioId);
        $this->assertFalse($m->leido);
    }

    public function testProgresoFromRow(): void
    {
        $p = Progreso::fromRow([
            'id' => '1',
            'cliente_id' => '2',
            'peso' => '80.5',
            'altura' => '178',
        ]);
        $this->assertEquals(80.5, $p->peso);
        $this->assertEquals(178.0, $p->altura);
    }
}
