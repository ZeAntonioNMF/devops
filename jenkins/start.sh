#FILE PATH - ${WORKSPACE_EXTRA}/.env

# DOCKER
DOCKER_IMAGE=node:14.20.1-bullseye-slim

# PROJECT
APP=${PROJECT}
PROJECT=${PROJECT}
PORT=XXXX
PORT_CONTAINER=XXXX
DADOS=/dados
WORKDIR=/usr/src/app
NODE_UID=1004
NODE_GID=users
CONTAINER_USERNAME=user

# ENVIRONMENT
NODE_ENV=${NODE_ENV}
DEPLOY_HOST=nomedoserver-docker.dominio.net
DATABASE_FILENAME=/dados/${PROJECT}/.tmp/data.db
HOST=0.0.0.0
URL=https://${HOST_HOMOLOG}/${PROJECT}
TZ=utc
NODE_VERSION=14
CMD="npm start"
ENV=homolog

# TOKENS
ADMIN_JWT_SECRET=${ADMIN_JWT_SECRET}
JWT_SECRET=${JWT_SECRET}

# CORS
CORS_ORIGIN=*

# LDAP
LDAP_URL=ldap://${LDAP_SES}
LDAP_DEFAULT_PASSWORD='Def#Passwd@'

# APP
APP_KEYS=${APP_KEYS}

# SERVER RESOURCE
LIMITS_CPU=0.75
LIMITS_MEMORY=1GB
RESERVATIONS_CPU= 0.25
RESERVATIONS_MEMORY=256M


# EXECUTAR SHELL
#!/bin/bash +x -e

# PROJECT BUILD
cd $WORKSPACE
/bin/bash $JENKINS_REPO/docker/deploy/start.sh