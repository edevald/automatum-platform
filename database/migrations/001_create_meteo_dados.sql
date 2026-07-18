CREATE TABLE IF NOT EXISTS meteo_dados (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    timestamp_estacao DATETIME NOT NULL,
    estacao_id VARCHAR(50) NOT NULL,
    localizacao VARCHAR(100) NOT NULL,
    boot_count INT UNSIGNED NOT NULL,
    uptime_s BIGINT UNSIGNED NOT NULL,
    temp_bme_c DECIMAL(5,2) NULL,
    umid_bme_pct DECIMAL(5,2) NULL,
    pressao_hpa DECIMAL(7,2) NULL,
    lux DECIMAL(10,2) NULL,
    temp_dht_c DECIMAL(5,2) NULL,
    umid_dht_pct DECIMAL(5,2) NULL,
    vento_medio_60s DECIMAL(6,2) NULL,
    vento_rajada_60s DECIMAL(6,2) NULL,
    direcao_graus DECIMAL(6,2) NULL,
    pulsos_chuva_60s INT UNSIGNED NOT NULL DEFAULT 0,
    chuva_24h_mm DECIMAL(8,3) NOT NULL DEFAULT 0.000,

    PRIMARY KEY (id),
    INDEX idx_meteo_timestamp (timestamp_estacao),
    INDEX idx_meteo_estacao (estacao_id),
    INDEX idx_meteo_criado_em (criado_em),

    UNIQUE KEY uk_meteo_registro (
        estacao_id,
        timestamp_estacao,
        boot_count,
        uptime_s
    )
) ENGINE=InnoDB
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
  