#!/bin/bash
# EN-US | Create a systemd service that autostarts & manages a docker-compose instance in the current directory 
# PT-BR | Crie um serviço systemd que inicia automaticamente e gerencia uma instância docker-compose no diretório atual
# Licensed as CC0 1.0 Universal (https://creativecommons.org/publicdomain/zero/1.0/)

SERVICENAME=$(basename $(pwd))

echo "Creating systemd service... /etc/systemd/system/${SERVICENAME}.service"
# Create systemd service file
sudo cat >/etc/systemd/system/$SERVICENAME.service <<EOF
[Unit]
Description=$SERVICENAME
Requires=docker.service
After=docker.service

[Service]
Restart=always
User=root
Group=docker
WorkingDirectory=$(pwd)
# Shutdown container (if running) when unit is started
ExecStartPre=$(which docker-compose) -f docker-compose.yml down
# Start container when unit is started
ExecStart=$(which docker-compose) -f docker-compose.yml up
# Stop container when unit is stopped
ExecStop=$(which docker-compose) -f docker-compose.yml down

[Install]
WantedBy=multi-user.target
EOF

echo "Enabling & starting $SERVICENAME"
# Autostart systemd service
sudo systemctl enable $SERVICENAME.service
# Start systemd service now
sudo systemctl start $SERVICENAME.service
