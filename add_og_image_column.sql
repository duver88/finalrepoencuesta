-- Agregar columna 'og_image' a la tabla surveys para Facebook/Open Graph
-- Ejecuta este SQL en tu base de datos de producción

USE pular_pvddad;

-- Agregar columna og_image si no existe
SET @dbname = DATABASE();
SET @tablename = 'surveys';
SET @columnname = 'og_image';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  'SELECT ''Column og_image already exists'' AS result;',
  'ALTER TABLE surveys ADD COLUMN og_image VARCHAR(255) NULL AFTER banner COMMENT ''Imagen para Facebook/Open Graph (1200x630)'';'
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Insertar registro en la tabla migrations si no existe
INSERT IGNORE INTO migrations (migration, batch)
VALUES ('2025_10_24_192632_add_og_image_to_surveys_table', 4);

SELECT 'Columna og_image agregada exitosamente' AS resultado;
