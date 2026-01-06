-- Agregar columnas de datos del dispositivo a la tabla votes
-- Ejecuta este SQL en tu base de datos de producción

USE pular_pvddad;

-- Agregar columnas si no existen
SET @dbname = DATABASE();
SET @tablename = 'votes';

-- Columna user_agent
SET @columnname = 'user_agent';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname
     AND TABLE_NAME = @tablename
     AND COLUMN_NAME = @columnname) > 0,
  'SELECT ''Column user_agent already exists'' AS result;',
  'ALTER TABLE votes ADD COLUMN user_agent VARCHAR(500) NULL AFTER fingerprint;'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Columna platform
SET @columnname = 'platform';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname
     AND TABLE_NAME = @tablename
     AND COLUMN_NAME = @columnname) > 0,
  'SELECT ''Column platform already exists'' AS result;',
  'ALTER TABLE votes ADD COLUMN platform VARCHAR(100) NULL AFTER user_agent;'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Columna screen_resolution
SET @columnname = 'screen_resolution';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname
     AND TABLE_NAME = @tablename
     AND COLUMN_NAME = @columnname) > 0,
  'SELECT ''Column screen_resolution already exists'' AS result;',
  'ALTER TABLE votes ADD COLUMN screen_resolution VARCHAR(50) NULL AFTER platform;'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Columna hardware_concurrency
SET @columnname = 'hardware_concurrency';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname
     AND TABLE_NAME = @tablename
     AND COLUMN_NAME = @columnname) > 0,
  'SELECT ''Column hardware_concurrency already exists'' AS result;',
  'ALTER TABLE votes ADD COLUMN hardware_concurrency INT NULL AFTER screen_resolution;'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Insertar registro en la tabla migrations si no existe
INSERT IGNORE INTO migrations (migration, batch)
VALUES ('2025_10_24_191143_add_device_data_to_votes_table', 3);

SELECT 'Migración de datos del dispositivo completada exitosamente' AS resultado;
