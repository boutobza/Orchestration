# -*- coding: utf-8 -*

import socket
import subprocess
import time
import json

hote = ''
port = 12800

connexion_principale = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
connexion_principale.bind((hote, port))
connexion_principale.listen(5)
print("Le serveur écoute à présent sur le port {}".format(port))

while 1:

    connexion_avec_client, infos_connexion = connexion_principale.accept()
    msg_recu = connexion_avec_client.recv(1024)
    print("",msg_recu)
    #on décode le message recu en un tableau 
    data = json.loads(msg_recu)

    if data[0] == "destroyall":
        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/destroyAllContainers.yml"])
    elif data[0] == "create":
        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/createContainer.yml",
            "-e",
            "nb={0}".format(data[1]),
            "-e",
            "image={0}".format(data[2])])
    elif data[0] == "start":
        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/startContainer.yml",
            "-e",
            "id={0}".format(data[1])])
    elif data[0] == "stop":
        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/stopContainer.yml",
            "-e",
            "id={0}".format(data[1])])
    elif data[0] == "destroy":
        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/destroyContainer.yml",
            "-e",
            "id={0}".format(data[1])])

    #on attend que la commande c ansible se termine
    c.communicate()
    #la commande suivante recupère les infos des conteneurs
    p = subprocess.Popen(["/usr/bin/ansible-playbook",
        "-i",
        "/etc/ansible/hosts",
        "/var/www/pageDeGestion/html/playbooks/getContainersInfo.yml"])
    #on attend que la commande p se finisse au cas ou ^^
    p.communicate()
    #la commande suivante envoie ok (peut importe ce que l'on envoie)  à la page php qui est normalement en train d'attendre
    connexion_avec_client.send("ok")
    print("FIN commandes Ansible")
    connexion_avec_client.close()
print("Fermeture de la connexion")
connexion_principale.close()
