-- Agregar columna 'color' a la tabla question_options
-- Ejecuta este SQL en tu base de datos de producción

USE pular_pvddad;

-- Verificar si la columna ya existe antes de agregarla
SET @dbname = DATABASE();
SET @tablename = 'question_options';
SET @columnname = 'color';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  'SELECT ''Column already exists'' AS result;',
  'ALTER TABLE question_options ADD COLUMN color VARCHAR(7) NULL AFTER option_text;'
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Insertar registro en la tabla migrations si no existe
INSERT IGNORE INTO migrations (migration, batch)
VALUES ('2025_10_24_182130_add_color_to_question_options_table', 2);

SELECT 'Migración completada exitosamente' AS resultado;
