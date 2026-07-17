# Usuários do banco Automacao

## automacao_admin

Finalidade: administração do schema `automacao`.

Não deve ser utilizado por aplicações.

## api_write

Finalidade: ingestão de dados pela API.

Privilégios:

- SELECT
- INSERT

## grafana_read

Finalidade: consultas do Grafana.

Privilégios:

- SELECT

## python_app

Finalidade: relatórios, automações e processamento de dados.

Privilégios:

- SELECT
- INSERT
- UPDATE

## backup_user

Finalidade: backups lógicos do MariaDB.

Privilégios:

- SELECT
- LOCK TABLES
- SHOW VIEW
- EVENT
- TRIGGER

## Regras

- O usuário root é reservado para administração global do MariaDB.
- As aplicações não utilizam root.
- As senhas reais permanecem apenas no arquivo `.env`.
- A porta 3306 não é exposta publicamente.