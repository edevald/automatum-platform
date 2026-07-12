# Automatum Platform — Princípios da Plataforma

## 1. Objetivo

A Automatum Platform é uma infraestrutura portável para aplicações de
automação industrial, IoT, monitoramento, firmware OTA, visualização de dados
e serviços de inteligência artificial.

## 2. Portabilidade

A plataforma deve poder ser migrada entre provedores de VPS sem depender de
configurações proprietárias.

A reconstrução deve depender principalmente de:

- Ubuntu Server;
- Docker Engine;
- Docker Compose;
- repositório Git;
- arquivos de configuração;
- backups externos.

## 3. Fonte da verdade

A configuração da infraestrutura será mantida neste repositório.

O Docker Compose será a fonte da verdade dos serviços, redes, volumes e
dependências da plataforma.

Interfaces gráficas de gerenciamento poderão ser utilizadas, mas não devem
ser obrigatórias para reconstruir o ambiente.

## 4. Aplicações em contêineres

Aplicações da plataforma não serão instaladas diretamente no Ubuntu.

O sistema operacional deverá conter apenas os componentes essenciais:

- Docker;
- Docker Compose;
- Git;
- ferramentas administrativas;
- ferramentas de segurança e backup.

## 5. Domínios

Os serviços serão publicados por subdomínios:

- api.automatum.tech
- grafana.automatum.tech
- db.automatum.tech
- firmware.automatum.tech
- panel.automatum.tech
- mqtt.automatum.tech
- status.automatum.tech

## 6. API

A API deverá utilizar versionamento explícito:

- /api/v1/*
- /api/v2/*, futuramente

O primeiro endpoint meteorológico será:

- /api/v1/meteo/receber.php

As respostas deverão utilizar JSON e códigos HTTP adequados.

## 7. Banco de dados

O banco principal será:

- automacao

As tabelas deverão utilizar:

- letras minúsculas;
- snake_case;
- nomes sem acentos;
- prefixos por domínio funcional.

Exemplos:

- meteo_dados
- meteo_estacoes
- freezer_dados
- freezer_equipamentos

## 8. Privilégio mínimo

Cada aplicação deverá possuir usuário próprio no banco:

- automacao_admin
- api_write
- grafana_read
- python_app
- backup_user

Nenhuma aplicação deverá utilizar o usuário root do MariaDB.

## 9. Segredos

Senhas, tokens e chaves não poderão ser versionados.

Os segredos deverão ser fornecidos por:

- arquivo .env local;
- variáveis de ambiente;
- secrets do ambiente de execução.

O repositório deverá conter somente um arquivo .env.example.

## 10. Firmware OTA

Os firmwares serão publicados por HTTPS:

- https://firmware.automatum.tech

Cada família de dispositivo deverá possuir:

- arquivo binário versionado;
- manifest.json;
- hash SHA-256;
- versão atual;
- versão anterior para recuperação.

## 11. Persistência

Dados persistentes deverão ficar separados do código.

Exemplos:

- volume do MariaDB;
- volume do Grafana;
- firmwares;
- arquivos de backup.

A remoção ou recriação de um contêiner não poderá apagar os dados.

## 12. Backups

Os backups deverão incluir:

- dump do MariaDB;
- configuração do Grafana;
- arquivos de firmware;
- arquivos de configuração;
- scripts Python;
- aplicação PHP.

Deverá existir pelo menos uma cópia fora da VPS.

## 13. Migração

Uma migração deverá seguir, em princípio:

1. criar nova VPS;
2. instalar Docker e Git;
3. clonar o repositório;
4. copiar o arquivo .env;
5. restaurar os backups;
6. executar Docker Compose;
7. validar os serviços;
8. alterar os registros DNS no Cloudflare.

## 14. Ambientes

A plataforma deverá ser preparada para:

- homologação;
- produção.

Inicialmente, os serviços poderão compartilhar a mesma VPS, mantendo
configurações e dados claramente separados quando necessário.

## 15. Alterações controladas

O Raspberry Pi permanecerá operacional até a validação completa da VPS.

O ESP32 somente será atualizado para a nova API após:

- API validada;
- banco validado;
- Grafana validado;
- HTTPS validado;
- testes de gravação e consulta concluídos.
