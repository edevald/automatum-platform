#!/usr/bin/env bash

set -Eeuo pipefail

PROJECT_DIR="/home/engenheiro/workspace/automatum-platform"
BACKUP_DIR="${PROJECT_DIR}/backup"
DAILY_DIR="${BACKUP_DIR}/daily"
WEEKLY_DIR="${BACKUP_DIR}/weekly"
MONTHLY_DIR="${BACKUP_DIR}/monthly"

DATE_NOW="$(date '+%Y-%m-%d_%H-%M-%S')"
DAY_OF_WEEK="$(date '+%u')"
DAY_OF_MONTH="$(date '+%d')"

ENV_FILE="${PROJECT_DIR}/.env"

if [[ ! -f "${ENV_FILE}" ]]; then
    echo "ERRO: arquivo .env não encontrado em ${ENV_FILE}"
    exit 1
fi

set -a
source "${ENV_FILE}"
set +a

mkdir -p "${DAILY_DIR}" "${WEEKLY_DIR}" "${MONTHLY_DIR}"

DAILY_FILE="${DAILY_DIR}/automacao_${DATE_NOW}.sql.gz"

echo "Iniciando backup do banco ${MARIADB_DATABASE}..."

docker compose \
    --project-directory "${PROJECT_DIR}" \
    exec -T mariadb \
    mariadb-dump \
    -u"${BACKUP_DB_USER}" \
    -p"${BACKUP_DB_PASSWORD}" \
    --single-transaction \
    --routines \
    --events \
    --triggers \
    --hex-blob \
    --default-character-set=utf8mb4 \
    "${MARIADB_DATABASE}" \
    | gzip -9 > "${DAILY_FILE}"

if [[ ! -s "${DAILY_FILE}" ]]; then
    echo "ERRO: arquivo de backup vazio."
    rm -f "${DAILY_FILE}"
    exit 1
fi

gzip -t "${DAILY_FILE}"

echo "Backup diário criado: ${DAILY_FILE}"

if [[ "${DAY_OF_WEEK}" == "7" ]]; then
    cp "${DAILY_FILE}" "${WEEKLY_DIR}/"
    echo "Cópia semanal criada."
fi

if [[ "${DAY_OF_MONTH}" == "01" ]]; then
    cp "${DAILY_FILE}" "${MONTHLY_DIR}/"
    echo "Cópia mensal criada."
fi

find "${DAILY_DIR}" -type f -name '*.sql.gz' -mtime +30 -delete
find "${WEEKLY_DIR}" -type f -name '*.sql.gz' -mtime +84 -delete
find "${MONTHLY_DIR}" -type f -name '*.sql.gz' -mtime +730 -delete

echo "Retenção aplicada."
echo "Backup concluído com sucesso."
