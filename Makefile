.PHONY: pull build push deploy login help

include .env
export $(shell sed 's/=.*//' .env)

# Variáveis
IMAGE=ghcr.io/$(DOCKER_USERNAME)/wootext:latest

pull:
	git pull origin main

login:
	docker login --username $(DOCKER_USERNAME) --password $(DOCKER_PASSWORD) ghcr.io

install:
	npm install
	npm run build
	composer install

build:
	docker build -t $(IMAGE) .

push:
	docker push $(IMAGE)

deploy: pull build push
#     curl -X POST https://seuservidor:9443/api/webhooks/abc123

help:
	@echo "Comandos disponíveis:"
	@echo "  \033[36mpull\033[0m                 Atualiza o repositório local com o remoto"
	@echo "  \033[36mlogin\033[0m                Faz login no Docker registry"
	@echo "  \033[36minstall\033[0m              Instala dependências do npm e composer"
	@echo "  \033[36mbuild\033[0m                Faz build da imagem Docker"
	@echo "  \033[36mpush\033[0m                 Envia a imagem Docker para o registry"
	@echo "  \033[36mdeploy\033[0m               Executa pull, build e push em sequência"
	@echo "  \033[36mhelp\033[0m                 Lista todos os comandos disponíveis"