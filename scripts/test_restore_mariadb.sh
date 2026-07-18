#!/usr/bin/env bash

set -Eeuo pipefail

PROJECT_DIR="/home/engenheiro/workspace/automatum-platform"
ENV_FILE="${PROJECT_DIR}/.env"
BACKUP_FILE="${1:-}"

if [[ -z "${BACKUP_FILE}" ]]; then
    BACKUP_FILE="$(ls -t "${PROJECT_DIR}"/backup/daily/*.sql.gz 2>/dev/null | head -1 || true)"
fi

if [[ -z "${BACKUP_FILE}" || ! -f "${BACKUP_FILE}" ]]; then
    echo "ERRO: nenhum arquivo de backup encontrado."
    exit 1
fi

set -a
source "${ENV_FILE}"
set +a

TEST_DB="automacao_restore_test"

echo "Criando banco temporário ${TEST_DB}..."

docker compose \
    --project-directory "${PROJECT_DIR}" \
    exec -T mariadb \
    mariadb \
    -uroot \
    -p"${MARIADB_ROOT_PASSWORD}" \
    -e "
        DROP DATABASE IF EXISTS ${TEST_DB};
        CREATE DATABASE ${TEST_DB}
        CHARACTER SET utf8mb4
        COLLATE utf8mb4_unicode_ci;
    "

echo "Restaurando ${BACKUP_FILE}..."

gzip -dc "${BACKUP_FILE}" | docker compose \
    --project-directory "${PROJECT_DIR}" \
    exec -T mariadb \
    mariadb \
    -uroot \
    -p"${MARIADB_ROOT_PASSWORD}" \
    "${TEST_DB}"

echo "Validando restauração..."

docker compose \
    --project-directory "${PROJECT_DIR}" \
    exec -T mariadb \
    mariadb \
    -uroot \
    -p"${MARIADB_ROOT_PASSWORD}" \
    -e "
        SELECT SCHEMA_NAME
        FROM information_schema.SCHEMATA
        WHERE SCHEMA_NAME='${TEST_DB}';

        SELECT COUNT(*) AS quantidade_tabelas
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA='${TEST_DB}';
    "

echo "Removendo banco temporário..."

docker compose \
    --project-directory "${PROJECT_DIR}" \
    exec -T mariadb \
    mariadb \
    -uroot \
    -p"${MARIADB_ROOT_PASSWORD}" \
    -e "DROP DATABASE ${TEST_DB};"

echo "Teste de restauração concluído com sucesso."
