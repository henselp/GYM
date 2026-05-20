-- =====================================================================
-- GYMFIT - Migración: tabla de progreso
-- =====================================================================

CREATE TABLE IF NOT EXISTS progreso (
    id              SERIAL PRIMARY KEY,
    cliente_id      INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    peso            DECIMAL(5,2),
    altura          DECIMAL(5,2),
    brazo           DECIMAL(4,2),
    cintura         DECIMAL(4,2),
    pierna          DECIMAL(4,2),
    notas           TEXT,
    registrado_en   TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_progreso_cliente ON progreso(cliente_id);

-- Datos demo de progreso
INSERT INTO progreso (cliente_id, peso, altura, brazo, cintura, pierna, notas, registrado_en)
SELECT
    (SELECT id FROM usuarios WHERE email='juanperez@gmail.com'),
    82.5, 178, 36.5, 82, 55, 'Inicio del plan', NOW() - INTERVAL '60 days'
WHERE NOT EXISTS (SELECT 1 FROM progreso WHERE cliente_id = (SELECT id FROM usuarios WHERE email='juanperez@gmail.com'));

INSERT INTO progreso (cliente_id, peso, altura, brazo, cintura, pierna, notas, registrado_en)
SELECT
    (SELECT id FROM usuarios WHERE email='juanperez@gmail.com'),
    80.2, 178, 37.1, 80, 55.5, 'Primer mes: buena evolución', NOW() - INTERVAL '30 days'
WHERE EXISTS (SELECT 1 FROM progreso WHERE cliente_id = (SELECT id FROM usuarios WHERE email='juanperez@gmail.com'));

INSERT INTO progreso (cliente_id, peso, altura, brazo, cintura, pierna, notas, registrado_en)
SELECT
    (SELECT id FROM usuarios WHERE email='juanperez@gmail.com'),
    78.0, 178, 37.8, 78, 56.2, 'Segundo mes: resultados visibles', NOW()
WHERE EXISTS (SELECT 1 FROM progreso WHERE cliente_id = (SELECT id FROM usuarios WHERE email='juanperez@gmail.com'));

-- Más rutinas demo para datos históricos
INSERT INTO rutinas (cliente_id, entrenador_id, contenido, observaciones, asignada_en)
SELECT
    (SELECT id FROM usuarios WHERE email='juanperez@gmail.com'),
    (SELECT id FROM usuarios WHERE email='entrenador@gymfit.com'),
    'Rutina inicial de evaluación',
    'Evaluación física completa',
    NOW() - INTERVAL '90 days'
WHERE NOT EXISTS (
    SELECT 1 FROM rutinas r
    JOIN usuarios u ON u.id = r.cliente_id
    WHERE u.email = 'juanperez@gmail.com' AND r.asignada_en < NOW() - INTERVAL '60 days'
);

INSERT INTO rutinas (cliente_id, entrenador_id, contenido, observaciones, asignada_en)
SELECT
    (SELECT id FROM usuarios WHERE email='juanperez@gmail.com'),
    (SELECT id FROM usuarios WHERE email='entrenador@gymfit.com'),
    'Rutina intermedia - mes 1',
    'Aumentar carga progresivamente',
    NOW() - INTERVAL '60 days'
WHERE EXISTS (
    SELECT 1 FROM rutinas r
    JOIN usuarios u ON u.id = r.cliente_id
    WHERE u.email = 'juanperez@gmail.com' AND r.asignada_en < NOW() - INTERVAL '60 days'
);
