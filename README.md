# 🚀 Sistema de Gestão de Vendas e Comissões

![Laravel](https://img.shields.io/badge/Laravel-12.x-red?style=for-the-badge&logo=laravel)
![Vue.js](https://img.shields.io/badge/Vue.js-3.x-green?style=for-the-badge&logo=vue.js)
![TypeScript](https://img.shields.io/badge/TypeScript-5.x-blue?style=for-the-badge&logo=typescript)
![Docker](https://img.shields.io/badge/Docker-Compose-blue?style=for-the-badge&logo=docker)

> **Sistema completo de vendas com comissão automática de 8,5% - Teste Técnico Tray**

## 📋 Requisitos Atendidos

### API (Laravel)
- ✅ Cadastrar vendedores (nome + email)
- ✅ Cadastrar vendas (vendedor + valor + data)
- ✅ Listar todos os vendedores
- ✅ Listar todas as vendas
- ✅ Listar vendas por vendedor específico
- ✅ Email diário para vendedores (vendas + comissão do dia)
- ✅ Email diário para administrador (soma total das vendas)
- ✅ Comissão automática de 8,5% sobre vendas

### Aplicação (Vue.js)
- ✅ Interação com os endpoints da API
- ✅ Reenvio manual de emails de comissão

### Bônus Implementados
- ✅ **Autenticação JWT** - Sistema completo de login/logout
- ✅ **Testes** - PHPUnit (backend) + Vitest (frontend)
- ✅ **Validação de dados** - FormRequests + validação frontend
- ✅ **Cache e Filas** - Redis para cache e processamento assíncrono
- ✅ **TypeScript** - Frontend 100% tipado
- ✅ **Docker** - Ambiente completo containerizado

## Funcionalidades

### Requisitos Obrigatórios ✅
- **Cadastro Vendedores** - Cadastro com nome e email único
- **Cadastro Vendas** - Comissão de 8,5% automática
- **Listagens** - Vendedores, vendas e vendas por vendedor
- **Emails Diários** - Para vendedores (comissões) e admin (totais)
- **Reenvio Manual** - Emails de comissão específicos

### Funcionalidades Bônus ✅
- **Autenticação JWT** - Login/logout seguro
- **Dashboard** - Estatísticas e gráficos em tempo real
- **Cache Redis** - Performance otimizada
- **Processamento Assíncrono** - Emails em background
- **Interface Responsiva** - Mobile-friendly

## Tecnologias

**Backend:** Laravel 12 + MySQL + Redis  
**Frontend:** Vue.js 3 + TypeScript + Naive UI  
**Infra:** Docker + Nginx + Supervisord

> **Detalhes técnicos:** [Backend README](./backend/README.md) | [Frontend README](./frontend/README.md)

## Instalação

### Ambiente Docker (Recomendado)
```bash
# 1. Clone e configure
git clone https://github.com/antonio-dsouza/tray-teste.git
cd tray-teste
cp backend/.env.example backend/.env

# 2. Suba o ambiente
docker-compose up -d --build

# 3. Configure backend
docker-compose exec backend composer install
docker-compose exec backend php artisan key:generate
docker-compose exec backend php artisan jwt:secret
docker-compose exec backend php artisan migrate --seed

# 4. Configure frontend
docker-compose exec frontend npm install
```

### Acessos
- **Frontend:** http://localhost:3000
- **API:** http://localhost:8080  
- **Swagger:** http://localhost:8080/docs

### Credenciais
```
Email: admin@teste-tray.com
Senha: password
```

## Estrutura do Projeto

```
tray-teste/
├── backend/              # 🔧 API Laravel 12
│   ├── README.md        # Arquitetura e design patterns
│   └── app/             # Controllers, Services, Models
├── frontend/            # 🎨 SPA Vue.js 3 + TypeScript  
│   ├── README.md        # Tecnologias e componentes
│   └── src/             # Views, Stores, Services
└── docker-compose.yml   # 🐳 Ambiente completo
```

## Documentação Detalhada

- **[Backend README](./backend/README.md)** - Arquitetura, design patterns e API
- **[Frontend README](./frontend/README.md)** - Tecnologias Vue.js e componentes

## Testes

```bash
# Backend
docker-compose exec backend php artisan test
```

## Critérios de Avaliação

✅ **Requisitos obrigatórios** - Cadastro vendedores/vendas + emails diários  
✅ **Organização** - Clean architecture + design patterns  
✅ **Qualidade** - Testes automatizados + validação  
✅ **Documentação** - READMEs detalhados + Swagger API  
✅ **Bônus** - JWT + TypeScript + Docker + Cache