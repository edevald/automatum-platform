ALTER TABLE meteo_dados
    ADD COLUMN firmware_name VARCHAR(40) NULL AFTER localizacao,
    ADD COLUMN firmware_version VARCHAR(20) NULL AFTER firmware_name,
    ADD COLUMN build_date DATE NULL AFTER firmware_version,
    ADD COLUMN build_number VARCHAR(30) NULL AFTER build_date,
    ADD COLUMN hardware_model VARCHAR(40) NULL AFTER build_number,
    ADD COLUMN hardware_revision VARCHAR(20) NULL AFTER hardware_model,
    ADD COLUMN git_commit VARCHAR(40) NULL AFTER hardware_revision;

CREATE INDEX idx_meteo_firmware_version
    ON meteo_dados (firmware_version);

CREATE INDEX idx_meteo_hardware_model
    ON meteo_dados (hardware_model);
    